<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

/**
 * AIBridgeService
 * ─────────────────────────────────────────────────────────────────────────────
 * Single point of contact between Laravel and the Python AI microservice.
 * All HTTP calls to the Python FastAPI service go through this class.
 *
 * Python service runs on: http://localhost:8001  (same Contabo VPS)
 * Configured via: AI_SERVICE_URL in .env
 *
 * Every method:
 * - Sets a timeout to prevent hung requests
 * - Logs errors without crashing the job
 * - Returns null on failure so callers can handle gracefully
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AIBridgeService
{
    private string $baseUrl;
    private int    $timeout;
    private int    $trainTimeout;

    public function __construct()
    {
        $this->baseUrl      = config('ai.service_url', 'http://localhost:8001');
        $this->timeout      = config('ai.timeout_seconds', 30);
        $this->trainTimeout = config('ai.train_timeout_seconds', 600); // 10 min for training
    }

    // ── Health ───────────────────────────────────────────────────────────────

    /**
     * Ping the Python service to check if it's running.
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('AIBridgeService: Python service health check failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ── Price Prediction ─────────────────────────────────────────────────────

    /**
     * Predict the fair market price for a single property.
     *
     * @param array $features Property features extracted from DB
     * @return array|null Prediction result or null on failure
     *
     * Expected response shape:
     * {
     *   predicted_price: float,
     *   predicted_price_per_m2: float,
     *   predicted_price_low: float,
     *   predicted_price_high: float,
     *   confidence_score: float,    // 0.0 – 1.0
     *   overprice_percent: float,
     *   underprice_percent: float,
     *   verdict: string,            // fair_value|overpriced|underpriced|great_deal
     *   comparable_ids: int[],
     *   model_version: string,
     * }
     */
    public function predictPrice(array $features): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/predict", [
                    'features' => $features,
                ]);

            if ($response->failed()) {
                Log::error('AIBridgeService::predictPrice failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (RequestException $e) {
            Log::error('AIBridgeService::predictPrice exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Batch predict prices for multiple properties.
     * More efficient than calling predictPrice() in a loop.
     *
     * @param array $propertiesFeatures Array of feature arrays, keyed by property_id
     * @return array|null Map of property_id => prediction result
     */
    public function predictPriceBatch(array $propertiesFeatures): ?array
    {
        try {
            $response = Http::timeout($this->trainTimeout)
                ->post("{$this->baseUrl}/predict/batch", [
                    'properties' => $propertiesFeatures,
                ]);

            if ($response->failed()) {
                Log::error('AIBridgeService::predictPriceBatch failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json('results');
        } catch (RequestException $e) {
            Log::error('AIBridgeService::predictPriceBatch exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ── Zone Clustering ──────────────────────────────────────────────────────

    /**
     * Run geospatial price zone clustering.
     * Python service pulls data from DB directly (given connection string)
     * or accepts property data as input.
     *
     * @param array $options  e.g. ['n_clusters' => 4, 'algorithm' => 'kmeans']
     * @param int|null $branchId  Scope to a specific city, or null for all
     * @return array|null GeoJSON FeatureCollection + metadata
     *
     * Expected response shape:
     * {
     *   version: int,
     *   algorithm: string,
     *   n_clusters: int,
     *   silhouette_score: float,
     *   zones: [
     *     {
     *       cluster_id: int,
     *       tier: string,
     *       color_hex: string,
     *       geojson_polygon: object,   // GeoJSON Feature
     *       centroid_lat: float,
     *       centroid_lng: float,
     *       bbox: { min_lat, max_lat, min_lng, max_lng },
     *       stats: { avg_price_per_m2, min, max, property_count, ... }
     *     }
     *   ]
     * }
     */
    public function clusterZones(array $options = [], ?int $branchId = null): ?array
    {
        try {
            $payload = array_merge($options, [
                'branch_id' => $branchId,
                'n_clusters' => $options['n_clusters'] ?? 4,
                'algorithm'  => $options['algorithm']  ?? 'kmeans',
            ]);

            $response = Http::timeout($this->trainTimeout)
                ->post("{$this->baseUrl}/cluster-zones", $payload);

            if ($response->failed()) {
                Log::error('AIBridgeService::clusterZones failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (RequestException $e) {
            Log::error('AIBridgeService::clusterZones exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ── Heatmap ──────────────────────────────────────────────────────────────

    /**
     * Generate heatmap tiles for all three types.
     *
     * @param string $type  'price' | 'demand' | 'density' | 'all'
     * @param float  $resolution  Grid cell size in degrees (default 0.005 ≈ 500m)
     * @param int|null $branchId
     * @return array|null Heatmap grid data
     *
     * Expected response shape:
     * {
     *   type: string,
     *   version: int,
     *   resolution: float,
     *   tiles: [
     *     { latitude, longitude, weight, raw_value, property_count,
     *       cell_min_lat, cell_max_lat, cell_min_lng, cell_max_lng }
     *   ]
     * }
     */
    public function generateHeatmap(
        string $type = 'all',
        float $resolution = 0.005,
        ?int $branchId = null
    ): ?array {
        try {
            $response = Http::timeout($this->trainTimeout)
                ->post("{$this->baseUrl}/heatmap", [
                    'type'       => $type,
                    'resolution' => $resolution,
                    'branch_id'  => $branchId,
                ]);

            if ($response->failed()) {
                Log::error('AIBridgeService::generateHeatmap failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (RequestException $e) {
            Log::error('AIBridgeService::generateHeatmap exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ── Model Training ───────────────────────────────────────────────────────

    /**
     * Trigger a full model training run on the Python service.
     *
     * @param string $modelType  'price_predictor' | 'demand_predictor'
     * @param array  $options    Hyperparameters to override defaults
     * @return array|null Training result with metrics
     *
     * Expected response shape:
     * {
     *   model_name: string,
     *   version: string,
     *   algorithm: string,
     *   training_samples: int,
     *   feature_names: string[],
     *   metrics: { rmse, mae, r2_score, mape },
     *   hyperparameters: object,
     *   model_file_path: string,
     *   model_file_size_bytes: int,
     *   training_started_at: string,
     *   training_completed_at: string,
     * }
     */
    public function trainModel(string $modelType, array $options = []): ?array
    {
        try {
            $response = Http::timeout($this->trainTimeout)
                ->post("{$this->baseUrl}/train", array_merge([
                    'model_type' => $modelType,
                ], $options));

            if ($response->failed()) {
                Log::error('AIBridgeService::trainModel failed', [
                    'model_type' => $modelType,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (RequestException $e) {
            Log::error('AIBridgeService::trainModel exception', [
                'model_type' => $modelType,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ── Area Insights ────────────────────────────────────────────────────────

    /**
     * Ask Python service to compute demand + liquidity scores for areas.
     * These are ML-based scores that go beyond simple SQL aggregation.
     *
     * @param array $areaIds  List of area IDs to compute scores for
     * @return array|null Map of area_id => { demand_score, liquidity_score }
     */
    public function computeAreaScores(array $areaIds): ?array
    {
        try {
            $response = Http::timeout($this->trainTimeout)
                ->post("{$this->baseUrl}/area-scores", [
                    'area_ids' => $areaIds,
                ]);

            if ($response->failed()) {
                Log::error('AIBridgeService::computeAreaScores failed', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            return $response->json('scores');
        } catch (RequestException $e) {
            Log::error('AIBridgeService::computeAreaScores exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
