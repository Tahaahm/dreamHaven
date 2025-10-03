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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->bigIncrements('preference_id');
            $table->unsignedBigInteger('user_id')->nullable();  // For user preferences
            $table->unsignedBigInteger('agent_id')->nullable();  // For agent preferences
            $table->unsignedBigInteger('office_id')->nullable(); // For real estate office preferences
            $table->boolean('property_updates')->default(true);  // Receive property update notifications
            $table->boolean('new_messages')->default(true);  // Receive new message notifications
            $table->boolean('reminders')->default(true);  // Receive reminder notifications
            $table->boolean('login_activity')->default(true);  // Receive login activity notifications
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
        Schema::dropIfExists('notification_preferences');
    }
};
