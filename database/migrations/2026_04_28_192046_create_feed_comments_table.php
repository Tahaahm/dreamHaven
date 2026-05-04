<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('post_id')->index();
            $table->foreign('post_id')->references('id')->on('feed_posts')->onDelete('cascade');

            // Polymorphic author — same three types as post author
            $table->string('author_id');
            $table->string('author_type');

            // Comment body — multilingual
            $table->text('body_en')->nullable();
            $table->text('body_ar')->nullable();
            $table->text('body_ku')->nullable();

            // ── Threading: one level of replies only (like Instagram) ───────
            // parent_id = null → top-level comment
            // parent_id = UUID → reply to that comment
            $table->uuid('parent_id')->nullable()->index();
            $table->foreign('parent_id')->references('id')->on('feed_comments')->onDelete('cascade');

            // Denormalized like count on comment (same pattern as post)
            $table->unsignedInteger('likes_count')->default(0);

            // Moderation
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('approved');

            $table->timestamps();
            $table->softDeletes();

            // Fast "load all comments for a post" query
            $table->index(['post_id', 'status', 'created_at'], 'idx_comment_post_date');
            // Fast "load replies to a comment"
            $table->index(['parent_id', 'created_at'], 'idx_comment_replies');
            // "All comments by this author"
            $table->index(['author_id', 'author_type'], 'idx_comment_author');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_comments');
    }
};
