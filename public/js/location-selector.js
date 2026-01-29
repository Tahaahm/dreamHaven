<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>
    <script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('js/location-selector.js') }}"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <title>Dream Mulk - Property Listings</title>

    <style>
        :root {
            --primary: #303b97;
            --primary-dark: #1e2456;
            --primary-light: #4a56c4;
            --accent: #d4af37;
            --accent-dark: #b8941f;
            --text-dark: #1a1a2e;
            --text-light: #eef1f5;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #1a1e3e 50%, #0a0e27 100%);
            color: var(--text-light);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Main Layout */
        .layout-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            padding-top: 80px;
        }

        /* ============ GLASSMORPHIC SIDEBAR ============ */
        .filter-sidebar {
            width: 360px;
            position: fixed;
            left: 0;
            top: 80px;
            bottom: 0;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .filter-sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .filter-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .filter-sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--accent), #f0d077);
            border-radius: 10px;
        }

        .sidebar-header {
            padding: 30px 25px 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px 20px 0 0;
            margin: 20px 20px 0 20px;
        }

        .sidebar-header h2 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-header p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .filter-content {
            padding: 25px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0 0 20px 20px;
            margin: 0 20px 20px 20px;
        }

        .filter-group {
            margin-bottom: 25px;
        }

        .filter-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .filter-input, .filter-select {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            color: white;
        }

        .filter-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
        }

        .filter-select option {
            background: var(--primary-dark);
            color: white;
        }

        .range-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .range-label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 6px;
            display: block;
            font-weight: 600;
        }

        /* Sort Section */
        .sort-section {
            margin: 30px 0;
            padding: 20px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        .sort-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .toggle-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .toggle-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 56px;
            height: 30px;
            flex-shrink: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.2);
            transition: 0.4s;
            border-radius: 30px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background: linear-gradient(135deg, var(--accent), #f0d077);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        /* Buttons */
        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #f0d077);
            color: var(--primary-dark);
            margin-bottom: 12px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--accent);
        }

        /* Mobile Filter Toggle */
        .mobile-filter-toggle {
            display: none;
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            color: var(--primary-dark);
            border: none;
            width: 65px;
            height: 65px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.5);
            z-index: 999;
            transition: all 0.3s ease;
        }

        .mobile-filter-toggle:hover {
            transform: scale(1.1);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 360px;
            padding: 30px 40px;
            transition: margin-left 0.3s ease;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 40px;
            text-align: center;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Results Bar */
        .results-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            margin-bottom: 30px;
        }

        .results-count {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .results-count strong {
            background: linear-gradient(135deg, var(--accent), #f0d077);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        .view-toggle {
            display: flex;
            gap: 10px;
        }

        .view-btn {
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.1rem;
        }

        .view-btn.active {
            background: linear-gradient(135deg, var(--accent), #f0d077);
            border-color: var(--accent);
            color: var(--primary-dark);
        }

        .view-btn:hover {
            transform: translateY(-2px);
            border-color: var(--accent);
        }

        /* Properties Grid */
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        /* List View */
        .properties-grid.list-view {
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .properties-grid.list-view .property-card {
            display: grid;
            grid-template-columns: 420px 1fr;
            height: auto;
        }

        .properties-grid.list-view .card-image-container {
            height: 100%;
            min-height: 320px;
        }

        .properties-grid.list-view .card-content {
            padding: 35px;
        }

        /* Property Card - Glassmorphic */
        .property-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(212, 175, 55, 0.3);
            border-color: rgba(212, 175, 55, 0.5);
        }

        /* Image Carousel */
        .card-image-container {
            position: relative;
            height: 280px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.3);
        }

        .carousel-image {
            position: absolute;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .carousel-image.active {
            opacity: 1;
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            opacity: 0;
            color: var(--primary);
            font-size: 1rem;
        }

        .property-card:hover .carousel-nav {
            opacity: 1;
        }

        .carousel-nav:hover {
            background: var(--accent);
            color: var(--primary-dark);
            transform: translateY(-50%) scale(1.15);
        }

        .carousel-nav.prev {
            left: 15px;
        }

        .carousel-nav.next {
            right: 15px;
        }

        /* Property Badges */
        .card-badges {
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            z-index: 10;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            backdrop-filter: blur(15px);
        }

        .badge-type {
            background: linear-gradient(135deg, var(--accent), #f0d077);
            color: var(--primary-dark);
        }

        .badge-listing {
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary);
        }

        .card-date {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary);
            z-index: 10;
        }

        .card-date i {
            color: var(--accent);
            margin-right: 5px;
        }

        /* Card Content */
        .card-content {
            padding: 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-price {
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 12px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3.3em;
        }

        .card-location {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 18px;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-location i {
            color: var(--accent);
        }

        /* Card Features */
        .card-features {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: auto;
        }

        .feature {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            flex: 1;
            transition: all 0.3s ease;
        }

        .feature:hover {
            background: rgba(212, 175, 55, 0.1);
            border-color: var(--accent);
        }

        .feature i {
            color: var(--accent);
            font-size: 1.2rem;
        }

        .feature-value {
            font-weight: 800;
            color: white;
            font-size: 1rem;
        }

        .feature-label {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 100px 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            grid-column: 1 / -1;
        }

        .no-results i {
            font-size: 6rem;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 25px;
        }

        .no-results h3 {
            font-size: 1.75rem;
            color: white;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .no-results p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }

        /* Loading */
        .loading {
            display: none;
            text-align: center;
            padding: 60px;
            grid-column: 1 / -1;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid var(--accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Link Styling */
        a {
            text-decoration: none;
            color: inherit;
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .properties-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            }
        }

        @media (max-width: 1200px) {
            .properties-grid.list-view .property-card {
                grid-template-columns: 380px 1fr;
            }
        }

        @media (max-width: 992px) {
            .filter-sidebar {
                transform: translateX(-100%);
            }

            .filter-sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .mobile-filter-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .properties-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 25px;
            }

            .properties-grid.list-view .property-card {
                grid-template-columns: 1fr;
            }

            .properties-grid.list-view .card-image-container {
                min-height: 260px;
            }
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2.5rem;
            }

            .results-bar {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .properties-grid {
                grid-template-columns: 1fr;
            }

            .filter-sidebar {
                width: 100%;
                max-width: 360px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .card-price {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    @php $navbarStyle = 'navbar-light'; @endphp
    @include('navbar')

    <div class="layout-container">
        <!-- Glassmorphic Sidebar Filter -->
        <aside class="filter-sidebar" id="filterSidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-sliders-h"></i> Filters</h2>
                <p>Find your dream property</p>
            </div>

            <div class="filter-content">
                <!-- Property Type -->
                <div class="filter-group">
                    <label class="filter-label">Property Type</label>
                    <select id="purpose-dropdown" class="filter-select">
                        <option value="">All Types</option>
                        <option value="villa">Villa</option>
                        <option value="house">House</option>
                        <option value="apartment">Apartment</option>
                        <option value="penthouse">Penthouse</option>
                        <option value="duplex">Duplex</option>
                        <option value="commercial">Commercial</option>
                        <option value="building">Building</option>
                        <option value="land">Land</option>
                    </select>
                </div>

                <!-- Listing Type -->
                <div class="filter-group">
                    <label class="filter-label">Listing Type</label>
                    <select id="property-type-dropdown" class="filter-select">
                        <option value="">All Listings</option>
                        <option value="sell">For Sale</option>
                        <option value="rent">For Rent</option>
                    </select>
                </div>

                <!-- City -->
                <div class="filter-group">
                    <label class="filter-label">City</label>
                    <select id="city-select" class="filter-select">
                        <option value="">Loading cities...</option>
                    </select>
                    <input type="hidden" id="city" name="city">
                </div>

                <!-- Area/District -->
                <div class="filter-group">
                    <label class="filter-label">Area</label>
                    <select id="area-select" class="filter-select" disabled>
                        <option value="">Select city first</option>
                    </select>
                    <input type="hidden" id="district" name="district">
                </div>

                <!-- Property ID -->
                <div class="filter-group">
                    <label class="filter-label">Property ID</label>
                    <input type="text" class="filter-input" id="property-id-input" placeholder="Enter property ID">
                </div>

                <!-- Search Keywords -->
                <div class="filter-group">
                    <label class="filter-label">Keywords</label>
                    <input type="text" class="filter-input" id="search-keywords-input" placeholder="Search by title, location...">
                </div>

                <!-- Area Range -->
                <div class="filter-group">
                    <label class="filter-label">Area (m²)</label>
                    <div class="range-inputs">
                        <div>
                            <span class="range-label">Min</span>
                            <input type="number" class="filter-input" id="min-area-input" placeholder="Min">
                        </div>
                        <div>
                            <span class="range-label">Max</span>
                            <input type="number" class="filter-input" id="max-area-input" placeholder="Max">
                        </div>
                    </div>
                </div>

                <!-- Price Range -->
                <div class="filter-group">
                    <label class="filter-label">Price ($)</label>
                    <div class="range-inputs">
                        <div>
                            <span class="range-label">Min</span>
                            <input type="number" class="filter-input" id="min-price-input" placeholder="Min">
                        </div>
                        <div>
                            <span class="range-label">Max</span>
                            <input type="number" class="filter-input" id="max-price-input" placeholder="Max">
                        </div>
                    </div>
                </div>

                <!-- Sort Section -->
                <div class="sort-section">
                    <div class="sort-title">Sort By</div>
                    <div class="toggle-container">
                        <span class="toggle-label">Date</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="toggle-switch">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label">Price</span>
                    </div>
                </div>

                <!-- Buttons -->
                <button class="btn btn-primary" id="search-button">
                    <i class="fas fa-search"></i> Search
                </button>
                <button class="btn btn-secondary" id="clear-filters">
                    <i class="fas fa-redo"></i> Clear Filters
                </button>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Mobile Filter Toggle -->
        <button class="mobile-filter-toggle" id="mobileFilterToggle">
            <i class="fas fa-filter"></i>
        </button>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Explore Properties</h1>
                <p>Find your perfect home from our exclusive collection</p>
            </div>

            <!-- Results Bar -->
            <div class="results-bar">
                <div class="results-count" id="results-counter">
                    Loading properties...
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" id="grid-view-btn" data-view="grid">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button class="view-btn" id="list-view-btn" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div class="loading" id="loading">
                <div class="loading-spinner"></div>
            </div>

            <!-- Properties Grid -->
            <div class="properties-grid" id="propertiesGrid">
                @if($properties->isEmpty())
                    <div class="no-results">
                        <i class="fas fa-home"></i>
                        <h3>No Properties Found</h3>
                        <p>Try adjusting your filters to see more results</p>
                    </div>
                @else
                    @foreach($properties as $property)
                    <div class="property-card"
                         data-type="{{ strtolower($property->type['category'] ?? '') }}"
                         data-listing="{{ strtolower($property->listing_type ?? '') }}"
                         data-price="{{ $property->price['usd'] ?? 0 }}"
                         data-date="{{ $property->created_at->timestamp }}">
                        <a href="{{ route('property.PropertyDetail', ['property_id' => $property->id]) }}">
                            <!-- Image Carousel -->
                            <div class="card-image-container">
                                @if(!empty($property->images) && count($property->images) > 0)
                                    @foreach($property->images as $index => $photo)
                                        <div class="carousel-image {{ $index == 0 ? 'active' : '' }}"
                                             style="background-image: url('{{ $photo }}');">
                                        </div>
                                    @endforeach

                                    @if(count($property->images) > 1)
                                        <div class="carousel-nav prev">
                                            <i class="fas fa-chevron-left"></i>
                                        </div>
                                        <div class="carousel-nav next">
                                            <i class="fas fa-chevron-right"></i>
                                        </div>
                                    @endif
                                @else
                                    <div class="carousel-image active"
                                         style="background-image: url('https://via.placeholder.com/400x300?text=No+Image');">
                                    </div>
                                @endif

                                <!-- Property Badges -->
                                <div class="card-badges">
                                    <span class="badge badge-type">{{ $property->type['category'] ?? 'Property' }}</span>
                                    <span class="badge badge-listing">{{ ucfirst($property->listing_type ?? 'N/A') }}</span>
                                </div>

                                <div class="card-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $property->created_at->format('M d, Y') }}
                                </div>
                            </div>
                        </a>

                        <!-- Card Content -->
                        <a href="{{ route('property.PropertyDetail', ['property_id' => $property->id]) }}">
                            <div class="card-content">
                                <div class="card-price">
                                    ${{ number_format($property->price['usd'] ?? 0) }}
                                </div>

                                <h3 class="card-title">
                                    {{ $property->name['en'] ?? 'Unnamed Property' }}
                                </h3>

                                <div class="card-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $property->address ?? 'Location not specified' }}</span>
                                </div>

                                <div class="card-features">
                                    <div class="feature">
                                        <i class="fas fa-bed"></i>
                                        <span class="feature-value">{{ $property->rooms['bedroom']['count'] ?? 0 }}</span>
                                        <span class="feature-label">Beds</span>
                                    </div>
                                    <div class="feature">
                                        <i class="fas fa-bath"></i>
                                        <span class="feature-value">{{ $property->rooms['bathroom']['count'] ?? 0 }}</span>
                                        <span class="feature-label">Baths</span>
                                    </div>
                                    <div class="feature">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span class="feature-value">{{ $property->area ?? 'N/A' }}</span>
                                        <span class="feature-label">m²</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                @endif
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $properties->links() }}
            </div>
        </main>
    </div>

    <script>
    $(document).ready(function () {
        let container = $('#propertiesGrid');
        let totalProperties = $('.property-card').length;
        let isotopeInstance = null;
        let locationSelector = null;

        // ==================== INITIALIZE ISOTOPE ====================
        function initializeIsotope() {
            container.imagesLoaded(function() {
                isotopeInstance = container.isotope({
                    itemSelector: '.property-card',
                    layoutMode: 'fitRows',
                    fitRows: {
                        gutter: 30
                    },
                    getSortData: {
                        date: function(item) {
                            return parseInt($(item).attr('data-date')) || 0;
                        },
                        price: function(item) {
                            return parseFloat($(item).attr('data-price')) || 0;
                        }
                    },
                    sortBy: 'date',
                    sortAscending: false
                });

                updateResultsCounter();
                console.log('✓ Isotope initialized with', totalProperties, 'properties');
            });
        }

        // ==================== IMAGE CAROUSEL ====================
        function initializeCarousel() {
            $('.card-image-container').each(function() {
                let $carousel = $(this);
                let images = $carousel.find('.carousel-image');
                let currentIndex = 0;

                if (images.length <= 1) return;

                function showImage(index) {
                    images.removeClass('active');
                    images.eq(index).addClass('active');
                }

                $carousel.find('.next').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    currentIndex = (currentIndex + 1) % images.length;
                    showImage(currentIndex);
                });

                $carousel.find('.prev').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    currentIndex = (currentIndex - 1 + images.length) % images.length;
                    showImage(currentIndex);
                });
            });
        }

        // ==================== UPDATE RESULTS COUNTER ====================
        function updateResultsCounter() {
            let visibleCount = $('.property-card:visible').length;
            let filterText = '';

            if ($('#purpose-dropdown').val() || $('#property-type-dropdown').val() ||
                $('#city-select').val() || $('#area-select').val() ||
                $('#search-keywords-input').val() || $('#property-id-input').val() ||
                $('#min-area-input').val() || $('#max-area-input').val() ||
                $('#min-price-input').val() || $('#max-price-input').val()) {
                filterText = ' (filtered)';
            }

            $('#results-counter').html(`Showing <strong>${visibleCount}</strong> of <strong>${totalProperties}</strong> properties${filterText}`);
        }

        // ==================== SEARCH AND FILTER ====================
        function performSearch() {
            if (!isotopeInstance) {
                console.warn('⚠ Isotope not initialized yet');
                return;
            }

            // Get all filter values
            let searchTerm = $('#search-keywords-input').val().toLowerCase().trim();
            let propertyId = $('#property-id-input').val().toLowerCase().trim();
            let minArea = parseFloat($('#min-area-input').val());
            let maxArea = parseFloat($('#max-area-input').val());
            let minPrice = parseFloat($('#min-price-input').val());
            let maxPrice = parseFloat($('#max-price-input').val());
            let purposeFilter = $('#purpose-dropdown').val().toLowerCase().trim();
            let listingTypeFilter = $('#property-type-dropdown').val().toLowerCase().trim();
            let selectedCityName = $('#city').val().toLowerCase().trim();
            let selectedAreaName = $('#district').val().toLowerCase().trim();

            console.log('🔍 Filtering with:', {
                searchTerm, propertyId, minArea, maxArea, minPrice, maxPrice,
                purposeFilter, listingTypeFilter, selectedCityName, selectedAreaName
            });

            let visibleCount = 0;

            // Remove existing no-results
            $('.no-results').remove();

            // Apply Isotope filter
            isotopeInstance.arrange({
                filter: function() {
                    let $card = $(this);

                    let cardTitle = ($card.find('.card-title').text() || '').toLowerCase().trim();
                    let cardLocation = ($card.find('.card-location span').text() || '').toLowerCase().trim();
                    let cardPrice = parseFloat($card.attr('data-price')) || 0;
                    let cardAreaText = ($card.find('.feature-value').last().text() || '').trim();
                    let cardArea = parseFloat(cardAreaText) || 0;
                    let cardType = ($card.attr('data-type') || '').toLowerCase().trim();
                    let cardListing = ($card.attr('data-listing') || '').toLowerCase().trim();
                    let cardHref = ($card.find('a').first().attr('href') || '').toLowerCase();

                    // Filter logic
                    let matchesSearch = !searchTerm || cardTitle.includes(searchTerm) || cardLocation.includes(searchTerm);
                    let matchesPropertyId = !propertyId || cardHref.includes(propertyId);

                    let matchesArea = true;
                    if (!isNaN(minArea) && cardArea < minArea) matchesArea = false;
                    if (!isNaN(maxArea) && cardArea > maxArea) matchesArea = false;

                    let matchesPrice = true;
                    if (!isNaN(minPrice) && cardPrice < minPrice) matchesPrice = false;
                    if (!isNaN(maxPrice) && cardPrice > maxPrice) matchesPrice = false;

                    let matchesPurpose = !purposeFilter || cardType === purposeFilter;
                    let matchesListingType = !listingTypeFilter || cardListing === listingTypeFilter;
                    let matchesCity = !selectedCityName || cardLocation.includes(selectedCityName);
                    let matchesSelectedArea = !selectedAreaName || cardLocation.includes(selectedAreaName);

                    let isVisible = matchesSearch && matchesPropertyId && matchesArea &&
                                   matchesPrice && matchesPurpose && matchesListingType &&
                                   matchesCity && matchesSelectedArea;

                    if (isVisible) visibleCount++;

                    return isVisible;
                }
            });

            console.log(`📊 ${visibleCount} of ${totalProperties} properties visible`);

            // Update counter
            updateResultsCounter();

            // Handle no results
            setTimeout(function() {
                let visibleCards = $('.property-card:visible').length;

                if (visibleCards === 0) {
                    if ($('.no-results').length === 0) {
                        container.append(`
                            <div class="no-results">
                                <i class="fas fa-search"></i>
                                <h3>No Properties Found</h3>
                                <p>Try adjusting your filters to see more results</p>
                            </div>
                        `);
                    }
                } else {
                    $('.no-results').remove();
                }
            }, 200);
        }

        // ==================== SORT TOGGLE ====================
        $('#toggle-switch').change(function() {
            if (!isotopeInstance) return;

            if ($(this).is(':checked')) {
                isotopeInstance.arrange({
                    sortBy: 'price',
                    sortAscending: true
                });
                console.log('✓ Sorted by: Price (Low to High)');
            } else {
                isotopeInstance.arrange({
                    sortBy: 'date',
                    sortAscending: false
                });
                console.log('✓ Sorted by: Date (Newest First)');
            }
        });

        // ==================== VIEW TOGGLE ====================
        $('.view-btn').on('click', function() {
            const view = $(this).data('view');

            $('.view-btn').removeClass('active');
            $(this).addClass('active');

            if (view === 'list') {
                container.removeClass('grid-view').addClass('list-view');
            } else {
                container.removeClass('list-view').addClass('grid-view');
            }

            if (isotopeInstance) {
                setTimeout(() => {
                    isotopeInstance.arrange();
                }, 100);
            }
        });

        // ==================== CLEAR FILTERS ====================
        $('#clear-filters').click(function() {
            console.log('🧹 Clearing all filters...');

            $('#purpose-dropdown').val('');
            $('#property-type-dropdown').val('');
            $('#city-select').val('');
            $('#area-select').html('<option value="">Select city first</option>').prop('disabled', true);
            $('#city').val('');
            $('#district').val('');
            $('#property-id-input').val('');
            $('#search-keywords-input').val('');
            $('#min-area-input').val('');
            $('#max-area-input').val('');
            $('#min-price-input').val('');
            $('#max-price-input').val('');
            $('#toggle-switch').prop('checked', false);

            if (isotopeInstance) {
                isotopeInstance.arrange({
                    sortBy: 'date',
                    sortAscending: false,
                    filter: '*'
                });
            }

            updateResultsCounter();
            console.log('✓ Filters cleared');
        });

        // ==================== EVENT LISTENERS ====================

        // Debounce for text inputs
        let searchTimeout;
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 400);
        }

        $('#search-keywords-input, #property-id-input, #min-area-input, #max-area-input, #min-price-input, #max-price-input')
            .on('input', debounceSearch);

        $('#purpose-dropdown, #property-type-dropdown').on('change', performSearch);

        $('#search-button').click(function() {
            console.log('🔍 Search button clicked');
            performSearch();

            if (window.innerWidth <= 992) {
                $('#filterSidebar').removeClass('active');
                $('#sidebarOverlay').removeClass('active');
            }
        });

        $('.filter-input, .filter-select').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                performSearch();
            }
        });

        // ==================== MOBILE FILTER TOGGLE ====================
        $('#mobileFilterToggle, #sidebarOverlay').click(function() {
            $('#filterSidebar').toggleClass('active');
            $('#sidebarOverlay').toggleClass('active');
        });

        // ==================== INITIALIZE LOCATION SELECTOR ====================
        async function initializeLocationSelector() {
            try {
                locationSelector = new LocationSelector({
                    citySelectId: 'city-select',
                    areaSelectId: 'area-select',
                    cityInputId: 'city',
                    districtInputId: 'district',
                    onCityChange: function(city) {
                        console.log('City changed:', city.nameEn);
                        performSearch();
                    },
                    onAreaChange: function(area) {
                        console.log('Area changed:', area.nameEn);
                        performSearch();
                    }
                });

                await locationSelector.init();
                console.log('✓ LocationSelector initialized');
            } catch (error) {
                console.error('✗ Failed to initialize LocationSelector:', error);
            }
        }

        // ==================== INITIALIZE EVERYTHING ====================
        console.log('🏠 Dream Mulk - Initializing Property Listings...');
        console.log('📦 Total properties:', totalProperties);

        initializeIsotope();
        initializeCarousel();
        initializeLocationSelector();

        $(window).resize(function() {
            if (window.innerWidth > 992) {
                $('#filterSidebar').removeClass('active');
                $('#sidebarOverlay').removeClass('active');
            }

            if (isotopeInstance) {
                setTimeout(() => isotopeInstance.arrange(), 200);
            }
        });

        console.log('✓ Initialization complete');
    });
    </script>
</body>
</html>
