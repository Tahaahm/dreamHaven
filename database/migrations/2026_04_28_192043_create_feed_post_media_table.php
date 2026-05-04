<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_post_media', function (Blueprint $table) {
            $table->id();

            $table->uuid('post_id')->index();
            $table->foreign('post_id')->references('id')->on('feed_posts')->onDelete('cascade');

            // ── Media Type ─────────────────────────────────────────────────
            $table->enum('media_type', [
                'image',   // JPG / PNG / WEBP
                'video',   // MP4 — uploaded by user
            ])->default('image');

            // ── File Paths ─────────────────────────────────────────────────
            $table->string('url');                    // Full URL to the file on your server/storage
            $table->string('thumbnail_url')->nullable(); // For video: extracted first-frame thumbnail
            // For image: compressed preview version

            // ── Video Metadata ─────────────────────────────────────────────
            $table->unsignedInteger('duration_seconds')->nullable(); // Video length
            $table->string('mime_type')->nullable();                 // video/mp4, image/jpeg etc.
            $table->unsignedBigInteger('file_size_bytes')->nullable(); // For upload validation

            // ── Display ────────────────────────────────────────────────────
            $table->unsignedTinyInteger('sort_order')->default(0); // Gallery order
            $table->string('alt_text')->nullable();                // Accessibility / SEO

            $table->timestamps();

            // Index for fast gallery loading per post
            $table->index(['post_id', 'sort_order'], 'idx_media_post_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_post_media');
    }
};
