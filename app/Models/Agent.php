<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $primaryKey = 'agent_id';

    protected $fillable = [
        'agent_name',
        'email',
        'phone',
        'office_id',
        'is_verified',
        'profile_photo',
    ];

    // Relationship with the real estate office
    public function office()
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id', 'office_id');
    }

    // Relationship with properties the agent manages
    public function properties()
    {
        return $this->hasMany(Property::class, 'agent_id', 'agent_id');
    }
}
