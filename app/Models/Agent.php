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
// Import Branch and Area models if they exist in specific namespaces,
// otherwise assume they are in App\Models
use App\Models\Branch;
use App\Models\Area;
use \App\Models\Support\HasFeedActivity;
use \App\Models\Support\HasFollowers;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Agent extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, HasApiTokens, HasFeedActivity, HasFollowers;

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
        'subscription_id',
        'current_plan',
        'properties_uploaded_this_month',
        'remaining_property_uploads',
        'primary_email',
        'primary_phone',
        'whatsapp_number',
        'office_address',
        'latitude',
        'longitude',
        'google_id',
        // Location Strings (Legacy/Display)
        'city',
        'district',

        // Location IDs (Relationships) - ADDED THESE
        'city_id',
        'area_id',

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
        'device_tokens', // ← ADD THIS
        'language',      // ← ADD THIS
        'followers_count',
        'following_count',

    ];

    protected $casts = [
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
        'device_tokens'                  => 'array', // ← ADD THIS

    ];

    // ==========================================
    // 🔗 RELATIONSHIPS
    // ==========================================

    /**
     * Relationship: Agent belongs to a Branch (City)
     * Matches: $agent->load('branch')
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'city_id');
    }

    /**
     * Relationship: Agent belongs to an Area
     * Matches: $agent->load('area')
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    /**
     * Relationship: Agent belongs to a Subscription
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    /**
     * Alias for subscription to match some controller logic calling currentSubscription
     */
    public function currentSubscription(): BelongsTo
    {
        return $this->subscription();
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


    public function showAddProperty()
    {
        // ✅ CHECK SUBSCRIPTION BEFORE SHOWING FORM
        $validationResult = $this->validateSubscription();
        if ($validationResult) {
            return $validationResult;
        }

        return view('agent.agent-property-add');
    }

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
                    'created_at'   => $t['created_at'] ?? now()->format('Y-m-d H:i:s'), // preserve original
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

    public function getFCMTokens(): array
    {
        $tokens = $this->device_tokens ?? [];

        if (is_string($tokens)) {
            $tokens = json_decode($tokens, true) ?? [];
        }

        return array_values(array_filter(
            array_column($tokens, 'fcm_token')
        ));
    }

    public function removeFCMToken(string $invalidToken): void
    {
        $tokens = $this->device_tokens ?? [];

        if (is_string($tokens)) {
            $tokens = json_decode($tokens, true) ?? [];
        }

        $filtered = array_values(
            array_filter($tokens, fn($t) => ($t['fcm_token'] ?? '') !== $invalidToken)
        );

        $this->update(['device_tokens' => $filtered]);
    }
}
