<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id', 
        'reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // Add this method to get the count of reports
    public function getReportsCountAttribute()
    {
        return $this->reports()->count();
    }
}