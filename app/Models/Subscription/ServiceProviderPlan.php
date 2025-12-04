<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceProviderPlan extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
        'monthly_price',
        'annual_price',
        'advertisement_slots',
        'featured_placement_days',
        'banner',
        'features',
        'trial_days',
        'most_popular',
        'overage_pricing',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'annual_price' => 'decimal:2',
            'advertisement_slots' => 'integer',
            'featured_placement_days' => 'integer',
            'banner' => 'integer',
            'features' => 'array',
            'trial_days' => 'integer',
            'most_popular' => 'boolean',
            'overage_pricing' => 'array',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('most_popular', true);
    }
}
