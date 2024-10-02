<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
      /**
     * Get all notifications for a real estate office or filter by agent.
     * If an agent ID is provided, it will return notifications for that agent.
     * If only a real estate office ID is provided, it will return notifications for the office.
     */
    public function index(Request $request)
    {
        if ($request->has('agent_id')) {
            // Get notifications for a specific agent
            $notifications = Notification::where('agent_id', $request->agent_id)->get();
        } elseif ($request->has('office_id')) {
            // Get notifications for a real estate office
            $notifications = Notification::where('office_id', $request->office_id)->get();
        } else {
            return response()->json(['message' => 'Please provide office_id or agent_id'], 400);
        }

        return response()->json($notifications, 200);
    }

    /**
     * Create a new notification for a real estate office or agent.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'office_id' => 'required|exists:real_estate_offices,office_id', // Office ID is required
            'agent_id' => 'nullable|exists:agents,agent_id',  // Optional agent ID
        ]);

        // Create a new notification
        $notification = Notification::create($validatedData);

        return response()->json(['message' => 'Notification created successfully', 'notification' => $notification], 201);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json(['message' => 'Notification marked as read', 'notification' => $notification]);
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }
}
