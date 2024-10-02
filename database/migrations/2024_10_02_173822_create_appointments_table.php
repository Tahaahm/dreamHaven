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
        Schema::create('appointments', function (Blueprint $table) {
            $table->bigIncrements('appointment_id');
            $table->unsignedBigInteger('user_id'); // Reference to the user (client)
            $table->unsignedBigInteger('agent_id')->nullable(); // Reference to the agent (if any)
            $table->unsignedBigInteger('office_id')->nullable(); // Reference to real estate office
            $table->date('date');
            $table->time('time');
            $table->enum('status', ['pending', 'processing', 'accepted'])->default('pending'); // Appointment status
            $table->text('location');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('agent_id')->references('agent_id')->on('agents')->onDelete('cascade');
            $table->foreign('office_id')->references('office_id')->on('real_estate_offices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
