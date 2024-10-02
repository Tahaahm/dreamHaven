<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
   /**
     * Get all appointments or filter by user/agent/office.
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

        return response()->json($appointments, 200);
    }

    /**
     * Create a new appointment between a user and an agent/office.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'agent_id' => 'nullable|exists:agents,agent_id',
            'office_id' => 'nullable|exists:real_estate_offices,office_id',
            'date' => 'required|date',
            'time' => 'required',
            'status' => 'in:pending,processing,accepted',
            'location' => 'required|string',
        ]);

        // Create a new appointment
        $appointment = Appointment::create($validatedData);

        return response()->json(['message' => 'Appointment created successfully', 'appointment' => $appointment], 201);
    }

    /**
     * Update an appointment's details.
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        // Validate the incoming request
        $validatedData = $request->validate([
            'date' => 'date',
            'time' => 'string',
            'status' => 'in:pending,processing,accepted',
            'location' => 'string',
        ]);

        // Update the appointment
        $appointment->update($validatedData);

        return response()->json(['message' => 'Appointment updated successfully', 'appointment' => $appointment]);
    }

    /**
     * Cancel an appointment (delete).
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $appointment->delete();

        return response()->json(['message' => 'Appointment cancelled successfully']);
    }
}
