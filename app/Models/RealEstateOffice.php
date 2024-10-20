<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;



class RealEstateOffice extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'office_id';

    protected $fillable = [
        'office_name',
        'admin_name',
        'admin_email',
        'password',
        'phone',
        'address',
        'profile_photo',
        'description',   // Include description
        'location',      // Include location
        'is_verified',
    ];



    protected $hidden = [
        'password', 'remember_token',
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

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
