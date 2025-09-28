<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\FirebaseException;

class FirebaseAuthService
{
    private $auth;

    public function __construct()
    {
        try {
            $serviceAccountPath = config('firebase.service_account_path');

            if (!file_exists($serviceAccountPath)) {
                throw new \Exception("Firebase service account file not found: {$serviceAccountPath}");
            }

            $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            $this->auth = $factory->createAuth();

            Log::info('Firebase Authentication initialized successfully');
        } catch (\Exception $e) {
            Log::error('Firebase Authentication initialization failed', [
                'error' => $e->getMessage(),
                'service_account_path' => $serviceAccountPath ?? 'not found'
            ]);
            throw $e;
        }
    }

    /**
     * Create user using Firebase Authentication only
     */
    public function createUser($email, $password, $userData = [])
    {
        try {
            // Create Firebase Auth user
            $userProperties = [
                'email' => $email,
                'password' => $password,
                'emailVerified' => true,
                'disabled' => false,
            ];

            // Add display name if available
            if (isset($userData['username'])) {
                $userProperties['displayName'] = $userData['username'];
            }

            // Add phone number if available and format it properly
            if (isset($userData['phone']) && !empty($userData['phone'])) {
                $formattedPhone = $this->formatPhoneNumber($userData['phone']);
                if ($formattedPhone) {
                    $userProperties['phoneNumber'] = $formattedPhone;
                    Log::info('Phone number formatted for Firebase', [
                        'original' => $userData['phone'],
                        'formatted' => $formattedPhone
                    ]);
                } else {
                    Log::warning('Could not format phone number, skipping', [
                        'phone' => $userData['phone']
                    ]);
                }
            }

            // Add photo URL if available
            if (isset($userData['photo_image']) && !empty($userData['photo_image'])) {
                $userProperties['photoUrl'] = $userData['photo_image'];
            }

            $createdUser = $this->auth->createUser($userProperties);
            $firebaseUid = $createdUser->uid;

            Log::info('Firebase Auth user created successfully', [
                'firebase_uid' => $firebaseUid,
                'email' => $email
            ]);

            // Generate custom token for the user
            $customToken = $this->auth->createCustomToken($firebaseUid);

            return [
                'success' => true,
                'firebase_uid' => $firebaseUid,
                'custom_token' => $customToken->toString(),
                'id_token' => $customToken->toString(), // Added for compatibility
                'user' => $createdUser
            ];
        } catch (FirebaseException $e) {
            Log::error('Firebase user creation failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('Unexpected error during Firebase user creation', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create Firebase user from existing Laravel user
     */
    public function createUserFromLaravel($laravelUser, $password)
    {
        try {
            Log::info('Creating Firebase user from Laravel user', [
                'laravel_user_id' => $laravelUser->id,
                'email' => $laravelUser->email,
                'phone' => $laravelUser->phone ?? 'empty'
            ]);

            $userProperties = [
                'email' => $laravelUser->email,
                'password' => $password,
                'emailVerified' => true,
                'displayName' => $laravelUser->username ?? '',
                'disabled' => false,
            ];

            // Add phone if available and format it properly
            if (!empty($laravelUser->phone)) {
                $formattedPhone = $this->formatPhoneNumber($laravelUser->phone);
                if ($formattedPhone) {
                    $userProperties['phoneNumber'] = $formattedPhone;
                    Log::info('Phone number formatted for Firebase', [
                        'original' => $laravelUser->phone,
                        'formatted' => $formattedPhone
                    ]);
                } else {
                    Log::warning('Could not format phone number, skipping', [
                        'phone' => $laravelUser->phone
                    ]);
                }
            }

            // Add photo if available
            if (!empty($laravelUser->photo_image)) {
                $userProperties['photoUrl'] = $laravelUser->photo_image;
            }

            Log::info('User properties for Firebase creation', [
                'properties' => array_keys($userProperties),
                'phone_included' => isset($userProperties['phoneNumber'])
            ]);

            $createdUser = $this->auth->createUser($userProperties);
            $firebaseUid = $createdUser->uid;

            // Generate custom token
            $customToken = $this->auth->createCustomToken($firebaseUid);

            Log::info('Firebase user created from Laravel successfully', [
                'laravel_user_id' => $laravelUser->id,
                'firebase_uid' => $firebaseUid
            ]);

            return [
                'success' => true,
                'firebase_uid' => $firebaseUid,
                'custom_token' => $customToken->toString(),
                'id_token' => $customToken->toString(), // Added for compatibility
                'user' => $createdUser
            ];
        } catch (FirebaseException $e) {
            Log::error('Failed to create Firebase user from Laravel', [
                'laravel_user_id' => $laravelUser->id,
                'email' => $laravelUser->email,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     */
    public function authenticateUser($email, $password)
    {
        try {
            $webApiKey = config('firebase.api_key');

            if (!$webApiKey) {
                throw new \Exception('Firebase Web API key not configured');
            }

            // Use Firebase REST API to actually verify the password
            $response = \Illuminate\Support\Facades\Http::timeout(30)->post(
                'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=' . $webApiKey,
                [
                    'email' => $email,
                    'password' => $password,
                    'returnSecureToken' => true
                ]
            );

            $data = $response->json();

            Log::info('Firebase authentication attempt', [
                'email' => $email,
                'response_status' => $response->status(),
                'has_id_token' => isset($data['idToken']),
                'error' => $data['error']['message'] ?? null
            ]);

            if ($response->successful() && isset($data['idToken'])) {
                // Password verification successful - get user details from Admin SDK
                $user = $this->auth->getUserByEmail($email);

                Log::info('Firebase authentication successful', [
                    'email' => $email,
                    'firebase_uid' => $user->uid
                ]);

                return [
                    'success' => true,
                    'firebase_uid' => $user->uid,
                    'email' => $user->email,
                    'custom_token' => $this->auth->createCustomToken($user->uid)->toString(),
                    'id_token' => $data['idToken'],
                    'firebase_user' => $user
                ];
            }

            // Authentication failed
            $errorMessage = 'Authentication failed';
            if (isset($data['error']['message'])) {
                $errorMessage = $data['error']['message'];

                // Make error messages more user-friendly
                if (strpos($errorMessage, 'INVALID_PASSWORD') !== false) {
                    $errorMessage = 'Invalid password';
                } elseif (strpos($errorMessage, 'EMAIL_NOT_FOUND') !== false) {
                    $errorMessage = 'User not found';
                } elseif (strpos($errorMessage, 'TOO_MANY_ATTEMPTS_TRY_LATER') !== false) {
                    $errorMessage = 'Too many failed attempts. Please try again later.';
                } elseif (strpos($errorMessage, 'USER_DISABLED') !== false) {
                    $errorMessage = 'User account has been disabled';
                }
            }

            Log::warning('Firebase authentication failed', [
                'email' => $email,
                'error' => $errorMessage,
                'full_response' => $data
            ]);

            return [
                'success' => false,
                'error' => $errorMessage
            ];
        } catch (\Exception $e) {
            Log::error('Firebase authentication error', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ['success' => false, 'error' => 'Authentication service error'];
        }
    }

    /**
     * Check if user exists in Firebase Authentication
     */
    public function userExists($email)
    {
        try {
            $user = $this->auth->getUserByEmail($email);
            return true;
        } catch (UserNotFound $e) {
            return false;
        } catch (FirebaseException $e) {
            Log::error('Error checking user existence', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get user by email from Firebase Authentication
     */
    public function getUserByEmail($email)
    {
        try {
            $user = $this->auth->getUserByEmail($email);
            return $user;
        } catch (UserNotFound $e) {
            return null;
        } catch (FirebaseException $e) {
            Log::error('Error getting user by email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get user by UID from Firebase Authentication
     */
    public function getUserByUid($firebaseUid)
    {
        try {
            $user = $this->auth->getUser($firebaseUid);
            return $user;
        } catch (UserNotFound $e) {
            return null;
        } catch (FirebaseException $e) {
            Log::error('Error getting user by UID', [
                'firebase_uid' => $firebaseUid,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Update user information in Firebase Authentication
     */
    public function updateUser($firebaseUid, $userData)
    {
        try {
            $updateProperties = [];

            if (isset($userData['email'])) {
                $updateProperties['email'] = $userData['email'];
            }

            if (isset($userData['password'])) {
                $updateProperties['password'] = $userData['password'];
            }

            if (isset($userData['username'])) {
                $updateProperties['displayName'] = $userData['username'];
            }

            if (isset($userData['phone'])) {
                $formattedPhone = $this->formatPhoneNumber($userData['phone']);
                if ($formattedPhone) {
                    $updateProperties['phoneNumber'] = $formattedPhone;
                } else {
                    Log::warning('Invalid phone number format during update', [
                        'phone' => $userData['phone']
                    ]);
                }
            }

            if (isset($userData['photo_image'])) {
                $updateProperties['photoUrl'] = $userData['photo_image'];
            }

            if (isset($userData['disabled'])) {
                $updateProperties['disabled'] = $userData['disabled'];
            }

            $updatedUser = $this->auth->updateUser($firebaseUid, $updateProperties);

            Log::info('Firebase user updated successfully', [
                'firebase_uid' => $firebaseUid,
                'updated_fields' => array_keys($updateProperties)
            ]);

            return [
                'success' => true,
                'user' => $updatedUser
            ];
        } catch (FirebaseException $e) {
            Log::error('Failed to update Firebase user', [
                'firebase_uid' => $firebaseUid,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete user from Firebase Authentication
     */
    public function deleteUser($firebaseUid)
    {
        try {
            $this->auth->deleteUser($firebaseUid);

            Log::info('Firebase user deleted successfully', [
                'firebase_uid' => $firebaseUid
            ]);

            return true;
        } catch (FirebaseException $e) {
            Log::error('Failed to delete Firebase user', [
                'firebase_uid' => $firebaseUid,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Update user password
     */
    public function updateUserPassword($firebaseUid, $newPassword)
    {
        try {
            $this->auth->updateUser($firebaseUid, [
                'password' => $newPassword
            ]);

            Log::info('Firebase user password updated', [
                'firebase_uid' => $firebaseUid
            ]);

            return true;
        } catch (FirebaseException $e) {
            Log::error('Failed to update Firebase user password', [
                'firebase_uid' => $firebaseUid,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate custom token for user
     */
    public function generateCustomToken($firebaseUid, $additionalClaims = [])
    {
        try {
            $customToken = $this->auth->createCustomToken($firebaseUid, $additionalClaims);
            return $customToken->toString();
        } catch (FirebaseException $e) {
            Log::error('Failed to generate custom token', [
                'firebase_uid' => $firebaseUid,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Format phone number to E.164 format for Firebase
     * Specifically handles Iraqi phone numbers
     */
    private function formatPhoneNumber($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        Log::info('Formatting phone number', [
            'original' => $phone,
            'cleaned' => $cleaned,
            'length' => strlen($cleaned)
        ]);

        // If empty after cleaning, return null
        if (empty($cleaned)) {
            Log::warning('Phone number is empty after cleaning');
            return null;
        }

        // Handle Iraqi phone numbers
        if (strlen($cleaned) == 11 && str_starts_with($cleaned, '07')) {
            // Iraqi mobile number starting with 07 (like 07517812988)
            // Remove the leading 0 and add Iraq country code +964
            $withoutLeadingZero = substr($cleaned, 1); // Remove leading 0
            $formatted = '+964' . $withoutLeadingZero;

            Log::info('Formatted Iraqi mobile number', [
                'original' => $phone,
                'formatted' => $formatted
            ]);

            return $formatted;
        }

        // If it already has country code (starts with 964)
        if (str_starts_with($cleaned, '964') && strlen($cleaned) >= 13) {
            $formatted = '+' . $cleaned;

            Log::info('Added + to existing country code', [
                'original' => $phone,
                'formatted' => $formatted
            ]);

            return $formatted;
        }

        // If it already starts with + and has good length
        if (str_starts_with($phone, '+') && strlen($cleaned) >= 10) {
            Log::info('Phone already in correct format', [
                'phone' => $phone
            ]);
            return $phone;
        }

        // For other lengths or formats, try to add Iraq country code
        if (strlen($cleaned) >= 10 && strlen($cleaned) <= 11) {
            // Remove leading zero if present
            if (str_starts_with($cleaned, '0')) {
                $cleaned = substr($cleaned, 1);
            }

            $formatted = '+964' . $cleaned;

            Log::info('Applied default Iraq country code', [
                'original' => $phone,
                'formatted' => $formatted
            ]);

            return $formatted;
        }

        Log::warning('Could not format phone number', [
            'original' => $phone,
            'cleaned' => $cleaned,
            'length' => strlen($cleaned)
        ]);

        // If phone number doesn't match expected patterns, return null
        return null;
    }

    /**
     * Get Firebase Auth instance (for advanced operations)
     */
    public function getAuth()
    {
        return $this->auth;
    }


    public function createUserDocument(User $user, array $additionalData = [])
    {
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
    public function generatePasswordResetLink(string $email): array
    {
        try {
            // Verify user exists first
            $user = $this->auth->getUserByEmail($email);

            // Generate password reset link
            $resetLink = $this->auth->generatePasswordResetLink($email);

            Log::info('Password reset link generated', [
                'email' => $email,
                'firebase_uid' => $user->uid
            ]);

            return [
                'success' => true,
                'reset_link' => $resetLink,
                'firebase_uid' => $user->uid
            ];
        } catch (UserNotFound $e) {
            Log::warning('Password reset requested for non-existent user', [
                'email' => $email
            ]);

            return [
                'success' => false,
                'error' => 'User not found',
                'error_code' => 'USER_NOT_FOUND'
            ];
        } catch (FirebaseException $e) {
            Log::error('Failed to generate password reset link', [
                'email' => $email,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'FIREBASE_ERROR'
            ];
        } catch (\Exception $e) {
            Log::error('Unexpected error generating password reset link', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'An unexpected error occurred',
                'error_code' => 'UNKNOWN_ERROR'
            ];
        }
    }

    /**
     * Send password reset email via Firebase REST API
     * This is the method that actually sends emails
     */
    public function sendPasswordResetEmail(string $email): array
    {
        try {
            $apiKey = config('firebase.api_key');

            if (!$apiKey) {
                throw new \Exception('Firebase API key not configured');
            }

            $url = "https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key={$apiKey}";

            $data = [
                'requestType' => 'PASSWORD_RESET',
                'email' => $email
            ];

            $response = \Illuminate\Support\Facades\Http::timeout(30)->post($url, $data);

            if ($response->successful()) {
                Log::info('Password reset email sent successfully', [
                    'email' => $email
                ]);

                return [
                    'success' => true,
                    'message' => 'Password reset email sent successfully'
                ];
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Failed to send password reset email';

                Log::error('Firebase REST API error', [
                    'email' => $email,
                    'error' => $errorData,
                    'status' => $response->status()
                ]);

                // Map Firebase error codes to user-friendly messages
                $userMessage = $this->mapFirebaseErrorMessage($errorMessage);

                return [
                    'success' => false,
                    'error' => $userMessage,
                    'error_code' => $errorMessage
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception during password reset email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Unable to send password reset email. Please try again later.',
                'error_code' => 'SERVICE_ERROR'
            ];
        }
    }

    /**
     * Map Firebase error messages to user-friendly messages
     */
    private function mapFirebaseErrorMessage(string $firebaseError): string
    {
        $errorMap = [
            'EMAIL_NOT_FOUND' => 'No account found with this email address',
            'INVALID_EMAIL' => 'Invalid email address format',
            'TOO_MANY_ATTEMPTS_TRY_LATER' => 'Too many attempts. Please try again later',
            'USER_DISABLED' => 'This account has been disabled',
        ];

        return $errorMap[$firebaseError] ?? 'Failed to send password reset email';
    }

    /**
     * Verify password reset code
     */
    public function verifyPasswordResetCode(string $oobCode): array
    {
        try {
            $email = $this->auth->verifyPasswordResetCode($oobCode);

            return [
                'success' => true,
                'email' => $email
            ];
        } catch (FirebaseException $e) {
            Log::error('Invalid password reset code', [
                'code_preview' => substr($oobCode, 0, 10) . '...',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Invalid or expired password reset code',
                'error_code' => 'INVALID_OOB_CODE'
            ];
        }
    }

    /**
     * Confirm password reset
     */
    /**
     * Confirm password reset
     */
    public function confirmPasswordReset(string $oobCode, string $newPassword): array
    {
        try {
            $email = $this->auth->verifyPasswordResetCode($oobCode);
            $this->auth->confirmPasswordReset($oobCode, $newPassword);

            Log::info('Password reset completed successfully', [
                'email' => $email
            ]);

            return [
                'success' => true,
                'email' => $email,
                'message' => 'Password reset completed successfully'
            ];
        } catch (FirebaseException $e) {
            Log::error('Failed to confirm password reset', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to reset password. The code may be invalid or expired.',
                'error_code' => 'RESET_FAILED'
            ];
        }
    }
}