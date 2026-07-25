<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Jobs
|--------------------------------------------------------------------------
*/

Schedule::job(new \App\Jobs\FlushPendingFeedNotificationsJob)
    ->everyThirtyMinutes();

/*
| Run queue worker hourly (only if you really need this approach)
*/
Schedule::command('queue:work --queue=notifications,default --stop-when-empty')
    ->hourly()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Scheduled Broadcasts - every minute
|--------------------------------------------------------------------------
| Picks up broadcasts created with a future `scheduled_at` and fires the
| FCM push once their time arrives. Rows are already in the DB (written by
| sendBroadcast with data->scheduled = true); this only sends the push.
| Cheap no-op when nothing is due.
*/
Schedule::command('notifications:dispatch-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

/*
|--------------------------------------------------------------------------
| Admin Dashboard Cache Warm - every 5 minutes
|--------------------------------------------------------------------------
| Rebuilds buildDashboardHeavy() in the background so no admin ever pays
| the ~10s analytics cost on a cold cache.
*/
Schedule::command('dashboard:warm')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

/*
|--------------------------------------------------------------------------
| Property Match Digest - every 3 days at 09:00
|--------------------------------------------------------------------------
*/
Schedule::job(new \App\Jobs\SendPropertyMatchDigestJob)
    ->cron('0 9 */3 * *') // every 3rd day of month at 09:00
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Price Drop Detection - daily at 08:00
|--------------------------------------------------------------------------
*/
Schedule::job(new \App\Jobs\PriceDropDetectionJob)
    ->dailyAt('08:00')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Resurface Reminder - every 3 days at 10:00
|--------------------------------------------------------------------------
*/
Schedule::job(new \App\Jobs\ResurfaceReminderJob)
    ->cron('0 10 */3 * *')
    ->withoutOverlapping();