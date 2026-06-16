<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new \App\Jobs\FlushPendingFeedNotificationsJob)
    ->everyThirtyMinutes();

Schedule::command('queue:work --queue=notifications,default --stop-when-empty')
    ->hourly()
    ->withoutOverlapping();

Schedule::job(new \App\Jobs\SendPropertyMatchDigestJob)
    ->everyThreeDays()
    ->at('09:00')
    ->withoutOverlapping();

Schedule::job(new \App\Jobs\PriceDropDetectionJob)
    ->dailyAt('08:00')
    ->withoutOverlapping();

Schedule::job(new \App\Jobs\ResurfaceReminderJob)
    ->cron('0 10 */3 * *')
    ->withoutOverlapping();