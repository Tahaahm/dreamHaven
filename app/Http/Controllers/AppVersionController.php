<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FirebaseFirestoreService;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AppVersionController extends Controller
{
    protected $firestoreService;

    public function __construct(FirebaseFirestoreService $firestoreService)
    {
        $this->firestoreService = $firestoreService;
    }

    /**
     * Get current app version from Firestore
     *
     * @return JsonResponse
     */
    public function getCurrentVersion(): JsonResponse
    {
        try {
            $result = $this->firestoreService->getAppVersion();

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to fetch app version',
                    'data' => null
                ], $result['skipped'] ?? false ? 503 : 404);
            }

            $appVersion = AppVersion::fromFirestore($result['data']);

            return response()->json([
                'success' => true,
                'message' => 'App version retrieved successfully',
                'data' => [
                    'version' => $appVersion->version,
                    'buildNumber' => $appVersion->buildNumber,
                    'minSupportedVersion' => $appVersion->minSupportedVersion,
                    'forceUpdate' => $appVersion->forceUpdate,
                    'updateMessage' => $appVersion->updateMessage,
                    'androidUrl' => $appVersion->androidUrl,
                    'iosUrl' => $appVersion->iosUrl,
                    'releaseDate' => $appVersion->releaseDate,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching app version', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching app version',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if app version needs update
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkVersion(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'current_version' => 'required|string',
                'platform' => 'required|in:android,ios',
                'build_number' => 'sometimes|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $currentVersion = $request->input('current_version');
            $platform = $request->input('platform');

            // Get latest version from Firestore
            $result = $this->firestoreService->getAppVersion();

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to fetch app version',
                    'data' => null
                ], $result['skipped'] ?? false ? 503 : 404);
            }

            $appVersion = AppVersion::fromFirestore($result['data']);

            // Get update information
            $updateInfo = $appVersion->getUpdateInfo($currentVersion, $platform);

            Log::info('Version check completed', [
                'current_version' => $currentVersion,
                'latest_version' => $appVersion->version,
                'needs_update' => $updateInfo['needs_update'],
                'force_update' => $updateInfo['force_update']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Version check completed',
                'data' => $updateInfo
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error checking version', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error checking version',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update app version in Firestore (Admin only)
     * This endpoint should be protected by admin middleware
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateVersion(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'version' => 'required|string',
                'buildNumber' => 'required|integer',
                'minSupportedVersion' => 'required|string',
                'forceUpdate' => 'required|boolean',
                'updateMessage' => 'required|string',
                'androidUrl' => 'required|url',
                'iosUrl' => 'required|url',
                'releaseDate' => 'sometimes|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $versionData = $request->only([
                'version',
                'buildNumber',
                'minSupportedVersion',
                'forceUpdate',
                'updateMessage',
                'androidUrl',
                'iosUrl',
                'releaseDate'
            ]);

            // If releaseDate not provided, use current timestamp
            if (!isset($versionData['releaseDate'])) {
                $versionData['releaseDate'] = now()->toISOString();
            }

            $result = $this->firestoreService->updateAppVersion($versionData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to update app version',
                ], 500);
            }

            Log::info('App version updated by admin', [
                'version' => $versionData['version'],
                'buildNumber' => $versionData['buildNumber'],
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'App version updated successfully',
                'data' => $result['data']
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating version', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating version',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
