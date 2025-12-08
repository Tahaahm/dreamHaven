<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Firestore;
use Kreait\Firebase\Exception\FirebaseException;
use App\Models\User;
use Carbon\Carbon;

class FirebaseFirestoreService
{
    private $firestore = null;
    private $initialized = false;
    private $initializationFailed = false;
    private $serviceAccountPath = null;
    /**
     * Lazy initialization - only connect when actually needed
     */
    private function initializeFirestore()
    {
        Log::info('🔵 [FIRESTORE] initializeFirestore() called', [
            'initialized' => $this->initialized,
            'initializationFailed' => $this->initializationFailed,
            'firestore_is_null' => $this->firestore === null
        ]);

        // Already initialized (success or failure)
        if ($this->initialized || $this->initializationFailed) {
            Log::info('🟡 [FIRESTORE] Already initialized, returning cached state', [
                'success' => $this->firestore !== null
            ]);
            return $this->firestore !== null;
        }

        // Check if Firestore is enabled
        $firestoreEnabled = config('firebase.firestore_enabled', false);
        Log::info('🔵 [FIRESTORE] Checking if enabled', [
            'firestore_enabled' => $firestoreEnabled
        ]);

        if (!$firestoreEnabled) {
            Log::info('🟠 [FIRESTORE] Firestore disabled via configuration');
            $this->initialized = true;
            return false;
        }
        $serviceAccountPath = config('firebase.service_account_path');

        try {
            Log::info('🔵 [FIRESTORE] Creating Firestore with custom config...');

            // Use Google Cloud Firestore directly with timeout settings
            $this->firestore = new \Google\Cloud\Firestore\FirestoreClient([
                'keyFilePath' => $serviceAccountPath,
                'transport' => 'grpc', // Use gRPC for better performance
                'transportConfig' => [
                    'grpc' => [
                        'stubOpts' => [
                            'timeout' => 5000000, // 5 seconds in microseconds
                        ]
                    ]
                ]
            ]);

            $this->initialized = true;
            Log::info('✅ [FIRESTORE] Firestore initialized with timeout');

            return true;
        } catch (\Exception $e) {
            Log::error('❌ [FIRESTORE] Initialization failed', [
                'error' => $e->getMessage()
            ]);

            $this->initializationFailed = true;
            $this->firestore = null;

            return false;
        }
    }
    /**
     * Create user document in Firestore
     */
    public function createUserDocument(User $user, array $additionalData = [])
    {
        if ($this->firestore === null) {
            Log::warning('Firestore not available, skipping user document creation', [
                'user_id' => $user->id,
                'suggestion' => 'Install google/cloud-firestore package to enable Firestore'
            ]);
            return [
                'success' => false,
                'error' => 'Firestore client not available',
                'skipped' => true
            ];
        }

        try {
            // Prepare user data for Firestore
            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'place' => $user->place,
                'lat' => $user->lat ? (float) $user->lat : null,
                'lng' => $user->lng ? (float) $user->lng : null,
                'about_me' => $user->about_me,
                'photo_image' => $user->photo_image,
                'language' => $user->language ?? 'en',
                'search_preferences' => $user->search_preferences ?? [],
                'device_tokens' => $user->device_tokens ?? [],
                'is_active' => true,
                'created_at' => $user->created_at ? $user->created_at->toISOString() : now()->toISOString(),
                'updated_at' => $user->updated_at ? $user->updated_at->toISOString() : now()->toISOString(),
                'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toISOString() : null,
                'last_login_at' => $user->last_login_at ? $user->last_login_at->toISOString() : null,
            ];

            // Add any additional data
            $userData = array_merge($userData, $additionalData);

            // Remove null values to keep Firestore clean
            $userData = array_filter($userData, function ($value) {
                return $value !== null;
            });

            // Create document in 'users' collection using user ID as document ID
            $docRef = $this->firestore
                ->database()
                ->collection('users')
                ->document($user->id);

            $docRef->set($userData);

            Log::info('Firestore user document created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'document_id' => $user->id
            ]);

            return [
                'success' => true,
                'document_id' => $user->id,
                'data' => $userData
            ];
        } catch (FirebaseException $e) {
            Log::error('Failed to create Firestore user document', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('Unexpected error creating Firestore user document', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user document in Firestore
     */
    public function updateUserDocument(User $user, array $updateData = [])
    {
        // ✅ Initialize Firestore first
        if (!$this->initializeFirestore()) {
            Log::warning('Firestore not available, skipping user document update', [
                'user_id' => $user->id,
                'suggestion' => 'Enable Firestore in .env'
            ]);
            return [
                'success' => false,
                'error' => 'Firestore client not available',
                'skipped' => true
            ];
        }

        try {
            // Prepare update data
            $firestoreUpdateData = [];

            $allowedFields = [
                'username',
                'phone',
                'place',
                'lat',
                'lng',
                'about_me',
                'photo_image',
                'language',
                'search_preferences',
                'device_tokens'
            ];

            foreach ($allowedFields as $field) {
                if (isset($updateData[$field]) || isset($user->$field)) {
                    $value = $updateData[$field] ?? $user->$field;

                    // Handle special types
                    if (in_array($field, ['lat', 'lng']) && $value !== null) {
                        $firestoreUpdateData[$field] = (float) $value;
                    } elseif (in_array($field, ['search_preferences', 'device_tokens'])) {
                        $firestoreUpdateData[$field] = is_array($value) ? $value : [];
                    } else {
                        $firestoreUpdateData[$field] = $value;
                    }
                }
            }

            // Always update the timestamp
            $firestoreUpdateData['updated_at'] = now()->toISOString();

            // Remove null values
            $firestoreUpdateData = array_filter($firestoreUpdateData, function ($value) {
                return $value !== null;
            });

            if (empty($firestoreUpdateData)) {
                Log::warning('No data to update in Firestore', ['user_id' => $user->id]);
                return ['success' => true, 'message' => 'No data to update'];
            }

            // Update document
            $docRef = $this->firestore
                ->database()
                ->collection('users')
                ->document($user->id);

            $docRef->update($firestoreUpdateData);

            Log::info('Firestore user document updated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'updated_fields' => array_keys($firestoreUpdateData)
            ]);

            return [
                'success' => true,
                'updated_fields' => array_keys($firestoreUpdateData),
                'data' => $firestoreUpdateData
            ];
        } catch (FirebaseException $e) {
            Log::error('Failed to update Firestore user document', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('Unexpected error updating Firestore user document', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user document from Firestore
     */
    public function getUserDocument($userId)
    {
        // ✅ Initialize Firestore first
        if (!$this->initializeFirestore()) {
            return [
                'success' => false,
                'error' => 'Firestore not available',
                'exists' => false
            ];
        }

        try {
            $docRef = $this->firestore
                ->database()
                ->collection('users')
                ->document($userId);

            $snapshot = $docRef->snapshot();

            if (!$snapshot->exists()) {
                return [
                    'success' => false,
                    'error' => 'User document not found',
                    'exists' => false
                ];
            }

            $userData = $snapshot->data();

            Log::info('Firestore user document retrieved successfully', [
                'user_id' => $userId
            ]);

            return [
                'success' => true,
                'exists' => true,
                'data' => $userData
            ];
        } catch (FirebaseException $e) {
            Log::error('Failed to get Firestore user document', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exists' => false
            ];
        }
    }

    /**
     * Delete user document from Firestore
     */
    public function deleteUserDocument($userId)
    {
        // ✅ Initialize Firestore first
        if (!$this->initializeFirestore()) {
            return [
                'success' => false,
                'error' => 'Firestore not available'
            ];
        }

        try {
            $docRef = $this->firestore
                ->database()
                ->collection('users')
                ->document($userId);

            $docRef->delete();

            Log::info('Firestore user document deleted successfully', [
                'user_id' => $userId
            ]);

            return ['success' => true];
        } catch (FirebaseException $e) {
            Log::error('Failed to delete Firestore user document', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if user document exists in Firestore
     */
    public function userDocumentExists($userId)
    {
        // ✅ Initialize Firestore first
        if (!$this->initializeFirestore()) {
            return false;
        }

        try {
            $docRef = $this->firestore
                ->database()
                ->collection('users')
                ->document($userId);

            $snapshot = $docRef->snapshot();
            return $snapshot->exists();
        } catch (FirebaseException $e) {
            Log::error('Error checking Firestore user document existence', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Create user's sub-collections (appointments, favorites, etc.)
     */
    public function createUserSubCollections(User $user)
    {
        // ✅ Initialize Firestore first
        if (!$this->initializeFirestore()) {
            Log::warning('Firestore not available, skipping sub-collections creation', [
                'user_id' => $user->id
            ]);
            return [
                'success' => false,
                'error' => 'Firestore client not available',
                'skipped' => true
            ];
        }

        try {
            $userId = $user->id;

            // Create initial documents in sub-collections to establish them
            $subCollections = [
                'appointments' => [
                    'created_at' => now()->toISOString(),
                    'placeholder' => true,
                    'note' => 'This is a placeholder document to create the collection'
                ],
                'favorites' => [
                    'created_at' => now()->toISOString(),
                    'placeholder' => true,
                    'note' => 'This is a placeholder document to create the collection'
                ],
                'notifications' => [
                    'created_at' => now()->toISOString(),
                    'placeholder' => true,
                    'note' => 'This is a placeholder document to create the collection'
                ],
                'search_history' => [
                    'created_at' => now()->toISOString(),
                    'placeholder' => true,
                    'note' => 'This is a placeholder document to create the collection'
                ]
            ];

            foreach ($subCollections as $collectionName => $placeholderData) {
                $this->firestore
                    ->database()
                    ->collection('users')
                    ->document($userId)
                    ->collection($collectionName)
                    ->document('placeholder')
                    ->set($placeholderData);
            }

            Log::info('User sub-collections created successfully', [
                'user_id' => $userId,
                'collections' => array_keys($subCollections)
            ]);

            return ['success' => true, 'collections' => array_keys($subCollections)];
        } catch (FirebaseException $e) {
            Log::error('Failed to create user sub-collections', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Batch write multiple operations
     */
    public function batchWrite(array $operations)
    {
        // ✅ Initialize Firestore first
        if (!$this->initializeFirestore()) {
            return [
                'success' => false,
                'error' => 'Firestore not available'
            ];
        }

        try {
            $batch = $this->firestore->database()->batch();

            foreach ($operations as $operation) {
                $docRef = $this->firestore
                    ->database()
                    ->collection($operation['collection'])
                    ->document($operation['document']);

                switch ($operation['type']) {
                    case 'set':
                        $batch->set($docRef, $operation['data']);
                        break;
                    case 'update':
                        $batch->update($docRef, $operation['data']);
                        break;
                    case 'delete':
                        $batch->delete($docRef);
                        break;
                }
            }

            $batch->commit();

            Log::info('Firestore batch write completed successfully', [
                'operations_count' => count($operations)
            ]);

            return ['success' => true, 'operations_count' => count($operations)];
        } catch (FirebaseException $e) {
            Log::error('Firestore batch write failed', [
                'error' => $e->getMessage(),
                'operations_count' => count($operations)
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Firestore instance for custom operations
     */
    public function getFirestore()
    {
        $this->initializeFirestore();
        return $this->firestore;
    }

    /**
     * Get app version from Firestore
     */
    public function getAppVersion()
    {
        Log::info('🔵 [APP_VERSION] getAppVersion() called');

        // Try to initialize (will return false if disabled or failed)
        Log::info('🔵 [APP_VERSION] Calling initializeFirestore()...');
        $initResult = $this->initializeFirestore();
        Log::info('🔵 [APP_VERSION] initializeFirestore() returned', [
            'result' => $initResult
        ]);

        if (!$initResult) {
            Log::info('📦 [APP_VERSION] Firestore not available - returning mock data');

            return [
                'success' => true,
                'exists' => true,
                'data' => [
                    'version' => '1.0.0',
                    'buildNumber' => 1,
                    'minSupportedVersion' => '1.0.0',
                    'forceUpdate' => false,
                    'updateMessage' => 'Please update to the latest version for the best experience.',
                    'androidUrl' => 'https://play.google.com/store/apps/details?id=com.dreamhaven',
                    'iosUrl' => 'https://apps.apple.com/app/dream-haven/id123456789',
                    'releaseDate' => now()->toISOString(),
                ]
            ];
        }

        // Firestore is available - fetch real data
        try {
            Log::info('🔵 [APP_VERSION] Firestore available, about to fetch document...');
            Log::info('⏰ [APP_VERSION] Before collection() call: ' . now()->format('Y-m-d H:i:s.u'));

            // ✅ CORRECT: Google Cloud Firestore API - no database() method
            $collection = $this->firestore->collection('app_config');
            Log::info('⏰ [APP_VERSION] After collection() call: ' . now()->format('Y-m-d H:i:s.u'));

            Log::info('🔵 [APP_VERSION] Getting document reference...');
            $docRef = $collection->document('version');
            Log::info('⏰ [APP_VERSION] After document() call: ' . now()->format('Y-m-d H:i:s.u'));

            Log::info('🔵 [APP_VERSION] About to call snapshot() - THIS MAY HANG...');
            $snapshot = $docRef->snapshot();
            Log::info('⏰ [APP_VERSION] After snapshot() call: ' . now()->format('Y-m-d H:i:s.u'));

            Log::info('🔵 [APP_VERSION] Snapshot retrieved successfully', [
                'exists' => $snapshot->exists()
            ]);

            if (!$snapshot->exists()) {
                Log::warning('🟠 [APP_VERSION] Document not found in Firestore');
                return [
                    'success' => false,
                    'error' => 'App version document not found',
                    'exists' => false
                ];
            }

            Log::info('🔵 [APP_VERSION] Extracting data from snapshot...');
            $versionData = $snapshot->data();

            Log::info('✅ [APP_VERSION] Data retrieved successfully', [
                'version' => $versionData['version'] ?? 'unknown',
                'buildNumber' => $versionData['buildNumber'] ?? 'unknown'
            ]);

            return [
                'success' => true,
                'exists' => true,
                'data' => $versionData
            ];
        } catch (\Exception $e) {
            Log::error('🔴 [APP_VERSION] Exception during data fetch', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exists' => false
            ];
        }
    }
    /**
     * Update app version in Firestore (admin only)
     */
    public function updateAppVersion(array $versionData)
    {
        // ✅ Initialize Firestore first
        if (!$this->initializeFirestore()) {
            Log::warning('Firestore not available, cannot update app version');
            return [
                'success' => false,
                'error' => 'Firestore client not available',
                'skipped' => true
            ];
        }

        try {
            // Validate required fields
            $requiredFields = ['version', 'buildNumber'];
            foreach ($requiredFields as $field) {
                if (!isset($versionData[$field])) {
                    return [
                        'success' => false,
                        'error' => "Missing required field: {$field}"
                    ];
                }
            }

            // Prepare data with timestamp
            $updateData = array_merge($versionData, [
                'updated_at' => now()->toISOString()
            ]);

            $docRef = $this->firestore
                ->database()
                ->collection('app_config')
                ->document('version');

            $docRef->set($updateData, ['merge' => true]);

            Log::info('App version updated in Firestore successfully', [
                'version' => $versionData['version'],
                'buildNumber' => $versionData['buildNumber']
            ]);

            return [
                'success' => true,
                'data' => $updateData
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update app version in Firestore', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}