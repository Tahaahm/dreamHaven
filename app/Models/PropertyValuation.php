<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyValuation extends Model
{
    protected $table = 'property_valuations';

    protected $fillable = [
        'property_id',
        'predicted_price',
        'predicted_price_per_m2',
        'actual_price',
        'actual_price_per_m2',
        'actual_area_m2',
        'overprice_percent',
        'underprice_percent',
        'predicted_price_low',
        'predicted_price_high',
        'confidence_score',
        'verdict',
        'feature_inputs',
        'model_version',
        'algorithm',
        'comparable_property_ids',
        'status',
        'error_message',
        'predicted_at',
    ];

    protected $casts = [
        'predicted_price'         => 'float',
        'predicted_price_per_m2'  => 'float',
        'actual_price'            => 'float',
        'actual_price_per_m2'     => 'float',
        'actual_area_m2'          => 'float',
        'overprice_percent'       => 'float',
        'underprice_percent'      => 'float',
        'predicted_price_low'     => 'float',
        'predicted_price_high'    => 'float',
        'confidence_score'        => 'float',
        'feature_inputs'          => 'array',
        'comparable_property_ids' => 'array',
        'predicted_at'            => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->orderByDesc('predicted_at');
    }

    /**
     * Returns human-readable verdict text for Flutter UI.
     */
    public function getVerdictLabelAttribute(): string
    {
        return match ($this->verdict) {
            'great_deal'  => '🔥 Great Deal',
            'underpriced' => '✅ Below Market',
            'fair_value'  => '⚖️ Fair Price',
            'overpriced'  => '⚠️ Overpriced',
            default       => $this->verdict,
        };
    }

    /**
     * Returns verdict color for Flutter UI badge.
     */
    public function getVerdictColorAttribute(): string
    {
        return match ($this->verdict) {
            'great_deal'  => '#16A34A',
            'underpriced' => '#22C55E',
            'fair_value'  => '#3B82F6',
            'overpriced'  => '#EF4444',
            default       => '#6B7280',
        };
    }

    /**
     * Confidence level label.
     */
    public function getConfidenceLabelAttribute(): string
    {
        return match (true) {
            $this->confidence_score >= 0.85 => 'High',
            $this->confidence_score >= 0.65 => 'Medium',
            default                         => 'Low',
        };
    }
}
