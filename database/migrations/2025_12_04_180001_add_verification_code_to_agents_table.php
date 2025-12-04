<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('agents', function (Blueprint $table) {
        $table->string('verification_code', 6)->nullable();
    });
}

public function down()
{
    Schema::table('agents', function (Blueprint $table) {
        $table->dropColumn('verification_code');
    });
}

};
