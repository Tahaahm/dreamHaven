<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments with optional filters.
     */
    public function index(Request $request)
    {
        try {
            $query = Appointment::with(['user', 'agent', 'office', 'property']);

            // Apply filters
            if ($request->has('office_id')) {
                $query->where('office_id', $request->office_id);
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('agent_id')) {
                $query->where('agent_id', $request->agent_id);
            }

            if ($request->has('property_id')) {
                $query->where('property_id', $request->property_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('date')) {
                $query->whereDate('appointment_date', $request->date);
            }

            // Date range filters
            if ($request->has('start_date')) {
                $query->whereDate('appointment_date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->whereDate('appointment_date', '<=', $request->end_date);
            }

            // Special filters
            if ($request->has('upcoming') && $request->upcoming) {
                $query->upcoming();
            }

            if ($request->has('today') && $request->today) {
                $query->today();
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'appointment_date');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $appointments = $query->paginate($perPage);

            return ApiResponse::success(
                ResponseDetails::successMessage('Appointments retrieved successfully'),
                $appointments,
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving appointments: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to retrieve appointments'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string|exists:users,id',
                'agent_id' => 'nullable|string|exists:agents,id',
                'office_id' => 'nullable|string|exists:real_estate_offices,id',
                'property_id' => 'nullable|string|exists:properties,id',
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required|date_format:H:i',
                'status' => 'nullable|in:pending,confirmed,completed,cancelled',
                'type' => 'nullable|in:viewing,consultation,signing,inspection',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'client_name' => 'required|string|max:255',
                'client_phone' => 'nullable|string|max:20',
                'client_email' => 'nullable|email|max:255',
            ]);
            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            // Ensure at least agent_id or office_id is provided
            if (!$request->agent_id && !$request->office_id) {
                return ApiResponse::error(
                    'Either agent_id or office_id must be provided',
                    null,
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            // Check for appointment conflicts (same agent/office, same date/time)
            $conflictQuery = Appointment::where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->whereIn('status', ['pending', 'confirmed']);

            if ($request->agent_id) {
                $conflictQuery->where('agent_id', $request->agent_id);
            } elseif ($request->office_id) {
                $conflictQuery->where('office_id', $request->office_id);
            }

            if ($conflictQuery->exists()) {
                return ApiResponse::error(
                    'This time slot is already booked',
                    null,
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $appointmentData = $request->all();
            $appointmentData['status'] = $appointmentData['status'] ?? 'pending';
            $appointmentData['type'] = $appointmentData['type'] ?? 'viewing';

            $appointment = Appointment::create($appointmentData);

            // Load relationships for response
            $appointment->load(['user', 'agent', 'office', 'property']);

            // Send appointment notifications
            app(NotificationController::class)->sendAppointmentNotifications($appointment->id);

            return ApiResponse::success(
                ResponseDetails::successMessage('Appointment created successfully'),
                $appointment,
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error creating appointment: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to create appointment'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }

    /**
     * Display the specified appointment.
     */
    public function show($id)
    {
        try {
            $appointment = Appointment::with(['user', 'agent', 'office', 'property'])->find($id);

            if (!$appointment) {
                return ApiResponse::error(
                    ResponseDetails::notFoundMessage('Appointment not found'),
                    null,
                    ResponseDetails::CODE_NOT_FOUND
                );
            }

            return ApiResponse::success(
                ResponseDetails::successMessage('Appointment retrieved successfully'),
                $appointment,
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving appointment: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to retrieve appointment'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $appointment = Appointment::find($id);

            if (!$appointment) {
                return ApiResponse::error(
                    ResponseDetails::notFoundMessage('Appointment not found'),
                    null,
                    ResponseDetails::CODE_NOT_FOUND
                );
            }

            $validator = Validator::make($request->all(), [
                'appointment_date' => 'sometimes|date|after_or_equal:today',
                'appointment_time' => 'sometimes|date_format:H:i',
                'status' => 'sometimes|in:pending,confirmed,completed,cancelled',
                'type' => 'sometimes|in:viewing,consultation,signing,inspection',
                'location' => 'sometimes|string|max:255',
                'notes' => 'sometimes|string',
                'client_name' => 'sometimes|string|max:255',
                'client_phone' => 'sometimes|string|max:20',
                'client_email' => 'sometimes|email|max:255',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            // Check for conflicts if date/time is being updated
            if ($request->has('appointment_date') || $request->has('appointment_time')) {
                $date = $request->appointment_date ?? $appointment->appointment_date;
                $time = $request->appointment_time ?? $appointment->appointment_time;

                $conflictQuery = Appointment::where('id', '!=', $id)
                    ->where('appointment_date', $date)
                    ->where('appointment_time', $time)
                    ->whereIn('status', ['pending', 'confirmed']);

                if ($appointment->agent_id) {
                    $conflictQuery->where('agent_id', $appointment->agent_id);
                } elseif ($appointment->office_id) {
                    $conflictQuery->where('office_id', $appointment->office_id);
                }

                if ($conflictQuery->exists()) {
                    return ApiResponse::error(
                        'This time slot is already booked',
                        null,
                        ResponseDetails::CODE_VALIDATION_ERROR
                    );
                }
            }

            // Store old status for comparison
            $oldStatus = $appointment->status;

            $appointment->update($request->all());

            // Handle status-specific updates
            if ($request->has('status')) {
                switch ($request->status) {
                    case 'confirmed':
                        if (!$appointment->confirmed_at) {
                            $appointment->confirmed_at = now();
                        }
                        break;
                    case 'completed':
                        if (!$appointment->completed_at) {
                            $appointment->completed_at = now();
                        }
                        break;
                    case 'cancelled':
                        if (!$appointment->cancelled_at) {
                            $appointment->cancelled_at = now();
                        }
                        break;
                }
                $appointment->save();

                // Send status update notification if status changed
                if ($oldStatus !== $request->status) {
                    app(NotificationController::class)->sendAppointmentStatusNotification($appointment->id, $request->status);
                }
            }

            // Send reschedule notification if date/time changed
            if ($request->has('appointment_date') || $request->has('appointment_time')) {
                app(NotificationController::class)->sendAppointmentStatusNotification($appointment->id, 'rescheduled');
            }

            $appointment->load(['user', 'agent', 'office', 'property']);

            return ApiResponse::success(
                ResponseDetails::successMessage('Appointment updated successfully'),
                $appointment,
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error updating appointment: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to update appointment'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }
    public function destroy($id)
    {
        try {
            $appointment = Appointment::find($id);

            if (!$appointment) {
                return ApiResponse::error(
                    ResponseDetails::notFoundMessage('Appointment not found'),
                    null,
                    ResponseDetails::CODE_NOT_FOUND
                );
            }

            // Send cancellation notification before deleting
            app(NotificationController::class)->sendAppointmentStatusNotification($appointment->id, 'cancelled');

            $appointment->delete();

            return ApiResponse::success(
                ResponseDetails::successMessage('Appointment deleted successfully'),
                null,
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error deleting appointment: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to delete appointment'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }
    /**
     * Get appointment statistics.
     */
    public function statistics(Request $request)
    {
        try {
            $query = Appointment::query();

            // Apply filters if provided
            if ($request->has('office_id')) {
                $query->where('office_id', $request->office_id);
            }

            if ($request->has('agent_id')) {
                $query->where('agent_id', $request->agent_id);
            }

            $stats = [
                'total' => $query->count(),
                'pending' => $query->clone()->where('status', 'pending')->count(),
                'confirmed' => $query->clone()->where('status', 'confirmed')->count(),
                'completed' => $query->clone()->where('status', 'completed')->count(),
                'cancelled' => $query->clone()->where('status', 'cancelled')->count(),
                'today' => $query->clone()->today()->count(),
                'upcoming' => $query->clone()->upcoming()->count(),
            ];

            return ApiResponse::success(
                ResponseDetails::successMessage('Statistics retrieved successfully'),
                $stats,
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving statistics: ' . $e->getMessage());
            return ApiResponse::error(
                ResponseDetails::serverErrorMessage('Failed to retrieve statistics'),
                null,
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }





    // zana's code ----------------------------------------------------------------------------------

    public function showSchedule()
    {
        $appointments = Appointment::all();
        return view('agent.scheduleList', compact('appointments'));
    }
    public function showAppointmentsPage()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login-page')->with('error', 'Please log in to view your appointments');
            }

            // Get user appointments with relationships
            $appointments = Appointment::with(['agent', 'office', 'property'])
                ->where('user_id', $user->id)
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get();

            Log::info('User appointments page loaded', [
                'user_id' => $user->id,
                'appointments_count' => $appointments->count()
            ]);

            return view('user.appointments', compact('appointments'));
        } catch (\Exception $e) {
            Log::error('Error loading user appointments page', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to load appointments');
        }
    }

    /**
     * Cancel appointment (WEB version)
     * Allows user to cancel their own appointment
     */
    public function cancelAppointment($id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login-page')->with('error', 'Please log in');
            }

            // Find appointment and verify ownership
            $appointment = Appointment::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$appointment) {
                return redirect()->route('user.appointments')
                    ->with('error', 'Appointment not found or you do not have permission to cancel it');
            }

            // Check if already cancelled or completed
            if ($appointment->status === 'cancelled') {
                return redirect()->route('user.appointments')
                    ->with('error', 'This appointment is already cancelled');
            }

            if ($appointment->status === 'completed') {
                return redirect()->route('user.appointments')
                    ->with('error', 'Cannot cancel a completed appointment');
            }

            // Update appointment status
            $appointment->status = 'cancelled';
            $appointment->cancelled_at = now();
            $appointment->save();

            // Send cancellation notification
            app(NotificationController::class)->sendAppointmentStatusNotification($appointment->id, 'cancelled');

            Log::info('Appointment cancelled by user (web)', [
                'user_id' => $user->id,
                'appointment_id' => $id
            ]);

            return redirect()->route('user.appointments')
                ->with('success', 'Appointment cancelled successfully');
        } catch (\Exception $e) {
            Log::error('Cancel appointment error (web)', [
                'message' => $e->getMessage(),
                'appointment_id' => $id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('user.appointments')
                ->with('error', 'Failed to cancel appointment');
        }
    }

    /**
     * Reschedule appointment (WEB version)
     * Allows user to reschedule their own appointment
     */
    public function rescheduleAppointmentWeb(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login-page')->with('error', 'Please log in');
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required|date_format:H:i'
            ]);

            if ($validator->fails()) {
                return redirect()->route('user.appointments')
                    ->withErrors($validator)
                    ->withInput();
            }

            // Find appointment and verify ownership
            $appointment = Appointment::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$appointment) {
                return redirect()->route('user.appointments')
                    ->with('error', 'Appointment not found or you do not have permission to reschedule it');
            }

            // Check if already completed or cancelled
            if ($appointment->status === 'completed') {
                return redirect()->route('user.appointments')
                    ->with('error', 'Cannot reschedule a completed appointment');
            }

            if ($appointment->status === 'cancelled') {
                return redirect()->route('user.appointments')
                    ->with('error', 'Cannot reschedule a cancelled appointment');
            }

            // Check for time slot conflicts
            $conflictQuery = Appointment::where('id', '!=', $id)
                ->where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->whereIn('status', ['pending', 'confirmed']);

            if ($appointment->agent_id) {
                $conflictQuery->where('agent_id', $appointment->agent_id);
            } elseif ($appointment->office_id) {
                $conflictQuery->where('office_id', $appointment->office_id);
            }

            if ($conflictQuery->exists()) {
                return redirect()->route('user.appointments')
                    ->with('error', 'This time slot is already booked. Please choose another time.');
            }

            // Update appointment
            $appointment->appointment_date = $request->appointment_date;
            $appointment->appointment_time = $request->appointment_time;
            $appointment->status = 'pending'; // Reset to pending after reschedule
            $appointment->confirmed_at = null;
            $appointment->save();

            // Send reschedule notification
            app(NotificationController::class)->sendAppointmentStatusNotification($appointment->id, 'rescheduled');

            Log::info('Appointment rescheduled by user (web)', [
                'user_id' => $user->id,
                'appointment_id' => $id,
                'new_date' => $request->appointment_date,
                'new_time' => $request->appointment_time
            ]);

            return redirect()->route('user.appointments')
                ->with('success', 'Appointment rescheduled successfully');
        } catch (\Exception $e) {
            Log::error('Reschedule appointment error (web)', [
                'message' => $e->getMessage(),
                'appointment_id' => $id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('user.appointments')
                ->with('error', 'Failed to reschedule appointment');
        }
    }
}