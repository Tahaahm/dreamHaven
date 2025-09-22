<?php

namespace App\Models\Support;

use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class OfficeSocialMedia extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id',
        'platform',
        'username',
        'profile_url',
        'sort_order'
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }
}