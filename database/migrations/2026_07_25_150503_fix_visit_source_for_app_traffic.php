<?php

/*
| Create with:
|     php artisan make:migration fix_visit_source_for_app_traffic
|
| Then paste this in. Running `php artisan migrate` on the server corrects the
| rows that were already stored as 'web' when they actually came from the app —
| no manual SQL, no server edits, no conflicts.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('visits')) {
            return;
        }

        // Any visit that landed on an API path came from the mobile app
        DB::table('visits')
            ->where('source', '!=', 'app')
            ->where(function ($q) {
                $q->where('landing_path', 'like', 'api/%')
                    ->orWhere('landing_path', 'like', 'v1/api/%')
                    ->orWhere('landing_path', 'like', '%/api/%');
            })
            ->update([
                'source'   => 'app',
                'platform' => DB::raw("CASE WHEN platform = 'browser' THEN 'app' ELSE platform END"),
            ]);
    }

    public function down(): void
    {
        // Nothing to reverse — this only corrects mislabeled data.
    }
};