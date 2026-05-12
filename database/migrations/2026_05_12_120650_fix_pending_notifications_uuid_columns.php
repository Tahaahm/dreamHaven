<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_notifications', function (Blueprint $table) {
            $table->string('post_id', 36)->change();
            $table->string('post_author_id', 36)->change();
            $table->string('last_actor_id', 36)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pending_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id')->change();
            $table->unsignedBigInteger('post_author_id')->change();
            $table->unsignedBigInteger('last_actor_id')->nullable()->change();
        });
    }
};
