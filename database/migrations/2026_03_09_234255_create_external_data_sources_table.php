<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EXTERNAL DATA SOURCES
 * ─────────────────────────────────────────────────────────────────────────────
 * Stores enrichment data points from external sources.
 * These are Points of Interest (POIs) and infrastructure data
 * that improve price predictions and demand modeling.
 *
 * Sources:
 * - OpenStreetMap (free, reliable for Kurdistan region)
 * - Manual data entry via admin panel
 * - Future: government APIs if available
 *
 * Used by:
 * - Python feature engineering (nearby POI count per property)
 * - Investment score development_score component
 * - Map UI (show nearby schools/hospitals on property detail)
 * ─────────────────────────────────────────────────────────────────────────────
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_data_sources', function (Blueprint $table) {

            $table->id();

            // ── POI Identity ──────────────────────────────────────────────────
            $table->string('name', 200);

            // Category: school | hospital | shopping | transport |
            //           university | park | mosque | government | bank
            $table->string('category', 50);
            $table->string('subcategory', 50)->nullable(); // e.g. "primary_school"

            // ── Location ──────────────────────────────────────────────────────
            $table->decimal('latitude',  10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address', 300)->nullable();

            // ── Area & branch reference ───────────────────────────────────────
            $table->unsignedBigInteger('area_id')->nullable();
            $table->foreign('area_id')
                ->references('id')
                ->on('areas')
                ->onDelete('set null');

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('set null');

            // ── Impact weight on property value (1–10) ────────────────────────
            // Higher = more positive impact on nearby property prices
            // e.g. hospital=8, school=7, park=5, mosque=4
            $table->unsignedTinyInteger('impact_weight')->default(5);

            // ── Data source ───────────────────────────────────────────────────
            $table->string('source', 50)->default('manual');
            // e.g. 'openstreetmap', 'manual', 'government', 'scraped'
            $table->string('source_id', 100)->nullable(); // external ID from source
            $table->string('source_url', 500)->nullable();

            // ── Quality ───────────────────────────────────────────────────────
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);

            // ── Additional metadata ───────────────────────────────────────────
            $table->json('meta')->nullable();
            // e.g. { "rating": 4.5, "capacity": 500, "year_opened": 2018 }

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            // Radius search: find all POIs within bounding box of a property
            $table->index(['latitude', 'longitude']);
            $table->index('category');
            $table->index('area_id');
            $table->index('branch_id');
            $table->index(['category', 'is_active']);
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_data_sources');
    }
};
