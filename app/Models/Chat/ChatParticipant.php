<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'chat_participants';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id',
        'participant_id',
        'participant_type',
        'role',
        'is_muted',
        'unread_count',
        'last_read_at',
        'left_at',
    ];

    protected $casts = [
        'is_muted'     => 'boolean',
        'unread_count' => 'integer',
        'last_read_at' => 'datetime',
        'left_at'      => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /**
     * Polymorphic: resolves to User, Agent, or RealEstateOffice.
     */
    public function participant(): MorphTo
    {
        return $this->morphTo('participant', 'participant_type', 'participant_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function markAsRead(): void
    {
        $this->update([
            'unread_count' => 0,
            'last_read_at' => now(),
        ]);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasLeft(): bool
    {
        return $this->left_at !== null;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereNull('left_at');
    }

    public function scopeWithUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }
}
