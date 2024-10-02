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
        Schema::create('agents', function (Blueprint $table) {
            $table->bigIncrements('agent_id');
            $table->unsignedBigInteger('office_id');  // Foreign key to real estate office
            $table->string('agent_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->boolean('is_verified')->default(false);
            $table->string('profile_photo')->nullable();
            $table->timestamps();

            $table->foreign('office_id')->references('office_id')->on('real_estate_offices')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
