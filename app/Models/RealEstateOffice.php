<?php

namespace App\Models;

use App\Models\Support\OfficeCompanyAgent;
use App\Models\Support\OfficeCustomerReview;
use App\Models\Support\OfficeNotificationReference;
use App\Models\Support\OfficeProjectPortfolio;
use App\Models\Support\OfficePropertyListing;
use App\Models\Support\OfficePropertyType;
use App\Models\Support\OfficeSocialMedia;
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
        'Subscription_id',
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

    // Relationships
    public function Subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'Subscription_id');
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

    // Accessors
    public function getActiveAgentsCountAttribute()
    {
        return $this->companyAgents()->where('is_active', true)->count();
    }
}
