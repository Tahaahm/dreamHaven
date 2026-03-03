<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProjectReview extends Model
{
    use HasFactory;

    protected $keyType    = 'string';
    public    $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'project_id',
        'user_id',
        'rating',
        'title',
        'review',
        'pros',
        'cons',
        'location_rating',
        'value_rating',
        'quality_rating',
        'amenities_rating',
        'developer_rating',
        'reviewer_type',
        'unit_type',
        'purchase_date',
        'would_recommend',
        'is_verified',
        'is_approved',
        'is_featured',
        'admin_notes',
        'verified_at',
        'verified_by',
        'helpful_count',
        'not_helpful_count',
        'images',
        'developer_response',
        'developer_responded_at',
        'developer_responded_by',
    ];

    protected $casts = [
        'id'                     => 'string',
        'pros'                   => 'array',
        'cons'                   => 'array',
        'images'                 => 'array',
        'purchase_date'          => 'date',
        'verified_at'            => 'datetime',
        'developer_responded_at' => 'datetime',
        'would_recommend'        => 'boolean',
        'is_verified'            => 'boolean',
        'is_approved'            => 'boolean',
        'is_featured'            => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeTopRated($query)
    {
        return $query->where('rating', '>=', 4)->where('is_approved', true);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function approve(): void
    {
        $this->update(['is_approved' => true]);
    }

    public function reject(): void
    {
        $this->update(['is_approved' => false]);
    }

    public function verify(string $adminId): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $adminId,
        ]);
    }

    public function addDeveloperResponse(string $response, string $respondedBy): void
    {
        $this->update([
            'developer_response'       => $response,
            'developer_responded_at'   => now(),
            'developer_responded_by'   => $respondedBy,
        ]);
    }

    public function getAverageDetailedRatingAttribute(): float
    {
        $ratings = array_filter([
            $this->location_rating,
            $this->value_rating,
            $this->quality_rating,
            $this->amenities_rating,
            $this->developer_rating,
        ]);

        return count($ratings) > 0
            ? round(array_sum($ratings) / count($ratings), 1)
            : (float) $this->rating;
    }

    public function getStarDisplayAttribute(): string
    {
        $filled = str_repeat('★', $this->rating);
        $empty  = str_repeat('☆', 5 - $this->rating);
        return $filled . $empty;
    }
}