<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Participants - using UUID to match your other tables
            $table->uuid('user_id');
            $table->uuid('agent_id')->nullable(); // Already nullable in the original creation
            $table->uuid('office_id')->nullable(); // Already nullable in the original creation
            $table->uuid('property_id')->nullable(); // Changed to uuid for consistency

            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->enum('type', ['viewing', 'consultation', 'signing', 'inspection'])->default('viewing');

            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('client_name');
            $table->string('client_phone')->nullable();
            $table->string('client_email')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // Foreign key references to match your table structures
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('set null');
            $table->foreign('office_id')->references('id')->on('real_estate_offices')->onDelete('set null');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');

            // Indexes for better performance
            $table->index('user_id');
            $table->index('agent_id');
            $table->index('office_id');
            $table->index('property_id');
            $table->index(['user_id', 'status']);
            $table->index(['agent_id', 'status']);
            $table->index(['office_id', 'status']);
            $table->index('appointment_date');
            $table->index(['appointment_date', 'appointment_time']);
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};