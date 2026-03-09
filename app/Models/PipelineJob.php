<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipelineJob extends Model
{
    protected $table = 'pipeline_jobs';

    protected $fillable = [
        'job_name',
        'job_type',
        'triggered_by',
        'scope',
        'status',
        'started_at',
        'completed_at',
        'duration_seconds',
        'records_processed',
        'records_created',
        'records_updated',
        'records_failed',
        'error_message',
        'error_trace',
        'python_response_summary',
        'memory_peak_bytes',
    ];

    protected $casts = [
        'started_at'              => 'datetime',
        'completed_at'            => 'datetime',
        'duration_seconds'        => 'integer',
        'records_processed'       => 'integer',
        'records_created'         => 'integer',
        'records_updated'         => 'integer',
        'records_failed'          => 'integer',
        'python_response_summary' => 'array',
        'memory_peak_bytes'       => 'integer',
    ];

    /**
     * Create and return a new running pipeline job log entry.
     */
    public static function startJob(
        string $jobName,
        string $jobType,
        string $triggeredBy = 'scheduler',
        ?string $scope = null
    ): self {
        return static::create([
            'job_name'     => $jobName,
            'job_type'     => $jobType,
            'triggered_by' => $triggeredBy,
            'scope'        => $scope,
            'status'       => 'running',
            'started_at'   => now(),
        ]);
    }

    /**
     * Mark this job as completed with result counts.
     */
    public function markCompleted(
        int $processed = 0,
        int $created = 0,
        int $updated = 0,
        array $pythonSummary = []
    ): void {
        $this->update([
            'status'                  => 'completed',
            'completed_at'            => now(),
            'duration_seconds'        => now()->diffInSeconds($this->started_at),
            'records_processed'       => $processed,
            'records_created'         => $created,
            'records_updated'         => $updated,
            'python_response_summary' => $pythonSummary,
            'memory_peak_bytes'       => memory_get_peak_usage(true),
        ]);
    }

    /**
     * Mark this job as failed with error details.
     */
    public function markFailed(string $message, string $trace = ''): void
    {
        $this->update([
            'status'        => 'failed',
            'completed_at'  => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
            'error_message' => $message,
            'error_trace'   => $trace,
            'memory_peak_bytes' => memory_get_peak_usage(true),
        ]);
    }

    /**
     * Get timestamp of last successful run of a given job.
     */
    public static function lastSuccessfulRun(string $jobName): ?\Carbon\Carbon
    {
        $job = static::where('job_name', $jobName)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->first();

        return $job?->completed_at;
    }
}
