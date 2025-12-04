<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();

            // General counts
            $table->unsignedInteger('total_users')->default(0);
            $table->unsignedInteger('total_agents')->default(0);
            $table->unsignedInteger('total_offices')->default(0);
            $table->unsignedInteger('total_properties')->default(0);
            $table->unsignedInteger('properties_for_sale')->default(0);
            $table->unsignedInteger('properties_for_rent')->default(0);
            $table->unsignedInteger('properties_sold')->default(0);
            $table->unsignedInteger('properties_rented')->default(0);

            // Property performance
            $table->unsignedBigInteger('total_views')->default(0);
            $table->unsignedBigInteger('unique_views')->default(0);
            $table->unsignedBigInteger('returning_views')->default(0);
            $table->float('average_time_on_listing', 8, 2)->default(0); // seconds
            $table->float('bounce_rate', 5, 2)->default(0); // %

            $table->unsignedBigInteger('favorites_count')->default(0);

            // Agent performance
            $table->unsignedInteger('active_agents')->default(0);
            $table->unsignedInteger('agents_with_properties')->default(0);

            // Office performance
            $table->unsignedInteger('offices_with_listings')->default(0);

            // Banner analytics
            $table->unsignedInteger('active_banners')->default(0);
            $table->unsignedBigInteger('banners_clicked')->default(0);
            $table->unsignedBigInteger('banners_impressions')->default(0);

            // Advanced analytics
            $table->json('top_properties')->nullable();
            $table->json('top_agents')->nullable();
            $table->json('top_offices')->nullable();
            $table->json('top_banners')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_analytics');
    }
};
