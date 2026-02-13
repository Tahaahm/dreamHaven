<?php

namespace App\Http\Controllers;

use App\Services\VideoFrameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VideoProcessingController extends Controller
{
    protected $videoFrameService;

    public function __construct(VideoFrameService $videoFrameService)
    {
        $this->videoFrameService = $videoFrameService;
    }

    /**
     * Extract best frames from uploaded video
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function extractFrames(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'video' => [
                'required',
                'file',
                'mimes:mp4,avi,mov,mkv,webm,flv',
                'max:512000' // 500MB in KB
            ],
            'num_frames' => 'nullable|integer|min:5|max:20'
        ], [
            'video.required' => 'Please upload a video file',
            'video.mimes' => 'Video must be MP4, AVI, MOV, MKV, WEBM, or FLV',
            'video.max' => 'Video file must not exceed 500MB',
            'num_frames.min' => 'Minimum 5 frames required',
            'num_frames.max' => 'Maximum 20 frames allowed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $videoFile = $request->file('video');
            $numFrames = $request->input('num_frames', 10);

            Log::info('Video frame extraction requested', [
                'filename' => $videoFile->getClientOriginalName(),
                'size_mb' => round($videoFile->getSize() / 1024 / 1024, 2),
                'num_frames' => $numFrames,
                'user_id' => auth()->id()
            ]);

            // Extract frames using Python AI service
            $result = $this->videoFrameService->extractFrames($videoFile, $numFrames);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to extract frames from video',
                    'error' => $result['error'] ?? 'Unknown error'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully extracted {$numFrames} best frames from video",
                'data' => [
                    'frames' => $result['frames'],
                    'scores' => $result['scores'],
                    'metadata' => $result['metadata']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Video frame extraction error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the video',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Check if video AI service is available
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkServiceHealth()
    {
        $isHealthy = $this->videoFrameService->checkHealth();

        return response()->json([
            'success' => true,
            'service_available' => $isHealthy,
            'message' => $isHealthy
                ? 'Video AI service is operational'
                : 'Video AI service is currently unavailable'
        ]);
    }

    /**
     * Get video AI service statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServiceStats()
    {
        $stats = $this->videoFrameService->getStats();

        if (!$stats) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service statistics'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Cleanup old files from video AI service
     * (Admin only)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanup(Request $request)
    {
        // Check if user is admin
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $maxAgeHours = $request->input('max_age_hours', 1);

        $result = $this->videoFrameService->cleanup($maxAgeHours);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger cleanup'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cleanup completed',
            'data' => $result
        ]);
    }
}