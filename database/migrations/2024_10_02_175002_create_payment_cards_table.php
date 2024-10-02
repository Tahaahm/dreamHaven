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
        Schema::create('payment_cards', function (Blueprint $table) {
            $table->bigIncrements('card_id');  // Unique identifier for the payment card
            $table->unsignedBigInteger('agent_id')->nullable();  // Foreign key for agents
            $table->unsignedBigInteger('office_id')->nullable();  // Foreign key for real estate offices
            $table->string('cardholder_name');  // Name on the card
            $table->string('card_number');  // Card number
            $table->string('expiry_date');  // Expiration date (MM/YY)
            $table->string('cvv');  // CVV code (encrypted)
            $table->timestamps();  // created_at and updated_at

            $table->foreign('agent_id')->references('agent_id')->on('agents')->onDelete('set null');
            $table->foreign('office_id')->references('office_id')->on('real_estate_offices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_cards');
    }
};
