<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Subscription Plans Table
 *
 * Run this migration with:
 * php artisan migrate
 *
 * To create this migration file, use:
 * php artisan make:migration create_subscription_plans_table
 */

class CreateSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('name');
            $table->string('type')->comment('banner, services, real_estate_office, agent');
            $table->text('description')->nullable();

            // Duration
            $table->integer('duration_months');
            $table->string('duration_label')->comment('1_month, 3_months, 6_months, 1_year');

            // Pricing in IQD (Iraqi Dinar)
            $table->decimal('original_price_iqd', 15, 2)->default(0)->comment('Original price before discount');
            $table->decimal('discount_iqd', 15, 2)->default(0)->comment('Discount amount');
            $table->decimal('final_price_iqd', 15, 2)->comment('Final price after discount');
            $table->decimal('price_per_month_iqd', 15, 2)->comment('Monthly cost');
            $table->decimal('total_amount_iqd', 15, 2)->comment('Total amount to pay');

            // Pricing in USD (US Dollar)
            $table->decimal('original_price_usd', 15, 2)->default(0)->comment('Original price before discount');
            $table->decimal('discount_usd', 15, 2)->default(0)->comment('Discount amount');
            $table->decimal('final_price_usd', 15, 2)->comment('Final price after discount');
            $table->decimal('price_per_month_usd', 15, 2)->comment('Monthly cost');
            $table->decimal('total_amount_usd', 15, 2)->comment('Total amount to pay');

            // Discount Information
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('Discount percentage (e.g., 50.00 for 50%)');
            $table->decimal('savings_percentage', 5, 2)->default(0)->comment('Savings percentage compared to monthly');

            // Agent Subscription Specific Fields
            $table->integer('max_properties')->nullable()->comment('Maximum number of properties allowed (for agent subscriptions)');
            $table->decimal('price_per_property_iqd', 10, 2)->nullable()->comment('Cost per property in IQD');
            $table->decimal('price_per_property_usd', 10, 2)->nullable()->comment('Cost per property in USD');

            // Features and Conditions (stored as JSON)
            $table->json('features')->nullable()->comment('List of features included in this plan');
            $table->json('conditions')->nullable()->comment('Terms and conditions for this plan');

            // Additional Information
            $table->text('note')->nullable()->comment('Additional notes or information');

            // Status and Display
            $table->boolean('active')->default(true)->comment('Is this plan active and available for purchase?');
            $table->boolean('is_featured')->default(false)->comment('Should this plan be featured/highlighted?');
            $table->integer('sort_order')->default(0)->comment('Display order (lower numbers appear first)');

            // Savings Comparison
            $table->decimal('savings_vs_monthly_iqd', 15, 2)->nullable()->comment('Savings compared to monthly payment in IQD');
            $table->decimal('savings_vs_monthly_usd', 15, 2)->nullable()->comment('Savings compared to monthly payment in USD');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better query performance
            $table->index('type');
            $table->index('active');
            $table->index(['type', 'active']);
            $table->index(['type', 'duration_months']);
            $table->index('is_featured');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
}
