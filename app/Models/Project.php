<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $primaryKey = 'project_id';

    protected $fillable = [
        'name',
        'description',
        'status',
        'image',
        'office_id'
    ];

    // Relationships
    public function office()
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id', 'office_id');
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'project_name', 'name');
    }
}
