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
        // Create categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('subtitle')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('is_active');
            $table->index('sort_order');
        });

        // Add category_id to service_providers table
        Schema::table('service_providers', function (Blueprint $table) {
            $table->uuid('category_id')->nullable()->after('id');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null'); // If category is deleted, set to null instead of deleting provider

            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key and column from service_providers
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);
            $table->dropColumn('category_id');
        });

        // Drop categories table
        Schema::dropIfExists('categories');
    }
};