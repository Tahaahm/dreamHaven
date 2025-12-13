<?php

// database/migrations/xxxx_xx_xx_create_areas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');

            // Multi-language support for area name
            $table->string('area_name_en');
            $table->string('area_name_ar');
            $table->string('area_name_ku');

            // Location coordinates
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes(); // For soft deletion support

            // Indexes for better performance
            $table->index('branch_id');
            $table->index('is_active');
            $table->index(['latitude', 'longitude']);
            $table->index('area_name_en');
            $table->index('area_name_ar');
            $table->index('area_name_ku');

            // Unique constraints: same area name can't exist twice in same branch
            $table->unique(['branch_id', 'area_name_en']);
            $table->unique(['branch_id', 'area_name_ar']);
            $table->unique(['branch_id', 'area_name_ku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
