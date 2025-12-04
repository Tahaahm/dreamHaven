<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JsonSeeder extends Seeder
{
    public function run(): void
    {
        // Get all files in storage/app/seed_data
        $files = Storage::files('seed_data');

        foreach ($files as $file) {
            // Get table name from file (lowercase)
            $table = strtolower(pathinfo($file, PATHINFO_FILENAME));

            // Read file contents
            $json = Storage::get($file);
            $data = json_decode($json, true);

            if (!$data) {
                echo "⚠️ Skipped {$file} (invalid JSON)\n";
                continue;
            }

            // Handle nested "data" key if it exists
            if (isset($data['data'])) {
                $data = $data['data'];
            } 
            // Handle single-object JSON
            elseif (array_keys($data) !== range(0, count($data) - 1)) {
                $data = [$data];
            }

            foreach ($data as $item) {
                // Make sure every row has an ID
                $item['id'] = $item['id'] ?? (string) Str::uuid();

                // Hash password if exists
                if (isset($item['password'])) {
                    $item['password'] = bcrypt($item['password']);
                }

                // Convert arrays to JSON strings
                foreach ($item as $key => $value) {
                    if (is_array($value)) {
                        $item[$key] = json_encode($value);
                    }
                }

                // ✅ Only keep columns that exist in table
                try {
                    $columns = DB::getSchemaBuilder()->getColumnListing($table);
                    $item = array_filter(
                        $item,
                        fn($key) => in_array($key, $columns),
                        ARRAY_FILTER_USE_KEY
                    );

                    // Insert or update
                    DB::table($table)->updateOrInsert(['id' => $item['id']], $item);

                } catch (\Throwable $e) {
                    echo "❌ Error inserting into {$table}: " . $e->getMessage() . "\n";
                }
            }

            echo "✅ Imported: " . count($data) . " rows into {$table}\n";
        }
    }
}
