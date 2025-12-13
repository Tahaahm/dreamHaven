<?php

// database/migrations/xxxx_xx_xx_create_branches_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            // Multi-language support for city name
            $table->string('city_name_en')->unique();
            $table->string('city_name_ar')->unique();
            $table->string('city_name_ku')->unique();

            // Location coordinates
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes(); // For soft deletion support

            // Indexes for better performance
            $table->index('is_active');
            $table->index(['latitude', 'longitude']);
            $table->index('city_name_en');
            $table->index('city_name_ar');
            $table->index('city_name_ku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
