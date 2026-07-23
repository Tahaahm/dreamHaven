<?php

namespace App\Services\Concerns;

use App\Models\Property;
use App\Models\User;
use App\Models\UserPropertyInteraction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Records raw user behavior (views, search clicks/impressions, the
 * mortgage-calculator signal, contact-intent pings) and reads back the
 * lightweight "signals" other traits use for personalization. Extracted
 * from PropertyInteractionService.php as-is — no behavior changed, only
 * relocated. PHP compiles trait methods directly into the class that uses
 * them, so PropertyInteractionService's public API (and every place that
 * type-hints or injects it) is completely unaffected by this split.
 */
trait TracksUserSignals
{
    // ══════════════════════════════════════════════════════════════════════════
    //  TRACK VIEW
    // ══════════════════════════════════════════════════════════════════════════
    public function trackView(string $userId, string $propertyId, array $metadata = []): bool
    {
        try {
            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'property_id'      => $propertyId,
                'interaction_type' => 'view',
                'metadata'         => array_merge($metadata, [
                    'timestamp' => now()->toDateTimeString(),
                    'ip'        => request()->ip(),
                ]),
                'created_at' => now(),
            ]);
            $this->updateRecentlyViewed($userId, $propertyId);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to track view', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function updateRecentlyViewed(string $userId, string $propertyId): void
    {
        $user = User::find($userId);
        if (!$user) return;
        $rv = $user->recently_viewed_properties ?? [];
        $rv = array_filter($rv, fn($id) => $id !== $propertyId);
        array_unshift($rv, $propertyId);
        $rv = array_slice($rv, 0, 50);
        $user->update(['recently_viewed_properties' => $rv, 'last_activity_at' => now()]);
    }

    public function getRecentlyViewed(string $userId, int $limit = 20): Collection
    {
        $user = User::find($userId);
        if (!$user || empty($user->recently_viewed_properties)) return collect();
        $ids   = array_slice($user->recently_viewed_properties, 0, $limit);
        $props = Property::whereIn('id', $ids)
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending'])->get();
        return $props->sortBy(fn($p) => array_search($p->id, $ids))->values();
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  TRACK SEARCH CLICK
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearchClick(
        string $userId,
        string $propertyId,
        string $searchQuery    = '',
        int    $resultPosition = 0,
        array  $activeFilters  = []
    ): bool {
        try {
            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'property_id'      => $propertyId,
                'interaction_type' => 'search_click',
                'metadata'         => json_encode([
                    'query'           => $searchQuery,
                    'result_position' => $resultPosition,
                    'active_filters'  => $activeFilters,
                    'timestamp'       => now()->toDateTimeString(),
                    'ip'              => request()->ip(),
                ]),
                'created_at' => now(),
            ]);
            Cache::forget("property_pop_score_{$propertyId}");
            Cache::forget('popular_properties_global');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to track search click', [
                'user_id'     => $userId,
                'property_id' => $propertyId,
                'error'       => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  TRACK SEARCH IMPRESSIONS
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearchImpressions(
        string $userId,
        array  $propertyIds,
        string $searchQuery    = '',
        array  $activeFilters  = []
    ): void {
        try {
            if (empty($propertyIds)) return;
            $cacheKey = 'search_imp_' . $userId . '_' . md5($searchQuery . implode(',', $propertyIds));
            if (Cache::has($cacheKey)) return;
            Cache::put($cacheKey, true, 300);

            $now        = now();
            $insertData = [];
            foreach ($propertyIds as $position => $pid) {
                $insertData[] = [
                    'user_id'          => str_starts_with($userId, 'guest_') ? null : $userId,
                    'session_id'       => str_starts_with($userId, 'guest_')
                        ? str_replace('guest_', '', $userId)
                        : session()->getId(),
                    'property_id'      => $pid,
                    'interaction_type' => 'search_impression',
                    'metadata'         => json_encode([
                        'query'          => $searchQuery,
                        'position'       => $position,
                        'active_filters' => $activeFilters,
                    ]),
                    'created_at' => $now,
                ];
            }
            UserPropertyInteraction::insert($insertData);
        } catch (\Exception $e) {
            Log::warning('Failed to track search impressions', ['error' => $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  CALCULATOR SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    public function storeCalculatorSignal(
        string $userId,
        float  $targetPriceUsd,
        float  $savedSoFarUsd  = 0,
        float  $monthlyUsd     = 0,
        int    $targetYears    = 0,
        string $mode           = 'how_long'
    ): void {
        try {
            if ($targetPriceUsd <= 0) return;
            UserPropertyInteraction::updateOrCreate(
                ['user_id' => $userId, 'property_id' => 'calculator_signal', 'interaction_type' => 'calculator_search'],
                [
                    'metadata' => json_encode([
                        'target_price_usd' => $targetPriceUsd,
                        'saved_so_far_usd' => $savedSoFarUsd,
                        'monthly_usd'      => $monthlyUsd,
                        'target_years'     => $targetYears,
                        'mode'             => $mode,
                        'budget_min_usd'   => round($targetPriceUsd * 0.80),
                        'budget_max_usd'   => round($targetPriceUsd * 1.20),
                        'signal_strength'  => $this->calcSignalStrength($targetPriceUsd, $savedSoFarUsd, $monthlyUsd, $targetYears),
                        'updated_at'       => now()->toISOString(),
                    ]),
                    'created_at' => now(),
                ]
            );
            Cache::forget("personalized_recs_{$userId}");
        } catch (\Throwable $e) {
            Log::warning('Calculator signal failed', ['error' => $e->getMessage()]);
        }
    }

    private function calcSignalStrength(float $price, float $saved, float $monthly, int $years): int
    {
        $score = 20;
        if ($monthly > 0) {
            $score += 20;
            if ($price > 0 && ($monthly * 12 / $price) >= 0.05) $score += 20;
        }
        if ($years > 0)  $score += 10;
        if ($saved > 0) {
            $score += 20;
            if ($price > 0 && ($saved / $price) >= 0.10) $score += 10;
        }
        return min($score, 100);
    }

    private function getCalculatorSignal(string $userId): ?array
    {
        try {
            $row = UserPropertyInteraction::where('user_id', $userId)
                ->where('interaction_type', 'calculator_search')
                ->where('property_id', 'calculator_signal')
                ->where('created_at', '>=', now()->subDays(90))
                ->latest()->first();
            if (!$row || !$row->metadata) return null;
            $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);
            return $meta ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  FILTER SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    private function getFilterSignal(string $userId): ?array
    {
        try {
            $row = UserPropertyInteraction::where('user_id', $userId)
                ->where('interaction_type', 'filter_applied')
                ->where('property_id', 'filter_signal')
                ->where('created_at', '>=', now()->subDays(60))
                ->latest()->first();
            if (!$row || !$row->metadata) return null;
            $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);
            return $meta ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  COMPARE SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    private function getComparedProperties(string $userId): Collection
    {
        try {
            $ids = UserPropertyInteraction::where('user_id', $userId)
                ->where('interaction_type', 'compare')
                ->where('created_at', '>=', now()->subDays(30))
                ->whereNotIn('property_id', ['search_signal', 'filter_signal', 'calculator_signal'])
                ->pluck('property_id')->unique()->values();
            if ($ids->isEmpty()) return collect();
            return Property::whereIn('id', $ids)->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  SEARCH SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    private function getLatestSearchSignal(string $userId): ?array
    {
        try {
            $row = UserPropertyInteraction::where('user_id', $userId)
                ->where('interaction_type', 'search_query_latest')
                ->where('property_id', 'search_signal_latest')
                ->where('created_at', '>=', now()->subDays(7))
                ->latest()->first();
            if (!$row || !$row->metadata) return null;
            $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);
            return $meta ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  IMPRESSIONS TRACKER
    // ══════════════════════════════════════════════════════════════════════════
    public function trackImpressions(string $userId, $properties, string $sourceEndpoint, array $extra = []): void
    {
        if (
            empty($properties) ||
            (is_object($properties) && method_exists($properties, 'isEmpty') && $properties->isEmpty())
        )
            return;
        try {
            $propertyIds = collect($properties)->pluck('id')->sort()->implode(',');
            $cacheKey    = 'impressions_' . $userId . '_' . $sourceEndpoint . '_' . md5($propertyIds);
            if (Cache::has($cacheKey)) return;
            Cache::put($cacheKey, true, 300);

            $timestamp  = now();
            $insertData = [];
            $ip         = request()->ip();
            $isGuest    = str_starts_with($userId, 'guest_');
            $sessionId  = $isGuest ? str_replace('guest_', '', $userId) : session()->getId();

            foreach ($properties as $property) {
                $insertData[] = [
                    'user_id'          => $isGuest ? null : $userId,
                    'session_id'       => $sessionId,
                    'property_id'      => $property->id,
                    'interaction_type' => 'impression',
                    'metadata'         => json_encode(array_merge([
                        'source_endpoint' => $sourceEndpoint,
                        'ip'              => $ip,
                        'is_guest'        => $isGuest,
                    ], $extra)),
                    'created_at' => $timestamp,
                ];
            }
            UserPropertyInteraction::insert($insertData);
        } catch (\Exception $e) {
            Log::error('Failed to track impressions', ['error' => $e->getMessage()]);
        }
    }

    public function trackContactIntent(
        string  $userId,
        string  $propertyId,
        string  $method      = 'whatsapp',
        ?string $propertyType = null,
        ?string $city         = null,
        ?float  $priceUsd     = null,
    ): void {
        try {
            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'property_id'      => $propertyId,
                'interaction_type' => 'contact_intent',
                'metadata'         => [
                    'contact_method' => $method,
                    'property_type'  => $propertyType,
                    'city'           => $city,
                    'price_usd'      => $priceUsd,
                    'weight'         => 6.0, // matches SIGNAL_WEIGHTS in UserTasteProfile
                    'timestamp'      => now()->toISOString(),
                ],
                'created_at' => now(),
            ]);
            // Bust taste profile cache so next recommendation reflects this signal
            Cache::forget("taste_profile_{$userId}");
        } catch (\Throwable $e) {
            Log::warning('trackContactIntent failed', ['error' => $e->getMessage()]);
        }
    }
}
