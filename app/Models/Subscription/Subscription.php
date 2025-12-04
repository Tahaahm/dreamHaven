<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Subscription\ServiceProviderPlan;

use App\Models\Subscription\SubscriptionPlan;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'status',
        'start_date',
        'end_date',
        'billing_cycle',
        'auto_renewal',
        'property_activation_limit',
        'properties_activated_this_month',
        'remaining_activations',
        'next_billing_date',
        'last_payment_date',
        'trial_period',
        'trial_end_date',
        'monthly_amount',
        'current_plan_id',
        'pending_plan_id',
        'plan_change_date',
        'plan_change_type',
        'prorated_amount',
        'prorated_days',
        'credit_balance',
        'proration_method',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'auto_renewal' => 'boolean',
            'property_activation_limit' => 'integer',
            'properties_activated_this_month' => 'integer',
            'remaining_activations' => 'integer',
            'next_billing_date' => 'date',
            'last_payment_date' => 'date',
            'trial_period' => 'boolean',
            'trial_end_date' => 'date',
            'monthly_amount' => 'decimal:2',
            'plan_change_date' => 'date',
            'prorated_amount' => 'decimal:2',
            'prorated_days' => 'integer',
            'credit_balance' => 'decimal:2',
        ];
    }

    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'current_plan_id');
    }

    public function pendingPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'pending_plan_id');
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'Subscription_id');
    }

    public function offices(): HasMany
    {
        return $this->hasMany(RealEstateOffice::class, 'Subscription_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query, $days = 7)
    {
        return $query->where('end_date', '<=', now()->addDays($days));
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isTrialActive()
    {
        return $this->trial_period && $this->trial_end_date && $this->trial_end_date->isFuture();
    }
}
