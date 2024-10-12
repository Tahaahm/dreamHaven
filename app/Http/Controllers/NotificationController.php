<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        if ($request->has('user_id')) {
            $notifications = Notification::where('user_id', $request->user_id)->get();
        } elseif ($request->has('agent_id')) {
            $notifications = Notification::where('agent_id', $request->agent_id)->get();
        } elseif ($request->has('office_id')) {
            $notifications = Notification::where('office_id', $request->office_id)->get();
        } else {
            $notifications = Notification::all();
        }

        return response()->json($notifications);
    }

    /**
     * Store a newly created notification in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,user_id',
            'agent_id' => 'nullable|exists:agents,agent_id',
            'office_id' => 'nullable|exists:real_estate_offices,office_id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'sent_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $notification = Notification::create($request->all());

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
     * Display the specified notification.
     */
    public function show($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }
        return response()->json($notification);
    }

    /**
     * Remove the specified notification from storage.
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
