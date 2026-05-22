<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->index();
            $table->foreign('conversation_id')
                ->references('id')
                ->on('chat_conversations')
                ->onDelete('cascade');

            // The Firestore message ID this media belongs to
            $table->string('firestore_message_id')->nullable()->index();

            // Uploader (polymorphic)
            $table->uuid('uploader_id');
            $table->string('uploader_type'); // App\Models\User|Agent|RealEstateOffice

            // File details
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('url');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            // type: image | file
            $table->enum('type', ['image', 'file'])->default('image');

            $table->timestamps();

            $table->index(['uploader_id', 'uploader_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_media');
    }
};