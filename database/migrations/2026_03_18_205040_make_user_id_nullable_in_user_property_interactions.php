<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_property_interactions', function (Blueprint $table) {
            $table->string('user_id', 100)->nullable()->change(); // allow guest_ prefix strings
            $table->string('session_id', 100)->nullable()->after('user_id'); // track guest session
        });
    }

    public function down(): void
    {
        Schema::table('user_property_interactions', function (Blueprint $table) {
            $table->uuid('user_id')->nullable(false)->change();
            $table->dropColumn('session_id');
        });
    }
};
