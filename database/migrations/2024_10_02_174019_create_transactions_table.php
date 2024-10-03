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
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('transaction_id');
            $table->unsignedBigInteger('user_id');  // Reference to the user or agent involved in the transaction
            $table->unsignedBigInteger('subscription_id')->nullable(); // Reference to the subscription (nullable)
            $table->decimal('amount', 10, 2);  // Transaction amount
            $table->enum('transaction_type', ['subscription', 'advertising', 'payment']);  // Type of transaction
            $table->string('status');  // Payment status (e.g., completed, pending, failed)
            $table->string('invoice_url')->nullable();  // URL to the invoice or PDF
            $table->timestamps();

            // Define foreign keys
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
           // $table->foreign('subscription_id')->references('subscription_id')->on('subscriptions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
