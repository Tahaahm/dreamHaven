@include('layouts.sidebar')

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, sans-serif;
        background: #ffffff;
        color: #1a1a1a;
    }

    .profile-container {
        min-height: 100vh;
        background: #ffffff;
        padding: 0;
    }

    .profile-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 24px;
    }

    /* Header */
    .profile-header {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 40px;
        margin-bottom: 32px;
    }

    .profile-top {
        display: flex;
        gap: 32px;
        align-items: flex-start;
        margin-bottom: 32px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        flex-shrink: 0;
    }

    .profile-info {
        flex: 1;
    }

    .profile-name {
        font-size: 28px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }

    .profile-role {
        font-size: 16px;
        color: #6b7280;
        margin-bottom: 16px;
    }

    .profile-actions {
        display: flex;
        gap: 12px;
    }

    .btn-edit {
        padding: 10px 20px;
        background: #303b97;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-edit:hover {
        background: #1e2660;
        color: white;
    }

    /* Stats */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        padding-top: 32px;
        border-top: 1px solid #e5e7eb;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        display: block;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 13px;
        color: #6b7280;
    }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 32px;
    }

    /* Card */
    .card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 24px;
    }

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-title {
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-title i {
        color: #6b7280;
        font-size: 14px;
    }

    .card-body {
        padding: 24px;
    }

    /* Info Rows */
    .info-row {
        padding: 16px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-row:first-child {
        padding-top: 0;
    }

    .info-label {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        display: block;
    }

    .info-value {
        font-size: 14px;
        color: #111827;
        font-weight: 500;
    }

    .info-value a {
        color: #303b97;
        text-decoration: none;
    }

    .info-value a:hover {
        text-decoration: underline;
    }

    /* Bio */
    .bio-text {
        font-size: 15px;
        line-height: 1.7;
        color: #374151;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-warning {
        background: #fef3c7;
        color: #92400e;
    }

    /* Social Links */
    .social-links {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .social-link {
        width: 48px;
        height: 48px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
        background: white;
        text-decoration: none;
    }

    .social-link:hover {
        border-color: #303b97;
        background: #f9fafb;
    }

    .social-link i {
        font-size: 20px;
        color: #6b7280;
    }

    .social-link:hover i {
        color: #303b97;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .profile-wrapper {
            padding: 20px 16px;
        }

        .profile-header {
            padding: 24px;
        }

        .profile-top {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .profile-actions {
            width: 100%;
            flex-direction: column;
        }

        .btn-edit {
            width: 100%;
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .profile-name {
            font-size: 24px;
        }
    }
</style>

@php
$currentUser = Auth::guard('agent')->check()
    ? Auth::guard('agent')->user()
    : auth()->user();

$isAgent = Auth::guard('agent')->check();
@endphp

<div class="profile-container">
    <div class="profile-wrapper">
        @if($currentUser)
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-top">
                    <img
                        src="{{ $currentUser && $currentUser->profile_image
                            ? asset('storage/' . ltrim($currentUser->profile_image, '/'))
                            : asset('property_images/IMG_0697.JPG') }}"
                        alt="Profile Photo"
                        class="profile-avatar"
                        onerror="this.src='{{ asset('property_images/IMG_0697.JPG') }}'"
                    >

                    <div class="profile-info">
                        <h1 class="profile-name">{{ $currentUser->agent_name ?? $currentUser->username }}</h1>
                        <p class="profile-role">{{ $isAgent ? 'Real Estate Agent' : 'Member' }}</p>

                        <div class="profile-actions">
                            <a href="{{ Auth::guard('agent')->check()
                                ? route('agent.edit', ['id' => $currentUser->id])
                                : route('profile.edit', ['id' => $currentUser->id])
                            }}" class="btn-edit">
                                <i class="fas fa-edit"></i>
                                Edit Profile
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                @if($isAgent)
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-value">{{ $currentUser->properties_sold ?? 0 }}</span>
                        <span class="stat-label">Properties Sold</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ number_format($currentUser->overall_rating ?? 0, 1) }}</span>
                        <span class="stat-label">Rating</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $currentUser->properties_uploaded_this_month ?? 0 }}</span>
                        <span class="stat-label">This Month</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $currentUser->remaining_property_uploads ?? 0 }}</span>
                        <span class="stat-label">Remaining</span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Left Column -->
                <div>
                    <!-- Bio -->
                    @if(!empty($currentUser->bio) || !empty($currentUser->agent_bio))
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-user"></i>
                                About
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="bio-text">{{ $currentUser->agent_bio ?? $currentUser->bio }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Contact Information -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-address-card"></i>
                                Contact Information
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="info-label">Email Address</span>
                                <span class="info-value">
                                    <a href="mailto:{{ $currentUser->primary_email ?? $currentUser->email ?? 'N/A' }}">
                                        {{ $currentUser->primary_email ?? $currentUser->email ?? 'N/A' }}
                                    </a>
                                </span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Phone Number</span>
                                <span class="info-value">{{ $currentUser->primary_phone ?? $currentUser->phone ?? 'N/A' }}</span>
                            </div>

                            @if(!empty($currentUser->address) || !empty($currentUser->office_address))
                            <div class="info-row">
                                <span class="info-label">Address</span>
                                <span class="info-value">{{ $currentUser->office_address ?? $currentUser->address }}</span>
                            </div>
                            @endif

                            @if(!empty($currentUser->city))
                            <div class="info-row">
                                <span class="info-label">City</span>
                                <span class="info-value">{{ $currentUser->city }}</span>
                            </div>
                            @endif

                            @if(!empty($currentUser->state))
                            <div class="info-row">
                                <span class="info-label">State</span>
                                <span class="info-value">{{ $currentUser->state }}</span>
                            </div>
                            @endif

                            @if(!empty($currentUser->zip_code))
                            <div class="info-row">
                                <span class="info-label">Zip Code</span>
                                <span class="info-value">{{ $currentUser->zip_code }}</span>
                            </div>
                            @endif

                            @if(!empty($currentUser->company_name))
                            <div class="info-row">
                                <span class="info-label">Company</span>
                                <span class="info-value">{{ $currentUser->company_name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <!-- Account Details -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-cog"></i>
                                Account Details
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <span class="info-label">Account Type</span>
                                <span class="info-value">{{ $isAgent ? 'Agent Account' : 'User Account' }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Status</span>
                                <span class="info-value">
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check-circle"></i>
                                        Active
                                    </span>
                                </span>
                            </div>

                            @if($isAgent && isset($currentUser->is_verified))
                            <div class="info-row">
                                <span class="info-label">Verification</span>
                                <span class="info-value">
                                    @if($currentUser->is_verified)
                                        <span class="status-badge status-active">
                                            <i class="fas fa-check-circle"></i>
                                            Verified
                                        </span>
                                    @else
                                        <span class="status-badge status-warning">
                                            <i class="fas fa-clock"></i>
                                            Pending
                                        </span>
                                    @endif
                                </span>
                            </div>
                            @endif

                            <div class="info-row">
                                <span class="info-label">Two-Factor Auth</span>
                                <span class="info-value">
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-times-circle"></i>
                                        Not Enabled
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Social Links -->
                    @if(!empty($currentUser->facebook) || !empty($currentUser->twitter) || !empty($currentUser->instagram) || !empty($currentUser->website))
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-share-alt"></i>
                                Social Links
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="social-links">
                                @if(!empty($currentUser->facebook))
                                    <a href="{{ $currentUser->facebook }}" target="_blank" class="social-link" title="Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                @endif

                                @if(!empty($currentUser->twitter))
                                    <a href="{{ $currentUser->twitter }}" target="_blank" class="social-link" title="Twitter">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                @endif

                                @if(!empty($currentUser->instagram))
                                    <a href="{{ $currentUser->instagram }}" target="_blank" class="social-link" title="Instagram">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                @endif

                                @if(!empty($currentUser->website))
                                    <a href="{{ $currentUser->website }}" target="_blank" class="social-link" title="Website">
                                        <i class="fas fa-globe"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>No user is logged in</p>
            </div>
        @endif
    </div>
</div>
