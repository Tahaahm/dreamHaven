<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AREA MARKET INSIGHTS
 * ─────────────────────────────────────────────────────────────────────────────
 * Stores pre-computed aggregated market metrics per area.
 * Populated by the ComputeAreaInsightsJob scheduler (every 6 hours).
 * Read-only from the API — never written to by user requests.
 *
 * Linked to: areas.id (existing table — NOT modified)
 * ─────────────────────────────────────────────────────────────────────────────
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_market_insights', function (Blueprint $table) {

            $table->id();

            // ── Foreign key to existing areas table ───────────────────────────
            $table->unsignedBigInteger('area_id');
            $table->foreign('area_id')
                ->references('id')
                ->on('areas')
                ->onDelete('cascade');

            // ── Price metrics ─────────────────────────────────────────────────
            $table->decimal('average_price',         15, 2)->default(0);
            $table->decimal('median_price',          15, 2)->default(0);
            $table->decimal('min_price',             15, 2)->default(0);
            $table->decimal('max_price',             15, 2)->default(0);
            $table->decimal('average_price_per_m2',  10, 2)->default(0);
            $table->decimal('median_price_per_m2',   10, 2)->default(0);

            // ── Listing volume ────────────────────────────────────────────────
            $table->unsignedInteger('listing_count')->default(0);
            $table->unsignedInteger('active_listings')->default(0);
            $table->unsignedInteger('sold_listings')->default(0);       // future
            $table->decimal('average_days_on_market', 8, 2)->default(0);

            // ── Demand & liquidity scores (0.00 – 100.00) ────────────────────
            // demand_score   = weighted score based on listing views, inquiries,
            //                  recent listing velocity
            // liquidity_score= how quickly properties sell in this area
            $table->decimal('demand_score',   5, 2)->default(0);
            $table->decimal('liquidity_score', 5, 2)->default(0);

            // ── Price growth (percentage change, can be negative) ─────────────
            $table->decimal('price_growth_7d',  6, 2)->default(0);
            $table->decimal('price_growth_30d', 6, 2)->default(0);
            $table->decimal('price_growth_90d', 6, 2)->default(0);
            $table->decimal('price_growth_1y',  6, 2)->default(0);

            // ── Investment score (0.00 – 100.00) ─────────────────────────────
            $table->decimal('investment_score', 5, 2)->default(0);

            // ── Price tier classification ─────────────────────────────────────
            // Values: 'affordable' | 'medium' | 'expensive' | 'luxury'
            $table->enum('price_tier', ['affordable', 'medium', 'expensive', 'luxury'])
                ->default('medium');

            // ── Currency used for all price fields ────────────────────────────
            $table->string('currency', 10)->default('USD');

            // ── Snapshot timestamp: when was this insight computed ────────────
            $table->timestamp('computed_at')->nullable();

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            // Unique: only one insight row per area at any time
            $table->unique('area_id');
            $table->index('price_tier');
            $table->index('investment_score');
            $table->index('demand_score');
            $table->index('computed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_market_insights');
    }
};
