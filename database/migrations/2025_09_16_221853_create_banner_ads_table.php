<?php
// Migration: Create banner_ads table
// php artisan make:migration create_banner_ads_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banner_ads', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // BASIC BANNER INFO (Multi-language support)
            $table->json('title'); // Banner title in multiple languages {"en": "English Title", "ar": "العنوان العربي", "ku": "ناونیشانی کوردی"}
            $table->json('description')->nullable(); // Banner description in multiple languages
            $table->string('image_url'); // Main banner image
            $table->string('image_alt')->nullable(); // Alt text for image
            $table->string('link_url')->nullable(); // Where banner links to
            $table->boolean('link_opens_new_tab')->default(false);

            // BANNER OWNER INFO
            $table->enum('owner_type', ['real_estate', 'agent']); // Who created the banner
            $table->uuid('owner_id'); // ID of the owner (agency or agent)
            $table->string('owner_name'); // Name of owner for quick reference
            $table->string('owner_email')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('owner_logo')->nullable(); // Owner's logo/photo

            // BANNER TYPE & TARGETING
            $table->enum('banner_type', [
                'property_listing',    // Specific property advertisement
                'agent_profile',       // Agent promotion banner
                'agency_branding',     // Real estate office branding
                'service_promotion',   // Services like mortgage, inspection
                'event_announcement',  // Open houses, seminars
                'general_marketing'    // Generic marketing content
            ]);

            // PROPERTY CONNECTION (if applicable)
            $table->uuid('property_id')->nullable(); // Link to specific property
            $table->decimal('property_price', 15, 2)->nullable(); // Property price for quick display
            $table->string('property_address')->nullable(); // Property address for quick display

            // DISPLAY & POSITIONING
            $table->enum('banner_size', [
                'banner',      // 728x90
                'leaderboard', // 970x250
                'rectangle',   // 300x250
                'sidebar',     // 300x600
                'mobile',      // 320x100
                'custom'       // Custom size
            ])->default('banner');
            $table->json('custom_dimensions')->nullable(); // {width: 400, height: 200}

            $table->enum('position', [
                'header',
                'sidebar_top',
                'sidebar_bottom',
                'content_top',
                'content_middle',
                'content_bottom',
                'footer',
                'popup',
                'floating'
            ])->default('sidebar_top');

            // TARGETING OPTIONS
            $table->json('target_locations')->nullable(); // Cities, regions to show banner
            $table->json('target_property_types')->nullable(); // house, apartment, commercial, etc
            $table->json('target_price_range')->nullable(); // {min: 100000, max: 500000}
            $table->json('target_pages')->nullable(); // Which pages to show on

            // SCHEDULING
            $table->datetime('start_date'); // When banner becomes active
            $table->datetime('end_date')->nullable(); // When banner expires
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['draft', 'active', 'paused', 'expired', 'rejected'])->default('draft');

            // PREMIUM FEATURES
            $table->boolean('is_featured')->default(false); // Premium placement
            $table->boolean('is_boosted')->default(false); // Paid boost
            $table->datetime('boost_start_date')->nullable();
            $table->datetime('boost_end_date')->nullable();
            $table->decimal('boost_amount', 10, 2)->nullable(); // Amount paid for boost
            $table->integer('display_priority')->default(0); // Higher = shows first

            // ANALYTICS
            $table->integer('views')->default(0); // How many times shown
            $table->integer('clicks')->default(0); // How many times clicked
            $table->decimal('ctr', 5, 4)->default(0); // Click-through rate
            $table->datetime('last_viewed_at')->nullable();
            $table->datetime('last_clicked_at')->nullable();

            // BUDGET & BILLING (if applicable)
            $table->enum('billing_type', ['free', 'fixed', 'per_click', 'per_impression'])->default('free');
            $table->decimal('budget_total', 10, 2)->nullable(); // Total budget
            $table->decimal('budget_spent', 10, 2)->default(0); // Amount spent so far
            $table->decimal('cost_per_click', 8, 4)->nullable();
            $table->decimal('cost_per_impression', 8, 6)->nullable();

            // ADDITIONAL CONTENT (Multi-language support)
            $table->json('call_to_action')->nullable(); // Button text in multiple languages {"en": "View Details", "ar": "عرض التفاصيل", "ku": "بینینی وردەکارییەکان"}
            $table->json('additional_images')->nullable(); // Gallery for carousel banners
            $table->json('terms_conditions')->nullable(); // Terms in multiple languages
            $table->boolean('show_contact_info')->default(false);
            $table->json('social_links')->nullable(); // Facebook, Instagram, etc.

            // APPROVAL & MODERATION
            $table->uuid('approved_by')->nullable(); // Admin who approved
            $table->datetime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();

            // METADATA
            $table->json('metadata')->nullable(); // Flexible field for additional data
            $table->string('created_by_ip')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Add soft deletes support

            // INDEXES FOR PERFORMANCE
            $table->index(['owner_type', 'owner_id']); // Find banners by owner
            $table->index(['banner_type', 'status']); // Filter by type and status
            $table->index(['is_active', 'start_date', 'end_date']); // Active banners in date range
            $table->index(['position', 'display_priority']); // Ordering for display
            $table->index(['is_featured', 'is_boosted']); // Premium banners
            $table->index(['status', 'approved_at']); // Moderation workflow
            $table->index('property_id'); // Link to properties
            $table->index(['views', 'clicks']); // Analytics queries
            $table->index(['created_at', 'owner_id']); // Recent banners by owner

            // FULL-TEXT SEARCH
            $table->fullText(['title', 'description']);

            // FOREIGN KEYS
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('banner_ads');
    }
};
