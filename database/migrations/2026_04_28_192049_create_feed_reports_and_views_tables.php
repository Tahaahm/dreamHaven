<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Reports (moderation queue) ─────────────────────────────────────
        Schema::create('feed_reports', function (Blueprint $table) {
            $table->id();

            $table->uuid('post_id')->index();
            $table->foreign('post_id')->references('id')->on('feed_posts')->onDelete('cascade');

            // Who reported it
            $table->string('reporter_id');
            $table->string('reporter_type');

            $table->enum('reason', [
                'spam',
                'fake_listing',
                'inappropriate',
                'misleading_price',
                'harassment',
                'other',
            ]);

            $table->text('notes')->nullable(); // Reporter's extra explanation

            $table->enum('status', [
                'pending',   // In admin queue
                'resolved',  // Admin took action
                'dismissed', // Admin dismissed — post was fine
            ])->default('pending')->index();

            $table->string('reviewed_by')->nullable(); // Admin user ID who handled it
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // Prevent duplicate reports from same user on same post
            $table->unique(['post_id', 'reporter_id', 'reporter_type'], 'uq_post_report');
        });

        // ── Post Views (for trending + analytics) ──────────────────────────
        // IMPORTANT: This table gets the most writes in the whole system.
        // Strategy: batch writes from Flutter (send array every 5 seconds)
        //           + daily cleanup job (delete rows older than 30 days)
        //           + aggregate into neighborhood_feeds for long-term data
        Schema::create('feed_post_views', function (Blueprint $table) {
            $table->id();

            $table->uuid('post_id')->index();
            $table->foreign('post_id')->references('id')->on('feed_posts')->onDelete('cascade');

            // Nullable user_id — guests can view too
            $table->string('viewer_id')->nullable();
            $table->string('viewer_type')->nullable();

            // Guest tracking — same pattern as your existing session IDs
            $table->string('guest_token', 64)->nullable();

            $table->timestamp('viewed_at')->useCurrent();

            // ── Indexes for trending calculation ────────────────────────────
            // "How many views did this post get in the last 24h?"
            $table->index(['post_id', 'viewed_at'], 'idx_view_post_time');
            // "Views by this user" (deduplication check)
            $table->index(['viewer_id', 'viewer_type', 'post_id'], 'idx_view_user_post');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_post_views');
        Schema::dropIfExists('feed_reports');
    }
};
