<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_notifications', function (Blueprint $table) {
            $table->string('post_author_type', 100)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pending_notifications', function (Blueprint $table) {
            $table->enum('post_author_type', ['user', 'agent', 'office'])->change();
        });
    }
};
