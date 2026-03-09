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

class ComputeAreaInsightsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 2;

    public function handle(InsightsAggregatorService $aggregator, AIBridgeService $ai): void
    {
        $log = PipelineJobLog::startJob(static::class, 'insights', 'scheduler');

        try {
            // Step 1: SQL aggregation
            $count = $aggregator->computeAllAreaInsights();

            // Step 2: Enrich with ML-based demand/liquidity scores from Python
            $areaIds = DB::table('areas')->pluck('id')->toArray();
            $scores  = $ai->computeAreaScores($areaIds);

            $updatedScores = 0;
            if ($scores) {
                foreach ($scores as $areaId => $score) {
                    DB::table('area_market_insights')
                        ->where('area_id', $areaId)
                        ->update([
                            'demand_score'   => $score['demand_score']   ?? 0,
                            'liquidity_score' => $score['liquidity_score'] ?? 0,
                        ]);
                    $updatedScores++;
                }
            }

            $log->markCompleted(
                processed: $count,
                updated: $updatedScores,
                pythonSummary: ['ai_scores_applied' => $updatedScores]
            );

            Log::info("ComputeAreaInsightsJob: {$count} areas, {$updatedScores} AI scores applied");
        } catch (\Throwable $e) {
            $log->markFailed($e->getMessage(), $e->getTraceAsString());
            throw $e;
        }
    }
}
