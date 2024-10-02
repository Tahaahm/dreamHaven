<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;


Route::get('/', function () {
    return view('welcome');
});


// User registration (sign-up)
Route::post('/register', [AuthController::class, 'register']);
// User login
Route::post('/login', [AuthController::class, 'login']);
// User logout (protected by authentication middleware)
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


// Retrieve all projects or filter by office
Route::get('/projects', [ProjectController::class, 'index']);
// Retrieve a specific project
Route::get('/projects/{id}', [ProjectController::class, 'show']);
// Create a new project (protected by authentication)
Route::middleware('auth:sanctum')->post('/projects', [ProjectController::class, 'store']);


// Retrieve all properties or filter by agent/office
Route::get('/properties', [PropertyController::class, 'index']);
// Retrieve a specific property
Route::get('/properties/{id}', [PropertyController::class, 'show']);
// Create a new property (protected by authentication)
Route::middleware('auth:sanctum')->post('/properties', [PropertyController::class, 'store']);

//Agent Router
Route::get('/agents', [AgentController::class, 'index']);
Route::post('/agents', [AgentController::class, 'store']);
Route::get('/agents/{id}', [AgentController::class, 'show']);
Route::put('/agents/{id}', [AgentController::class, 'update']);
Route::delete('/agents/{id}', [AgentController::class, 'destroy']);



// Retrieve all appointments or filter by user/agent/office
Route::get('/appointments', [AppointmentController::class, 'index']);
// Create a new appointment (protected by authentication)
Route::middleware('auth:sanctum')->post('/appointments', [AppointmentController::class, 'store']);
// Update an appointment (protected by authentication)
Route::middleware('auth:sanctum')->put('/appointments/{id}', [AppointmentController::class, 'update']);
// Cancel an appointment (protected by authentication)
Route::middleware('auth:sanctum')->delete('/appointments/{id}', [AppointmentController::class, 'destroy']);





// Retrieve all notifications for an office or agent (real estate office is required)
Route::get('/notifications', [NotificationController::class, 'index']);
// Create a new notification for an office or agent
Route::middleware('auth:sanctum')->post('/notifications', [NotificationController::class, 'store']);
// Mark a notification as read
Route::middleware('auth:sanctum')->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
// Delete a notification
Route::middleware('auth:sanctum')->delete('/notifications/{id}', [NotificationController::class, 'destroy']);
