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
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('notification_id');
            $table->unsignedBigInteger('user_id')->nullable();  // For user notifications
            $table->unsignedBigInteger('agent_id')->nullable();  // For agent notifications
            $table->unsignedBigInteger('office_id')->nullable(); // For real estate office notifications
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Corrected foreign key references
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade'); // Changed to 'user_id'
            $table->foreign('agent_id')->references('agent_id')->on('agents')->onDelete('cascade'); // Ensure 'agent_id' is referenced correctly
            $table->foreign('office_id')->references('office_id')->on('real_estate_offices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
