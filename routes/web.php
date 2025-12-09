<?php

use App\Http\Controllers\AppVersionController;
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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Middleware\EnsureUserIsVerified;
use App\Http\Middleware\AgentOrAdmin;
use App\Http\Middleware\AgentOnly;

// ============================================
// TEST & DEBUG ROUTES (Remove in production)
// ============================================

Route::get('/test-no-middleware', function () {
    return "NO MIDDLEWARE REACHED";
});

Route::get('/test-middleware', function () {
    return "Middleware alias works!";
})->middleware('agent.or.admin');

// ============================================
// PUBLIC WEB ROUTES
// ============================================

Route::get('/', [PropertyController::class, 'newindex'])->name('newindex');
Route::get('/login-page', [AuthController::class, 'showLoginForm'])->name('login-page');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::post('/auth/google', [AuthController::class, 'googleLogin'])->name('auth.google');

Route::get('/contact-us', function () {
    return view('contact-us');
})->name('contact-us');

Route::get('/about-us', function () {
    return view('about-us');
})->name('about-us');

Route::get('/properties/search', [PropertyController::class, 'search'])->name('properties.search');
Route::get('/list', [PropertyController::class, 'showList'])->name('property.list');
Route::get('/PropertyDetail/{property_id}', [PropertyController::class, 'showPortfolio'])->name('property.PropertyDetail');
Route::get('/projects', [ProjectController::class, 'showProjects'])->name('projects.index');
Route::get('/agents', [AgentController::class, 'index'])->name('agents.list');
Route::get('/review', [AuthController::class, 'showReviews'])->name('agent.review');

// Become Agent Routes
Route::get('/become-agent', function () {
    $user = auth('web')->user();
    if (!$user) {
        return redirect()->route('login-page')->with('error', 'Please log in first.');
    }
    return view('agent.become', compact('user'));
})->middleware('auth:web')->name('become.agent.prompt');

Route::get('/become-agent/{user_id}', [AgentController::class, 'showCreateFromUserForm'])->name('agent.create.from.user');
Route::post('/become-agent', [AgentController::class, 'createFromUser'])->name('agent.create.from.user.submit');
Route::post('/agent/request', [AgentController::class, 'createFromUser'])->name('agent.request');

// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('newindex')->with('success', 'Email verified successfully!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::get('/email/verify', [AuthController::class, 'showVerifyEmail'])
    ->middleware('auth:web,agent')
    ->name('verification.notice');

Route::post('/verify-email', [AuthController::class, 'verifyCode'])
    ->middleware('auth:web,agent')
    ->name('verify.code');

Route::post('/resend-code', [AuthController::class, 'resendCode'])
    ->middleware('auth:web,agent')
    ->name('resend.code');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth:web,agent', 'throttle:6,1'])->name('verification.send');

// ============================================
// PROTECTED WEB ROUTES (Authenticated Users)
// ============================================

Route::middleware(['auth:web,agent', EnsureUserIsVerified::class])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile Management
    Route::get('profile/{id}/edit', [AuthController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update/{id}', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile', [AuthController::class, 'showProfile'])
        ->middleware(AgentOrAdmin::class)
        ->name('admin.profile');

    // Alternative profile edit route for compatibility
    Route::get('/profile/{id}/edit', [PropertyController::class, 'editUser']);

    // Agent Profile
    Route::get('/agent/edit/{id}', [AgentController::class, 'edit'])->name('agent.edit');
    Route::put('/agent/update-profile/{id}', [AgentController::class, 'updateAgentProfile'])->name('agent.updateProfile');
    Route::get('/agent/admin-dashboard', [AuthController::class, 'adminDashboard'])->name('agent.admin-dashboard');
    Route::get('/agent/properties', [PropertyController::class, 'showUserProperties'])->name('agent.property.list');

    // Real Estate Office
    Route::middleware(['auth:agent'])->group(function () {
        Route::get('/agent/real-estate-office', [RealEstateOfficeController::class, 'create'])->name('agent.real-estate-office');
        Route::post('/agent/real-estate-office', [RealEstateOfficeController::class, 'store'])->name('agent.real-estate-office.store');
        Route::get('/agent/real-estate-office-profile/{id}', [RealEstateOfficeController::class, 'profile'])->name('agent.office.profile');
    });

    Route::get('/office/{id}/dashboard', [RealEstateOfficeController::class, 'dashboard'])->name('office.dashboard');
    Route::get('/office/{id}/profile', [RealEstateOfficeController::class, 'profile'])->name('office.profile');
    Route::get('/office/{id}/agents/load-more', [RealEstateOfficeController::class, 'loadMoreAgents'])->name('office.agents.load-more');
    Route::get('/office/{id}/properties/load-more', [RealEstateOfficeController::class, 'loadMoreProperties'])->name('office.properties.load-more');

    // User Management
    Route::post('/user/store', [AuthController::class, 'store'])->name('user.store');

    // Property Management
    Route::prefix('property')->group(function () {
        Route::get('upload', [PropertyController::class, 'create'])->name('property.upload');
        Route::post('store', [PropertyController::class, 'store'])->name('property.store');
        Route::get('{id}', [PropertyController::class, 'show'])->name('property.show');
        Route::get('/{property_id}/edit', [PropertyController::class, 'edit'])->name('property.edit');
        Route::put('/{id}', [PropertyController::class, 'update'])->name('property.update');
        Route::delete('/{property_id}', [PropertyController::class, 'destroy'])->name('property.delete');
        Route::post('/{property_id}/remove-image', [PropertyController::class, 'removeImage'])->name('property.removeImage');
    });

    Route::post('/upload-images', [PropertyController::class, 'uploadImages'])->name('property.uploadImages');
    Route::get('/properties/{property_id}/edit', [PropertyController::class, 'editProperty'])->name('property.edit');

    // Projects
    Route::post('/projects/store', [ProjectController::class, 'store'])->name('projects.store');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'showNotifications'])->name('notifications.show');
    Route::get('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/notifications/delete/{id}', [NotificationController::class, 'destroy'])->name('notifications.delete');

    // Schedule
    Route::get('/schedule', [AppointmentController::class, 'showSchedule'])->name('schedule');
    Route::get('/appointments/schedule-list', [AppointmentController::class, 'showScheduleList'])->name('appointments.scheduleList');

    // Reports
    Route::post('/report', [ReportController::class, 'store'])->name('report.store');

    // Admin Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::get('/admin/property-list', [PropertyController::class, 'showUserProperties'])->name('admin.property-list');

        // User Management
        Route::get('/admin/users', [AuthController::class, 'usersList'])->name('admin.users');
        Route::get('/admin/users/{id}', [AuthController::class, 'userDetail'])->name('admin.users.show');
        Route::post('/admin/users/{id}/suspend', [AuthController::class, 'suspendUser'])->name('admin.users.suspend');
        Route::delete('/admin/users/{id}', [AuthController::class, 'deleteUser'])->name('admin.users.delete');

        // Entity Management
        Route::get('/admin/entities', [AdminController::class, 'entitiesList'])->name('admin.entities.list');
        Route::get('/admin/entity-detail/{type}/{id}', [AuthController::class, 'entityDetail'])->name('admin.entity.detail');
        Route::post('/admin/entity-suspend/{type}/{id}', [AuthController::class, 'suspendEntity'])->name('admin.entity.suspend');
        Route::delete('/admin/entity-delete/{type}/{id}', [AuthController::class, 'deleteEntity'])->name('admin.entity.delete');
        Route::get('/admin/user/{id}', [AdminController::class, 'userDetail'])->name('admin.users.show');
        Route::get('/admin/agent/{id}', [AdminController::class, 'agentDetail'])->name('admin.agents.show');
        Route::post('/admin/entity/suspend/{id}', [AdminController::class, 'suspendEntity'])->name('admin.entity.suspend');
        Route::delete('/admin/entity/delete/{id}', [AdminController::class, 'deleteEntity'])->name('admin.entity.delete');

        // Property Management
        Route::get('/admin/properties', function () {
            if (!auth()->user() || auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized');
            }
            return app(AdminController::class)->adminProperties(request());
        })->name('admin.properties');

        Route::delete('/admin/properties/{id}', function ($id) {
            if (!auth()->user() || auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized');
            }
            return app(AdminController::class)->deleteProperty($id);
        })->name('admin.properties.delete');
    });
});

// ============================================
// API ROUTES - V1
// ============================================

Route::prefix('api/v1')->group(function () {

    // ===== PUBLIC AUTHENTICATION ROUTES =====
    Route::prefix('auth')->group(function () {
        Route::post('/login', [UserController::class, 'login']);
        Route::post('/register', [UserController::class, 'register']);
        Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
        Route::post('/confirm-password-reset', [UserController::class, 'confirmPasswordReset']);
        Route::post('/google/signin', [UserController::class, 'googleSignIn']);

        // Email Verification Endpoints
        Route::post('/send-verification-code', [UserController::class, 'sendVerificationCode']);
        Route::post('/verify-code', [UserController::class, 'verifyCodeBeforeRegister']);
    });

    // ===== AUTHENTICATED USER ROUTES =====
    Route::middleware('auth:sanctum')->group(function () {

        // Authentication Management
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [UserController::class, 'logout']);
            Route::post('/refresh', [UserController::class, 'refresh']);
            Route::patch('/change-password', [UserController::class, 'changePassword']);
            Route::post('/google/link', [UserController::class, 'linkGoogleAccount']);
        });

        // User Profile Management
        Route::prefix('user')->group(function () {
            Route::get('/profile', [UserController::class, 'getProfile']);
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

        // Property Search & Recommendations
        Route::prefix('properties')->group(function () {
            Route::get('/search', [UserController::class, 'searchProperties']);
            Route::get('/recommendations', [UserController::class, 'getRecommendations']);
        });

        // Admin/System Operations
        Route::post('/notifications', [UserController::class, 'createNotification']);
        Route::post('/notifications/bulk', [UserController::class, 'createBulkNotifications']);
        Route::post('/appointments', [UserController::class, 'createAppointment']);
        Route::post('/appointments/bulk', [UserController::class, 'createBulkAppointments']);
    });
});

// ============================================
// API ROUTES - Real Estate Offices
// ============================================

Route::prefix('real-estate-offices')->group(function () {
    // Public routes
    Route::get('/', [RealEstateOfficeController::class, 'index']);
    Route::get('/{id}', [RealEstateOfficeController::class, 'show']);
    Route::get('/{id}/properties', [RealEstateOfficeController::class, 'fetchProperties']);
    Route::post('/login', [RealEstateOfficeController::class, 'login']);

    // Protected routes
    Route::post('/', [RealEstateOfficeController::class, 'store']);
    Route::put('/{id}', [RealEstateOfficeController::class, 'update']);
    Route::delete('/{id}', [RealEstateOfficeController::class, 'destroy']);
});

// ============================================
// API ROUTES - Agents
// ============================================

Route::prefix('v1/api/agents')->group(function () {
    // Public Routes
    Route::get('/', [AgentController::class, 'index']);
    Route::get('/search', [AgentController::class, 'search']);
    Route::get('/top-rated', [AgentController::class, 'getTopRated']);
    Route::get('/nearby', [AgentController::class, 'getNearbyAgents']);
    Route::get('/company/{companyId}', [AgentController::class, 'getAgentsByCompany']);
    Route::get('/{id}', [AgentController::class, 'show']);
    Route::post('/users/{user_id}/convert-to-agent', [AgentController::class, 'createFromUser']);

    // Authenticated Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [AgentController::class, 'store']);
        Route::put('/{id}', [AgentController::class, 'update']);
        Route::patch('/{id}', [AgentController::class, 'update']);
        Route::delete('/{id}', [AgentController::class, 'destroy']);
        Route::patch('/{id}/verify', [AgentController::class, 'toggleVerification']);
        Route::patch('/{id}/remove-company', [AgentController::class, 'removeFromCompany']);
    });
});

// ============================================
// API ROUTES - Properties
// ============================================

Route::prefix('v1/api/properties')->group(function () {
    // Public Routes
    Route::get('/search', [PropertyController::class, 'search']);
    Route::get('/nearby', [PropertyController::class, 'nearby']);
    Route::get('/featured', [PropertyController::class, 'getFeatured']);
    Route::get('/boosted', [PropertyController::class, 'getBoosted']);
    Route::get('/statistics', [PropertyController::class, 'getStatistics']);
    Route::post('/map', [PropertyController::class, 'getMapProperties']);
    Route::get('/owner/{ownerType}/{ownerId}', [PropertyController::class, 'getByOwner'])
        ->where(['ownerType' => 'User|Agent|RealEstateOffice']);
    Route::get('/', [PropertyController::class, 'index']);
    Route::get('/{id}', [PropertyController::class, 'show']);

    // Authenticated Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [PropertyController::class, 'store']);
        Route::post('/store', [PropertyController::class, 'store']); // Alternative endpoint
        Route::put('/{id}', [PropertyController::class, 'update']);
        Route::patch('/{id}', [PropertyController::class, 'update']);
        Route::delete('/{id}', [PropertyController::class, 'destroy']);
        Route::patch('/{id}/status', [PropertyController::class, 'updateStatus']);
        Route::patch('/{id}/boost', [PropertyController::class, 'toggleBoost']);
        Route::post('/{id}/favorites', [PropertyController::class, 'addToFavorites']);
        Route::delete('/{id}/favorites', [PropertyController::class, 'removeFromFavorites']);
        Route::get('/my-properties', [PropertyController::class, 'getMyProperties']);
        Route::patch('/bulk-update', [PropertyController::class, 'bulkUpdate']);
    });

    // Admin/Agent Routes
    Route::middleware(['auth:sanctum', 'role:admin,agent'])->group(function () {
        Route::patch('/{id}/verification', [PropertyController::class, 'toggleVerification']);
        Route::patch('/{id}/active', [PropertyController::class, 'toggleActive']);
        Route::patch('/{id}/publish', [PropertyController::class, 'togglePublish']);
        Route::get('/analytics/overview', [PropertyController::class, 'getAnalyticsOverview']);
        Route::get('/analytics/trends', [PropertyController::class, 'getTrends']);
        Route::get('/{id}/analytics', [PropertyController::class, 'getPropertyAnalytics']);
        Route::patch('/bulk-verify', [PropertyController::class, 'bulkVerify']);
        Route::patch('/bulk-publish', [PropertyController::class, 'bulkPublish']);
        Route::patch('/bulk-status', [PropertyController::class, 'bulkStatusUpdate']);
    });

    // Super Admin Routes
    Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
        Route::get('/admin/dashboard', [PropertyController::class, 'getAdminDashboard']);
        Route::get('/admin/flagged', [PropertyController::class, 'getFlaggedProperties']);
        Route::delete('/admin/bulk-delete', [PropertyController::class, 'bulkDelete']);
        Route::patch('/admin/force-verify/{id}', [PropertyController::class, 'forceVerify']);
    });
});

// ============================================
// API ROUTES - Projects
// ============================================

Route::prefix('v1/api/projects')->group(function () {
    // Public Routes
    Route::get('/featured', [ProjectController::class, 'featured']);
    Route::get('/developer/{developerId}', [ProjectController::class, 'byDeveloper']);
    Route::get('/', [ProjectController::class, 'index']);
    Route::get('/{id}', [ProjectController::class, 'show']);

    // Authenticated Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [ProjectController::class, 'store']);
        Route::put('/{id}', [ProjectController::class, 'update']);
        Route::patch('/{id}', [ProjectController::class, 'update']);
        Route::delete('/{id}', [ProjectController::class, 'destroy']);
    });
});

// ============================================
// API ROUTES - Appointments
// ============================================

Route::prefix('appointments')->group(function () {
    Route::get('/', [AppointmentController::class, 'index']);
    Route::post('/', [AppointmentController::class, 'store']);
    Route::get('/statistics', [AppointmentController::class, 'statistics']);
    Route::get('/{id}', [AppointmentController::class, 'show']);
    Route::put('/{id}', [AppointmentController::class, 'update']);
    Route::patch('/{id}', [AppointmentController::class, 'update']);
    Route::delete('/{id}', [AppointmentController::class, 'destroy']);
});

// ============================================
// API ROUTES - Notifications
// ============================================

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'clearAll']);
    });

    Route::prefix('admin/notifications')->middleware(['admin'])->group(function () {
        Route::post('/announcement', [NotificationController::class, 'sendSystemAnnouncement']);
    });
});

// Legacy notification routes (for backward compatibility)
Route::get('/notifications', [NotificationController::class, 'index']);
Route::middleware('auth:sanctum')->post('/notifications', [NotificationController::class, 'store']);
Route::middleware('auth:sanctum')->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::middleware('auth:sanctum')->delete('/notifications/{id}', [NotificationController::class, 'destroy']);

// ============================================
// API ROUTES - Service Providers
// ============================================

Route::prefix('v1/api/service-providers')->group(function () {
    // Public Routes (specific routes first)
    Route::get('/search', [ServiceProviderController::class, 'getServiceProviders']);
    Route::get('/nearby', [ServiceProviderController::class, 'getProvidersByLocation']);
    Route::get('/statistics', [ServiceProviderController::class, 'getStatistics']);
    Route::get('/categories', [ServiceProviderController::class, 'getCategories']);
    Route::get('/', [ServiceProviderController::class, 'getServiceProviders']);

    // Wildcard routes last
    Route::get('/{id}', [ServiceProviderController::class, 'getServiceProvider']);
    Route::get('/{id}/reviews', [ServiceProviderController::class, 'getReviews']);

    // Authenticated Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [ServiceProviderController::class, 'createServiceProvider']);
        Route::put('/{id}', [ServiceProviderController::class, 'updateServiceProvider']);
        Route::delete('/{id}', [ServiceProviderController::class, 'deleteServiceProvider']);

        Route::post('/categories', [ServiceProviderController::class, 'createCategory']);
        Route::put('/categories/{id}', [ServiceProviderController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [ServiceProviderController::class, 'deleteCategory']);

        Route::post('/{id}/gallery', [ServiceProviderController::class, 'addGalleryImages']);
        Route::put('/gallery/{imageId}', [ServiceProviderController::class, 'updateGalleryImage']);
        Route::delete('/gallery/{imageId}', [ServiceProviderController::class, 'deleteGalleryImage']);

        Route::post('/{id}/offerings', [ServiceProviderController::class, 'addOffering']);
        Route::put('/offerings/{offeringId}', [ServiceProviderController::class, 'updateOffering']);
        Route::delete('/offerings/{offeringId}', [ServiceProviderController::class, 'deleteOffering']);

        Route::post('/{id}/reviews', [ServiceProviderController::class, 'addReview']);
        Route::put('/reviews/{reviewId}', [ServiceProviderController::class, 'updateReviewStatus']);
        Route::delete('/reviews/{reviewId}', [ServiceProviderController::class, 'deleteReview']);

        Route::post('/{id}/assign-plan', [ServiceProviderController::class, 'assignPlan']);
        Route::delete('/{id}/cancel-plan', [ServiceProviderController::class, 'cancelPlan']);
        Route::get('/{id}/plan-status', [ServiceProviderController::class, 'getPlanStatus']);
    });
});

// ============================================
// API ROUTES - Banner Ads
// ============================================

Route::prefix('v1/api/banner-ads')->group(function () {
    // Public Routes
    Route::get('/active', [BannerAdController::class, 'getActiveForDisplay']);
    Route::post('/{id}/click', [BannerAdController::class, 'recordClick']);

    // Authenticated Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [BannerAdController::class, 'index']);
        Route::post('/', [BannerAdController::class, 'store']);
        Route::get('/{id}', [BannerAdController::class, 'show']);
        Route::put('/{id}', [BannerAdController::class, 'update']);
        Route::delete('/{id}', [BannerAdController::class, 'destroy']);
        Route::patch('/{id}/pause', [BannerAdController::class, 'pause']);
        Route::patch('/{id}/resume', [BannerAdController::class, 'resume']);
        Route::post('/{id}/boost', [BannerAdController::class, 'boost']);
        Route::get('/{id}/analytics', [BannerAdController::class, 'analytics']);
        Route::post('/upload-image', [BannerAdController::class, 'uploadImage']);
    });

    // Admin Routes
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/pending-approval', [BannerAdController::class, 'pendingApproval']);
        Route::patch('/{id}/approve', [BannerAdController::class, 'approve']);
        Route::patch('/{id}/reject', [BannerAdController::class, 'reject']);
    });
});

// ============================================
// API ROUTES - App Version
// ============================================

Route::prefix('app')->group(function () {
    // Get current app version
    Route::get('/version', [AppVersionController::class, 'getCurrentVersion']);

    // Check if app needs update
    Route::post('/version/check', [AppVersionController::class, 'checkVersion']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Update app version (Admin only)
    Route::post('/version/update', [AppVersionController::class, 'updateVersion']);
});

// ============================================
// LEGACY ROUTES (For Backward Compatibility)
// ============================================

// Legacy projects routes
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);
Route::middleware('auth:sanctum')->post('/projects', [ProjectController::class, 'store']);
