<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'user_id',
        'agent_id',
        'office_id',
        'title',
        'message',
        'is_read',
        'sent_at'
    ];

    // Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relationship with agent
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'agent_id');
    }

    // Relationship with real estate office
    public function office()
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id', 'office_id');
    }
}
