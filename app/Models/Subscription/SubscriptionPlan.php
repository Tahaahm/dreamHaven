<?php

namespace App\Models\Subscription;


use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Subscription\ServiceProviderPlan;


// Subscription Plan Model
class SubscriptionPlan extends Model
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
        'property_activation_limit',
        'team_members',
        'features',
        'trial_days',
        'most_popular',
        'banner',
        'overage_pricing',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'annual_price' => 'decimal:2',
            'property_activation_limit' => 'integer',
            'team_members' => 'integer',
            'features' => 'array',
            'trial_days' => 'integer',
            'most_popular' => 'boolean',
            'banner' => 'integer',
            'overage_pricing' => 'array',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function Subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'current_plan_id');
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
