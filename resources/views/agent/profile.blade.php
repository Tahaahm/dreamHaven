@include('layouts.sidebar')

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .profile-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 2rem;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .profile-wrapper {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Hero Section */
    .profile-hero {
        position: relative;
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .hero-background {
        height: 320px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .hero-background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url('{{ asset($backgroundImage ?? 'images/AdobeStock_565645717.jpeg') }}');
        background-size: cover;
        background-position: center;
        opacity: 0.3;
    }

    .hero-content {
        position: relative;
        padding: 2rem 3rem;
        margin-top: -120px;
    }

    .profile-main-info {
        display: flex;
        align-items: flex-end;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .profile-avatar {
        position: relative;
        flex-shrink: 0;
    }

    .avatar-image {
        width: 180px;
        height: 180px;
        border-radius: 24px;
        object-fit: cover;
        border: 6px solid white;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease;
    }

    .avatar-image:hover {
        transform: scale(1.05);
    }

    .profile-header-info {
        flex-grow: 1;
        padding-bottom: 1rem;
    }

    .profile-name {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 0.5rem;
    }

    .profile-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .edit-profile-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .edit-profile-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    /* Quick Stats */
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 2rem 3rem;
        background: #f8f9fa;
        border-radius: 16px;
        margin-bottom: 2rem;
    }

    .stat-card {
        text-align: center;
        padding: 1rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Main Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 2rem;
    }

    /* Contact Information Card */
    .info-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-title i {
        color: #667eea;
    }

    .info-row {
        display: flex;
        align-items: flex-start;
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #64748b;
        min-width: 140px;
        font-size: 0.9rem;
    }

    .info-value {
        color: #1a202c;
        flex-grow: 1;
        word-break: break-word;
    }

    /* Bio Section */
    .bio-section {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .bio-text {
        color: #475569;
        line-height: 1.8;
        font-size: 1rem;
    }

    /* Social Links */
    .social-links-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .social-links {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .social-link {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }

    .social-link:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .social-link img {
        width: 28px;
        height: 28px;
        filter: brightness(0) invert(1);
    }

    /* Account Status */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .profile-container {
            padding: 1rem;
        }

        .profile-main-info {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .profile-name {
            font-size: 2rem;
        }

        .hero-content {
            padding: 2rem 1.5rem;
        }

        .quick-stats {
            padding: 1.5rem;
        }

        .info-row {
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            min-width: auto;
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
            <!-- Hero Section -->
            <div class="profile-hero">
                <div class="hero-background"></div>
                
                <div class="hero-content">
                    <div class="profile-main-info">
                        <div class="profile-avatar">
                            <img 
                                src="{{ $currentUser && $currentUser->profile_image 
                                    ? asset('storage/' . ltrim($currentUser->profile_image, '/')) 
                                    : asset('property_images/IMG_0697.JPG') }}"
                                alt="Profile Photo" 
                                class="avatar-image"
                            >
                        </div>
                        
                        <div class="profile-header-info">
                            <h1 class="profile-name">{{ $currentUser->agent_name ?? $currentUser->username }}</h1>
                            <div class="profile-badge">
                                <i class="fas fa-star"></i>
                                {{ $isAgent ? 'Real Estate Agent' : 'Member' }}
                            </div>
                            <div>
                                <button class="edit-profile-btn"
                                    onclick="window.location.href='{{ Auth::guard('agent')->check()
                                        ? route('agent.edit', ['id' => $currentUser->id])
                                        : route('profile.edit', ['id' => $currentUser->id])
                                    }}'">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    @if($isAgent)
                    <div class="quick-stats">
                        <div class="stat-card">
                            <div class="stat-value">{{ $currentUser->properties_sold ?? 0 }}</div>
                            <div class="stat-label">Properties Sold</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ number_format($currentUser->overall_rating ?? 0, 1) }}</div>
                            <div class="stat-label">Rating</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ $currentUser->properties_uploaded_this_month ?? 0 }}</div>
                            <div class="stat-label">Listings This Month</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ $currentUser->remaining_property_uploads ?? 0 }}</div>
                            <div class="stat-label">Remaining Uploads</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="content-grid">
                <!-- Left Column -->
                <div>
                    <!-- Bio Section -->
                    @if(!empty($currentUser->bio) || !empty($currentUser->agent_bio))
                    <div class="bio-section">
                        <h2 class="card-title">
                            <i class="fas fa-user"></i>
                            About Me
                        </h2>
                        <p class="bio-text">{{ $currentUser->agent_bio ?? $currentUser->bio }}</p>
                    </div>
                    @endif

                    <!-- Contact Information -->
                    <div class="info-card">
                        <h2 class="card-title">
                            <i class="fas fa-address-card"></i>
                            Contact Information
                        </h2>
                        
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value">{{ $currentUser->primary_email ?? $currentUser->email ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value">{{ $currentUser->primary_phone ?? $currentUser->phone ?? 'N/A' }}</span>
                        </div>
                        
                        @if(!empty($currentUser->address) || !empty($currentUser->office_address))
                        <div class="info-row">
                            <span class="info-label">Address:</span>
                            <span class="info-value">{{ $currentUser->office_address ?? $currentUser->address }}</span>
                        </div>
                        @endif
                        
                        @if(!empty($currentUser->city))
                        <div class="info-row">
                            <span class="info-label">City:</span>
                            <span class="info-value">{{ $currentUser->city }}</span>
                        </div>
                        @endif
                        
                        @if(!empty($currentUser->state))
                        <div class="info-row">
                            <span class="info-label">State:</span>
                            <span class="info-value">{{ $currentUser->state }}</span>
                        </div>
                        @endif
                        
                        @if(!empty($currentUser->zip_code))
                        <div class="info-row">
                            <span class="info-label">Zip Code:</span>
                            <span class="info-value">{{ $currentUser->zip_code }}</span>
                        </div>
                        @endif

                        @if(!empty($currentUser->company_name))
                        <div class="info-row">
                            <span class="info-label">Company:</span>
                            <span class="info-value">{{ $currentUser->company_name }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <!-- Social Links -->
                    @if(!empty($currentUser->facebook) || !empty($currentUser->twitter) || !empty($currentUser->instagram) || !empty($currentUser->website))
                    <div class="social-links-card">
                        <h2 class="card-title">
                            <i class="fas fa-share-alt"></i>
                            Connect With Me
                        </h2>
                        <div class="social-links">
                            @if(!empty($currentUser->facebook))
                                <a href="{{ $currentUser->facebook }}" target="_blank" class="social-link">
                                    <img src="{{ asset('icons/facebook.png') }}" alt="Facebook">
                                </a>
                            @endif

                            @if(!empty($currentUser->twitter))
                                <a href="{{ $currentUser->twitter }}" target="_blank" class="social-link">
                                    <img src="{{ asset('icons/twitter.png') }}" alt="Twitter">
                                </a>
                            @endif

                            @if(!empty($currentUser->instagram))
                                <a href="{{ $currentUser->instagram }}" target="_blank" class="social-link">
                                    <img src="{{ asset('icons/instagram.png') }}" alt="Instagram">
                                </a>
                            @endif

                            @if(!empty($currentUser->website))
                                <a href="{{ $currentUser->website }}" target="_blank" class="social-link">
                                    <img src="{{ asset('icons/website.png') }}" alt="Website">
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Account Details -->
                    <div class="info-card" style="margin-top: 2rem;">
                        <h2 class="card-title">
                            <i class="fas fa-cog"></i>
                            Account Details
                        </h2>
                        
                        <div class="info-row">
                            <span class="info-label">Account Type:</span>
                            <span class="info-value">{{ $isAgent ? 'Agent Account' : 'User Account' }}</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span class="info-value">
                                <span class="status-badge status-active">
                                    <i class="fas fa-check-circle"></i>
                                    Active
                                </span>
                            </span>
                        </div>

                        @if($isAgent && !empty($currentUser->is_verified))
                        <div class="info-row">
                            <span class="info-label">Verification:</span>
                            <span class="info-value">
                                @if($currentUser->is_verified)
                                    <span class="status-badge status-active">
                                        <i class="fas fa-badge-check"></i>
                                        Verified
                                    </span>
                                @else
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-clock"></i>
                                        Pending
                                    </span>
                                @endif
                            </span>
                        </div>
                        @endif
                        
                        <div class="info-row">
                            <span class="info-label">2FA:</span>
                            <span class="info-value">
                                <span class="status-badge status-inactive">
                                    <i class="fas fa-times-circle"></i>
                                    Not Set
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="info-card">
                <p style="color: #dc2626; text-align: center; padding: 2rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    No user is logged in.
                </p>
            </div>
        @endif
    </div>
</div>