<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RealEstateOffice extends Model
{
    use HasFactory, HasUuids;

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
        'password', // Add this if you need authentication
    ];

    protected $hidden = [
        'password', // Hide password in JSON responses
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'average_rating' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'properties_sold' => 'integer',
            'years_experience' => 'integer',
            'availability_schedule' => 'array',
        ];
    }

    // Relationships
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
