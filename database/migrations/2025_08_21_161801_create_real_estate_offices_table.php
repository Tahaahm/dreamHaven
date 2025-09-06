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
        // Main real estate offices table
        Schema::create('real_estate_offices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Company basic information
            $table->string('company_name');
            $table->text('company_bio')->nullable();
            $table->string('company_bio_image')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('account_type')->default('real_estate_official');
            $table->uuid('subscription_id')->nullable();
            $table->enum('current_plan', ['starter', 'professional', 'enterprise'])->nullable();
            $table->boolean('is_verified')->default(false);
            $table->decimal('average_rating', 3, 2)->default(0.00);

            // Contact details
            $table->string('email_address')->nullable();
            $table->string('phone_number')->nullable();

            // Office location
            $table->text('office_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();

            // Business data
            $table->integer('properties_sold')->default(0);
            $table->integer('years_experience')->default(0);

            // Additional information
            $table->text('about_company')->nullable();

            // Availability schedule (stored as JSON)
            $table->json('availability_schedule')->nullable();

            $table->timestamps();

            // Indexes - Fixed spatial index issue
            $table->index('company_name');
            $table->index('is_verified');
            $table->index('subscription_id');
            $table->index('current_plan');
            $table->index(['city', 'district']);
            $table->index('average_rating');
            $table->index('latitude');
            $table->index('longitude');
            $table->index(['latitude', 'longitude']); // Composite index instead of spatial

            // Foreign key to subscriptions table
            $table->foreign(columns: 'subscription_id')->references('id')->on('subscriptions')->onDelete('set null');
        });

        // Office property types table
        Schema::create('office_property_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('office_id');
            $table->string('type');
            $table->string('specialization')->nullable();

            $table->timestamps();

            $table->foreign('office_id')
                  ->references('id')
                  ->on('real_estate_offices')
                  ->onDelete('cascade');

            $table->index('office_id');
            $table->index(['type', 'specialization']);
        });

        // Office property listings table
        Schema::create('office_property_listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('office_id');
            $table->uuid('property_id');
            $table->enum('status', ['completed', 'in_progress', 'pending'])->default('pending');

            $table->timestamps();

            $table->foreign('office_id')
                  ->references('id')
                  ->on('real_estate_offices')
                  ->onDelete('cascade');

            $table->foreign('property_id')
                  ->references('id')
                  ->on('properties')
                  ->onDelete('cascade');

            $table->index('office_id');
            $table->index(['office_id', 'status']);
            $table->index('property_id');
        });

        // Office project portfolio table
        Schema::create('office_project_portfolio', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('office_id');
            $table->string('project_name');
            $table->text('project_description')->nullable();
            $table->string('project_image')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->string('project_type')->nullable();
            $table->enum('status', ['completed', 'in_progress', 'pending'])->default('pending');
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('office_id')
                  ->references('id')
                  ->on('real_estate_offices')
                  ->onDelete('cascade');

            $table->index('office_id');
            $table->index(['office_id', 'status']);
            $table->index('project_type');
            $table->index('sort_order');
        });

        // Office company agents table
        Schema::create('office_company_agents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('office_id');
            $table->uuid('agent_id');
            $table->string('agent_name');
            $table->string('role')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('office_id')
                  ->references('id')
                  ->on('real_estate_offices')
                  ->onDelete('cascade');

            $table->foreign('agent_id')
                  ->references('id')
                  ->on('agents')
                  ->onDelete('cascade');

            $table->index('office_id');
            $table->index('agent_id');
            $table->index(['office_id', 'is_active']);
        });

        // Office social media table
        Schema::create('office_social_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('office_id');
            $table->string('platform');
            $table->string('username')->nullable();
            $table->string('profile_url')->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('office_id')
                  ->references('id')
                  ->on('real_estate_offices')
                  ->onDelete('cascade');

            $table->index('office_id');
            $table->index('platform');
        });

        // Office customer reviews table
        Schema::create('office_customer_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('office_id');
            $table->string('reviewer_name');
            $table->string('reviewer_avatar')->nullable();
            $table->integer('star_rating'); // 1-5 stars
            $table->text('review_content')->nullable();
            $table->date('review_date');
            $table->string('property_type')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            $table->foreign('office_id')
                  ->references('id')
                  ->on('real_estate_offices')
                  ->onDelete('cascade');

            $table->index('office_id');
            $table->index(['office_id', 'star_rating']);
            $table->index('review_date');
            $table->index('is_featured');
        });

        // Office notification references table
        Schema::create('office_notification_references', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('office_id');
            $table->string('notification_id');
            $table->timestamp('notification_date');
            $table->enum('notification_status', ['unread', 'read', 'dismissed'])->default('unread');
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string('type')->nullable();

            $table->timestamps();

            $table->foreign('office_id')
                  ->references('id')
                  ->on('real_estate_offices')
                  ->onDelete('cascade');

            $table->index('office_id');
            $table->index(['office_id', 'notification_status']);
            $table->index('notification_id');
            $table->index('notification_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_notification_references');
        Schema::dropIfExists('office_customer_reviews');
        Schema::dropIfExists('office_social_media');
        Schema::dropIfExists('office_company_agents');
        Schema::dropIfExists('office_project_portfolio');
        Schema::dropIfExists('office_property_listings');
        Schema::dropIfExists('office_property_types');
        Schema::dropIfExists('real_estate_offices');
    }
};
