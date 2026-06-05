<?php

namespace App\Services\Intelligence;

use App\Models\Property;
use Illuminate\Support\Collection;

/**
 * FeedBrain (v3)
 * --------------
 *  The single scoring brain. Given a candidate property and a UserTasteProfile,
 *  returns a 0..100 match score with a transparent breakdown of why.
 *
 *  V3 — scores against EVERY taste dimension:
 *
 *  RELEVANCE PILLAR (0..100, then capped):
 *    City match           ──  up to 30
 *    Type match           ──  up to 25
 *    Listing type match   ──     20
 *    Price band           ──     15
 *    Bedrooms             ──     10
 *    Amenities (NEW)      ──  up to 15   ← pool / gym / garden / parking...
 *    Features  (NEW)      ──  up to 10   ← balcony / view / corner unit...
 *    Furnished (NEW)      ──      5
 *    Area band (NEW)      ──     10
 *    CF match  (NEW)      ──  up to 20   ← "people like you also loved this"
 *
 *  QUALITY PILLAR:
 *    boosted + verified + favourites + views + rating + photos
 *    NEW v3: −25 penalty if property is in dismissed_ids (impression fatigue)
 *
 *  FRESHNESS PILLAR — recency curve.
 *  EXPLORATION  PILLAR — deterministic per-property jitter (daily-rotating salt).
 *
 *  Blend shifts with intent_score: high intent → more relevance & less explore.
 *
 *  DIVERSITY (in rank()):
 *    • city / type caps (preserved)
 *    • owner cap — max 2 per agent/office (NEW v3, stops feed feeling spammy)
 */
class FeedBrain
{
    private const OWNER_CAP = 2;

    public function scoreProperty(Property $property, array $profile, int $salt = 0): array
    {
        $relevance = $this->relevanceScore($property, $profile);
        $quality   = $this->qualityScore($property, $profile);
        $freshness = $this->freshnessScore($property);
        $explore   = $this->explorationScore($property, $salt);

        $w = $this->weights((int) ($profile['intent_score'] ?? 0));

        $score = $relevance * $w['relevance']
            + $quality   * $w['quality']
            + $freshness * $w['freshness']
            + $explore   * $w['exploration'];

        return [
            'score'     => round($score, 2),
            'relevance' => round($relevance, 1),
            'quality'   => round($quality, 1),
            'freshness' => round($freshness, 1),
            'reasons'   => $this->reasons($property, $profile),
        ];
    }

    /**
     * Score & rank a whole candidate collection. Returns sorted, each
     * property tagged with ->feed_score and ->feed_reasons.
     *
     * V3: now enforces OWNER diversity in addition to city / type.
     */
    public function rank(Collection $candidates, array $profile, int $limit, int $salt = 0): Collection
    {
        $scored = $candidates->map(function ($p) use ($profile, $salt) {
            $r = $this->scoreProperty($p, $profile, $salt);
            $p->feed_score     = $r['score'];
            $p->feed_relevance = $r['relevance'];
            $p->feed_reasons   = $r['reasons'];
            return $p;
        })->sortByDesc('feed_score')->values();

        // Explore/exploit: top 85% by score + a few wildcards from the rest
        // (fresh or verified) so the feed never feels dead and new stock surfaces.
        $exploitN = (int) ceil($limit * 0.85);
        $exploit  = $scored->take($exploitN);
        $poolRest = $scored->slice($exploitN);

        $exploreN = $limit - $exploit->count();
        $explore  = $poolRest
            ->filter(fn($p) => $this->isFresh($p) || ($p->verified ?? false))
            ->take($exploreN);

        if ($explore->count() < $exploreN) {
            $explore = $explore->merge($poolRest->take($exploreN - $explore->count()));
        }

        return $this->diversify($exploit->merge($explore), $limit);
    }

    // ── PILLARS ─────────────────────────────────────────────────────────────

    private function relevanceScore(Property $p, array $profile): float
    {
        if (!($profile['has_history'] ?? false)) return 0.0;

        $score = 0.0;

        // City match (0..30)
        $city = strtolower($p->address_details['city']['en'] ?? '');
        foreach ($profile['cities'] as $prefCity => $weight) {
            if (strtolower($prefCity) === $city) {
                $score += 30 * $weight;
                break;
            }
        }

        // Type match (0..25)
        $type = strtolower($p->type['category'] ?? '');
        foreach ($profile['types'] as $prefType => $weight) {
            if (strtolower($prefType) === $type) {
                $score += 25 * $weight;
                break;
            }
        }

        // Listing type match (0..20)
        if ($profile['listing_type'] && $p->listing_type === $profile['listing_type']) {
            $score += 20;
        }

        // Price band (0..15)
        $usd = (float) ($p->price['usd'] ?? 0);
        if ($profile['price'] && $usd > 0) {
            $band = $profile['price'];
            if ($usd >= $band['min'] && $usd <= $band['max']) {
                $score += 15;
            } elseif ($usd <= $band['max']) {
                $score += 8;
            }
        }

        // Bedrooms (0..10)
        if ($profile['bedrooms']) {
            $beds = (int) ($p->rooms['bedroom']['count'] ?? 0);
            if ($beds === $profile['bedrooms']) $score += 10;
            elseif (abs($beds - $profile['bedrooms']) === 1) $score += 5;
        }

        // ── V3 NEW: Amenity match (0..15) ───────────────────────────────────
        // Sum each amenity's preference weight × 5. Caps at 15.
        // A user who keeps favouriting pool-having places will see pool-having
        // places bubble up — without ever saying "I want a pool."
        if (!empty($profile['amenities']) && is_array($p->amenities)) {
            $aScore = 0.0;
            foreach ($p->amenities as $am) {
                $key = strtolower(trim((string) $am));
                if (isset($profile['amenities'][$key])) {
                    $aScore += $profile['amenities'][$key] * 5;
                }
            }
            $score += min($aScore, 15);
        }

        // ── V3 NEW: Feature match (0..10) ───────────────────────────────────
        if (!empty($profile['features']) && is_array($p->features)) {
            $fScore = 0.0;
            foreach ($p->features as $f) {
                $key = strtolower(trim((string) $f));
                if (isset($profile['features'][$key])) {
                    $fScore += $profile['features'][$key] * 4;
                }
            }
            $score += min($fScore, 10);
        }

        // ── V3 NEW: Furnished preference (0..5) ─────────────────────────────
        // Profile furnished_pref is in [-1..+1]; only count it as a real signal
        // when |pref| > 0.3 (otherwise too noisy).
        $fp = $profile['furnished_pref'] ?? null;
        if ($fp !== null && abs($fp) > 0.3) {
            if (($p->furnished && $fp > 0) || (!$p->furnished && $fp < 0)) {
                $score += 5;
            }
        }

        // ── V3 NEW: Area band match (0..10) ─────────────────────────────────
        // Same idea as price band but for sqm.
        $area = (float) ($p->area ?? 0);
        if (!empty($profile['area_band']) && $area > 0) {
            $band = $profile['area_band'];
            if ($area >= $band['min'] && $area <= $band['max']) {
                $score += 10;
            } elseif (
                $band['target'] > 0
                && abs($area - $band['target']) / $band['target'] < 0.40
            ) {
                $score += 5;
            }
        }

        // ── V3 NEW: Collaborative filtering match (0..20) ───────────────────
        // The big discovery lever. cf_matches[propId] is 0..1.
        if (!empty($profile['cf_matches']) && isset($profile['cf_matches'][$p->id])) {
            $score += $profile['cf_matches'][$p->id] * 20;
        }

        return min($score, 100);
    }

    private function qualityScore(Property $p, array $profile): float
    {
        $score = 0.0;
        if ($this->boostActive($p)) $score += 35;
        if ($p->verified)           $score += 20;
        $score += min(($p->favorites_count ?? 0) * 1.5, 15);
        $score += min(log(max($p->views ?? 0, 0) + 1, 10) * 5, 12);
        $score += min((float) ($p->rating ?? 0) * 3, 10);
        $imgs = is_array($p->images) ? count($p->images) : 0;
        if ($imgs >= 5) $score += 5;
        if (!empty($p->virtual_tour_url)) $score += 3;

        // ── V3 NEW: impression fatigue penalty ──────────────────────────────
        // We've shown this person this property a lot and they never engaged.
        // Penalise so it falls naturally in the feed (but still possible to
        // surface if nothing better qualifies — not an absolute exclusion).
        if (
            !empty($profile['dismissed_ids'])
            && in_array($p->id, $profile['dismissed_ids'], true)
        ) {
            $score -= 25;
        }

        return max(0, min($score, 100));
    }

    private function freshnessScore(Property $p): float
    {
        if (!$p->created_at) return 0.0;
        $days = $p->created_at->diffInDays(now());
        return match (true) {
            $days <= 1  => 100,
            $days <= 3  => 85,
            $days <= 7  => 70,
            $days <= 14 => 50,
            $days <= 30 => 30,
            $days <= 60 => 15,
            default     => 5,
        };
    }

    /**
     * Deterministic exploration jitter. Same property + salt always gives the
     * same value (stable within a session), but rotating salt daily reshuffles
     * the long tail so users see different good-but-not-top listings over time.
     */
    private function explorationScore(Property $p, int $salt): float
    {
        $h = crc32($p->id . ':' . $salt);
        return ($h % 1000) / 10.0; // 0..99.9
    }

    private function weights(int $intent): array
    {
        $t = max(0, min(100, $intent)) / 100;
        return [
            'relevance'   => 0.25 + 0.20 * $t,
            'quality'     => 0.30 - 0.05 * $t,
            'freshness'   => 0.25 - 0.10 * $t,
            'exploration' => 0.20 - 0.05 * $t,
        ];
    }

    // ── DIVERSITY ───────────────────────────────────────────────────────────

    /**
     * Stop the feed being 10 identical villas in one city OR 5 listings from
     * the same office. V3 adds OWNER cap.
     */
    private function diversify(Collection $ranked, int $limit): Collection
    {
        $cityMax = max(2, (int) ceil($limit * 0.45));
        $typeMax = max(2, (int) ceil($limit * 0.55));
        $cityN = $typeN = $ownerN = [];
        $picked   = collect();
        $overflow = collect();

        foreach ($ranked as $p) {
            $c = strtolower($p->address_details['city']['en'] ?? 'unknown');
            $t = strtolower($p->type['category'] ?? 'unknown');
            $o = ($p->owner_type ?? 'x') . ':' . ($p->owner_id ?? 'x');

            $cityN[$c]  = $cityN[$c]  ?? 0;
            $typeN[$t]  = $typeN[$t]  ?? 0;
            $ownerN[$o] = $ownerN[$o] ?? 0;

            if (
                $cityN[$c]  >= $cityMax
                || $typeN[$t]  >= $typeMax
                || $ownerN[$o] >= self::OWNER_CAP
            ) {
                $overflow->push($p);
                continue;
            }
            $picked->push($p);
            $cityN[$c]++;
            $typeN[$t]++;
            $ownerN[$o]++;
            if ($picked->count() >= $limit) break;
        }

        // If diversity caps blocked too many, top up from overflow.
        if ($picked->count() < $limit) {
            $picked = $picked->merge($overflow->take($limit - $picked->count()));
        }
        return $picked->values();
    }

    // ── TRANSPARENT "WHY" ────────────────────────────────────────────────────

    /**
     * Honest, specific reasons. Each carries a machine `key`, a sharp `headline`
     * (numbers / city / dates / amenities) and a warm `tone` line — Flutter
     * picks per surface.
     *
     * V3 adds: amenity-match, feature-match, furnished-match, area-match,
     *          and "similar to ones you saved" (CF) reasons. These are the
     *          ones that make users say "wow, this gets me."
     */
    private function reasons(Property $p, array $profile): array
    {
        $out = [];

        // ── Strongest signal first: explicit budget match ────────────────────
        $usd = (float) ($p->price['usd'] ?? 0);
        if (
            $profile['budget'] && $usd > 0
            && $usd >= ($profile['budget']['budget_min_usd'] ?? 0)
            && $usd <= ($profile['budget']['budget_max_usd'] ?? 0)
        ) {
            $out[] = [
                'key'      => 'in_budget',
                'headline' => 'Within your $' . $this->k($profile['budget']['budget_min_usd'])
                    . '–$' . $this->k($profile['budget']['budget_max_usd']) . ' budget',
                'tone'     => 'Right in your price range',
            ];
        }

        // ── V3 NEW: collaborative filtering — the biggest "wow" reason ──────
        if (!empty($profile['cf_matches']) && isset($profile['cf_matches'][$p->id])) {
            $out[] = [
                'key'      => 'similar_taste',
                'headline' => 'People who liked your saved properties also love this',
                'tone'     => 'Similar to ones you saved',
            ];
        }

        // City
        $city = $p->address_details['city']['en'] ?? null;
        if ($city && isset($profile['cities'][$city])) {
            $out[] = [
                'key'      => 'your_area',
                'headline' => "In {$city}, where you've been looking",
                'tone'     => "More in {$city}",
            ];
        }

        // Type
        $type = strtolower($p->type['category'] ?? '');
        foreach (($profile['types'] ?? []) as $pt => $w) {
            if (strtolower($pt) === $type) {
                $out[] = [
                    'key'      => 'your_type',
                    'headline' => 'Matches the ' . $type . 's you favour',
                    'tone'     => 'Your kind of place',
                ];
                break;
            }
        }

        // ── V3 NEW: amenity match — call out the SPECIFIC amenities ─────────
        // "Has the pool you usually look for" is much sharper than "Picked for you".
        if (!empty($profile['amenities']) && is_array($p->amenities)) {
            $matched = [];
            foreach ($p->amenities as $am) {
                $key = strtolower(trim((string) $am));
                if (isset($profile['amenities'][$key]) && $profile['amenities'][$key] >= 0.5) {
                    $matched[] = $key;
                }
            }
            if (!empty($matched)) {
                $matched = array_slice(array_unique($matched), 0, 2);
                $list    = implode(' & ', array_map('ucfirst', $matched));
                $out[]   = [
                    'key'      => 'amenity_match',
                    'headline' => "Has the {$list} you usually look for",
                    'tone'     => "Has " . strtolower($list),
                ];
            }
        }

        // ── V3 NEW: furnished preference ────────────────────────────────────
        $fp = $profile['furnished_pref'] ?? null;
        if ($fp !== null && abs($fp) > 0.3) {
            if ($p->furnished && $fp > 0) {
                $out[] = [
                    'key'      => 'furnished_match',
                    'headline' => 'Furnished, like the ones you save',
                    'tone'     => 'Furnished',
                ];
            } elseif (!$p->furnished && $fp < 0) {
                $out[] = [
                    'key'      => 'unfurnished_match',
                    'headline' => 'Unfurnished — your preference',
                    'tone'     => 'Unfurnished',
                ];
            }
        }

        // ── V3 NEW: area match ───────────────────────────────────────────────
        $area = (float) ($p->area ?? 0);
        if (!empty($profile['area_band']) && $area > 0) {
            $band = $profile['area_band'];
            if ($area >= $band['min'] && $area <= $band['max']) {
                $out[] = [
                    'key'      => 'area_match',
                    'headline' => round($area) . ' sqm — your preferred size',
                    'tone'     => round($area) . ' sqm',
                ];
            }
        }

        // Quality / status signals
        if ($this->boostActive($p)) {
            $out[] = ['key' => 'promoted', 'headline' => 'Promoted listing', 'tone' => 'Featured'];
        }
        if ($p->verified) {
            $out[] = ['key' => 'verified', 'headline' => 'Verified by Dream Mulk', 'tone' => 'Verified'];
        }
        if ($this->isFresh($p)) {
            $days  = (int) $p->created_at->diffInDays(now());
            $label = $days <= 1 ? 'Listed today' : "New — listed {$days} days ago";
            $out[] = ['key' => 'new', 'headline' => $label, 'tone' => 'Just listed'];
        }
        if (($p->favorites_count ?? 0) > 10) {
            $out[] = [
                'key'      => 'popular',
                'headline' => $p->favorites_count . ' people saved this',
                'tone'     => 'Popular',
            ];
        }
        if (($p->views ?? 0) > 100) {
            $out[] = ['key' => 'trending', 'headline' => 'Trending — lots of views', 'tone' => 'Trending'];
        }

        if (empty($out)) {
            $out[] = ['key' => 'quality', 'headline' => 'Picked for you', 'tone' => 'Picked for you'];
        }
        return $out;
    }

    // ── small helpers ────────────────────────────────────────────────────────

    private function boostActive(Property $p): bool
    {
        if (!$p->is_boosted) return false;
        if ($p->boost_start_date && $p->boost_start_date > now()) return false;
        if ($p->boost_end_date && $p->boost_end_date < now())     return false;
        return true;
    }

    private function isFresh(Property $p): bool
    {
        return $p->created_at && $p->created_at->diffInDays(now()) <= 7;
    }

    private function k($n): string
    {
        $n = (float) $n;
        return $n >= 1000 ? round($n / 1000) . 'K' : (string) round($n);
    }
}
