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
        // Main service providers table
        Schema::create('service_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Company basic information
            $table->string('company_name');
            $table->text('company_bio')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('profile_image')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0.00); // e.g., 4.75

            // Location information
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();

            // Business information
            $table->string('business_type')->nullable();
            $table->text('business_description')->nullable();
            $table->integer('years_in_business')->default(0);
            $table->integer('completed_projects')->default(0);

            // Contact details
            $table->string('phone_number')->nullable();
            $table->string('email_address')->nullable();
            $table->string('website_url')->nullable();

            // Business hours (stored as JSON for flexibility)
            $table->json('business_hours')->nullable();

            // Additional information
            $table->text('company_overview')->nullable();

            $table->timestamps();

            // Indexes - Fixed spatial index issue
            $table->index('company_name');
            $table->index('is_verified');
            $table->index('business_type');
            $table->index(['city', 'district']);
            $table->index('average_rating');
            $table->index('latitude');
            $table->index('longitude');
            $table->index(['latitude', 'longitude']); // Composite index instead of spatial
        });

        // Project gallery table
        Schema::create('service_provider_galleries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_provider_id');
            $table->string('image_url');
            $table->text('description')->nullable();
            $table->string('project_title')->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('service_provider_id')
                  ->references('id')
                  ->on('service_providers')
                  ->onDelete('cascade');

            $table->index('service_provider_id');
            $table->index('sort_order');
        });

        // Service offerings table
        Schema::create('service_provider_offerings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_provider_id');
            $table->string('service_title');
            $table->text('service_description')->nullable();
            $table->string('price_range')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('service_provider_id')
                  ->references('id')
                  ->on('service_providers')
                  ->onDelete('cascade');

            $table->index('service_provider_id');
            $table->index(['service_provider_id', 'active']);
            $table->index('sort_order');
        });

        // Customer reviews table
        Schema::create('service_provider_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_provider_id');
            $table->string('reviewer_name');
            $table->string('reviewer_avatar')->nullable();
            $table->integer('star_rating'); // 1-5 stars
            $table->text('review_content')->nullable();
            $table->date('review_date');
            $table->string('service_type')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            $table->foreign('service_provider_id')
                  ->references('id')
                  ->on('service_providers')
                  ->onDelete('cascade');

            $table->index('service_provider_id');
            $table->index(['service_provider_id', 'star_rating']);
            $table->index('review_date');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_reviews');
        Schema::dropIfExists('service_provider_offerings');
        Schema::dropIfExists('service_provider_galleries');
        Schema::dropIfExists('service_providers');
    }
};
