<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Helper\ApiResponse;
use App\Models\User;
use App\Models\Appointment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UserController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|min:3|max:50|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'password' => ['required', 'confirmed', PasswordRule::defaults()],
                'phone' => 'nullable|string|min:10|max:15',
                'place' => 'nullable|string|max:100',
                'lat' => 'nullable|numeric|between:-90,90',
                'lng' => 'nullable|numeric|between:-180,180',
                'about_me' => 'nullable|string|max:1000',
                'photo_image' => 'nullable|url',
                'language' => 'in:en,ar,ku',
                'device_name' => 'nullable|string|max:255',
                'device_token' => 'nullable|string|max:500',
                'search_preferences' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Registration validation failed', $validator->errors(), 400);
            }

            DB::beginTransaction();

            $userData = $request->only([
                'username',
                'email',
                'phone',
                'place',
                'lat',
                'lng',
                'about_me',
                'photo_image'
            ]);

            $userData['id'] = (string) Str::uuid();
            $userData['password'] = Hash::make(value: $request->password);
            $userData['language'] = $request->get('language', 'en');

            // Handle search preferences
            $userData['search_preferences'] = $request->has('search_preferences')
                ? json_encode($request->search_preferences)
                : json_encode($this->getDefaultSearchPreferences());

            // Handle device token during registration - ENCODE AS JSON
            $deviceTokens = [];
            if ($request->has('device_token') && $request->has('device_name')) {
                $deviceTokens[] = [
                    'device' => $request->device_name,
                    'tokenId' => $request->device_token,
                    'created_at' => now()->toISOString(),
                    'last_used' => now()->toISOString()
                ];
            }
            $userData['device_tokens'] = json_encode($deviceTokens); // JSON ENCODE HERE

            $userData['created_at'] = now();
            $userData['updated_at'] = now();

            DB::table('users')->insert($userData);
            $user = User::find($userData['id']);

            DB::commit();

            $token = $user->createToken('auth-token')->plainTextToken;

            // Send welcome notification
            if (class_exists('App\Http\Controllers\NotificationController')) {
                app(NotificationController::class)->sendWelcomeNotification($user->id);
            }

            return ApiResponse::success('User registered successfully', [
                'user' => $this->transformUserData($user),
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User registration error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Registration failed', $e->getMessage(), 500);
        }
    }
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'confirmed', PasswordRule::defaults()],
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Password change validation failed',
                    $validator->errors(),
                    400
                );
            }

            $user = $request->user(); // Get authenticated user

            // Check current password
            if (!Hash::check($request->current_password, $user->password)) {
                return ApiResponse::error(
                    'Current password is incorrect',
                    null,
                    400
                );
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->updated_at = now();
            $user->save();

            return ApiResponse::success(
                'Password changed successfully',
                null,
                200
            );
        } catch (\Exception $e) {
            Log::error('Password change error', ['message' => $e->getMessage()]);
            return ApiResponse::error(
                'Failed to change password',
                $e->getMessage(),
                500
            );
        }
    }



    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'login' => 'required|string', // Can be username or email
                'password' => 'required|string',
                'device_name' => 'nullable|string|max:255',
                'device_token' => 'nullable|string|max:500' // FCM token
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Login validation failed', $validator->errors(), 400);
            }

            $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $user = User::where($loginField, $request->login)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return ApiResponse::error('Invalid credentials', ['login' => 'The provided credentials are incorrect'], 401);
            }

            $deviceName = $request->get('device_name', 'Unknown Device');
            $deviceToken = $request->get('device_token'); // This is the FCM token

            // Handle device token management if both device_name and device_token exist
            if ($deviceToken && $deviceName) {
                $this->updateUserDeviceToken($user, $deviceName, $deviceToken);
            }

            $token = $user->createToken('auth-token - ' . $deviceName)->plainTextToken;

            $user->update(['last_login_at' => now()]);

            // Send login notification (optional for security)
            if (class_exists('App\Http\Controllers\NotificationController')) {
                app(NotificationController::class)->sendLoginNotification($user->id, $deviceName);
            }

            return ApiResponse::success('Login successful', [
                'user' => $this->transformUserData($user->fresh()),
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            Log::error('User login error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Login failed', $e->getMessage(), 500);
        }
    }


    /**
     * Update or insert user device token in the device_tokens array
     */

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $user = Auth::user();

            if ($request->boolean('logout_all_devices')) {
                $user->tokens()->delete();
            } else {
                $request->user()->currentAccessToken()->delete();
            }

            return ApiResponse::success('Logged out successfully', null, 200);
        } catch (\Exception $e) {
            Log::error('User logout error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Logout failed', $e->getMessage(), 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        try {
            $user = Auth::user();
            $deviceName = $request->get('device_name', 'Unknown Device');

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Create new token
            $token = $user->createToken('auth-token - ' . $deviceName)->plainTextToken;

            return ApiResponse::success('Token refreshed successfully', [
                'user' => $this->transformUserData($user),
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            Log::error('Token refresh error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Token refresh failed', $e->getMessage(), 500);
        }
    }

    /**
     * Update user location and other profile data (PATCH method)
     */
    public function updateLocation(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'lat' => 'sometimes|numeric|between:-90,90',
                'lng' => 'sometimes|numeric|between:-180,180',
                'place' => 'sometimes|nullable|string|max:100',
                'username' => 'sometimes|string|min:3|max:50|unique:users,username,' . $user->id,
                'phone' => 'sometimes|nullable|string|min:10|max:15',
                'about_me' => 'sometimes|nullable|string|max:1000',
                'photo_image' => 'sometimes|nullable|url',
                'language' => 'sometimes|in:en,ar,ku',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'search_preferences' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            DB::beginTransaction();

            // Get all allowed fields from the request
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
                // Only update fields that are present in the request
                if ($request->has($field)) {
                    $updateData[$field] = $request->input($field);
                }
            }

            Log::info('Update data prepared', [
                'user_id' => $user->id,
                'update_data' => $updateData
            ]);

            // Update user with the provided data
            if (!empty($updateData)) {
                $user->update($updateData);

                // Send nearby property notifications if location changed
                if (isset($updateData['lat']) && isset($updateData['lng'])) {
                    app(NotificationController::class)->sendNearbyPropertyNotifications(
                        $user->id,
                        $updateData['lat'],
                        $updateData['lng']
                    );
                }
            }

            DB::commit();

            Log::info('User profile updated successfully', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($updateData)
            ]);

            return ApiResponse::success('Profile updated successfully', [
                'user' => $this->transformUserData($user->fresh())
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Profile update error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to update profile', $e->getMessage(), 500);
        }
    }

    // Add this method to your UserController class
    private function transformUserData($user)
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

    /**
     * Get user profile with appointments and notifications
     */
    public function getProfile(Request $request)
    {
        try {
            $user = Auth::user();

            // Get user's appointments - using Eloquent relationships instead of raw joins
            $appointments = Appointment::with(['agent', 'office', 'property'])
                ->where('user_id', $user->id)
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get();

            // Get user's notifications (only if the table exists)
            $notifications = collect(); // Empty collection as fallback
            try {
                $notifications = DB::table('notifications')
                    ->where('user_id', $user->id)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->orderBy('sent_at', 'desc')
                    ->get();
            } catch (\Exception $e) {
                // Notifications table doesn't exist or has issues
                Log::warning('Notifications query failed: ' . $e->getMessage());
            }

            // Transform appointments data using relationships
            $transformedAppointments = $appointments->map(function ($appointment) {
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
                    // Using relationships instead of direct column access
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
            });

            // Transform notifications data
            $transformedNotifications = $notifications->map(function ($notification) {
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
            });

            return ApiResponse::success('Profile retrieved successfully', [
                'user' => $this->transformUserData($user),
                'appointments' => $transformedAppointments,
                'notifications' => $transformedNotifications,
                'appointments_count' => $appointments->count(),
                'unread_notifications_count' => $notifications->where('is_read', false)->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get profile error', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error('Failed to get profile', $e->getMessage(), 500);
        }
    }

    public function markNotificationRead(Request $request, $notificationId)
    {
        try {
            $user = Auth::user();

            $notification = DB::table('notifications')
                ->where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return ApiResponse::error('Notification not found', null, 404);
            }

            if ($notification->is_read) {
                return ApiResponse::success('Notification already marked as read', null, 200);
            }

            DB::table('notifications')
                ->where('id', $notificationId)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                    'updated_at' => now()
                ]);

            return ApiResponse::success('Notification marked as read', null, 200);
        } catch (\Exception $e) {
            Log::error('Mark notification read error', [
                'message' => $e->getMessage(),
                'notification_id' => $notificationId,
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to mark notification as read', $e->getMessage(), 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(Request $request)
    {
        try {
            $user = Auth::user();

            $updatedCount = DB::table('notifications')
                ->where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                    'updated_at' => now()
                ]);

            return ApiResponse::success('All notifications marked as read', [
                'updated_count' => $updatedCount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Mark all notifications read error', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to mark all notifications as read', $e->getMessage(), 500);
        }
    }

    /**
     * Get user's notifications
     */
    public function getNotifications(Request $request)
    {
        try {
            $user = Auth::user();

            $isRead = $request->get('is_read'); // true, false, or null for all
            $type = $request->get('type'); // property, appointment, system, promotion, alert
            $priority = $request->get('priority'); // low, medium, high, urgent
            $limit = $request->get('limit', 20);
            $offset = $request->get('offset', 0);

            $query = DB::table('notifications')
                ->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                });

            // Apply filters
            if ($isRead !== null) {
                $query->where('is_read', filter_var($isRead, FILTER_VALIDATE_BOOLEAN));
            }

            if ($type) {
                $query->where('type', $type);
            }

            if ($priority) {
                $query->where('priority', $priority);
            }

            $notifications = $query
                ->orderBy('sent_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();

            $transformedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'data' => $notification->data ? json_decode($notification->data, true) : null,
                    'action_url' => $notification->action_url,
                    'action_text' => $notification->action_text,
                    'is_read' => (bool) $notification->is_read,
                    'read_at' => $notification->read_at,
                    'sent_at' => $notification->sent_at,
                    'expires_at' => $notification->expires_at,
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                ];
            });

            $totalCount = DB::table('notifications')
                ->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->count();

            $unreadCount = DB::table('notifications')
                ->where('user_id', $user->id)
                ->where('is_read', false)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->count();

            return ApiResponse::success('Notifications retrieved successfully', [
                'notifications' => $transformedNotifications,
                'total_count' => $totalCount,
                'unread_count' => $unreadCount,
                'current_count' => $notifications->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get notifications error', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to get notifications', $e->getMessage(), 500);
        }
    }

    /**
     * Get user's appointments with improved filtering and pagination
     */
    public function getAppointments(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'status' => 'nullable|in:pending,confirmed,completed,cancelled',
                'type' => 'nullable|in:viewing,consultation,signing,inspection',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'limit' => 'nullable|integer|min:1|max:100',
                'offset' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            $status = $request->get('status');
            $type = $request->get('type');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $limit = $request->get('limit', 20);
            $offset = $request->get('offset', 0);

            $query = DB::table('appointments')
                ->where('appointments.user_id', $user->id)
                ->select('appointments.*');

            // Apply filters
            if ($status) {
                $query->where('appointments.status', $status);
            }

            if ($type) {
                $query->where('appointments.type', $type);
            }

            if ($dateFrom) {
                $query->whereDate('appointments.appointment_date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('appointments.appointment_date', '<=', $dateTo);
            }

            $appointments = $query
                ->orderBy('appointments.appointment_date', 'desc')
                ->orderBy('appointments.appointment_time', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();

            $transformedAppointments = $appointments->map(function ($appointment) {
                return $this->transformAppointmentData($appointment);
            });

            // Get total count for pagination
            $totalQuery = DB::table('appointments')->where('user_id', $user->id);
            if ($status) $totalQuery->where('status', $status);
            if ($type) $totalQuery->where('type', $type);
            if ($dateFrom) $totalQuery->whereDate('appointment_date', '>=', $dateFrom);
            if ($dateTo) $totalQuery->whereDate('appointment_date', '<=', $dateTo);
            $totalCount = $totalQuery->count();

            return ApiResponse::success('Appointments retrieved successfully', [
                'appointments' => $transformedAppointments,
                'total_count' => $totalCount,
                'current_count' => $appointments->count(),
                'has_more' => ($offset + $appointments->count()) < $totalCount,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get appointments error', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to get appointments', $e->getMessage(), 500);
        }
    }

    /**
     * Updated transformAppointmentData method without related table joins
     */
    private function transformAppointmentData($appointment)
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
            // For now, these will be null until you create the related tables
            'agent' => $appointment->agent_id ? [
                'id' => $appointment->agent_id,
                'name' => 'Agent', // Placeholder
                'phone' => null,
                'email' => null,
            ] : null,
            'office' => $appointment->office_id ? [
                'id' => $appointment->office_id,
                'name' => 'Office', // Placeholder
                'address' => null,
                'phone' => null,
            ] : null,
            'property' => $appointment->property_id ? [
                'id' => $appointment->property_id,
                'title' => 'Property', // Placeholder
                'address' => null,
                'price' => null,
            ] : null,
            'created_at' => $appointment->created_at,
            'updated_at' => $appointment->updated_at,
        ];
    }
    /**
     * Get single appointment details
     */
    public function getAppointment(Request $request, $appointmentId)
    {
        try {
            $user = Auth::user();

            if (!$appointmentId) {
                return ApiResponse::error('Appointment ID is required', null, 400);
            }

            $appointment = DB::table('appointments')
                ->leftJoin('agents', 'appointments.agent_id', '=', 'agents.id')
                ->leftJoin('real_estate_offices', 'appointments.office_id', '=', 'real_estate_offices.id')
                ->leftJoin('properties', 'appointments.property_id', '=', 'properties.id')
                ->where('appointments.id', $appointmentId)
                ->where('appointments.user_id', $user->id)
                ->select([
                    'appointments.*',
                    'agents.name as agent_name',
                    'agents.phone as agent_phone',
                    'agents.email as agent_email',
                    'real_estate_offices.name as office_name',
                    'real_estate_offices.address as office_address',
                    'real_estate_offices.phone as office_phone',
                    'properties.title as property_title',
                    'properties.address as property_address',
                    'properties.price as property_price'
                ])
                ->first();

            if (!$appointment) {
                return ApiResponse::error('Appointment not found', null, 404);
            }

            return ApiResponse::success('Appointment retrieved successfully', [
                'appointment' => $this->transformAppointmentData($appointment)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get appointment error', [
                'message' => $e->getMessage(),
                'appointment_id' => $appointmentId,
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to get appointment', $e->getMessage(), 500);
        }
    }

    /**
     * Get appointments by user ID, sorted by created_at
     */
    public function getAppointmentsByUser(Request $request, $userId)
    {
        try {
            $authenticatedUser = Auth::user();

            if (!$userId) {
                return ApiResponse::error('User ID is required', null, 400);
            }

            // Validate that the user exists
            $targetUser = DB::table('users')->where('id', $userId)->first();
            if (!$targetUser) {
                return ApiResponse::error('User not found', null, 404);
            }

            $appointments = DB::table('appointments')
                ->where('appointments.user_id', $userId)
                ->orderBy('appointments.created_at', 'desc')
                ->get();

            $transformedAppointments = $appointments->map(function ($appointment) {
                return $this->transformAppointmentData($appointment);
            });

            return ApiResponse::success('User appointments retrieved successfully', [
                'appointments' => $transformedAppointments,
                'user_id' => $userId,
                'total_count' => $appointments->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get user appointments error', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'requested_by' => Auth::id()
            ]);
            return ApiResponse::error('Failed to get user appointments', $e->getMessage(), 500);
        }
    }

    /**
     * Cancel appointment (user can only cancel their own appointments)
     */
    public function cancelAppointment(Request $request, $appointmentId)
    {
        try {
            $user = Auth::user();

            if (!$appointmentId) {
                return ApiResponse::error('Appointment ID is required', null, 400);
            }

            DB::beginTransaction();

            $appointment = DB::table('appointments')
                ->where('id', $appointmentId)
                ->where('user_id', $user->id)
                ->first();

            if (!$appointment) {
                DB::rollback();
                return ApiResponse::error('Appointment not found', null, 404);
            }

            if ($appointment->status === 'cancelled') {
                DB::rollback();
                return ApiResponse::error('Appointment is already cancelled', null, 400);
            }

            if ($appointment->status === 'completed') {
                DB::rollback();
                return ApiResponse::error('Cannot cancel a completed appointment', null, 400);
            }

            DB::table('appointments')
                ->where('id', $appointmentId)
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'updated_at' => now()
                ]);

            // Send appointment status notification
            app(NotificationController::class)->sendAppointmentStatusNotification($appointmentId, 'cancelled');

            DB::commit();

            return ApiResponse::success('Appointment cancelled successfully', null, 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Cancel appointment error', [
                'message' => $e->getMessage(),
                'appointment_id' => $appointmentId,
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to cancel appointment', $e->getMessage(), 500);
        }
    }

    public function createNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|uuid|exists:users,id',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'required|in:property,appointment,system,promotion,alert',
                'priority' => 'required|in:low,medium,high,urgent',
                'data' => 'nullable|array',
                'action_url' => 'nullable|string|max:500',
                'action_text' => 'nullable|string|max:100',
                'is_read' => 'boolean',
                'expires_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            DB::beginTransaction();

            $notificationData = [
                'id' => (string) Str::uuid(),
                'user_id' => $request->user_id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'priority' => $request->priority,
                'data' => $request->data ? json_encode($request->data) : null,
                'action_url' => $request->action_url,
                'action_text' => $request->action_text,
                'is_read' => $request->get('is_read', false),
                'sent_at' => now(),
                'expires_at' => $request->expires_at,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('notifications')->insert($notificationData);

            DB::commit();

            return ApiResponse::success('Notification created successfully', [
                'notification' => $notificationData
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Create notification error', [
                'message' => $e->getMessage(),
                'user_id' => $request->get('user_id')
            ]);
            return ApiResponse::error('Failed to create notification', $e->getMessage(), 500);
        }
    }

    /**
     * Create multiple notifications
     */
    public function createBulkNotifications(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'notifications' => 'required|array|max:50',
                'notifications.*.user_id' => 'required|uuid|exists:users,id',
                'notifications.*.title' => 'required|string|max:255',
                'notifications.*.message' => 'required|string',
                'notifications.*.type' => 'required|in:property,appointment,system,promotion,alert',
                'notifications.*.priority' => 'required|in:low,medium,high,urgent',
                'notifications.*.data' => 'nullable|array',
                'notifications.*.action_url' => 'nullable|string|max:500',
                'notifications.*.action_text' => 'nullable|string|max:100',
                'notifications.*.is_read' => 'boolean',
                'notifications.*.expires_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            DB::beginTransaction();

            $insertData = [];
            foreach ($request->notifications as $notification) {
                $insertData[] = [
                    'id' => (string) Str::uuid(),
                    'user_id' => $notification['user_id'],
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                    'type' => $notification['type'],
                    'priority' => $notification['priority'],
                    'data' => isset($notification['data']) ? json_encode($notification['data']) : null,
                    'action_url' => $notification['action_url'] ?? null,
                    'action_text' => $notification['action_text'] ?? null,
                    'is_read' => $notification['is_read'] ?? false,
                    'sent_at' => now(),
                    'expires_at' => $notification['expires_at'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('notifications')->insert($insertData);

            DB::commit();

            return ApiResponse::success('Notifications created successfully', [
                'created_count' => count($insertData),
                'notifications' => $insertData
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Create bulk notifications error', [
                'message' => $e->getMessage(),
                'count' => count($request->get('notifications', []))
            ]);
            return ApiResponse::error('Failed to create notifications', $e->getMessage(), 500);
        }
    }
    public function createAppointment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|uuid|exists:users,id',
                'agent_id' => 'nullable|uuid',
                'office_id' => 'nullable|uuid',
                'property_id' => 'nullable|uuid',
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required|date_format:H:i:s',
                'status' => 'in:pending,confirmed,completed,cancelled',
                'type' => 'required|in:viewing,consultation,signing,inspection',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'client_name' => 'required|string|max:255',
                'client_phone' => 'nullable|string|max:20',
                'client_email' => 'nullable|email|max:255',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            DB::beginTransaction();

            $appointmentData = [
                'id' => (string) Str::uuid(),
                'user_id' => $request->user_id,
                'agent_id' => $request->agent_id,
                'office_id' => $request->office_id,
                'property_id' => $request->property_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'status' => $request->get('status', 'pending'),
                'type' => $request->type,
                'location' => $request->location,
                'notes' => $request->notes,
                'client_name' => $request->client_name,
                'client_phone' => $request->client_phone,
                'client_email' => $request->client_email,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('appointments')->insert($appointmentData);

            // Send appointment notifications
            app(NotificationController::class)->sendAppointmentNotifications($appointmentData['id']);

            DB::commit();

            return ApiResponse::success('Appointment created successfully', [
                'appointment' => $appointmentData
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Create appointment error', [
                'message' => $e->getMessage(),
                'user_id' => $request->get('user_id')
            ]);
            return ApiResponse::error('Failed to create appointment', $e->getMessage(), 500);
        }
    }

    /**
     * Create multiple appointments
     */
    public function createBulkAppointments(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'appointments' => 'required|array|max:20',
                'appointments.*.user_id' => 'required|uuid|exists:users,id',
                'appointments.*.appointment_date' => 'required|date',
                'appointments.*.appointment_time' => 'required|date_format:H:i:s',
                'appointments.*.type' => 'required|in:viewing,consultation,signing,inspection',
                'appointments.*.client_name' => 'required|string|max:255',
                'appointments.*.status' => 'in:pending,confirmed,completed,cancelled',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            DB::beginTransaction();

            $insertData = [];
            foreach ($request->appointments as $appointment) {
                $insertData[] = [
                    'id' => (string) Str::uuid(),
                    'user_id' => $appointment['user_id'],
                    'agent_id' => $appointment['agent_id'] ?? null,
                    'office_id' => $appointment['office_id'] ?? null,
                    'property_id' => $appointment['property_id'] ?? null,
                    'appointment_date' => $appointment['appointment_date'],
                    'appointment_time' => $appointment['appointment_time'],
                    'status' => $appointment['status'] ?? 'pending',
                    'type' => $appointment['type'],
                    'location' => $appointment['location'] ?? null,
                    'notes' => $appointment['notes'] ?? null,
                    'client_name' => $appointment['client_name'],
                    'client_phone' => $appointment['client_phone'] ?? null,
                    'client_email' => $appointment['client_email'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('appointments')->insert($insertData);

            DB::commit();

            return ApiResponse::success('Appointments created successfully', [
                'created_count' => count($insertData),
                'appointments' => $insertData
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Create bulk appointments error', [
                'message' => $e->getMessage(),
                'count' => count($request->get('appointments', []))
            ]);
            return ApiResponse::error('Failed to create appointments', $e->getMessage(), 500);
        }
    }

    /**
     * Delete notification (user can only delete their own notifications)
     */
    public function deleteNotification(Request $request, $notificationId)
    {
        try {
            $user = Auth::user();

            if (!$notificationId) {
                return ApiResponse::error('Notification ID is required', null, 400);
            }

            DB::beginTransaction();

            $notification = DB::table('notifications')
                ->where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                DB::rollback();
                return ApiResponse::error('Notification not found', null, 404);
            }

            $deleted = DB::table('notifications')
                ->where('id', $notificationId)
                ->where('user_id', $user->id)
                ->delete();

            if (!$deleted) {
                DB::rollback();
                return ApiResponse::error('Failed to delete notification', null, 500);
            }

            DB::commit();

            return ApiResponse::success('Notification deleted successfully', null, 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Delete notification error', [
                'message' => $e->getMessage(),
                'notification_id' => $notificationId,
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to delete notification', $e->getMessage(), 500);
        }
    }

    /**
     * Delete appointment (user can only delete their own appointments)
     */
    public function deleteAppointment(Request $request, $appointmentId)
    {
        try {
            $user = Auth::user();

            if (!$appointmentId) {
                return ApiResponse::error('Appointment ID is required', null, 400);
            }

            DB::beginTransaction();

            $appointment = DB::table('appointments')
                ->where('id', $appointmentId)
                ->where('user_id', $user->id)
                ->first();

            if (!$appointment) {
                DB::rollback();
                return ApiResponse::error('Appointment not found', null, 404);
            }

            // Optional: Prevent deletion of certain appointment statuses
            if ($appointment->status === 'completed') {
                DB::rollback();
                return ApiResponse::error('Cannot delete a completed appointment', null, 400);
            }

            $deleted = DB::table('appointments')
                ->where('id', $appointmentId)
                ->where('user_id', $user->id)
                ->delete();

            if (!$deleted) {
                DB::rollback();
                return ApiResponse::error('Failed to delete appointment', null, 500);
            }

            DB::commit();

            return ApiResponse::success('Appointment deleted successfully', null, 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Delete appointment error', [
                'message' => $e->getMessage(),
                'appointment_id' => $appointmentId,
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to delete appointment', $e->getMessage(), 500);
        }
    }

    /**
     * Clear all notifications for the authenticated user
     */
    public function clearAllNotifications(Request $request)
    {
        try {
            $user = Auth::user();

            DB::beginTransaction();

            $deletedCount = DB::table('notifications')
                ->where('user_id', $user->id)
                ->delete();

            DB::commit();

            return ApiResponse::success('All notifications cleared successfully', [
                'deleted_count' => $deletedCount
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Clear all notifications error', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to clear all notifications', $e->getMessage(), 500);
        }
    }
    /**
     * Reschedule appointment (user can only reschedule their own appointments)
     */
    public function rescheduleAppointment(Request $request, $appointmentId)
    {
        try {
            $user = Auth::user();

            if (!$appointmentId) {
                return ApiResponse::error('Appointment ID is required', null, 400);
            }

            $validator = Validator::make($request->all(), [
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required|date_format:H:i:s',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            DB::beginTransaction();

            $appointment = DB::table('appointments')
                ->where('id', $appointmentId)
                ->where('user_id', $user->id)
                ->first();

            if (!$appointment) {
                DB::rollback();
                return ApiResponse::error('Appointment not found', null, 404);
            }

            if ($appointment->status === 'completed') {
                DB::rollback();
                return ApiResponse::error('Cannot reschedule a completed appointment', null, 400);
            }

            if ($appointment->status === 'cancelled') {
                DB::rollback();
                return ApiResponse::error('Cannot reschedule a cancelled appointment', null, 400);
            }

            // Update appointment with new date/time and reset status to pending
            $updated = DB::table('appointments')
                ->where('id', $appointmentId)
                ->update([
                    'appointment_date' => $request->appointment_date,
                    'appointment_time' => $request->appointment_time,
                    'status' => 'pending', // Automatically set to pending
                    'confirmed_at' => null, // Clear confirmation timestamp
                    'updated_at' => now()
                ]);

            if (!$updated) {
                DB::rollback();
                return ApiResponse::error('Failed to reschedule appointment', null, 500);
            }

            // Send appointment status notification for rescheduling
            app(NotificationController::class)->sendAppointmentStatusNotification($appointmentId, 'pending');

            // Get the updated appointment data
            $updatedAppointment = DB::table('appointments')
                ->where('id', $appointmentId)
                ->first();

            DB::commit();

            Log::info('Appointment rescheduled successfully', [
                'appointment_id' => $appointmentId,
                'user_id' => $user->id,
                'new_date' => $request->appointment_date,
                'new_time' => $request->appointment_time,
                'old_status' => $appointment->status,
                'new_status' => 'pending'
            ]);

            return ApiResponse::success('Appointment rescheduled successfully', [
                'appointment' => $this->transformAppointmentData($updatedAppointment)
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Reschedule appointment error', [
                'message' => $e->getMessage(),
                'appointment_id' => $appointmentId,
                'user_id' => Auth::id()
            ]);
            return ApiResponse::error('Failed to reschedule appointment', $e->getMessage(), 500);
        }
    }
    public function getSearchPreferences(Request $request)
    {
        try {
            $user = Auth::user();

            // Get user preferences or return default
            $searchPreferences = $user->search_preferences ?? $this->getDefaultSearchPreferences();

            return ApiResponse::success('Search preferences retrieved successfully', [
                'search_preferences' => $searchPreferences
            ], 200);
        } catch (\Exception $e) {
            Log::error('Search preferences retrieval error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Failed to retrieve search preferences', null, 500);
        }
    }


    /**
     * Update user's search preferences
     */
    public function updateSearchPreferences(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'search_preferences' => 'required|array',
                'search_preferences.filters' => 'required|array',
                'search_preferences.filters.price_enabled' => 'required|boolean',
                'search_preferences.filters.min_price' => 'nullable|numeric|min:0',
                'search_preferences.filters.max_price' => 'nullable|numeric|min:0',
                'search_preferences.filters.location_enabled' => 'required|boolean',
                'search_preferences.filters.location_radius' => 'nullable|numeric|min:1|max:100',
                'search_preferences.filters.property_types' => 'nullable|array',
                'search_preferences.filters.property_types.*' => 'string|max:50',
                'search_preferences.filters.min_bedrooms' => 'nullable|integer|min:0|max:10',
                'search_preferences.filters.max_bedrooms' => 'nullable|integer|min:0|max:10',

                'search_preferences.sorting' => 'required|array',
                'search_preferences.sorting.price_enabled' => 'required|boolean',
                'search_preferences.sorting.price_order' => 'nullable|in:low_to_high,high_to_low',
                'search_preferences.sorting.popularity_enabled' => 'required|boolean',
                'search_preferences.sorting.date_enabled' => 'required|boolean',
                'search_preferences.sorting.date_order' => 'nullable|in:newest,oldest',
                'search_preferences.sorting.distance_enabled' => 'required|boolean',

                'search_preferences.behavior' => 'required|array',
                'search_preferences.behavior.enable_notifications' => 'required|boolean',
                'search_preferences.behavior.save_search_history' => 'required|boolean',
                'search_preferences.behavior.auto_suggestions' => 'required|boolean',
                'search_preferences.behavior.recent_searches' => 'required|boolean',
                'search_preferences.behavior.max_history_items' => 'nullable|integer|min:10|max:200',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            DB::beginTransaction();

            $searchPreferences = $request->input('search_preferences');

            // Validate price range
            if ($searchPreferences['filters']['price_enabled']) {
                $minPrice = $searchPreferences['filters']['min_price'] ?? 0;
                $maxPrice = $searchPreferences['filters']['max_price'] ?? 0;

                if ($minPrice > 0 && $maxPrice > 0 && $minPrice >= $maxPrice) {
                    return ApiResponse::error('Validation failed', [
                        'min_price' => ['Minimum price must be less than maximum price']
                    ], 400);
                }
            }

            // Validate bedroom range
            $minBedrooms = $searchPreferences['filters']['min_bedrooms'] ?? null;
            $maxBedrooms = $searchPreferences['filters']['max_bedrooms'] ?? null;

            if ($minBedrooms !== null && $maxBedrooms !== null && $minBedrooms > $maxBedrooms) {
                return ApiResponse::error('Validation failed', [
                    'min_bedrooms' => ['Minimum bedrooms must be less than or equal to maximum bedrooms']
                ], 400);
            }

            Log::info('Updating search preferences', [
                'user_id' => $user->id,
                'preferences' => $searchPreferences
            ]);

            // Update user search preferences
            $user->update([
                'search_preferences' => $searchPreferences
            ]);

            DB::commit();

            Log::info('Search preferences updated successfully', [
                'user_id' => $user->id
            ]);

            return ApiResponse::success('Search preferences updated successfully', [
                'search_preferences' => $searchPreferences
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Search preferences update error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Failed to update search preferences', null, 500);
        }
    }

    /**
     * Reset search preferences to default
     */
    public function resetSearchPreferences(Request $request)
    {
        try {
            $user = Auth::user();

            DB::beginTransaction();

            $defaultPreferences = $this->getDefaultSearchPreferences();

            Log::info('Resetting search preferences to default', [
                'user_id' => $user->id
            ]);

            $user->update([
                'search_preferences' => $defaultPreferences
            ]);

            DB::commit();

            Log::info('Search preferences reset successfully', [
                'user_id' => $user->id
            ]);

            return ApiResponse::success('Search preferences reset to defaults', [
                'search_preferences' => $defaultPreferences
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Search preferences reset error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Failed to reset search preferences', null, 500);
        }
    }
    /**
     * Get search filters for property search
     */
    public function getSearchFilters(Request $request)
    {
        try {
            $user = Auth::user();
            $searchPreferences = $user->search_preferences ?? $this->getDefaultSearchPreferences();

            $filters = $searchPreferences['filters'] ?? [];
            $sorting = $searchPreferences['sorting'] ?? [];

            $searchFilters = [];

            // Price filters
            if (($filters['price_enabled'] ?? false) && ($filters['min_price'] || $filters['max_price'])) {
                if ($filters['min_price']) {
                    $searchFilters['min_price'] = $filters['min_price'];
                }
                if ($filters['max_price']) {
                    $searchFilters['max_price'] = $filters['max_price'];
                }
            }

            // Location filters
            if ($filters['location_enabled'] ?? false) {
                $searchFilters['location_radius'] = $filters['location_radius'] ?? 10;

                // Add user's location if available
                if ($user->lat && $user->lng) {
                    $searchFilters['user_lat'] = $user->lat;
                    $searchFilters['user_lng'] = $user->lng;
                }
            }

            // Property type filters
            if (!empty($filters['property_types'])) {
                $searchFilters['property_types'] = $filters['property_types'];
            }

            // Bedroom filters
            if (isset($filters['min_bedrooms'])) {
                $searchFilters['min_bedrooms'] = $filters['min_bedrooms'];
            }
            if (isset($filters['max_bedrooms'])) {
                $searchFilters['max_bedrooms'] = $filters['max_bedrooms'];
            }

            // Sorting criteria
            $sortCriteria = [];
            if ($sorting['price_enabled'] ?? false) {
                $sortCriteria[] = 'price_' . ($sorting['price_order'] ?? 'low_to_high');
            }
            if ($sorting['popularity_enabled'] ?? false) {
                $sortCriteria[] = 'popularity';
            }
            if ($sorting['date_enabled'] ?? false) {
                $sortCriteria[] = 'date_' . ($sorting['date_order'] ?? 'newest');
            }
            if ($sorting['distance_enabled'] ?? false && $user->lat && $user->lng) {
                $sortCriteria[] = 'distance';
            }

            $searchFilters['sort_by'] = empty($sortCriteria) ? ['relevance'] : $sortCriteria;

            return ApiResponse::success('Search filters retrieved', [
                'filters' => $searchFilters,
                'user_preferences' => $searchPreferences
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting search filters', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Failed to get search filters', null, 500);
        }
    }
    public function searchProperties(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'query' => 'nullable|string|max:255',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50',
                'lat' => 'nullable|numeric|between:-90,90',
                'lng' => 'nullable|numeric|between:-180,180',
                'override_preferences' => 'nullable|boolean',

                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'property_types' => 'nullable|array',
                'min_bedrooms' => 'nullable|integer|min:0',
                'max_bedrooms' => 'nullable|integer|min:0',
                'radius' => 'nullable|numeric|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            $query = $request->get('query', '');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);
            $overridePreferences = $request->get('override_preferences', false);

            // Get search filters
            $searchFilters = [];

            if ($overridePreferences) {
                // Use request parameters directly
                if ($request->has('min_price')) {
                    $searchFilters['min_price'] = $request->get('min_price');
                }
                if ($request->has('max_price')) {
                    $searchFilters['max_price'] = $request->get('max_price');
                }
                if ($request->has('property_types')) {
                    $searchFilters['property_types'] = $request->get('property_types');
                }
                if ($request->has('min_bedrooms')) {
                    $searchFilters['min_bedrooms'] = $request->get('min_bedrooms');
                }
                if ($request->has('max_bedrooms')) {
                    $searchFilters['max_bedrooms'] = $request->get('max_bedrooms');
                }
                if ($request->has('radius')) {
                    $searchFilters['location_radius'] = $request->get('radius');
                }

                // Use provided location or user's location
                $lat = $request->get('lat', $user->lat);
                $lng = $request->get('lng', $user->lng);

                if ($lat && $lng) {
                    $searchFilters['user_lat'] = $lat;
                    $searchFilters['user_lng'] = $lng;
                }
            } else {
                // Use user preferences
                $filtersResponse = $this->getSearchFilters($request);
                if ($filtersResponse->getStatusCode() === 200) {
                    $responseData = json_decode($filtersResponse->getContent(), true);
                    $searchFilters = $responseData['data']['filters'];
                }
            }

            // For now, return mock data since we don't have a properties table yet
            // In real implementation, you would query your properties table here
            $mockProperties = $this->getMockProperties($query, $searchFilters, $page, $perPage);

            Log::info('Property search performed', [
                'user_id' => $user->id,
                'query' => $query,
                'filters' => $searchFilters,
                'page' => $page,
                'per_page' => $perPage
            ]);

            return ApiResponse::success('Properties retrieved successfully', [
                'properties' => $mockProperties['data'],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $mockProperties['total'],
                    'total_pages' => ceil($mockProperties['total'] / $perPage),
                    'has_more' => ($page * $perPage) < $mockProperties['total']
                ],
                'search_info' => [
                    'query' => $query,
                    'filters_applied' => $searchFilters,
                    'override_preferences' => $overridePreferences
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Property search error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Failed to search properties', null, 500);
        }
    }
    public function getRecommendations(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'limit' => 'nullable|integer|min:1|max:20',
                'type' => 'nullable|in:recent,trending,nearby,similar'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            $limit = $request->get('limit', 10);
            $type = $request->get('type', 'recent');

            // Get user's search preferences
            $searchPreferences = $user->search_preferences ?? $this->getDefaultSearchPreferences();

            // Generate recommendations based on preferences
            $recommendations = $this->generateRecommendations($user, $searchPreferences, $type, $limit);

            Log::info('Recommendations generated', [
                'user_id' => $user->id,
                'type' => $type,
                'limit' => $limit
            ]);

            return ApiResponse::success('Recommendations retrieved successfully', [
                'recommendations' => $recommendations,
                'type' => $type,
                'based_on' => [
                    'user_location' => $user->place,
                    'search_preferences' => !empty($user->search_preferences),
                    'user_language' => $user->language
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Recommendations error', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Failed to get recommendations', null, 500);
        }
    }
    private function getMockProperties($query, $filters, $page, $perPage)
    {
        $mockData = [
            [
                'id' => 1,
                'title' => 'Luxury Villa in Erbil',
                'description' => 'Beautiful 4-bedroom villa with garden',
                'price' => 250000,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 350,
                'type' => 'Villa',
                'location' => 'Erbil Center',
                'lat' => 36.1911,
                'lng' => 44.0092,
                'images' => ['villa1.jpg', 'villa2.jpg'],
                'featured' => true,
                'created_at' => now()->subDays(5),
            ],
            [
                'id' => 2,
                'title' => 'Modern Apartment Downtown',
                'description' => '2-bedroom modern apartment in city center',
                'price' => 120000,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area' => 120,
                'type' => 'Apartment',
                'location' => 'Erbil Downtown',
                'lat' => 36.1833,
                'lng' => 44.0167,
                'images' => ['apt1.jpg', 'apt2.jpg'],
                'featured' => false,
                'created_at' => now()->subDays(2),
            ],
            [
                'id' => 3,
                'title' => 'Family House with Garden',
                'description' => '3-bedroom house perfect for families',
                'price' => 180000,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 200,
                'type' => 'House',
                'location' => 'Ankawa',
                'lat' => 36.2167,
                'lng' => 44.0333,
                'images' => ['house1.jpg', 'house2.jpg'],
                'featured' => true,
                'created_at' => now()->subDays(1),
            ]
        ];

        // Apply filters
        $filtered = collect($mockData);

        if (!empty($filters['min_price'])) {
            $filtered = $filtered->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $filtered = $filtered->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['property_types'])) {
            $filtered = $filtered->whereIn('type', $filters['property_types']);
        }

        if (!empty($filters['min_bedrooms'])) {
            $filtered = $filtered->where('bedrooms', '>=', $filters['min_bedrooms']);
        }

        if (!empty($filters['max_bedrooms'])) {
            $filtered = $filtered->where('bedrooms', '<=', $filters['max_bedrooms']);
        }

        // Apply search query
        if ($query) {
            $filtered = $filtered->filter(function ($property) use ($query) {
                return str_contains(strtolower($property['title']), strtolower($query)) ||
                    str_contains(strtolower($property['description']), strtolower($query)) ||
                    str_contains(strtolower($property['location']), strtolower($query));
            });
        }

        $total = $filtered->count();
        $paginatedData = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

        return [
            'data' => $paginatedData,
            'total' => $total
        ];
    }
    private function generateRecommendations($user, $preferences, $type, $limit)
    {
        // Mock recommendations based on user preferences
        $allProperties = $this->getMockProperties('', [], 1, 50)['data'];

        $recommendations = collect($allProperties)->take($limit)->map(function ($property) use ($type) {
            $property['recommendation_score'] = rand(70, 99);
            $property['recommendation_reason'] = $this->getRecommendationReason($type);
            return $property;
        });

        return $recommendations->toArray();
    }
    private function getRecommendationReason($type)
    {
        $reasons = [
            'recent' => 'Recently added properties',
            'trending' => 'Popular in your area',
            'nearby' => 'Close to your location',
            'similar' => 'Similar to your recent searches'
        ];

        return $reasons[$type] ?? 'Recommended for you';
    }
    /**
     * Get default search preferences structure
     */
    private function getDefaultSearchPreferences()
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
    /**
     * Update or insert device token for user
     */
    /**
     * Update or insert device token for user
     */
    private function updateUserDeviceToken(User $user, string $deviceName, string $fcmToken)
    {
        try {
            // Debug: Log what we're starting with
            Log::info('Starting device token update', [
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'fcm_token' => substr($fcmToken, 0, 20) . '...', // Log partial token for security
                'current_device_tokens' => $user->device_tokens
            ]);

            // Get current device tokens array (default to empty array if null)
            $deviceTokens = $user->device_tokens ?? [];

            // Check if device already exists
            $deviceExists = false;
            foreach ($deviceTokens as $index => $deviceData) {
                if (isset($deviceData['device_name']) && $deviceData['device_name'] === $deviceName) {
                    // Update existing device token
                    $deviceTokens[$index] = [
                        'device_name' => $deviceName,
                        'fcm_token' => $fcmToken,
                        'last_updated' => now()->format('Y-m-d H:i:s'), // Use standard format
                        'last_login' => now()->format('Y-m-d H:i:s')
                    ];
                    $deviceExists = true;
                    Log::info('Updated existing device token', ['index' => $index]);
                    break;
                }
            }

            // If device doesn't exist, add new device
            if (!$deviceExists) {
                $newDevice = [
                    'device_name' => $deviceName,
                    'fcm_token' => $fcmToken,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'last_updated' => now()->format('Y-m-d H:i:s'),
                    'last_login' => now()->format('Y-m-d H:i:s')
                ];

                $deviceTokens[] = $newDevice;
                Log::info('Added new device token', ['new_device' => $newDevice]);
            }

            // Debug: Log what we're about to save
            Log::info('About to save device tokens', [
                'user_id' => $user->id,
                'device_tokens_count' => count($deviceTokens),
                'device_tokens' => $deviceTokens
            ]);

            // Update user's device_tokens field
            $updateResult = $user->update(['device_tokens' => $deviceTokens]);

            // Debug: Check if update was successful
            Log::info('Update result', [
                'update_successful' => $updateResult,
                'user_device_tokens_after_update' => $user->fresh()->device_tokens
            ]);

            // Verify the data was actually saved
            $freshUser = $user->fresh();
            if (empty($freshUser->device_tokens)) {
                Log::error('Device tokens are empty after update!', [
                    'user_id' => $user->id,
                    'attempted_tokens' => $deviceTokens
                ]);
            }

            Log::info('Device token updated successfully', [
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'action' => $deviceExists ? 'updated' : 'created',
                'final_tokens_count' => count($freshUser->device_tokens ?? [])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user device token', [
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw exception here to prevent login failure
            // Just log the error and continue with login process
        }
    }

    // Also add this helper method to check device tokens
    public function getUserDeviceTokens($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return response()->json([
                'user_id' => $userId,
                'device_tokens' => $user->device_tokens,
                'device_tokens_count' => count($user->device_tokens ?? [])
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user device tokens', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch device tokens'], 500);
        }
    }
    // Add this method to UserController
    public function updateDeviceToken(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'device' => 'required|string|max:255',
                'token_id' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            $deviceTokens = $user->device_tokens ?? [];
            $tokenId = $request->token_id;
            $deviceName = $request->device;

            // Remove existing token for this device if exists
            $deviceTokens = array_filter($deviceTokens, function ($token) use ($deviceName) {
                return $token['device'] !== $deviceName;
            });

            // Add new token
            $deviceTokens[] = [
                'device' => $deviceName,
                'tokenId' => $tokenId,
                'created_at' => now()->toISOString(),
                'last_used' => now()->toISOString()
            ];

            // Keep only last 5 tokens per user
            if (count($deviceTokens) > 5) {
                $deviceTokens = array_slice($deviceTokens, -5);
            }

            $user->update(['device_tokens' => array_values($deviceTokens)]);

            return ApiResponse::success('Device token updated successfully', [
                'device_count' => count($deviceTokens)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Device token update error', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Failed to update device token', $e->getMessage(), 500);
        }
    } // Add these methods to UserController

    public function removeDeviceToken(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'device' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            $deviceTokens = $user->device_tokens ?? [];
            $deviceName = $request->device;

            // Remove token for this device
            $deviceTokens = array_filter($deviceTokens, function ($token) use ($deviceName) {
                return $token['device'] !== $deviceName;
            });

            $user->update(['device_tokens' => array_values($deviceTokens)]);

            return ApiResponse::success('Device token removed successfully', [
                'device_count' => count($deviceTokens)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Device token removal error', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Failed to remove device token', $e->getMessage(), 500);
        }
    }

    public function getDeviceTokens(Request $request)
    {
        try {
            $user = Auth::user();

            $deviceTokens = $user->device_tokens ?? [];

            // Remove sensitive token data, only return device info
            $safeTokens = array_map(function ($token) {
                return [
                    'device' => $token['device'],
                    'created_at' => $token['created_at'] ?? null,
                    'last_used' => $token['last_used'] ?? null
                ];
            }, $deviceTokens);

            return ApiResponse::success('Device tokens retrieved successfully', [
                'devices' => $safeTokens,
                'total_devices' => count($safeTokens)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get device tokens error', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Failed to get device tokens', $e->getMessage(), 500);
        }
    }
}