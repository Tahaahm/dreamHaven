<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MARKET TRENDS
 * ─────────────────────────────────────────────────────────────────────────────
 * Time-series price snapshots per area.
 * One row = one area on one date.
 *
 * This is the historical data that powers:
 * - Price growth calculations (7d, 30d, 90d)
 * - Trend charts in the Flutter app
 * - AI model training (time-series features)
 *
 * Populated by: SnapshotMarketTrendsJob (runs daily at midnight)
 * Consumed by:  GET /market/trends?area_id=X&period=30d
 *
 * Design: append-only. Never UPDATE rows. Only INSERT new daily snapshots.
 * This preserves the full historical record.
 * ─────────────────────────────────────────────────────────────────────────────
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_trends', function (Blueprint $table) {

            $table->id();

            // ── Area reference ────────────────────────────────────────────────
            $table->unsignedBigInteger('area_id');
            $table->foreign('area_id')
                ->references('id')
                ->on('areas')
                ->onDelete('cascade');

            // ── Snapshot date ─────────────────────────────────────────────────
            // DATE type (not datetime) — one snapshot per area per day
            $table->date('snapshot_date');

            // ── Price metrics on this date ────────────────────────────────────
            $table->decimal('avg_price',         15, 2)->default(0);
            $table->decimal('median_price',      15, 2)->default(0);
            $table->decimal('avg_price_per_m2',  10, 2)->default(0);
            $table->decimal('min_price',         15, 2)->default(0);
            $table->decimal('max_price',         15, 2)->default(0);

            // ── Volume metrics ────────────────────────────────────────────────
            $table->unsignedInteger('listing_count')->default(0);
            $table->unsignedInteger('new_listings')->default(0);     // added today
            $table->unsignedInteger('removed_listings')->default(0); // removed today

            // ── Demand metric on this date ────────────────────────────────────
            $table->decimal('demand_score',   5, 2)->default(0);
            $table->decimal('liquidity_score', 5, 2)->default(0);

            // ── Day-over-day change ───────────────────────────────────────────
            // Computed at insert time vs previous day's row
            $table->decimal('price_change_vs_yesterday', 6, 2)->default(0);

            // ── Property type breakdown ───────────────────────────────────────
            // JSON: { "apartment": 120000, "villa": 350000, "house": 180000 }
            // Stores average price per property type on this day
            $table->json('price_by_type')->nullable();

            // ── Listing type breakdown ────────────────────────────────────────
            // JSON: { "sale": 45, "rent": 30 }
            $table->json('count_by_listing_type')->nullable();

            // ── Period flag ───────────────────────────────────────────────────
            // 'daily' | 'weekly' | 'monthly'
            // Weekly and monthly snapshots are computed separately for efficiency
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])
                ->default('daily');

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            // Primary query pattern: WHERE area_id = ? AND snapshot_date >= ?
            $table->index(['area_id', 'snapshot_date']);
            $table->index(['area_id', 'period_type', 'snapshot_date']);
            $table->index('snapshot_date');

            // One row per area per date per period type
            $table->unique(
                ['area_id', 'snapshot_date', 'period_type'],
                'unique_area_date_period'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_trends');
    }
};
