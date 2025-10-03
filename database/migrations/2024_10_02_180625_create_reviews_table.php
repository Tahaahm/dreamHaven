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
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('review_id');  // Unique identifier for the review
            $table->unsignedBigInteger('user_id');  // Reference to the user who submitted the review
            $table->unsignedBigInteger('property_id');  // Reference to the property being reviewed
            $table->integer('rating');  // Rating (e.g., 1-5 stars)
            $table->text('comment')->nullable();  // Review comment
            $table->timestamps();  // Created at and updated at

            // Foreign keys
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('property_id')->references('property_id')->on('properties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
