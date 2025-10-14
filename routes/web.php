<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RealEstateOfficeController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BannerAdController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceProviderController;


Route::get('/', [PropertyController::class, 'newindex']);
Route::get('/', [PropertyController::class, 'newindex'])->name('newindex');

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



// Property Routes - Cleaned and Organized
Route::prefix('v1/api/properties')->group(function () {

    // ===== PUBLIC ROUTES (No Authentication Required) =====

    // Search and filtering routes (specific routes first)
    Route::get('/search', [PropertyController::class, 'search']);
    Route::get('/nearby', [PropertyController::class, 'nearby']);
    Route::get('/featured', [PropertyController::class, 'getFeatured']);
    Route::get('/boosted', [PropertyController::class, 'getBoosted']);
    Route::get('/statistics', [PropertyController::class, 'getStatistics']);

    // Map functionality
    Route::post('/map', [PropertyController::class, 'getMapProperties']);

    Route::get('/owner/{ownerType}/{ownerId}', [PropertyController::class, 'getByOwner'])
        ->where(['ownerType' => 'User|Agent|RealEstateOffice']);

    // Basic CRUD - public read operations
    Route::get('/', [PropertyController::class, 'index']);
    Route::get('/{id}', [PropertyController::class, 'show']);

    // ===== AUTHENTICATED ROUTES =====
    Route::middleware(['auth:sanctum'])->group(function () {

        // Property CRUD Operations
        Route::post('/', [PropertyController::class, 'store']);              // Create new property
        Route::post('/store', [PropertyController::class, 'store']);         // Alternative create endpoint (keeping for frontend compatibility)
        Route::put('/{id}', [PropertyController::class, 'update']);          // Full update
        Route::patch('/{id}', [PropertyController::class, 'update']);        // Partial update
        Route::delete('/{id}', [PropertyController::class, 'destroy']);      // Delete property

        // Property Status Management
        Route::patch('/{id}/status', [PropertyController::class, 'updateStatus']);     // Update status (available/sold/rented/pending)
        Route::patch('/{id}/boost', [PropertyController::class, 'toggleBoost']);       // Toggle boost/promotion

        // User Interactions
        Route::post('/{id}/favorites', [PropertyController::class, 'addToFavorites']);
        Route::delete('/{id}/favorites', [PropertyController::class, 'removeFromFavorites']);

        // User's Property Management
        Route::get('/my-properties', [PropertyController::class, 'getMyProperties']);          // Current user's properties

        // Bulk Operations
        Route::patch('/bulk-update', [PropertyController::class, 'bulkUpdate']);
    });

    // ===== ADMIN/AGENT ROUTES =====
    Route::middleware(['auth:sanctum', 'role:admin,agent'])->group(function () {

        // Property Management & Verification
        Route::patch('/{id}/verification', [PropertyController::class, 'toggleVerification']);
        Route::patch('/{id}/active', [PropertyController::class, 'toggleActive']);
        Route::patch('/{id}/publish', [PropertyController::class, 'togglePublish']);

        // Advanced Analytics
        Route::get('/analytics/overview', [PropertyController::class, 'getAnalyticsOverview']);
        Route::get('/analytics/trends', [PropertyController::class, 'getTrends']);
        Route::get('/{id}/analytics', [PropertyController::class, 'getPropertyAnalytics']);

        // Bulk Management Operations
        Route::patch('/bulk-verify', [PropertyController::class, 'bulkVerify']);
        Route::patch('/bulk-publish', [PropertyController::class, 'bulkPublish']);
        Route::patch('/bulk-status', [PropertyController::class, 'bulkStatusUpdate']);
    });

    // ===== SUPER ADMIN ROUTES =====
    Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {

        // Administrative Management
        Route::get('/admin/dashboard', [PropertyController::class, 'getAdminDashboard']);
        Route::get('/admin/flagged', [PropertyController::class, 'getFlaggedProperties']);
        Route::delete('/admin/bulk-delete', [PropertyController::class, 'bulkDelete']);
        Route::patch('/admin/force-verify/{id}', [PropertyController::class, 'forceVerify']);
    });
});

Route::prefix('v1/api/projects')->group(function () {

    // ===== PUBLIC ROUTES (No Authentication Required) =====

    // Search and filtering routes (specific routes first)
    Route::get('/featured', [ProjectController::class, 'featured']); // GET /v1/api/projects/featured

    // Developer-specific projects
    Route::get('/developer/{developerId}', [ProjectController::class, 'byDeveloper']); // GET /v1/api/projects/developer/{id}

    // Basic CRUD - public read operations
    Route::get('/', [ProjectController::class, 'index']); // GET /v1/api/projects
    Route::get('/{id}', [ProjectController::class, 'show']); // GET /v1/api/projects/{id}

    // ===== AUTHENTICATED ROUTES =====
    Route::middleware(['auth:sanctum'])->group(function () {

        // Project CRUD Operations
        Route::post('/', [ProjectController::class, 'store']); // Create new project
        Route::put('/{id}', [ProjectController::class, 'update']); // Full update
        Route::patch('/{id}', [ProjectController::class, 'update']); // Partial update
        Route::delete('/{id}', [ProjectController::class, 'destroy']); // Delete project

    });
});




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

    // ===== PUBLIC AUTHENTICATION ROUTES =====
    Route::prefix('auth')->group(function () {
        Route::post('/login', [UserController::class, 'login']);
        Route::post('/register', [UserController::class, 'register']);
        Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
        Route::post('/confirm-password-reset', [UserController::class, 'confirmPasswordReset']);
    });

    // ===== AUTHENTICATED USER ROUTES =====
    Route::middleware('auth:sanctum')->group(function () {

        // Authentication Management
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [UserController::class, 'logout']);
            Route::post('/refresh', [UserController::class, 'refresh']);
            Route::patch('/change-password', [UserController::class, 'changePassword']);
        });

        // User Profile Management - MOVED INSIDE AUTH GROUP
        Route::prefix('user')->group(function () {
            Route::get('/profile', [UserController::class, 'getProfile']); // ← FIXED: Now authenticated
            Route::patch('/profile', [UserController::class, 'updateLocation']);

            // Device Token Management
            Route::patch('/device-token', [UserController::class, 'updateDeviceToken']);
            Route::delete('/device-token', [UserController::class, 'removeDeviceToken']);
            Route::get('/device-tokens', [UserController::class, 'getDeviceTokens']);

            // Search Preferences Management
            Route::get('/search-preferences', [UserController::class, 'getSearchPreferences']);
            Route::patch('/search-preferences', [UserController::class, 'updateSearchPreferences']);
            Route::post('/search-preferences/reset', [UserController::class, 'resetSearchPreferences']);
            Route::get('/search-filters', [UserController::class, 'getSearchFilters']);

            // User Notifications Management
            Route::get('/notifications', [UserController::class, 'getNotifications']);
            Route::patch('/notifications/{id}/read', [UserController::class, 'markNotificationRead']);
            Route::patch('/notifications/read-all', [UserController::class, 'markAllNotificationsRead']);
            Route::delete('/notifications/{notificationId}', [UserController::class, 'deleteNotification']);
            Route::delete('/notifications', [UserController::class, 'clearAllNotifications']);

            // User Appointments Management
            Route::get('/appointments', [UserController::class, 'getAppointments']);
            Route::get('/appointments/{userId}', [UserController::class, 'getAppointmentsByUser']);
            Route::patch('/appointments/{id}/cancel', [UserController::class, 'cancelAppointment']);
            Route::patch('/appointments/{appointmentId}/reschedule', [UserController::class, 'rescheduleAppointment']);
            Route::delete('/appointments/{appointmentId}', [UserController::class, 'deleteAppointment']);
        });

        // Property Search & Recommendations (User Context)
        Route::prefix('properties')->group(function () {
            Route::get('/search', [UserController::class, 'searchProperties']);
            Route::get('/recommendations', [UserController::class, 'getRecommendations']);
        });

        // Admin/System Operations (if needed in user context)
        Route::post('/notifications', [UserController::class, 'createNotification']);
        Route::post('/notifications/bulk', [UserController::class, 'createBulkNotifications']);
        Route::post('/appointments', [UserController::class, 'createAppointment']);
        Route::post('/appointments/bulk', [UserController::class, 'createBulkAppointments']);
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


// Add this to your existing routes/web.php or routes/api.php

// Banner Ad Routes
Route::prefix('v1/api/banner-ads')->group(function () {

    // ===== PUBLIC ROUTES (No Authentication Required) =====
    Route::get('/active', [BannerAdController::class, 'getActiveForDisplay']); // Get banners for display
    Route::post('/{id}/click', [BannerAdController::class, 'recordClick']); // Track clicks

    // ===== AUTHENTICATED ROUTES =====
    Route::middleware(['auth:sanctum'])->group(function () {

        // Basic CRUD Operations
        Route::get('/', [BannerAdController::class, 'index']); // List banners with filters
        Route::post('/', [BannerAdController::class, 'store']); // Create banner
        Route::get('/{id}', [BannerAdController::class, 'show']); // Get single banner
        Route::put('/{id}', [BannerAdController::class, 'update']); // Update banner
        Route::delete('/{id}', [BannerAdController::class, 'destroy']); // Delete banner

        // Banner Management
        Route::patch('/{id}/pause', [BannerAdController::class, 'pause']); // Pause banner
        Route::patch('/{id}/resume', [BannerAdController::class, 'resume']); // Resume banner
        Route::post('/{id}/boost', [BannerAdController::class, 'boost']); // Boost banner

        // Analytics & Performance
        Route::get('/{id}/analytics', [BannerAdController::class, 'analytics']); // Get analytics

        // File Upload
        Route::post('/upload-image', [BannerAdController::class, 'uploadImage']); // Upload banner image
    });

    // ===== ADMIN ROUTES =====
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

        // Admin Management
        Route::get('/pending-approval', [BannerAdController::class, 'pendingApproval']); // Pending banners
        Route::patch('/{id}/approve', [BannerAdController::class, 'approve']); // Approve banner
        Route::patch('/{id}/reject', [BannerAdController::class, 'reject']); // Reject banner
    });
});
















// Zana's Routes ----------------------------------------------------------------------------------------------------------------


// Properties list
Route::get('/list', [PropertyController::class, 'index'])->name('list');

// About Us page
Route::get('/about-us', function () {
    return view('about-us');
})->name('about-us');

// Contact page
Route::get('/contact-us', function () {
    return view('contact-us');
})->name('contact-us');

Route::get('/login-page', function () {
    return view('login-page');
})->name('login-page');

Route::get('/properties/search', [PropertyController::class, 'search'])->name('properties.search');


Route::post('/user/store', [AuthController::class, 'store'])->name('user.store');

// Regular user login
Route::post('/auth/login', [AuthController::class, 'loginUser'])->name('loginUser');

// Logout
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/agent/admin-dashboard', [AuthController::class, 'adminDashboard'])->name('agent.admin-dashboard');

// Show upload form
Route::get('/property/upload', [PropertyController::class, 'create'])->name('upload');

// Handle the POST submission
Route::post('/property/upload', [PropertyController::class, 'store'])->name('property.upload');


Route::get('/admin/property-list', [PropertyController::class, 'adminPropertyList'])->name('admin.property-list');
Route::get('/admin/property-list', [PropertyController::class, 'showUserProperties'])->name('admin.property-list');

Route::middleware('auth')->group(function () {
    Route::get('/review', [AuthController::class, 'showReviews'])->name('agent.review');
});

Route::get('/profile', [AuthController::class, 'showProfile'])->middleware('auth')->name('admin.profile');


Route::get('/notifications', [NotificationController::class, 'showNotifications'])->name('notifications.show');
Route::get('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
Route::get('/notifications/delete/{id}', [NotificationController::class, 'destroy'])->name('notifications.delete');
// Retrieve all notifications for an office or agent (real estate office is required)
Route::get('/notifications', [NotificationController::class, 'index']);
// Create a new notification for an office or agent
Route::middleware('auth:sanctum')->post('/notifications', [NotificationController::class, 'store']);
// Mark a notification as read
Route::middleware('auth:sanctum')->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
// Delete a notification
Route::middleware('auth:sanctum')->delete('/notifications/{id}', [NotificationController::class, 'destroy']);

Route::get('/notifications', [NotificationController::class, 'showNotifications']);
Route::get('/notifications', [NotificationController::class, 'showNotifications'])->name('notifications');


Route::get('/schedule', [AppointmentController::class, 'showSchedule'])->name('schedule');

Route::get('/appointments/schedule-list', [AppointmentController::class, 'showScheduleList'])->name('appointments.scheduleList');

// Retrieve all projects or filter by office
Route::get('/projects', [ProjectController::class, 'index']);
// Retrieve a specific project
Route::get('/projects/{id}', [ProjectController::class, 'show']);
// Create a new project (protected by authentication)
Route::middleware('auth:sanctum')->post('/projects', [ProjectController::class, 'store']);


Route::get('/projects', [ProjectController::class, 'showProjects'])->name('projects');


Route::post('/projects/store', [ProjectController::class, 'store'])->name('projects.store');
