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
        Schema::create('login_activities', function (Blueprint $table) {
            $table->bigIncrements('login_activity_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Reference to user or agent
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('office_id')->nullable();
            $table->string('location'); // Location of the login
            $table->string('device');   // Device used for login
            $table->string('ip_address'); // IP address
            $table->timestamp('login_time'); // Timestamp for login
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
        Schema::dropIfExists('login_activities');
    }
};
