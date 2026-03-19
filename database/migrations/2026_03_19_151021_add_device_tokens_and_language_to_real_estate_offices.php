<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('real_estate_offices', function (Blueprint $table) {
            $table->json('device_tokens')->nullable()->after('availability_schedule');
            $table->string('language', 10)->default('en')->after('device_tokens');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('real_estate_offices', function (Blueprint $table) {
            $table->dropColumn(['device_tokens', 'language']);
        });
    }
};
