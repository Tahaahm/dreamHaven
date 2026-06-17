<?php

namespace App\Jobs;

use App\Models\Property;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\User\UserNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriceDropDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const MIN_DROP_PERCENT          = 2.0;
    private const INTERACTION_LOOKBACK_DAYS = 60;
    private const MAX_NOTIFS_PER_USER       = 3;

    public $timeout = 300;
    public $tries   = 2;

    public function handle(): void
    {
        Log::info('PriceDropDetectionJob: starting');

        $this->snapshotPrices();

        $drops = $this->detectDrops();

        if (empty($drops)) {
            Log::info('PriceDropDetectionJob: no price drops detected today');
            return;
        }

        Log::info('PriceDropDetectionJob: ' . count($drops) . ' properties with price drops');

        $notifService   = app(UserNotificationService::class);
        $firebase       = app(FirebaseService::class);
        $userNotifCount = [];
        $notifsSent     = 0;

        foreach ($drops as $drop) {
            $interestedUsers = $this->getInterestedUsers($drop->property_id);

            foreach ($interestedUsers as $userData) {
                $userId = $userData->user_id;
                $userNotifCount[$userId] = ($userNotifCount[$userId] ?? 0) + 1;
                if ($userNotifCount[$userId] > self::MAX_NOTIFS_PER_USER) continue;

                $user = User::find($userId);
                if (!$user) continue;

                $lang = strtolower(trim($user->language ?? 'en'));
                $msg  = $this->buildMessage($drop, $lang);

                // Store in DB
                $notifService->createNotification([
                    'user_id'     => $userId,
                    'title'       => $msg['title'],
                    'message'     => $msg['body'],
                    'type'        => 'price_drop',
                    'priority'    => 'high',
                    'data'        => [
                        'property_id'  => $drop->property_id,
                        'drop_percent' => $drop->drop_percent,
                        'new_price'    => $drop->new_price,
                        'old_price'    => $drop->old_price,
                        'currency'     => $drop->currency,
                    ],
                    'action_url'  => "/properties/{$drop->property_id}",
                    'action_text' => 'View Property',
                    'expires_at'  => now()->addDays(7),
                ]);

                // Send FCM
                try {
                    $firebase->sendToUser($user, [
                        'title' => $msg['title'],
                        'body'  => $msg['body'],
                    ], [
                        'type'         => 'price_drop',
                        'property_id'  => (string) $drop->property_id,
                        'drop_percent' => (string) $drop->drop_percent,
                        'new_price'    => (string) $drop->new_price,
                        'deep_link'    => "dreammulk://property/{$drop->property_id}",
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("PriceDropJob: FCM failed for user {$userId}: {$e->getMessage()}");
                }

                $notifsSent++;
                usleep(50000);
            }
        }

        Log::info("PriceDropDetectionJob: sent {$notifsSent} notifications");
    }

    private function snapshotPrices(): void
    {
        Property::where('is_active', true)
            ->where('published', true)
            ->select('id', 'price', 'currency')
            ->chunk(500, function ($properties) {
                $rows = $properties->map(fn($p) => [
                    'property_id' => $p->id,
                    'price'       => $p->price,
                    'currency'    => $p->currency ?? 'USD',
                    'snapped_at'  => now(),
                    'created_at'  => now(),
                ])->toArray();
                DB::table('price_snapshots')->insert($rows);
            });

        DB::table('price_snapshots')
            ->where('snapped_at', '<', now()->subDays(30))
            ->delete();
    }

    private function detectDrops(): array
    {
        return DB::select("
            SELECT
                today.property_id,
                today.price         AS new_price,
                prev.price          AS old_price,
                today.currency,
                ROUND(((prev.price - today.price) / prev.price) * 100, 2) AS drop_percent,
                p.name              AS property_name
            FROM price_snapshots today
            JOIN price_snapshots prev
                ON  today.property_id = prev.property_id
                AND prev.snapped_at   = (
                    SELECT MAX(ps2.snapped_at)
                    FROM price_snapshots ps2
                    WHERE ps2.property_id = today.property_id
                      AND ps2.snapped_at  < today.snapped_at
                )
            JOIN properties p ON p.id = today.property_id
            WHERE today.snapped_at >= CURDATE()
              AND today.price < prev.price
              AND ((prev.price - today.price) / prev.price) * 100 >= ?
              AND p.is_active  = 1
              AND p.published  = 1
            ORDER BY drop_percent DESC
        ", [self::MIN_DROP_PERCENT]);
    }

    private function getInterestedUsers(string $propertyId): array
    {
        return DB::select("
            SELECT upi.user_id, u.language
            FROM user_property_interactions upi
            JOIN users u ON u.id = upi.user_id
            WHERE upi.property_id = ?
              AND upi.created_at  >= ?
            GROUP BY upi.user_id, u.language
            ORDER BY MAX(
                CASE upi.interaction_type
                    WHEN 'contact_intent'    THEN 6
                    WHEN 'favorite'          THEN 5
                    WHEN 'return_to_listing' THEN 4
                    ELSE 1
                END
            ) DESC
            LIMIT 200
        ", [$propertyId, now()->subDays(self::INTERACTION_LOOKBACK_DAYS)]);
    }

    private function buildMessage(object $drop, string $lang): array
    {
        $pct      = number_format($drop->drop_percent, 0);
        $newPrice = number_format($drop->new_price, 0);
        $currency = $drop->currency;

        $messages = [
            'en' => [
                'title' => "💰 Price Drop — {$pct}% off!",
                'body'  => "A property you watched just dropped in price. Now {$currency} {$newPrice}.",
            ],
            'ar' => [
                'title' => "💰 انخفاض السعر — {$pct}%!",
                'body'  => "انخفض سعر عقار كنت تتابعه. الآن {$newPrice} {$currency}.",
            ],
            'ku' => [
                'title' => "💰 نرخ کەم بووەوە — {$pct}%!",
                'body'  => "نرخی خانووێک کە تەماشات دەکرد کەم بوو. ئێستا {$newPrice} {$currency}.",
            ],
        ];

        return $messages[$lang] ?? $messages['en'];
    }
}
