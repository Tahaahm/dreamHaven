<?php

// app/Models/PropertyBoost.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PropertyBoost extends Model
{
    protected $fillable = [
        'property_id',
        'owner_id',
        'owner_type',
        'plan_id',
        'plan_name',
        'amount_paid',
        'currency',
        'payment_ref',
        'payment_method',
        'status',
        'start_date',
        'end_date',
        'cancelled_at',
        'views_at_start',
        'reach_at_start',
        'meta',
    ];

    protected $casts = [
        'start_date'   => 'datetime',
        'end_date'     => 'datetime',
        'cancelled_at' => 'datetime',
        'amount_paid'  => 'decimal:2',
        'meta'         => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function isActive(): bool
    {
        return $this->status === 'active'
            && Carbon::parse($this->end_date)->isFuture();
    }

    public function daysRemaining(): int
    {
        if (!$this->isActive()) return 0;
        return max(0, (int) now()->diffInDays(Carbon::parse($this->end_date), false));
    }

    // Auto-expire boosts that have passed their end_date
    // Call from a scheduled command: php artisan boost:expire
    public static function expireOld(): int
    {
        return static::where('status', 'active')
            ->where('end_date', '<=', now())
            ->update(['status' => 'expired']);
    }
}