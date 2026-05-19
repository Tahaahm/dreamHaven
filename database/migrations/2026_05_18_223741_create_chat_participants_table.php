<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->index();
            $table->foreign('conversation_id')
                ->references('id')
                ->on('chat_conversations')
                ->onDelete('cascade');

            // Polymorphic participant: User | Agent | RealEstateOffice
            $table->uuid('participant_id');
            $table->string('participant_type'); // App\Models\User|Agent|RealEstateOffice

            // Role inside the conversation
            $table->enum('role', ['member', 'admin'])->default('member');

            // Mute & notification control per participant
            $table->boolean('is_muted')->default(false);

            // Track unread count per participant (incremented on new message,
            // reset to 0 when participant opens the conversation)
            $table->unsignedInteger('unread_count')->default(0);

            // Last time this participant read the conversation
            $table->timestamp('last_read_at')->nullable();

            // Soft-leave: participant leaves group but history is preserved
            $table->timestamp('left_at')->nullable();

            $table->timestamps();

            // Prevent duplicate participant in same conversation
            $table->unique(['conversation_id', 'participant_id', 'participant_type'], 'unique_participant');
            $table->index(['participant_id', 'participant_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
    }
};
