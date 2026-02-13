<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoFrameService
{
    protected $apiUrl;
    protected $timeout;

    public function __construct()
    {
        // Python AI service URL
        $this->apiUrl = config('services.video_ai.url', 'http://127.0.0.1:8001');
        $this->timeout = config('services.video_ai.timeout', 300); // 5 minutes
    }

    /**
     * Extract frames from video using Python AI service
     *
     * @param UploadedFile $videoFile
     * @param int $numFrames
     * @return array
     */
    public function extractFrames(UploadedFile $videoFile, int $numFrames = 10): array
    {
        try {
            Log::info('VideoFrameService: Starting frame extraction', [
                'filename' => $videoFile->getClientOriginalName(),
                'size' => $videoFile->getSize(),
                'num_frames' => $numFrames
            ]);

            // Validate file
            $maxSize = 500 * 1024 * 1024; // 500MB
            if ($videoFile->getSize() > $maxSize) {
                return [
                    'success' => false,
                    'error' => 'Video file too large (max 500MB)'
                ];
            }

            $allowedMimes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'];
            if (!in_array($videoFile->getMimeType(), $allowedMimes)) {
                return [
                    'success' => false,
                    'error' => 'Unsupported video format'
                ];
            }

            // Call Python AI service
            $response = Http::timeout($this->timeout)
                ->attach('video', file_get_contents($videoFile->getRealPath()), $videoFile->getClientOriginalName())
                ->post($this->apiUrl . '/extract-frames', [
                    'num_frames' => $numFrames
                ]);

            if (!$response->successful()) {
                Log::error('VideoFrameService: API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => 'Frame extraction service unavailable'
                ];
            }

            $data = $response->json();

            if (!isset($data['success']) || !$data['success']) {
                Log::error('VideoFrameService: Extraction failed', [
                    'response' => $data
                ]);

                return [
                    'success' => false,
                    'error' => $data['message'] ?? 'Frame extraction failed'
                ];
            }

            // Convert relative URLs to full URLs
            $baseUrl = rtrim($this->apiUrl, '/');
            if (isset($data['data']['frames'])) {
                $data['data']['frames'] = array_map(function ($frame) use ($baseUrl) {
                    return $baseUrl . $frame;
                }, $data['data']['frames']);
            }

            Log::info('VideoFrameService: Frame extraction successful', [
                'num_frames' => count($data['data']['frames'] ?? [])
            ]);

            return [
                'success' => true,
                'frames' => $data['data']['frames'] ?? [],
                'scores' => $data['data']['scores'] ?? [],
                'metadata' => $data['data']['metadata'] ?? []
            ];
        } catch (\Exception $e) {
            Log::error('VideoFrameService: Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while processing the video'
            ];
        }
    }

    /**
     * Check if the Python AI service is healthy
     *
     * @return bool
     */
    public function checkHealth(): bool
    {
        try {
            $response = Http::timeout(10)->get($this->apiUrl . '/health');

            if (!$response->successful()) {
                return false;
            }

            $data = $response->json();

            return isset($data['status']) && $data['status'] === 'healthy';
        } catch (\Exception $e) {
            Log::warning('VideoFrameService: Health check failed', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get service statistics
     *
     * @return array|null
     */
    public function getStats(): ?array
    {
        try {
            $response = Http::timeout(10)->get($this->apiUrl . '/stats');

            if (!$response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::warning('VideoFrameService: Stats request failed', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Trigger cleanup of old files
     *
     * @param int $maxAgeHours
     * @return array|null
     */
    public function cleanup(int $maxAgeHours = 1): ?array
    {
        try {
            $response = Http::timeout(30)->post($this->apiUrl . '/cleanup', [
                'max_age_hours' => $maxAgeHours
            ]);

            if (!$response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::warning('VideoFrameService: Cleanup request failed', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Download frame from Python service and save locally
     *
     * @param string $frameUrl
     * @param string $localPath
     * @return bool
     */
    public function downloadFrame(string $frameUrl, string $localPath): bool
    {
        try {
            $response = Http::timeout(30)->get($frameUrl);

            if (!$response->successful()) {
                return false;
            }

            // Ensure directory exists
            $directory = dirname($localPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($localPath, $response->body());

            return true;
        } catch (\Exception $e) {
            Log::error('VideoFrameService: Frame download failed', [
                'url' => $frameUrl,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}