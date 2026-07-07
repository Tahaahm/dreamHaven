<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'apple_id')) {
                $table->string('apple_id')->nullable()->unique()->after('google_id');
            }
        });

        Schema::table('agents', function (Blueprint $table) {
            if (!Schema::hasColumn('agents', 'apple_id')) {
                $table->string('apple_id')->nullable()->unique()->after('google_id');
            }
        });

        Schema::table('real_estate_offices', function (Blueprint $table) {
            if (!Schema::hasColumn('real_estate_offices', 'apple_id')) {
                $table->string('apple_id')->nullable()->unique()->after('google_id');
            }
        });
    }

    public function down(): void
    {
        foreach (['users', 'agents', 'real_estate_offices'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('apple_id');
            });
        }
    }
};
