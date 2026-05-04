<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Neighborhood Feed Aggregates ────────────────────────────────────
        // Pre-computed stats per neighborhood per period.
        // Written by a scheduled Laravel job (every hour).
        // Reading this = ONE row instead of aggregating 50,000 view rows.
        // This is what powers:
        //   - "Trending in Ankawa" badges
        //   - Neighborhood Score price data
        //   - "Prices went up 8% this month" feed posts

        Schema::create('neighborhood_feeds', function (Blueprint $table) {
            $table->id();

            // Which neighborhood
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            // Period: "2026-04" (monthly) or "2026-W17" (weekly)
            $table->string('period', 20); // e.g. "2026-04", "2026-W17"
            $table->enum('period_type', ['weekly', 'monthly'])->default('monthly');

            // ── Content Stats ───────────────────────────────────────────────
            $table->unsignedInteger('total_posts')->default(0);
            $table->unsignedInteger('total_views')->default(0);
            $table->unsignedInteger('total_likes')->default(0);
            $table->unsignedInteger('total_comments')->default(0);
            $table->unsignedInteger('active_posters')->default(0); // Unique authors this period

            // ── Price Data (from property listings tagged to this area) ─────
            $table->decimal('avg_price_usd', 12, 2)->nullable();
            $table->decimal('avg_price_per_m2_usd', 10, 2)->nullable();
            $table->decimal('price_change_pct', 6, 2)->nullable(); // vs previous period (+8.5, -3.2)
            $table->unsignedInteger('total_listings')->default(0); // Properties listed this period

            // ── Trending Score (used to rank neighborhoods) ─────────────────
            // Formula: (views * 1) + (likes * 3) + (comments * 5) + (posts * 2)
            $table->unsignedInteger('trending_score')->default(0)->index();

            $table->timestamp('calculated_at')->nullable(); // When this row was last updated

            $table->timestamps();

            // One row per neighborhood per period — no duplicates
            $table->unique(['branch_id', 'period', 'period_type'], 'uq_neighborhood_period');

            // Fast neighborhood screen query
            $table->index(['period', 'trending_score'], 'idx_period_trending');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('neighborhood_feeds');
    }
};
