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

class ComputeInvestmentScoresJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 2;

    public function handle(InsightsAggregatorService $aggregator): void
    {
        $log = PipelineJobLog::startJob(static::class, 'investment', 'scheduler');

        try {
            $count = $aggregator->computeAllInvestmentScores();
            $log->markCompleted(processed: $count, updated: $count);
            Log::info("ComputeInvestmentScoresJob: scored {$count} areas");
        } catch (\Throwable $e) {
            $log->markFailed($e->getMessage(), $e->getTraceAsString());
            throw $e;
        }
    }
}
