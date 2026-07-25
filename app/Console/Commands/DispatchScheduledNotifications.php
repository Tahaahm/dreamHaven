<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pushes broadcasts that were created with a future `scheduled_at`.
 *
 * sendBroadcast() writes the rows immediately with `sent_at` set to the
 * scheduled time and `data->scheduled = true`, and skips FCM. This command
 * picks those rows up once their time arrives, sends the push, and flips
 * the flag so they're never sent twice.
 *
 * Register in routes/console.php (Laravel 11+):
 *     Schedule::command('notifications:dispatch-scheduled')->everyMinute()->withoutOverlapping();
 */
class DispatchScheduledNotifications extends Command
{
    protected $signature = 'notifications:dispatch-scheduled
                            {--limit=1000 : Max rows to process in one run}';

    protected $description = 'Send FCM pushes for broadcasts whose scheduled time has arrived';

    public function handle(): int
    {
        $fcm = null;

        try {
            if (class_exists(FirebaseService::class)) {
                $fcm = app(FirebaseService::class);
            }
        } catch (\Throwable $e) {
            $this->error('FirebaseService unavailable: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (!$fcm) {
            $this->warn('FirebaseService not configured — nothing to do.');
            return self::SUCCESS;
        }

        $due = DB::table('notifications')
            ->whereRaw("JSON_EXTRACT(data, '$.scheduled') = true")
            ->where('sent_at', '<=', now())
            ->limit((int) $this->option('limit'))
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled notifications due.');
            return self::SUCCESS;
        }

        $this->info("Dispatching {$due->count()} scheduled notification(s)...");

        $sent   = 0;
        $failed = 0;

        foreach ($due as $row) {
            $data = json_decode($row->data, true) ?: [];

            $payload = [
                'title'   => $row->title,
                'message' => $row->message,
                'image'   => $row->image_url,
            ];

            $fcmData = $this->stringify(array_merge($data, [
                'type'        => $row->type,
                'id'          => $row->id,
                'priority'    => $row->priority,
                'action_url'  => $row->action_url  ?? '',
                'action_text' => $row->action_text ?? '',
                'image_url'   => $row->image_url   ?? '',
                'scheduled'   => false,
            ]));

            $recipient = match (true) {
                (bool) $row->user_id   => User::find($row->user_id),
                (bool) $row->agent_id  => Agent::find($row->agent_id),
                (bool) $row->office_id => RealEstateOffice::find($row->office_id),
                default                => null,
            };

            $ok = false;

            if ($recipient) {
                try {
                    $ok = match (true) {
                        $recipient instanceof User             => (bool) $fcm->sendToUser($recipient, $payload, $fcmData),
                        $recipient instanceof Agent            => (bool) $fcm->sendToAgent($recipient, $payload, $fcmData),
                        $recipient instanceof RealEstateOffice => (bool) $fcm->sendToOffice($recipient, $payload, $fcmData),
                        default                                => false,
                    };
                } catch (\Throwable $e) {
                    Log::error('Scheduled push failed: ' . $e->getMessage(), ['notification_id' => $row->id]);
                }
            }

            $ok ? $sent++ : $failed++;

            // Clear the flag either way so a dead token can't jam the queue.
            $data['scheduled'] = false;

            DB::table('notifications')
                ->where('id', $row->id)
                ->update([
                    'data'       => json_encode($data),
                    'updated_at' => now(),
                ]);
        }

        $this->info("Done. Sent: {$sent}, failed: {$failed}.");

        Log::info('Scheduled notifications dispatched', [
            'sent'   => $sent,
            'failed' => $failed,
        ]);

        return self::SUCCESS;
    }

    private function stringify(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                $out[$key] = '';
            } elseif (is_bool($value)) {
                $out[$key] = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $out[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $out[$key] = (string) $value;
            }
        }

        return $out;
    }
}