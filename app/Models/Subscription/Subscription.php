<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Subscription\SubscriptionPlan;
use App\Models\Agent;
use App\Models\RealEstateOffice;

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

    // ==================== RELATIONSHIPS ====================

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

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function scopeExpiring($query, $days = 7)
    {
        return $query->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now())
            ->orWhere('status', 'cancelled');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // If trial is active, subscription is active
        if ($this->isTrialActive()) {
            return true;
        }

        // Check end date
        if ($this->end_date && $this->end_date < now()->startOfDay()) {
            return false;
        }

        return true;
    }

    /**
     * Check if trial is active
     */
    public function isTrialActive(): bool
    {
        return $this->trial_period && $this->trial_end_date && $this->trial_end_date >= now()->startOfDay();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        if ($this->status === 'cancelled') {
            return true;
        }

        if ($this->end_date && $this->end_date < now()->startOfDay()) {
            return true;
        }

        return false;
    }

    /**
     * Get days remaining
     */
    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        $endDate = $this->trial_period && $this->trial_end_date
            ? $this->trial_end_date
            : $this->end_date;

        if (!$endDate) {
            return PHP_INT_MAX; // Unlimited
        }

        return max(0, now()->diffInDays($endDate, false));
    }

    /**
     * Get remaining percentage
     */
    public function remainingPercentage(): float
    {
        $endDate = $this->trial_period && $this->trial_end_date
            ? $this->trial_end_date
            : $this->end_date;

        if (!$endDate) {
            return 100;
        }

        $total = $this->start_date->diffInDays($endDate);
        $remaining = $this->daysRemaining();

        return $total > 0 ? ($remaining / $total) * 100 : 0;
    }

    /**
     * Check if can activate property
     */
    public function canActivateProperty(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // Unlimited properties (null or 0 means unlimited)
        if ($this->property_activation_limit === null || $this->property_activation_limit === 0) {
            return true;
        }

        // Check remaining activations
        return $this->remaining_activations > 0;
    }

    /**
     * Increment property count
     */
    public function incrementPropertyCount(): void
    {
        if ($this->property_activation_limit > 0) {
            $this->increment('properties_activated_this_month');
            $this->decrement('remaining_activations');
        }
    }

    /**
     * Decrement property count
     */
    public function decrementPropertyCount(): void
    {
        if ($this->property_activation_limit > 0) {
            $this->decrement('properties_activated_this_month');
            $this->increment('remaining_activations');
        }
    }

    /**
     * Reset monthly property count
     */
    public function resetMonthlyPropertyCount(): void
    {
        $this->update([
            'properties_activated_this_month' => 0,
            'remaining_activations' => $this->property_activation_limit ?? 0,
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'auto_renewal' => false,
        ]);
    }

    /**
     * Suspend subscription
     */
    public function suspend(): void
    {
        $this->update(['status' => 'suspended']);
    }

    /**
     * Reactivate subscription
     */
    public function reactivate(): void
    {
        if (!$this->isExpired()) {
            $this->update(['status' => 'active']);
        }
    }

    /**
     * Renew subscription
     */
    public function renew(int $months = null): void
    {
        $months = $months ?? ($this->billing_cycle === 'annual' ? 12 : 1);

        $newEndDate = $this->end_date && $this->end_date > now()
            ? $this->end_date->addMonths($months)
            : now()->addMonths($months);

        $this->update([
            'end_date' => $newEndDate,
            'next_billing_date' => $newEndDate,
            'status' => 'active',
        ]);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->monthly_amount, 2);
    }

    /**
     * Get subscription type (from plan)
     */
    public function getSubscriptionType(): ?string
    {
        return $this->currentPlan?->type;
    }
}
