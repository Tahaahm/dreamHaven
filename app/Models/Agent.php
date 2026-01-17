<?php

namespace App\Models;

use App\Models\Subscription\Subscription;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Support\AgentClientReview;
use App\Models\Support\AgentNotification;
use App\Models\Support\AgentSocialPlatform;
use App\Models\Support\AgentSpecialization;
use App\Models\Support\AgentUploadedProperty;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Agent extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, HasApiTokens;
    protected $table = 'agents';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'agent_name',
        'agent_bio',
        'bio_image',
        'profile_image',
        'type',
        'password',
        'subscriber_id',
        'is_verified',
        'overall_rating',
        'subscription_id', // Note: Ideally this should be subscription_id (lowercase) in DB
        'current_plan',
        'properties_uploaded_this_month',
        'remaining_property_uploads',
        'primary_email',
        'primary_phone',
        'whatsapp_number',
        'office_address',
        'latitude',
        'longitude',
        'city',
        'district',
        'properties_sold',
        'years_experience',
        'license_number',
        'company_id',
        'company_name',
        'employment_status',
        'agent_overview',
        'working_hours',
        'commission_rate',
        'consultation_fee',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'overall_rating' => 'decimal:2',
            'properties_uploaded_this_month' => 'integer',
            'remaining_property_uploads' => 'integer',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'properties_sold' => 'integer',
            'years_experience' => 'integer',
            'working_hours' => 'array',
            'commission_rate' => 'decimal:2',
            'consultation_fee' => 'decimal:2',
        ];
    }

    // ==========================================
    // 🔗 RELATIONSHIPS
    // ==========================================

    // Fixed naming convention (camelCase) for easier access: $agent->subscription
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'company_id');
    }

    public function specializations(): HasMany
    {
        return $this->hasMany(AgentSpecialization::class, 'agent_id', 'id');
    }

    public function uploadedProperties(): HasMany
    {
        return $this->hasMany(AgentUploadedProperty::class, 'agent_id');
    }

    public function socialPlatforms(): HasMany
    {
        return $this->hasMany(AgentSocialPlatform::class, 'agent_id');
    }

    public function clientReviews(): HasMany
    {
        return $this->hasMany(AgentClientReview::class, 'agent_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AgentNotification::class, 'agent_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'agent_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'agent_id');
    }

    public function systemNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'agent_id');
    }

    public function ownedProperties(): MorphMany
    {
        return $this->morphMany(Property::class, 'owner');
    }

    public function properties(): MorphMany
    {
        return $this->morphMany(Property::class, 'owner');
    }

    // ==========================================
    // 🛡️ SUBSCRIPTION LOGIC HELPERS
    // ==========================================

    /**
     * Check if the agent has a valid, active subscription
     */
    public function hasActiveSubscription(): bool
    {
        if (!$this->subscription) {
            return false;
        }

        return $this->subscription->status === 'active'
            && $this->subscription->end_date > now();
    }

    /**
     * Check if the agent has remaining slots to upload properties
     */
    public function canAddProperty(): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        $limit = $this->subscription->property_activation_limit;

        // If limit is 0 or -1, treat as unlimited (depending on your logic)
        // Here assuming > 0 is a hard limit
        if ($limit > 0) {
            return $this->subscription->remaining_activations > 0;
        }

        return true; // Unlimited
    }

    /**
     * Get info for the dashboard UI (Limit vs Used)
     */
    public function getPropertyLimitInfo(): array
    {
        if (!$this->subscription) {
            return ['limit' => 0, 'remaining' => 0, 'used' => 0];
        }

        $limit = $this->subscription->property_activation_limit;
        $remaining = $this->subscription->remaining_activations;

        return [
            'limit' => $limit,
            'remaining' => $remaining,
            'used' => $limit - $remaining,
            'is_unlimited' => $limit <= 0
        ];
    }

    /**
     * Increment usage when a property is uploaded
     */
    public function incrementPropertyCount(): void
    {
        if ($this->subscription) {
            $this->subscription->decrement('remaining_activations');
            $this->subscription->increment('properties_activated_this_month');

            // Also update local Agent stats if you keep them synced
            $this->increment('properties_uploaded_this_month');
            $this->decrement('remaining_property_uploads');
        }
    }

    /**
     * Decrement usage (e.g. if property is deleted)
     */
    public function decrementPropertyCount(): void
    {
        if ($this->subscription) {
            $this->subscription->increment('remaining_activations');
            $this->subscription->decrement('properties_activated_this_month');

            // Also update local Agent stats
            $this->decrement('properties_uploaded_this_month');
            $this->increment('remaining_property_uploads');
        }
    }

    // ==========================================
    // 🔍 SCOPES
    // ==========================================

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

    public function currentSubscription(): BelongsTo
    {
        return $this->subscription();
    }
}
