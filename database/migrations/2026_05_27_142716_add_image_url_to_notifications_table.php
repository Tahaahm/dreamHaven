<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Only add if not already present
            if (!Schema::hasColumn('notifications', 'image_url')) {
                // Place after 'message' for logical grouping
                $table->string('image_url', 500)->nullable()->after('message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};
