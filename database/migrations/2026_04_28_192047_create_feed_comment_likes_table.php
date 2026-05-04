<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_comment_likes', function (Blueprint $table) {
            $table->id();

            $table->uuid('comment_id')->index();
            $table->foreign('comment_id')->references('id')->on('feed_comments')->onDelete('cascade');

            // Polymorphic liker
            $table->string('liker_id');
            $table->string('liker_type');

            $table->timestamps();

            // One like per user per comment — DB enforced
            $table->unique(['comment_id', 'liker_id', 'liker_type'], 'uq_comment_like');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_comment_likes');
    }
};
