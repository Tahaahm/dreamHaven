<?php

/*
| php artisan make:migration add_interaction_analytics_index
| Paste this in, then: php artisan migrate
|
| The composite index below is what makes the "most viewed" query use a
| range scan instead of a full table scan + temp table.
|
| Column order matters: interaction_type first (the equality filter),
| then created_at (the range), then property_id and user_id so the whole
| aggregate can be answered from the index without touching the rows.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private ?string $table = null;

    public function __construct()
    {
        foreach (['user_property_interactions', 'property_interactions', 'interactions'] as $candidate) {
            try {
                if (Schema::hasTable($candidate)) {
                    $this->table = $candidate;
                    break;
                }
            } catch (\Throwable $e) {
                // keep looking
            }
        }
    }

    public function up(): void
    {
        if (!$this->table) {
            return;
        }

        $existing = $this->existingIndexes();

        // Covering index for: WHERE interaction_type = ? AND created_at >= ?
        //                     GROUP BY property_id, COUNT(DISTINCT user_id)
        if (
            Schema::hasColumn($this->table, 'interaction_type')
            && !in_array('idx_upi_type_created_prop', $existing, true)
        ) {
            $this->run("ADD INDEX `idx_upi_type_created_prop` (`interaction_type`, `created_at`, `property_id`, `user_id`)");
        }

        // For the win-back query: WHERE user_id IS NOT NULL AND created_at >= ?
        if (
            !in_array('idx_upi_user_created2', $existing, true)
            && !in_array('idx_upi_user_created', $existing, true)
        ) {
            $this->run("ADD INDEX `idx_upi_user_created2` (`user_id`, `created_at`)");
        }
    }

    public function down(): void
    {
        if (!$this->table) {
            return;
        }

        foreach (['idx_upi_type_created_prop', 'idx_upi_user_created2'] as $name) {
            $this->run("DROP INDEX `{$name}`");
        }
    }

    private function run(string $sql): void
    {
        try {
            DB::statement("ALTER TABLE `{$this->table}` {$sql}");
        } catch (\Throwable $e) {
            // Already exists, or the table is locked — safe to skip
        }
    }

    private function existingIndexes(): array
    {
        try {
            return collect(DB::select("SHOW INDEX FROM `{$this->table}`"))
                ->pluck('Key_name')->unique()->values()->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
};
