<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
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

        return ApiResponse::success(
            ResponseDetails::successMessage('Notifications retrieved successfully'),
            $notifications,
            ResponseDetails::CODE_SUCCESS
        );
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
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $notification = Notification::create($request->all());

        return ApiResponse::success(
            ResponseDetails::successMessage('Notification created successfully'),
            $notification,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Notification not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $notification->is_read = true;
        $notification->save();

        return ApiResponse::success(
            ResponseDetails::successMessage('Notification marked as read'),
            $notification,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Display the specified notification.
     */
    public function show($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Notification not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        return ApiResponse::success(
            ResponseDetails::successMessage('Notification retrieved successfully'),
            $notification,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy($id)
    {
        $notification = Notification::find($id);
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
    }
}
