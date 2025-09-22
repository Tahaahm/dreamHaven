<?php

namespace App\Models\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAppointment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'appointment_id',
        'appointment_title',
        'with_whom',
        'appointment_date',
        'description',
        'status',
        'notes'
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
