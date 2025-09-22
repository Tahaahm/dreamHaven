<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Polymorphic relationship - can belong to User, Agent, or RealEstateOffice
            $table->uuid('owner_id');
            $table->string('owner_type'); // 'user', 'agent', 'real_estate_office' (will be mapped to respective models)

            // Basic property information
            $table->json('name');
            $table->json('description')->nullable();
            $table->json('images')->nullable();
            $table->json('availability');
            $table->json('type');
            $table->decimal('area', 10, 2);
            $table->boolean('furnished')->default(false);

            // Pricing
            $table->json('price');
            $table->enum('listing_type', ['rent', 'sell']);
            $table->enum('rental_period', ['monthly', 'yearly'])->nullable();

            // Structure
            $table->json('rooms');
            $table->json('features')->nullable();
            $table->json('amenities')->nullable();

            // Location
            $table->json('locations');
            $table->json('address_details');
            $table->string('address')->nullable();

            // Building details
            $table->integer('floor_number')->nullable();
            $table->json('floor_details')->nullable();
            $table->integer('year_built')->nullable();
            $table->json('construction_details')->nullable();

            // Energy and utilities
            $table->string('energy_rating')->nullable();
            $table->json('energy_details')->nullable();
            $table->boolean('electricity')->default(true);
            $table->boolean('water')->default(true);
            $table->boolean('internet')->default(false);

            // Media
            $table->string('virtual_tour_url')->nullable();
            $table->json('virtual_tour_details')->nullable();
            $table->string('floor_plan_url')->nullable();
            $table->json('additional_media')->nullable();

            // Status and verification
            $table->boolean('verified')->default(false);
            $table->json('verification_details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('published')->default(false);
            $table->enum('status', ['cancelled', 'pending', 'approved', 'available', 'sold', 'rented'])->default('pending');

            // Analytics
            $table->integer('views')->default(0);
            $table->json('view_analytics')->nullable();
            $table->integer('favorites_count')->default(0);
            $table->json('favorites_analytics')->nullable();
            $table->decimal('rating', 3, 2)->default(0);

            // Promotion
            $table->boolean('is_boosted')->default(false);
            $table->timestamp('boost_start_date')->nullable();
            $table->timestamp('boost_end_date')->nullable();

            // Additional data
            $table->json('legal_information')->nullable();
            $table->json('investment_analysis')->nullable();
            $table->json('furnishing_details')->nullable();
            $table->json('seo_metadata')->nullable();
            $table->json('nearby_amenities')->nullable();

            $table->timestamps();

            // Indexes for polymorphic relationship and performance
            $table->index(['owner_id', 'owner_type']);
            $table->index('owner_type');
            $table->index('listing_type');
            $table->index('status');
            $table->index('is_active');
            $table->index('published');
            $table->index(['status', 'is_active', 'published']);
            $table->index('views');
            $table->index('favorites_count');
            $table->index('rating');
            $table->index('is_boosted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
