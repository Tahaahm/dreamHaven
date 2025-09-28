<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_favorites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('user_id');

            // Favorite context
            $table->enum('favorite_type', [
                'general',
                'investment',
                'personal_use',
                'comparison'
            ])->default('general');
            $table->text('notes')->nullable(); // User's personal notes
            $table->json('interested_features')->nullable(); // What they like about it
            $table->json('tags')->nullable(); // User's custom tags

            // Notification preferences
            $table->boolean('notify_price_change')->default(true);
            $table->boolean('notify_status_change')->default(true);
            $table->boolean('notify_new_units')->default(false);
            $table->boolean('notify_promotions')->default(true);

            // Priority and organization
            $table->integer('priority')->default(1); // 1=low, 3=medium, 5=high
            $table->string('list_name')->nullable(); // Custom list/folder name
            $table->integer('sort_order')->default(0); // User's custom ordering

            // Activity tracking
            $table->integer('view_count')->default(0); // How many times user viewed after favoriting
            $table->timestamp('last_viewed_at')->nullable();
            $table->boolean('is_archived')->default(false); // User archived this favorite

            $table->timestamps();

            // Foreign keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('project_id');
            $table->index('user_id');
            $table->index('favorite_type');
            $table->index('priority');
            $table->index('list_name');
            $table->index('is_archived');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'is_archived']);

            // Unique constraint - one favorite per user per project
            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_favorites');
    }
};
