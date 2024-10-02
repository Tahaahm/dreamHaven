<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('project_id');  // Unique identifier for the project
            $table->unsignedBigInteger('office_id');  // Foreign key to the real estate office
            $table->string('name');  // Name of the project
            $table->text('description')->nullable();  // Description of the project
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');  // Project status
            $table->string('image')->nullable();  // URL to an image of the project
            $table->timestamps();  // Created at and updated at

            $table->foreign('office_id')->references('office_id')->on('real_estate_offices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
