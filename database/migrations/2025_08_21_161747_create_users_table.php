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
        // Main users table
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic user information
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();

            // Location information - Fixed for spatial index
            $table->string('place')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();

            // Profile information
            $table->text('about_me')->nullable();
            $table->string('photo_image')->nullable();
            $table->string('language')->default('en');

            // Search preferences (stored as JSON for flexibility)
            $table->json('search_preferences')->nullable();

            // Device tokens column to store multiple device tokens as JSON
            $table->json('device_tokens')->nullable();

            $table->timestamps();

            // Regular indexes (removed spatial index to avoid MySQL issues)
            $table->index('username');
            $table->index('email');
            $table->index('phone');
            $table->index('place');
            $table->index('language');
            $table->index('lat');
            $table->index('lng');
            $table->index(['lat', 'lng']); // Composite index instead of spatial
            $table->index('device_tokens'); // Index for better performance when querying device tokens
        });

        // User notification references table
        Schema::create('user_notification_references', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('notification_id');
            $table->timestamp('notification_date');
            $table->enum('notification_status', ['unread', 'read', 'dismissed'])->default('unread');
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string('type')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('user_id');
            $table->index(['user_id', 'notification_status']);
            $table->index('notification_id');
            $table->index('notification_date');
        });

        // User appointments table
        Schema::create('user_appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('appointment_id');
            $table->string('appointment_title');
            $table->string('with_whom');
            $table->timestamp('appointment_date')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('user_id');
            $table->index('appointment_id');
            $table->index(['user_id', 'status']);
            $table->index('appointment_date');
        });

        // User favorite properties table
        Schema::create('user_favorite_properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('property_id');
            $table->timestamp('favorited_at')->useCurrent();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Unique constraint to prevent duplicate favorites
            $table->unique(['user_id', 'property_id']);

            $table->index('user_id');
            $table->index('property_id');
            $table->index('favorited_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorite_properties');
        Schema::dropIfExists('user_appointments');
        Schema::dropIfExists('user_notification_references');
        Schema::dropIfExists('users');
    }
};