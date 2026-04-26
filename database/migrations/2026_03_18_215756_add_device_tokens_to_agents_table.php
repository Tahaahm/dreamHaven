<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('agents', function (Blueprint $table) {
            if (!Schema::hasColumn('agents', 'device_tokens')) {
                $table->json('device_tokens')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('agents', 'language')) {
                $table->string('language', 10)->default('en')->after('updated_at');
            }
        });
    }

    public function down()
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn(['device_tokens', 'language']);
        });
    }
};