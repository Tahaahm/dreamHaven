<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── feed_follows ───────────────────────────────────────────────────
        // Polymorphic both sides:
        //   follower = who is following   (User / Agent / RealEstateOffice)
        //   followee = who is followed    (User / Agent / RealEstateOffice)
        //
        // This means:
        //   A User can follow an Agent           ✅
        //   A User can follow an Office          ✅
        //   A User can follow another User       ✅
        //   An Agent can follow an Office        ✅
        //   An Office can follow an Agent        ✅
        //   etc. — all combinations work

        Schema::create('feed_follows', function (Blueprint $table) {
            $table->id();

            // ── Who is following ────────────────────────────────────────────
            $table->string('follower_id');
            $table->string('follower_type'); // user | agent | office

            // ── Who is being followed ───────────────────────────────────────
            $table->string('followee_id');
            $table->string('followee_type'); // user | agent | office

            // ── Status ──────────────────────────────────────────────────────
            // 'accepted'  — normal public follow (instant)
            // 'pending'   — if you ever want private/approval-based accounts
            $table->enum('status', ['accepted', 'pending'])->default('accepted');

            $table->timestamps();

            // ── One follow relationship per pair — DB enforced ───────────────
            $table->unique(
                ['follower_id', 'follower_type', 'followee_id', 'followee_type'],
                'uq_follow_pair'
            );

            // ── Indexes ──────────────────────────────────────────────────────
            // "Who is following me?" (my followers list)
            $table->index(['followee_id', 'followee_type', 'status'], 'idx_followee');
            // "Who am I following?" (my following list)
            $table->index(['follower_id', 'follower_type', 'status'], 'idx_follower');
        });

        // ── Denormalized counters on existing author tables ─────────────────
        // Add followers_count + following_count to users, agents, offices
        // so we never run COUNT() on feed_follows to show profile badges

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('followers_count')->default(0)->after('last_activity_at');
            $table->unsignedInteger('following_count')->default(0)->after('followers_count');
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->unsignedInteger('followers_count')->default(0)->after('language');
            $table->unsignedInteger('following_count')->default(0)->after('followers_count');
        });

        Schema::table('real_estate_offices', function (Blueprint $table) {
            $table->unsignedInteger('followers_count')->default(0)->after('language');
            $table->unsignedInteger('following_count')->default(0)->after('followers_count');
        });
    }

    public function down(): void
    {
        Schema::table('real_estate_offices', function (Blueprint $table) {
            $table->dropColumn(['followers_count', 'following_count']);
        });
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn(['followers_count', 'following_count']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['followers_count', 'following_count']);
        });
        Schema::dropIfExists('feed_follows');
    }
};
