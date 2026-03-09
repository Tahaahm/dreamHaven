<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI MODEL METADATA
 * ─────────────────────────────────────────────────────────────────────────────
 * Tracks every trained ML model version.
 * Acts as a model registry — who trained what, when, how accurate.
 *
 * This table allows:
 * - Rolling back to a previous model if new one performs worse
 * - A/B testing between model versions
 * - Auditing prediction quality over time
 * - The Python service to know which model file to load
 *
 * Populated by: TrainAIModelsJob after each training run
 * ─────────────────────────────────────────────────────────────────────────────
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_model_metadata', function (Blueprint $table) {

            $table->id();

            // ── Model identity ────────────────────────────────────────────────
            $table->string('model_name', 100);
            // e.g. 'price_predictor', 'demand_predictor', 'zone_clusterer'

            $table->string('version', 20);
            // e.g. 'v1.0', 'v1.1', 'v2.0'

            $table->string('algorithm', 50)->default('xgboost');
            // e.g. 'xgboost', 'gradient_boosting', 'random_forest', 'kmeans'

            // ── File location ─────────────────────────────────────────────────
            // Path to .pkl file on the Python service server
            $table->string('model_file_path', 500)->nullable();
            $table->unsignedBigInteger('model_file_size_bytes')->default(0);

            // ── Training data info ────────────────────────────────────────────
            $table->unsignedInteger('training_samples')->default(0);
            $table->unsignedInteger('feature_count')->default(0);
            $table->json('feature_names')->nullable();
            // JSON array of feature names used in training

            $table->date('training_data_from')->nullable();
            $table->date('training_data_to')->nullable();

            // ── Accuracy metrics ──────────────────────────────────────────────
            // For regression models (price predictor):
            $table->decimal('rmse',        15, 2)->nullable(); // Root Mean Square Error
            $table->decimal('mae',         15, 2)->nullable(); // Mean Absolute Error
            $table->decimal('r2_score',    6, 4)->nullable();  // R² (0–1, higher is better)
            $table->decimal('mape',        6, 2)->nullable();  // Mean Abs % Error

            // For clustering models (zone clusterer):
            $table->decimal('silhouette_score', 6, 4)->nullable(); // -1 to 1
            $table->unsignedSmallInteger('n_clusters')->nullable();

            // ── Hyperparameters ───────────────────────────────────────────────
            // JSON snapshot of the hyperparameters used
            $table->json('hyperparameters')->nullable();

            // ── Training environment ──────────────────────────────────────────
            $table->string('python_version',     10)->nullable();
            $table->string('sklearn_version',    10)->nullable();
            $table->string('xgboost_version',    10)->nullable();
            $table->string('trained_on_server',  50)->nullable(); // e.g. 'contabo-vps-1'

            // ── Status & lifecycle ────────────────────────────────────────────
            // training | ready | active | deprecated | failed
            $table->enum('status', ['training', 'ready', 'active', 'deprecated', 'failed'])
                ->default('training');

            $table->boolean('is_active')->default(false);
            // Only ONE model per model_name should have is_active = true

            $table->text('notes')->nullable();
            $table->text('error_log')->nullable();

            $table->timestamp('training_started_at')->nullable();
            $table->timestamp('training_completed_at')->nullable();
            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index(['model_name', 'is_active']);
            $table->index(['model_name', 'status']);
            $table->index('version');
            $table->index('status');

            // One version per model name
            $table->unique(['model_name', 'version'], 'unique_model_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_model_metadata');
    }
};
