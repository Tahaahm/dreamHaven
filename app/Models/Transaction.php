<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Transaction Model
class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'transaction_reference',
        'property_id',
        'buyer_user_id',
        'seller_user_id',
        'agent_id',
        'office_id',
        'type',
        'status',
        'amount_iqd',
        'amount_usd',
        'commission_amount',
        'commission_rate',
        'payment_method',
        'payment_status',
        'contract_number',
        'contract_date',
        'completion_date',
        'started_at',
        'completed_at',
        'cancelled_at',
        'notes',
        'payment_breakdown',
        'documents',
    ];

    protected function casts(): array
    {
        return [
            'amount_iqd' => 'decimal:2',
            'amount_usd' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'contract_date' => 'datetime',
            'completion_date' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'payment_breakdown' => 'array',
            'documents' => 'array',
        ];
    }

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    // Methods
    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'payment_status' => 'completed',
        ]);
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    // Accessors
    public function getTotalAmountAttribute()
    {
        return $this->amount_iqd + ($this->amount_usd * $this->getUsdToIqdRate());
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'failed' => 'danger',
            default => 'secondary'
        };
    }

    // Helper method for currency conversion (you can implement actual rates)
    private function getUsdToIqdRate()
    {
        return 1320; // Example rate - implement actual conversion logic
    }
}

// Session Model
class Session extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
    ];

    protected function casts(): array
    {
        return [
            'last_activity' => 'integer',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeActive($query, $minutes = 30)
    {
        return $query->where('last_activity', '>', now()->subMinutes($minutes)->timestamp);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Methods
    public function isActive($minutes = 30)
    {
        return $this->last_activity > now()->subMinutes($minutes)->timestamp;
    }

    // Accessors
    public function getLastActivityDateAttribute()
    {
        return \Carbon\Carbon::createFromTimestamp($this->last_activity);
    }

    public function getBrowserAttribute()
    {
        // Simple user agent parsing (you can use a proper library)
        if (str_contains($this->user_agent, 'Chrome')) return 'Chrome';
        if (str_contains($this->user_agent, 'Firefox')) return 'Firefox';
        if (str_contains($this->user_agent, 'Safari')) return 'Safari';
        if (str_contains($this->user_agent, 'Edge')) return 'Edge';
        return 'Unknown';
    }

    public function getDeviceTypeAttribute()
    {
        if (str_contains($this->user_agent, 'Mobile')) return 'Mobile';
        if (str_contains($this->user_agent, 'Tablet')) return 'Tablet';
        return 'Desktop';
    }
}