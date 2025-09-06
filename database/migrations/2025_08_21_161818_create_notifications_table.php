<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Changed to UUID for consistency

            // Recipients - using UUID to match your other tables
            $table->uuid('user_id')->nullable();
            $table->uuid('agent_id')->nullable();
            $table->uuid('office_id')->nullable();

            $table->string('title');
            $table->text('message');
            $table->enum('type', ['property', 'appointment', 'system', 'promotion', 'alert'])->default('system');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Fixed foreign key references to match your table structures
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('office_id')->references('id')->on('real_estate_offices')->onDelete('cascade');

            // Indexes for better performance
            $table->index('user_id');
            $table->index('agent_id');
            $table->index('office_id');
            $table->index(['user_id', 'is_read']);
            $table->index('type');
            $table->index('priority');
            $table->index('sent_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
