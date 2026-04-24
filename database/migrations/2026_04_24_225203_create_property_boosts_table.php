<?php

// database/migrations/2024_01_01_000001_create_property_boosts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_boosts', function (Blueprint $table) {
            $table->id();
            $table->string('property_id');             // matches properties.id (string PK)
            $table->string('owner_id');
            $table->string('owner_type');

            // Plan
            $table->string('plan_id');                 // starter|growth|pro|max
            $table->string('plan_name');
            $table->decimal('amount_paid', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('payment_ref')->nullable();
            $table->string('payment_method')->nullable(); // wallet|fib|card|cash

            // Status
            $table->enum('status', ['active', 'expired', 'cancelled'])
                ->default('active');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->timestamp('cancelled_at')->nullable();

            // Snapshot at purchase time (for baseline comparison)
            $table->integer('views_at_start')->default(0);
            $table->integer('reach_at_start')->default(0);

            // Extra data
            $table->json('meta')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('property_id');
            $table->index(['property_id', 'status']);
            $table->index('owner_id');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_boosts');
    }
};