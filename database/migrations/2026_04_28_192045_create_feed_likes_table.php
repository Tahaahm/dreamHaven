<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_likes', function (Blueprint $table) {
            $table->id();

            $table->uuid('post_id')->index();
            $table->foreign('post_id')->references('id')->on('feed_posts')->onDelete('cascade');

            // Polymorphic liker — User / Agent / RealEstateOffice can all like
            $table->string('liker_id');
            $table->string('liker_type');

            $table->timestamps();

            // ── Critical: one like per user per post ────────────────────────
            // The DB enforces this — no need to check in Laravel first
            $table->unique(['post_id', 'liker_id', 'liker_type'], 'uq_post_like');

            // Fast "did this user like this post?" check
            $table->index(['liker_id', 'liker_type', 'post_id'], 'idx_liker_post');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_likes');
    }
};
