<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $property->name['en'] ?? 'Property Details' }} - Dream Haven</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <style>
        :root {
            --primary: #303b97;
            --primary-dark: #1e2660;
            --primary-light: #4a56c7;
            --accent: #FFD700;
            --success: #00D09C;
            --danger: #FF4757;
            --dark: #2C3E50;
            --gray: #6b7280;
            --light-bg: #F8F9FA;
            --white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-bg);
            color: var(--dark);
            overflow-x: hidden;
        }

        /* Hero Section - Smaller */
        .property-hero {
            position: relative;
            height: 50vh;
            min-height: 400px;
            background: #000;
            margin-bottom: 0;
        }

        .swiper {
            width: 100%;
            height: 100%;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .property-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
            padding: 40px 0 20px;
            z-index: 10;
        }

        .hero-badges {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .hero-badge {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hero-badge.verified {
            background: var(--success);
            color: white;
        }

        .hero-badge.featured {
            background: var(--accent);
            color: var(--dark);
        }

        .swiper-button-prev,
        .swiper-button-next {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            color: var(--primary);
        }

        .swiper-button-prev:after,
        .swiper-button-next:after {
            font-size: 16px;
            font-weight: bold;
        }

        .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: white;
        }

        .swiper-pagination-bullet-active {
            background: var(--accent);
        }

        .hero-title {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-location {
            color: white;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .hero-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        /* Main Content */
        .content-wrapper {
            max-width: 1200px;
            margin: -40px auto 40px;
            padding: 0 20px;
            position: relative;
            z-index: 20;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }

        /* Info Cards */
        .info-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        /* Stats Grid - SMALLER */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 25px;
        }

        .stat-box {
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 15px 12px;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s;
        }

        .stat-box:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(48, 59, 151, 0.2);
        }

        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 8px;
            opacity: 0.9;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.7rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Description */
        .description-text {
            font-size: 1rem;
            line-height: 1.8;
            color: #555;
        }

        /* Details Grid - Compact */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .detail-item {
            background: var(--light-bg);
            padding: 12px 15px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .detail-item:hover {
            background: #e8edf2;
            transform: translateX(3px);
        }

        .detail-label {
            font-weight: 500;
            color: var(--gray);
            font-size: 0.85rem;
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .detail-value i {
            color: var(--primary);
            font-size: 0.9rem;
        }

        /* Features - Compact */
        .features-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .feature-item {
            background: var(--light-bg);
            padding: 10px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .feature-item:hover {
            background: var(--primary);
            color: white;
            transform: translateX(3px);
        }

        .feature-item i {
            color: var(--success);
            font-size: 1rem;
        }

        .feature-item:hover i {
            color: var(--accent);
        }

        /* Contact Form - Compact */
        .contact-form-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }

        .form-control {
            border: 2px solid var(--light-bg);
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s;
            margin-bottom: 15px;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(48, 59, 151, 0.1);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(48, 59, 151, 0.3);
        }

        /* View Agent Button */
        .btn-view-agent {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .btn-view-agent:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(48, 59, 151, 0.3);
            color: white;
        }

        .btn-view-agent i {
            font-size: 1.1rem;
        }

        /* Map - Compact */
        .map-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            height: 350px;
            margin-bottom: 20px;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        /* Report Section - Compact */
        .report-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }

        .report-card textarea {
            border: 2px solid var(--light-bg);
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 0.95rem;
            min-height: 100px;
            width: 100%;
            font-family: inherit;
        }

        .report-card textarea:focus {
            border-color: var(--danger);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 71, 87, 0.1);
        }

        .btn-report {
            background: var(--danger);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .btn-report:hover {
            background: #e63946;
            transform: translateY(-2px);
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border: none;
            padding: 15px;
            border-radius: 10px;
            margin-top: 12px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .property-hero {
                height: 40vh;
                min-height: 300px;
            }

            .hero-title {
                font-size: 1.5rem;
            }

            .hero-price {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .features-list {
                grid-template-columns: 1fr;
            }

            .content-wrapper {
                margin-top: -30px;
            }
        }

        @media (max-width: 480px) {
            .hero-badges {
                top: 15px;
                left: 15px;
            }

            .hero-badge {
                padding: 5px 12px;
                font-size: 0.7rem;
            }

            .stats-grid {
                gap: 8px;
            }

            .stat-box {
                padding: 12px 10px;
            }

            .stat-icon {
                font-size: 1.2rem;
            }

            .stat-value {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    @php $navbarStyle = 'navbar-light'; @endphp
    @include('navbar')

    <!-- Hero Section with Image Gallery -->
    <div class="property-hero">
        <div class="swiper heroSwiper">
            <div class="swiper-wrapper">
                @foreach($property->images as $index => $photo)
                <div class="swiper-slide">
                    <img src="{{ $photo }}" alt="Property Image {{ $index + 1 }}" onerror="this.src='{{ asset('property_images/default-property.jpg') }}'">
                </div>
                @endforeach
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>

        <!-- Badges -->
        <div class="hero-badges">
            @if($property->verified)
            <div class="hero-badge verified">
                <i class="fas fa-check-circle"></i> Verified
            </div>
            @endif
            @if($property->is_boosted)
            <div class="hero-badge featured">
                <i class="fas fa-star"></i> Featured
            </div>
            @endif
            <div class="hero-badge">
                <i class="fas fa-tag"></i> {{ ucfirst($property->listing_type) }}
            </div>
        </div>

        <!-- Overlay Info -->
        <div class="property-overlay">
            <div class="container">
                <h1 class="hero-title">{{ $property->name['en'] ?? $property->name ?? 'Untitled Property' }}</h1>
                <div class="hero-location">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $property->address_details['city']['en'] ?? $property->address ?? 'Location not specified' }}
                </div>
                <div class="hero-price">
                    ${{ number_format($property->price['usd'] ?? 0) }}
                    <span style="font-size: 1.2rem; font-weight: 400;">/ {{ $property->listing_type === 'rent' ? 'month' : 'total' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="content-grid">
            <div>
                <!-- Stats Grid - COMPACT -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-icon"><i class="fas fa-bed"></i></div>
                        <div class="stat-value">{{ $property->rooms['bedroom']['count'] ?? 0 }}</div>
                        <div class="stat-label">Bedrooms</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon"><i class="fas fa-bath"></i></div>
                        <div class="stat-value">{{ $property->rooms['bathroom']['count'] ?? 0 }}</div>
                        <div class="stat-label">Bathrooms</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon"><i class="fas fa-ruler-combined"></i></div>
                        <div class="stat-value">{{ number_format($property->area ?? 0) }}</div>
                        <div class="stat-label">m² Area</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon"><i class="fas fa-couch"></i></div>
                        <div class="stat-value">{{ $property->furnished ? 'Yes' : 'No' }}</div>
                        <div class="stat-label">Furnished</div>
                    </div>
                </div>

                <!-- Description -->
                <div class="info-card">
                    <h2 class="card-title">
                        <i class="fas fa-file-alt"></i>
                        About This Property
                    </h2>
                    <p class="description-text">{{ $property->description['en'] ?? 'No description provided.' }}</p>

                    @php
                        $owner = $property->owner;
                        $canShowAgent = false;
                        $agentUrl = '#';

                        if ($owner) {
                            $ownerClass = get_class($owner);

                            // Check if owner is Agent
                            if ($ownerClass === 'App\\Models\\Agent') {
                                $canShowAgent = true;
                                $agentUrl = route('agent.profile', $owner->id);
                            }
                            // If RealEstateOffice, we could show office page
                            elseif ($ownerClass === 'App\\Models\\RealEstateOffice') {
                                $canShowAgent = true;
                                $agentUrl = route('office.profile', $owner->id);
                            }
                        }
                    @endphp

                    @if($canShowAgent)
                    <div class="mt-3">
                        <a href="{{ $agentUrl }}" class="btn-view-agent">
                            <i class="fas fa-user-tie"></i>
                            View Agent Profile
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Details -->
                <div class="info-card">
                    <h2 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Property Details
                    </h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Property Type</span>
                            <span class="detail-value">
                                {{ ucfirst($property->type['category'] ?? 'N/A') }}
                                <i class="fas fa-home"></i>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Listing Type</span>
                            <span class="detail-value">
                                {{ ucfirst($property->listing_type ?? 'N/A') }}
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Floor Number</span>
                            <span class="detail-value">
                                {{ $property->floor_number ?? 'N/A' }}
                                <i class="fas fa-layer-group"></i>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Year Built</span>
                            <span class="detail-value">
                                {{ $property->year_built ?? 'N/A' }}
                                <i class="fas fa-calendar-check"></i>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Electricity</span>
                            <span class="detail-value">
                                {{ $property->electricity ? 'Available' : 'Not Available' }}
                                <i class="fas fa-bolt"></i>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Water</span>
                            <span class="detail-value">
                                {{ $property->water ? 'Available' : 'Not Available' }}
                                <i class="fas fa-tint"></i>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Internet</span>
                            <span class="detail-value">
                                {{ $property->internet ? 'Available' : 'Not Available' }}
                                <i class="fas fa-wifi"></i>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Furnished</span>
                            <span class="detail-value">
                                {{ $property->furnished ? 'Yes' : 'No' }}
                                <i class="fas fa-couch"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Features & Amenities -->
                @if(!empty($property->features) || !empty($property->amenities))
                <div class="info-card">
                    <h2 class="card-title">
                        <i class="fas fa-star"></i>
                        Features & Amenities
                    </h2>
                    <div class="features-list">
                        @foreach($property->features ?? [] as $feature)
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            {{ ucfirst($feature) }}
                        </div>
                        @endforeach
                        @foreach($property->amenities ?? [] as $amenity)
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            {{ ucfirst($amenity) }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Map -->
                <div class="map-card">
                    <div id="map"></div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form-card">
                    <h2 class="card-title">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </h2>
                    <form action="/submit-contact" method="post">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone-number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (Optional)</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>

                <!-- Report Section -->
                <div class="report-card">
                    <h2 class="card-title">
                        <i class="fas fa-flag"></i>
                        Report This Property
                    </h2>
                    <form method="post" action="{{ route('report.store') }}">
                        @csrf
                        <textarea name="report" placeholder="Please describe the issue..." required></textarea>
                        <input type="hidden" name="property_id" value="{{ $property->id }}">
                        <button type="submit" class="btn-report">
                            <i class="fas fa-flag"></i> Submit Report
                        </button>
                    </form>
                    @if(session('success'))
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Google Maps -->
    <script>
        function initMap() {
            @php
                // Get location from locations array
                $lat = 36.1911; // Default: Erbil
                $lng = 44.0091;

                if (!empty($property->locations) && is_array($property->locations) && isset($property->locations[0])) {
                    $lat = $property->locations[0]['lat'] ?? $lat;
                    $lng = $property->locations[0]['lng'] ?? $lng;
                }
            @endphp

            var propertyLocation = {
                lat: {{ $lat }},
                lng: {{ $lng }}
            };

            var map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: propertyLocation,
                styles: [
                    {
                        "featureType": "water",
                        "elementType": "geometry",
                        "stylers": [{"color": "#e9e9e9"}, {"lightness": 17}]
                    },
                    {
                        "featureType": "landscape",
                        "elementType": "geometry",
                        "stylers": [{"color": "#f5f5f5"}, {"lightness": 20}]
                    }
                ]
            });

            var marker = new google.maps.Marker({
                position: propertyLocation,
                map: map,
                animation: google.maps.Animation.DROP,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    fillColor: "#303b97",
                    fillOpacity: 1,
                    strokeWeight: 3,
                    strokeColor: "#ffffff"
                }
            });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>

    <!-- Initialize Swiper -->
    <script>
        var swiper = new Swiper(".heroSwiper", {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
    </script>
</body>
</html>
