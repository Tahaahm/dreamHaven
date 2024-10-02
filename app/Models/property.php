<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class property extends Model
{
    use HasFactory;

    protected $primaryKey = 'property_id';

    protected $fillable = [
        'title',
        'description',
        'price',
        'address',
        'location',
        'property_type',
        'bedrooms',
        'bathrooms',
        'kitchen_rooms',
        'reception_rooms',
        'area',
        'images',
        'video_tour',
        'amenities',
        'project_name',
        'project_description',
        'status',
        'listing_type',
        'rating',
        'views',
        'favorites_count',
        'availability',
        'agent_id',
        'office_id'
    ];

    // Relationships
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'agent_id');
    }

    public function office()
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id', 'office_id');
    }

    public function reviews()
{
    return $this->hasMany(Review::class, 'property_id', 'property_id');
}


}
