<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create user_property_interactions table
        Schema::create('user_property_interactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');  // Match users table UUID
            $table->uuid('property_id');  // Match properties table UUID
            $table->string('interaction_type', 50); // 'view', 'favorite', 'share', 'contact'
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['property_id', 'created_at']);
            $table->index(['user_id', 'property_id', 'interaction_type'], 'user_property_interaction_idx');
            $table->index('interaction_type');

            // Foreign keys (optional - uncomment if you want cascade deletes)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
        });

        // Add columns to existing users table
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't already exist
            if (!Schema::hasColumn('users', 'recently_viewed_properties')) {
                $table->json('recently_viewed_properties')->nullable()->after('search_preferences');
            }
            if (!Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('recently_viewed_properties');
            }
        });
    }

    public function down(): void
    {
        // Drop user_property_interactions table
        Schema::dropIfExists('user_property_interactions');

        // Remove columns from users table
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'recently_viewed_properties')) {
                $table->dropColumn('recently_viewed_properties');
            }
            if (Schema::hasColumn('users', 'last_activity_at')) {
                $table->dropColumn('last_activity_at');
            }
        });
    }
};
