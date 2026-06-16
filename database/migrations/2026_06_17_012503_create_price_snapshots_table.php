<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_price_snapshots_table
 *
 * Run: php artisan make:migration create_price_snapshots_table
 * Then replace the generated file with this content.
 *
 * php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('property_id');          // matches properties.id type
            $table->decimal('price', 15, 2);
            $table->string('currency', 10)->default('USD');
            $table->timestamp('snapped_at');
            $table->timestamp('created_at')->useCurrent();

            // Performance indexes
            $table->index(['property_id', 'snapped_at'], 'idx_price_snapshots_prop_date');
            $table->index('snapped_at', 'idx_price_snapshots_date');

            // FK (optional — remove if properties.id is not integer)
            // $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_snapshots');
    }
};