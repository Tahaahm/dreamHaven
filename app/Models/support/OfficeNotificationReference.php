<?php

namespace App\Models\Support;

use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class OfficeNotificationReference extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id',
        'notification_id',
        'notification_date',
        'notification_status',
        'title',
        'message',
        'type'
    ];

    protected function casts(): array
    {
        return [
            'notification_date' => 'datetime',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }
}