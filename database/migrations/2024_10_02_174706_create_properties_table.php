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
        Schema::create('properties', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Ensure this line is present
            $table->bigIncrements('property_id'); // Unique identifier for the property
            $table->unsignedBigInteger('agent_id')->nullable(); // Agent managing the property
            $table->unsignedBigInteger('office_id')->nullable(); // Real estate office managing the property
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('address')->nullable();
            $table->json('location'); // GeoPoint (latitude, longitude) as JSON
           
            $table->string('property_type'); // e.g., 'apartment', 'house', 'commercial'
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('kitchen_rooms')->nullable();
            $table->integer('reception_rooms')->nullable();
            $table->integer('flooring')->nullable();
            $table->float('area'); // Area in square meters
            $table->json('images'); // List of image URLs
            $table->string('video_tour')->nullable(); // URL to video tour
            $table->json('amenities')->nullable(); // List of amenities (e.g., pool, gym)
            $table->string('project_name')->nullable(); // Name of the project (if applicable)
            $table->text('project_description')->nullable(); // Description of the project
            $table->enum('status', ['available', 'sold', 'rented'])->default('available'); // Property status
            $table->enum('listing_type', ['rent', 'sell']); // Type of listing
            $table->float('rating')->default(0); // Average rating
            $table->integer('views')->default(0); // Number of views
            $table->integer('favorites_count')->default(0); // Number of favorites
            $table->boolean('availability')->default(true); // Availability status
            $table->timestamps(); // createdAt and updatedAt

            $table->foreign('agent_id')->references('agent_id')->on('agents')->onDelete('set null');
            $table->foreign('office_id')->references('office_id')->on('real_estate_offices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
