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
            // Add password field after email_address
            $table->string('password')->after('email_address');

            // Add remember token for "remember me" functionality
            $table->rememberToken()->after('password');

            // Add email verification fields
            $table->timestamp('email_verified_at')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('real_estate_offices', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token', 'email_verified_at']);
        });
    }
};
