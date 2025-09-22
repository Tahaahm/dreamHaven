<?php

namespace App\Models\Support;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class UserFavoriteProperty extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'property_id',
        'favorited_at',
        'notes'
    ];

    protected function casts(): array
    {
        return [
            'favorited_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
