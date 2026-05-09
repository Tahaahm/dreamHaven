<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_notifications', function (Blueprint $table) {
            // ── Primary key ────────────────────────────────────────────────
            $table->id();

            // ── Post reference ─────────────────────────────────────────────
            $table->unsignedBigInteger('post_id');
            $table->enum('post_author_type', ['user', 'agent', 'office']);
            $table->unsignedBigInteger('post_author_id');

            // ── Action type ────────────────────────────────────────────────
            $table->enum('action_type', ['like', 'comment', 'save']);

            // ── Aggregated actor data ──────────────────────────────────────
            // actor_ids   → [1, 5, 23, ...]         (IDs who performed the action)
            // actor_types → ["user","agent","user",...] (parallel array)
            // actor_names → ["Ahmad","Zana","Taha",...] (display names, parallel)
            $table->json('actor_ids');
            $table->json('actor_types');
            $table->json('actor_names');

            // Running count — may exceed actor_ids length if same actor repeats
            $table->unsignedInteger('actor_count')->default(1);

            // ── Last actor snapshot (for notification text) ────────────────
            $table->unsignedBigInteger('last_actor_id')->nullable();
            $table->enum('last_actor_type', ['user', 'agent', 'office'])->nullable();
            $table->string('last_actor_name', 100)->nullable();

            // ── Comment preview (only populated when action_type = comment) ─
            // Stores a truncated preview of the most recent comment
            $table->string('last_comment_preview', 150)->nullable();

            // ── Cooldown & flush state ─────────────────────────────────────
            // last_updated_at  → bumped on every new action arriving
            // cooldown_until   → after FCM is sent, set to now() + 15 min
            //                    scheduler skips rows where cooldown_until > now()
            // is_flushed       → TRUE only during the brief flush window;
            //                    reset to FALSE after cooldown so next batch works
            $table->timestamp('last_updated_at');
            $table->timestamp('cooldown_until')->nullable();
            $table->boolean('is_flushed')->default(false);
            $table->timestamp('flushed_at')->nullable();

            // ── Link to saved notification (filled after flush) ────────────
            // After FCM is sent, the scheduler also saves a row in `notifications`
            // and stores its UUID here for traceability
            $table->char('notification_id', 36)->nullable(); // UUID FK → notifications.id

            $table->timestamps();

            // ── Indexes ────────────────────────────────────────────────────
            // Scheduler query: unflushed rows past their cooldown
            $table->index(['is_flushed', 'cooldown_until'], 'idx_flush_queue');

            // Look up pending row for a specific post + action (upsert key)
            $table->index(['post_id', 'action_type'], 'idx_post_action');

            // Look up all pending notifications for a post author
            $table->index(['post_author_id', 'post_author_type'], 'idx_author');

            // Unique: one pending row per post + action_type
            // (we upsert into this row on every like/comment/save)
            $table->unique(['post_id', 'action_type'], 'uq_post_action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_notifications');
    }
};