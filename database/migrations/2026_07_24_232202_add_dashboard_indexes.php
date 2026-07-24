<?php

/*
| Save as: database/migrations/2026_07_25_000000_add_dashboard_indexes.php
| Then:    php artisan migrate
|
| These are the columns every dashboard aggregate filters or groups on.
| Without them MySQL full-scans the table for each metric — which is a large
| part of your 10 second load.
|
| Each index is added inside try/catch, so ones you already have are skipped
| instead of failing the migration.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** table => [index name => columns] */
    private array $indexes = [
        'properties' => [
            'idx_props_created'      => 'created_at',
            'idx_props_status'       => 'status',
            'idx_props_active'       => 'is_active',
            'idx_props_listing_type' => 'listing_type',
            'idx_props_boosted'      => 'is_boosted',
            'idx_props_owner'        => 'owner_type, owner_id',
        ],
        'users' => [
            'idx_users_created'  => 'created_at',
            'idx_users_verified' => 'is_verified',
        ],
        'agents' => [
            'idx_agents_verified' => 'is_verified',
            'idx_agents_sub'      => 'subscription_id',
        ],
        'real_estate_offices' => [
            'idx_offices_verified' => 'is_verified',
            'idx_offices_sub'      => 'subscription_id',
        ],
        'subscriptions' => [
            'idx_subs_status_start' => 'status, start_date',
            'idx_subs_end'          => 'end_date',
        ],
        'user_property_interactions' => [
            'idx_upi_user_created' => 'user_id, created_at',
            'idx_upi_prop_created' => 'property_id, created_at',
            'idx_upi_created'      => 'created_at',
        ],
        'appointments' => [
            'idx_appt_date'   => 'appointment_date',
            'idx_appt_status' => 'status',
        ],
        'banner_ads' => [
            'idx_banner_status' => 'status',
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $definitions) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $existing = $this->existingIndexes($table);

            foreach ($definitions as $name => $columns) {
                if (in_array($name, $existing, true)) {
                    continue;
                }

                // Skip if any column is missing on this install
                $missing = false;
                foreach (explode(',', $columns) as $column) {
                    if (!Schema::hasColumn($table, trim($column))) {
                        $missing = true;
                        break;
                    }
                }

                if ($missing) {
                    continue;
                }

                try {
                    DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$this->quote($columns)})");
                } catch (\Throwable $e) {
                    // Already indexed under another name, or locked — carry on
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $definitions) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach (array_keys($definitions) as $name) {
                try {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
                } catch (\Throwable $e) {
                    // Wasn't there — fine
                }
            }
        }
    }

    private function existingIndexes(string $table): array
    {
        try {
            return collect(DB::select("SHOW INDEX FROM `{$table}`"))
                ->pluck('Key_name')->unique()->values()->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function quote(string $columns): string
    {
        return collect(explode(',', $columns))
            ->map(fn($c) => '`' . trim($c) . '`')
            ->implode(', ');
    }
};
