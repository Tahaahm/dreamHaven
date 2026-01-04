<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $office->company_name ?? 'Office Profile' }} - Dream Haven</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #f8f9fa;
            color: #1a1a1a;
            padding-top: 70px;
        }

        /* Hero Banner */
        .hero-banner {
            height: 320px;
            background: linear-gradient(135deg, #303b97 0%, #1e2660 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-bg-image {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.3;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(48, 59, 151, 0.8), rgba(30, 38, 96, 0.95));
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 24px 40px;
            color: white;
        }

        .hero-flex {
            display: flex;
            gap: 28px;
            align-items: flex-start;
        }

        .office-logo-large {
            width: 110px;
            height: 110px;
            background: white;
            border-radius: 16px;
            padding: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            flex-shrink: 0;
        }

        .office-logo-large img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .office-logo-large i {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #303b97;
        }

        .hero-text {
            flex: 1;
        }

        .office-title {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .office-subtitle {
            font-size: 16px;
            opacity: 0.95;
            margin-bottom: 20px;
        }

        .hero-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge-hero {
            padding: 7px 16px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .badge-verified {
            background: rgba(16, 185, 129, 0.3);
        }

        /* Stats Grid */
        .stats-section {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin: -40px auto 40px;
            max-width: 1200px;
            border-radius: 16px;
            padding: 32px;
            position: relative;
            z-index: 3;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 24px;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid #e5e7eb;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(48, 59, 151, 0.12);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            margin: 0 auto 12px;
            background: linear-gradient(135deg, #303b97, #5865f2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 800;
            color: #303b97;
            display: block;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* Main Container */
        .main-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px 60px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 32px;
        }

        /* Card */
        .info-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 24px;
        }

        .card-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: #303b97;
            font-size: 16px;
        }

        .card-body {
            padding: 24px;
        }

        /* Contact */
        .contact-item {
            display: flex;
            gap: 14px;
            margin-bottom: 18px;
        }

        .contact-item:last-child {
            margin-bottom: 0;
        }

        .contact-icon {
            width: 38px;
            height: 38px;
            background: #303b97;
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .contact-text {
            flex: 1;
        }

        .contact-label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .contact-value {
            font-size: 14px;
            color: #111827;
            font-weight: 600;
        }

        .contact-value a {
            color: #303b97;
            text-decoration: none;
        }

        .contact-value a:hover {
            text-decoration: underline;
        }

        /* Quick Actions */
        .quick-buttons {
            display: grid;
            gap: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-quick {
            padding: 13px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #303b97;
            color: white;
        }

        .btn-primary:hover {
            background: #1e2660;
            color: white;
        }

        .btn-whatsapp {
            background: #25D366;
            color: white;
        }

        .btn-whatsapp:hover {
            background: #20ba5a;
            color: white;
        }

        .btn-secondary {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            color: #374151;
        }

        /* Working Hours */
        .hours-grid {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .hour-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 14px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .hour-day {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
        }

        .hour-time {
            font-size: 13px;
            color: #6b7280;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
        }

        .view-all {
            font-size: 14px;
            color: #303b97;
            font-weight: 600;
            text-decoration: none;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        /* About */
        .about-text {
            font-size: 15px;
            line-height: 1.8;
            color: #4b5563;
        }

        /* Agents Grid */
        .agents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }

        .agent-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }

        .agent-card:hover {
            border-color: #303b97;
            box-shadow: 0 8px 24px rgba(48, 59, 151, 0.12);
            transform: translateY(-4px);
        }

        .agent-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 14px;
            background: #f3f4f6;
            position: relative;
            overflow: hidden;
        }

        .agent-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .agent-avatar i {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #9ca3af;
        }

        .verified-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 22px;
            height: 22px;
            background: #10b981;
            border-radius: 50%;
            border: 3px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .verified-badge i {
            color: white;
            font-size: 9px;
        }

        .agent-name {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .agent-role {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .agent-stats {
            display: flex;
            justify-content: center;
            gap: 16px;
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
            font-size: 12px;
            color: #6b7280;
        }

        .agent-stats span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Properties Grid */
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .property-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }

        .property-card:hover {
            border-color: #303b97;
            box-shadow: 0 8px 24px rgba(48, 59, 151, 0.12);
            transform: translateY(-4px);
        }

        .property-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .property-card:hover .property-image img {
            transform: scale(1.1);
        }

        .property-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(48, 59, 151, 0.95);
            color: white;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .property-content {
            padding: 16px;
        }

        .property-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .property-location {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .property-price {
            font-size: 20px;
            font-weight: 800;
            color: #303b97;
            margin-bottom: 12px;
        }

        .property-features {
            display: flex;
            justify-content: space-between;
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
            font-size: 12px;
            color: #6b7280;
        }

        .property-features span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 56px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 15px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .hero-flex {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .office-title {
                font-size: 28px;
            }

            .agents-grid, .properties-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 60px;
            }

            .hero-banner {
                height: 280px;
            }

            .office-title {
                font-size: 24px;
            }

            .stats-section {
                margin: -30px 16px 30px;
                padding: 24px 16px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }

            .agents-grid, .properties-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @php $navbarStyle = 'navbar-light'; @endphp
    @include('navbar')

    <!-- Hero Banner -->
    <div class="hero-banner">
        @php
            // Handle background image - support both storage paths and external URLs
            $bgImage = null;
            if(!empty($office->company_bio_image)) {
                if(filter_var($office->company_bio_image, FILTER_VALIDATE_URL)) {
                    $bgImage = $office->company_bio_image;
                } else {
                    $bgImage = asset('storage/' . ltrim($office->company_bio_image, '/'));
                }
            }
        @endphp

        @if($bgImage)
        <img src="{{ $bgImage }}" class="hero-bg-image" alt="Background" onerror="this.style.display='none'">
        @endif

        <div class="hero-overlay"></div>

        <div class="hero-content">
            <div class="hero-flex">
                <div class="office-logo-large">
                    @php
                        // Handle logo - support both storage paths and external URLs
                        $logoImage = null;
                        if(!empty($office->profile_image)) {
                            if(filter_var($office->profile_image, FILTER_VALIDATE_URL)) {
                                $logoImage = $office->profile_image;
                            } else {
                                $logoImage = asset('storage/' . ltrim($office->profile_image, '/'));
                            }
                        }
                    @endphp

                    @if($logoImage)
                        <img src="{{ $logoImage }}" alt="{{ $office->company_name }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <i class="fas fa-building" style="display: none;"></i>
                    @else
                        <i class="fas fa-building"></i>
                    @endif
                </div>

                <div class="hero-text">
                    <h1 class="office-title">{{ $office->company_name ?? 'Real Estate Office' }}</h1>
                    <p class="office-subtitle">{{ $office->company_bio ?? 'Your Trusted Real Estate Partner' }}</p>

                    <div class="hero-badges">
                        @if($office->is_verified)
                        <span class="badge-hero badge-verified">
                            <i class="fas fa-check-circle"></i> Verified Office
                        </span>
                        @endif
                        <span class="badge-hero">
                            <i class="fas fa-star"></i> {{ number_format($office->average_rating ?? 0, 1) }} Rating
                        </span>
                        <span class="badge-hero">
                            <i class="fas fa-city"></i> {{ $office->city ?? 'Kurdistan' }}
                        </span>
                        <span class="badge-hero">
                            <i class="fas fa-award"></i> {{ $office->years_experience ?? 0 }}+ Years
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="main-wrapper">
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="stat-number">{{ $office->agents ? $office->agents->count() : 0 }}</span>
                    <span class="stat-label">Expert Agents</span>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <span class="stat-number">{{ isset($properties) ? $properties->count() : 0 }}</span>
                    <span class="stat-label">Active Listings</span>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <span class="stat-number">{{ $office->properties_sold ?? 0 }}</span>
                    <span class="stat-label">Properties Sold</span>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <span class="stat-number">{{ $office->years_experience ?? 0 }}+</span>
                    <span class="stat-label">Years Experience</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-grid">
            <!-- Sidebar -->
            <div>
                <!-- Contact Card -->
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-address-card"></i>
                            Contact Information
                        </div>
                    </div>
                    <div class="card-body">
                        @if($office->phone_number)
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <div class="contact-label">Phone</div>
                                <div class="contact-value">{{ $office->phone_number }}</div>
                            </div>
                        </div>
                        @endif

                        @if($office->email_address)
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <div class="contact-label">Email</div>
                                <div class="contact-value">
                                    <a href="mailto:{{ $office->email_address }}">{{ $office->email_address }}</a>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($office->office_address)
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <div class="contact-label">Address</div>
                                <div class="contact-value">{{ $office->office_address }}</div>
                            </div>
                        </div>
                        @endif

                        @if($office->city)
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-city"></i>
                            </div>
                            <div class="contact-text">
                                <div class="contact-label">Location</div>
                                <div class="contact-value">
                                    {{ $office->city }}@if($office->district), {{ $office->district }}@endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="quick-buttons">
                            @if($office->phone_number)
                            <a href="tel:{{ $office->phone_number }}" class="btn-quick btn-primary">
                                <i class="fas fa-phone"></i> Call Office
                            </a>
                            @endif

                            <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/\D/', '', $office->phone_number ?? '') }}"
                               target="_blank"
                               class="btn-quick btn-whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>

                            @if($office->email_address)
                            <a href="mailto:{{ $office->email_address }}" class="btn-quick btn-secondary">
                                <i class="fas fa-envelope"></i> Email Us
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Working Hours -->
                @if($office->availability_schedule)
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-clock"></i>
                            Working Hours
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="hours-grid">
                            @foreach($office->availability_schedule as $day => $hours)
                            <div class="hour-row">
                                <span class="hour-day">{{ ucfirst($day) }}</span>
                                <span class="hour-time">{{ $hours }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Main Content -->
            <div>
                <!-- About -->
                @if($office->about_company)
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-info-circle"></i>
                            About Us
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="about-text">{{ $office->about_company }}</p>
                    </div>
                </div>
                @endif

                <!-- Our Team -->
                <div class="info-card">
                    <div class="card-header">
                        <div class="section-header">
                            <div class="card-title">
                                <i class="fas fa-users"></i>
                                Our Professional Team
                            </div>
                            @if($office->agents && $office->agents->count() > 8)
                            <a href="#" class="view-all">View All</a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($office->agents && $office->agents->count() > 0)
                        <div class="agents-grid">
                            @foreach($office->agents->take(8) as $agent)
                            <a href="{{ route('agent.profile', $agent->id) }}" class="agent-card">
                                <div class="agent-avatar">
                                    @if($agent->profile_image)
                                        <img src="{{ filter_var($agent->profile_image, FILTER_VALIDATE_URL) ? $agent->profile_image : asset('storage/' . ltrim($agent->profile_image, '/')) }}"
                                             alt="{{ $agent->agent_name }}"
                                             onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-user\'></i>';">
                                    @else
                                        <i class="fas fa-user"></i>
                                    @endif

                                    @if($agent->is_verified)
                                    <div class="verified-badge">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="agent-name">{{ $agent->agent_name ?? 'Agent' }}</div>
                                <div class="agent-role">{{ $agent->type ?? 'Real Estate Agent' }}</div>
                                <div class="agent-stats">
                                    <span><i class="fas fa-star"></i> {{ number_format($agent->overall_rating ?? 0, 1) }}</span>
                                    <span><i class="fas fa-home"></i> {{ $agent->ownedProperties ? $agent->ownedProperties->count() : 0 }}</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No agents available</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Properties -->
                <div class="info-card">
                    <div class="card-header">
                        <div class="section-header">
                            <div class="card-title">
                                <i class="fas fa-home"></i>
                                Our Properties
                            </div>
                            @if(isset($properties) && $properties->count() > 8)
                            <a href="{{ route('property.list') }}?office_id={{ $office->id }}" class="view-all">View All</a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($properties) && $properties->count() > 0)
                        <div class="properties-grid">
                            @foreach($properties->take(8) as $property)
                            <a href="{{ route('property.PropertyDetail', $property->id) }}" class="property-card">
                                <div class="property-image">
                                    @php
                                        $propertyImage = $property->main_image
                                            ?? (isset($property->images[0]) ? $property->images[0] : asset('property_images/default-property.jpg'));

                                        if(!filter_var($propertyImage, FILTER_VALIDATE_URL) && !str_starts_with($propertyImage, '/')) {
                                            $propertyImage = asset('storage/' . ltrim($propertyImage, '/'));
                                        }
                                    @endphp
                                    <img src="{{ $propertyImage }}"
                                         alt="{{ $property->name['en'] ?? 'Property' }}"
                                         onerror="this.src='{{ asset('property_images/default-property.jpg') }}'">

                                    @if($property->listing_type)
                                    <div class="property-badge">{{ $property->listing_type }}</div>
                                    @endif
                                </div>
                                <div class="property-content">
                                    <h3 class="property-title">{{ $property->name['en'] ?? 'Property' }}</h3>
                                    <div class="property-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $property->address_details['city']['en'] ?? 'Location' }}
                                    </div>
                                    <div class="property-price">
                                        ${{ number_format($property->price['usd'] ?? 0) }}
                                    </div>
                                    <div class="property-features">
                                        <span><i class="fas fa-bed"></i> {{ $property->rooms['bedroom']['count'] ?? 0 }}</span>
                                        <span><i class="fas fa-bath"></i> {{ $property->rooms['bathroom']['count'] ?? 0 }}</span>
                                        <span><i class="fas fa-ruler-combined"></i> {{ number_format($property->area ?? 0) }}m²</span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <div class="empty-state">
                            <i class="fas fa-home"></i>
                            <p>No properties available</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
