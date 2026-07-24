<?php

/*
| Save as: database/migrations/2026_07_24_000000_create_visits_table.php
| Then run: php artisan migrate
|
| One row per visitor per day. Guests are identified by a salted hash of
| IP + user agent (or the device id your Flutter app sends), so no raw IP
| is ever stored.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            // Who (anonymously)
            $table->char('visitor_hash', 64);
            $table->date('visit_date');

            // Logged in later in the day? We fill this in when we know.
            $table->string('user_id')->nullable()->index();

            // Where they came from
            $table->string('source', 12)->default('web');   // web | app | api
            $table->string('platform', 32)->nullable();     // android | ios | browser
            $table->string('country', 2)->nullable();
            $table->string('landing_path', 191)->nullable();
            $table->string('referrer', 191)->nullable();

            // How much they did
            $table->unsignedInteger('sessions')->default(1);
            $table->unsignedInteger('hits')->default(1);

            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            // One row per visitor per day
            $table->unique(['visitor_hash', 'visit_date'], 'visits_unique_per_day');

            // Dashboard queries
            $table->index(['visit_date', 'source'], 'visits_date_source_idx');
            $table->index('last_seen_at', 'visits_last_seen_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
