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
        Schema::create('real_estate_offices', function (Blueprint $table) {
            $table->bigIncrements('office_id');
            $table->string('office_name');
            $table->string('admin_name');
            $table->string('admin_email');
            $table->string('phone');
            $table->text('address')->nullable();
            $table->string('profile_photo')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_estate_offices'); // Ensure table name matches the one in the 'up' method
    }
};
