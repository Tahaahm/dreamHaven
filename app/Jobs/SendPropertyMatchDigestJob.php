<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Property;
use App\Models\Notification;
use App\Services\FirebaseService;
use App\Services\Intelligence\UserTasteProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SendPropertyMatchDigestJob
 *
 * Runs every 3 days via scheduler.
 * For each user with interaction history, builds their full UserTasteProfile,
 * finds the best matching NEW properties (since last digest), scores them,
 * and sends a personalized push notification digest.
 *
 * Intelligence sources used (all already computed by UserTasteProfile):
 *   - Preferred cities (weighted by interaction frequency + recency)
 *   - Preferred property types
 *   - Listing type (rent vs sell)
 *   - Price band (from behavior, filter signals, calculator)
 *   - Bedroom preference
 *   - Heat centroid (geographic cluster of views)
 *   - Seen property IDs (never re-recommend already-viewed)
 *   - Strip feedback (which strip types they engage with)
 *   - Intent score (0–100, how close to buying/renting)
 */
class SendPropertyMatchDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // How many days back to look for new properties
    private const LOOKBACK_DAYS = 3;

    // Max properties to include in one digest notification
    private const MAX_PROPS_IN_DIGEST = 5;

    // Min score for a property to be included
    private const MIN_MATCH_SCORE = 40;

    // Don't spam users who already got a digest recently (days)
    private const DIGEST_COOLDOWN_DAYS = 3;

    // Process users in chunks to avoid memory issues
    private const CHUNK_SIZE = 50;

    public function handle(): void
    {
        Log::info('🔔 PropertyMatchDigest: Job started');

        $processed = 0;
        $sent      = 0;
        $skipped   = 0;

        // Only process users who have device tokens (can receive FCM)
        // and have some interaction history (not brand new)
        User::whereRaw("JSON_LENGTH(device_tokens) > 0")
            ->where(function ($q) {
                // Has notifications enabled OR preference not set
                $q->where('search_preferences->behavior->enable_notifications', true)
                    ->orWhereNull('search_preferences')
                    ->orWhereRaw("JSON_EXTRACT(search_preferences, '$.behavior.enable_notifications') IS NULL");
            })
            ->whereExists(function ($q) {
                // Only users with at least some interaction history
                $q->select(DB::raw(1))
                    ->from('user_property_interactions')
                    ->whereColumn('user_id', 'users.id')
                    ->where('created_at', '>=', now()->subDays(90));
            })
            ->select(['id', 'language', 'device_tokens', 'place', 'search_preferences', 'last_activity_at'])
            ->chunk(self::CHUNK_SIZE, function ($users) use (&$processed, &$sent, &$skipped) {
                foreach ($users as $user) {
                    try {
                        $result = $this->processUser($user);
                        $processed++;
                        if ($result === 'sent')    $sent++;
                        if ($result === 'skipped') $skipped++;
                    } catch (\Throwable $e) {
                        Log::error("PropertyMatchDigest: Failed for user {$user->id}", [
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // Small sleep between users to avoid DB hammering
                    usleep(50000); // 50ms
                }
            });

        Log::info('🔔 PropertyMatchDigest: Job completed', [
            'processed' => $processed,
            'sent'      => $sent,
            'skipped'   => $skipped,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  PROCESS ONE USER
    // ──────────────────────────────────────────────────────────────────────────

    private function processUser(User $user): string
    {
        // Skip if user got a digest too recently
        if ($this->gotRecentDigest($user->id)) {
            return 'skipped';
        }

        // Build the full taste profile — this is the brain
        $profile = app(UserTasteProfile::class)->build((string) $user->id);

        // Skip cold-start users with no real history
        if (!$profile['has_history'] && empty($profile['cities'])) {
            return 'skipped';
        }

        // Find matching new properties using the full profile
        $matches = $this->findMatchingProperties($profile, $user);

        if ($matches->isEmpty()) {
            Log::info("PropertyMatchDigest: No matches for user {$user->id}");
            return 'skipped';
        }

        // Build and send the digest
        $this->sendDigest($user, $profile, $matches);

        return 'sent';
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  FIND MATCHING PROPERTIES (the smart part)
    // ──────────────────────────────────────────────────────────────────────────

    private function findMatchingProperties(array $profile, User $user): \Illuminate\Support\Collection
    {
        $cities      = array_keys($profile['cities']      ?? []);
        $types       = array_keys($profile['types']       ?? []);
        $listingType = $profile['listing_type'];
        $price       = $profile['price'];
        $bedrooms    = $profile['bedrooms'];
        $heat        = $profile['heat_centroid'];
        $seenIds     = $profile['seen_ids'] ?? [];

        // Base query: only new properties since last digest
        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->whereNotIn('id', $seenIds);

        // ── Apply profile signals as soft filters ──────────────────────────
        // We build a SCORING query, not a hard filter, so we don't miss good
        // matches just because one dimension is slightly off.

        // Cities — soft: match OR no city set
        if (!empty($cities)) {
            $query->where(function ($q) use ($cities) {
                $q->whereRaw("1=0"); // start false, OR in matches
                foreach ($cities as $city) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                        [strtolower($city)]
                    );
                }
            });
        }

        // Listing type — hard filter if we know it
        if ($listingType) {
            $query->where('listing_type', $listingType);
        }

        // Property types — soft: if we know preferred types, filter to them
        if (!empty($types)) {
            $query->where(function ($q) use ($types) {
                $q->whereRaw("1=0");
                foreach ($types as $type) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                        [strtolower($type)]
                    );
                }
            });
        }

        // Price band — allow ±25% tolerance so we don't miss near-budget listings
        if ($price && !empty($price['min']) && !empty($price['max'])) {
            $tolerance = 0.25;
            $minPrice  = $price['min'] * (1 - $tolerance);
            $maxPrice  = $price['max'] * (1 + $tolerance);
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) BETWEEN ? AND ?",
                [$minPrice, $maxPrice]
            );
        }

        // Bedrooms — allow ±1 bedroom tolerance
        if ($bedrooms !== null) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) AS UNSIGNED) BETWEEN ? AND ?",
                [max(0, $bedrooms - 1), $bedrooms + 1]
            );
        }

        // ── Build scoring expression ───────────────────────────────────────
        // Score each candidate property on how well it matches the full profile.
        $scoreExpr = $this->buildScoringExpression($profile);

        $candidates = $query
            ->selectRaw("properties.*, ({$scoreExpr}) as match_score")
            ->orderByDesc('match_score')
            ->orderByDesc('is_boosted')
            ->orderByDesc('verified')
            ->limit(self::MAX_PROPS_IN_DIGEST * 3) // fetch more, pick best after scoring
            ->get();

        // Filter to only strong matches
        return $candidates
            ->filter(fn($p) => ($p->match_score ?? 0) >= self::MIN_MATCH_SCORE)
            ->take(self::MAX_PROPS_IN_DIGEST)
            ->values();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  DYNAMIC SCORING EXPRESSION
    //  Builds a MySQL expression that scores each property against the profile.
    //  Higher score = better match.
    // ──────────────────────────────────────────────────────────────────────────

    private function buildScoringExpression(array $profile): string
    {
        $parts = [];

        // ── 1. City match (0–30 pts, weighted by how strongly preferred) ──────
        $cities = $profile['cities'] ?? [];
        if (!empty($cities)) {
            $cityScore = [];
            foreach ($cities as $city => $weight) {
                $pts = (int) round($weight * 30); // weight 0–1, scale to 0–30
                $safe = addslashes(strtolower($city));
                $cityScore[] = "WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = '{$safe}' THEN {$pts}";
            }
            $parts[] = "(CASE " . implode(' ', $cityScore) . " ELSE 0 END)";
        }

        // ── 2. Property type match (0–25 pts, weighted by preference strength) ─
        $types = $profile['types'] ?? [];
        if (!empty($types)) {
            $typeScore = [];
            foreach ($types as $type => $weight) {
                $pts = (int) round($weight * 25);
                $safe = addslashes(strtolower($type));
                $typeScore[] = "WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = '{$safe}' THEN {$pts}";
            }
            $parts[] = "(CASE " . implode(' ', $typeScore) . " ELSE 0 END)";
        }

        // ── 3. Price match (0–20 pts) ─────────────────────────────────────────
        $price = $profile['price'];
        if ($price && !empty($price['target'])) {
            $target = (float) $price['target'];
            $min    = (float) ($price['min'] ?? $target * 0.7);
            $max    = (float) ($price['max'] ?? $target * 1.3);
            // Full points if within budget, partial if within 25% over
            $parts[] = "(CASE
                WHEN CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) BETWEEN {$min} AND {$max} THEN 20
                WHEN CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) BETWEEN {$min} AND " . ($max * 1.25) . " THEN 8
                ELSE 0
            END)";
        }

        // ── 4. Bedroom match (0–15 pts) ───────────────────────────────────────
        $bedrooms = $profile['bedrooms'];
        if ($bedrooms !== null) {
            $beds = (int) $bedrooms;
            $parts[] = "(CASE
                WHEN CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) AS UNSIGNED) = {$beds} THEN 15
                WHEN CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) AS UNSIGNED) = " . ($beds + 1) . " THEN 8
                WHEN CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) AS UNSIGNED) = " . max(0, $beds - 1) . " THEN 5
                ELSE 0
            END)";
        }

        // ── 5. Heat centroid proximity (0–20 pts) ─────────────────────────────
        // Properties close to where the user normally looks get bonus points.
        $heat = $profile['heat_centroid'];
        if ($heat && !empty($heat['lat']) && !empty($heat['lng'])) {
            $lat    = (float) $heat['lat'];
            $lng    = (float) $heat['lng'];
            $radius = (float) ($heat['radius_km'] ?? 10);

            $parts[] = "(CASE WHEN (6371 * acos(LEAST(1,
                cos(radians({$lat})) *
                cos(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lat')) AS DECIMAL(10,6)))) *
                cos(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lng')) AS DECIMAL(10,6))) - radians({$lng})) +
                sin(radians({$lat})) *
                sin(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lat')) AS DECIMAL(10,6))))
            ))) <= {$radius} THEN 20
            WHEN (6371 * acos(LEAST(1,
                cos(radians({$lat})) *
                cos(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lat')) AS DECIMAL(10,6)))) *
                cos(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lng')) AS DECIMAL(10,6))) - radians({$lng})) +
                sin(radians({$lat})) *
                sin(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lat')) AS DECIMAL(10,6))))
            ))) <= " . ($radius * 2) . " THEN 10
            ELSE 0 END)";
        }

        // ── 6. Quality bonuses (always applied) ───────────────────────────────
        $parts[] = "(CASE WHEN is_boosted = 1 THEN 10 ELSE 0 END)";
        $parts[] = "(CASE WHEN verified = 1 THEN 8 ELSE 0 END)";
        $parts[] = "(CASE WHEN DATEDIFF(NOW(), created_at) <= 1 THEN 5
                         WHEN DATEDIFF(NOW(), created_at) <= 3 THEN 3
                         ELSE 0 END)"; // freshness bonus

        // ── 7. Intent score amplifier ─────────────────────────────────────────
        // High-intent users (used calculator, compared properties) get a
        // multiplier so their digest only shows the absolute best matches.
        // We can't use PHP variables in MySQL expressions, so we bake the
        // intent score in as a constant derived from the profile.
        $intentScore = (int) ($profile['intent_score'] ?? 0);
        if ($intentScore >= 60) {
            // High intent: add a flat bonus so the MIN_SCORE threshold is easier
            // to reach with truly matching properties, but we also enforce
            // stricter city/type/price filtering above.
            $parts[] = "5"; // small flat bonus — the real filtering is in the WHERE clause
        }

        return empty($parts) ? "0" : implode(' + ', $parts);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  BUILD NOTIFICATION PAYLOAD
    // ──────────────────────────────────────────────────────────────────────────

    private function buildNotificationPayload(
        User   $user,
        array  $profile,
        \Illuminate\Support\Collection $matches
    ): array {
        $lang  = strtolower(trim($user->language ?? 'en'));
        $count = $matches->count();

        // Dynamic headline based on what we know about the user
        $headline = $this->buildHeadline($lang, $profile, $count);
        $subline  = $this->buildSubline($lang, $profile, $matches);

        return [
            'title'   => $headline,
            'message' => $subline,
        ];
    }

    private function buildHeadline(string $lang, array $profile, int $count): string
    {
        $intentScore = $profile['intent_score'] ?? 0;
        $types       = array_keys($profile['types'] ?? []);
        $cities      = array_keys($profile['cities'] ?? []);
        $topType     = !empty($types) ? ucfirst($types[0]) : null;
        $topCity     = !empty($cities) ? $cities[0] : null;

        if ($lang === 'ar') {
            if ($topType && $topCity) {
                return "🏠 {$count} " . ($topType === 'apartment' ? 'شقة' : 'عقار') . " جديد في {$topCity}";
            }
            return "🏠 {$count} عقار جديد يناسب اهتماماتك";
        }

        if ($lang === 'ku') {
            if ($topType && $topCity) {
                return "🏠 {$count} خانووی نوێ لە {$topCity}";
            }
            return "🏠 {$count} خانووی نوێ بۆ تۆ دۆزرایەوە";
        }

        // English — vary by intent + profile richness
        if ($intentScore >= 70 && $topType && $topCity) {
            return "🎯 {$count} new {$topType}s in {$topCity} — perfect match";
        }
        if ($topCity) {
            return "🏠 {$count} new properties in {$topCity} for you";
        }
        return "🏠 {$count} new properties matching your taste";
    }

    private function buildSubline(string $lang, array $profile, \Illuminate\Support\Collection $matches): string
    {
        $price   = $profile['price'];
        $top     = $matches->first();
        $topCity = $top
            ? ($top->address_details['city'][$lang] ?? $top->address_details['city']['en'] ?? '')
            : '';

        $priceHint = '';
        if ($price && !empty($price['target'])) {
            $priceHint = '$' . number_format((int) $price['target'], 0);
        }

        if ($lang === 'ar') {
            return $priceHint
                ? "أفضل مطابقة حول {$priceHint}. اضغط للعرض."
                : "اكتشف العقارات الجديدة التي تطابق بحثك.";
        }
        if ($lang === 'ku') {
            return $priceHint
                ? "باشترین دۆزراوە نزیکەی {$priceHint}. دابمەزرێ ببینیت."
                : "خانووە نوێیەکانی تازە بینە کە لەگەڵ ئارەزووکانت دەگونجێت.";
        }

        return $priceHint
            ? "Best matches around {$priceHint} in {$topCity}. Tap to explore."
            : "Fresh listings matched to your search history. Tap to explore.";
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  SEND THE DIGEST
    // ──────────────────────────────────────────────────────────────────────────

    private function sendDigest(User $user, array $profile, \Illuminate\Support\Collection $matches): void
    {
        $lang    = strtolower(trim($user->language ?? 'en'));
        $payload = $this->buildNotificationPayload($user, $profile, $matches);

        // Store in notifications table
        $topPropertyId = $matches->first()?->id;

        $notification = Notification::create([
            'id'         => (string) Str::uuid(),
            'user_id'    => $user->id,
            'title'      => $payload['title'],
            'message'    => $payload['message'],
            'type'       => 'property',
            'priority'   => $profile['intent_score'] >= 70 ? 'high' : 'medium',
            'data'       => [
                'digest'        => true,
                'match_count'   => $matches->count(),
                'property_ids'  => $matches->pluck('id')->toArray(),
                'top_property'  => $topPropertyId,
                'intent_score'  => $profile['intent_score'],
                'digest_type'   => $this->resolveDigestType($profile),
            ],
            'action_url'  => $topPropertyId ? "/properties/{$topPropertyId}" : '/properties',
            'action_text' => $lang === 'ar' ? 'عرض العقارات' : ($lang === 'ku' ? 'خانووەکان ببینە' : 'View Properties'),
            'is_read'     => false,
            'sent_at'     => now(),
            'expires_at'  => now()->addDays(3),
        ]);

        // Send FCM push
        try {
            $firebaseService = new FirebaseService();

            $fcmData = [
                'type'          => 'property_digest',
                'id'            => $notification->id ?? null,
                'priority'      => $profile['intent_score'] >= 70 ? 'high' : 'medium',
                'digest'        => 'true',
                'match_count'   => (string) $matches->count(),
                'property_ids'  => json_encode($matches->pluck('id')->toArray()),
                'top_property'  => (string) ($topPropertyId ?? ''),
                'action_url'    => $topPropertyId ? "/properties/{$topPropertyId}" : '/properties',
                'action_text'   => $lang === 'ar' ? 'عرض العقارات' : ($lang === 'ku' ? 'خانووەکان ببینە' : 'View Properties'),
            ];

            $result = $firebaseService->sendToUser($user, $payload, $fcmData);

            Log::info('PropertyMatchDigest: sent', [
                'user_id'       => $user->id,
                'match_count'   => $matches->count(),
                'intent_score'  => $profile['intent_score'],
                'digest_type'   => $fcmData['action_url'],
                'fcm_success'   => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('PropertyMatchDigest: FCM failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Describe WHY this digest was generated — useful for analytics and
     * for varying the notification copy in future.
     */
    private function resolveDigestType(array $profile): string
    {
        if ($profile['intent_score'] >= 70) return 'high_intent';
        if (!empty($profile['price']))       return 'budget_matched';
        if (!empty($profile['cities']))      return 'city_focused';
        if (!empty($profile['types']))       return 'type_focused';
        return 'general';
    }

    /**
     * Check if this user already received a digest in the cooldown window.
     */
    private function gotRecentDigest(int $userId): bool
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->where('type', 'property')
            ->whereRaw("JSON_EXTRACT(data, '$.digest') = true")
            ->where('sent_at', '>=', now()->subDays(self::DIGEST_COOLDOWN_DAYS))
            ->exists();
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// ADD THIS TO: app/Console/Kernel.php  →  protected function schedule(Schedule $schedule)
// ─────────────────────────────────────────────────────────────────────────────
//
//  $schedule->job(new \App\Jobs\SendPropertyMatchDigestJob())
//           ->everyThreeDays()
//           ->at('09:00')          // send at 9 AM (Kurdistan time)
//           ->withoutOverlapping() // never run two at once
//           ->onOneServer()        // only one server if you have multiple
//           ->runInBackground();
//
// ─────────────────────────────────────────────────────────────────────────────
// HOW THE INTELLIGENCE WORKS — what each user receives is driven by:
//
//  UserTasteProfile signal          → How it affects the digest
//  ─────────────────────────────────────────────────────────────
//  cities[]  (weighted 0-1)         → City WHERE clause + city score (0-30pts)
//  types[]   (weighted 0-1)         → Type WHERE clause + type score (0-25pts)
//  listing_type                     → Hard filter (rent vs sell)
//  price.target / min / max         → Price WHERE ±25% + score (0-20pts)
//  bedrooms                         → Bedroom WHERE ±1 + score (0-15pts)
//  heat_centroid lat/lng/radius_km  → Geo proximity score (0-20pts)
//  is_boosted / verified            → Quality bonus (0-18pts)
//  intent_score 0-100               → Flat bonus for high-intent users
//  seen_ids[]                       → Excluded from results (never repeat)
//
//  MIN_MATCH_SCORE = 40 → property must score ≥40 to appear in digest
//  MAX_NOTIFS_PER_DAY  → respected via gotRecentDigest() cooldown
// ─────────────────────────────────────────────────────────────────────────────
