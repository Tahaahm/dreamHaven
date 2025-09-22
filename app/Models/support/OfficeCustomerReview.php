<?php

namespace App\Models\Support;

use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



class OfficeCustomerReview extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id',
        'reviewer_name',
        'reviewer_avatar',
        'star_rating',
        'review_content',
        'review_date',
        'property_type',
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

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }
}