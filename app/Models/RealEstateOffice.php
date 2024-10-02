<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealEstateOffice extends Model
{
    use HasFactory;

    protected $primaryKey = 'office_id';

    protected $fillable = [
        'office_name',
        'admin_name',
        'admin_email',
        'phone',
        'address',
        'profile_photo',
        'is_verified',
    ];

    // Relationship with agents
    public function agents()
    {
        return $this->hasMany(Agent::class, 'office_id', 'office_id');
    }

    // Relationship with properties
    public function properties()
    {
        return $this->hasMany(Property::class, 'office_id', 'office_id');
    }

    // Relationship with projects
    public function projects()
    {
        return $this->hasMany(Project::class, 'office_id', 'office_id');
    }
}
