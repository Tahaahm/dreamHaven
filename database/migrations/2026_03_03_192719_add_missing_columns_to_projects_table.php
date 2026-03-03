<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {

            // Payment & Pricing
            if (!Schema::hasColumn('projects', 'down_payment_percentage')) {
                $table->decimal('down_payment_percentage', 5, 2)->nullable()->after('pricing_currency');
            }
            if (!Schema::hasColumn('projects', 'installment_available')) {
                $table->boolean('installment_available')->default(false)->after('down_payment_percentage');
            }
            if (!Schema::hasColumn('projects', 'installment_months')) {
                $table->integer('installment_months')->nullable()->after('installment_available');
            }

            // Project Scale
            if (!Schema::hasColumn('projects', 'total_units')) {
                $table->integer('total_units')->nullable()->after('unit_types');
            }
            if (!Schema::hasColumn('projects', 'available_units')) {
                $table->integer('available_units')->default(0)->after('total_units');
            }
            if (!Schema::hasColumn('projects', 'total_floors')) {
                $table->integer('total_floors')->nullable()->after('available_units');
            }
            if (!Schema::hasColumn('projects', 'buildings_count')) {
                $table->integer('buildings_count')->default(1)->after('total_floors');
            }
            if (!Schema::hasColumn('projects', 'total_area')) {
                $table->decimal('total_area', 12, 2)->nullable()->after('buildings_count');
            }
            if (!Schema::hasColumn('projects', 'built_area')) {
                $table->decimal('built_area', 12, 2)->nullable()->after('total_area');
            }

            // Construction
            if (!Schema::hasColumn('projects', 'completion_percentage')) {
                $table->integer('completion_percentage')->default(0)->after('built_area');
            }
            if (!Schema::hasColumn('projects', 'year_built')) {
                $table->integer('year_built')->nullable()->after('completion_percentage');
            }
            if (!Schema::hasColumn('projects', 'architect')) {
                $table->string('architect')->nullable()->after('year_built');
            }
            if (!Schema::hasColumn('projects', 'contractor')) {
                $table->string('contractor')->nullable()->after('architect');
            }

            // Legal
            if (!Schema::hasColumn('projects', 'rera_registration')) {
                $table->string('rera_registration')->nullable()->after('contractor');
            }
            if (!Schema::hasColumn('projects', 'virtual_tour_url')) {
                $table->string('virtual_tour_url')->nullable()->after('rera_registration');
            }

            // Features (JSON arrays)
            if (!Schema::hasColumn('projects', 'project_features')) {
                $table->json('project_features')->nullable()->after('virtual_tour_url');
            }
            if (!Schema::hasColumn('projects', 'nearby_amenities')) {
                $table->json('nearby_amenities')->nullable()->after('project_features');
            }
            if (!Schema::hasColumn('projects', 'marketing_highlights')) {
                $table->json('marketing_highlights')->nullable()->after('nearby_amenities');
            }

            // Media
            if (!Schema::hasColumn('projects', 'cover_image_url')) {
                $table->string('cover_image_url')->nullable()->after('marketing_highlights');
            }
            if (!Schema::hasColumn('projects', 'logo_url')) {
                $table->string('logo_url')->nullable()->after('cover_image_url');
            }
            if (!Schema::hasColumn('projects', 'images')) {
                $table->json('images')->nullable()->after('logo_url');
            }

            // Flags
            if (!Schema::hasColumn('projects', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('images');
            }
            if (!Schema::hasColumn('projects', 'is_premium')) {
                $table->boolean('is_premium')->default(false)->after('is_featured');
            }
            if (!Schema::hasColumn('projects', 'is_hot_project')) {
                $table->boolean('is_hot_project')->default(false)->after('is_premium');
            }
            if (!Schema::hasColumn('projects', 'is_boosted')) {
                $table->boolean('is_boosted')->default(false)->after('is_hot_project');
            }
            if (!Schema::hasColumn('projects', 'boost_start_date')) {
                $table->timestamp('boost_start_date')->nullable()->after('is_boosted');
            }
            if (!Schema::hasColumn('projects', 'boost_end_date')) {
                $table->timestamp('boost_end_date')->nullable()->after('boost_start_date');
            }

            // Analytics
            if (!Schema::hasColumn('projects', 'views')) {
                $table->integer('views')->default(0)->after('boost_end_date');
            }
            if (!Schema::hasColumn('projects', 'favorites_count')) {
                $table->integer('favorites_count')->default(0)->after('views');
            }
            if (!Schema::hasColumn('projects', 'inquiries_count')) {
                $table->integer('inquiries_count')->default(0)->after('favorites_count');
            }
            if (!Schema::hasColumn('projects', 'site_visits_count')) {
                $table->integer('site_visits_count')->default(0)->after('inquiries_count');
            }
            if (!Schema::hasColumn('projects', 'bookings_count')) {
                $table->integer('bookings_count')->default(0)->after('site_visits_count');
            }
            if (!Schema::hasColumn('projects', 'units_sold')) {
                $table->integer('units_sold')->default(0)->after('bookings_count');
            }
            if (!Schema::hasColumn('projects', 'rating')) {
                $table->decimal('rating', 3, 2)->default(0)->after('units_sold');
            }
            if (!Schema::hasColumn('projects', 'reviews_count')) {
                $table->integer('reviews_count')->default(0)->after('rating');
            }

            // Dates
            if (!Schema::hasColumn('projects', 'launch_date')) {
                $table->date('launch_date')->nullable()->after('reviews_count');
            }
            if (!Schema::hasColumn('projects', 'construction_start_date')) {
                $table->date('construction_start_date')->nullable()->after('launch_date');
            }
            if (!Schema::hasColumn('projects', 'expected_completion_date')) {
                $table->date('expected_completion_date')->nullable()->after('construction_start_date');
            }
            if (!Schema::hasColumn('projects', 'handover_date')) {
                $table->date('handover_date')->nullable()->after('expected_completion_date');
            }

            // Location extras
            if (!Schema::hasColumn('projects', 'full_address')) {
                $table->string('full_address')->nullable()->after('handover_date');
            }
            if (!Schema::hasColumn('projects', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('full_address');
            }
            if (!Schema::hasColumn('projects', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('projects', 'address_details')) {
                $table->json('address_details')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('projects', 'locations')) {
                $table->json('locations')->nullable()->after('address_details');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $columns = [
                'down_payment_percentage', 'installment_available', 'installment_months',
                'total_units', 'available_units', 'total_floors', 'buildings_count',
                'total_area', 'built_area', 'completion_percentage', 'year_built',
                'architect', 'contractor', 'rera_registration', 'virtual_tour_url',
                'project_features', 'nearby_amenities', 'marketing_highlights',
                'cover_image_url', 'logo_url', 'images',
                'is_featured', 'is_premium', 'is_hot_project',
                'is_boosted', 'boost_start_date', 'boost_end_date',
                'views', 'favorites_count', 'inquiries_count',
                'site_visits_count', 'bookings_count', 'units_sold',
                'rating', 'reviews_count',
                'launch_date', 'construction_start_date',
                'expected_completion_date', 'handover_date',
                'full_address', 'latitude', 'longitude',
                'address_details', 'locations',
            ];

            // Only drop columns that exist
            $existing = array_filter($columns, fn($col) => Schema::hasColumn('projects', $col));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};