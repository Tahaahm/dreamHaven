<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_posts', function (Blueprint $table) {
            // ── Primary Key ────────────────────────────────────────────────
            $table->uuid('id')->primary();

            // ── Polymorphic Author (User / Agent / RealEstateOffice) ───────
            // Matches your existing owner_type / owner_id pattern on Property
            $table->string('author_id');           // UUID of the author
            $table->string('author_type');         // App\Models\User | App\Models\Agent | App\Models\RealEstateOffice

            // ── Post Content ───────────────────────────────────────────────
            $table->enum('post_type', [
                'general',           // Normal text/photo post
                'listing_share',     // Sharing a property from Dream Mulk
                'market_update',     // "Prices in Ankawa went up 8%"
                'question',          // "Anyone know a good agent in Gulan?"
                'milestone',         // "Just bought my apartment!"
                'tip',               // Real estate advice
                'office_announcement', // Office posting about new projects
            ])->default('general');

            // Multilingual body — matches your existing DText / JSON pattern
            $table->text('body_en')->nullable();
            $table->text('body_ar')->nullable();
            $table->text('body_ku')->nullable();

            // ── Location (nullable = public / not area-specific) ───────────
            // NOT required — a post can be global or tied to a neighborhood
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');

            // ── Linked Property (optional — for listing_share posts) ───────
            $table->uuid('property_id')->nullable()->index();
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');

            // ── Status ─────────────────────────────────────────────────────
            $table->enum('status', [
                'pending',    // Awaiting moderation (for new/flagged users)
                'approved',   // Visible in feed
                'rejected',   // Hidden by admin
                'hidden',     // Hidden by author themselves
            ])->default('approved')->index();

            // ── Denormalized Counters (avoid COUNT() on every feed load) ───
            // Increment/decrement these when likes/comments/saves happen
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('saves_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);

            // ── Admin Controls ─────────────────────────────────────────────
            $table->boolean('is_pinned')->default(false);      // Pin to top of neighborhood feed
            $table->boolean('is_featured')->default(false);    // Featured in explore/home

            // ── Timestamps ─────────────────────────────────────────────────
            $table->timestamps();
            $table->softDeletes(); // Never hard-delete — needed for moderation history

            // ── Indexes for feed queries ────────────────────────────────────
            // "Show approved posts in Ankawa sorted by newest"
            $table->index(['branch_id', 'status', 'created_at'], 'idx_feed_branch_status_date');
            // "Show all posts by this agent"
            $table->index(['author_id', 'author_type', 'created_at'], 'idx_feed_author_date');
            // "Show pinned posts first"
            $table->index(['status', 'is_pinned', 'created_at'], 'idx_feed_status_pinned');
            // "Global feed — no area filter"
            $table->index(['status', 'created_at'], 'idx_feed_global');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_posts');
    }
};
