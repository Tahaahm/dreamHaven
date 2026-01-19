<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPropertyInteraction extends Model
{
    protected $fillable = [
        'user_id',
        'property_id',
        'interaction_type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }
}
