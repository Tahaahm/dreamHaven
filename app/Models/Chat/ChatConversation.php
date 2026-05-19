<?php

namespace App\Models\Chat;

use App\Models\Agent;
use App\Models\Property;
use App\Models\RealEstateOffice;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ChatConversation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'chat_conversations';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'type',
        'name',
        'avatar',
        'created_by',
        'created_by_type',
        'last_message',
        'last_message_type',
        'last_message_sender_id',
        'last_message_sender_type',
        'last_message_at',
        'property_id',
        'expires_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'expires_at'      => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function participants(): HasMany
    {
        return $this->hasMany(ChatParticipant::class, 'conversation_id');
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(ChatParticipant::class, 'conversation_id')
            ->whereNull('left_at');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ChatMedia::class, 'conversation_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Reset the 30-day expiry window.
     * Called every time a new message is sent so active chats are never purged.
     */
    public function refreshExpiry(): void
    {
        $this->update([
            'expires_at' => Carbon::now()->addDays(30),
        ]);
    }

    /**
     * Update the last-message preview shown in the inbox.
     */
    public function updateLastMessage(
        string $text,
        string $type,
        string $senderId,
        string $senderType
    ): void {
        $this->update([
            'last_message'             => $text,
            'last_message_type'        => $type,
            'last_message_sender_id'   => $senderId,
            'last_message_sender_type' => $senderType,
            'last_message_at'          => Carbon::now(),
            'expires_at'               => Carbon::now()->addDays(30),
        ]);
    }

    /**
     * Check whether a given actor (by ID + morph type) is in this conversation.
     */
    public function hasParticipant(string $participantId, string $participantType): bool
    {
        return $this->participants()
            ->where('participant_id', $participantId)
            ->where('participant_type', $participantType)
            ->whereNull('left_at')
            ->exists();
    }

    /**
     * Increment unread count for every participant except the sender.
     */
    public function incrementUnreadForOthers(string $senderId, string $senderType): void
    {
        $this->participants()
            ->where(function ($q) use ($senderId, $senderType) {
                $q->where('participant_id', '!=', $senderId)
                    ->orWhere('participant_type', '!=', $senderType);
            })
            ->whereNull('left_at')
            ->increment('unread_count');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    /**
     * Conversations that belong to a given actor.
     */
    public function scopeForParticipant($query, string $participantId, string $participantType)
    {
        return $query->whereHas('participants', function ($q) use ($participantId, $participantType) {
            $q->where('participant_id', $participantId)
                ->where('participant_type', $participantType)
                ->whereNull('left_at');
        });
    }

    /**
     * Conversations past their expiry date (ready for purge).
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }

    // ── Static factory helpers ─────────────────────────────────────────────────

    /**
     * Find an existing direct conversation between two participants,
     * or return null if none exists.
     */
    public static function findDirect(
        string $participantAId,
        string $participantAType,
        string $participantBId,
        string $participantBType
    ): ?self {
        return self::where('type', 'direct')
            ->whereHas('participants', function ($q) use ($participantAId, $participantAType) {
                $q->where('participant_id', $participantAId)
                    ->where('participant_type', $participantAType);
            })
            ->whereHas('participants', function ($q) use ($participantBId, $participantBType) {
                $q->where('participant_id', $participantBId)
                    ->where('participant_type', $participantBType);
            })
            ->first();
    }

    /**
     * Resolve the morph type string for a given role string.
     * Matches the strings stored in participant_type column.
     */
    public static function morphTypeFor(string $role): string
    {
        return match ($role) {
            'agent'  => Agent::class,
            'office' => RealEstateOffice::class,
            default  => User::class,
        };
    }
}
