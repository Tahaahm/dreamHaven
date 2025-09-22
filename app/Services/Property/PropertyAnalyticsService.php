<?php

namespace App\Services\Property;

use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyAnalyticsService
{
    /**
     * Increment property views and update analytics
     */
    public function incrementViews(Property $property): void
    {
        try {
            $property->increment('views');

            // Update view analytics
            $viewAnalytics = $property->view_analytics ?? [
                'unique_views' => 0,
                'returning_views' => 0,
                'average_time_on_listing' => 0,
                'bounce_rate' => 0,
            ];

            $viewAnalytics['unique_views'] = ($viewAnalytics['unique_views'] ?? 0) + 1;

            $property->update(['view_analytics' => $viewAnalytics]);
        } catch (\Exception $e) {
            Log::error('Failed to increment views', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get overall property statistics
     */
    public function getOverallStatistics(): array
    {
        try {
            return [
                'total_properties' => Property::count(),
                'active_properties' => Property::where('is_active', true)->count(),
                'published_properties' => Property::where('published', true)->count(),
                'verified_properties' => Property::where('verified', true)->count(),
                'boosted_properties' => Property::where('is_boosted', true)->count(),

                // By listing type
                'for_rent' => Property::where('listing_type', 'rent')->count(),
                'for_sale' => Property::where('listing_type', 'sell')->count(),

                // By status
                'available' => Property::where('status', 'available')->count(),
                'sold' => Property::where('status', 'sold')->count(),
                'rented' => Property::where('status', 'rented')->count(),
                'pending' => Property::where('status', 'pending')->count(),

                // Analytics totals
                'total_views' => Property::sum('views'),
                'total_favorites' => Property::sum('favorites_count'),
                'average_rating' => Property::where('rating', '>', 0)->avg('rating'),

                // Time-based statistics
                'properties_this_month' => Property::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count(),
                'properties_this_week' => Property::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'properties_today' => Property::whereDate('created_at', today())->count(),

                // Price analytics
                'average_price_usd' => Property::avg(DB::raw("JSON_EXTRACT(price, '$.usd')")),
                'average_price_iqd' => Property::avg(DB::raw("JSON_EXTRACT(price, '$.iqd')")),
                'price_ranges' => $this->getPriceRangeDistribution(),

                // Property types distribution
                'by_type' => $this->getPropertyTypeDistribution(),

                // Location analytics
                'by_city' => $this->getCityDistribution(),

                // Utilities statistics
                'with_electricity' => Property::where('electricity', true)->count(),
                'with_water' => Property::where('water', true)->count(),
                'with_internet' => Property::where('internet', true)->count(),

                // Furnished statistics
                'furnished' => Property::where('furnished', true)->count(),
                'unfurnished' => Property::where('furnished', false)->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get overall statistics', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get property performance analytics for a specific property
     */
    public function getPropertyAnalytics(Property $property): array
    {
        return [
            'basic_stats' => [
                'views' => $property->views,
                'favorites_count' => $property->favorites_count,
                'rating' => $property->rating,
                'days_online' => $property->created_at->diffInDays(now()),
                'last_updated' => $property->updated_at,
            ],
            'view_analytics' => $property->view_analytics ?? [],
            'favorites_analytics' => $property->favorites_analytics ?? [],
            'performance_score' => $this->calculatePerformanceScore($property),
            'recommendations' => $this->getPerformanceRecommendations($property),
            'comparison' => $this->compareWithSimilarProperties($property),
        ];
    }

    /**
     * Get trending statistics over time
     */
    public function getTrendingData(int $days = 30): array
    {
        return [
            'property_creation_trend' => $this->getCreationTrend($days),
            'views_trend' => $this->getViewsTrend($days),
            'price_trend' => $this->getPriceTrend($days),
            'engagement_trend' => $this->getEngagementTrend($days),
        ];
    }

    /**
     * Get dashboard analytics for admin/agents
     */
    public function getDashboardAnalytics(string $timeframe = 'month'): array
    {
        $startDate = match ($timeframe) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };

        return [
            'overview' => [
                'total_properties' => Property::count(),
                'new_properties' => Property::where('created_at', '>=', $startDate)->count(),
                'pending_verification' => Property::where('verified', false)->where('published', true)->count(),
                'active_listings' => Property::where('is_active', true)->where('published', true)->count(),
            ],
            'performance' => [
                'total_views' => Property::where('updated_at', '>=', $startDate)->sum('views'),
                'total_favorites' => Property::where('updated_at', '>=', $startDate)->sum('favorites_count'),
                'average_rating' => Property::where('rating', '>', 0)->avg('rating'),
                'conversion_rate' => $this->calculateConversionRate($startDate),
            ],
            'top_performers' => [
                'most_viewed' => Property::orderBy('views', 'desc')->limit(5)
                    ->get(['id', 'name', 'views'])->toArray(),
                'most_favorited' => Property::orderBy('favorites_count', 'desc')->limit(5)
                    ->get(['id', 'name', 'favorites_count'])->toArray(),
                'highest_rated' => Property::where('rating', '>', 0)->orderBy('rating', 'desc')->limit(5)
                    ->get(['id', 'name', 'rating'])->toArray(),
            ],
            'alerts' => $this->getPerformanceAlerts(),
        ];
    }

    /**
     * Calculate performance score for a property
     */
    public function calculatePerformanceScore(Property $property): array
    {
        // Views score (0-30 points)
        $viewsScore = min(($property->views / 100) * 30, 30);

        // Favorites score (0-25 points)
        $favoritesScore = min(($property->favorites_count / 50) * 25, 25);

        // Rating score (0-25 points)
        $ratingScore = ($property->rating / 5) * 25;

        // Verification bonus (0-10 points)
        $verificationScore = $property->verified ? 10 : 0;

        // Age factor (newer properties get slight boost)
        $ageDays = $property->created_at->diffInDays(now());
        $ageScore = max(10 - ($ageDays / 30), 0);

        $totalScore = $viewsScore + $favoritesScore + $ratingScore + $verificationScore + $ageScore;

        return [
            'total_score' => round($totalScore, 1),
            'grade' => $this->getPerformanceGrade($totalScore),
            'breakdown' => [
                'views' => round($viewsScore, 1),
                'favorites' => round($favoritesScore, 1),
                'rating' => round($ratingScore, 1),
                'verification' => $verificationScore,
                'age_factor' => round($ageScore, 1),
            ],
            'percentile' => $this->getPerformancePercentile($property, $totalScore),
        ];
    }

    // Private helper methods

    private function getPriceRangeDistribution(): array
    {
        return [
            'under_50k' => Property::whereRaw("JSON_EXTRACT(price, '$.usd') < 50000")->count(),
            '50k_100k' => Property::whereRaw("JSON_EXTRACT(price, '$.usd') BETWEEN 50000 AND 100000")->count(),
            '100k_200k' => Property::whereRaw("JSON_EXTRACT(price, '$.usd') BETWEEN 100000 AND 200000")->count(),
            '200k_500k' => Property::whereRaw("JSON_EXTRACT(price, '$.usd') BETWEEN 200000 AND 500000")->count(),
            'over_500k' => Property::whereRaw("JSON_EXTRACT(price, '$.usd') > 500000")->count(),
        ];
    }

    private function getPropertyTypeDistribution(): array
    {
        return Property::select(
            DB::raw("JSON_EXTRACT(type, '$.category') as property_type"),
            DB::raw('COUNT(*) as count')
        )->groupBy('property_type')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->pluck('count', 'property_type')
            ->toArray();
    }

    private function getCityDistribution(): array
    {
        return Property::select(
            DB::raw("JSON_EXTRACT(address_details, '$.city.en') as city"),
            DB::raw('COUNT(*) as count')
        )->whereRaw("JSON_EXTRACT(address_details, '$.city.en') IS NOT NULL")
            ->groupBy('city')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->pluck('count', 'city')
            ->toArray();
    }

    private function getCreationTrend(int $days): array
    {
        return Property::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
    }

    private function getViewsTrend(int $days): array
    {
        return Property::selectRaw('DATE(updated_at) as date, SUM(views) as total_views')
            ->where('updated_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total_views', 'date')
            ->toArray();
    }

    private function getPriceTrend(int $days): array
    {
        return Property::selectRaw('DATE(created_at) as date, AVG(JSON_EXTRACT(price, "$.usd")) as avg_price')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'avg_price' => round($item->avg_price, 2)
                ];
            })
            ->pluck('avg_price', 'date')
            ->toArray();
    }

    private function getEngagementTrend(int $days): array
    {
        return Property::selectRaw('DATE(updated_at) as date, AVG(views + favorites_count * 2) as engagement_score')
            ->where('updated_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('engagement_score', 'date')
            ->toArray();
    }

    private function calculateConversionRate(\DateTime $startDate): float
    {
        $totalViews = Property::where('updated_at', '>=', $startDate)->sum('views');
        $totalFavorites = Property::where('updated_at', '>=', $startDate)->sum('favorites_count');

        if ($totalViews == 0) {
            return 0;
        }

        return round(($totalFavorites / $totalViews) * 100, 2);
    }

    private function getPerformanceAlerts(): array
    {
        $alerts = [];

        // Low performance properties
        $lowPerformance = Property::where('views', '<', 5)
            ->where('created_at', '<', now()->subDays(14))
            ->where('is_active', true)
            ->count();

        if ($lowPerformance > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$lowPerformance} properties have very low views after 2 weeks",
                'count' => $lowPerformance
            ];
        }

        // Unverified published properties
        $unverified = Property::where('verified', false)
            ->where('published', true)
            ->count();

        if ($unverified > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "{$unverified} published properties need verification",
                'count' => $unverified
            ];
        }

        // Expired boosts
        $expiredBoosts = Property::where('is_boosted', true)
            ->where('boost_end_date', '<', now())
            ->count();

        if ($expiredBoosts > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$expiredBoosts} properties have expired boosts",
                'count' => $expiredBoosts
            ];
        }

        return $alerts;
    }

    private function getPerformanceGrade(float $score): string
    {
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }

    private function getPerformancePercentile(Property $property, float $score): int
    {
        $lowerPerforming = Property::where('id', '!=', $property->id)
            ->where(function ($query) use ($score) {
                $query->whereRaw('(views/100*30 + favorites_count/50*25 + rating/5*25) < ?', [$score]);
            })->count();

        $totalProperties = Property::where('id', '!=', $property->id)->count();

        if ($totalProperties == 0) {
            return 50;
        }

        return min(100, max(1, round(($lowerPerforming / $totalProperties) * 100)));
    }

    private function getPerformanceRecommendations(Property $property): array
    {
        $recommendations = [];

        if ($property->views < 10) {
            $recommendations[] = [
                'type' => 'visibility',
                'priority' => 'high',
                'message' => 'Add more high-quality images and improve description to increase views',
                'action' => 'improve_listing'
            ];
        }

        if ($property->favorites_count < 2 && $property->views > 50) {
            $recommendations[] = [
                'type' => 'engagement',
                'priority' => 'medium',
                'message' => 'Low favorites rate suggests pricing or features may need adjustment',
                'action' => 'review_pricing'
            ];
        }

        if (!$property->verified) {
            $recommendations[] = [
                'type' => 'verification',
                'priority' => 'high',
                'message' => 'Get property verified to increase trust and visibility',
                'action' => 'verify_property'
            ];
        }

        if (empty($property->virtual_tour_url)) {
            $recommendations[] = [
                'type' => 'media',
                'priority' => 'medium',
                'message' => 'Adding a virtual tour can significantly increase engagement',
                'action' => 'add_virtual_tour'
            ];
        }

        return $recommendations;
    }

    private function compareWithSimilarProperties(Property $property): array
    {
        $similarProperties = Property::where('id', '!=', $property->id)
            ->whereRaw("JSON_EXTRACT(type, '$.category') = ?", [$property->type['category'] ?? ''])
            ->whereRaw("JSON_EXTRACT(address_details, '$.city.en') = ?", [$property->address_details['city']['en'] ?? ''])
            ->limit(50)
            ->get();

        if ($similarProperties->isEmpty()) {
            return [];
        }

        return [
            'views_comparison' => [
                'property_views' => $property->views,
                'similar_avg' => round($similarProperties->avg('views'), 1),
                'percentile' => $this->calculatePercentile($property->views, $similarProperties->pluck('views')->toArray())
            ],
            'favorites_comparison' => [
                'property_favorites' => $property->favorites_count,
                'similar_avg' => round($similarProperties->avg('favorites_count'), 1),
                'percentile' => $this->calculatePercentile($property->favorites_count, $similarProperties->pluck('favorites_count')->toArray())
            ],
            'price_comparison' => [
                'property_price' => $property->price['usd'] ?? 0,
                'similar_avg' => round($similarProperties->avg(function ($p) {
                    return $p->price['usd'] ?? 0;
                }), 2),
                'position' => $this->getPricePosition($property->price['usd'] ?? 0, $similarProperties)
            ]
        ];
    }

    private function calculatePercentile(float $value, array $dataset): int
    {
        if (empty($dataset)) {
            return 50;
        }

        $lowerCount = count(array_filter($dataset, function ($v) use ($value) {
            return $v < $value;
        }));

        return min(100, max(1, round(($lowerCount / count($dataset)) * 100)));
    }

    private function getPricePosition(float $price, $similarProperties): string
    {
        if ($price == 0) return 'unknown';

        $avgPrice = $similarProperties->avg(function ($p) {
            return $p->price['usd'] ?? 0;
        });

        if ($price > $avgPrice * 1.2) return 'above_market';
        if ($price < $avgPrice * 0.8) return 'below_market';
        return 'market_rate';
    }
}