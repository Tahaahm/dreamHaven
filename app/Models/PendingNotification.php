<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingNotification extends Model
{
    protected $fillable = [
        'post_id',
        'post_author_type',
        'post_author_id',
        'action_type',
        'actor_ids',
        'actor_types',
        'actor_names',
        'actor_count',
        'last_actor_id',
        'last_actor_type',
        'last_actor_name',
        'last_comment_preview',
        'last_updated_at',
        'cooldown_until',
        'is_flushed',
        'flushed_at',
        'notification_id',
    ];

    protected $casts = [
        'actor_ids'    => 'array',
        'actor_types'  => 'array',
        'actor_names'  => 'array',
        'actor_count'  => 'integer',
        'is_flushed'   => 'boolean',
        'last_updated_at' => 'datetime',
        'cooldown_until'  => 'datetime',
        'flushed_at'      => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────

    /**
     * Ready to flush: not flushed, and either no cooldown or cooldown has passed.
     * Called by the scheduler every 3 minutes.
     */
    // In PendingNotification.php
    public function scopeReadyToFlush($query)
    {
        return $query->where('is_flushed', false)
            ->where(function ($q) {
                $q->whereNull('cooldown_until')
                    ->orWhere('cooldown_until', '<=', now());
            });
    }
    /**
     * In cooldown: a notification was recently sent for this post+action,
     * and we're still in the suppression window.
     */
    public function scopeInCooldown($query)
    {
        return $query->where('cooldown_until', '>', now());
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Build the human-readable notification title for this pending batch.
     *
     * Examples:
     *   "Ahmad liked your post"
     *   "Ahmad and Zana liked your post"
     *   "Ahmad and 12 others liked your post"
     *   "Ahmad commented: Great property!..."
     *   "Ahmad and 5 others commented on your post"
     */
    public function buildTitle(): string
    {
        $count  = $this->actor_count;
        $first  = $this->last_actor_name ?? 'Someone';
        $action = $this->action_type;

        // ── Single actor ──────────────────────────────────────────────────
        if ($count === 1) {
            return match ($action) {
                'like'    => "{$first} liked your post",
                'save'    => "{$first} saved your post",
                'comment' => "{$first} commented on your post",
                default   => "{$first} interacted with your post",
            };
        }

        // ── Two actors ────────────────────────────────────────────────────
        $names  = $this->actor_names ?? [];
        $second = count($names) >= 2 ? $names[count($names) - 2] : null;

        if ($count === 2 && $second) {
            return match ($action) {
                'like'    => "{$first} and {$second} liked your post",
                'save'    => "{$first} and {$second} saved your post",
                'comment' => "{$first} and {$second} commented on your post",
                default   => "{$first} and {$second} interacted with your post",
            };
        }

        // ── Many actors ───────────────────────────────────────────────────
        $others = $count - 1;
        return match ($action) {
            'like'    => "{$first} and {$others} others liked your post",
            'save'    => "{$first} and {$others} others saved your post",
            'comment' => "{$first} and {$others} others commented on your post",
            default   => "{$first} and {$others} others interacted with your post",
        };
    }

    /**
     * Build the notification body (shown as subtitle / expanded text).
     */
    public function buildBody(): string
    {
        $action = $this->action_type;
        $count  = $this->actor_count;

        if ($action === 'comment' && $this->last_comment_preview) {
            $preview = $this->last_comment_preview;
            return $count > 1
                ? "Latest: \"{$preview}\""
                : "\"{$preview}\"";
        }

        if ($action === 'like') {
            return $count === 1
                ? 'Tap to see your post'
                : "Your post has {$count} likes now";
        }

        if ($action === 'save') {
            return $count === 1
                ? 'Someone bookmarked your post'
                : "Your post has been saved {$count} times";
        }

        return 'Tap to see your post';
    }

    /**
     * Multilingual title — used for trilingual FCM payload.
     */
    public function buildTitleMultilingual(): array
    {
        $count  = $this->actor_count;
        $first  = $this->last_actor_name ?? 'Someone';
        $others = $count - 1;
        $action = $this->action_type;

        $templates = [
            'like' => [
                'single' => [
                    'en' => "{$first} liked your post",
                    'ar' => "أعجب {$first} بمنشورك",
                    'ku' => "{$first} پۆستەکەت لایک کرد",
                ],
                'double' => fn($second) => [
                    'en' => "{$first} and {$second} liked your post",
                    'ar' => "أعجب {$first} و{$second} بمنشورك",
                    'ku' => "{$first} و {$second} پۆستەکەت لایک کردن",
                ],
                'many' => [
                    'en' => "{$first} and {$others} others liked your post",
                    'ar' => "أعجب {$first} و{$others} آخرين بمنشورك",
                    'ku' => "{$first} و {$others} کەسی تر پۆستەکەت لایک کردن",
                ],
            ],
            'comment' => [
                'single' => [
                    'en' => "{$first} commented on your post",
                    'ar' => "علّق {$first} على منشورك",
                    'ku' => "{$first} لەسەر پۆستەکەت کۆمێنت کرد",
                ],
                'double' => fn($second) => [
                    'en' => "{$first} and {$second} commented on your post",
                    'ar' => "علّق {$first} و{$second} على منشورك",
                    'ku' => "{$first} و {$second} لەسەر پۆستەکەت کۆمێنت کردن",
                ],
                'many' => [
                    'en' => "{$first} and {$others} others commented on your post",
                    'ar' => "علّق {$first} و{$others} آخرون على منشورك",
                    'ku' => "{$first} و {$others} کەسی تر لەسەر پۆستەکەت کۆمێنت کردن",
                ],
            ],
            'save' => [
                'single' => [
                    'en' => "{$first} saved your post",
                    'ar' => "حفظ {$first} منشورك",
                    'ku' => "{$first} پۆستەکەت پاشەکەوت کرد",
                ],
                'double' => fn($second) => [
                    'en' => "{$first} and {$second} saved your post",
                    'ar' => "حفظ {$first} و{$second} منشورك",
                    'ku' => "{$first} و {$second} پۆستەکەت پاشەکەوت کردن",
                ],
                'many' => [
                    'en' => "{$first} and {$others} others saved your post",
                    'ar' => "حفظ {$first} و{$others} آخرون منشورك",
                    'ku' => "{$first} و {$others} کەسی تر پۆستەکەت پاشەکەوت کردن",
                ],
            ],
        ];

        $t = $templates[$action] ?? $templates['like'];

        if ($count === 1) {
            return $t['single'];
        }

        $names  = $this->actor_names ?? [];
        $second = count($names) >= 2 ? $names[count($names) - 2] : null;

        if ($count === 2 && $second) {
            return ($t['double'])($second);
        }

        return $t['many'];
    }
}