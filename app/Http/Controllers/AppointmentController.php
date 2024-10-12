<?php

namespace App\Http\Controllers;

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

        return response()->json($appointments);
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
            return response()->json($validator->errors(), 400);
        }

        $appointment = Appointment::create($request->all());

        return response()->json(['message' => 'Appointment created successfully', 'appointment' => $appointment], 201);
    }

    /**
     * Display the specified appointment.
     */
    public function show($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }
        return response()->json($appointment);
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'date',
            'time' => 'string',
            'status' => 'in:pending,processing,accepted',
            'location' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $appointment->update($request->all());

        return response()->json(['message' => 'Appointment updated successfully', 'appointment' => $appointment]);
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully']);
    }
}
