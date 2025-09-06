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
        Schema::table('service_providers', function (Blueprint $table) {
            // Add plan-related columns
            $table->string('plan_id')->nullable()->after('company_overview');
            $table->boolean('plan_active')->default(false)->after('plan_id');
            $table->timestamp('plan_expires_at')->nullable()->after('plan_active');

            // Add foreign key constraint
            $table->foreign('plan_id')
                ->references('id')
                ->on('service_provider_plans')
                ->onDelete('set null');

            // Add indexes for better performance
            $table->index('plan_id');
            $table->index(['plan_active', 'plan_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            // Drop foreign key and indexes first
            $table->dropForeign(['plan_id']);
            $table->dropIndex(['plan_id']);
            $table->dropIndex(['plan_active', 'plan_expires_at']);

            // Drop the columns
            $table->dropColumn(['plan_id', 'plan_active', 'plan_expires_at']);
        });
    }
};