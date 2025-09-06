<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transaction_reference')->unique()->nullable();

            $table->uuid('property_id')->nullable();
            $table->uuid('buyer_user_id')->nullable();
            $table->uuid('seller_user_id')->nullable();
            $table->uuid('agent_id')->nullable();
            $table->uuid('office_id')->nullable();

            $table->enum('type', ['sale', 'rent', 'lease'])->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'failed'])->nullable();

            $table->decimal('amount_iqd', 15, 2)->nullable();
            $table->decimal('amount_usd', 12, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable();

            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'installment', 'mortgage'])->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'completed', 'refunded'])->nullable();

            $table->string('contract_number')->nullable();
            $table->timestamp('contract_date')->nullable();
            $table->timestamp('completion_date')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('payment_breakdown')->nullable();
            $table->json('documents')->nullable();

            $table->timestamps();

            // Foreign key references - all nullable to prevent cascade issues
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');
            $table->foreign('buyer_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('seller_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('set null');
            $table->foreign('office_id')->references('id')->on('real_estate_offices')->onDelete('set null');

            // Indexes for performance
            $table->index('property_id');
            $table->index('buyer_user_id');
            $table->index('seller_user_id');
            $table->index('agent_id');
            $table->index('office_id');
            $table->index('status');
            $table->index('type');
            $table->index('payment_status');
            $table->index('contract_date');
            $table->index(['status', 'type']);
            $table->index('transaction_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};