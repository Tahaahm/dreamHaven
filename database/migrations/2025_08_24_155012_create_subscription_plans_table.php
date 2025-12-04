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
        Schema::create('Subscription_plans', function (Blueprint $table) {
            // Primary identifier
            $table->string('id')->primary(); // Using string ID like "starter_plan"

            // Basic plan information
            $table->string('name');
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('monthly_price', 10, 2)->default(0.00);
            $table->decimal('annual_price', 10, 2)->default(0.00);

            // Plan limits
            $table->integer('property_activation_limit')->default(0);
            $table->integer('team_members')->default(1);

            // Plan features (stored as JSON)
            $table->json('features')->nullable();

            // Trial and marketing
            $table->integer('trial_days')->default(0);
            $table->boolean('most_popular')->default(false);
            $table->integer('banner')->default(0);

            // Overage pricing (stored as JSON for flexibility)
            $table->json('overage_pricing')->nullable();

            // Status and ordering
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);

            // Laravel timestamps
            $table->timestamps();

            // Indexes
            $table->index('active');
            $table->index(['active', 'sort_order']);
            $table->index('most_popular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Subscription_plans');
    }
};
