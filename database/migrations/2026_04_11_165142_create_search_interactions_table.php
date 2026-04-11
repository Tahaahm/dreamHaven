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
        Schema::create('search_interactions', function (Blueprint $table) {
            $table->id();

            // Link to users table. Nullable allows tracking guest searches.
            // Using constrained() and onDelete('cascade') ensures data integrity.
            $table->uuid('user_id')->nullable()->index();

            // Store the full FilterCriteria object from Flutter as JSON.
            $table->json('filters');

            // Store common filter values in their own columns for faster analytics/querying later.
            $table->string('city')->nullable()->index();
            $table->string('property_type')->nullable();
            $table->string('listing_type')->nullable(); // sale or rent

            // Track how many properties were found with these filters.
            // Helpful for identifying if users are finding what they need.
            $table->integer('results_count')->default(0);

            // Store device info to identify unique guests across sessions.
            $table->string('device_id')->nullable()->index();
            $table->string('device_name')->nullable();

            $table->timestamps();

            // Foreign key relationship
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_interactions');
    }
};
