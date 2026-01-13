<?php

namespace App\Models;

use App\Models\Support\OfficeCompanyAgent;
use App\Models\Support\OfficeCustomerReview;
use App\Models\Support\OfficeNotificationReference;
use App\Models\Support\OfficeProjectPortfolio;
use App\Models\Support\OfficePropertyListing;
use App\Models\Support\OfficePropertyType;
use App\Models\Support\OfficeSocialMedia;
use App\Models\Subscription\Subscription;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RealEstateOffice extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, HasApiTokens;

    protected $fillable = [
        'company_name',
        'company_bio',
        'company_bio_image',
        'profile_image',
        'account_type',
        'subscription_id',
        'current_plan',
        'is_verified',
        'average_rating',
        'email_address',
        'password',
        'phone_number',
        'office_address',
        'latitude',
        'longitude',
        'city',
        'district',
        'properties_sold',
        'years_experience',
        'about_company',
        'availability_schedule',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'average_rating' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'properties_sold' => 'integer',
            'years_experience' => 'integer',
            'availability_schedule' => 'array',
        ];
    }

    // Override the email column name for authentication
    public function getEmailForPasswordReset()
    {
        return $this->email_address;
    }

    // Override username for authentication
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    // ==================== RELATIONSHIPS ====================

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function propertyTypes(): HasMany
    {
        return $this->hasMany(OfficePropertyType::class, 'office_id');
    }

    public function propertyListings(): HasMany
    {
        return $this->hasMany(OfficePropertyListing::class, 'office_id');
    }

    public function projectPortfolio(): HasMany
    {
        return $this->hasMany(OfficeProjectPortfolio::class, 'office_id');
    }

    public function companyAgents(): HasMany
    {
        return $this->hasMany(OfficeCompanyAgent::class, 'office_id');
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'company_id');
    }

    public function socialMedia(): HasMany
    {
        return $this->hasMany(OfficeSocialMedia::class, 'office_id');
    }

    public function customerReviews(): HasMany
    {
        return $this->hasMany(OfficeCustomerReview::class, 'office_id');
    }

    public function notificationReferences(): HasMany
    {
        return $this->hasMany(OfficeNotificationReference::class, 'office_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'office_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'office_id');
    }

    public function systemNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'office_id');
    }

    public function ownedProperties(): MorphMany
    {
        return $this->morphMany(Property::class, 'owner');
    }

    // ==================== SCOPES ====================

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeByPlan($query, $plan)
    {
        return $query->where('current_plan', $plan);
    }

    public function scopeWithActiveSubscription($query)
    {
        return $query->whereHas('subscription', function ($q) {
            $q->where('status', 'active')
                ->where(function ($subQ) {
                    $subQ->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                });
        });
    }

    // ==================== ACCESSORS ====================

    public function getActiveAgentsCountAttribute()
    {
        return $this->companyAgents()->where('is_active', true)->count();
    }

    // ==================== SUBSCRIPTION HELPER METHODS ====================

    /**
     * Check if office has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        // ✅ CHANGE THIS to lowercase
        if (!$this->subscription_id || !$this->subscription) {
            return false;
        }

        return $this->subscription->isActive();
    }

    /**
     * Get current property count
     */
    public function getCurrentPropertyCount(): int
    {
        return Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $this->id)
            ->count();
    }

    /**
     * Check if office can add more properties
     */
    public function canAddProperty(): bool
    {
        // No subscription = no access
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        $subscription = $this->subscription;

        // Unlimited properties (null or 0 limit)
        if ($subscription->property_activation_limit === null || $subscription->property_activation_limit === 0) {
            return true;
        }

        // Check remaining activations
        return $subscription->remaining_activations > 0;
    }

    /**
     * Get property limit info
     */
    public function getPropertyLimitInfo(): array
    {
        $subscription = $this->subscription;
        $currentCount = $this->getCurrentPropertyCount();

        if (!$subscription || !$subscription->isActive()) {
            return [
                'has_subscription' => false,
                'current' => $currentCount,
                'limit' => 0,
                'remaining' => 0,
                'can_add' => false,
                'percentage_used' => 0,
                'is_unlimited' => false,
            ];
        }

        $limit = $subscription->property_activation_limit ?? null;
        $isUnlimited = $limit === null || $limit === 0;

        $remaining = $isUnlimited ? PHP_INT_MAX : max(0, $subscription->remaining_activations);
        $percentageUsed = $isUnlimited ? 0 : ($limit > 0 ? ($currentCount / $limit) * 100 : 0);

        return [
            'has_subscription' => true,
            'current' => $currentCount,
            'limit' => $isUnlimited ? '∞' : $limit,
            'remaining' => $isUnlimited ? '∞' : $remaining,
            'can_add' => $this->canAddProperty(),
            'percentage_used' => $percentageUsed,
            'is_unlimited' => $isUnlimited,
            'subscription_end_date' => $subscription->end_date,
            'days_remaining' => $subscription->daysRemaining(),
            'is_trial' => $subscription->isTrialActive(),
            'trial_end_date' => $subscription->trial_end_date,
        ];
    }

    /**
     * Increment property count (when adding property)
     */
    public function incrementPropertyCount(): void
    {
        if ($this->subscription && $this->subscription->property_activation_limit > 0) {
            $this->subscription->incrementPropertyCount();
        }
    }

    /**
     * Decrement property count (when removing property)
     */
    public function decrementPropertyCount(): void
    {
        if ($this->subscription && $this->subscription->property_activation_limit > 0) {
            $this->subscription->decrementPropertyCount();
        }
    }

    /**
     * Get subscription status badge
     */
    public function getSubscriptionStatusBadge(): array
    {
        if (!$this->hasActiveSubscription()) {
            return [
                'text' => 'No Subscription',
                'color' => 'red',
                'icon' => 'ban',
            ];
        }

        $subscription = $this->subscription;

        if ($subscription->isTrialActive()) {
            return [
                'text' => 'Trial Active',
                'color' => 'blue',
                'icon' => 'clock',
            ];
        }

        $daysLeft = $subscription->daysRemaining();

        if ($daysLeft <= 7) {
            return [
                'text' => 'Expiring Soon',
                'color' => 'orange',
                'icon' => 'exclamation-triangle',
            ];
        }

        return [
            'text' => 'Active',
            'color' => 'green',
            'icon' => 'check-circle',
        ];
    }
}