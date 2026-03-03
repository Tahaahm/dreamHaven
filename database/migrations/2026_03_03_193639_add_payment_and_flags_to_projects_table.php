<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {

            // Payment & Installment
            if (!Schema::hasColumn('projects', 'down_payment_percentage')) {
                $table->decimal('down_payment_percentage', 5, 2)
                      ->nullable()
                      ->after('pricing_currency');
            }

            if (!Schema::hasColumn('projects', 'installment_available')) {
                $table->boolean('installment_available')
                      ->default(false)
                      ->after('down_payment_percentage');
            }

            if (!Schema::hasColumn('projects', 'installment_months')) {
                $table->integer('installment_months')
                      ->nullable()
                      ->after('installment_available');
            }

            // Project Flags
            if (!Schema::hasColumn('projects', 'is_hot_project')) {
                $table->boolean('is_hot_project')
                      ->default(false)
                      ->after('is_premium');
            }

            // Analytics
            if (!Schema::hasColumn('projects', 'units_sold')) {
                $table->integer('units_sold')
                      ->default(0)
                      ->after('favorites_count');
            }

            if (!Schema::hasColumn('projects', 'site_visits_count')) {
                $table->integer('site_visits_count')
                      ->default(0)
                      ->after('units_sold');
            }

            if (!Schema::hasColumn('projects', 'bookings_count')) {
                $table->integer('bookings_count')
                      ->default(0)
                      ->after('site_visits_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $columns = [
                'down_payment_percentage',
                'installment_available',
                'installment_months',
                'is_hot_project',
                'units_sold',
                'site_visits_count',
                'bookings_count',
            ];

            $existing = array_filter(
                $columns,
                fn($col) => Schema::hasColumn('projects', $col)
            );

            if (!empty($existing)) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};