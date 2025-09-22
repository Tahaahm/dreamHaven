<?php

namespace App\Models\Support;

use App\Models\ServiceProvider;

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
        'is_featured'
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'star_rating' => 'integer',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
