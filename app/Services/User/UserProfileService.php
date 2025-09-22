<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserProfileService
{
    /**
     * Get full user profile with related data
     */
    public function getFullProfile(?User $user, bool $includeAppointments = true, bool $includeNotifications = true): ?array
    {
        // Handle null user gracefully
        if (!$user) {
            Log::warning('UserProfileService::getFullProfile called with null user');
            return null;
        }

        try {
            $profile = [
                'user' => $this->transformUserData($user)
            ];

            if ($includeAppointments) {
                $profile['appointments'] = $this->getUserAppointments($user);
                $profile['appointments_count'] = count($profile['appointments']);
            }

            if ($includeNotifications) {
                $profile['notifications'] = $this->getUserNotifications($user);
                $profile['unread_notifications_count'] = collect($profile['notifications'])
                    ->where('is_read', false)->count();
            }

            return $profile;
        } catch (\Exception $e) {
            Log::error('Failed to get user profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update user profile data
     */
    public function updateProfile(User $user, array $data): User
    {
        DB::beginTransaction();

        try {
            $updateData = $this->prepareProfileUpdateData($data);

            Log::info('Update data prepared', [
                'user_id' => $user->id,
                'update_data' => array_keys($updateData)
            ]);

            if (!empty($updateData)) {
                $user->update($updateData);

                // Send nearby property notifications if location changed
                if (isset($updateData['lat']) && isset($updateData['lng'])) {
                    $this->sendNearbyPropertyNotifications($user->id, $updateData['lat'], $updateData['lng']);
                }
            }

            DB::commit();

            Log::info('User profile updated successfully', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($updateData)
            ]);

            return $user->fresh();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Transform user data for API response
     */
    public function transformUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'place' => $user->place,
            'lat' => $user->lat,
            'lng' => $user->lng,
            'about_me' => $user->about_me,
            'photo_image' => $user->photo_image,
            'language' => $user->language,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'search_preferences' => $user->search_preferences ?? $this->getDefaultSearchPreferences(),
        ];
    }

    // Private helper methods

    /**
     * Prepare profile update data
     */
    private function prepareProfileUpdateData(array $data): array
    {
        $updateData = [];
        $allowedFields = [
            'lat',
            'lng',
            'place',
            'username',
            'phone',
            'about_me',
            'photo_image',
            'language',
            'email',
            'search_preferences'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        return $updateData;
    }

    /**
     * Get user appointments with relationships
     */
    private function getUserAppointments(User $user): array
    {
        try {
            $appointments = Appointment::with(['agent', 'office', 'property'])
                ->where('user_id', $user->id)
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get();

            return $appointments->map(function ($appointment) {
                return $this->transformAppointmentData($appointment);
            })->toArray();
        } catch (\Exception $e) {
            Log::warning('Failed to load user appointments', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            // Fallback to basic query without relationships
            return $this->getUserAppointmentsBasic($user);
        }
    }

    /**
     * Get user appointments without relationships (fallback)
     */
    private function getUserAppointmentsBasic(User $user): array
    {
        try {
            $appointments = DB::table('appointments')
                ->where('user_id', $user->id)
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get();

            return $appointments->map(function ($appointment) {
                return $this->transformBasicAppointmentData($appointment);
            })->toArray();
        } catch (\Exception $e) {
            Log::warning('Failed to load basic user appointments', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get user notifications
     */
    private function getUserNotifications(User $user): array
    {
        try {
            $notifications = DB::table('notifications')
                ->where('user_id', $user->id)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->orderBy('sent_at', 'desc')
                ->get();

            return $notifications->map(function ($notification) {
                return $this->transformNotificationData($notification);
            })->toArray();
        } catch (\Exception $e) {
            Log::warning('Failed to load user notifications', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Transform appointment data with relationships
     */
    private function transformAppointmentData($appointment): array
    {
        return [
            'id' => $appointment->id,
            'user_id' => $appointment->user_id,
            'agent_id' => $appointment->agent_id,
            'office_id' => $appointment->office_id,
            'property_id' => $appointment->property_id,
            'appointment_date' => $appointment->appointment_date,
            'appointment_time' => $appointment->appointment_time,
            'status' => $appointment->status,
            'type' => $appointment->type,
            'location' => $appointment->location,
            'notes' => $appointment->notes,
            'client_name' => $appointment->client_name,
            'client_phone' => $appointment->client_phone,
            'client_email' => $appointment->client_email,
            'confirmed_at' => $appointment->confirmed_at,
            'completed_at' => $appointment->completed_at,
            'cancelled_at' => $appointment->cancelled_at,
            'agent_name' => $appointment->agent ?
                ($appointment->agent->agent_name ?? $appointment->agent->name ?? 'Unknown Agent') : null,
            'agent_phone' => $appointment->agent ?
                ($appointment->agent->phone_number ?? $appointment->agent->phone ?? null) : null,
            'office_name' => $appointment->office ?
                ($appointment->office->company_name ?? $appointment->office->name ?? 'Unknown Office') : null,
            'property_title' => $appointment->property ?
                ($appointment->property->name ?? $appointment->property->title ?? 'Unknown Property') : null,
            'property_address' => $appointment->property ?
                ($appointment->property->location ?? $appointment->property->address ?? null) : null,
            'created_at' => $appointment->created_at,
            'updated_at' => $appointment->updated_at,
        ];
    }

    /**
     * Transform basic appointment data without relationships
     */
    private function transformBasicAppointmentData($appointment): array
    {
        return [
            'id' => $appointment->id,
            'appointment_date' => $appointment->appointment_date,
            'appointment_time' => $appointment->appointment_time,
            'status' => $appointment->status,
            'type' => $appointment->type,
            'location' => $appointment->location,
            'notes' => $appointment->notes,
            'client_name' => $appointment->client_name,
            'client_phone' => $appointment->client_phone,
            'client_email' => $appointment->client_email,
            'confirmed_at' => $appointment->confirmed_at ?? null,
            'completed_at' => $appointment->completed_at ?? null,
            'cancelled_at' => $appointment->cancelled_at ?? null,
            'agent' => $appointment->agent_id ? [
                'id' => $appointment->agent_id,
                'name' => 'Agent',
                'phone' => null,
                'email' => null,
            ] : null,
            'office' => $appointment->office_id ? [
                'id' => $appointment->office_id,
                'name' => 'Office',
                'address' => null,
                'phone' => null,
            ] : null,
            'property' => $appointment->property_id ? [
                'id' => $appointment->property_id,
                'title' => 'Property',
                'address' => null,
                'price' => null,
            ] : null,
            'created_at' => $appointment->created_at,
            'updated_at' => $appointment->updated_at,
        ];
    }

    /**
     * Transform notification data
     */
    private function transformNotificationData($notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title ?? '',
            'message' => $notification->message ?? '',
            'type' => $notification->type ?? 'info',
            'priority' => $notification->priority ?? 'normal',
            'data' => $notification->data ? json_decode($notification->data, true) : null,
            'action_url' => $notification->action_url ?? null,
            'action_text' => $notification->action_text ?? null,
            'is_read' => (bool) ($notification->is_read ?? false),
            'read_at' => $notification->read_at ?? null,
            'sent_at' => $notification->sent_at ?? null,
            'expires_at' => $notification->expires_at ?? null,
            'created_at' => $notification->created_at ?? null,
            'updated_at' => $notification->updated_at ?? null,
        ];
    }

    /**
     * Send nearby property notifications
     */
    private function sendNearbyPropertyNotifications(string $userId, float $lat, float $lng): void
    {
        try {
            if (class_exists('App\Http\Controllers\NotificationController')) {
                app(\App\Http\Controllers\NotificationController::class)->sendNearbyPropertyNotifications($userId, $lat, $lng);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send nearby property notifications', [
                'user_id' => $userId,
                'lat' => $lat,
                'lng' => $lng,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get default search preferences
     */
    private function getDefaultSearchPreferences(): array
    {
        return [
            'filters' => [
                'price_enabled' => false,
                'min_price' => null,
                'max_price' => null,
                'location_enabled' => false,
                'location_radius' => 10.0,
                'property_types' => [],
                'min_bedrooms' => null,
                'max_bedrooms' => null,
            ],
            'sorting' => [
                'price_enabled' => false,
                'price_order' => 'low_to_high',
                'popularity_enabled' => false,
                'date_enabled' => false,
                'date_order' => 'newest',
                'distance_enabled' => false,
            ],
            'behavior' => [
                'enable_notifications' => true,
                'save_search_history' => true,
                'auto_suggestions' => true,
                'recent_searches' => true,
                'max_history_items' => 50,
            ]
        ];
    }
}
