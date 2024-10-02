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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['user', 'agent', 'real_estate_office']); // Define roles
            $table->unsignedBigInteger('office_id')->nullable(); // Link to real estate office if agent
            $table->boolean('is_verified')->default(false);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('office_id')->references('office_id')->on('real_estate_offices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
