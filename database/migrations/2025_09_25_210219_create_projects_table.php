<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Owner (Developer/Company/Agency)
            $table->uuid('developer_id');
            $table->string('developer_type'); // 'real_estate_office', 'developer_company', etc.

            // Basic project information
            $table->json('name'); // Multi-language support
            $table->json('description')->nullable();
            $table->string('slug')->unique();
            $table->json('images'); // Project images/gallery
            $table->string('logo_url')->nullable(); // Project/developer logo
            $table->string('cover_image_url')->nullable(); // Main project image

            // Project details
            $table->enum('project_type', [
                'residential',
                'commercial',
                'mixed_use',
                'industrial',
                'retail',
                'office',
                'hospitality'
            ]);
            $table->json('project_category')->nullable(); // apartment_complex, shopping_mall, etc.

            // Location
            $table->json('locations'); // City, district, neighborhood
            $table->json('address_details');
            $table->string('full_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Project scale
            $table->decimal('total_area', 12, 2)->nullable(); // Total project area
            $table->decimal('built_area', 12, 2)->nullable(); // Total built area
            $table->integer('total_units')->nullable(); // Total properties/units in project
            $table->integer('available_units')->default(0); // Currently available units
            $table->json('unit_types')->nullable(); // 1BR, 2BR, commercial, etc.

            // Building details
            $table->integer('total_floors')->nullable();
            $table->integer('buildings_count')->default(1);
            $table->json('building_details')->nullable(); // Per building info
            $table->integer('year_built')->nullable();
            $table->integer('completion_year')->nullable();
            $table->json('construction_details')->nullable();
            $table->string('architect')->nullable();
            $table->string('contractor')->nullable();

            // Pricing
            $table->json('price_range'); // Min and max prices
            $table->json('pricing_details')->nullable(); // Price per sqft, etc.
            $table->enum('pricing_currency', ['USD', 'EUR', 'IQD'])->default('USD');

            // Features and amenities
            $table->json('project_features')->nullable(); // Swimming pool, gym, parking, etc.
            $table->json('nearby_amenities')->nullable(); // Schools, hospitals, malls nearby
            $table->json('transportation')->nullable(); // Metro, bus stations, etc.
            $table->json('facilities')->nullable(); // Security, maintenance, etc.

            // Media and documents
            $table->string('virtual_tour_url')->nullable();
            $table->json('floor_plans')->nullable(); // Master plan, floor plans
            $table->json('brochures')->nullable(); // PDF brochures, documents
            $table->json('videos')->nullable(); // Project videos
            $table->json('additional_media')->nullable();

            // Status and phases
            $table->enum('status', [
                'planning',
                'under_construction',
                'completed',
                'delivered',
                'cancelled',
                'on_hold'
            ])->default('planning');
            $table->enum('sales_status', [
                'pre_launch',
                'launched',
                'selling',
                'sold_out',
                'suspended'
            ])->default('pre_launch');
            $table->integer('completion_percentage')->default(0);
            $table->json('phases')->nullable(); // Multi-phase projects

            // Dates
            $table->date('launch_date')->nullable();
            $table->date('construction_start_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->date('handover_date')->nullable();

            // Legal and approvals
            $table->json('approvals')->nullable(); // Government approvals, permits
            $table->json('certifications')->nullable(); // Green building, etc.
            $table->json('legal_information')->nullable();
            $table->string('rera_registration')->nullable(); // Real Estate Regulatory Authority

            // Developer information
            $table->json('developer_info')->nullable();
            $table->json('contact_info')->nullable(); // Sales office contact

            // Marketing and SEO
            $table->json('seo_metadata')->nullable();
            $table->json('marketing_highlights')->nullable(); // Key selling points
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_premium')->default(false);

            // Analytics
            $table->integer('views')->default(0);
            $table->json('view_analytics')->nullable();
            $table->integer('inquiries_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);

            // Promotion
            $table->boolean('is_boosted')->default(false);
            $table->timestamp('boost_start_date')->nullable();
            $table->timestamp('boost_end_date')->nullable();

            // Visibility
            $table->boolean('is_active')->default(true);
            $table->boolean('published')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['developer_id', 'developer_type']);
            $table->index('project_type');
            $table->index('status');
            $table->index('sales_status');
            $table->index(['is_active', 'published']);
            $table->index('is_featured');
            $table->index('is_premium');
            $table->index('views');
            $table->index('rating');
            $table->index('completion_percentage');
            $table->index(['latitude', 'longitude']); // For map searches
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
