<?php

namespace App\Services\Intelligence;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CollaborativeFilterService
 *
 * "Users like you also loved these properties"
 *
 * Algorithm:
 *  1. Find users who share ≥2 high-signal interactions with current user
 *     (favorite, contact_intent, return_to_listing — these are purchase-intent signals)
 *  2. Score those "similar users" by overlap strength
 *  3. Collect properties those similar users engaged with that current user hasn't seen
 *  4. Re-rank candidates by how many similar users engaged + interaction weight
 *  5. Return top N, excluding already-viewed properties
 *
 * No new tables needed — pure query on user_property_interactions.
 */
class CollaborativeFilterService
{
    // Signals that indicate real intent (not just passive browsing)
    private const INTENT_SIGNALS = [
        'favorite',
        'contact_intent',
        'return_to_listing',
        'compare',
        'share_property',
    ];

    // Weights per signal type for candidate scoring
    private const SIGNAL_WEIGHTS = [
        'return_to_listing' => 8.0,
        'contact_intent'    => 6.0,
        'favorite'          => 5.0,
        'compare'           => 4.0,
        'share_property'    => 3.5,
        'photo_gallery_open' => 2.5,
        'view'              => 1.0,
    ];

    // Minimum shared properties to consider a user "similar"
    private const MIN_OVERLAP = 2;

    // How many similar users to consider
    private const MAX_SIMILAR_USERS = 50;

    // Final candidates to return
    private const MAX_CANDIDATES = 15;

    // Cache TTL: 30 minutes (collaborative is expensive, cache aggressively)
    private const CACHE_TTL = 1800;

    // Lookback window for interactions
    private const LOOKBACK_DAYS = 90;

    /**
     * Get collaborative recommendations for a user.
     *
     * @param int $userId
     * @param string $language
     * @return array  [ 'properties' => [...], 'similar_user_count' => int, 'source' => 'collaborative' ]
     */
    public function getRecommendations(int $userId, string $language = 'en'): array
    {
        $cacheKey = "collab_recs_{$userId}_{$language}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $language) {
            return $this->compute($userId, $language);
        });
    }

    /**
     * Invalidate cache when user generates new signals.
     */
    public function invalidate(int $userId): void
    {
        foreach (['en', 'ar', 'ku'] as $lang) {
            Cache::forget("collab_recs_{$userId}_{$lang}");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CORE COMPUTATION
    // ─────────────────────────────────────────────────────────────────────────

    private function compute(int $userId, string $language): array
    {
        $since = now()->subDays(self::LOOKBACK_DAYS);

        // ── STEP 1: Find similar users ────────────────────────────────────────
        // Users who interacted with ≥2 of the same properties as current user
        // using intent-level signals only (not passive views)
        $intentSignals = implode("','", self::INTENT_SIGNALS);

        $similarUsers = DB::select("
            SELECT
                p2.user_id        AS similar_user_id,
                COUNT(DISTINCT p2.property_id) AS shared_properties,
                SUM(
                    CASE p2.interaction_type
                        WHEN 'return_to_listing' THEN 8.0
                        WHEN 'contact_intent'    THEN 6.0
                        WHEN 'favorite'          THEN 5.0
                        WHEN 'compare'           THEN 4.0
                        WHEN 'share_property'    THEN 3.5
                        ELSE 1.0
                    END
                ) AS similarity_score
            FROM user_property_interactions p1
            JOIN user_property_interactions p2
                ON  p1.property_id    = p2.property_id
                AND p1.user_id       != p2.user_id
                AND p2.interaction_type IN ('{$intentSignals}')
                AND p2.created_at    >= ?
            WHERE p1.user_id          = ?
              AND p1.interaction_type IN ('{$intentSignals}')
              AND p1.created_at       >= ?
            GROUP BY p2.user_id
            HAVING shared_properties  >= ?
            ORDER BY similarity_score DESC
            LIMIT ?
        ", [
            $since,
            $userId,
            $since,
            self::MIN_OVERLAP,
            self::MAX_SIMILAR_USERS,
        ]);

        if (empty($similarUsers)) {
            return $this->emptyResult();
        }

        $similarUserIds    = array_column($similarUsers, 'similar_user_id');
        $similarUserCount  = count($similarUserIds);

        // ── STEP 2: Properties current user already knows about ───────────────
        $seenPropertyIds = DB::table('user_property_interactions')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->pluck('property_id')
            ->unique()
            ->toArray();

        // ── STEP 3: Candidate properties from similar users ───────────────────
        // Score = Σ (signal_weight × similarity_score_of_that_user)
        $similarityByUser = collect($similarUsers)
            ->keyBy('similar_user_id')
            ->map(fn($u) => $u->similarity_score)
            ->toArray();

        $placeholders = implode(',', array_fill(0, count($similarUserIds), '?'));
        $seenPlaceholders = !empty($seenPropertyIds)
            ? 'AND upi.property_id NOT IN (' . implode(',', array_fill(0, count($seenPropertyIds), '?')) . ')'
            : '';

        $candidateQuery = "
            SELECT
                upi.property_id,
                COUNT(DISTINCT upi.user_id)  AS supporter_count,
                SUM(
                    CASE upi.interaction_type
                        WHEN 'return_to_listing' THEN 8.0
                        WHEN 'contact_intent'    THEN 6.0
                        WHEN 'favorite'          THEN 5.0
                        WHEN 'compare'           THEN 4.0
                        WHEN 'share_property'    THEN 3.5
                        WHEN 'photo_gallery_open'THEN 2.5
                        ELSE 1.0
                    END
                ) AS raw_score
            FROM user_property_interactions upi
            WHERE upi.user_id IN ({$placeholders})
              AND upi.created_at >= ?
              {$seenPlaceholders}
            GROUP BY upi.property_id
            ORDER BY raw_score DESC
            LIMIT ?
        ";

        $bindings = array_merge(
            $similarUserIds,
            [$since],
            $seenPropertyIds,
            [self::MAX_CANDIDATES * 3]  // fetch more than needed, filter below
        );

        $candidates = DB::select($candidateQuery, $bindings);

        if (empty($candidates)) {
            return $this->emptyResult();
        }

        // ── STEP 4: Load and rank property models ─────────────────────────────
        $candidateIds    = array_column($candidates, 'property_id');
        $scoreByProperty = collect($candidates)->keyBy('property_id');

        $properties = Property::whereIn('id', $candidateIds)
            ->where('is_active', true)
            ->where('is_published', true)
            ->limit(self::MAX_CANDIDATES)
            ->get();

        // Attach collaborative score to each property for ranking
        $ranked = $properties
            ->map(function ($prop) use ($scoreByProperty) {
                $score = $scoreByProperty->get($prop->id);
                $prop->_collab_score       = $score?->raw_score ?? 0;
                $prop->_supporter_count    = $score?->supporter_count ?? 0;
                return $prop;
            })
            ->sortByDesc('_collab_score')
            ->values();

        // ── STEP 5: Format response ────────────────────────────────────────────
        $formatted = $ranked->map(function ($prop) use ($language) {
            return $this->formatProperty($prop, $language);
        })->filter()->values()->toArray();

        return [
            'properties'         => $formatted,
            'similar_user_count' => $similarUserCount,
            'source'             => 'collaborative',
            'generated_at'       => now()->toISOString(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  STRIP CONTEXT
    //  Called by SmartStripService to power the "users_like_you" strip
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get context for the "users_like_you" SmartStrip.
     * Returns: [ 'count' => int, 'top_property' => [...] ] or null if not enough data.
     */
    public function getStripContext(int $userId): ?array
    {
        $cacheKey = "collab_strip_{$userId}";

        return Cache::remember($cacheKey, 900, function () use ($userId) {
            $since = now()->subDays(self::LOOKBACK_DAYS);
            $intentSignals = implode("','", self::INTENT_SIGNALS);

            // Count of distinct similar users (quick query)
            $result = DB::selectOne("
                SELECT COUNT(DISTINCT p2.user_id) AS similar_count
                FROM user_property_interactions p1
                JOIN user_property_interactions p2
                    ON  p1.property_id    = p2.property_id
                    AND p1.user_id       != p2.user_id
                    AND p2.interaction_type IN ('{$intentSignals}')
                    AND p2.created_at    >= ?
                WHERE p1.user_id          = ?
                  AND p1.interaction_type IN ('{$intentSignals}')
                  AND p1.created_at       >= ?
            ", [$since, $userId, $since]);

            $count = $result?->similar_count ?? 0;

            if ($count < 3) return null;  // not enough signal

            // Get top candidate property for the strip preview
            $recs = $this->getRecommendations($userId);
            $topProperty = $recs['properties'][0] ?? null;

            if (!$topProperty) return null;

            return [
                'similar_user_count' => $count,
                'top_property'       => $topProperty,
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function formatProperty(Property $prop, string $language): ?array
    {
        try {
            $name        = $this->localize($prop->name, $language);
            $description = $this->localize($prop->description, $language);
            $location    = $this->localize($prop->location ?? $prop->address, $language);

            return [
                'id'               => $prop->id,
                'name'             => $name,
                'description'      => $description,
                'location'         => $location,
                'price'            => $prop->price,
                'currency'         => $prop->currency ?? 'USD',
                'period'           => $prop->period ?? 'total',
                'type'             => $prop->property_type ?? $prop->type,
                'status'           => $prop->listing_type ?? $prop->status,
                'bedrooms'         => $prop->bedrooms ?? 0,
                'bathrooms'        => $prop->bathrooms ?? 0,
                'area'             => $prop->area ?? 0,
                'images'           => $prop->images ?? [],
                'is_verified'      => (bool) $prop->is_verified,
                'is_featured'      => (bool) ($prop->is_featured ?? false),
                '_source'          => 'collaborative',
                '_supporter_count' => $prop->_supporter_count ?? 0,
            ];
        } catch (\Throwable $e) {
            Log::warning("CollaborativeFilter: format error for property {$prop->id}: {$e->getMessage()}");
            return null;
        }
    }

    private function localize(mixed $value, string $language): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded[$language] ?? $decoded['en'] ?? $value;
            }
            return $value;
        }
        if (is_array($value)) {
            return $value[$language] ?? $value['en'] ?? '';
        }
        return '';
    }

    private function emptyResult(): array
    {
        return [
            'properties'         => [],
            'similar_user_count' => 0,
            'source'             => 'collaborative',
            'generated_at'       => now()->toISOString(),
        ];
    }
}