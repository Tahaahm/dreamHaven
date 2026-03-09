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

class ComputePropertyValuationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries   = 2;

    public function __construct(
        private ?int $propertyId = null
    ) {}

    public function handle(AIBridgeService $ai, InsightsAggregatorService $aggregator): void
    {
        $log = PipelineJobLog::startJob(
            static::class,
            'valuations',
            $this->propertyId ? 'api' : 'scheduler',
            $this->propertyId ? "property:{$this->propertyId}" : 'all'
        );

        try {
            if ($this->propertyId) {
                // ── Single property valuation ──────────────────────────────
                $this->valuateSingle($this->propertyId, $ai, $aggregator);
                $log->markCompleted(processed: 1, created: 1);
            } else {
                // ── Batch valuation of all active properties ───────────────
                $count = $this->valuateBatch($ai, $aggregator);
                $log->markCompleted(processed: $count, created: $count);
                Log::info("ComputePropertyValuationsJob: valuated {$count} properties");
            }
        } catch (\Throwable $e) {
            $log->markFailed($e->getMessage(), $e->getTraceAsString());
            throw $e;
        }
    }

    private function valuateSingle(
        int $propertyId,
        AIBridgeService $ai,
        InsightsAggregatorService $aggregator
    ): void {
        // Mark as processing
        PropertyValuation::updateOrCreate(
            ['property_id' => $propertyId, 'model_version' => $this->getActiveModelVersion()],
            ['status' => 'processing']
        );

        $features = $aggregator->extractPropertyFeatures($propertyId);
        if (!$features) {
            PropertyValuation::where('property_id', $propertyId)
                ->update(['status' => 'failed', 'error_message' => 'Property not found']);
            return;
        }

        $prediction = $ai->predictPrice($features);
        if (!$prediction) {
            PropertyValuation::where('property_id', $propertyId)
                ->update(['status' => 'failed', 'error_message' => 'AI service returned null']);
            return;
        }

        $this->savePrediction($propertyId, $features, $prediction);
    }

    private function valuateBatch(AIBridgeService $ai, InsightsAggregatorService $aggregator): int
    {
        $properties = DB::table('properties')
            ->where('status', 'active')
            ->select('id')
            ->get();

        $count         = 0;
        $modelVersion  = $this->getActiveModelVersion();
        $batchFeatures = [];

        foreach ($properties as $property) {
            $features = $aggregator->extractPropertyFeatures($property->id);
            if ($features) {
                $batchFeatures[$property->id] = $features;
            }
        }

        // Send to Python in batches of 100
        foreach (array_chunk($batchFeatures, 100, true) as $batch) {
            $predictions = $ai->predictPriceBatch($batch);
            if (!$predictions) continue;

            foreach ($predictions as $propertyId => $prediction) {
                $this->savePrediction($propertyId, $batch[$propertyId], $prediction);
                $count++;
            }
        }

        return $count;
    }

    private function savePrediction(int $propertyId, array $features, array $prediction): void
    {
        $modelVersion = $prediction['model_version'] ?? $this->getActiveModelVersion();

        PropertyValuation::updateOrCreate(
            ['property_id' => $propertyId, 'model_version' => $modelVersion],
            [
                'predicted_price'         => $prediction['predicted_price'],
                'predicted_price_per_m2'  => $prediction['predicted_price_per_m2'],
                'predicted_price_low'     => $prediction['predicted_price_low']  ?? null,
                'predicted_price_high'    => $prediction['predicted_price_high'] ?? null,
                'actual_price'            => $features['price'],
                'actual_price_per_m2'     => $features['area_size'] > 0
                    ? $features['price'] / $features['area_size'] : null,
                'actual_area_m2'          => $features['area_size'],
                'confidence_score'        => $prediction['confidence_score'],
                'overprice_percent'       => $prediction['overprice_percent'],
                'underprice_percent'      => $prediction['underprice_percent'],
                'verdict'                 => $prediction['verdict'],
                'feature_inputs'          => $features,
                'model_version'           => $modelVersion,
                'algorithm'               => $prediction['algorithm'] ?? 'xgboost',
                'comparable_property_ids' => $prediction['comparable_ids'] ?? [],
                'status'                  => 'completed',
                'predicted_at'            => now(),
            ]
        );
    }

    private function getActiveModelVersion(): string
    {
        return AiModelMetadata::getActive('price_predictor')?->version ?? 'v1.0';
    }
}
