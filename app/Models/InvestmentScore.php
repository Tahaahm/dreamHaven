<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ─────────────────────────────────────────────────────────────────────────────
// InvestmentScore
// ─────────────────────────────────────────────────────────────────────────────

class InvestmentScore extends Model
{
    protected $table = 'investment_scores';

    protected $fillable = [
        'area_id',
        'investment_score',
        'price_growth_score',
        'demand_score',
        'supply_score',
        'liquidity_score',
        'development_score',
        'weight_price_growth',
        'weight_demand',
        'weight_supply',
        'weight_liquidity',
        'weight_development',
        'grade',
        'recommendation',
        'price_growth_90d',
        'listing_velocity',
        'avg_days_on_market',
        'poi_count',
        'active_listing_count',
        'trend',
        'analysis_summary',
        'risk_flags',
        'positive_signals',
        'computed_at',
    ];

    protected $casts = [
        'investment_score'    => 'float',
        'price_growth_score'  => 'float',
        'demand_score'        => 'float',
        'supply_score'        => 'float',
        'liquidity_score'     => 'float',
        'development_score'   => 'float',
        'weight_price_growth' => 'float',
        'weight_demand'       => 'float',
        'weight_supply'       => 'float',
        'weight_liquidity'    => 'float',
        'weight_development'  => 'float',
        'price_growth_90d'    => 'float',
        'listing_velocity'    => 'float',
        'avg_days_on_market'  => 'float',
        'poi_count'           => 'integer',
        'active_listing_count' => 'integer',
        'risk_flags'          => 'array',
        'positive_signals'    => 'array',
        'computed_at'         => 'datetime',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function scopeStrongBuy(Builder $query): Builder
    {
        return $query->where('recommendation', 'strong_buy');
    }

    public function scopeTopScores(Builder $query, int $limit = 10): Builder
    {
        return $query->orderByDesc('investment_score')->limit($limit);
    }

    public function scopeRising(Builder $query): Builder
    {
        return $query->where('trend', 'rising');
    }

    /**
     * Recommendation label with emoji for Flutter UI.
     */
    public function getRecommendationLabelAttribute(): string
    {
        return match ($this->recommendation) {
            'strong_buy' => '🚀 Strong Buy',
            'buy'        => '✅ Buy',
            'hold'       => '⏸️ Hold',
            'avoid'      => '🚫 Avoid',
            default      => ucfirst($this->recommendation),
        };
    }

    /**
     * Score breakdown as array for Flutter radar/chart widget.
     */
    public function getScoreBreakdownAttribute(): array
    {
        return [
            ['label' => 'Price Growth', 'value' => $this->price_growth_score, 'weight' => $this->weight_price_growth],
            ['label' => 'Demand',       'value' => $this->demand_score,       'weight' => $this->weight_demand],
            ['label' => 'Supply',       'value' => $this->supply_score,       'weight' => $this->weight_supply],
            ['label' => 'Liquidity',    'value' => $this->liquidity_score,    'weight' => $this->weight_liquidity],
            ['label' => 'Development',  'value' => $this->development_score,  'weight' => $this->weight_development],
        ];
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// ExternalDataSource
// ─────────────────────────────────────────────────────────────────────────────

class ExternalDataSource extends Model
{
    protected $table = 'external_data_sources';

    protected $fillable = [
        'name',
        'category',
        'subcategory',
        'latitude',
        'longitude',
        'address',
        'area_id',
        'branch_id',
        'impact_weight',
        'source',
        'source_id',
        'source_url',
        'is_verified',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'latitude'      => 'float',
        'longitude'     => 'float',
        'impact_weight' => 'integer',
        'is_verified'   => 'boolean',
        'is_active'     => 'boolean',
        'meta'          => 'array',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Find all POIs within a given radius (km) of a lat/lng point.
     * Uses bounding box approximation for speed (no PostGIS required).
     * 1 degree lat ≈ 111 km. 1 degree lng ≈ 111 * cos(lat) km.
     */
    public function scopeNearby(
        Builder $query,
        float $lat,
        float $lng,
        float $radiusKm = 2.0
    ): Builder {
        $latDelta = $radiusKm / 111.0;
        $lngDelta = $radiusKm / (111.0 * cos(deg2rad($lat)));

        return $query
            ->where('latitude',  '>=', $lat - $latDelta)
            ->where('latitude',  '<=', $lat + $latDelta)
            ->where('longitude', '>=', $lng - $lngDelta)
            ->where('longitude', '<=', $lng + $lngDelta)
            ->where('is_active', true);
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// AiModelMetadata
// ─────────────────────────────────────────────────────────────────────────────

class AiModelMetadata extends Model
{
    protected $table = 'ai_model_metadata';

    protected $fillable = [
        'model_name',
        'version',
        'algorithm',
        'model_file_path',
        'model_file_size_bytes',
        'training_samples',
        'feature_count',
        'feature_names',
        'training_data_from',
        'training_data_to',
        'rmse',
        'mae',
        'r2_score',
        'mape',
        'silhouette_score',
        'n_clusters',
        'hyperparameters',
        'python_version',
        'sklearn_version',
        'xgboost_version',
        'trained_on_server',
        'status',
        'is_active',
        'notes',
        'error_log',
        'training_started_at',
        'training_completed_at',
    ];

    protected $casts = [
        'model_file_size_bytes'   => 'integer',
        'training_samples'        => 'integer',
        'feature_count'           => 'integer',
        'feature_names'           => 'array',
        'training_data_from'      => 'date',
        'training_data_to'        => 'date',
        'rmse'                    => 'float',
        'mae'                     => 'float',
        'r2_score'                => 'float',
        'mape'                    => 'float',
        'silhouette_score'        => 'float',
        'n_clusters'              => 'integer',
        'hyperparameters'         => 'array',
        'is_active'               => 'boolean',
        'training_started_at'     => 'datetime',
        'training_completed_at'   => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForModel(Builder $query, string $modelName): Builder
    {
        return $query->where('model_name', $modelName);
    }

    /**
     * Get the currently active model for a given model name.
     */
    public static function getActive(string $modelName): ?self
    {
        return static::where('model_name', $modelName)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Activate this model and deactivate all others of the same name.
     */
    public function activate(): void
    {
        static::where('model_name', $this->model_name)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false, 'status' => 'deprecated']);

        $this->update(['is_active' => true, 'status' => 'active']);
    }

    /**
     * Training duration in minutes.
     */
    public function getTrainingDurationMinutesAttribute(): ?float
    {
        if (!$this->training_started_at || !$this->training_completed_at) return null;
        return round($this->training_started_at->diffInSeconds($this->training_completed_at) / 60, 1);
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// PipelineJob
// ─────────────────────────────────────────────────────────────────────────────

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
