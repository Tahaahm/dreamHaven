<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class ChatMedia extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'chat_media';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id',
        'firestore_message_id',
        'uploader_id',
        'uploader_type',
        'disk',
        'path',
        'url',
        'mime_type',
        'size_bytes',
        'type',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function uploader(): MorphTo
    {
        return $this->morphTo('uploader', 'uploader_type', 'uploader_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Delete the physical file from disk when the model is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (ChatMedia $media) {
            if (Storage::disk($media->disk)->exists($media->path)) {
                Storage::disk($media->disk)->delete($media->path);
            }
        });
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function sizeInKb(): float
    {
        return round($this->size_bytes / 1024, 2);
    }
}
