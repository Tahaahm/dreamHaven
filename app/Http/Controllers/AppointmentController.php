<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Appointment;
use Illuminate\Http\Request;
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
    /**
     * Display the specified appointment.
     */
    public function show($id)
    {
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
}
