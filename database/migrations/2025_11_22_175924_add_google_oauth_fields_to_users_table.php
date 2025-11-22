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
        Schema::table('users', function (Blueprint $table) {
            // Add google_id column for Google OAuth integration
            $table->string('google_id')->nullable()->unique()->after('email');

            // Add last_login_at column to track user's last login time
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');

            // Add index for faster lookups on google_id
            $table->index('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['google_id']);
            $table->dropColumn(['google_id', 'last_login_at']);
        });
    }
};
