<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Feed Post Saves / Favorites ────────────────────────────────────
        // This is separate from property favorites (user_favorite_properties)
        // This saves a FEED POST — the social content
        Schema::create('feed_saves', function (Blueprint $table) {
            $table->id();

            $table->uuid('post_id')->index();
            $table->foreign('post_id')->references('id')->on('feed_posts')->onDelete('cascade');

            // Polymorphic saver — User / Agent / RealEstateOffice
            $table->string('saver_id');
            $table->string('saver_type');

            $table->timestamps();

            // One save per user per post — DB enforced
            $table->unique(['post_id', 'saver_id', 'saver_type'], 'uq_post_save');

            // "Show me all my saved posts" tab query
            $table->index(['saver_id', 'saver_type', 'created_at'], 'idx_saver_posts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_saves');
    }
};
