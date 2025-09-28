<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('user_id');

            // Review content
            $table->integer('rating'); // 1-5 stars
            $table->string('title')->nullable(); // Review title/summary
            $table->text('review')->nullable(); // Detailed review
            $table->json('pros')->nullable(); // What they liked
            $table->json('cons')->nullable(); // What they didn't like

            // Detailed ratings (optional breakdown)
            $table->integer('location_rating')->nullable(); // 1-5
            $table->integer('value_rating')->nullable(); // Value for money 1-5
            $table->integer('quality_rating')->nullable(); // Build quality 1-5
            $table->integer('amenities_rating')->nullable(); // Amenities 1-5
            $table->integer('developer_rating')->nullable(); // Developer service 1-5

            // Review context
            $table->enum('reviewer_type', [
                'owner',
                'tenant',
                'investor',
                'visitor',
                'potential_buyer'
            ])->default('potential_buyer');
            $table->string('unit_type')->nullable(); // Which unit type they reviewed
            $table->date('purchase_date')->nullable(); // When they bought/rented
            $table->boolean('would_recommend')->default(true);

            // Verification and moderation
            $table->boolean('is_verified')->default(false); // Verified purchase/ownership
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->text('admin_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable(); // Admin who verified

            // Engagement
            $table->integer('helpful_count')->default(0); // How many found it helpful
            $table->integer('not_helpful_count')->default(0);
            $table->json('images')->nullable(); // User uploaded images

            // Response from developer
            $table->text('developer_response')->nullable();
            $table->timestamp('developer_responded_at')->nullable();
            $table->uuid('developer_responded_by')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('project_id');
            $table->index('user_id');
            $table->index('rating');
            $table->index('is_approved');
            $table->index('is_verified');
            $table->index('is_featured');
            $table->index('reviewer_type');
            $table->index('created_at');
            $table->index(['project_id', 'rating']);
            $table->index(['is_approved', 'rating']);

            // Unique constraint - one review per user per project
            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_reviews');
    }
};
