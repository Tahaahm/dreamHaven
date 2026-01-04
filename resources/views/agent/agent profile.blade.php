<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $agent->agent_name ?? 'Agent Profile' }} - Dream Haven</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
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
            line-height: 1.5;
        }

        /* Header */
        .profile-header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 40px 0 0;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
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
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }

        .profile-avatar-icon {
            font-size: 48px;
            color: #9ca3af;
        }

        .verified-badge {
            position: absolute;
            bottom: -4px;
            right: -4px;
            width: 28px;
            height: 28px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #ffffff;
        }

        .verified-badge i {
            color: white;
            font-size: 12px;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .profile-title {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .profile-meta {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #374151;
        }

        .meta-item i {
            color: #6b7280;
            font-size: 14px;
        }

        .meta-item strong {
            font-weight: 600;
            color: #111827;
        }

        .profile-actions {
            display: flex;
            gap: 12px;
        }

        .btn-action {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #303b97;
            color: white;
        }

        .btn-primary:hover {
            background: #1e2660;
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

        .btn-whatsapp {
            background: #25D366;
            color: white;
        }

        .btn-whatsapp:hover {
            background: #20ba5a;
            color: white;
        }

        /* Stats Bar */
        .stats-bar {
            border-top: 1px solid #e5e7eb;
            padding: 24px 0;
        }

        .stats-container {
            display: flex;
            justify-content: space-around;
            max-width: 800px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            display: block;
        }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 24px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
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

        /* Sidebar */
        .contact-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .contact-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .contact-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .contact-label i {
            font-size: 12px;
        }

        .contact-value {
            font-size: 14px;
            color: #111827;
            font-weight: 500;
        }

        .contact-value a {
            color: #303b97;
            text-decoration: none;
        }

        .contact-value a:hover {
            text-decoration: underline;
        }

        .contact-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-contact {
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: all 0.15s;
        }

        /* Company */
        .company-card {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 20px;
            background: #f9fafb;
            border-radius: 10px;
        }

        .company-logo {
            width: 56px;
            height: 56px;
            border-radius: 8px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .company-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .company-logo i {
            color: #9ca3af;
            font-size: 24px;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .company-role {
            font-size: 13px;
            color: #6b7280;
        }

        /* About */
        .about-text {
            font-size: 15px;
            line-height: 1.7;
            color: #374151;
        }

        .badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .badge {
            padding: 6px 12px;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 13px;
            color: #4b5563;
            font-weight: 500;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-box {
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .info-box-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-box-label i {
            font-size: 12px;
        }

        .info-box-value {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
        }

        /* Properties */
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .property-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.15s;
            background: white;
        }

        .property-card:hover {
            border-color: #303b97;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .property-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f3f4f6;
        }

        .property-body {
            padding: 16px;
        }

        .property-title {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .property-location {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .property-location i {
            font-size: 12px;
        }

        .property-price {
            font-size: 18px;
            font-weight: 700;
            color: #303b97;
            margin-bottom: 12px;
        }

        .property-features {
            display: flex;
            gap: 16px;
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
            font-size: 13px;
            color: #6b7280;
        }

        .property-features span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .property-features i {
            font-size: 13px;
        }

        /* Reviews */
        .review-item {
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reviewer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }

        .reviewer-name {
            font-weight: 600;
            font-size: 14px;
            color: #111827;
        }

        .review-rating {
            color: #fbbf24;
            font-size: 14px;
        }

        .review-text {
            font-size: 14px;
            line-height: 1.6;
            color: #4b5563;
        }

        .review-date {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 8px;
        }

        .btn-load-more {
            width: 100%;
            padding: 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            color: #374151;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-load-more:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .profile-top {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .profile-actions {
                justify-content: center;
            }

            .profile-meta {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .profile-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }

            .properties-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @php $navbarStyle = 'navbar-light'; @endphp
    @include('navbar')

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="header-container">
            <div class="profile-top">
                <!-- Avatar -->
                <div class="profile-avatar">
                    @if($agent->profile_image)
                        <img src="{{ asset('storage/' . ltrim($agent->profile_image, '/')) }}"
                             alt="{{ $agent->agent_name }}"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <i class="fas fa-user profile-avatar-icon" style="display: none;"></i>
                    @else
                        <i class="fas fa-user profile-avatar-icon"></i>
                    @endif

                    @if($agent->is_verified)
                    <div class="verified-badge">
                        <i class="fas fa-check"></i>
                    </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="profile-info">
                    <h1 class="profile-name">{{ $agent->agent_name ?? 'Agent Name' }}</h1>
                    <p class="profile-title">{{ $agent->type ?? 'Real Estate Professional' }}</p>

                    <div class="profile-meta">
                        <div class="meta-item">
                            <i class="fas fa-star"></i>
                            <strong>{{ number_format($agent->overall_rating ?? 0, 1) }}</strong> Rating
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-home"></i>
                            <strong>{{ $agent->ownedProperties->count() ?? 0 }}</strong> Properties
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-briefcase"></i>
                            <strong>{{ $agent->years_experience ?? 0 }}</strong> Years
                        </div>
                        @if($agent->license_number)
                        <div class="meta-item">
                            <i class="fas fa-shield-alt"></i>
                            Licensed Professional
                        </div>
                        @endif
                    </div>

                    <div class="profile-actions">
                        @if($agent->whatsapp_number ?? $agent->primary_phone)
                        <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/\D/', '', $agent->whatsapp_number ?? $agent->primary_phone) }}&text=Hi"
                           target="_blank"
                           class="btn-action btn-whatsapp">
                            <i class="fab fa-whatsapp"></i>
                            WhatsApp
                        </a>
                        @endif
                        @if($agent->primary_phone)
                        <a href="tel:{{ $agent->primary_phone }}" class="btn-action btn-primary">
                            <i class="fas fa-phone"></i>
                            Call
                        </a>
                        @endif
                        @if($agent->primary_email)
                        <a href="mailto:{{ $agent->primary_email }}" class="btn-action btn-secondary">
                            <i class="fas fa-envelope"></i>
                            Email
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stats-container">
                    <div class="stat-item">
                        <span class="stat-value">{{ $agent->ownedProperties->count() ?? 0 }}</span>
                        <span class="stat-label">Active Listings</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $agent->properties_sold ?? 0 }}</span>
                        <span class="stat-label">Properties Sold</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ $agent->years_experience ?? 0 }}</span>
                        <span class="stat-label">Years Experience</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ number_format($agent->overall_rating ?? 0, 1) }}</span>
                        <span class="stat-label">Client Rating</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-grid">
            <!-- Sidebar -->
            <div>
                <!-- Contact -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-address-book"></i>
                            Contact Information
                        </div>
                    </div>
                    <div class="card-body">
                        @if($agent->primary_phone)
                        <div class="contact-item">
                            <div class="contact-label">
                                <i class="fas fa-phone"></i>
                                Phone
                            </div>
                            <div class="contact-value">{{ $agent->primary_phone }}</div>
                        </div>
                        @endif

                        @if($agent->primary_email)
                        <div class="contact-item">
                            <div class="contact-label">
                                <i class="fas fa-envelope"></i>
                                Email
                            </div>
                            <div class="contact-value">
                                <a href="mailto:{{ $agent->primary_email }}">{{ $agent->primary_email }}</a>
                            </div>
                        </div>
                        @endif

                        @if($agent->city)
                        <div class="contact-item">
                            <div class="contact-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Location
                            </div>
                            <div class="contact-value">{{ $agent->city }}{{ $agent->district ? ', ' . $agent->district : '' }}</div>
                        </div>
                        @endif

                        @if($agent->office_address)
                        <div class="contact-item">
                            <div class="contact-label">
                                <i class="fas fa-building"></i>
                                Office Address
                            </div>
                            <div class="contact-value">{{ $agent->office_address }}</div>
                        </div>
                        @endif

                        <div class="contact-buttons">
                            @if($agent->whatsapp_number ?? $agent->primary_phone)
                            <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/\D/', '', $agent->whatsapp_number ?? $agent->primary_phone) }}"
                               target="_blank"
                               class="btn-contact btn-whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            @endif

                            @if($agent->primary_email)
                            <a href="mailto:{{ $agent->primary_email }}" class="btn-contact btn-primary">
                                <i class="fas fa-envelope"></i> Send Email
                            </a>
                            @endif

                            @if($agent->primary_phone)
                            <a href="tel:{{ $agent->primary_phone }}" class="btn-contact btn-secondary">
                                <i class="fas fa-phone"></i> Call Now
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Company -->
                @if($agent->company)
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-building"></i>
                            Company
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="company-card">
                            <div class="company-logo">
                                @if($agent->company->logo)
                                <img src="{{ asset('storage/' . ltrim($agent->company->logo, '/')) }}" alt="{{ $agent->company->company_name }}">
                                @else
                                <i class="fas fa-building"></i>
                                @endif
                            </div>
                            <div class="company-info">
                                <div class="company-name">{{ $agent->company->company_name ?? 'Real Estate Company' }}</div>
                                <div class="company-role">{{ $agent->employment_status ?? 'Real Estate Agent' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Main -->
            <div>
                <!-- About -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-user"></i>
                            About
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="about-text">
                            {{ $agent->agent_bio ?? $agent->agent_overview ?? 'Professional real estate agent dedicated to helping clients find their perfect property.' }}
                        </p>

                        @if($agent->specializations && $agent->specializations->count() > 0)
                        <div class="badges">
                            @foreach($agent->specializations->take(8) as $spec)
                            <span class="badge">{{ $spec->specialization_name ?? 'Specialization' }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Professional Details -->
                @if($agent->license_number || $agent->years_experience || $agent->properties_sold || $agent->commission_rate)
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Professional Details
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            @if($agent->license_number)
                            <div class="info-box">
                                <div class="info-box-label">
                                    <i class="fas fa-id-card"></i>
                                    License Number
                                </div>
                                <div class="info-box-value">{{ $agent->license_number }}</div>
                            </div>
                            @endif

                            @if($agent->years_experience)
                            <div class="info-box">
                                <div class="info-box-label">
                                    <i class="fas fa-briefcase"></i>
                                    Experience
                                </div>
                                <div class="info-box-value">{{ $agent->years_experience }} Years</div>
                            </div>
                            @endif

                            @if($agent->properties_sold)
                            <div class="info-box">
                                <div class="info-box-label">
                                    <i class="fas fa-handshake"></i>
                                    Properties Sold
                                </div>
                                <div class="info-box-value">{{ $agent->properties_sold }}</div>
                            </div>
                            @endif

                            @if($agent->commission_rate)
                            <div class="info-box">
                                <div class="info-box-label">
                                    <i class="fas fa-percent"></i>
                                    Commission Rate
                                </div>
                                <div class="info-box-value">{{ $agent->commission_rate }}%</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Properties -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-home"></i>
                            Active Listings ({{ $agent->ownedProperties->count() ?? 0 }})
                        </div>
                    </div>
                    <div class="card-body">
                        @if($agent->ownedProperties && $agent->ownedProperties->count() > 0)
                        <div class="properties-grid">
                            @foreach($agent->ownedProperties->take(6) as $property)
                            <a href="{{ route('property.PropertyDetail', $property->id) }}" style="text-decoration: none;">
                                <div class="property-card">
                                    <img src="{{ $property->main_image ?? ($property->images[0] ?? asset('property_images/default-property.jpg')) }}"
                                         alt="{{ $property->name['en'] ?? 'Property' }}"
                                         class="property-image"
                                         onerror="this.src='{{ asset('property_images/default-property.jpg') }}'">
                                    <div class="property-body">
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
                                </div>
                            </a>
                            @endforeach
                        </div>

                        @if($agent->ownedProperties->count() > 6)
                        <button class="btn-load-more mt-3" onclick="window.location.href='{{ route('property.list') }}?agent_id={{ $agent->id }}'">
                            View All {{ $agent->ownedProperties->count() }} Properties
                        </button>
                        @endif
                        @else
                        <div class="empty-state">
                            <i class="fas fa-home" style="font-size: 48px; margin-bottom: 16px;"></i>
                            <p>No active listings at the moment</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Reviews -->
                @if($agent->clientReviews && $agent->clientReviews->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-star"></i>
                            Client Reviews ({{ $agent->clientReviews->count() }})
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($agent->clientReviews->take(5) as $review)
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        {{ strtoupper(substr($review->client_name ?? 'A', 0, 1)) }}
                                    </div>
                                    <span class="reviewer-name">{{ $review->client_name ?? 'Anonymous' }}</span>
                                </div>
                                <span class="review-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= ($review->rating ?? 0))
                                            <i class="fas fa-star"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </span>
                            </div>
                            <p class="review-text">{{ $review->review_text ?? 'Great experience working with this agent!' }}</p>
                            <div class="review-date">{{ isset($review->created_at) ? $review->created_at->format('M d, Y') : '' }}</div>
                        </div>
                        @endforeach

                        @if($agent->clientReviews->count() > 5)
                        <button class="btn-load-more">
                            Load More Reviews
                        </button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
