<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $primaryKey = 'review_id';

    protected $fillable = [
        'user_id',
        'property_id',
        'rating',
        'comment'
    ];

    // Relationship with the user who wrote the review
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relationship with the property being reviewed
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
