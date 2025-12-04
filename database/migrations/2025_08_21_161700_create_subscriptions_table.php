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
        Schema::create('Subscriptions', function (Blueprint $table) {
            // Primary identifier
            $table->uuid('id')->primary();

            // User relationship
            $table->string('user_id');

            // Subscription status
            $table->enum('status', ['active', 'inactive', 'suspended', 'cancelled'])
                ->default('inactive');

            // Subscription dates
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // Billing configuration
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->boolean('auto_renewal')->default(true);

            // Property limits and usage
            $table->integer('property_activation_limit')->default(0);
            $table->integer('properties_activated_this_month')->default(0);
            $table->integer('remaining_activations')->default(0);

            // Billing dates
            $table->date('next_billing_date')->nullable();
            $table->date('last_payment_date')->nullable();

            // Trial configuration
            $table->boolean('trial_period')->default(false);
            $table->date('trial_end_date')->nullable();

            // Pricing
            $table->decimal('monthly_amount', 10, 2)->default(0.00);

            // Mid-cycle changes & prorated billing
            $table->string('current_plan_id')->nullable();
            $table->string('pending_plan_id')->nullable();
            $table->date('plan_change_date')->nullable();
            $table->enum('plan_change_type', ['upgrade', 'downgrade'])->nullable();
            $table->decimal('prorated_amount', 10, 2)->default(0.00);
            $table->integer('prorated_days')->default(0);
            $table->decimal('credit_balance', 10, 2)->default(0.00);
            $table->enum('proration_method', allowed: ['daily', 'none'])->default('daily');

            // Laravel timestamps
            $table->timestamps();

            // Indexes for better performance
            $table->index('user_id');
            $table->index('status');
            $table->index(['status', 'next_billing_date']);
            $table->index('trial_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Subscriptions');
    }
};
