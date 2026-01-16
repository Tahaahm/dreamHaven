<?php

use App\Http\Controllers\AdminController;
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
use App\Http\Controllers\AgentAuthController;
use App\Http\Controllers\AppVersionController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OfficeAuthController;
use App\Http\Controllers\SubscriptionPlanController;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\AgentOrAdmin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Middleware\EnsureUserIsVerified;

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

Route::get('/properties/search', [PropertyController::class, 'searchView'])->name(name: 'properties.search');
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

    Route::get('/agent/profile-page', [AgentController::class, 'showProfilePage'])->name('agent.profile.page');
    Route::put('/agent/profile-update', [AgentController::class, 'updateAgentProfileNew'])->name('agent.profile.update');
    Route::put('/agent/password-update', [AgentController::class, 'updateAgentPassword'])->name('agent.password.update');

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
        Route::get('/agent/real-estate-office-profile/{id}', [RealEstateOfficeController::class, 'profile'])->name('agent.office.public.profile');
    });

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
        // Route::get('/admin/entities', [AdminController::class, 'entitiesList'])->name('admin.entities.list');
        // Route::get('/admin/entity-detail/{type}/{id}', [AuthController::class, 'entityDetail'])->name('admin.entity.detail');
        // Route::post('/admin/entity-suspend/{type}/{id}', [AuthController::class, 'suspendEntity'])->name('admin.entity.suspend');
        // Route::delete('/admin/entity-delete/{type}/{id}', [AuthController::class, 'deleteEntity'])->name('admin.entity.delete');
        // Route::get('/admin/user/{id}', [AdminController::class, 'userDetail'])->name('admin.users.show');
        // Route::get('/admin/agent/{id}', [AdminController::class, 'agentDetail'])->name('admin.agents.show');
        // Route::post('/admin/entity/suspend/{id}', [AdminController::class, 'suspendEntity'])->name('admin.entity.suspend');
        // Route::delete('/admin/entity/delete/{id}', [AdminController::class, 'deleteEntity'])->name('admin.entity.delete');

        // Property Management
        // Route::get('/admin/properties', function () {
        //     if (!auth()->user() || auth()->user()->role !== 'admin') {
        //         abort(403, 'Unauthorized');
        //     }
        //     return app(AdminController::class)->adminProperties(request());
        // })->name('admin.properties');

        // Route::delete('/admin/properties/{id}', function ($id) {
        //     if (!auth()->user() || auth()->user()->role !== 'admin') {
        //         abort(403, 'Unauthorized');
        //     }
        //     return app(AdminController::class)->deleteProperty($id);
        // })->name('admin.properties.delete');
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
        Route::post('/check-availability', [UserController::class, 'checkAvailability']);

        Route::post('/agent/login', [AuthController::class, 'loginAgent']);
        Route::post('/office/login', [AuthController::class, 'loginRealEstateOffice']);
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
            Route::delete('/profile', [UserController::class, 'deleteAccount']);



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
    Route::get('/', [RealEstateOfficeController::class, 'index']);
    Route::get('/{id}', [RealEstateOfficeController::class, 'show']);
    Route::get('/{id}/properties', [RealEstateOfficeController::class, 'fetchProperties']);
    Route::post('/login', [RealEstateOfficeController::class, 'login']);
    Route::post('/', [RealEstateOfficeController::class, 'store']);
    Route::put('/{id}', [RealEstateOfficeController::class, 'update']);
    Route::delete('/{id}', [RealEstateOfficeController::class, 'destroy']);
});

// ============================================
// API ROUTES - Agents
// ============================================

// In web.php - REPLACE the entire agents route section with this:

Route::prefix('v1/api/agents')->group(function () {
    // ✅ SPECIFIC ROUTES FIRST (before catch-all /{id})
    Route::get('/search', [AgentController::class, 'search']);
    Route::get('/top-rated', [AgentController::class, 'getTopRated']);
    Route::get('/nearby', [AgentController::class, 'getNearbyAgents']);
    Route::get('/company/{companyId}', [AgentController::class, 'getAgentsByCompany']);

    // ✅ AUTHENTICATED SPECIFIC ROUTES (must be before /{id})
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [AgentController::class, 'getAgentProfile']);
        Route::get('/dashboard-stats', [AgentController::class, 'getDashboardStats']);
        Route::get('/my-properties', [AgentController::class, 'getMyProperties']);
        Route::post('/', [AgentController::class, 'store']);
        Route::put('/{id}', [AgentController::class, 'update']);
        Route::patch('/{id}', [AgentController::class, 'update']);
        Route::delete('/{id}', [AgentController::class, 'destroy']);
        Route::patch('/{id}/verify', [AgentController::class, 'toggleVerification']);
        Route::patch('/{id}/remove-company', [AgentController::class, 'removeFromCompany']);
    });

    // ✅ PUBLIC ROUTES
    Route::get('/', [AgentController::class, 'index']);
    Route::post('/login', [AgentController::class, 'login']);
    Route::post('/users/{user_id}/convert-to-agent', [AgentController::class, 'createFromUser']);

    // ✅ CATCH-ALL ROUTES LAST (must be at the end!)
    Route::get('/{id}', [AgentController::class, 'show']);
});

// ============================================
// API ROUTES - Location
// ============================================

Route::prefix('v1/api/location')->group(function () {
    Route::get('/branches', [LocationController::class, 'getBranches']);
    Route::get('/cities', [LocationController::class, 'getCities']);
    Route::get('/branches/{id}', [LocationController::class, 'getBranch']);
    Route::post('/branches', [LocationController::class, 'createBranch']);
    Route::put('/branches/{id}', [LocationController::class, 'updateBranch']);
    Route::delete('/branches/{id}', [LocationController::class, 'deleteBranch']);
    Route::get('/branches/{branchId}/areas', [LocationController::class, 'getAreasByBranch']);
    Route::get('/areas/{id}', [LocationController::class, 'getArea']);
    Route::post('/areas', [LocationController::class, 'createArea']);
    Route::put('/areas/{id}', [LocationController::class, 'updateArea']);
    Route::delete('/areas/{id}', [LocationController::class, 'deleteArea']);
    Route::get('/search', [LocationController::class, 'searchLocations']);
    Route::get('/stats', [LocationController::class, 'getLocationStats']);
});

// ============================================
// API ROUTES - Properties
// ============================================
Route::prefix('v1/api/properties')->group(function () {
    // ============================================
    // PUBLIC ROUTES - Property Discovery
    // ============================================
    Route::get('/search', [PropertyController::class, 'search']);
    Route::get('/nearby', [PropertyController::class, 'nearby']);

    // Property Discovery Endpoints
    Route::get('/featured', [PropertyController::class, 'getFeatured']);
    Route::get('/recommended', [PropertyController::class, 'getRecommended']); // ✅ NEW
    Route::get('/boosted', [PropertyController::class, 'getBoosted']);
    Route::get('/recent', [PropertyController::class, 'getRecent']); // ✅ NEW
    Route::get('/popular', [PropertyController::class, 'getPopular']); // ✅ NEW

    Route::get('/statistics', [PropertyController::class, 'getStatistics']);
    Route::post('/map', [PropertyController::class, 'getMapProperties']);
    Route::get('/owner/{ownerType}/{ownerId}', [PropertyController::class, 'getByOwner'])
        ->where(['ownerType' => 'User|Agent|RealEstateOffice']);
    Route::get('/', [PropertyController::class, 'index']);
    Route::get('/{id}', [PropertyController::class, 'show']);
    Route::post('/store', [PropertyController::class, 'store']);

    // ============================================
    // AUTHENTICATED ROUTES - User Actions
    // ============================================
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::put('/update/mobile/{id}', [PropertyController::class, 'updateMobile']);
        Route::patch('/update/mobile/{id}', [PropertyController::class, 'updateMobile']);

        Route::post('/', [PropertyController::class, 'store']);
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

    // ============================================
    // ADMIN/AGENT ROUTES - Management
    // ============================================
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

    // ============================================
    // SUPER ADMIN ROUTES - System Management
    // ============================================
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
    Route::get('/featured', [ProjectController::class, 'featured']);
    Route::get('/developer/{developerId}', [ProjectController::class, 'byDeveloper']);
    Route::get('/', [ProjectController::class, 'index']);
    Route::get('/{id}', [ProjectController::class, 'show']);

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

// ============================================
// API ROUTES - Service Providers
// ============================================

Route::prefix('v1/api/service-providers')->group(function () {
    Route::get('/search', [ServiceProviderController::class, 'getServiceProviders']);
    Route::get('/nearby', [ServiceProviderController::class, 'getProvidersByLocation']);
    Route::get('/statistics', [ServiceProviderController::class, 'getStatistics']);
    Route::get('/categories', [ServiceProviderController::class, 'getCategories']);
    Route::get('/', [ServiceProviderController::class, 'getServiceProviders']);
    Route::get('/{id}', [ServiceProviderController::class, 'getServiceProvider']);
    Route::get('/{id}/reviews', [ServiceProviderController::class, 'getReviews']);

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
    Route::get('/active', [BannerAdController::class, 'getActiveForDisplay']);
    Route::post('/{id}/click', [BannerAdController::class, 'recordClick']);

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
    Route::get('/version', [AppVersionController::class, 'getCurrentVersion']);
    Route::post('/version/check', [AppVersionController::class, 'checkVersion']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/version/update', [AppVersionController::class, 'updateVersion']);
});

// ============================================
// API ROUTES - Subscription Plans
// ============================================

Route::prefix('subscription-plans')->group(function () {
    Route::get('/', [SubscriptionPlanController::class, 'index']);
    Route::post('/', [SubscriptionPlanController::class, 'store']);
    Route::get('/{id}', [SubscriptionPlanController::class, 'show']);
    Route::put('/{id}', [SubscriptionPlanController::class, 'update']);
    Route::patch('/{id}', [SubscriptionPlanController::class, 'update']);
    Route::delete('/{id}', [SubscriptionPlanController::class, 'destroy']);
    Route::get('/type/{type}', [SubscriptionPlanController::class, 'getByType']);
    Route::patch('/{id}/toggle-active', [SubscriptionPlanController::class, 'toggleActive']);
});

// ============================================
// LEGACY & ADDITIONAL WEB ROUTES
// ============================================

Route::get('/api/v1/users/{id}', [UserController::class, 'getUserById']);
Route::delete('/property/{property_id}', [PropertyController::class, 'destroy'])->name('property.delete');
Route::get('/property/{property_id}/edit', [PropertyController::class, 'edit'])->name('property.edit');
Route::put('/property/{id}', [PropertyController::class, 'update'])->name('property.update');
Route::post('/property/{property_id}/remove-image', [PropertyController::class, 'removeImage'])->name('property.removeImage');
Route::get('/properties', [PropertyController::class, 'showList'])->name('property.list');

Route::get('/profile', [AuthController::class, 'showProfile'])->middleware(\App\Http\Middleware\AgentOrAdmin::class)->name('admin.profile');

Route::group(['prefix' => 'admin'], function () {
    Route::middleware('auth')->group(function () {
        Route::get('/properties', function () {
            if (!Auth::user() || Auth::user()->role !== 'admin') {
                abort(403, 'Unauthorized');
            }
            return app(AdminController::class)->adminProperties(request());
        })->name('admin.properties');

        Route::delete('/properties/{id}', function ($id) {
            if (!Auth::user() || Auth::user()->role !== 'admin') {
                abort(403, 'Unauthorized');
            }
            return app(AdminController::class)->deleteProperty($id);
        })->name('admin.properties.delete');
    });
});

// ============================================
// USER ROUTES (Auth Required)
// ============================================

Route::middleware(['auth:web,agent'])->group(function () {
    Route::get('/my-appointments', [AppointmentController::class, 'showAppointmentsPage'])->name('user.appointments');
    Route::get('/my-notifications', [NotificationController::class, 'showNotificationsPage'])->name('user.notifications');
    Route::get('/user/profile', [UserController::class, 'showProfile'])->name('user.profile');
    Route::put('/user/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::put('/user/password', [UserController::class, 'updatePassword'])->name('user.password.update');
    Route::get('/notifications/read/{id}', [NotificationController::class, 'markAsReadWeb'])->name('notifications.read');
    Route::get('/notifications/delete/{id}', [NotificationController::class, 'deleteWeb'])->name('notifications.delete');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancelAppointment'])->name('appointments.cancel');
    Route::post('/appointments/{id}/reschedule', [AppointmentController::class, 'rescheduleAppointmentWeb'])->name('appointments.reschedule');
});
// ============================================
// REAL ESTATE OFFICE - WEB AUTHENTICATION & MANAGEMENT
// ============================================

Route::prefix('office')->name('office.')->group(function () {

    // ========== PUBLIC ROUTES (Guest only) ==========
    Route::middleware('guest:office')->group(function () {
        Route::get('/login', [OfficeAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [OfficeAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [OfficeAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [OfficeAuthController::class, 'register'])->name('register.submit');
    });

    // ========== PROTECTED ROUTES (Office only) ==========
    Route::middleware('auth.office')->group(function () {
        Route::get('/agents', [OfficeAuthController::class, 'showAgents'])->name('agents');
        Route::get('/agents/add', [OfficeAuthController::class, 'showAddAgent'])->name('agents.add');
        Route::get('/agents/search', [OfficeAuthController::class, 'searchAgents'])->name('agents.search');
        Route::post('/agents/store', [OfficeAuthController::class, 'storeAgent'])->name('agents.store');
        Route::delete('/agents/{id}/remove', [OfficeAuthController::class, 'removeAgent'])->name('agents.remove');

        Route::get('/banners', [OfficeAuthController::class, 'showBanners'])->name('banners');
        Route::get('/banner/add', [OfficeAuthController::class, 'showAddBanner'])->name('banner.add');
        Route::post('/banner/store', [OfficeAuthController::class, 'storeBanner'])->name('banner.store');
        Route::get('/banner/{id}/edit', [OfficeAuthController::class, 'editBanner'])->name('banner.edit');
        Route::put('/banner/{id}', [OfficeAuthController::class, 'updateBanner'])->name('banner.update');
        Route::delete('/banner/{id}', [OfficeAuthController::class, 'deleteBanner'])->name('banner.delete');
        Route::post('/banner/{id}/pause', [OfficeAuthController::class, 'pauseBanner'])->name('banner.pause');
        Route::post('/banner/{id}/resume', [OfficeAuthController::class, 'resumeBanner'])->name('banner.resume');
        Route::get('/banner/{id}/analytics', [OfficeAuthController::class, 'bannerAnalytics'])->name('banner.analytics');

        Route::post('/logout', [OfficeAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [OfficeAuthController::class, 'dashboard'])->name('dashboard');

        Route::get('/profile', [OfficeAuthController::class, 'showProfile'])->name('profile');
        Route::put('/profile', [OfficeAuthController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [OfficeAuthController::class, 'updatePassword'])->name('password.update');

        Route::get('/properties', [OfficeAuthController::class, 'showProperties'])->name('properties');
        Route::get('/property/upload', [OfficeAuthController::class, 'showPropertyUpload'])->name('property.upload');
        Route::post('/property/store', [OfficeAuthController::class, 'storeProperty'])->name('property.store');
        Route::get('/property/{id}/edit', [OfficeAuthController::class, 'editProperty'])->name('property.edit');
        Route::put('/property/{id}', [OfficeAuthController::class, 'updateProperty'])->name('property.update');
        Route::delete('/property/{id}', [OfficeAuthController::class, 'deleteProperty'])->name('property.delete');

        Route::get('/appointments', [OfficeAuthController::class, 'showAppointments'])->name('appointments');
        Route::put('/appointment/{id}/status', [OfficeAuthController::class, 'updateAppointmentStatus'])->name('appointment.status');

        Route::get('/projects', [OfficeAuthController::class, 'projects'])->name('projects');
        Route::get('/project/add', [OfficeAuthController::class, 'showProjectAdd'])->name('project.add');
        Route::post('/project/store', [OfficeAuthController::class, 'storeProject'])->name('project.store');
        Route::get('/project/{id}/edit', [OfficeAuthController::class, 'editProject'])->name('project.edit');
        Route::put('/project/{id}', [OfficeAuthController::class, 'updateProject'])->name('project.update');
        Route::delete('/project/{id}', [OfficeAuthController::class, 'deleteProject'])->name('project.delete');

        Route::get('/subscriptions', [OfficeAuthController::class, 'showSubscriptions'])->name('subscriptions');
        Route::post('/subscription/subscribe/{id}', [OfficeAuthController::class, 'subscribe'])->name('subscription.subscribe');
        Route::get('/subscription/confirm', [OfficeAuthController::class, 'confirmSubscription'])->name('subscription.confirm');
        Route::post('/subscription/process', [OfficeAuthController::class, 'processSubscription'])->name('subscription.process');
        Route::post('/subscription/cancel', [OfficeAuthController::class, 'cancelSubscription'])->name('subscription.cancel');

        Route::get('/leads', [OfficeAuthController::class, 'showLeads'])->name('leads');
        Route::post('/lead/{id}/status', [OfficeAuthController::class, 'updateLeadStatus'])->name('lead.status');

        Route::get('/offers', [OfficeAuthController::class, 'showOffers'])->name('offers');
        Route::post('/offer/create', [OfficeAuthController::class, 'createOffer'])->name('offer.create');

        Route::get('/agreements', [OfficeAuthController::class, 'showAgreements'])->name('agreements');
        Route::get('/activities', [OfficeAuthController::class, 'showActivities'])->name('activities');
        Route::get('/contacts', [OfficeAuthController::class, 'showContacts'])->name('contacts');
        Route::get('/campaigns', [OfficeAuthController::class, 'showCampaigns'])->name('campaigns');
        Route::get('/documents', [OfficeAuthController::class, 'showDocuments'])->name('documents');
    });
});



Route::prefix('agent')->name('agent.')->group(function () {
    // ========== GUEST ROUTES ==========
    Route::middleware('guest:agent')->group(function () {
        Route::get('/login', [AgentAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AgentAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [AgentAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AgentAuthController::class, 'register'])->name('register.submit');
    });

    // ========== AUTHENTICATED ROUTES ==========
    // ⭐ CHANGED: auth:agent → auth.agent
    Route::middleware('auth.agent')->group(function () {
        Route::post('/logout', [AgentAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [AgentAuthController::class, 'showDashboard'])->name('dashboard');

        Route::get('/properties', [AgentAuthController::class, 'showProperties'])->name('properties');
        Route::get('/properties/add', [AgentAuthController::class, 'showAddProperty'])->name('property.add');
        Route::post('/properties/add', [AgentAuthController::class, 'storeProperty'])->name('property.store');
        Route::get('/properties/{id}/edit', [AgentAuthController::class, 'showEditProperty'])->name('property.edit');
        Route::put('/properties/{id}', [AgentAuthController::class, 'updateProperty'])->name('property.update');
        Route::delete('/properties/{id}', [AgentAuthController::class, 'deleteProperty'])->name('property.delete');

        Route::get('/subscriptions', [AgentAuthController::class, 'showSubscriptions'])->name('subscriptions');

        Route::get('/appointments', [AgentAuthController::class, 'showAppointments'])->name('appointments');
        Route::put('/appointment/{id}/status', [AgentAuthController::class, 'updateAppointmentStatus'])->name('appointment.status');

        Route::get('/banners', [AgentAuthController::class, 'showBanners'])->name('banners');
        Route::get('/banner/add', [AgentAuthController::class, 'showAddBanner'])->name('banner.add');
        Route::post('/banner/store', [AgentAuthController::class, 'storeBanner'])->name('banner.store');
        Route::get('/banner/{id}/edit', [AgentAuthController::class, 'editBanner'])->name('banner.edit');
        Route::put('/banner/{id}', [AgentAuthController::class, 'updateBanner'])->name('banner.update');
        Route::delete('/banner/{id}', [AgentAuthController::class, 'deleteBanner'])->name('banner.delete');
        Route::post('/banner/{id}/pause', [AgentAuthController::class, 'pauseBanner'])->name('banner.pause');
        Route::post('/banner/{id}/resume', [AgentAuthController::class, 'resumeBanner'])->name('banner.resume');
        Route::get('/banner/{id}/analytics', [AgentAuthController::class, 'bannerAnalytics'])->name('banner.analytics');

        Route::get('/profile/edit', [AgentAuthController::class, 'showEditProfile'])->name('profile.edit');
        Route::put('/profile/update', [AgentAuthController::class, 'updateProfile'])->name('profile.update');
        Route::get('/profile/{id}', [AgentAuthController::class, 'showProfile'])->name('profile.show');

        Route::get('/password/change', [AgentAuthController::class, 'showChangePassword'])->name('password.change');
        Route::put('/password/update', [AgentAuthController::class, 'updatePassword'])->name('password.update');
    });
});

// ========== PUBLIC ROUTES ==========
Route::get('/account-deletion', function () {
    return view('account-deletion');
});

Route::get('/agent/test', function () {
    dd('AGENT TEST ROUTE WORKS');
});
Route::get('/agent/{id}', [AgentAuthController::class, 'showProfile'])->name('agent.profile');



///admin role



// ============================================
// ADMIN PANEL ROUTES
// ============================================

// ============================================
// ADMIN PANEL ROUTES
// ============================================

Route::prefix('admin')->name('admin.')->group(function () {

    // ========== GUEST ROUTES (Login) ==========
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminController::class, 'login'])->name('login.post');
        Route::get('/register', [AdminController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AdminController::class, 'register'])->name('register.post');
    });

    // ========== AUTHENTICATED ADMIN ROUTES ==========
    Route::middleware(['auth:admin'])->group(function () {

        // Logout
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/stats', [AdminController::class, 'getStats'])->name('stats');
        Route::get('/chart-data', [AdminController::class, 'getChartData'])->name('chart.data');

        // ========== USERS MANAGEMENT ==========
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminController::class, 'usersIndex'])->name('index');
            Route::get('/create', [AdminController::class, 'usersCreate'])->name('create');
            Route::post('/store', [AdminController::class, 'usersStore'])->name('store');
            Route::get('/{id}', [AdminController::class, 'usersShow'])->name('show');
            Route::get('/{id}/edit', [AdminController::class, 'usersEdit'])->name('edit');
            Route::put('/{id}', [AdminController::class, 'usersUpdate'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'usersDelete'])->name('delete');
            Route::post('/{id}/suspend', [AdminController::class, 'usersSuspend'])->name('suspend');
            Route::post('/{id}/activate', [AdminController::class, 'usersActivate'])->name('activate');

            Route::put('/{id}/update-image', [AdminController::class, 'updateUserImage'])->name('update-image');
        });

        // ========== AGENTS MANAGEMENT ==========
        Route::prefix('agents')->name('agents.')->group(function () {
            Route::get('/', [AdminController::class, 'agentsIndex'])->name('index');
            Route::get('/pending', [AdminController::class, 'agentsPending'])->name('pending');
            Route::get('/{id}', [AdminController::class, 'agentsShow'])->name('show');
            Route::get('/{id}/edit', [AdminController::class, 'agentsEdit'])->name('edit');
            Route::put('/{id}', [AdminController::class, 'agentsUpdate'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'agentsDelete'])->name('delete');
            Route::post('/{id}/verify', [AdminController::class, 'agentsVerify'])->name('verify');
            Route::post('/{id}/suspend', [AdminController::class, 'agentsSuspend'])->name('suspend');
        });

        // ========== REAL ESTATE OFFICES MANAGEMENT ==========
        Route::prefix('offices')->name('offices.')->group(function () {
            Route::get('/', [AdminController::class, 'officesIndex'])->name('index');
            Route::get('/pending', [AdminController::class, 'officesPending'])->name('pending');
            Route::get('/{id}', [AdminController::class, 'officesShow'])->name('show');
            Route::get('/{id}/edit', [AdminController::class, 'officesEdit'])->name('edit');
            Route::put('/{id}', [AdminController::class, 'officesUpdate'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'officesDelete'])->name('delete');
            Route::post('/{id}/verify', [AdminController::class, 'officesVerify'])->name('verify');
            Route::post('/{id}/suspend', [AdminController::class, 'officesSuspend'])->name('suspend');
        });

        // ========== PROPERTIES MANAGEMENT ==========
        Route::prefix('properties')->name('properties.')->group(function () {
            Route::get('/', [AdminController::class, 'propertiesIndex'])->name('index');
            Route::get('/pending', [AdminController::class, 'propertiesPending'])->name('pending');
            Route::get('/{id}', [AdminController::class, 'propertiesShow'])->name('show');
            Route::get('/{id}/edit', [AdminController::class, 'propertiesEdit'])->name('edit');
            Route::put('/{id}', [AdminController::class, 'propertiesUpdate'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'propertiesDelete'])->name('delete');
            Route::post('/{id}/approve', [AdminController::class, 'propertiesApprove'])->name('approve');
            Route::post('/{id}/reject', [AdminController::class, 'propertiesReject'])->name('reject');
            Route::post('/{id}/toggle-active', [AdminController::class, 'propertiesToggleActive'])->name('toggle.active');
        });

        // ========== PROJECTS MANAGEMENT ==========
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [AdminController::class, 'projectsIndex'])->name('index');
            Route::get('/{id}', [AdminController::class, 'projectsShow'])->name('show');
            Route::get('/{id}/edit', [AdminController::class, 'projectsEdit'])->name('edit');
            Route::put('/{id}', [AdminController::class, 'projectsUpdate'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'projectsDelete'])->name('delete');
            Route::post('/{id}/toggle-active', [AdminController::class, 'projectsToggleActive'])->name('toggle.active');
        });

        // ========== BANNERS MANAGEMENT ==========
        Route::prefix('banners')->name('banners.')->group(function () {
            Route::get('/', [AdminController::class, 'bannersIndex'])->name('index');
            Route::get('/create', [AdminController::class, 'bannersCreate'])->name('create');
            Route::post('/store', [AdminController::class, 'bannersStore'])->name('store');
            Route::get('/pending', [AdminController::class, 'bannersPending'])->name('pending');
            Route::get('/{id}', [AdminController::class, 'bannersShow'])->name('show');
            Route::get('/{id}/edit', [AdminController::class, 'bannersEdit'])->name('edit');
            Route::put('/{id}', [AdminController::class, 'bannersUpdate'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'bannersDelete'])->name('delete');
            Route::post('/{id}/approve', [AdminController::class, 'bannersApprove'])->name('approve');
            Route::post('/{id}/reject', [AdminController::class, 'bannersReject'])->name('reject');
            Route::post('/{id}/pause', [AdminController::class, 'bannersPause'])->name('pause');
            Route::post('/{id}/resume', [AdminController::class, 'bannersResume'])->name('resume');
        });

        // ========== SUBSCRIPTIONS MANAGEMENT ==========
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/', [AdminController::class, 'subscriptionsIndex'])->name('index');
            Route::get('/{id}', [AdminController::class, 'subscriptionsShow'])->name('show');
            Route::get('/{id}/edit', [AdminController::class, 'subscriptionsEdit'])->name('edit');
            Route::put('/{id}', [AdminController::class, 'subscriptionsUpdate'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'subscriptionsDelete'])->name('delete');
            Route::post('/{id}/cancel', [AdminController::class, 'subscriptionsCancel'])->name('cancel');
            Route::post('/{id}/renew', [AdminController::class, 'subscriptionsRenew'])->name('renew');
        });

        // ========== SUBSCRIPTION PLANS MANAGEMENT ==========
        // Inside your existing admin group...
        Route::prefix('subscription-plans')->name('subscription-plans.')->group(function () {
            Route::get('/', [AdminController::class, 'subscriptionPlansIndex'])->name('index');
            Route::get('/create', [AdminController::class, 'subscriptionPlansCreate'])->name('create');
            Route::post('/store', [AdminController::class, 'subscriptionPlansStore'])->name('store');
            Route::get('/{id}', [AdminController::class, 'subscriptionPlansShow'])->name('show');
            Route::get('/{id}/edit', [AdminController::class, 'subscriptionPlansEdit'])->name('edit');
            Route::put('/{id}', [AdminController::class, 'subscriptionPlansUpdate'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'subscriptionPlansDelete'])->name('delete');

            // FIX: Changed name from 'toggle.active' to 'toggle-active'
            Route::post('/{id}/toggle-active', [AdminController::class, 'subscriptionPlansToggleActive'])->name('toggle-active');
        });

        // ========== TRANSACTIONS ==========
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [AdminController::class, 'transactionsIndex'])->name('index');
            Route::get('/{id}', [AdminController::class, 'transactionsShow'])->name('show');
            Route::post('/{id}/approve', [AdminController::class, 'transactionsApprove'])->name('approve');
            Route::post('/{id}/reject', [AdminController::class, 'transactionsReject'])->name('reject');
        });

        // ========== APPOINTMENTS ==========
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', [AdminController::class, 'appointmentsIndex'])->name('index');
            Route::get('/{id}', [AdminController::class, 'appointmentsShow'])->name('show');
            Route::post('/{id}/cancel', [AdminController::class, 'appointmentsCancel'])->name('cancel');
            Route::delete('/{id}', [AdminController::class, 'appointmentsDelete'])->name('delete');
        });

        // ========== SERVICE PROVIDERS ==========
        Route::prefix('service-providers')->name('service-providers.')->group(function () {
            Route::get('/', [AdminController::class, 'serviceProvidersIndex'])->name('index');
            Route::get('/{id}', [AdminController::class, 'serviceProvidersShow'])->name('show');
            Route::get('/{id}/edit', [AdminController::class, 'serviceProvidersEdit'])->name('edit');
            Route::put('/{id}', [AdminController::class, 'serviceProvidersUpdate'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'serviceProvidersDelete'])->name('delete');
            Route::post('/{id}/verify', [AdminController::class, 'serviceProvidersVerify'])->name('verify');
        });

        // ========== REVIEWS & REPORTS ==========
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [AdminController::class, 'reviewsIndex'])->name('index');
            Route::get('/{id}', [AdminController::class, 'reviewsShow'])->name('show');
            Route::delete('/{id}', [AdminController::class, 'reviewsDelete'])->name('delete');
            Route::post('/{id}/approve', [AdminController::class, 'reviewsApprove'])->name('approve');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AdminController::class, 'reportsIndex'])->name('index');
            Route::get('/{id}', [AdminController::class, 'reportsShow'])->name('show');
            Route::post('/{id}/resolve', [AdminController::class, 'reportsResolve'])->name('resolve');
            Route::delete('/{id}', [AdminController::class, 'reportsDelete'])->name('delete');
        });

        // ========== SETTINGS ==========
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [AdminController::class, 'settingsIndex'])->name('index');
            Route::put('/update', [AdminController::class, 'settingsUpdate'])->name('update');
        });

        // ========== PROFILE ==========
        Route::get('/profile', [AdminController::class, 'profileShow'])->name('profile');
        Route::put('/profile', [AdminController::class, 'profileUpdate'])->name('profile.update');
        Route::put('/profile/password', [AdminController::class, 'profilePasswordUpdate'])->name('profile.password.update');
    });
});