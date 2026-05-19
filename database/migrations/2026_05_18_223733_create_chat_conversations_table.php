<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // direct | group
            $table->enum('type', ['direct', 'group'])->default('direct');

            // Group-only fields
            $table->string('name')->nullable();           // group display name
            $table->string('avatar')->nullable();         // group avatar URL
            $table->uuid('created_by')->nullable();       // participant who created it
            $table->string('created_by_type')->nullable(); // App\Models\User|Agent|RealEstateOffice

            // Last message preview (denormalized for inbox performance)
            $table->text('last_message')->nullable();
            $table->string('last_message_type')->default('text'); // text|image|property
            $table->uuid('last_message_sender_id')->nullable();
            $table->string('last_message_sender_type')->nullable();
            $table->timestamp('last_message_at')->nullable();

            // Property context — when chat is initiated from a property listing
            $table->uuid('property_id')->nullable();
            $table->index('property_id');

            // 30-day auto-purge: reset every time a new message is sent
            $table->timestamp('expires_at')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
