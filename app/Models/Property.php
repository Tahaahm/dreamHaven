<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties'; // Ensure this matches your table name
    protected $primaryKey = 'property_id'; // Specify the primary key if it's not 'id'
    public $incrementing = true; // If the primary key is an auto-incrementing integer

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'lat',
        'lng',
        'property_type',
        'bedrooms',
        'bathrooms',
        'parking_spaces',
        'area',
        'furnishing',
        'flooring',
        'water_supply',
        'amenities',
        'price',
        'currency',
        'payment_frequency',
        'images',
        'videos',
        'virtual_tour',
        'additional_information',
        'status',
        'featured',
        'highlighted',
        'views',
        'likes',
        'dislikes',
        'address',
        'comments',
    ];

    protected $casts = [
        'images' => 'array',
        'videos' => 'json',
        'comments' => 'json',
        'featured' => 'boolean',
        'highlighted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
