<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
