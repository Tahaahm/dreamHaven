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
use \App\Models\Support\HasFeedActivity;
use \App\Models\Support\HasFollowers;

class RealEstateOffice extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, HasApiTokens, HasFeedActivity, HasFollowers;

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
        'device_tokens',  // ← ADDED
        'language',       // ← ADDED
        'google_id',
        'followers_count',
        'following_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'is_verified'         => 'boolean',
            'average_rating'      => 'decimal:2',
            'latitude'            => 'decimal:8',
            'longitude'           => 'decimal:8',
            'properties_sold'     => 'integer',
            'years_experience'    => 'integer',
            'availability_schedule' => 'array',
            'device_tokens'       => 'array',  // ← ADDED
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

    // ==================== DEVICE TOKEN METHODS ====================

    /**
     * Add or update an FCM token for this office.
     * Same logic as Agent and User.
     */
    public function addFCMToken(string $token, ?string $deviceName = null): void
    {
        $tokens     = $this->device_tokens ?? [];
        $deviceName = $deviceName ?? 'Unknown Device';

        // If the exact token already exists — nothing to do
        $existingTokens = collect($tokens)->map(
            fn($t) => is_array($t) ? ($t['fcm_token'] ?? null) : $t
        );
        if ($existingTokens->contains($token)) {
            return;
        }

        // If same device name exists → replace its token
        $found  = false;
        $tokens = collect($tokens)->map(function ($t) use ($token, $deviceName, &$found) {
            $existingDevice = is_array($t) ? ($t['device_name'] ?? null) : null;

            if ($existingDevice === $deviceName) {
                $found = true;
                return [
                    'device_name'  => $deviceName,
                    'fcm_token'    => $token,
                    'created_at'   => $t['created_at'] ?? now()->format('Y-m-d H:i:s'), // keep original
                    'last_updated' => now()->format('Y-m-d H:i:s'),
                    'last_login'   => now()->format('Y-m-d H:i:s'),
                ];
            }

            return $t;
        })->toArray();

        // Device name not found → add as new device
        if (!$found) {
            $tokens[] = [
                'device_name'  => $deviceName,
                'fcm_token'    => $token,
                'created_at'   => now()->format('Y-m-d H:i:s'),
                'last_updated' => now()->format('Y-m-d H:i:s'),
                'last_login'   => now()->format('Y-m-d H:i:s'),
            ];
        }

        $this->update(['device_tokens' => $tokens]);
    }

    /**
     * Remove a specific FCM token.
     */
    public function removeFCMToken(string $token): void
    {
        $tokens = collect($this->device_tokens ?? [])
            ->reject(fn($t) => (is_array($t) ? ($t['fcm_token'] ?? null) : $t) === $token)
            ->values()
            ->toArray();

        $this->update(['device_tokens' => $tokens]);
    }

    /**
     * Get all raw FCM token strings for this office.
     */
    public function getFCMTokens(): array
    {
        $fcmTokens = [];

        foreach ($this->device_tokens ?? [] as $device) {
            if (!empty($device['fcm_token'])) {
                $fcmTokens[] = $device['fcm_token'];
            }
        }

        return $fcmTokens;
    }

    // ==================== SUBSCRIPTION HELPER METHODS ====================

    /**
     * Check if office has active subscription
     */
    public function hasActiveSubscription(): bool
    {
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
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        $subscription = $this->subscription;

        if ($subscription->property_activation_limit === null || $subscription->property_activation_limit === 0) {
            return true;
        }

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
                'current'          => $currentCount,
                'limit'            => 0,
                'remaining'        => 0,
                'can_add'          => false,
                'percentage_used'  => 0,
                'is_unlimited'     => false,
            ];
        }

        $limit       = $subscription->property_activation_limit ?? null;
        $isUnlimited = $limit === null || $limit === 0;

        $remaining      = $isUnlimited ? PHP_INT_MAX : max(0, $subscription->remaining_activations);
        $percentageUsed = $isUnlimited ? 0 : ($limit > 0 ? ($currentCount / $limit) * 100 : 0);

        return [
            'has_subscription'     => true,
            'current'              => $currentCount,
            'limit'                => $isUnlimited ? '∞' : $limit,
            'remaining'            => $isUnlimited ? '∞' : $remaining,
            'can_add'              => $this->canAddProperty(),
            'percentage_used'      => $percentageUsed,
            'is_unlimited'         => $isUnlimited,
            'subscription_end_date' => $subscription->end_date,
            'days_remaining'       => $subscription->daysRemaining(),
            'is_trial'             => $subscription->isTrialActive(),
            'trial_end_date'       => $subscription->trial_end_date,
        ];
    }

    /**
     * Increment property count (when adding property)
     */
    public function incrementPropertyCount(): void
    {
        if ($this->subscription) {
            $this->subscription->incrementPropertyCount();
        }
    }

    /**
     * Decrement property count in subscription
     */
    public function decrementPropertyCount(): void
    {
        if ($this->subscription) {
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
                'text'  => 'No Subscription',
                'color' => 'red',
                'icon'  => 'ban',
            ];
        }

        $subscription = $this->subscription;

        if ($subscription->isTrialActive()) {
            return [
                'text'  => 'Trial Active',
                'color' => 'blue',
                'icon'  => 'clock',
            ];
        }

        $daysLeft = $subscription->daysRemaining();

        if ($daysLeft <= 7) {
            return [
                'text'  => 'Expiring Soon',
                'color' => 'orange',
                'icon'  => 'exclamation-triangle',
            ];
        }

        return [
            'text'  => 'Active',
            'color' => 'green',
            'icon'  => 'check-circle',
        ];
    }
}
