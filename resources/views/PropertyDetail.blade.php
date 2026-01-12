<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $property->name['en'] ?? 'Property Details' }} - Dream Haven</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <style>
        :root {
            --brand-primary: #2563eb;
            --brand-dark: #0f172a;
            --brand-accent: #f59e0b;
            --brand-success: #10b981;
            --brand-danger: #ef4444;
            --bg-light: #f8fafc;
            --card-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--brand-dark);
            line-height: 1.6;
        }

        /* Hero Section Styling */
        .hero-container {
            position: relative;
            height: 65vh;
            min-height: 450px;
            background: #000;
        }

        .hero-swiper { width: 100%; height: 100%; }
        .hero-swiper img { width: 100%; height: 100%; object-fit: cover; }

        .hero-badge-container {
            position: absolute;
            top: 25px;
            left: 25px;
            z-index: 10;
            display: flex;
            gap: 10px;
        }

        .glass-badge {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.8rem;
            color: var(--brand-dark);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .badge-verified { background: var(--brand-success); color: white; }

        /* Main Content Layout */
        .property-grid {
            margin-top: -60px;
            position: relative;
            z-index: 20;
            padding-bottom: 80px;
        }

        .main-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 35px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 24px;
        }

        /* Stats Grid */
        .stats-bento {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--bg-light);
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            transition: 0.3s;
            border: 1px solid transparent;
        }

        .stat-card:hover {
            background: white;
            border-color: var(--brand-primary);
            transform: translateY(-5px);
        }

        .stat-card i { font-size: 1.4rem; color: var(--brand-primary); margin-bottom: 8px; }
        .stat-card .val { display: block; font-size: 1.25rem; font-weight: 800; }
        .stat-card .lbl { font-size: 0.7rem; text-transform: uppercase; color: #64748b; font-weight: 600; }

        /* Typography */
        .property-title { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 2.2rem; }
        .property-price { font-size: 2.5rem; font-weight: 800; color: var(--brand-primary); letter-spacing: -1px; }
        .section-header { font-weight: 700; font-size: 1.25rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* Sidebars & Sticky */
        .sticky-col { position: sticky; top: 25px; }

        .contact-box {
            background: var(--brand-dark);
            color: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        }

        .contact-box .form-control {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            padding: 12px;
        }

        .btn-premium {
            background: var(--brand-primary);
            border: none;
            padding: 15px;
            font-weight: 700;
            border-radius: 12px;
            transition: 0.3s;
        }
        .btn-premium:hover { background: #1d4ed8; transform: scale(1.02); }

        /* Amenities Pills */
        .pill-container { display: flex; flex-wrap: wrap; gap: 10px; }
        .amenity-pill {
            background: #f1f5f9;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Map Card */
        .map-wrapper {
            height: 350px;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 24px;
            border: 1px solid var(--border-color);
        }

        @media (max-width: 991px) {
            .stats-bento { grid-template-columns: repeat(2, 1fr); }
            .property-grid { margin-top: 20px; }
            .property-title { font-size: 1.7rem; }
        }
    </style>
</head>
<body>
    @php $navbarStyle = 'navbar-light'; @endphp
    @include('navbar')

    <div class="hero-container">
        <div class="swiper hero-swiper">
            <div class="swiper-wrapper">
                @foreach($property->images as $index => $photo)
                <div class="swiper-slide">
                    <img src="{{ $photo }}" alt="Property Image" onerror="this.src='{{ asset('property_images/default-property.jpg') }}'">
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
        </div>

        <div class="hero-badge-container">
            @if($property->verified)
                <div class="glass-badge badge-verified"><i class="fas fa-check-circle"></i> VERIFIED</div>
            @endif
            @if($property->is_boosted)
                <div class="glass-badge" style="color: var(--brand-accent)"><i class="fas fa-crown"></i> FEATURED</div>
            @endif
            <div class="glass-badge"><i class="fas fa-tag"></i> {{ strtoupper($property->listing_type) }}</div>
        </div>
    </div>

    <div class="container property-grid">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="main-card">
                    <div class="d-md-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h1 class="property-title mb-2">{{ $property->name['en'] ?? $property->name ?? 'Untitled Property' }}</h1>
                            <p class="text-muted"><i class="fas fa-map-marker-alt text-primary me-2"></i> {{ $property->address_details['city']['en'] ?? $property->address ?? 'Location' }}</p>
                        </div>
                        <div class="text-md-end mt-3 mt-md-0">
                            <div class="property-price">${{ number_format($property->price['usd'] ?? 0) }}</div>
                            <span class="text-muted fw-medium">/ {{ $property->listing_type === 'rent' ? 'month' : 'total' }}</span>
                        </div>
                    </div>

                    <div class="stats-bento">
                        <div class="stat-card">
                            <i class="fas fa-bed"></i>
                            <span class="val">{{ $property->rooms['bedroom']['count'] ?? 0 }}</span>
                            <span class="lbl">Bedrooms</span>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-bath"></i>
                            <span class="val">{{ $property->rooms['bathroom']['count'] ?? 0 }}</span>
                            <span class="lbl">Bathrooms</span>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-ruler-combined"></i>
                            <span class="val">{{ number_format($property->area ?? 0) }}</span>
                            <span class="lbl">m² Area</span>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-couch"></i>
                            <span class="val">{{ $property->furnished ? 'Yes' : 'No' }}</span>
                            <span class="lbl">Furnished</span>
                        </div>
                    </div>

                    <h3 class="section-header"><i class="fas fa-align-left text-primary"></i> Description</h3>
                    <p class="text-secondary mb-4">{{ $property->description['en'] ?? 'No description provided.' }}</p>

                    @php
                        $owner = $property->owner;
                        $canShowAgent = false;
                        $agentUrl = '#';
                        if ($owner) {
                            $ownerClass = get_class($owner);
                            if ($ownerClass === 'App\\Models\\Agent') { $canShowAgent = true; $agentUrl = route('agent.profile', $owner->id); }
                            elseif ($ownerClass === 'App\\Models\\RealEstateOffice') { $canShowAgent = true; $agentUrl = route('office.profile', $owner->id); }
                        }
                    @endphp

                    @if($canShowAgent)
                    <a href="{{ $agentUrl }}" class="btn btn-outline-primary fw-bold px-4 py-2" style="border-radius: 10px;">
                        <i class="fas fa-user-tie me-2"></i> View Professional Profile
                    </a>
                    @endif
                </div>

                <div class="main-card">
                    <h3 class="section-header"><i class="fas fa-list-check text-primary"></i> Property Specifications</h3>
                    <div class="row g-3">
                        @php
                            $details = [
                                ['label' => 'Property Type', 'val' => ucfirst($property->type['category'] ?? 'N/A'), 'icon' => 'fa-home'],
                                ['label' => 'Year Built', 'val' => $property->year_built ?? 'N/A', 'icon' => 'fa-calendar-check'],
                                ['label' => 'Floor Number', 'val' => $property->floor_number ?? 'N/A', 'icon' => 'fa-layer-group'],
                                ['label' => 'Electricity', 'val' => $property->electricity ? 'Available' : 'No', 'icon' => 'fa-bolt'],
                                ['label' => 'Water', 'val' => $property->water ? 'Available' : 'No', 'icon' => 'fa-tint'],
                                ['label' => 'Internet', 'val' => $property->internet ? 'Fiber Optic' : 'No', 'icon' => 'fa-wifi'],
                            ];
                        @endphp
                        @foreach($details as $det)
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between p-3 rounded-3" style="background: #f8fafc;">
                                <span class="text-muted small fw-bold">{{ $det['label'] }}</span>
                                <span class="fw-bold"><i class="fas {{ $det['icon'] }} me-2 text-primary"></i>{{ $det['val'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if(!empty($property->features) || !empty($property->amenities))
                <div class="main-card">
                    <h3 class="section-header"><i class="fas fa-wand-magic-sparkles text-primary"></i> Amenities & Features</h3>
                    <div class="pill-container">
                        @foreach(array_merge($property->features ?? [], $property->amenities ?? []) as $item)
                        <div class="amenity-pill">
                            <i class="fas fa-check text-success"></i> {{ ucfirst($item) }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="map-wrapper" id="map"></div>

                <div class="main-card" style="border-left: 5px solid var(--brand-danger);">
                    <h3 class="section-header text-danger"><i class="fas fa-circle-exclamation"></i> Report Issue</h3>
                    <form method="post" action="{{ route('report.store') }}">
                        @csrf
                        <textarea name="report" class="form-control mb-3" rows="2" placeholder="Describe the issue with this listing..." required></textarea>
                        <input type="hidden" name="property_id" value="{{ $property->id }}">
                        <button type="submit" class="btn btn-danger btn-sm px-4 fw-bold">Submit Report</button>
                    </form>
                    @if(session('success'))
                        <div class="alert alert-success mt-3 rounded-3 border-0">{{ session('success') }}</div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sticky-col">
                    <div class="contact-box">
                        <h4 class="fw-bold mb-4">Inquire About This</h4>
                        <form action="/submit-contact" method="post">
                            @csrf
                            <div class="mb-3">
                                <label class="small text-light opacity-75 mb-1">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="small text-light opacity-75 mb-1">Phone Number</label>
                                <input type="tel" name="phone-number" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="small text-light opacity-75 mb-1">Message</label>
                                <textarea name="message" class="form-control" rows="4" required>I am interested in {{ $property->name['en'] ?? 'this property' }}. Please contact me.</textarea>
                            </div>
                            <button type="submit" class="btn btn-premium w-100 text-white">
                                <i class="fas fa-paper-plane me-2"></i> Send Inquiry
                            </button>
                        </form>
                        <div class="mt-4 pt-4 border-top border-secondary text-center">
                            <p class="small text-light opacity-50 mb-0">Typically responds within 24 hours</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        // Swiper Logic
        new Swiper(".hero-swiper", {
            loop: true,
            autoplay: { delay: 4000 },
            pagination: { el: ".swiper-pagination", clickable: true },
        });

        // Map Logic - Retained your exact variables
        function initMap() {
            @php
                $lat = 36.1911; $lng = 44.0091;
                if (!empty($property->locations) && is_array($property->locations) && isset($property->locations[0])) {
                    $lat = $property->locations[0]['lat'] ?? $lat;
                    $lng = $property->locations[0]['lng'] ?? $lng;
                }
            @endphp
            var loc = { lat: {{ $lat }}, lng: {{ $lng }} };
            var map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15, center: loc,
                styles: [{"featureType":"all","elementType":"geometry.fill","stylers":[{"weight":"2.00"}]}]
            });
            new google.maps.Marker({ position: loc, map: map, icon: { path: google.maps.SymbolPath.CIRCLE, scale: 10, fillColor: "#2563eb", fillOpacity: 1, strokeWeight: 3, strokeColor: "#ffffff" } });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
</body>
</html>
