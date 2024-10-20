<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RealEstateOfficeController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    return view('welcome');
});


// User Routes
Route::get('/users', [AuthController::class, 'index']); // List all users
Route::post('/users', [AuthController::class, 'store']); // Create a new user
Route::get('/users/{id}', [AuthController::class, 'show']); // Get a specific user
Route::put('/users/{id}', [AuthController::class, 'update']); // Update a specific user
Route::delete('/users/{id}', [AuthController::class, 'destroy']); // Delete a user



// Real Estate Office Routes
// Real Estate Office Routes
Route::get('/real-estate-offices', [RealEstateOfficeController::class, 'index']); // List all offices
Route::post('/real-estate-offices', [RealEstateOfficeController::class, 'store']); // Create a new office
Route::post('/real-estate-office/login', [RealEstateOfficeController::class, 'login']);
Route::get('/real-estate-offices/{id}', [RealEstateOfficeController::class, 'show']); // Get a specific office
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/real-estate-offices/{id}', [RealEstateOfficeController::class, 'update']);
    Route::delete('/real-estate-offices/{id}', [RealEstateOfficeController::class, 'destroy']);
});



// Agent Routes
Route::get('/agents', [AgentController::class, 'index']); // List all agents
Route::post('/agents', [AgentController::class, 'store']); // Create a new agent
Route::get('/agents/{id}', [AgentController::class, 'show']); // Get a specific agent
Route::put('/agents/{id}', [AgentController::class, 'update']); // Update an agent
Route::delete('/agents/{id}', [AgentController::class, 'destroy']); // Delete an agent





// Property Routes
Route::get('/properties', [PropertyController::class, 'index']); // List all properties
Route::post('/properties', [PropertyController::class, 'store']); // Create a new property
Route::get('/properties/{id}', [PropertyController::class, 'show']); // Get a specific property
Route::put('/properties/{id}', [PropertyController::class, 'update']); // Update a property
Route::delete('/properties/{id}', [PropertyController::class, 'destroy']); // Delete a property




// Project Routes
Route::get('/projects', [ProjectController::class, 'index']); // لیستی هەموو پرۆژەکان
Route::post('/projects', [ProjectController::class, 'store']); // دروستکردنی پرۆژەی نوێ
Route::get('/projects/{id}', [ProjectController::class, 'show']); // وەرگرتنی پرۆژەی دیاری کراو
Route::put('/projects/{id}', [ProjectController::class, 'update']); // نوێکردنەوەی پرۆژە
Route::delete('/projects/{id}', [ProjectController::class, 'destroy']); // سڕینەوەی پرۆژە




// Appointment Routes
Route::get('/appointments', [AppointmentController::class, 'index']); // List all appointments
Route::post('/appointments', [AppointmentController::class, 'store']); // Create a new appointment
Route::get('/appointments/{id}', [AppointmentController::class, 'show']); // Get a specific appointment
Route::put('/appointments/{id}', [AppointmentController::class, 'update']); // Update an appointment
Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']); // Delete an appointment




// Notification Routes
Route::get('/notifications', [NotificationController::class, 'index']); // List all notifications
Route::post('/notifications', [NotificationController::class, 'store']); // Create a new notification
Route::get('/notifications/{id}', [NotificationController::class, 'show']); // Get a specific notification
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']); // Mark a notification as read
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']); // Delete a notification
