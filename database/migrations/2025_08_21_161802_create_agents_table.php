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
        // Main agents table
        Schema::create('agents', function (Blueprint $table) {
           $table->char('id', 36)->primary();

            // Basic agent information
            $table->string('agent_name');
            $table->text('agent_bio')->nullable();
            $table->string('bio_image')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('type')->default('real_estate_official');
            $table->string('subscriber_id')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->decimal('overall_rating', 3, 2)->default(0.00);

            // Subscription information
            $table->uuid('subscription_id')->nullable();
            $table->enum('current_plan', ['starter', 'professional', 'enterprise'])->nullable();
            $table->integer('properties_uploaded_this_month')->default(0);
            $table->integer('remaining_property_uploads')->default(0);

            // Contact information
            $table->string('primary_email')->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('whatsapp_number')->nullable();

            // Work location
            $table->text('office_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();

            // Professional data
            $table->integer('properties_sold')->default(0);
            $table->integer('years_experience')->default(0);
            $table->string('license_number')->nullable();

            // Company affiliation
            $table->char('company_id', 36)->nullable();
            $table->string('company_name')->nullable();
            $table->enum('employment_status', ['employee', 'independent', 'partner'])->nullable();

            // Additional information
            $table->text('agent_overview')->nullable();

            // Working hours (stored as JSON)
            $table->json('working_hours')->nullable();

            // Pricing information
            $table->decimal('commission_rate', 5, 2)->nullable(); // e.g., 3.50 for 3.5%
            $table->decimal('consultation_fee', 10, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');

            $table->timestamps();

            // Indexes - Fixed spatial index issue
            $table->index('agent_name');
            $table->index('is_verified');
            $table->index('subscription_id');
            $table->index('current_plan');
            $table->index(['city', 'district']);
            $table->index('overall_rating');
            $table->index('company_id');
            $table->index('latitude');
            $table->index('longitude');
            $table->index(['latitude', 'longitude']); // Composite index instead of spatial

            // Foreign key to subscriptions table
            $table->foreign(columns: 'subscription_id')->references('id')->on('subscriptions')->onDelete('set null');
           $table->foreign('company_id')->references('id')->on('real_estate_offices')->onDelete('set null');
 });

        // Agent specializations table
        Schema::create('agent_specializations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('agent_id');
            $table->string('property_type');
            $table->string('service_area');

            $table->timestamps();

            $table->foreign('agent_id')
                  ->references('id')
                  ->on('agents')
                  ->onDelete('cascade');

            $table->index('agent_id');
            $table->index(['property_type', 'service_area']);
        });

        // Agent uploaded properties table
        Schema::create('agent_uploaded_properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('agent_id');
            $table->uuid('property_id');
            $table->date('upload_date');
            $table->enum('property_status', ['active', 'sold', 'rented', 'expired'])->default('active');

            $table->timestamps();

            $table->foreign('agent_id')
                  ->references('id')
                  ->on('agents')
                  ->onDelete('cascade');

            $table->foreign('property_id')
                  ->references('id')
                  ->on('properties')
                  ->onDelete('cascade');

            $table->index('agent_id');
            $table->index(['agent_id', 'property_status']);
            $table->index('upload_date');
            $table->index('property_id');
        });

        // Agent social platforms table
        Schema::create('agent_social_platforms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('agent_id');
            $table->string('platform_name');
            $table->string('account_handle')->nullable();
            $table->string('profile_link')->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('agent_id')
                  ->references('id')
                  ->on('agents')
                  ->onDelete('cascade');

            $table->index('agent_id');
            $table->index('platform_name');
        });

        // Agent client reviews table
        Schema::create('agent_client_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('agent_id');
            $table->string('client_name');
            $table->string('client_photo')->nullable();
            $table->integer('rating_score'); // 1-5 stars
            $table->text('review_text')->nullable();
            $table->date('review_date');
            $table->string('service_type')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            $table->foreign('agent_id')
                  ->references('id')
                  ->on('agents')
                  ->onDelete('cascade');

            $table->index('agent_id');
            $table->index(['agent_id', 'rating_score']);
            $table->index('review_date');
            $table->index('is_featured');
        });

        // Agent notifications table
        Schema::create('agent_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('agent_id');
            $table->string('notification_id');
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->foreign('agent_id')
                  ->references('id')
                  ->on('agents')
                  ->onDelete('cascade');

            $table->index('agent_id');
            $table->index(['agent_id', 'is_read']);
            $table->index('notification_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_notifications');
        Schema::dropIfExists('agent_client_reviews');
        Schema::dropIfExists('agent_social_platforms');
        Schema::dropIfExists('agent_uploaded_properties');
        Schema::dropIfExists('agent_specializations');
        Schema::dropIfExists('agents');
    }
};
