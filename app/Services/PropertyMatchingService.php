<?php

namespace App\Services\Property;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PropertyMatchingService
 *
 * Matches newly created properties against users' saved filter signals
 * stored in user_property_interactions (property_id = 'filter_signal').
 *
 * No new tables needed — reads from the existing interactions table
 * that UserBehaviorService and SmartStripService already write to.
 */
class PropertyMatchingService
{
    // Minimum relevance score to qualify for notification
    private const MIN_SCORE = 50;

    // Max property notifications per user per day (anti-spam)
    private const MAX_NOTIFS_PER_DAY = 2;

    // How far back to look for filter signals (days)
    private const SIGNAL_LOOKBACK = 60;

    // ──────────────────────────────────────────────────────────────────────────
    //  PUBLIC ENTRY POINT
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Find all users whose saved filter signal matches the given property.
     * Returns a collection sorted by match score descending.
     *
     * Each item: ['user_id' => int, 'meta' => array, 'score' => int]
     */
    public function findMatchesForProperty(Property $property): Collection
    {
        $addressDetails = is_array($property->address_details)
            ? $property->address_details
            : json_decode($property->address_details, true);

        $cityEn      = strtolower(trim($addressDetails['city']['en'] ?? ''));
        $priceUsd    = is_array($property->price) ? (float) ($property->price['usd'] ?? 0) : 0;
        $category    = strtolower($property->type['category'] ?? '');
        $listingType = $property->listing_type ?? '';
        $bedrooms    = (int) ($property->rooms['bedroom']['count'] ?? 0);

        // Pull the most recent filter_signal per user from the interactions table.
        // This is exactly what SmartStripService reads — no extra table needed.
        $signals = DB::table('user_property_interactions')
            ->where('interaction_type', 'filter_applied')
            ->where('property_id', 'filter_signal')
            ->where('created_at', '>=', now()->subDays(self::SIGNAL_LOOKBACK))
            ->whereNotNull('user_id')
            ->whereNotNull('metadata')
            ->orderByDesc('created_at')
            ->get()
            ->unique('user_id'); // keep only the most recent signal per user

        $matches = collect();

        foreach ($signals as $signal) {
            $meta = is_string($signal->metadata)
                ? json_decode($signal->metadata, true)
                : (array) $signal->metadata;

            if (empty($meta)) continue;

            // Skip signals where user opened the filter modal but changed nothing
            if ($this->isJunkSignal($meta)) continue;

            $score = $this->score(
                $meta,
                $cityEn,
                $priceUsd,
                $category,
                $listingType,
                $bedrooms
            );

            if ($score < self::MIN_SCORE) continue;

            // Frequency cap: skip if this user already got 2 property notifications today
            if ($this->hitsDailyLimit((int) $signal->user_id)) continue;

            $matches->push([
                'user_id' => (int) $signal->user_id,
                'meta'    => $meta,
                'score'   => $score,
            ]);
        }

        return $matches->sortByDesc('score')->values();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  SCORING
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Score how well a property matches a user's saved filter signal.
     * Returns 0 on hard exclusion (wrong city / listing type / property type).
     */
    private function score(
        array  $meta,
        string $cityEn,
        float  $priceUsd,
        string $category,
        string $listingType,
        int    $bedrooms
    ): int {
        $score = 0;

        // ── City ─────────────────────────────────────────────────────────────
        $signalCity = strtolower(trim($meta['city'] ?? ''));
        if ($signalCity === '') {
            $score += 20; // no city preference → partial credit
        } elseif ($signalCity === $cityEn) {
            $score += 40; // exact city match
        } else {
            return 0; // city mismatch = hard exclude
        }

        // ── Listing type ──────────────────────────────────────────────────────
        $signalListing = strtolower(trim($meta['listing_type'] ?? ''));
        if ($signalListing === '' || $signalListing === 'all') {
            $score += 10; // no preference
        } elseif ($signalListing === $listingType) {
            $score += 20; // exact match
        } else {
            return 0; // wrong listing type = hard exclude
        }

        // ── Property type ─────────────────────────────────────────────────────
        $signalType = strtolower(trim($meta['property_type'] ?? ''));
        if ($signalType === '' || $signalType === 'all') {
            $score += 10; // no preference
        } elseif ($signalType === $category) {
            $score += 20; // exact match
        } else {
            return 0; // wrong type = hard exclude
        }

        // ── Price range ───────────────────────────────────────────────────────
        if ($priceUsd > 0) {
            // Support both key naming conventions from different signal sources
            $min = (float) ($meta['min_price_usd'] ?? $meta['min_price'] ?? 0);
            $max = (float) ($meta['max_price_usd'] ?? $meta['max_price'] ?? 0);

            if ($max > 0) {
                $minOk = $min <= 0 || $priceUsd >= $min;
                $maxOk = $priceUsd <= $max;

                if ($minOk && $maxOk) {
                    $score += 30; // within budget
                } elseif ($minOk && $priceUsd <= $max * 1.15) {
                    $score += 10; // within 15% over budget — still worth notifying
                } else {
                    return 0; // way outside budget
                }
            }
        }

        // ── Bedrooms ──────────────────────────────────────────────────────────
        $signalBeds = (int) ($meta['bedrooms'] ?? 0);
        if ($signalBeds > 0 && $bedrooms > 0) {
            if ($bedrooms >= $signalBeds) {
                $score += 10; // meets or exceeds bedroom requirement
            } else {
                $score -= 20; // fewer bedrooms than wanted — penalise but don't exclude
            }
        }

        return max(0, $score);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Returns true if the filter signal carries no real intent
     * (user opened the modal but didn't actually set any filters).
     */
    private function isJunkSignal(array $meta): bool
    {
        $junk = ['', 'all', '0', 'any', 'none'];

        $hasListing  = !in_array(strtolower($meta['listing_type']  ?? ''), $junk, true);
        $hasType     = !in_array(strtolower($meta['property_type'] ?? ''), $junk, true);
        $hasCity     = !empty(trim($meta['city'] ?? ''));
        $hasMaxPrice = (float) ($meta['max_price_usd'] ?? $meta['max_price'] ?? 0) > 0;
        $hasBedrooms = (int) ($meta['bedrooms'] ?? 0) > 0;

        return !$hasListing && !$hasType && !$hasCity && !$hasMaxPrice && !$hasBedrooms;
    }

    /**
     * Returns true if this user already received MAX_NOTIFS_PER_DAY
     * property-type notifications today (prevents spam).
     */
    private function hitsDailyLimit(int $userId): bool
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->where('type', 'property')
            ->whereDate('sent_at', today())
            ->count() >= self::MAX_NOTIFS_PER_DAY;
    }
}
