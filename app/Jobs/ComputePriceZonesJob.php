<?php

namespace App\Jobs;

use App\Models\PipelineJob as PipelineJobLog;
use App\Models\PriceZone;
use App\Models\HeatmapTile;
use App\Models\PropertyValuation;
use App\Models\AiModelMetadata;
use App\Services\AIBridgeService;
use App\Services\InsightsAggregatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComputePriceZonesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900; // 15 min — clustering can be slow
    public int $tries   = 2;

    public function handle(AIBridgeService $ai): void
    {
        $log = PipelineJobLog::startJob(static::class, 'zones', 'scheduler');

        try {
            // Get new version number
            $newVersion = (PriceZone::max('version') ?? 0) + 1;

            // Call Python clustering for each city branch
            $branches    = DB::table('branches')->select('id', 'name')->get();
            $totalZones  = 0;

            foreach ($branches as $branch) {
                $result = $ai->clusterZones(
                    ['n_clusters' => 4, 'algorithm' => 'kmeans'],
                    $branch->id
                );

                if (!$result || empty($result['zones'])) {
                    Log::warning("ComputePriceZonesJob: No zones returned for branch {$branch->id}");
                    continue;
                }

                foreach ($result['zones'] as $zone) {
                    PriceZone::create([
                        'zone_name'        => "{$branch->name} Zone " . strtoupper(chr(65 + $zone['cluster_id'])),
                        'zone_code'        => strtoupper(substr($branch->name, 0, 3)) . '-' . ($zone['cluster_id'] + 1) . '-' . $newVersion,
                        'tier'             => $zone['tier'],
                        'color_hex'        => PriceZone::colorForTier($zone['tier']),
                        'geojson_polygon'  => json_encode($zone['geojson_polygon']),
                        'bbox_min_lat'     => $zone['bbox']['min_lat'],
                        'bbox_max_lat'     => $zone['bbox']['max_lat'],
                        'bbox_min_lng'     => $zone['bbox']['min_lng'],
                        'bbox_max_lng'     => $zone['bbox']['max_lng'],
                        'centroid_lat'     => $zone['centroid_lat'],
                        'centroid_lng'     => $zone['centroid_lng'],
                        'avg_price_per_m2' => $zone['stats']['avg_price_per_m2'],
                        'min_price_per_m2' => $zone['stats']['min_price_per_m2'],
                        'max_price_per_m2' => $zone['stats']['max_price_per_m2'],
                        'avg_total_price'  => $zone['stats']['avg_total_price'],
                        'property_count'   => $zone['stats']['property_count'],
                        'demand_score'     => $zone['stats']['demand_score'] ?? 0,
                        'investment_score' => $zone['stats']['investment_score'] ?? 0,
                        'algorithm'        => $result['algorithm'],
                        'cluster_id'       => $zone['cluster_id'],
                        'branch_id'        => $branch->id,
                        'version'          => $newVersion,
                        'is_active'        => false, // activate atomically after all branches done
                        'computed_at'      => now(),
                    ]);
                    $totalZones++;
                }
            }

            // Atomic version swap: deactivate old, activate new
            PriceZone::swapActiveVersion($newVersion);

            $log->markCompleted(
                processed: $totalZones,
                created: $totalZones,
                pythonSummary: ['new_version' => $newVersion, 'zones_created' => $totalZones]
            );

            Log::info("ComputePriceZonesJob: {$totalZones} zones created, version {$newVersion} activated");
        } catch (\Throwable $e) {
            $log->markFailed($e->getMessage(), $e->getTraceAsString());
            throw $e;
        }
    }
}
