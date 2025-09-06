<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Notification;
use App\Models\User;
use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Models\Property;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Get notifications for authenticated user/agent/office
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'nullable|in:property,appointment,system,promotion,alert',
                'priority' => 'nullable|in:low,medium,high,urgent',
                'is_read' => 'nullable|boolean',
                'limit' => 'nullable|integer|min:1|max:100',
                'offset' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            // Get the authenticated user/agent/office
            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error(
                    ResponseDetails::unauthorizedMessage(),
                    null,
                    ResponseDetails::CODE_UNAUTHORIZED
                );
            }

            $query = Notification::query()->notExpired();

            // Filter by recipient (user, agent, or office)
            if ($user instanceof User) {
                $query->where('user_id', $user->id);
            } elseif ($user instanceof Agent) {
                $query->where('agent_id', $user->id);
            } elseif ($user instanceof RealEstateOffice) {
                $query->where('office_id', $user->id);
            }

            // Apply filters
            if ($request->has('type')) {
                $query->byType($request->type);
            }

            if ($request->has('priority')) {
                $query->byPriority($request->priority);
            }

            if ($request->has('is_read')) {
                if ($request->boolean('is_read')) {
                    $query->where('is_read', true);
                } else {
                    $query->unread();
                }
            }

            $limit = $request->get('limit', 20);
            $offset = $request->get('offset', 0);

            $notifications = $query->orderBy('sent_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();

            $unreadCount = Notification::query()
                ->notExpired()
                ->unread()
                ->where($this->getRecipientColumn($user), $user->id)
                ->count();

            return ApiResponse::success(
                ResponseDetails::successMessage('Notifications retrieved successfully'),
                [
                    'notifications' => $notifications,
                    'unread_count' => $unreadCount,
                    'total_count' => $notifications->count(),
                ],
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving notifications: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to retrieve notifications'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error(
                    ResponseDetails::unauthorizedMessage(),
                    null,
                    ResponseDetails::CODE_UNAUTHORIZED
                );
            }

            $notification = Notification::where('id', $id)
                ->where($this->getRecipientColumn($user), $user->id)
                ->first();

            if (!$notification) {
                return ApiResponse::error(
                    ResponseDetails::notFoundMessage('Notification not found'),
                    null,
                    ResponseDetails::CODE_NOT_FOUND
                );
            }

            $notification->markAsRead();

            return ApiResponse::success(
                ResponseDetails::successMessage('Notification marked as read'),
                $notification,
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to mark notification as read'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error(
                    ResponseDetails::unauthorizedMessage(),
                    null,
                    ResponseDetails::CODE_UNAUTHORIZED
                );
            }

            $updatedCount = Notification::where($this->getRecipientColumn($user), $user->id)
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return ApiResponse::success(
                ResponseDetails::successMessage('All notifications marked as read'),
                ['updated_count' => $updatedCount],
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to mark all notifications as read'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error(
                    ResponseDetails::unauthorizedMessage(),
                    null,
                    ResponseDetails::CODE_UNAUTHORIZED
                );
            }

            $notification = Notification::where('id', $id)
                ->where($this->getRecipientColumn($user), $user->id)
                ->first();

            if (!$notification) {
                return ApiResponse::error(
                    ResponseDetails::notFoundMessage('Notification not found'),
                    null,
                    ResponseDetails::CODE_NOT_FOUND
                );
            }

            $notification->delete();

            return ApiResponse::success(
                ResponseDetails::successMessage('Notification deleted successfully'),
                null,
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to delete notification'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }

    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error(
                    ResponseDetails::unauthorizedMessage(),
                    null,
                    ResponseDetails::CODE_UNAUTHORIZED
                );
            }

            $deletedCount = Notification::where($this->getRecipientColumn($user), $user->id)
                ->delete();

            return ApiResponse::success(
                ResponseDetails::successMessage('All notifications cleared successfully'),
                ['deleted_count' => $deletedCount],
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error clearing all notifications: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to clear all notifications'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }

    // ===== NOTIFICATION CREATION METHODS =====

    /**
     * Send welcome notification on user registration
     */
    public function sendWelcomeNotification($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                Log::warning("User not found for welcome notification: {$userId}");
                return false;
            }

            $this->createNotification([
                'user_id' => $userId,
                'title' => 'Welcome to Our Platform!',
                'message' => "Hello {$user->username}! Welcome to our real estate platform. Explore thousands of properties and find your dream home.",
                'type' => 'system',
                'priority' => 'medium',
                'data' => [
                    'welcome_bonus' => true,
                    'user_type' => 'new_user',
                    'registration_date' => now()->toDateString(),
                ],
                'action_url' => '/properties',
                'action_text' => 'Browse Properties',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending welcome notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send login notification (optional - for security)
     */
    public function sendLoginNotification($userId, $deviceInfo = null)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            // Only send if user has security notifications enabled
            $preferences = $user->search_preferences ?? [];
            if (!($preferences['behavior']['enable_notifications'] ?? true)) {
                return false;
            }

            $this->createNotification([
                'user_id' => $userId,
                'title' => 'Login Alert',
                'message' => "Your account was accessed from a new device or location at " . now()->format('M j, Y g:i A'),
                'type' => 'system',
                'priority' => 'low',
                'data' => [
                    'device_info' => $deviceInfo,
                    'login_time' => now()->toISOString(),
                ],
                'expires_at' => now()->addDays(7),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending login notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send new property notifications to interested users
     */
    public function sendNewPropertyNotifications($propertyId)
    {
        try {
            $property = Property::find($propertyId);
            if (!$property || !$property->is_active) {
                return false;
            }

            // Get users who might be interested based on location and preferences
            $interestedUsers = $this->findInterestedUsers($property);

            foreach ($interestedUsers as $user) {
                $this->createNotification([
                    'user_id' => $user->id,
                    'title' => 'New Property Alert!',
                    'message' => "A new property matching your preferences has been listed: " . ($property->name['en'] ?? 'New Property'),
                    'type' => 'property',
                    'priority' => 'medium',
                    'data' => [
                        'property_id' => $propertyId,
                        'property_type' => $property->type['category'] ?? null,
                        'price_usd' => $property->price['usd'] ?? null,
                        'price_iqd' => $property->price['iqd'] ?? null,
                        'location' => $property->locations[0] ?? null,
                        'match_reason' => 'location_preference',
                    ],
                    'action_url' => "/properties/{$propertyId}",
                    'action_text' => 'View Property',
                    'expires_at' => now()->addDays(30),
                ]);
            }

            Log::info("Sent new property notifications to " . count($interestedUsers) . " users for property: {$propertyId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending new property notifications: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send nearby property notifications
     */
    public function sendNearbyPropertyNotifications($userId, $userLat, $userLng, $radius = 5)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            // Find nearby properties
            $nearbyProperties = Property::whereRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(JSON_EXTRACT(locations, '$[0].lat'))) * cos(radians(JSON_EXTRACT(locations, '$[0].lng')) - radians(?)) + sin(radians(?)) * sin(radians(JSON_EXTRACT(locations, '$[0].lat'))))) <= ?",
                [$userLat, $userLng, $userLat, $radius]
            )->where('is_active', true)
                ->where('created_at', '>=', now()->subDays(7))
                ->limit(5)
                ->get();

            if ($nearbyProperties->count() > 0) {
                $this->createNotification([
                    'user_id' => $userId,
                    'title' => 'Properties Near You',
                    'message' => "We found " . $nearbyProperties->count() . " new properties within {$radius}km of your location.",
                    'type' => 'property',
                    'priority' => 'medium',
                    'data' => [
                        'nearby_properties' => $nearbyProperties->pluck('id')->toArray(),
                        'radius_km' => $radius,
                        'user_location' => ['lat' => $userLat, 'lng' => $userLng],
                    ],
                    'action_url' => '/properties/nearby',
                    'action_text' => 'View Nearby Properties',
                    'expires_at' => now()->addDays(14),
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending nearby property notifications: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send appointment confirmation notification
     */
    public function sendAppointmentNotifications($appointmentId)
    {
        try {
            $appointment = Appointment::with(['user', 'agent', 'office', 'property'])->find($appointmentId);
            if (!$appointment) {
                return false;
            }

            // Notify the user
            if ($appointment->user) {
                $this->createNotification([
                    'user_id' => $appointment->user_id,
                    'title' => 'Appointment Scheduled',
                    'message' => "Your appointment has been scheduled for {$appointment->appointment_date} at {$appointment->appointment_time}.",
                    'type' => 'appointment',
                    'priority' => 'high',
                    'data' => [
                        'appointment_id' => $appointmentId,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                        'appointment_type' => $appointment->type,
                        'property_id' => $appointment->property_id,
                    ],
                    'action_url' => "/appointments/{$appointmentId}",
                    'action_text' => 'View Appointment',
                ]);
            }

            // Notify the agent
            if ($appointment->agent_id) {
                $this->createNotification([
                    'agent_id' => $appointment->agent_id,
                    'title' => 'New Appointment',
                    'message' => "You have a new appointment scheduled with {$appointment->client_name} on {$appointment->appointment_date} at {$appointment->appointment_time}.",
                    'type' => 'appointment',
                    'priority' => 'high',
                    'data' => [
                        'appointment_id' => $appointmentId,
                        'client_name' => $appointment->client_name,
                        'client_phone' => $appointment->client_phone,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                    ],
                    'action_url' => "/appointments/{$appointmentId}",
                    'action_text' => 'View Appointment',
                ]);
            }

            // Notify the office
            if ($appointment->office_id) {
                $this->createNotification([
                    'office_id' => $appointment->office_id,
                    'title' => 'New Appointment',
                    'message' => "A new appointment has been scheduled with {$appointment->client_name} on {$appointment->appointment_date} at {$appointment->appointment_time}.",
                    'type' => 'appointment',
                    'priority' => 'medium',
                    'data' => [
                        'appointment_id' => $appointmentId,
                        'client_name' => $appointment->client_name,
                        'agent_id' => $appointment->agent_id,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                    ],
                    'action_url' => "/appointments/{$appointmentId}",
                    'action_text' => 'View Appointment',
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending appointment notifications: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send appointment status update notifications
     */
    public function sendAppointmentStatusNotification($appointmentId, $newStatus)
    {
        try {
            $appointment = Appointment::with(['user', 'agent', 'office'])->find($appointmentId);
            if (!$appointment) {
                return false;
            }

            $statusMessages = [
                'confirmed' => 'Your appointment has been confirmed',
                'completed' => 'Your appointment has been completed',
                'cancelled' => 'Your appointment has been cancelled',
            ];

            $message = $statusMessages[$newStatus] ?? "Your appointment status has been updated to {$newStatus}";

            // Notify the user
            if ($appointment->user) {
                $this->createNotification([
                    'user_id' => $appointment->user_id,
                    'title' => 'Appointment Update',
                    'message' => $message . " for {$appointment->appointment_date} at {$appointment->appointment_time}.",
                    'type' => 'appointment',
                    'priority' => $newStatus === 'cancelled' ? 'high' : 'medium',
                    'data' => [
                        'appointment_id' => $appointmentId,
                        'old_status' => $appointment->getOriginal('status'),
                        'new_status' => $newStatus,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                    ],
                    'action_url' => "/appointments/{$appointmentId}",
                    'action_text' => 'View Appointment',
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending appointment status notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send reminder notifications for upcoming appointments
     */
    public function sendAppointmentReminders()
    {
        try {
            // Get appointments for tomorrow
            $upcomingAppointments = Appointment::with(['user', 'agent', 'office'])
                ->whereDate('appointment_date', now()->addDay())
                ->whereIn('status', ['pending', 'confirmed'])
                ->get();

            foreach ($upcomingAppointments as $appointment) {
                // Remind user
                if ($appointment->user) {
                    $this->createNotification([
                        'user_id' => $appointment->user_id,
                        'title' => 'Appointment Reminder',
                        'message' => "You have an appointment tomorrow at {$appointment->appointment_time}. Don't forget!",
                        'type' => 'appointment',
                        'priority' => 'medium',
                        'data' => [
                            'appointment_id' => $appointment->id,
                            'appointment_date' => $appointment->appointment_date,
                            'appointment_time' => $appointment->appointment_time,
                            'reminder_type' => 'day_before',
                        ],
                        'action_url' => "/appointments/{$appointment->id}",
                        'action_text' => 'View Appointment',
                        'expires_at' => now()->addDays(2),
                    ]);
                }

                // Remind agent
                if ($appointment->agent_id) {
                    $this->createNotification([
                        'agent_id' => $appointment->agent_id,
                        'title' => 'Appointment Reminder',
                        'message' => "You have an appointment with {$appointment->client_name} tomorrow at {$appointment->appointment_time}.",
                        'type' => 'appointment',
                        'priority' => 'medium',
                        'data' => [
                            'appointment_id' => $appointment->id,
                            'client_name' => $appointment->client_name,
                            'appointment_date' => $appointment->appointment_date,
                            'appointment_time' => $appointment->appointment_time,
                            'reminder_type' => 'day_before',
                        ],
                        'action_url' => "/appointments/{$appointment->id}",
                        'action_text' => 'View Appointment',
                        'expires_at' => now()->addDays(2),
                    ]);
                }
            }

            Log::info("Sent appointment reminders for " . $upcomingAppointments->count() . " appointments");
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending appointment reminders: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send property price drop notifications
     */
    public function sendPriceDropNotification($propertyId, $oldPriceUSD, $newPriceUSD)
    {
        try {
            $property = Property::find($propertyId);
            if (!$property) {
                return false;
            }

            $priceDropPercent = round((($oldPriceUSD - $newPriceUSD) / $oldPriceUSD) * 100);
            if ($priceDropPercent < 5) { // Only notify for drops of 5% or more
                return false;
            }

            // Find users who might be interested
            $interestedUsers = $this->findInterestedUsers($property);

            foreach ($interestedUsers as $user) {
                $this->createNotification([
                    'user_id' => $user->id,
                    'title' => 'Price Drop Alert! 📉',
                    'message' => "Great news! The price of a property you might like has dropped by {$priceDropPercent}%!",
                    'type' => 'property',
                    'priority' => 'high',
                    'data' => [
                        'property_id' => $propertyId,
                        'old_price_usd' => $oldPriceUSD,
                        'new_price_usd' => $newPriceUSD,
                        'price_drop_percent' => $priceDropPercent,
                        'savings_usd' => $oldPriceUSD - $newPriceUSD,
                    ],
                    'action_url' => "/properties/{$propertyId}",
                    'action_text' => 'View Property',
                    'expires_at' => now()->addDays(14),
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending price drop notification: ' . $e->getMessage());
            return false;
        }
    }

    // ===== PRIVATE HELPER METHODS =====

    /**
     * Create a notification record
     */
    private function createNotification(array $data)
    {
        try {
            $data['id'] = (string) Str::uuid();
            $data['sent_at'] = now();

            return Notification::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage(), $data);
            return null;
        }
    }

    /**
     * Find users who might be interested in a property
     */
    private function findInterestedUsers(Property $property)
    {
        try {
            $query = User::where('search_preferences->behavior->enable_notifications', true);

            // Find users near the property location
            if (isset($property->locations[0])) {
                $propertyLat = $property->locations[0]['lat'];
                $propertyLng = $property->locations[0]['lng'];
                $radius = 20; // 20km radius

                $query->whereRaw(
                    "(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) <= ?",
                    [$propertyLat, $propertyLng, $propertyLat, $radius]
                );
            }

            // Additional filters based on user preferences could be added here
            // For example: price range, property type, etc.

            return $query->limit(100)->get(); // Limit to prevent spam

        } catch (\Exception $e) {
            Log::error('Error finding interested users: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get the appropriate recipient column based on user type
     */
    private function getRecipientColumn($user)
    {
        if ($user instanceof User) {
            return 'user_id';
        } elseif ($user instanceof Agent) {
            return 'agent_id';
        } elseif ($user instanceof RealEstateOffice) {
            return 'office_id';
        }

        return 'user_id'; // Default
    }

    /**
     * Send system-wide announcement
     */
    public function sendSystemAnnouncement(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'priority' => 'required|in:low,medium,high,urgent',
                'recipient_type' => 'required|in:users,agents,offices,all',
                'expires_at' => 'nullable|date|after:now',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $recipientType = $request->recipient_type;
            $notifications = [];

            if ($recipientType === 'users' || $recipientType === 'all') {
                $users = User::where('search_preferences->behavior->enable_notifications', true)->get();
                foreach ($users as $user) {
                    $notifications[] = [
                        'id' => (string) Str::uuid(),
                        'user_id' => $user->id,
                        'agent_id' => null,
                        'office_id' => null,
                        'title' => $request->title,
                        'message' => $request->message,
                        'type' => 'system',
                        'priority' => $request->priority,
                        'data' => json_encode(['announcement' => true]),
                        'action_url' => $request->action_url,
                        'action_text' => $request->action_text,
                        'is_read' => false,
                        'sent_at' => now(),
                        'expires_at' => $request->expires_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if ($recipientType === 'agents' || $recipientType === 'all') {
                $agents = Agent::where('is_verified', true)->get();
                foreach ($agents as $agent) {
                    $notifications[] = [
                        'id' => (string) Str::uuid(),
                        'user_id' => null,
                        'agent_id' => $agent->id,
                        'office_id' => null,
                        'title' => $request->title,
                        'message' => $request->message,
                        'type' => 'system',
                        'priority' => $request->priority,
                        'data' => json_encode(['announcement' => true]),
                        'action_url' => $request->action_url,
                        'action_text' => $request->action_text,
                        'is_read' => false,
                        'sent_at' => now(),
                        'expires_at' => $request->expires_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if ($recipientType === 'offices' || $recipientType === 'all') {
                $offices = RealEstateOffice::where('is_verified', true)->get();
                foreach ($offices as $office) {
                    $notifications[] = [
                        'id' => (string) Str::uuid(),
                        'user_id' => null,
                        'agent_id' => null,
                        'office_id' => $office->id,
                        'title' => $request->title,
                        'message' => $request->message,
                        'type' => 'system',
                        'priority' => $request->priority,
                        'data' => json_encode(['announcement' => true]),
                        'action_url' => $request->action_url,
                        'action_text' => $request->action_text,
                        'is_read' => false,
                        'sent_at' => now(),
                        'expires_at' => $request->expires_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Bulk insert notifications
            if (!empty($notifications)) {
                DB::table('notifications')->insert($notifications);
            }

            return ApiResponse::success(
                ResponseDetails::successMessage('System announcement sent successfully'),
                ['sent_to' => count($notifications), 'recipient_type' => $recipientType],
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error sending system announcement: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to send system announcement'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }
    /**
     * Send notification when new office is created
     */
    public function sendNewOfficeNotification($officeId)
    {
        try {
            $office = RealEstateOffice::find($officeId);
            if (!$office || !$office->latitude || !$office->longitude) {
                return false;
            }

            // Find users within 20km radius of the new office
            $radius = 20; // 20km radius
            $interestedUsers = User::whereNotNull('lat')
                ->whereNotNull('lng')
                ->whereRaw(
                    "(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) <= ?",
                    [$office->latitude, $office->longitude, $office->latitude, $radius]
                )
                ->where('search_preferences->behavior->enable_notifications', true)
                ->limit(100)
                ->get();

            foreach ($interestedUsers as $user) {
                $this->createNotification([
                    'user_id' => $user->id,
                    'title' => 'New Real Estate Office in Your Area',
                    'message' => "A new real estate office '{$office->company_name}' has opened in your area. Check out their services!",
                    'type' => 'system',
                    'priority' => 'medium',
                    'data' => [
                        'office_id' => $officeId,
                        'office_name' => $office->company_name,
                        'office_city' => $office->city,
                        'office_district' => $office->district,
                        'years_experience' => $office->years_experience,
                    ],
                    'action_url' => "/offices/{$officeId}",
                    'action_text' => 'View Office',
                    'expires_at' => now()->addDays(30),
                ]);
            }

            Log::info("Sent new office notifications to " . $interestedUsers->count() . " users for office: {$officeId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending new office notifications: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification when office gets verified
     */
    public function sendOfficeVerificationNotification($officeId)
    {
        try {
            $office = RealEstateOffice::find($officeId);
            if (!$office) {
                return false;
            }

            // Notify office agents about verification
            $agents = Agent::where('office_id', $officeId)->get();
            foreach ($agents as $agent) {
                $this->createNotification([
                    'agent_id' => $agent->id,
                    'title' => 'Office Verified Successfully!',
                    'message' => "Congratulations! Your office '{$office->company_name}' has been verified. This will increase your credibility with clients.",
                    'type' => 'system',
                    'priority' => 'high',
                    'data' => [
                        'office_id' => $officeId,
                        'office_name' => $office->company_name,
                        'verification_date' => now()->toDateString(),
                    ],
                    'action_url' => "/offices/{$officeId}",
                    'action_text' => 'View Office Profile',
                ]);
            }

            // Notify users who have interacted with this office recently
            $recentUsers = DB::table('appointments')
                ->where('office_id', $officeId)
                ->where('created_at', '>=', now()->subDays(30))
                ->distinct()
                ->pluck('user_id');

            foreach ($recentUsers as $userId) {
                $this->createNotification([
                    'user_id' => $userId,
                    'title' => 'Office Verification Update',
                    'message' => "Good news! '{$office->company_name}' that you recently interacted with has been verified for authenticity and quality.",
                    'type' => 'system',
                    'priority' => 'medium',
                    'data' => [
                        'office_id' => $officeId,
                        'office_name' => $office->company_name,
                        'verification_date' => now()->toDateString(),
                    ],
                    'action_url' => "/offices/{$officeId}",
                    'action_text' => 'View Office',
                    'expires_at' => now()->addDays(14),
                ]);
            }

            // Notify nearby users about the verified office
            if ($office->latitude && $office->longitude) {
                $nearbyUsers = User::whereNotNull('lat')
                    ->whereNotNull('lng')
                    ->whereRaw(
                        "(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) <= ?",
                        [$office->latitude, $office->longitude, $office->latitude, 15]
                    )
                    ->where('search_preferences->behavior->enable_notifications', true)
                    ->whereNotIn('id', $recentUsers)
                    ->limit(50)
                    ->get();

                foreach ($nearbyUsers as $user) {
                    $this->createNotification([
                        'user_id' => $user->id,
                        'title' => 'Verified Office Near You',
                        'message' => "'{$office->company_name}' in your area has been verified. You can now trust their services with confidence!",
                        'type' => 'promotion',
                        'priority' => 'medium',
                        'data' => [
                            'office_id' => $officeId,
                            'office_name' => $office->company_name,
                            'office_city' => $office->city,
                            'verification_badge' => true,
                        ],
                        'action_url' => "/offices/{$officeId}",
                        'action_text' => 'Explore Services',
                        'expires_at' => now()->addDays(7),
                    ]);
                }
            }

            Log::info("Sent verification notifications for office: {$officeId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending office verification notifications: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send appointment-related notifications to office
     */
    public function sendOfficeAppointmentNotification($appointmentId, $type = 'new')
    {
        try {
            $appointment = Appointment::with(['user', 'agent', 'office', 'property'])->find($appointmentId);
            if (!$appointment || !$appointment->office_id) {
                return false;
            }

            $messageMap = [
                'new' => "New appointment scheduled with {$appointment->client_name} on {$appointment->appointment_date} at {$appointment->appointment_time}",
                'cancelled' => "Appointment with {$appointment->client_name} scheduled for {$appointment->appointment_date} has been cancelled",
                'rescheduled' => "Appointment with {$appointment->client_name} has been rescheduled to {$appointment->appointment_date} at {$appointment->appointment_time}",
                'confirmed' => "Appointment with {$appointment->client_name} for {$appointment->appointment_date} has been confirmed",
            ];

            $this->createNotification([
                'office_id' => $appointment->office_id,
                'title' => 'Appointment ' . ucfirst($type),
                'message' => $messageMap[$type] ?? 'Appointment status updated',
                'type' => 'appointment',
                'priority' => $type === 'new' ? 'high' : 'medium',
                'data' => [
                    'appointment_id' => $appointmentId,
                    'client_name' => $appointment->client_name,
                    'client_phone' => $appointment->client_phone,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                    'appointment_type' => $appointment->type,
                    'status_change' => $type,
                ],
                'action_url' => "/appointments/{$appointmentId}",
                'action_text' => 'View Appointment',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending office appointment notification: ' . $e->getMessage());
            return false;
        }
    }
}