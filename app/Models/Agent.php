<?php

namespace App\Models;

use App\Models\Subscription\Subscription;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Support\AgentClientReview;
use App\Models\Support\AgentNotification;
use App\Models\Support\AgentSocialPlatform;
use App\Models\Support\AgentSpecialization;
use App\Models\Support\AgentUploadedProperty;

use Illuminate\Foundation\Auth\User as Authenticatable; // <-- important for Auth
use Illuminate\Notifications\Notifiable;

class Agent extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $table = 'agents';
    public $incrementing = false;      // UUIDs are not auto-incrementing
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
        'Subscription_id',
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

    // Relationships
    public function Subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'Subscription_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'company_id');
    }

    public function specializations(): HasMany
    {
        return $this->hasMany(AgentSpecialization::class, 'agent_id');
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

    // Scopes
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
}
