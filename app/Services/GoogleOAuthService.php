<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleOAuthService
{
    private $clientId;
    private $clientSecret;
    private $googleTokenVerifyUrl = 'https://oauth2.googleapis.com/tokeninfo';
    private $googleUserInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';

    public function __construct()
    {
        $this->clientId = config('services.google.client_id');
        $this->clientSecret = config('services.google.client_secret');
    }

    /**
     * Verify Google ID Token and get user information
     * This method verifies the token received from Google Sign-In on the client side
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            // Verify the ID token with Google
            $response = Http::timeout(30)->get($this->googleTokenVerifyUrl, [
                'id_token' => $idToken
            ]);

            if (!$response->successful()) {
                Log::error('Google token verification failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => 'Invalid Google token',
                    'error_code' => 'INVALID_TOKEN'
                ];
            }

            $tokenData = $response->json();

            // Validate the token is for our app
            if (isset($tokenData['aud']) && $tokenData['aud'] !== $this->clientId) {
                Log::error('Google token audience mismatch', [
                    'expected' => $this->clientId,
                    'received' => $tokenData['aud']
                ]);

                return [
                    'success' => false,
                    'error' => 'Invalid token audience',
                    'error_code' => 'INVALID_AUDIENCE'
                ];
            }

            // Extract user information from token
            $userData = [
                'google_id' => $tokenData['sub'] ?? null,
                'email' => $tokenData['email'] ?? null,
                'email_verified' => $tokenData['email_verified'] ?? false,
                'name' => $tokenData['name'] ?? null,
                'picture' => $tokenData['picture'] ?? null,
                'given_name' => $tokenData['given_name'] ?? null,
                'family_name' => $tokenData['family_name'] ?? null,
            ];

            Log::info('Google token verified successfully', [
                'google_id' => $userData['google_id'],
                'email' => $userData['email']
            ]);

            return [
                'success' => true,
                'user_data' => $userData
            ];
        } catch (Exception $e) {
            Log::error('Google token verification exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Token verification failed',
                'error_code' => 'VERIFICATION_ERROR'
            ];
        }
    }

    /**
     * Get user information using access token
     * Alternative method if you're using access tokens instead of ID tokens
     */
    public function getUserInfoFromAccessToken(string $accessToken): array
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken
            ])->get($this->googleUserInfoUrl);

            if (!$response->successful()) {
                Log::error('Failed to get Google user info', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => 'Failed to get user information',
                    'error_code' => 'USER_INFO_ERROR'
                ];
            }

            $userData = $response->json();

            Log::info('Google user info retrieved successfully', [
                'google_id' => $userData['sub'] ?? null,
                'email' => $userData['email'] ?? null
            ]);

            return [
                'success' => true,
                'user_data' => [
                    'google_id' => $userData['sub'] ?? null,
                    'email' => $userData['email'] ?? null,
                    'email_verified' => $userData['email_verified'] ?? false,
                    'name' => $userData['name'] ?? null,
                    'picture' => $userData['picture'] ?? null,
                    'given_name' => $userData['given_name'] ?? null,
                    'family_name' => $userData['family_name'] ?? null,
                ]
            ];
        } catch (Exception $e) {
            Log::error('Google user info exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to retrieve user information',
                'error_code' => 'USER_INFO_EXCEPTION'
            ];
        }
    }

    /**
     * Validate Google credentials
     * Quick validation method for additional security checks
     */
    public function validateCredentials(string $email, string $googleId): bool
    {
        // Add any additional validation logic here
        return !empty($email) && !empty($googleId) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Generate a secure random password for Google sign-in users
     * Since Google users don't need passwords, we generate a secure random one
     */
    public function generateSecurePassword(): string
    {
        return bin2hex(random_bytes(32)); // 64 character random password
    }
}
