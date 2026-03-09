<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PIPELINE JOBS
 * ─────────────────────────────────────────────────────────────────────────────
 * Audit log for every scheduled pipeline job run.
 * Records what ran, when, how long it took, and whether it succeeded.
 *
 * This is NOT Laravel's built-in jobs table.
 * This is a CUSTOM audit table for the AI pipeline specifically.
 *
 * Allows:
 * - Admin dashboard showing pipeline health
 * - Detecting when a job last ran (to avoid duplicate runs)
 * - Debugging failed jobs with stored error logs
 * - Performance monitoring (how long does each job take?)
 *
 * Populated by: all ComputeXxxJob classes after each run
 * ─────────────────────────────────────────────────────────────────────────────
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_jobs', function (Blueprint $table) {

            $table->id();

            // ── Job identity ──────────────────────────────────────────────────
            $table->string('job_name', 100);
            // e.g. 'ComputeAreaInsightsJob', 'ComputePriceZonesJob'

            $table->string('job_type', 50);
            // e.g. 'insights' | 'zones' | 'heatmap' | 'valuations'
            //       'investment' | 'trends' | 'training'

            $table->string('triggered_by', 50)->default('scheduler');
            // 'scheduler' | 'manual' | 'api' | 'webhook'

            // ── Scope ─────────────────────────────────────────────────────────
            // null = all areas/branches, or specify a scope
            $table->string('scope', 100)->nullable();
            // e.g. 'branch:1' | 'area:45' | 'all'

            // ── Status ────────────────────────────────────────────────────────
            // pending | running | completed | failed | partial
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'partial'])
                ->default('pending');

            // ── Timing ────────────────────────────────────────────────────────
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();

            // ── Results ───────────────────────────────────────────────────────
            $table->unsignedInteger('records_processed')->default(0);
            $table->unsignedInteger('records_created')->default(0);
            $table->unsignedInteger('records_updated')->default(0);
            $table->unsignedInteger('records_failed')->default(0);

            // ── Error details ─────────────────────────────────────────────────
            $table->text('error_message')->nullable();
            $table->longText('error_trace')->nullable();

            // ── Python service response ───────────────────────────────────────
            // Summary of what the Python microservice returned
            $table->json('python_response_summary')->nullable();

            // ── Memory & performance ──────────────────────────────────────────
            $table->unsignedBigInteger('memory_peak_bytes')->nullable();

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index(['job_name', 'status']);
            $table->index(['job_type', 'started_at']);
            $table->index('status');
            $table->index('started_at');

            // Fast lookup: "when did ComputeAreaInsightsJob last succeed?"
            $table->index(
                ['job_name', 'status', 'completed_at'],
                'idx_last_successful_run'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_jobs');
    }
};
