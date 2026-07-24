<?php

/*
| Save as: app/Console/Commands/WarmDashboardCache.php
|
| Then register the schedule. Laravel 11/12 — routes/console.php:
|
|     use Illuminate\Support\Facades\Schedule;
|     Schedule::command('dashboard:warm')->everyFiveMinutes();
|
| Older Laravel — app/Console/Kernel.php schedule():
|
|     $schedule->command('dashboard:warm')->everyFiveMinutes();
|
| Make sure the cron entry exists on the server (you already run cron as
| www-data for Dream Mulk):
|
|     * * * * * cd /var/www/Dream-haven && php artisan schedule:run >> /dev/null 2>&1
|
| Run it once by hand right after installing:
|
|     php artisan dashboard:warm
*/

namespace App\Console\Commands;

use App\Http\Controllers\AdminController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WarmDashboardCache extends Command
{
    protected $signature = 'dashboard:warm';

    protected $description = 'Rebuild the admin dashboard analytics cache so the page loads instantly';

    public function handle(AdminController $admin): int
    {
        $start = microtime(true);

        $this->info('Building dashboard metrics…');

        $heavy = $admin->buildDashboardHeavy();
        Cache::put('admin.dashboard.heavy', $heavy, 3600);

        $core = $admin->buildDashboardCore();
        Cache::put('admin.dashboard.core', $core, 300);

        $seconds = round(microtime(true) - $start, 2);

        // Show which block costs what, so slow spots are obvious
        if (!empty($heavy['_timings'])) {
            $rows = collect($heavy['_timings'])
                ->sortDesc()
                ->map(fn($ms, $block) => [$block, $ms . ' ms'])
                ->values()->all();

            $this->table(['Block', 'Time'], $rows);
        }

        $this->info("Dashboard cache warmed in {$seconds}s.");

        return self::SUCCESS;
    }
}
