<?php
// app/Models/ServiceProviderReview.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProviderReview extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_provider_id',
        'reviewer_name',
        'reviewer_avatar',
        'star_rating',
        'review_content',
        'review_date',
        'service_type',
        'is_verified',
        'is_featured',
    ];

    protected $casts = [
        'star_rating' => 'integer',
        'review_date' => 'date',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('review_date', 'desc');
    }
}