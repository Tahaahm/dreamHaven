<?php

namespace App\Jobs;

use App\Models\Property;
use App\Services\User\UserNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PriceDropDetectionJob
 *
 * Runs daily at 08:00 via Kernel.php scheduler.
 *
 * What it does:
 *  1. Snapshots current prices of all active properties into price_snapshots table
 *  2. Compares with yesterday's snapshot → finds price drops ≥ 2%
 *  3. Finds users who interacted with those properties (view, favorite, contact_intent)
 *  4. Sends FCM push notification: "Price dropped on a property you watched"
 *
 * Migration needed (run once):
 *   php artisan make:migration create_price_snapshots_table
 *
 * Schema:
 *   price_snapshots:
 *     id, property_id (FK), price (decimal 15,2), currency (varchar),
 *     snapped_at (timestamp), created_at
 *     INDEX: (property_id, snapped_at)
 */
class PriceDropDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Minimum drop % to trigger notification (avoids noise from tiny adjustments)
    private const MIN_DROP_PERCENT = 2.0;

    // How far back to look for user interactions with this property
    private const INTERACTION_LOOKBACK_DAYS = 60;

    // Max notifications per user per day (don't spam)
    private const MAX_NOTIFS_PER_USER = 3;

    public $timeout = 300;
    public $tries   = 2;

    public function handle(UserNotificationService $notifService): void
    {
        Log::info('PriceDropDetectionJob: starting');

        // ── STEP 1: Snapshot current prices ──────────────────────────────────
        $this->snapshotPrices();

        // ── STEP 2: Detect drops ──────────────────────────────────────────────
        $drops = $this->detectDrops();

        if (empty($drops)) {

            Log::info('PriceDropDetectionJob: no price drops detected today');
            return;
        }

        Log::info('PriceDropDetectionJob: ' . count($drops) . ' properties with price drops');


        // ── STEP 3: Notify interested users ───────────────────────────────────
        $notifsSent   = 0;
        $userNotifCount = [];

        foreach ($drops as $drop) {
            $interestedUsers = $this->getInterestedUsers($drop->property_id);

            foreach ($interestedUsers as $user) {
                $userId = $user->user_id;

                // Rate limit per user
                $userNotifCount[$userId] = ($userNotifCount[$userId] ?? 0) + 1;
                if ($userNotifCount[$userId] > self::MAX_NOTIFS_PER_USER) continue;

                $this->sendPriceDropNotification(
                    $notifService,
                    $userId,
                    $drop,
                    $user->preferred_language ?? 'en'
                );
                $notifsSent++;

                // Small sleep to avoid FCM rate limits
                usleep(50000); // 50ms
            }
        }

        Log::info("PriceDropDetectionJob: sent {$notifsSent} notifications");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  STEP 1: Snapshot
    // ─────────────────────────────────────────────────────────────────────────

    private function snapshotPrices(): void
    {
        // Chunk to avoid memory issues on large property sets
        Property::where('is_active', true)
            ->where('is_published', true)
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

        // Clean up snapshots older than 30 days to keep table lean
        DB::table('price_snapshots')
            ->where('snapped_at', '<', now()->subDays(30))
            ->delete();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  STEP 2: Detect drops
    // ─────────────────────────────────────────────────────────────────────────

    private function detectDrops()
    {
        // Compare today's snapshot with the most recent previous snapshot
        return DB::select("
            SELECT
                today.property_id,
                today.price          AS new_price,
                prev.price           AS old_price,
                today.currency,
                ROUND(((prev.price - today.price) / prev.price) * 100, 2) AS drop_percent,
                p.name               AS property_name
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
              AND p.is_active    = 1
              AND p.is_published = 1
            ORDER BY drop_percent DESC
        ", [self::MIN_DROP_PERCENT]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  STEP 3: Find interested users
    // ─────────────────────────────────────────────────────────────────────────

    private function getInterestedUsers(string $propertyId)
    {
        $since = now()->subDays(self::INTERACTION_LOOKBACK_DAYS);

        // Users who had any meaningful interaction with this property
        // Order by intent strength (contact_intent first)
        return DB::select("
            SELECT
                upi.user_id,
                u.preferred_language,
                MAX(
                    CASE upi.interaction_type
                        WHEN 'contact_intent'    THEN 6
                        WHEN 'favorite'          THEN 5
                        WHEN 'return_to_listing' THEN 4
                        WHEN 'compare'           THEN 3
                        WHEN 'view'              THEN 1
                        ELSE 1
                    END
                ) AS intent_level
            FROM user_property_interactions upi
            JOIN users u ON u.id = upi.user_id
            WHERE upi.property_id = ?
              AND upi.created_at  >= ?
              AND u.id            IS NOT NULL
            GROUP BY upi.user_id, u.preferred_language
            ORDER BY intent_level DESC
            LIMIT 200
        ", [$propertyId, $since]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  FCM Notification
    // ─────────────────────────────────────────────────────────────────────────

    private function sendPriceDropNotification(
        UserNotificationService $notifService,
        int $userId,
        object $drop,
        string $lang
    ): void {
        $dropPct    = number_format($drop->drop_percent, 0);
        $newPrice   = number_format($drop->new_price, 0);
        $currency   = $drop->currency;

        $propName = $drop->property_name;
        if (is_string($propName)) {
            $decoded = json_decode($propName, true);
            if (is_array($decoded)) {
                $propName = $decoded[$lang] ?? $decoded['en'] ?? $propName;
            }
        }

        $messages = [
            'en' => [
                'title' => "💰 Price Drop — {$dropPct}% off!",
                'body'  => "A property you watched just dropped in price. Now {$currency} {$newPrice}.",
            ],
            'ar' => [
                'title' => "💰 انخفاض السعر — {$dropPct}%!",
                'body'  => "انخفض سعر عقار كنت تتابعه. الآن {$newPrice} {$currency}.",
            ],
            'ku' => [
                'title' => "💰 نرخ کەم بووەوە — {$dropPct}%!",
                'body'  => "نرخی خانووێک کە تەماشات دەکرد کەم بوو. ئێستا {$newPrice} {$currency}.",
            ],
        ];

        $msg = $messages[$lang] ?? $messages['en'];

        try {
            $notifService->sendToUser($userId, [
                'title'       => $msg['title'],
                'body'        => $msg['body'],
                'type'        => 'price_drop',
                'property_id' => $drop->property_id,
                'drop_percent' => $drop->drop_percent,
                'new_price'   => $drop->new_price,
                'old_price'   => $drop->old_price,
                'currency'    => $currency,
                'deep_link'   => "dreammulk://property/{$drop->property_id}",
            ]);
        } catch (\Throwable $e) {
            Log::warning("PriceDropJob: FCM failed for user {$userId}: {$e->getMessage()}");
        }
    }
}