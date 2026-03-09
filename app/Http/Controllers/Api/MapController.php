<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceZone;
use App\Models\HeatmapTile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * MapController
 * ─────────────────────────────────────────────────────────────────────────────
 * Serves pre-computed map data to the Flutter app.
 * All responses are cached — no computation happens here.
 *
 * GET /api/v1/map/zones
 * GET /api/v1/map/heatmap
 * ─────────────────────────────────────────────────────────────────────────────
 */
class MapController extends Controller
{
    /**
     * GET /api/v1/map/zones
     *
     * Returns a GeoJSON FeatureCollection of all active price zones.
     * Each Feature has properties for tier, color, stats, and tap panel data.
     *
     * Query params:
     *   branch_id  : filter by city (optional)
     *   tier       : filter by tier affordable|medium|expensive|luxury (optional)
     *   min_lat, max_lat, min_lng, max_lng : viewport bounds (optional, speeds up render)
     *
     * Cache: 6 hours (zones only change when ComputePriceZonesJob runs)
     */
    public function zones(Request $request): JsonResponse
    {
        $branchId = $request->integer('branch_id') ?: null;
        $tier     = $request->string('tier')->toString() ?: null;

        // Viewport bounds (for large-scale optimization)
        $minLat = $request->float('min_lat') ?: null;
        $maxLat = $request->float('max_lat') ?: null;
        $minLng = $request->float('min_lng') ?: null;
        $maxLng = $request->float('max_lng') ?: null;

        $cacheKey = "map_zones_" . md5(
            ($branchId ?? 'all') . ($tier ?? 'all') .
                ($minLat ?? '') . ($maxLat ?? '') . ($minLng ?? '') . ($maxLng ?? '')
        );

        $data = Cache::remember($cacheKey, now()->addHours(6), function () use (
            $branchId,
            $tier,
            $minLat,
            $maxLat,
            $minLng,
            $maxLng
        ) {
            $query = PriceZone::active()->latestVersion();

            if ($branchId) $query->forBranch($branchId);
            if ($tier)     $query->byTier($tier);

            if ($minLat && $maxLat && $minLng && $maxLng) {
                $query->inViewport($minLat, $maxLat, $minLng, $maxLng);
            }

            $zones = $query->get();

            // Build GeoJSON FeatureCollection
            $features = $zones->map(function (PriceZone $zone) {
                $polygon = json_decode($zone->geojson_polygon, true);

                return [
                    'type'       => 'Feature',
                    'id'         => $zone->id,
                    'geometry'   => $polygon['geometry'] ?? $polygon,
                    'properties' => [
                        // Map rendering
                        'zone_id'          => $zone->id,
                        'zone_name'        => $zone->zone_name,
                        'zone_code'        => $zone->zone_code,
                        'tier'             => $zone->tier,
                        'tier_label'       => $zone->tier_label,
                        'color_hex'        => $zone->color_hex,

                        // Tap panel data (shown when user taps a zone)
                        'avg_price'        => $zone->avg_total_price,
                        'avg_price_per_m2' => $zone->avg_price_per_m2,
                        'min_price_per_m2' => $zone->min_price_per_m2,
                        'max_price_per_m2' => $zone->max_price_per_m2,
                        'property_count'   => $zone->property_count,
                        'demand_score'     => $zone->demand_score,
                        'investment_score' => $zone->investment_score,

                        // Map label anchor
                        'centroid_lat'     => $zone->centroid_lat,
                        'centroid_lng'     => $zone->centroid_lng,
                    ],
                ];
            });

            return [
                'type'     => 'FeatureCollection',
                'version'  => $zones->first()?->version,
                'count'    => $features->count(),
                'features' => $features->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/map/heatmap
     *
     * Returns weighted points for Flutter heatmap layer.
     * Optimized for fast rendering — returns only lat/lng/weight.
     *
     * Query params:
     *   type       : price|demand|density  (default: density)
     *   branch_id  : filter by city (optional)
     *   min_lat, max_lat, min_lng, max_lng : viewport bounds (reduces payload)
     *
     * Cache: 6 hours
     */
    public function heatmap(Request $request): JsonResponse
    {
        $type     = $request->string('type', 'density')->toString();
        $branchId = $request->integer('branch_id') ?: null;

        $minLat = $request->float('min_lat') ?: null;
        $maxLat = $request->float('max_lat') ?: null;
        $minLng = $request->float('min_lng') ?: null;
        $maxLng = $request->float('max_lng') ?: null;

        // Validate type
        if (!in_array($type, ['price', 'demand', 'density'])) {
            $type = 'density';
        }

        $cacheKey = "map_heatmap_{$type}_" . md5(
            ($branchId ?? 'all') .
                ($minLat ?? '') . ($maxLat ?? '') . ($minLng ?? '') . ($maxLng ?? '')
        );

        $data = Cache::remember($cacheKey, now()->addHours(6), function () use (
            $type,
            $branchId,
            $minLat,
            $maxLat,
            $minLng,
            $maxLng
        ) {
            $query = HeatmapTile::active()->ofType($type);

            if ($branchId) $query->where('branch_id', $branchId);

            if ($minLat && $maxLat && $minLng && $maxLng) {
                $query->inViewport($minLat, $maxLat, $minLng, $maxLng);
            }

            // Select only what Flutter needs — keeps payload small
            $tiles = $query->select(['latitude', 'longitude', 'weight'])->get();

            return [
                'type'   => $type,
                'count'  => $tiles->count(),
                'points' => $tiles->map->toHeatmapPoint()->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
