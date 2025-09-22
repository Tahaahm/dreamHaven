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
    private $firestore;

    public function __construct()
    {
        try {
            // Check if Firestore client is available
            if (!class_exists('Google\Cloud\Firestore\FirestoreClient')) {
                Log::warning('Google Cloud Firestore client not installed. Firestore operations will be disabled.', [
                    'suggestion' => 'Run: composer require google/cloud-firestore'
                ]);
                $this->firestore = null;
                return;
            }

            $serviceAccountPath = config('firebase.service_account_path');

            if (!file_exists($serviceAccountPath)) {
                throw new \Exception("Firebase service account file not found: {$serviceAccountPath}");
            }

            $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            $this->firestore = $factory->createFirestore();

            Log::info('Firebase Firestore initialized successfully');
        } catch (\Exception $e) {
            Log::error('Firebase Firestore initialization failed', [
                'error' => $e->getMessage(),
                'service_account_path' => $serviceAccountPath ?? 'not found'
            ]);
            $this->firestore = null; // Set to null instead of throwing
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
        try {
            if ($this->firestore === null) {
                Log::warning('Firestore not available, skipping user document update', [
                    'user_id' => $user->id,
                    'suggestion' => 'Install google/cloud-firestore package to enable Firestore'
                ]);
                return [
                    'success' => false,
                    'error' => 'Firestore client not available',
                    'skipped' => true
                ];
            }
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
        if ($this->firestore === null) {
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
        return $this->firestore;
    }
}
