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


class TrainAIModelsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800; // 30 min — training can take a while
    public int $tries   = 1;    // Don't retry training — expensive

    public function __construct(
        private string $modelType = 'price_predictor'
    ) {}

    public function handle(AIBridgeService $ai): void
    {
        $log = PipelineJobLog::startJob(static::class, 'training', 'scheduler');

        try {
            $result = $ai->trainModel($this->modelType);

            if (!$result) {
                $log->markFailed('Python training returned null — check Python service logs');
                return;
            }

            // Save model metadata to DB
            $metadata = AiModelMetadata::create([
                'model_name'              => $result['model_name'],
                'version'                 => $result['version'],
                'algorithm'               => $result['algorithm'],
                'model_file_path'         => $result['model_file_path'],
                'model_file_size_bytes'   => $result['model_file_size_bytes'] ?? 0,
                'training_samples'        => $result['training_samples'],
                'feature_count'           => count($result['feature_names'] ?? []),
                'feature_names'           => $result['feature_names'] ?? [],
                'rmse'                    => $result['metrics']['rmse'] ?? null,
                'mae'                     => $result['metrics']['mae']  ?? null,
                'r2_score'                => $result['metrics']['r2_score'] ?? null,
                'mape'                    => $result['metrics']['mape'] ?? null,
                'hyperparameters'         => $result['hyperparameters'] ?? [],
                'status'                  => 'ready',
                'training_started_at'     => $result['training_started_at'],
                'training_completed_at'   => $result['training_completed_at'],
            ]);

            // Activate the new model (deactivates old)
            $metadata->activate();

            $log->markCompleted(
                processed: $result['training_samples'],
                pythonSummary: [
                    'model_version' => $result['version'],
                    'r2_score'      => $result['metrics']['r2_score'] ?? null,
                    'rmse'          => $result['metrics']['rmse'] ?? null,
                ]
            );

            Log::info("TrainAIModelsJob: {$this->modelType} v{$result['version']} trained, R²={$result['metrics']['r2_score']}");
        } catch (\Throwable $e) {
            $log->markFailed($e->getMessage(), $e->getTraceAsString());
            throw $e;
        }
    }
}
