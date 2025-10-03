<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Appointment;
use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

=======
use Illuminate\Support\Facades\Validator;


class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        if ($request->has('user_id')) {
            $appointments = Appointment::where('user_id', $request->user_id)->get();
        } elseif ($request->has('agent_id')) {
            $appointments = Appointment::where('agent_id', $request->agent_id)->get();
        } elseif ($request->has('office_id')) {
            $appointments = Appointment::where('office_id', $request->office_id)->get();
        } else {
            $appointments = Appointment::all();
        }

        return ApiResponse::success(
            ResponseDetails::successMessage('Appointments retrieved successfully'),
            $appointments,
            ResponseDetails::CODE_SUCCESS
        );
    }

     /**
     * Store a newly created appointment in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'agent_id' => 'nullable|exists:agents,agent_id',
            'office_id' => 'nullable|exists:real_estate_offices,office_id',
            'date' => 'required|date',
            'time' => 'required',
            'status' => 'in:pending,processing,accepted',
            'location' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $appointment = Appointment::create($request->all());

        return ApiResponse::success(
            ResponseDetails::successMessage('Appointment created successfully'),
            $appointment,
            ResponseDetails::CODE_SUCCESS
        );
    }
>>>>>>> myproject/main
    /**
     * Display the specified appointment.
     */
    public function show($id)
    {
<<<<<<< HEAD
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
=======
        $appointment = Appointment::find($id);
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
    }


    /**
     * Update the specified appointment in storage.
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Appointment not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $validator = Validator::make($request->all(), [
            'date' => 'date',
            'time' => 'string',
            'status' => 'in:pending,processing,accepted',
            'location' => 'string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $appointment->update($request->all());

        return ApiResponse::success(
            ResponseDetails::successMessage('Appointment updated successfully'),
            $appointment,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Appointment not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $appointment->delete();

        return ApiResponse::success(
            ResponseDetails::successMessage('Appointment deleted successfully'),
            null,
            ResponseDetails::CODE_SUCCESS
        );
    }


    //added function
 public function showSchedule()
{
    $appointments = Appointment::all();
    return view('agent.scheduleList', compact('appointments'));
}


>>>>>>> myproject/main
}
