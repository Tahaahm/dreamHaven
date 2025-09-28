<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_inquiries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('user_id')->nullable(); // If user is registered

            // Contact information
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('country_code', 10)->default('+964');

            // Inquiry details
            $table->text('message')->nullable();
            $table->enum('inquiry_type', [
                'general_info',
                'price_inquiry',
                'site_visit',
                'booking_interest',
                'investment_query',
                'payment_plans',
                'amenities_info',
                'other'
            ])->default('general_info');

            // Interested unit details
            $table->json('interested_unit_types')->nullable(); // 1BR, 2BR, etc.
            $table->json('budget_range')->nullable(); // Min/max budget
            $table->enum('purpose', ['buy', 'invest', 'rent'])->nullable();
            $table->date('preferred_handover_date')->nullable();

            // Inquiry source tracking
            $table->enum('source', [
                'website',
                'mobile_app',
                'phone_call',
                'walk_in',
                'referral',
                'social_media',
                'advertisement',
                'other'
            ])->default('website');
            $table->string('referral_source')->nullable(); // If referral

            // Status management
            $table->enum('status', [
                'new',
                'contacted',
                'qualified',
                'site_visit_scheduled',
                'site_visit_completed',
                'negotiating',
                'converted',
                'not_interested',
                'closed'
            ])->default('new');
            $table->text('internal_notes')->nullable(); // Staff notes

            // Assignment and follow-up
            $table->uuid('assigned_to')->nullable(); // Sales agent assigned
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('follow_up_date')->nullable();
            $table->timestamp('site_visit_date')->nullable();

            // Response tracking
            $table->integer('contact_attempts')->default(0);
            $table->timestamp('last_contact_attempt')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->boolean('sms_sent')->default(false);

            // Lead scoring
            $table->integer('lead_score')->default(0); // 0-100
            $table->enum('lead_quality', ['hot', 'warm', 'cold'])->default('warm');
            $table->boolean('is_qualified')->default(false);

            $table->timestamps();

            // Foreign keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('project_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('inquiry_type');
            $table->index('lead_quality');
            $table->index('assigned_to');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_inquiries');
    }
};
