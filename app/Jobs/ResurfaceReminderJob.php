<?php

namespace App\Jobs;

use App\Services\User\UserNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ResurfaceReminderJob
 *
 * Runs every 3 days at 10:00 via Kernel.php scheduler.
 *
 * Finds users who:
 *   - Had a contact_intent signal on a property (tapped WhatsApp/Call/Message)
 *   - BUT no chat conversation was subsequently created for that property
 *   - 3–10 days ago (not too fresh, not too stale)
 *
 * Sends a gentle FCM nudge:
 *   "Still interested? The owner is still available."
 *
 * Rate limits:
 *   - Max 1 resurface per user per 72h
 *   - Max 2 properties per notification batch
 *   - Never resurface if property is no longer active
 */
class ResurfaceReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Window: contact_intent happened 3–10 days ago
    private const MIN_DAYS_AGO = 3;
    private const MAX_DAYS_AGO = 10;

    // Max properties to resurface per user per run
    private const MAX_PER_USER = 2;

    // Cooldown: don't resurface same user within 72h
    private const COOLDOWN_HOURS = 72;

    public $timeout = 180;
    public $tries   = 2;

    public function handle(UserNotificationService $notifService): void
    {
        Log::info('ResurfaceReminderJob: starting');

        $candidates = $this->findCandidates();

        if (empty($candidates)) {
            Log::info('ResurfaceReminderJob: no candidates found');
            return;
        }

        Log::info("ResurfaceReminderJob: {$candidates->count()} user-property pairs to resurface");

        // Group by user, send max MAX_PER_USER per user
        $grouped      = $candidates->groupBy('user_id');
        $notifsSent   = 0;
        $cooldownKey  = fn($uid) => "resurfaced_{$uid}";

        foreach ($grouped as $userId => $userCandidates) {
            // Respect cooldown
            if (Cache::has($cooldownKey($userId))) continue;

            $top = $userCandidates->take(self::MAX_PER_USER);
            $lang = $userCandidates->first()->preferred_language ?? 'en';

            foreach ($top as $candidate) {
                $this->sendReminderNotification($notifService, $userId, $candidate, $lang);
                $notifsSent++;
                usleep(50000); // 50ms
            }

            // Set cooldown for this user
            Cache::put($cooldownKey($userId), true, now()->addHours(self::COOLDOWN_HOURS));
        }

        Log::info("ResurfaceReminderJob: sent {$notifsSent} reminders");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  FIND CANDIDATES
    // ─────────────────────────────────────────────────────────────────────────

    private function findCandidates()
    {
        $minDate = now()->subDays(self::MAX_DAYS_AGO);
        $maxDate = now()->subDays(self::MIN_DAYS_AGO);

        // Find contact_intent signals with no subsequent chat for that property
        return DB::table('user_property_interactions AS upi')
            ->select(
                'upi.user_id',
                'upi.property_id',
                'upi.created_at AS intent_at',
                'p.name         AS property_name',
                'p.price',
                'p.currency',
                'p.images',
                'u.preferred_language',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(upi.metadata, '$.contact_method')) AS contact_method")
            )
            ->join('properties AS p', function ($join) {
                $join->on('p.id', '=', 'upi.property_id')
                    ->where('p.is_active',    true)
                    ->where('p.is_published', true);
            })
            ->join('users AS u', 'u.id', '=', 'upi.user_id')
            ->where('upi.interaction_type', 'contact_intent')
            ->whereBetween('upi.created_at', [$minDate, $maxDate])
            // No chat conversation exists for this property-user pair
            ->whereNotExists(function ($query) {
                $query->from('conversations AS c')
                    ->join('conversation_participants AS cp', 'cp.conversation_id', '=', 'c.id')
                    ->whereColumn('c.property_id',   'upi.property_id')
                    ->whereColumn('cp.participant_id', 'upi.user_id')
                    ->where('c.created_at', '>=', DB::raw('upi.created_at'));
            })
            // Not already resurfaced (check notification log)
            ->whereNotExists(function ($query) {
                $query->from('user_notifications AS un')
                    ->whereColumn('un.user_id', 'upi.user_id')
                    ->where('un.type', 'resurface_reminder')
                    ->whereColumn('un.data->>"$.property_id"', 'upi.property_id')
                    ->where('un.created_at', '>=', now()->subHours(self::COOLDOWN_HOURS));
            })
            ->orderByDesc('upi.created_at')
            ->get();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  SEND NOTIFICATION
    // ─────────────────────────────────────────────────────────────────────────

    private function sendReminderNotification(
        UserNotificationService $notifService,
        int $userId,
        object $candidate,
        string $lang
    ): void {
        $propName = $candidate->property_name;
        if (is_string($propName)) {
            $decoded = json_decode($propName, true);
            if (is_array($decoded)) {
                $propName = $decoded[$lang] ?? $decoded['en'] ?? $propName;
            }
        }

        $price    = number_format($candidate->price, 0);
        $currency = $candidate->currency ?? 'USD';

        // Pick first image as thumbnail
        $images = [];
        if ($candidate->images) {
            $imgs = is_string($candidate->images)
                ? json_decode($candidate->images, true)
                : $candidate->images;
            $images = is_array($imgs) ? $imgs : [];
        }
        $thumbnail = $images[0] ?? null;

        $messages = [
            'en' => [
                'title' => '👀 Still interested?',
                'body'  => "The owner of \"{$propName}\" is still available. Don't miss out.",
            ],
            'ar' => [
                'title' => '👀 لا تزال مهتمًا؟',
                'body'  => "مالك \"{$propName}\" لا يزال متاحًا. لا تفوّت الفرصة.",
            ],
            'ku' => [
                'title' => '👀 هێشتا حازیت؟',
                'body'  => "خاوەنی \"{$propName}\" هێشتا بەردەستە. فەرسەتەکە لەدەست مەدە.",
            ],
        ];

        $msg = $messages[$lang] ?? $messages['en'];

        try {
            $notifService->sendToUser($userId, [
                'title'        => $msg['title'],
                'body'         => $msg['body'],
                'type'         => 'resurface_reminder',
                'property_id'  => $candidate->property_id,
                'property_name' => $propName,
                'price'        => $candidate->price,
                'currency'     => $currency,
                'thumbnail'    => $thumbnail,
                'deep_link'    => "dreammulk://property/{$candidate->property_id}",
            ]);
        } catch (\Throwable $e) {
            Log::warning("ResurfaceJob: FCM failed for user {$userId}: {$e->getMessage()}");
        }
    }
}