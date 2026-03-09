<?php

namespace App\Services;

use App\Jobs\ComputeAreaInsightsJob;
use App\Jobs\ComputePriceZonesJob;
use App\Jobs\ComputeHeatmapJob;
use App\Jobs\ComputeInvestmentScoresJob;
use App\Jobs\TrainAIModelsJob;
use App\Jobs\SnapshotMarketTrendsJob;
use App\Jobs\ComputePropertyValuationsJob;
use App\Models\PipelineJob;
use Illuminate\Support\Facades\Log;

/**
 * PipelineOrchestratorService
 * ─────────────────────────────────────────────────────────────────────────────
 * Controls the order and scheduling of all AI pipeline jobs.
 *
 * Correct execution order (data dependencies):
 *   1. SnapshotMarketTrendsJob     — capture today's prices FIRST
 *   2. ComputeAreaInsightsJob      — aggregate metrics (uses trend history)
 *   3. ComputeInvestmentScoresJob  — score areas (uses area insights)
 *   4. ComputePriceZonesJob        — cluster zones (uses area insights)
 *   5. ComputeHeatmapJob           — generate tiles (uses properties)
 *   6. ComputePropertyValuationsJob— batch valuations (uses trained model)
 *   7. TrainAIModelsJob            — retrain model (uses all above, weekly only)
 *
 * Why order matters:
 *   - Price zones need area avg_price_per_m2 to determine tier colors
 *   - Investment scores need demand_score from area insights
 *   - Property valuations need a trained model to exist
 * ─────────────────────────────────────────────────────────────────────────────
 */
class PipelineOrchestratorService
{
    /**
     * Run the full daily pipeline.
     * Called by the Laravel scheduler at midnight every day.
     */
    public function runDailyPipeline(): void
    {
        Log::info('Pipeline: Starting daily pipeline');

        // Dispatch jobs with delays to ensure sequential execution
        // Using delay() prevents all jobs competing for the same queue worker
        SnapshotMarketTrendsJob::dispatch()
            ->onQueue('pipeline');

        ComputeAreaInsightsJob::dispatch()
            ->onQueue('pipeline')
            ->delay(now()->addMinutes(3));

        ComputeInvestmentScoresJob::dispatch()
            ->onQueue('pipeline')
            ->delay(now()->addMinutes(8));

        ComputePriceZonesJob::dispatch()
            ->onQueue('pipeline')
            ->delay(now()->addMinutes(13));

        ComputeHeatmapJob::dispatch(['type' => 'all'])
            ->onQueue('pipeline')
            ->delay(now()->addMinutes(20));

        Log::info('Pipeline: Daily jobs dispatched');
    }

    /**
     * Run the weekly pipeline (includes model retraining).
     * Called every Sunday at 02:00.
     */
    public function runWeeklyPipeline(): void
    {
        Log::info('Pipeline: Starting weekly pipeline (includes training)');

        $this->runDailyPipeline();

        // Train after daily pipeline finishes (allow 60min buffer)
        TrainAIModelsJob::dispatch('price_predictor')
            ->onQueue('pipeline')
            ->delay(now()->addMinutes(60));

        // After training, run batch valuations
        ComputePropertyValuationsJob::dispatch()
            ->onQueue('pipeline')
            ->delay(now()->addMinutes(90));

        Log::info('Pipeline: Weekly jobs dispatched');
    }

    /**
     * Run only area insights (called when new properties are added).
     * Lighter than full pipeline — just refreshes aggregated metrics.
     */
    public function runInsightsOnly(): void
    {
        ComputeAreaInsightsJob::dispatch()->onQueue('pipeline');
        ComputeInvestmentScoresJob::dispatch()
            ->onQueue('pipeline')
            ->delay(now()->addMinutes(5));
    }

    /**
     * Run on-demand valuation for a single property.
     * Called by API when user requests AI valuation for a specific property.
     */
    public function valuateSingleProperty(int $propertyId): void
    {
        ComputePropertyValuationsJob::dispatch($propertyId)
            ->onQueue('valuations'); // separate queue for responsiveness
    }

    /**
     * Get pipeline health status for admin dashboard.
     */
    public function getPipelineStatus(): array
    {
        $jobs = [
            'SnapshotMarketTrendsJob',
            'ComputeAreaInsightsJob',
            'ComputeInvestmentScoresJob',
            'ComputePriceZonesJob',
            'ComputeHeatmapJob',
            'TrainAIModelsJob',
            'ComputePropertyValuationsJob',
        ];

        $status = [];
        foreach ($jobs as $jobName) {
            $last = PipelineJob::lastSuccessfulRun($jobName);
            $lastFailed = PipelineJob::where('job_name', $jobName)
                ->where('status', 'failed')
                ->orderByDesc('started_at')
                ->first();

            $status[] = [
                'job'              => $jobName,
                'last_success'     => $last?->toDateTimeString(),
                'last_failed'      => $lastFailed?->started_at?->toDateTimeString(),
                'is_healthy'       => $last && (!$lastFailed || $last > $lastFailed->started_at),
            ];
        }

        return $status;
    }
}
