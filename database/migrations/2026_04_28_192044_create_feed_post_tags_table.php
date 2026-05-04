<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_post_tags', function (Blueprint $table) {
            $table->id();

            $table->uuid('post_id');
            $table->foreign('post_id')->references('id')->on('feed_posts')->onDelete('cascade');

            // e.g. "ankawa", "villa", "investment", "for-rent", "new-project"
            $table->string('tag', 60)->index();

            $table->timestamps();

            // Prevent duplicate tags on the same post
            $table->unique(['post_id', 'tag'], 'uq_post_tag');
            // Fast "search by tag" query
            $table->index(['tag', 'post_id'], 'idx_tag_posts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_post_tags');
    }
};
