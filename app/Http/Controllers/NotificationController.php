<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Agent;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Property;
use App\Models\RealEstateOffice;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Single shared Firebase instance for the whole request.
     * Previously this was `new FirebaseService()` inside every loop iteration,
     * which reloaded the service-account credentials once per recipient.
     */
    private ?FirebaseService $fcm = null;

    /** Rows inserted per DB batch. */
    private const CHUNK = 500;

    public function __construct()
    {
        try {
            if (class_exists(FirebaseService::class)) {
                $this->fcm = app(FirebaseService::class);
            } else {
                Log::warning('FirebaseService class not found — all FCM sends will be skipped.');
            }
        } catch (\Throwable $e) {
            Log::error('FirebaseService could not be resolved: ' . $e->getMessage());
            $this->fcm = null;
        }
    }

    // =================================================================
    // READ / MUTATE  (API)
    // =================================================================

    /**
     * List notifications for the authenticated user / agent / office.
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type'     => 'nullable|in:property,appointment,system,promotion,alert',
                'priority' => 'nullable|in:low,medium,high,urgent',
                'is_read'  => 'nullable|boolean',
                'limit'    => 'nullable|integer|min:1|max:100',
                'offset'   => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error(
                    ResponseDetails::unauthorizedMessage(),
                    null,
                    ResponseDetails::CODE_UNAUTHORIZED
                );
            }

            $column = $this->getRecipientColumn($user);

            $query = Notification::query()
                ->notExpired()
                ->where($column, $user->id);

            if ($request->filled('type')) {
                $query->byType($request->type);
            }

            if ($request->filled('priority')) {
                $query->byPriority($request->priority);
            }

            if ($request->has('is_read')) {
                $request->boolean('is_read')
                    ? $query->where('is_read', true)
                    : $query->unread();
            }

            $limit  = (int) $request->get('limit', 20);
            $offset = (int) $request->get('offset', 0);

            // FIX: total_count used to return the page size, so the client
            // could never tell whether another page existed. Count first,
            // then slice.
            $totalCount = (clone $query)->count();

            $notifications = $query->orderBy('sent_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();

            $unreadCount = Notification::query()
                ->notExpired()
                ->unread()
                ->where($column, $user->id)
                ->count();

            return ApiResponse::success(
                ResponseDetails::successMessage('Notifications retrieved successfully'),
                [
                    'notifications' => $notifications,
                    'unread_count'  => $unreadCount,
                    'total_count'   => $totalCount,
                    'returned'      => $notifications->count(),
                    'limit'         => $limit,
                    'offset'        => $offset,
                    'has_more'      => ($offset + $notifications->count()) < $totalCount,
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
                    'is_read'    => true,
                    'read_at'    => now(),
                    'updated_at' => now(),
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

            $deletedCount = Notification::where($this->getRecipientColumn($user), $user->id)->delete();

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

    // =================================================================
    // TRANSACTIONAL NOTIFICATIONS
    // =================================================================

    public function sendWelcomeNotification($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                Log::warning("User not found for welcome notification: {$userId}");
                return false;
            }

            $title   = 'Welcome to Our Platform!';
            $message = "Hello {$user->username}! Welcome to our real estate platform. "
                . 'Explore thousands of properties and find your dream home.';

            $notification = $this->createNotification([
                'user_id'     => $userId,
                'title'       => $title,
                'message'     => $message,
                'type'        => 'system',
                'priority'    => 'medium',
                'data'        => [
                    'welcome_bonus'     => true,
                    'user_type'         => 'new_user',
                    'registration_date' => now()->toDateString(),
                ],
                'action_url'  => '/properties',
                'action_text' => 'Browse Properties',
            ]);

            $this->push($user, compact('title', 'message'), [
                'type'              => 'system',
                'id'                => $notification?->id,
                'priority'          => 'medium',
                'action_url'        => '/properties',
                'action_text'       => 'Browse Properties',
                'user_type'         => 'new_user',
                'welcome_bonus'     => true,
                'registration_date' => now()->toDateString(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending welcome notification: ' . $e->getMessage());
            return false;
        }
    }

    public function sendLoginNotification($userId, $deviceInfo = null)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            if (!$this->notificationsEnabled($user)) {
                return false;
            }

            $title   = 'Login Alert';
            $message = 'Your account was accessed from a new device or location at '
                . now()->format('M j, Y g:i A');

            $notification = $this->createNotification([
                'user_id'    => $userId,
                'title'      => $title,
                'message'    => $message,
                'type'       => 'system',
                'priority'   => 'low',
                'data'       => [
                    'device_info' => $deviceInfo,
                    'login_time'  => now()->toISOString(),
                ],
                'expires_at' => now()->addDays(7),
            ]);

            $this->push($user, compact('title', 'message'), [
                'type'        => 'system',
                'id'          => $notification?->id,
                'device_info' => $deviceInfo,
                'login_time'  => now()->toISOString(),
                'priority'    => 'low',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending login notification: ' . $e->getMessage(), [
                'user_id' => $userId,
            ]);
            return false;
        }
    }

    /**
     * Notify users / agents / offices about a newly listed property in their city.
     */
    public function sendNewPropertyNotifications($propertyId)
    {
        try {
            $property = Property::find($propertyId);
            if (!$property || !$property->is_active) {
                return false;
            }

            $addressDetails = $this->asArray($property->address_details);

            $cityEn = strtolower(trim($addressDetails['city']['en'] ?? ''));
            $cityAr = trim($addressDetails['city']['ar'] ?? '');
            $cityKu = trim($addressDetails['city']['ku'] ?? '');

            $interestedUsers   = $this->findInterestedUsers($property, $cityEn, $cityAr, $cityKu);
            $interestedAgents  = $this->findInterestedAgents($cityEn, $cityAr, $cityKu);
            $interestedOffices = $this->findInterestedOffices($cityEn, $cityAr, $cityKu);

            if ($interestedUsers->isEmpty() && $interestedAgents->isEmpty() && $interestedOffices->isEmpty()) {
                Log::info("No interested recipients found for property: {$propertyId}");
                return true;
            }

            $titles = [
                'en' => 'New Property Alert!',
                'ar' => 'تنبيه عقار جديد!',
                'ku' => 'ئاگادارکردنەوەی خانووی نوێ!',
            ];

            $names = $this->asArray($property->name);

            $messages = [
                'en' => 'A new property matching your city has been listed: '
                    . ($names['en'] ?? $names['ar'] ?? $names['ku'] ?? 'New Property'),
                'ar' => 'تم إدراج عقار جديد في مدينتك: '
                    . ($names['ar'] ?? $names['en'] ?? $names['ku'] ?? 'عقار جديد'),
                'ku' => 'خانووێکی نوێ لە شارەکەتدا تۆمار کرا: '
                    . ($names['ku'] ?? $names['en'] ?? $names['ar'] ?? 'خانووی نوێ'),
            ];

            $price = $this->asArray($property->price);
            $type  = $this->asArray($property->type);

            $actionText = fn(string $lang) => match ($lang) {
                'ar'    => 'عرض العقار',
                'ku'    => 'خانووەکە ببینە',
                default => 'View Property',
            };

            // One closure handles all three audiences — the original repeated
            // this block three times with only the ID column changing.
            $notify = function ($recipients, string $column) use (
                $titles,
                $messages,
                $property,
                $propertyId,
                $addressDetails,
                $price,
                $type,
                $actionText
            ) {
                foreach ($recipients as $recipient) {
                    $lang = strtolower(trim($recipient->language ?? 'en'));
                    if (!isset($titles[$lang])) {
                        $lang = 'en';
                    }

                    $notification = $this->createNotification([
                        $column       => $recipient->id,
                        'title'       => $titles[$lang],
                        'message'     => $messages[$lang],
                        'type'        => 'property',
                        'priority'    => 'medium',
                        'data'        => [
                            'property_id'   => (string) $propertyId,
                            'property_type' => $type['category'] ?? null,
                            'price_usd'     => $price['usd'] ?? null,
                            'city'          => $addressDetails['city'] ?? null,
                            'match_reason'  => 'city_match',
                            'language'      => $lang,
                        ],
                        'action_url'  => "/properties/{$propertyId}",
                        'action_text' => $actionText($lang),
                        'expires_at'  => now()->addDays(30),
                    ]);

                    $this->push(
                        $recipient,
                        ['title' => $titles[$lang], 'message' => $messages[$lang]],
                        [
                            'type'          => 'property',
                            'id'            => $notification?->id,
                            'priority'      => 'medium',
                            'property_id'   => $propertyId,
                            'property_type' => $type['category'] ?? '',
                            'price_usd'     => $price['usd'] ?? '',
                            'price_iqd'     => $price['iqd'] ?? '',
                            'city'          => $addressDetails['city'] ?? null,
                            'match_reason'  => 'city_match',
                            'language'      => $lang,
                            'action_url'    => "/properties/{$propertyId}",
                            'action_text'   => $actionText($lang),
                        ]
                    );
                }
            };

            $notify($interestedUsers, 'user_id');
            $notify($interestedAgents, 'agent_id');
            $notify($interestedOffices, 'office_id');

            Log::info('Sent new property notifications', [
                'property_id' => $propertyId,
                'users'       => $interestedUsers->count(),
                'agents'      => $interestedAgents->count(),
                'offices'     => $interestedOffices->count(),
                'total'       => $interestedUsers->count() + $interestedAgents->count() + $interestedOffices->count(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending new property notifications: ' . $e->getMessage());
            return false;
        }
    }

    public function sendNearbyPropertyNotifications($userId, $userLat, $userLng, $radius = 5)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            // FIX: JSON_EXTRACT returns a JSON value, not a number. Without
            // JSON_UNQUOTE + CAST, radians() silently produced garbage and the
            // radius filter never matched correctly.
            $latExpr = 'CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, \'$[0].lat\')) AS DECIMAL(10,7))';
            $lngExpr = 'CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, \'$[0].lng\')) AS DECIMAL(10,7))';

            $nearbyProperties = Property::whereRaw(
                "(6371 * acos(
                    LEAST(1, GREATEST(-1,
                        cos(radians(?)) * cos(radians({$latExpr}))
                        * cos(radians({$lngExpr}) - radians(?))
                        + sin(radians(?)) * sin(radians({$latExpr}))
                    ))
                )) <= ?",
                [$userLat, $userLng, $userLat, $radius]
            )
                ->where('is_active', true)
                ->where('created_at', '>=', now()->subDays(7))
                ->limit(5)
                ->get();

            if ($nearbyProperties->isEmpty()) {
                return true;
            }

            $title   = 'Properties Near You';
            $message = 'We found ' . $nearbyProperties->count() . " new properties within {$radius}km of your location.";

            $notification = $this->createNotification([
                'user_id'     => $userId,
                'title'       => $title,
                'message'     => $message,
                'type'        => 'property',
                'priority'    => 'medium',
                'data'        => [
                    'nearby_properties' => $nearbyProperties->pluck('id')->toArray(),
                    'radius_km'         => $radius,
                    'user_location'     => ['lat' => $userLat, 'lng' => $userLng],
                ],
                'action_url'  => '/properties/nearby',
                'action_text' => 'View Nearby Properties',
                'expires_at'  => now()->addDays(14),
            ]);

            $this->push($user, compact('title', 'message'), [
                'type'              => 'property',
                'id'                => $notification?->id,
                'priority'          => 'medium',
                'nearby_properties' => $nearbyProperties->pluck('id')->toArray(),
                'radius_km'         => $radius,
                'user_location'     => ['lat' => $userLat, 'lng' => $userLng],
                'action_url'        => '/properties/nearby',
                'action_text'       => 'View Nearby Properties',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending nearby property notifications: ' . $e->getMessage());
            return false;
        }
    }

    public function sendAppointmentNotifications($appointmentId)
    {
        try {
            $appointment = Appointment::with(['user', 'agent', 'office', 'property'])->find($appointmentId);
            if (!$appointment) {
                return false;
            }

            $when = "{$appointment->appointment_date} at {$appointment->appointment_time}";

            // ── User ──────────────────────────────────────────────────────
            if ($appointment->user) {
                $title   = 'Appointment Scheduled';
                $message = "Your appointment has been scheduled for {$when}.";

                $notification = $this->createNotification([
                    'user_id'     => $appointment->user_id,
                    'title'       => $title,
                    'message'     => $message,
                    'type'        => 'appointment',
                    'priority'    => 'high',
                    'data'        => [
                        'appointment_id'   => (string) $appointmentId,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                        'appointment_type' => $appointment->type,
                        'property_id'      => $appointment->property_id,
                    ],
                    'action_url'  => "/appointments/{$appointmentId}",
                    'action_text' => 'View Appointment',
                ]);

                $this->push($appointment->user, compact('title', 'message'), [
                    'type'             => 'appointment',
                    'id'               => $notification?->id,
                    'priority'         => 'high',
                    'appointment_id'   => $appointmentId,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                    'appointment_type' => $appointment->type,
                    'property_id'      => $appointment->property_id,
                    'action_url'       => "/appointments/{$appointmentId}",
                    'action_text'      => 'View Appointment',
                ]);
            }

            // ── Agent ─────────────────────────────────────────────────────
            if ($appointment->agent_id) {
                $title   = 'New Appointment';
                $message = "You have a new appointment scheduled with {$appointment->client_name} on {$when}.";

                $notification = $this->createNotification([
                    'agent_id'    => $appointment->agent_id,
                    'title'       => $title,
                    'message'     => $message,
                    'type'        => 'appointment',
                    'priority'    => 'high',
                    'data'        => [
                        'appointment_id'   => (string) $appointmentId,
                        'client_name'      => $appointment->client_name,
                        'client_phone'     => $appointment->client_phone,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                    ],
                    'action_url'  => "/appointments/{$appointmentId}",
                    'action_text' => 'View Appointment',
                ]);

                $this->push($appointment->agent, compact('title', 'message'), [
                    'type'             => 'appointment',
                    'id'               => $notification?->id,
                    'priority'         => 'high',
                    'appointment_id'   => $appointmentId,
                    'client_name'      => $appointment->client_name,
                    'client_phone'     => $appointment->client_phone,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                    'action_url'       => "/appointments/{$appointmentId}",
                    'action_text'      => 'View Appointment',
                ]);
            }

            // ── Office ────────────────────────────────────────────────────
            if ($appointment->office_id) {
                $title   = 'New Appointment';
                $message = "A new appointment has been scheduled with {$appointment->client_name} on {$when}.";

                $notification = $this->createNotification([
                    'office_id'   => $appointment->office_id,
                    'title'       => $title,
                    'message'     => $message,
                    'type'        => 'appointment',
                    'priority'    => 'medium',
                    'data'        => [
                        'appointment_id'   => (string) $appointmentId,
                        'client_name'      => $appointment->client_name,
                        'agent_id'         => $appointment->agent_id,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                    ],
                    'action_url'  => "/appointments/{$appointmentId}",
                    'action_text' => 'View Appointment',
                ]);

                // FIX: the office branch created the DB row but never pushed.
                $this->push($appointment->office, compact('title', 'message'), [
                    'type'             => 'appointment',
                    'id'               => $notification?->id,
                    'priority'         => 'medium',
                    'appointment_id'   => $appointmentId,
                    'client_name'      => $appointment->client_name,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                    'action_url'       => "/appointments/{$appointmentId}",
                    'action_text'      => 'View Appointment',
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending appointment notifications: ' . $e->getMessage());
            return false;
        }
    }

    public function sendAppointmentStatusNotification($appointmentId, $newStatus)
    {
        try {
            $appointment = Appointment::with(['user', 'agent', 'office'])->find($appointmentId);
            if (!$appointment || !$appointment->user) {
                return false;
            }

            $statusMessages = [
                'confirmed' => 'Your appointment has been confirmed',
                'completed' => 'Your appointment has been completed',
                'cancelled' => 'Your appointment has been cancelled',
            ];

            $base    = $statusMessages[$newStatus] ?? "Your appointment status has been updated to {$newStatus}";
            $title   = 'Appointment Update';
            $message = $base . " for {$appointment->appointment_date} at {$appointment->appointment_time}.";
            $prio    = $newStatus === 'cancelled' ? 'high' : 'medium';

            $notification = $this->createNotification([
                'user_id'     => $appointment->user_id,
                'title'       => $title,
                'message'     => $message,
                'type'        => 'appointment',
                'priority'    => $prio,
                'data'        => [
                    'appointment_id'   => (string) $appointmentId,
                    'old_status'       => $appointment->getOriginal('status'),
                    'new_status'       => $newStatus,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                ],
                'action_url'  => "/appointments/{$appointmentId}",
                'action_text' => 'View Appointment',
            ]);

            $this->push($appointment->user, compact('title', 'message'), [
                'type'             => 'appointment',
                'id'               => $notification?->id,
                'priority'         => $prio,
                'appointment_id'   => $appointmentId,
                'old_status'       => $appointment->getOriginal('status'),
                'new_status'       => $newStatus,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'action_url'       => "/appointments/{$appointmentId}",
                'action_text'      => 'View Appointment',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending appointment status notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reminder for tomorrow's appointments. Chunked so a busy day can't
     * exhaust memory or blow the execution limit.
     */
    public function sendAppointmentReminders()
    {
        try {
            $total = 0;

            Appointment::with(['user', 'agent'])
                ->whereDate('appointment_date', now()->addDay())
                ->whereIn('status', ['pending', 'confirmed'])
                ->chunkById(200, function ($appointments) use (&$total) {
                    foreach ($appointments as $appointment) {
                        $total++;

                        if ($appointment->user) {
                            $title   = 'Appointment Reminder';
                            $message = "You have an appointment tomorrow at {$appointment->appointment_time}. Don't forget!";

                            $n = $this->createNotification([
                                'user_id'     => $appointment->user_id,
                                'title'       => $title,
                                'message'     => $message,
                                'type'        => 'appointment',
                                'priority'    => 'medium',
                                'data'        => [
                                    'appointment_id'   => (string) $appointment->id,
                                    'appointment_date' => $appointment->appointment_date,
                                    'appointment_time' => $appointment->appointment_time,
                                    'reminder_type'    => 'day_before',
                                ],
                                'action_url'  => "/appointments/{$appointment->id}",
                                'action_text' => 'View Appointment',
                                'expires_at'  => now()->addDays(2),
                            ]);

                            $this->push($appointment->user, compact('title', 'message'), [
                                'type'             => 'appointment',
                                'id'               => $n?->id,
                                'priority'         => 'medium',
                                'appointment_id'   => $appointment->id,
                                'appointment_date' => $appointment->appointment_date,
                                'appointment_time' => $appointment->appointment_time,
                                'reminder_type'    => 'day_before',
                                'action_url'       => "/appointments/{$appointment->id}",
                                'action_text'      => 'View Appointment',
                            ]);
                        }

                        if ($appointment->agent_id) {
                            $title   = 'Appointment Reminder';
                            $message = "You have an appointment with {$appointment->client_name} tomorrow at {$appointment->appointment_time}.";

                            $n = $this->createNotification([
                                'agent_id'    => $appointment->agent_id,
                                'title'       => $title,
                                'message'     => $message,
                                'type'        => 'appointment',
                                'priority'    => 'medium',
                                'data'        => [
                                    'appointment_id'   => (string) $appointment->id,
                                    'client_name'      => $appointment->client_name,
                                    'appointment_date' => $appointment->appointment_date,
                                    'appointment_time' => $appointment->appointment_time,
                                    'reminder_type'    => 'day_before',
                                ],
                                'action_url'  => "/appointments/{$appointment->id}",
                                'action_text' => 'View Appointment',
                                'expires_at'  => now()->addDays(2),
                            ]);

                            $this->push($appointment->agent, compact('title', 'message'), [
                                'type'             => 'appointment',
                                'id'               => $n?->id,
                                'priority'         => 'medium',
                                'appointment_id'   => $appointment->id,
                                'client_name'      => $appointment->client_name,
                                'appointment_date' => $appointment->appointment_date,
                                'appointment_time' => $appointment->appointment_time,
                                'reminder_type'    => 'day_before',
                                'action_url'       => "/appointments/{$appointment->id}",
                                'action_text'      => 'View Appointment',
                            ]);
                        }
                    }
                });

            Log::info("Sent appointment reminders for {$total} appointments");
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending appointment reminders: ' . $e->getMessage());
            return false;
        }
    }

    public function sendPriceDropNotification($propertyId, $oldPriceUSD, $newPriceUSD)
    {
        try {
            $property = Property::find($propertyId);
            if (!$property) {
                return false;
            }

            $old = (float) $oldPriceUSD;
            $new = (float) $newPriceUSD;

            // FIX: this used to divide by $oldPriceUSD unguarded — a property
            // with a null/zero old price threw a DivisionByZeroError.
            if ($old <= 0 || $new >= $old) {
                return false;
            }

            $priceDropPercent = (int) round((($old - $new) / $old) * 100);
            if ($priceDropPercent < 5) {
                return false;
            }

            $title   = 'Price Drop Alert! 📉';
            $message = "Great news! The price of a property you might like has dropped by {$priceDropPercent}%!";

            $interestedUsers = $this->findInterestedUsers($property);

            foreach ($interestedUsers as $user) {
                $notification = $this->createNotification([
                    'user_id'     => $user->id,
                    'title'       => $title,
                    'message'     => $message,
                    'type'        => 'property',
                    'priority'    => 'high',
                    'data'        => [
                        'property_id'        => (string) $propertyId,
                        'old_price_usd'      => $old,
                        'new_price_usd'      => $new,
                        'price_drop_percent' => $priceDropPercent,
                        'savings_usd'        => $old - $new,
                    ],
                    'action_url'  => "/properties/{$propertyId}",
                    'action_text' => 'View Property',
                    'expires_at'  => now()->addDays(14),
                ]);

                $this->push($user, compact('title', 'message'), [
                    'type'               => 'property',
                    'id'                 => $notification?->id,
                    'priority'           => 'high',
                    'property_id'        => $propertyId,
                    'old_price_usd'      => $old,
                    'new_price_usd'      => $new,
                    'price_drop_percent' => $priceDropPercent,
                    'savings_usd'        => $old - $new,
                    'action_url'         => "/properties/{$propertyId}",
                    'action_text'        => 'View Property',
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending price drop notification: ' . $e->getMessage());
            return false;
        }
    }

    public function sendNewOfficeNotification($officeId)
    {
        try {
            $office = RealEstateOffice::find($officeId);
            if (!$office || !$office->latitude || !$office->longitude) {
                return false;
            }

            $radius  = 20;
            $title   = 'New Real Estate Office in Your Area';
            $message = "A new real estate office '{$office->company_name}' has opened in your area. Check out their services!";

            $interestedUsers = $this->notifiableUsers()
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->whereRaw(
                    '(6371 * acos(LEAST(1, GREATEST(-1,
                        cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?))
                        + sin(radians(?)) * sin(radians(lat))
                    )))) <= ?',
                    [$office->latitude, $office->longitude, $office->latitude, $radius]
                )
                ->limit(100)
                ->get();

            foreach ($interestedUsers as $user) {
                $notification = $this->createNotification([
                    'user_id'     => $user->id,
                    'title'       => $title,
                    'message'     => $message,
                    'type'        => 'system',
                    'priority'    => 'medium',
                    'data'        => [
                        'office_id'        => (string) $officeId,
                        'office_name'      => $office->company_name,
                        'office_city'      => $office->city,
                        'office_district'  => $office->district,
                        'years_experience' => $office->years_experience,
                    ],
                    'action_url'  => "/offices/{$officeId}",
                    'action_text' => 'View Office',
                    'expires_at'  => now()->addDays(30),
                ]);

                $this->push($user, compact('title', 'message'), [
                    'type'             => 'system',
                    'id'               => $notification?->id,
                    'priority'         => 'medium',
                    'office_id'        => $officeId,
                    'office_name'      => $office->company_name,
                    'office_city'      => $office->city,
                    'office_district'  => $office->district,
                    'years_experience' => $office->years_experience,
                    'action_url'       => "/offices/{$officeId}",
                    'action_text'      => 'View Office',
                ]);
            }

            Log::info("Sent new office notifications to {$interestedUsers->count()} users for office: {$officeId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending new office notifications: ' . $e->getMessage());
            return false;
        }
    }

    public function sendOfficeVerificationNotification($officeId)
    {
        try {
            $office = RealEstateOffice::find($officeId);
            if (!$office) {
                return false;
            }

            // ── Office's own agents ───────────────────────────────────────
            $agentTitle   = 'Office Verified Successfully!';
            $agentMessage = "Congratulations! Your office '{$office->company_name}' has been verified. "
                . 'This will increase your credibility with clients.';

            foreach (Agent::where('office_id', $officeId)->get() as $agent) {
                $n = $this->createNotification([
                    'agent_id'    => $agent->id,
                    'title'       => $agentTitle,
                    'message'     => $agentMessage,
                    'type'        => 'system',
                    'priority'    => 'high',
                    'data'        => [
                        'office_id'         => (string) $officeId,
                        'office_name'       => $office->company_name,
                        'verification_date' => now()->toDateString(),
                    ],
                    'action_url'  => "/offices/{$officeId}",
                    'action_text' => 'View Office Profile',
                ]);

                $this->push($agent, ['title' => $agentTitle, 'message' => $agentMessage], [
                    'type'              => 'system',
                    'id'                => $n?->id,
                    'priority'          => 'high',
                    'office_id'         => $officeId,
                    'office_name'       => $office->company_name,
                    'verification_date' => now()->toDateString(),
                    'action_url'        => "/offices/{$officeId}",
                    'action_text'       => 'View Office Profile',
                ]);
            }

            // ── Users who booked with this office in the last 30 days ─────
            $recentUsers = User::whereIn('id', function ($q) use ($officeId) {
                $q->select('user_id')
                    ->from('appointments')
                    ->where('office_id', $officeId)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->whereNotNull('user_id')
                    ->distinct();
            })->get();

            if ($recentUsers->isNotEmpty()) {
                $title   = 'Office Verification Update';
                $message = "Good news! '{$office->company_name}' that you recently interacted with "
                    . 'has been verified for authenticity and quality.';

                $this->insertBulk($recentUsers, 'user_id', [
                    'title'       => $title,
                    'message'     => $message,
                    'type'        => 'system',
                    'priority'    => 'medium',
                    'data'        => [
                        'office_id'         => (string) $officeId,
                        'office_name'       => $office->company_name,
                        'verification_date' => now()->toDateString(),
                    ],
                    'action_url'  => "/offices/{$officeId}",
                    'action_text' => 'View Office',
                    'expires_at'  => now()->addDays(14),
                ]);

                $this->pushMany($recentUsers, compact('title', 'message'), [
                    'type'              => 'system',
                    'priority'          => 'medium',
                    'office_id'         => $officeId,
                    'office_name'       => $office->company_name,
                    'verification_date' => now()->toDateString(),
                    'action_url'        => "/offices/{$officeId}",
                    'action_text'       => 'View Office',
                ]);
            }

            // ── Nearby users who haven't interacted ───────────────────────
            if ($office->latitude && $office->longitude) {
                $nearbyUsers = $this->notifiableUsers()
                    ->whereNotNull('lat')
                    ->whereNotNull('lng')
                    ->whereRaw(
                        '(6371 * acos(LEAST(1, GREATEST(-1,
                            cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?))
                            + sin(radians(?)) * sin(radians(lat))
                        )))) <= ?',
                        [$office->latitude, $office->longitude, $office->latitude, 15]
                    )
                    ->whereNotIn('id', $recentUsers->pluck('id'))
                    ->limit(50)
                    ->get();

                if ($nearbyUsers->isNotEmpty()) {
                    $title   = 'Verified Office Near You';
                    $message = "'{$office->company_name}' in your area has been verified. "
                        . 'You can now trust their services with confidence!';

                    $this->insertBulk($nearbyUsers, 'user_id', [
                        'title'       => $title,
                        'message'     => $message,
                        'type'        => 'promotion',
                        'priority'    => 'medium',
                        'data'        => [
                            'office_id'          => (string) $officeId,
                            'office_name'        => $office->company_name,
                            'office_city'        => $office->city,
                            'verification_badge' => true,
                        ],
                        'action_url'  => "/offices/{$officeId}",
                        'action_text' => 'Explore Services',
                        'expires_at'  => now()->addDays(7),
                    ]);

                    $this->pushMany($nearbyUsers, compact('title', 'message'), [
                        'type'               => 'promotion',
                        'priority'           => 'medium',
                        'office_id'          => $officeId,
                        'office_name'        => $office->company_name,
                        'office_city'        => $office->city,
                        'verification_badge' => true,
                        'action_url'         => "/offices/{$officeId}",
                        'action_text'        => 'Explore Services',
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

    public function sendOfficeAppointmentNotification($appointmentId, $type = 'new')
    {
        try {
            $appointment = Appointment::with(['user', 'agent', 'office', 'property'])->find($appointmentId);
            if (!$appointment || !$appointment->office_id) {
                return false;
            }

            $when = "{$appointment->appointment_date} at {$appointment->appointment_time}";

            $messageMap = [
                'new'         => "New appointment scheduled with {$appointment->client_name} on {$when}",
                'cancelled'   => "Appointment with {$appointment->client_name} scheduled for {$appointment->appointment_date} has been cancelled",
                'rescheduled' => "Appointment with {$appointment->client_name} has been rescheduled to {$when}",
                'confirmed'   => "Appointment with {$appointment->client_name} for {$appointment->appointment_date} has been confirmed",
            ];

            $title   = 'Appointment ' . ucfirst($type);
            $message = $messageMap[$type] ?? 'Appointment status updated';
            $prio    = $type === 'new' ? 'high' : 'medium';

            $notification = $this->createNotification([
                'office_id'   => $appointment->office_id,
                'title'       => $title,
                'message'     => $message,
                'type'        => 'appointment',
                'priority'    => $prio,
                'data'        => [
                    'appointment_id'   => (string) $appointmentId,
                    'client_name'      => $appointment->client_name,
                    'client_phone'     => $appointment->client_phone,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                    'appointment_type' => $appointment->type,
                    'status_change'    => $type,
                ],
                'action_url'  => "/appointments/{$appointmentId}",
                'action_text' => 'View Appointment',
            ]);

            // FIX: this method only ever wrote to the DB — no push was sent.
            $this->push($appointment->office, compact('title', 'message'), [
                'type'             => 'appointment',
                'id'               => $notification?->id,
                'priority'         => $prio,
                'appointment_id'   => $appointmentId,
                'client_name'      => $appointment->client_name,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'status_change'    => $type,
                'action_url'       => "/appointments/{$appointmentId}",
                'action_text'      => 'View Appointment',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending office appointment notification: ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // PROPERTY LIFECYCLE (owner-facing)
    // =================================================================

    public function sendPropertyVerificationNotification($propertyId)
    {
        return $this->notifyOwner(
            $propertyId,
            fn($p) => $p->verified,
            fn($p) => [
                'title'       => 'Property Verified Successfully!',
                'message'     => "Congratulations! Your property '{$this->getPropertyName($p)}' has been verified by our team.",
                'priority'    => 'high',
                'data'        => [
                    'property_id'       => (string) $p->id,
                    'property_name'     => $p->name,
                    'verification_date' => now()->toDateString(),
                ],
            ]
        );
    }

    public function sendPropertyStatusChangeNotification($propertyId, $oldStatus, $newStatus)
    {
        $important = ['sold', 'rented', 'available', 'cancelled'];
        if (!in_array($newStatus, $important, true)) {
            return false;
        }

        $statusMessages = [
            'sold'      => 'Your property has been marked as sold',
            'rented'    => 'Your property has been marked as rented',
            'available' => 'Your property is now available for viewing',
            'cancelled' => 'Your property listing has been cancelled',
        ];

        $base = $statusMessages[$newStatus] ?? "Your property status has been updated to {$newStatus}";

        return $this->notifyOwner(
            $propertyId,
            fn($p) => true,
            fn($p) => [
                'title'    => 'Property Status Update',
                'message'  => $base . ' - ' . $this->getPropertyName($p),
                'priority' => $newStatus === 'cancelled' ? 'high' : 'medium',
                'data'     => [
                    'property_id'   => (string) $p->id,
                    'old_status'    => $oldStatus,
                    'new_status'    => $newStatus,
                    'property_name' => $p->name,
                ],
            ]
        );
    }

    public function sendPropertyBoostNotification($propertyId, $isBoosted)
    {
        return $this->notifyOwner(
            $propertyId,
            fn($p) => true,
            fn($p) => [
                'title'    => $isBoosted ? 'Property Boosted!' : 'Property Boost Removed',
                'message'  => $isBoosted
                    ? "Your property '{$this->getPropertyName($p)}' is now boosted and will get more visibility!"
                    : "The boost for your property '{$this->getPropertyName($p)}' has been removed.",
                'priority' => 'medium',
                'data'     => [
                    'property_id'   => (string) $p->id,
                    'property_name' => $p->name,
                    'is_boosted'    => (bool) $isBoosted,
                    'boost_date'    => now()->toDateString(),
                ],
            ]
        );
    }

    public function sendPropertyPublishNotification($propertyId, $isPublished)
    {
        return $this->notifyOwner(
            $propertyId,
            fn($p) => true,
            fn($p) => [
                'title'    => $isPublished ? 'Property Published!' : 'Property Unpublished',
                'message'  => $isPublished
                    ? "Your property '{$this->getPropertyName($p)}' is now live and visible to users!"
                    : "Your property '{$this->getPropertyName($p)}' has been removed from public listings.",
                'priority' => 'medium',
                'data'     => [
                    'property_id'   => (string) $p->id,
                    'property_name' => $p->name,
                    'is_published'  => (bool) $isPublished,
                    'publish_date'  => now()->toDateString(),
                ],
            ]
        );
    }

    /**
     * Shared body for the four owner-facing property notifications above.
     */
    private function notifyOwner($propertyId, callable $guard, callable $build): bool
    {
        try {
            $property = Property::find($propertyId);
            if (!$property || !$guard($property)) {
                return false;
            }

            $owner = $this->loadOwner($property);
            if (!$owner) {
                return false;
            }

            $payload = $build($property);

            $notification = $this->createNotification([
                $this->getOwnerColumn($owner) => $owner->id,
                'title'       => $payload['title'],
                'message'     => $payload['message'],
                'type'        => 'system',
                'priority'    => $payload['priority'],
                'data'        => $payload['data'],
                'action_url'  => "/properties/{$propertyId}",
                'action_text' => 'View Property',
            ]);

            $this->push(
                $owner,
                ['title' => $payload['title'], 'message' => $payload['message']],
                array_merge($payload['data'], [
                    'type'        => 'system',
                    'id'          => $notification?->id,
                    'priority'    => $payload['priority'],
                    'action_url'  => "/properties/{$propertyId}",
                    'action_text' => 'View Property',
                ])
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending owner property notification: ' . $e->getMessage(), [
                'property_id' => $propertyId,
            ]);
            return false;
        }
    }

    // =================================================================
    // ADMIN BROADCASTS
    // =================================================================

    /**
     * Single-language system announcement.
     */
    public function sendSystemAnnouncement(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title'          => 'required|string|max:255',
                'message'        => 'required|string',
                'priority'       => 'required|in:low,medium,high,urgent',
                'recipient_type' => 'required|in:users,agents,offices,all',
                'expires_at'     => 'nullable|date|after:now',
                'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'image_url'      => 'nullable|string|max:500',
                'action_url'     => 'nullable|string|max:255',
                'action_text'    => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $imageUrl = $this->resolveImageUrl($request);

            $counts = $this->fanOut([
                'recipient_type' => $request->recipient_type,
                'titles'         => ['en' => $request->title],
                'messages'       => ['en' => $request->message],
                'type'           => 'system',
                'priority'       => $request->priority,
                'image_url'      => $imageUrl,
                'action_url'     => $request->action_url,
                'action_text'    => $request->action_text,
                'expires_at'     => $request->expires_at,
                'sent_at'        => now(),
                'send_fcm'       => true,
                'data_extra'     => ['announcement' => true],
            ]);

            return ApiResponse::success(
                ResponseDetails::successMessage('System announcement sent successfully'),
                [
                    'sent_to'        => array_sum($counts),
                    'recipient_type' => $request->recipient_type,
                    'breakdown'      => $counts,
                    'image_url'      => $imageUrl,
                ],
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
     * Multilingual broadcast (EN required, AR/KU optional).
     */
    public function sendBroadcast(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title_en'       => 'required|string|max:100',
                'message_en'     => 'required|string|max:300',
                'title_ar'       => 'nullable|string|max:100',
                'message_ar'     => 'nullable|string|max:300',
                'title_ku'       => 'nullable|string|max:100',
                'message_ku'     => 'nullable|string|max:300',
                'type'           => 'required|in:system,property,promotion,alert',
                'priority'       => 'required|in:low,medium,high,urgent',
                'recipient_type' => 'required|in:users,agents,offices,all',
                'action_url'     => 'nullable|string|max:255',
                'action_text'    => 'nullable|string|max:100',
                'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'image_url'      => 'nullable|string|max:500',
                'expires_at'     => 'nullable|date|after:now',
                'scheduled_at'   => 'nullable|date|after:now',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $imageUrl = $this->resolveImageUrl($request);

            // Only keep languages the admin actually filled in.
            $titles = array_filter([
                'en' => $request->title_en,
                'ar' => $request->title_ar,
                'ku' => $request->title_ku,
            ]);

            $messages = array_filter([
                'en' => $request->message_en,
                'ar' => $request->message_ar,
                'ku' => $request->message_ku,
            ]);

            // FIX: scheduled_at was validated and then thrown away. Rows are
            // now stored with a future sent_at and the push is deferred; the
            // `notifications:dispatch-scheduled` command picks them up.
            $scheduledAt = $request->filled('scheduled_at')
                ? \Carbon\Carbon::parse($request->scheduled_at)
                : null;

            $counts = $this->fanOut([
                'recipient_type' => $request->recipient_type,
                'titles'         => $titles,
                'messages'       => $messages,
                'type'           => $request->type,
                'priority'       => $request->priority,
                'image_url'      => $imageUrl,
                'action_url'     => $request->action_url,
                'action_text'    => $request->action_text,
                'expires_at'     => $request->expires_at,
                'sent_at'        => $scheduledAt ?? now(),
                'send_fcm'       => $scheduledAt === null,
                'data_extra'     => [
                    'broadcast' => true,
                    'titles'    => $titles,
                    'messages'  => $messages,
                    'scheduled' => $scheduledAt !== null,
                ],
            ]);

            $warnings = $this->translationWarnings($request->recipient_type, $titles);

            Log::info('Broadcast queued/sent', [
                'breakdown'    => $counts,
                'total'        => array_sum($counts),
                'type'         => $request->type,
                'image'        => $imageUrl,
                'scheduled_at' => $scheduledAt?->toDateTimeString(),
                'warnings'     => $warnings,
            ]);

            return ApiResponse::success(
                ResponseDetails::successMessage(
                    $scheduledAt ? 'Broadcast scheduled successfully' : 'Broadcast sent successfully'
                ),
                [
                    'sent_to'      => array_sum($counts),
                    'users'        => $counts['user_id']   ?? 0,
                    'agents'       => $counts['agent_id']  ?? 0,
                    'offices'      => $counts['office_id'] ?? 0,
                    'image_url'    => $imageUrl,
                    'scheduled_at' => $scheduledAt?->toDateTimeString(),
                    'warnings'     => $warnings,
                ],
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Broadcast error: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to send broadcast'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }

    /**
     * Chunked fan-out to users / agents / offices.
     *
     * The old version loaded every recipient into memory with ->get(), then
     * looped twice (once to build rows, once for FCM). This walks the table in
     * batches of 500, inserting and pushing in the same pass.
     *
     * @return array<string,int> keyed by recipient column
     */
    private function fanOut(array $opts): array
    {
        $type      = $opts['recipient_type'];
        $titles    = $opts['titles'];
        $messages  = $opts['messages'];
        $imageUrl  = $opts['image_url']  ?? null;
        $sentAt    = $opts['sent_at']    ?? now();
        $sendFcm   = $opts['send_fcm']   ?? true;
        $dataExtra = $opts['data_extra'] ?? [];
        $now       = now();

        $counts = [];

        $targets = [];
        if (in_array($type, ['users', 'all'], true)) {
            // FIX: was `where('search_preferences->behavior->enable_notifications', true)`,
            // which silently excluded every user whose preferences are NULL.
            $targets['user_id'] = $this->notifiableUsers();
        }
        if (in_array($type, ['agents', 'all'], true)) {
            $targets['agent_id'] = Agent::where('is_verified', true);
        }
        if (in_array($type, ['offices', 'all'], true)) {
            $targets['office_id'] = RealEstateOffice::where('is_verified', true);
        }

        $fcmData = $this->stringify(array_merge($dataExtra, [
            'type'        => $opts['type'],
            'priority'    => $opts['priority'],
            'titles'      => $titles,
            'messages'    => $messages,
            'action_url'  => $opts['action_url']  ?? '',
            'action_text' => $opts['action_text'] ?? '',
            'image_url'   => $imageUrl ?? '',
        ]));

        foreach ($targets as $column => $query) {
            $counts[$column] = 0;

            $query->chunkById(self::CHUNK, function ($recipients) use (
                &$counts,
                $column,
                $titles,
                $messages,
                $opts,
                $imageUrl,
                $sentAt,
                $sendFcm,
                $dataExtra,
                $fcmData,
                $now
            ) {
                $rows = [];

                foreach ($recipients as $recipient) {
                    $resolved = $this->resolveLanguage($recipient->language ?? null, $titles, $messages);

                    $rows[] = [
                        'id'          => (string) Str::uuid(),
                        'user_id'     => $column === 'user_id'   ? $recipient->id : null,
                        'agent_id'    => $column === 'agent_id'  ? $recipient->id : null,
                        'office_id'   => $column === 'office_id' ? $recipient->id : null,
                        'title'       => $resolved['title'],
                        'message'     => $resolved['message'],
                        'type'        => $opts['type'],
                        'priority'    => $opts['priority'],
                        'image_url'   => $imageUrl,
                        'data'        => json_encode($dataExtra),
                        'action_url'  => $opts['action_url']  ?? null,
                        'action_text' => $opts['action_text'] ?? null,
                        'is_read'     => false,
                        'sent_at'     => $sentAt,
                        'expires_at'  => $opts['expires_at'] ?? null,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];

                    if ($sendFcm) {
                        $this->push(
                            $recipient,
                            [
                                'title'   => $resolved['title'],
                                'message' => $resolved['message'],
                                'image'   => $imageUrl,
                            ],
                            $fcmData
                        );
                    }
                }

                if ($rows) {
                    DB::table('notifications')->insert($rows);
                    $counts[$column] += count($rows);
                }
            });
        }

        return $counts;
    }

    /**
     * Warn the admin when recipients speak a language they left blank.
     */
    private function translationWarnings(string $recipientType, array $titles): array
    {
        $warnings = [];

        foreach (['ku' => 'Kurdish', 'ar' => 'Arabic'] as $code => $label) {
            if (!empty($titles[$code])) {
                continue;
            }

            $exists = false;

            if (in_array($recipientType, ['users', 'all'], true)) {
                $exists = $this->notifiableUsers()->where('language', $code)->exists();
            }
            if (!$exists && in_array($recipientType, ['agents', 'all'], true)) {
                $exists = Agent::where('is_verified', true)->where('language', $code)->exists();
            }
            if (!$exists && in_array($recipientType, ['offices', 'all'], true)) {
                $exists = RealEstateOffice::where('is_verified', true)->where('language', $code)->exists();
            }

            if ($exists) {
                $warnings[] = "Some recipients have {$label} selected but no {$label} translation "
                    . 'was provided — they received English.';
            }
        }

        return $warnings;
    }

    // =================================================================
    // WEB (admin panel / blade)
    // =================================================================

    public function showNotifications()
    {
        $notifications = Notification::orderBy('sent_at', 'desc')->limit(200)->get();

        return view('agent.notification', compact('notifications'));
    }

    public function showNotificationsPage()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login-page')->with('error', 'Please log in to view your notifications');
            }

            // Kept on the query builder + stdClass mapping on purpose — the
            // user.notifications blade reads `data` as a decoded array.
            $notifications = DB::table('notifications')
                ->where('user_id', $user->id)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->orderBy('sent_at', 'desc')
                ->get()
                ->map(fn($n) => (object) [
                    'id'          => $n->id,
                    'title'       => $n->title,
                    'message'     => $n->message,
                    'type'        => $n->type,
                    'priority'    => $n->priority,
                    'image_url'   => $n->image_url ?? null,
                    'data'        => json_decode($n->data, true),
                    'action_url'  => $n->action_url,
                    'action_text' => $n->action_text,
                    'is_read'     => $n->is_read,
                    'read_at'     => $n->read_at,
                    'sent_at'     => $n->sent_at,
                    'created_at'  => $n->created_at,
                    'updated_at'  => $n->updated_at,
                ]);

            Log::info('User notifications page loaded', [
                'user_id'             => $user->id,
                'notifications_count' => $notifications->count(),
            ]);

            return view('user.notifications', compact('notifications'));
        } catch (\Exception $e) {
            Log::error('Error loading user notifications page', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'Failed to load notifications');
        }
    }

    /**
     * FIX: used to return a redirect on the "not logged in" / "not found"
     * branches and JSON everywhere else, so the fetch() caller choked on HTML.
     * Always JSON now.
     */
    public function markAsReadWeb($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['status' => false, 'message' => 'Please log in'], 401);
            }

            $updated = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->update([
                    'is_read'    => true,
                    'read_at'    => now(),
                    'updated_at' => now(),
                ]);

            if (!$updated) {
                return response()->json(['status' => false, 'message' => 'Notification not found'], 404);
            }

            return response()->json(['status' => true, 'message' => 'Notification marked as read']);
        } catch (\Exception $e) {
            Log::error('Mark notification as read error (web)', [
                'message'         => $e->getMessage(),
                'notification_id' => $id,
                'user_id'         => Auth::id(),
            ]);

            return response()->json(['status' => false, 'message' => 'Failed to mark notification as read'], 500);
        }
    }

    public function deleteWeb($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['status' => false, 'message' => 'Please log in'], 401);
            }

            $deleted = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->delete();

            if (!$deleted) {
                return response()->json(['status' => false, 'message' => 'Notification not found'], 404);
            }

            return response()->json(['status' => true, 'message' => 'Notification deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Delete notification error (web)', [
                'message'         => $e->getMessage(),
                'notification_id' => $id,
                'user_id'         => Auth::id(),
            ]);

            return response()->json(['status' => false, 'message' => 'Failed to delete notification'], 500);
        }
    }

    // =================================================================
    // PRIVATE HELPERS
    // =================================================================

    /**
     * Coerce every FCM data value to a string.
     *
     * This is the big one: FCM's v1 API rejects the whole message if any data
     * value is not a string. The old code passed nulls, ints and booleans
     * (`'id' => null`, `'is_boosted' => true`, `'price_drop_percent' => 42`),
     * so those pushes were failing server-side while the logs said "sent".
     */
    private function stringify(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                $out[$key] = '';
            } elseif (is_bool($value)) {
                $out[$key] = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $out[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            } elseif ($value instanceof \DateTimeInterface) {
                $out[$key] = $value->format(\DateTimeInterface::ATOM);
            } else {
                $out[$key] = (string) $value;
            }
        }

        return $out;
    }

    /**
     * Send one push to whichever recipient type was passed.
     * Never throws — a Firebase failure must not roll back the DB write.
     */
    private function push($recipient, array $notification, array $data = []): bool
    {
        if (!$this->fcm || !$recipient) {
            return false;
        }

        try {
            $payload = $this->stringify($data);

            return match (true) {
                $recipient instanceof User              => (bool) $this->fcm->sendToUser($recipient, $notification, $payload),
                $recipient instanceof Agent             => (bool) $this->fcm->sendToAgent($recipient, $notification, $payload),
                $recipient instanceof RealEstateOffice  => (bool) $this->fcm->sendToOffice($recipient, $notification, $payload),
                default                                 => false,
            };
        } catch (\Throwable $e) {
            Log::error('FCM dispatch failed: ' . $e->getMessage(), [
                'recipient' => $recipient::class,
                'id'        => $recipient->id ?? null,
            ]);
            return false;
        }
    }

    /**
     * Batch push using the multicast endpoint when the collection is Users.
     */
    private function pushMany($recipients, array $notification, array $data = []): void
    {
        if (!$this->fcm || $recipients->isEmpty()) {
            return;
        }

        try {
            $payload = $this->stringify($data);

            if (
                $recipients->first() instanceof User
                && method_exists($this->fcm, 'sendToMultipleUsers')
            ) {
                $this->fcm->sendToMultipleUsers($recipients, $notification, $payload);
                return;
            }

            foreach ($recipients as $recipient) {
                $this->push($recipient, $notification, $data);
            }
        } catch (\Throwable $e) {
            Log::error('FCM batch dispatch failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk-insert one notification row per recipient.
     */
    private function insertBulk($recipients, string $column, array $payload): int
    {
        $now  = now();
        $rows = [];

        foreach ($recipients as $recipient) {
            $rows[] = [
                'id'          => (string) Str::uuid(),
                'user_id'     => $column === 'user_id'   ? $recipient->id : null,
                'agent_id'    => $column === 'agent_id'  ? $recipient->id : null,
                'office_id'   => $column === 'office_id' ? $recipient->id : null,
                'title'       => $payload['title'],
                'message'     => $payload['message'],
                'type'        => $payload['type'],
                'priority'    => $payload['priority'],
                'image_url'   => $payload['image_url']   ?? null,
                'data'        => json_encode($payload['data'] ?? []),
                'action_url'  => $payload['action_url']  ?? null,
                'action_text' => $payload['action_text'] ?? null,
                'is_read'     => false,
                'sent_at'     => $now,
                'expires_at'  => $payload['expires_at']  ?? null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        foreach (array_chunk($rows, self::CHUNK) as $chunk) {
            DB::table('notifications')->insert($chunk);
        }

        return count($rows);
    }

    private function createNotification(array $data): ?Notification
    {
        try {
            $data['id']      = (string) Str::uuid();
            $data['sent_at'] = now();

            return Notification::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage(), $data);
            return null;
        }
    }

    /**
     * Base query for users who should receive notifications:
     * preference explicitly on, OR never set (the default).
     */
    private function notifiableUsers()
    {
        return User::where(function ($q) {
            $q->where('search_preferences->behavior->enable_notifications', true)
                ->orWhereNull('search_preferences')
                ->orWhereRaw("JSON_EXTRACT(search_preferences, '\$.behavior.enable_notifications') IS NULL");
        });
    }

    private function notificationsEnabled($user): bool
    {
        $prefs = $user->search_preferences ?? [];

        return (bool) ($prefs['behavior']['enable_notifications'] ?? true);
    }

    /**
     * Find users interested in a property, matched by city in any of the
     * three languages. Users with no city set are always included.
     */
    private function findInterestedUsers(
        Property $property,
        string $cityEn = '',
        string $cityAr = '',
        string $cityKu = ''
    ) {
        try {
            if ($cityEn === '' && $cityAr === '' && $cityKu === '') {
                $addressDetails = $this->asArray($property->address_details);

                $cityEn = $addressDetails['city']['en'] ?? '';
                $cityAr = $addressDetails['city']['ar'] ?? '';
                $cityKu = $addressDetails['city']['ku'] ?? '';
            }

            $cityEn = strtolower(trim($cityEn));
            $cityAr = trim($cityAr);
            $cityKu = trim($cityKu);

            return $this->notifiableUsers()
                ->whereRaw('JSON_LENGTH(device_tokens) > 0')
                ->where(function ($q) use ($cityEn, $cityAr, $cityKu) {
                    $q->whereNull('place')
                        ->orWhereRaw("TRIM(COALESCE(place, '')) = ''");

                    if ($cityEn !== '') {
                        $q->orWhereRaw('LOWER(TRIM(place)) LIKE ?', ['%' . $cityEn . '%'])
                            ->orWhereRaw(
                                "LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(search_preferences, '\$.location.city')))) LIKE ?",
                                ['%' . $cityEn . '%']
                            );
                    }
                    if ($cityAr !== '') {
                        $q->orWhereRaw('TRIM(place) LIKE ?', ['%' . $cityAr . '%']);
                    }
                    if ($cityKu !== '') {
                        $q->orWhereRaw('TRIM(place) LIKE ?', ['%' . $cityKu . '%']);
                    }
                })
                ->limit(200)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error finding interested users: ' . $e->getMessage());
            return collect();
        }
    }

    private function findInterestedAgents(string $cityEn, string $cityAr, string $cityKu)
    {
        return $this->findInterestedByCity(Agent::query(), $cityEn, $cityAr, $cityKu, 'agents');
    }

    private function findInterestedOffices(string $cityEn, string $cityAr, string $cityKu)
    {
        return $this->findInterestedByCity(RealEstateOffice::query(), $cityEn, $cityAr, $cityKu, 'offices');
    }

    /**
     * Shared city-matching query for agents and offices — the original had
     * two near-identical copies of this.
     */
    private function findInterestedByCity($query, string $cityEn, string $cityAr, string $cityKu, string $label)
    {
        try {
            return $query
                ->whereRaw('JSON_LENGTH(device_tokens) > 0')
                ->where(function ($q) use ($cityEn, $cityAr, $cityKu) {
                    $q->whereNull('city')
                        ->orWhereRaw("TRIM(COALESCE(city, '')) = ''");

                    if ($cityEn !== '') {
                        $q->orWhereRaw('LOWER(TRIM(city)) LIKE ?', ['%' . $cityEn . '%']);
                    }
                    if ($cityAr !== '') {
                        $q->orWhereRaw('TRIM(city) LIKE ?', ['%' . $cityAr . '%']);
                    }
                    if ($cityKu !== '') {
                        $q->orWhereRaw('TRIM(city) LIKE ?', ['%' . $cityKu . '%']);
                    }
                })
                ->limit(200)
                ->get();
        } catch (\Exception $e) {
            Log::error("Error finding interested {$label}: " . $e->getMessage());
            return collect();
        }
    }

    private function getRecipientColumn($user): string
    {
        return match (true) {
            $user instanceof User             => 'user_id',
            $user instanceof Agent            => 'agent_id',
            $user instanceof RealEstateOffice => 'office_id',
            default                           => 'user_id',
        };
    }

    private function getOwnerColumn($owner): string
    {
        return $this->getRecipientColumn($owner);
    }

    private function loadOwner($property)
    {
        if (!$property->owner_type || !$property->owner_id) {
            return null;
        }

        $ownerClass = $property->owner_type;

        // Guard against an arbitrary class name in the column.
        $allowed = [User::class, Agent::class, RealEstateOffice::class];
        if (!in_array(ltrim($ownerClass, '\\'), $allowed, true)) {
            Log::warning('Unexpected owner_type on property', [
                'property_id' => $property->id,
                'owner_type'  => $ownerClass,
            ]);
            return null;
        }

        return $ownerClass::find($property->owner_id);
    }

    private function getPropertyName($property): string
    {
        $name = $this->asArray($property->name);

        if ($name) {
            return $name['en'] ?? $name['ar'] ?? $name['ku'] ?? 'Property';
        }

        return is_string($property->name) && $property->name !== '' ? $property->name : 'Property';
    }

    /**
     * Normalise a column that may be a JSON string or an already-cast array.
     */
    private function asArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return json_decode($value, true) ?: [];
        }

        return [];
    }

    private function resolveImageUrl(Request $request): ?string
    {
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $path = $request->file('image')->store('notifications', 'public');
            return rtrim(config('app.url'), '/') . Storage::url($path);
        }

        if ($request->filled('image_url')) {
            $url = $request->image_url;
            return str_starts_with($url, 'http')
                ? $url
                : rtrim(config('app.url'), '/') . '/' . ltrim($url, '/');
        }

        return null;
    }

    /**
     * Pick the recipient's language, falling back to English, then to
     * whatever the admin actually filled in.
     */
    private function resolveLanguage(?string $userLang, array $titles, array $messages): array
    {
        if (empty($titles) || empty($messages)) {
            return ['title' => '', 'message' => ''];
        }

        $lang = strtolower(trim($userLang ?? 'en'));

        if (isset($titles[$lang], $messages[$lang])) {
            return ['title' => $titles[$lang], 'message' => $messages[$lang]];
        }

        if (isset($titles['en'], $messages['en'])) {
            return ['title' => $titles['en'], 'message' => $messages['en']];
        }

        $first = array_key_first($titles);

        return [
            'title'   => $titles[$first],
            'message' => $messages[$first] ?? '',
        ];
    }
}