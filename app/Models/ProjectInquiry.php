<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProjectInquiry extends Model
{
    use HasFactory;

    protected $keyType    = 'string';
    public    $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'project_id',
        'user_id',
        'name',
        'email',
        'phone',
        'country_code',
        'message',
        'inquiry_type',
        'interested_unit_types',
        'budget_range',
        'purpose',
        'preferred_handover_date',
        'source',
        'referral_source',
        'status',
        'internal_notes',
        'assigned_to',
        'contacted_at',
        'follow_up_date',
        'site_visit_date',
        'contact_attempts',
        'last_contact_attempt',
        'email_sent',
        'sms_sent',
        'lead_score',
        'lead_quality',
        'is_qualified',
    ];

    protected $casts = [
        'id'                     => 'string',
        'interested_unit_types'  => 'array',
        'budget_range'           => 'array',
        'contacted_at'           => 'datetime',
        'follow_up_date'         => 'datetime',
        'site_visit_date'        => 'datetime',
        'last_contact_attempt'   => 'datetime',
        'preferred_handover_date'=> 'date',
        'email_sent'             => 'boolean',
        'sms_sent'               => 'boolean',
        'is_qualified'           => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeHot($query)
    {
        return $query->where('lead_quality', 'hot');
    }

    public function scopeQualified($query)
    {
        return $query->where('is_qualified', true);
    }

    public function scopeAssignedTo($query, $agentId)
    {
        return $query->where('assigned_to', $agentId);
    }

    public function scopeNeedsFollowUp($query)
    {
        return $query->where('follow_up_date', '<=', now())
                     ->whereNotIn('status', ['converted', 'not_interested', 'closed']);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function incrementContactAttempts(): void
    {
        $this->update([
            'contact_attempts'    => $this->contact_attempts + 1,
            'last_contact_attempt'=> now(),
        ]);
    }

    public function markAsContacted(): void
    {
        $this->update([
            'status'       => 'contacted',
            'contacted_at' => now(),
        ]);
    }

    public function getLeadQualityColorAttribute(): string
    {
        return match($this->lead_quality) {
            'hot'  => 'text-red-600 bg-red-50',
            'warm' => 'text-orange-600 bg-orange-50',
            'cold' => 'text-blue-600 bg-blue-50',
            default => 'text-gray-600 bg-gray-50',
        };
    }
}