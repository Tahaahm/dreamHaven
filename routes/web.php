<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RealEstateOfficeController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceProviderController;


Route::get('/', function () {
    return view('welcome');
});


Route::prefix('real-estate-offices')->group(function () {
    // Public routes
    Route::get('/', [RealEstateOfficeController::class, 'index']);
    Route::get('/{id}', [RealEstateOfficeController::class, 'show']);
    Route::get('/{id}/properties', [RealEstateOfficeController::class, 'fetchProperties']);
    Route::post('/login', [RealEstateOfficeController::class, 'login']);

    // Protected routes (uncomment when authentication is implemented)
    // Route::middleware('auth:sanctum')->group(function () {
    Route::post('/', [RealEstateOfficeController::class, 'store']);
    Route::put('/{id}', [RealEstateOfficeController::class, 'update']);
    Route::delete('/{id}', [RealEstateOfficeController::class, 'destroy']);
    // });
});


Route::prefix('v1/api/agents')->group(function () {

    // ===== PUBLIC ROUTES (No Authentication Required) =====
    Route::get('/', [AgentController::class, 'index']);      // fixed
    Route::get('/search', [AgentController::class, 'search']);
    Route::get('/top-rated', [AgentController::class, 'getTopRated']);
    Route::get('/nearby', [AgentController::class, 'getNearbyAgents']);
    Route::get('/company/{companyId}', [AgentController::class, 'getAgentsByCompany']);
    Route::get('/{id}', [AgentController::class, 'show']);

    // ===== AUTHENTICATED ROUTES =====
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [AgentController::class, 'store']);
        Route::put('/{id}', [AgentController::class, 'update']);
        Route::patch('/{id}', [AgentController::class, 'update']);
        Route::delete('/{id}', [AgentController::class, 'destroy']);

        Route::patch('/{id}/verify', [AgentController::class, 'toggleVerification']);
        Route::patch('/{id}/remove-company', [AgentController::class, 'removeFromCompany']);
    });
});



// routes/api.php or routes/web.php
Route::prefix('v1/api/properties')->group(function () {

    Route::get('/search', [PropertyController::class, 'search']);
    Route::get('/nearby', [PropertyController::class, 'nearby']);
    Route::get('/featured', [PropertyController::class, 'getFeatured']);
    Route::get('/boosted', [PropertyController::class, 'getBoosted']);
    Route::get('/statistics', [PropertyController::class, 'getStatistics']);
    Route::post('/map', [PropertyController::class, 'getMapProperties']);

    // Listing type routes (NEW)
    Route::get('/for-rent', [PropertyController::class, 'getByListingType'])
        ->defaults('listingType', 'rent');
    Route::get('/for-sale', [PropertyController::class, 'getByListingType'])
        ->defaults('listingType', 'sell');

    // Owner-specific properties (before generic {id})
    Route::get('/owner/{ownerType}/{ownerId}', [PropertyController::class, 'getByOwner'])
        ->where(['ownerType' => 'User|Agent|RealEstateOffice']);

    // Basic CRUD (public read operations)
    Route::get('/', [PropertyController::class, 'index']);
    Route::get('/{id}', [PropertyController::class, 'show']); // This should be last

    // ===== AUTHENTICATED ROUTES =====

    Route::middleware(['auth:sanctum'])->group(function () {
        //get map

        // Property Management (Owner/Agent operations)
        Route::post('/', [PropertyController::class, 'store']);              // Create
        Route::put('/{id}', [PropertyController::class, 'update']);          // Update
        Route::patch('/{id}', [PropertyController::class, 'update']);        // Partial update
        Route::delete('/{id}', [PropertyController::class, 'destroy']);      // Delete

        // Property Status Management (NEW)
        Route::patch('/{id}/status', [PropertyController::class, 'updateStatus']);     // Update status (available/sold/rented/pending)
        Route::patch('/{id}/boost', [PropertyController::class, 'toggleBoost']);       // Toggle boost/promotion

        // User Interactions
        Route::post('/{id}/favorites', [PropertyController::class, 'addToFavorites']);
        Route::delete('/{id}/favorites', [PropertyController::class, 'removeFromFavorites']);

        // Bulk Operations (UPDATED)
        Route::patch('/bulk-update', [PropertyController::class, 'bulkUpdate']);

        // Owner Management Routes (NEW)
        Route::get('/my-properties', [PropertyController::class, 'getMyProperties']);          // Get current user's properties
        Route::get('/my-properties/drafts', [PropertyController::class, 'getMyDrafts']);       // Get unpublished properties
        Route::get('/my-properties/analytics', [PropertyController::class, 'getMyAnalytics']); // Get property analytics

    });

    // ===== ADMIN/AGENT ONLY ROUTES =====

    Route::middleware(['auth:sanctum', 'role:admin,agent'])->group(function () {

        // Property Verification & Management
        Route::patch('/{id}/verification', [PropertyController::class, 'toggleVerification']);
        Route::patch('/{id}/active', [PropertyController::class, 'toggleActive']);
        Route::patch('/{id}/publish', [PropertyController::class, 'togglePublish']);           // NEW: Toggle publish status

        // Advanced Analytics (NEW)
        Route::get('/analytics/overview', [PropertyController::class, 'getAnalyticsOverview']);
        Route::get('/analytics/trends', [PropertyController::class, 'getTrends']);
        Route::get('/{id}/analytics', [PropertyController::class, 'getPropertyAnalytics']);

        // Bulk Management Operations (ENHANCED)
        Route::patch('/bulk-verify', [PropertyController::class, 'bulkVerify']);
        Route::patch('/bulk-publish', [PropertyController::class, 'bulkPublish']);
        Route::patch('/bulk-status', [PropertyController::class, 'bulkStatusUpdate']);
    });

    // ===== SUPER ADMIN ONLY ROUTES =====

    Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {

        // Advanced Management
        Route::get('/admin/dashboard', [PropertyController::class, 'getAdminDashboard']);
        Route::delete('/admin/bulk-delete', [PropertyController::class, 'bulkDelete']);
        Route::patch('/admin/force-verify/{id}', [PropertyController::class, 'forceVerify']);
        Route::get('/admin/flagged', [PropertyController::class, 'getFlaggedProperties']);
    });
});




// Project Routes
Route::get('/projects', [ProjectController::class, 'index']); // لیستی هەموو پرۆژەکان
Route::post('/projects', [ProjectController::class, 'store']); // دروستکردنی پرۆژەی نوێ
Route::get('/projects/{id}', [ProjectController::class, 'show']); // وەرگرتنی پرۆژەی دیاری کراو
Route::put('/projects/{id}', [ProjectController::class, 'update']); // نوێکردنەوەی پرۆژە
Route::delete('/projects/{id}', [ProjectController::class, 'destroy']); // سڕینەوەی پرۆژە





Route::middleware(['auth:sanctum'])->group(function () {
    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'clearAll']);
    });

    // Admin routes (add appropriate middleware for admin access)
    Route::prefix('admin/notifications')->middleware(['admin'])->group(function () {
        Route::post('/announcement', [NotificationController::class, 'sendSystemAnnouncement']);
    });
});
Route::prefix('api/v1')->group(function () {
    Route::post('/auth/login', [UserController::class, 'login']);
    Route::post('/auth/register', [UserController::class, 'register']);

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Authentication management
        Route::post('/auth/logout', [UserController::class, 'logout']);
        Route::post('/auth/refresh', [UserController::class, 'refresh']);
        Route::patch('/auth/change-password', [UserController::class, 'changePassword']);


        // User profile management (PATCH method as requested)
        Route::patch('/user/profile', [UserController::class, 'updateLocation']);
        Route::get('/user/profile', [UserController::class, 'getProfile']);

        Route::patch('/user/device-token', [UserController::class, 'updateDeviceToken']);
        Route::delete('/user/device-token', [UserController::class, 'removeDeviceToken']);
        Route::get('/user/device-tokens', [UserController::class, 'getDeviceTokens']);


        // Search Preferences (added to UserController)
        Route::get('/user/search-preferences', [UserController::class, 'getSearchPreferences']);
        Route::patch('/user/search-preferences', [UserController::class, 'updateSearchPreferences']);
        Route::post('/user/search-preferences/reset', [UserController::class, 'resetSearchPreferences']);
        Route::get('/user/search-filters', [UserController::class, 'getSearchFilters']);

        // Property Search (added to UserController)
        Route::get('/properties/search', [UserController::class, 'searchProperties']);
        Route::get('/properties/recommendations', [UserController::class, 'getRecommendations']);

        // Notification routes
        Route::get('/user/notifications', [UserController::class, 'getNotifications']);
        Route::patch('/user/notifications/{id}/read', [UserController::class, 'markNotificationRead']);
        Route::patch('/user/notifications/read-all', [UserController::class, 'markAllNotificationsRead']);
        Route::post('/notifications', [UserController::class, 'createNotification']);
        Route::post('/notifications/bulk', [UserController::class, 'createBulkNotifications']);
        Route::delete('/user/notifications/{notificationId}', [UserController::class, 'deleteNotification']);
        Route::delete('/user/notifications', [UserController::class, 'clearAllNotifications']);

        // Appointment routes
        Route::get('/user/appointments', [UserController::class, 'getAppointments']);
        Route::patch('/user/appointments/{id}/cancel', [UserController::class, 'cancelAppointment']);
        Route::post('/appointments', [UserController::class, 'createAppointment']);
        Route::post('/appointments/bulk', [UserController::class, 'createBulkAppointments']);
        Route::patch('/user/appointments/{appointmentId}/reschedule', [UserController::class, 'rescheduleAppointment']);
        Route::delete('/user/appointments/{appointmentId}', [UserController::class, 'deleteAppointment']);
        Route::get('/user/appointments/{userId}', action: [UserController::class, 'getAppointmentsByUser']);
    });
});
// Appointment routes
Route::prefix('appointments')->group(function () {
    Route::get('/', [AppointmentController::class, 'index']);
    Route::post('/', [AppointmentController::class, 'store']);
    Route::get('/statistics', [AppointmentController::class, 'statistics']);
    Route::get('/{id}', [AppointmentController::class, 'show']);
    Route::put('/{id}', [AppointmentController::class, 'update']);
    Route::patch('/{id}', [AppointmentController::class, 'update']);
    Route::delete('/{id}', [AppointmentController::class, 'destroy']);
});


Route::prefix('v1/api/service-providers')->group(function () {
    // ===== PUBLIC ROUTES =====

    // SPECIFIC ROUTES FIRST (before wildcard routes)
    Route::get('/search', [ServiceProviderController::class, 'getServiceProviders']);
    Route::get('/nearby', [ServiceProviderController::class, 'getProvidersByLocation']);
    Route::get('/statistics', [ServiceProviderController::class, 'getStatistics']);
    Route::get('/categories', [ServiceProviderController::class, 'getCategories']); // ← Moved up

    // GENERAL ROUTES
    Route::get('/', [ServiceProviderController::class, 'getServiceProviders']);

    // WILDCARD ROUTES LAST
    Route::get('/{id}', [ServiceProviderController::class, 'getServiceProvider']); // ← Moved down
    Route::get('/{id}/reviews', [ServiceProviderController::class, 'getReviews']);

    // ===== AUTHENTICATED ROUTES =====
    Route::middleware(['auth:sanctum'])->group(function () {

        // Service Providers CRUD
        Route::post('/', [ServiceProviderController::class, 'createServiceProvider']);
        Route::put('/{id}', [ServiceProviderController::class, 'updateServiceProvider']);
        Route::delete('/{id}', [ServiceProviderController::class, 'deleteServiceProvider']);

        // Categories CRUD
        Route::post('/categories', [ServiceProviderController::class, 'createCategory']);
        Route::put('/categories/{id}', [ServiceProviderController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [ServiceProviderController::class, 'deleteCategory']);

        // Gallery Management
        Route::post('/{id}/gallery', [ServiceProviderController::class, 'addGalleryImages']);
        Route::put('/gallery/{imageId}', [ServiceProviderController::class, 'updateGalleryImage']);
        Route::delete('/gallery/{imageId}', [ServiceProviderController::class, 'deleteGalleryImage']);

        // Offerings Management
        Route::post('/{id}/offerings', [ServiceProviderController::class, 'addOffering']);
        Route::put('/offerings/{offeringId}', [ServiceProviderController::class, 'updateOffering']);
        Route::delete('/offerings/{offeringId}', [ServiceProviderController::class, 'deleteOffering']);

        // Reviews
        Route::post('/{id}/reviews', [ServiceProviderController::class, 'addReview']);
        Route::put('/reviews/{reviewId}', [ServiceProviderController::class, 'updateReviewStatus']);
        Route::delete('/reviews/{reviewId}', [ServiceProviderController::class, 'deleteReview']);

        // Plan Management
        Route::post('/{id}/assign-plan', [ServiceProviderController::class, 'assignPlan']);
        Route::delete('/{id}/cancel-plan', [ServiceProviderController::class, 'cancelPlan']);
        Route::get('/{id}/plan-status', [ServiceProviderController::class, 'getPlanStatus']);
    });
});

Route::get('/status', function () {
    return response()->json([
        'database' => 'connected',
        'cache' => 'active',
        'queue' => 'running',
        'storage' => 'available'
    ]);
});