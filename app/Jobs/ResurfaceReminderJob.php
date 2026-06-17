<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FirebaseService;
use App\Services\User\UserNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResurfaceReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const MIN_DAYS_AGO    = 3;
    private const MAX_DAYS_AGO    = 10;
    private const MAX_PER_USER    = 2;
    private const COOLDOWN_HOURS  = 72;

    public $timeout = 180;
    public $tries   = 2;

    public function handle(): void
    {
        Log::info('ResurfaceReminderJob: starting');

        $candidates = $this->findCandidates();

        if ($candidates->isEmpty()) {
            Log::info('ResurfaceReminderJob: no candidates found');
            return;
        }

        Log::info("ResurfaceReminderJob: {$candidates->count()} candidates");

        $notifService   = app(UserNotificationService::class);
        $firebase       = app(FirebaseService::class);
        $grouped        = $candidates->groupBy('user_id');
        $notifsSent     = 0;

        foreach ($grouped as $userId => $userCandidates) {
            $cooldownKey = "resurfaced_{$userId}";
            if (Cache::has($cooldownKey)) continue;

            $top  = $userCandidates->take(self::MAX_PER_USER);
            $lang = $userCandidates->first()->language ?? 'en';
            $lang = strtolower(trim($lang));

            $user = User::find($userId);
            if (!$user) continue;

            foreach ($top as $candidate) {
                $msg = $this->buildMessage($candidate, $lang);

                // Store in DB
                $notifService->createNotification([
                    'user_id'     => $userId,
                    'title'       => $msg['title'],
                    'message'     => $msg['body'],
                    'type'        => 'resurface_reminder',
                    'priority'    => 'medium',
                    'data'        => [
                        'property_id' => $candidate->property_id,
                    ],
                    'action_url'  => "/properties/{$candidate->property_id}",
                    'action_text' => 'View Property',
                    'expires_at'  => now()->addDays(3),
                ]);

                // Send FCM
                try {
                    $firebase->sendToUser($user, [
                        'title' => $msg['title'],
                        'body'  => $msg['body'],
                    ], [
                        'type'        => 'resurface_reminder',
                        'property_id' => (string) $candidate->property_id,
                        'deep_link'   => "dreammulk://property/{$candidate->property_id}",
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("ResurfaceJob: FCM failed for user {$userId}: {$e->getMessage()}");
                }

                $notifsSent++;
                usleep(50000);
            }

            Cache::put($cooldownKey, true, now()->addHours(self::COOLDOWN_HOURS));
        }

        Log::info("ResurfaceReminderJob: sent {$notifsSent} reminders");
    }

    private function findCandidates()
    {
        $minDate = now()->subDays(self::MAX_DAYS_AGO);
        $maxDate = now()->subDays(self::MIN_DAYS_AGO);

        return DB::table('user_property_interactions AS upi')
            ->select(
                'upi.user_id',
                'upi.property_id',
                'upi.created_at AS intent_at',
                'p.name         AS property_name',
                'p.price',
                'p.currency',
                'u.language'
            )
            ->join('properties AS p', function ($join) {
                $join->on('p.id', '=', 'upi.property_id')
                    ->where('p.is_active',  true)
                    ->where('p.published',  true);
            })
            ->join('users AS u', 'u.id', '=', 'upi.user_id')
            ->where('upi.interaction_type', 'contact_intent')
            ->whereBetween('upi.created_at', [$minDate, $maxDate])
            ->whereNotExists(function ($query) {
                $query->from('conversations AS c')
                    ->join('conversation_participants AS cp', 'cp.conversation_id', '=', 'c.id')
                    ->whereColumn('c.property_id',    'upi.property_id')
                    ->whereColumn('cp.participant_id', 'upi.user_id')
                    ->where('c.created_at', '>=', DB::raw('upi.created_at'));
            })
            ->orderByDesc('upi.created_at')
            ->get();
    }

    private function buildMessage(object $candidate, string $lang): array
    {
        $propName = $candidate->property_name;
        if (is_string($propName)) {
            $decoded = json_decode($propName, true);
            if (is_array($decoded)) {
                $propName = $decoded[$lang] ?? $decoded['en'] ?? $propName;
            }
        }

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

        return $messages[$lang] ?? $messages['en'];
    }
}
