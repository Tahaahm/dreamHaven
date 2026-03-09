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

class ComputeHeatmapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 2;

    public function __construct(
        private array $options = ['type' => 'all']
    ) {}

    public function handle(AIBridgeService $ai): void
    {
        $log = PipelineJobLog::startJob(static::class, 'heatmap', 'scheduler');

        try {
            $type       = $this->options['type'] ?? 'all';
            $newVersion = (HeatmapTile::max('version') ?? 0) + 1;

            $result = $ai->generateHeatmap($type, 0.005);

            if (!$result || empty($result['tiles'])) {
                $log->markFailed('Python service returned empty heatmap tiles');
                return;
            }

            // Bulk insert tiles for performance
            $types = $type === 'all' ? ['price', 'demand', 'density'] : [$type];

            foreach ($types as $t) {
                $tilesForType = array_filter($result['tiles'], fn($tile) => $tile['type'] === $t);
                if (empty($tilesForType)) continue;

                $rows = array_map(fn($tile) => [
                    'latitude'        => $tile['latitude'],
                    'longitude'       => $tile['longitude'],
                    'cell_min_lat'    => $tile['cell_min_lat'],
                    'cell_max_lat'    => $tile['cell_max_lat'],
                    'cell_min_lng'    => $tile['cell_min_lng'],
                    'cell_max_lng'    => $tile['cell_max_lng'],
                    'heatmap_type'    => $t,
                    'weight'          => $tile['weight'],
                    'raw_value'       => $tile['raw_value'],
                    'property_count'  => $tile['property_count'] ?? 0,
                    'avg_price'       => $tile['avg_price'] ?? 0,
                    'avg_price_per_m2' => $tile['avg_price_per_m2'] ?? 0,
                    'version'         => $newVersion,
                    'is_active'       => false,
                    'computed_at'     => now(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ], array_values($tilesForType));

                // Chunk inserts to avoid memory issues with large datasets
                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('heatmap_tiles')->insert($chunk);
                }

                HeatmapTile::swapActiveVersion($newVersion, $t);
            }

            $log->markCompleted(
                processed: count($result['tiles']),
                created: count($result['tiles']),
                pythonSummary: ['new_version' => $newVersion, 'type' => $type]
            );
        } catch (\Throwable $e) {
            $log->markFailed($e->getMessage(), $e->getTraceAsString());
            throw $e;
        }
    }
}
