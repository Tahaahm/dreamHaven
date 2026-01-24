<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <title>Dream Haven - Property Listings</title>

    <style>
        :root {
            --primary-color: #667eea;
            --primary-light: #764ba2;
            --primary-dark: #5568d3;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --shadow-sm: 0 2px 8px rgba(102, 126, 234, 0.08);
            --shadow-md: 0 4px 16px rgba(102, 126, 234, 0.12);
            --shadow-lg: 0 8px 32px rgba(102, 126, 234, 0.16);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f1f5f9;
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Main Layout */
        .layout-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Left Sidebar Filter - FIXED POSITION */
        .filter-sidebar {
            width: 320px;
            background: white;
            box-shadow: var(--shadow-md);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .filter-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .filter-sidebar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .filter-sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            border-radius: 10px;
        }

        .sidebar-header {
            padding: 30px 25px 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
            margin-top: 55px;
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header p {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .cache-info {
            margin-top: 10px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cache-info i {
            font-size: 0.875rem;
        }

        .cache-refresh-btn {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-size: 0.75rem;
            transition: all 0.3s ease;
        }

        .cache-refresh-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .filter-content {
         
            padding: 25px;
        }

        .filter-group {
            margin-bottom: 25px;
        }

        .filter-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-input, .filter-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: white;
            color: var(--text-dark);
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .range-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .range-label {
            font-size: 0.75rem;
            color: var(--text-gray);
            margin-bottom: 5px;
            display: block;
        }

        /* Sort Toggle */
        .sort-section {
            margin: 30px 0;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 12px;
            border: 2px solid var(--border-color);
        }

        .sort-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .toggle-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .toggle-label {
            font-size: 0.875rem;
            color: var(--text-gray);
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
            background: #cbd5e1;
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
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        /* Buttons */
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            margin-bottom: 12px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--bg-light);
        }

        /* Mobile Filter Toggle */
        .mobile-filter-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            z-index: 999;
            transition: all 0.3s ease;
        }

        .mobile-filter-toggle:hover {
            transform: scale(1.1);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 320px;
            margin-top: 40px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .page-header p {
            font-size: 1.1rem;
            color: var(--text-gray);
        }

        /* Results Bar */
        .results-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
        }

        .results-count {
            font-size: 1rem;
            color: var(--text-gray);
        }

        .results-count strong {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        .view-toggle {
            display: flex;
            gap: 10px;
        }

        .view-btn {
            padding: 10px 14px;
            background: var(--bg-light);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-gray);
            font-size: 1.1rem;
        }

        .view-btn.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            border-color: var(--primary-color);
            color: white;
        }

        .view-btn:hover {
            transform: translateY(-2px);
        }

        /* Properties Grid */
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        /* List View */
        .properties-grid.list-view {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .properties-grid.list-view .property-card {
            display: grid;
            grid-template-columns: 400px 1fr;
            height: auto;
        }

        .properties-grid.list-view .card-image-container {
            height: 100%;
            min-height: 300px;
        }

        .properties-grid.list-view .card-content {
            padding: 30px;
        }

        /* Property Card */
        .property-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        /* Image Carousel */
        .card-image-container {
            position: relative;
            height: 260px;
            overflow: hidden;
            background: #e2e8f0;
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            opacity: 0;
            color: var(--primary-color);
            font-size: 0.9rem;
        }

        .property-card:hover .carousel-nav {
            opacity: 1;
        }

        .carousel-nav:hover {
            background: white;
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-nav.prev {
            left: 12px;
        }

        .carousel-nav.next {
            right: 12px;
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
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            backdrop-filter: blur(10px);
        }

        .badge-type {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
        }

        .badge-listing {
            background: rgba(255, 255, 255, 0.95);
            color: var(--primary-color);
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .card-date {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-gray);
            z-index: 10;
        }

        .card-date i {
            color: var(--primary-color);
            margin-right: 4px;
        }

        /* Card Content */
        .card-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-price {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3em;
        }

        .card-location {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-gray);
            font-size: 0.875rem;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .card-location i {
            color: var(--primary-color);
        }

        /* Card Features */
        .card-features {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: auto;
        }

        .feature {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 10px;
            background: var(--bg-light);
            border-radius: 10px;
            flex: 1;
        }

        .feature i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .feature-value {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .feature-label {
            font-size: 0.7rem;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            grid-column: 1 / -1;
        }

        .no-results i {
            font-size: 5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .no-results h3 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .no-results p {
            color: var(--text-gray);
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        /* Link Styling */
        a {
            text-decoration: none;
            color: inherit;
        }

        /* Loading */
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
            grid-column: 1 / -1;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .properties-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 25px;
            }

            .properties-grid.list-view .property-card {
                grid-template-columns: 350px 1fr;
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
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }

            .properties-grid.list-view .property-card {
                grid-template-columns: 1fr;
            }

            .properties-grid.list-view .card-image-container {
                min-height: 250px;
            }
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
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
                max-width: 320px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }

            .page-header h1 {
                font-size: 1.75rem;
            }

            .card-price {
                font-size: 1.5rem;
            }
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
    </style>
</head>
<body>
    @php $navbarStyle = 'navbar-light'; @endphp
    @include('navbar')

    <div class="layout-container">
        <!-- Left Sidebar Filter -->
        <aside class="filter-sidebar" id="filterSidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-sliders-h"></i> Filters</h2>
                <p>Find your perfect property</p>
                <div class="cache-info" id="cacheInfo" style="display: none;">
                    <i class="fas fa-clock"></i>
                    <span id="cacheStatus">Loading...</span>
                    <button class="cache-refresh-btn" id="refreshCacheBtn" title="Refresh cities data">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
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
                        <option value="property">Property</option>
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
                    <select id="city-dropdown" class="filter-select">
                        <option value="">Loading cities...</option>
                    </select>
                    <button type="button" id="retry-cities-btn" class="btn btn-secondary" style="display: none; margin-top: 8px; padding: 8px; font-size: 0.8rem;">
                        <i class="fas fa-redo"></i> Retry Loading Cities
                    </button>
                </div>

                <!-- Region/Area -->
                <div class="filter-group">
                    <label class="filter-label">Area</label>
                    <select id="area-dropdown" class="filter-select">
                        <option value="">Select city first</option>
                    </select>
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

                <!-- Debug Toggle (Development Only) -->
                <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 0.75rem;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="debug-mode" style="cursor: pointer;">
                        <span>Show Filter Debug Info (Console)</span>
                    </label>
                </div>

                <!-- API Diagnostics -->
                <div id="api-diagnostics" style="margin-top: 15px; padding: 10px; background: #e8f4f8; border-radius: 8px; font-size: 0.75rem; display: none;">
                    <div style="font-weight: 600; margin-bottom: 8px; color: #1e293b;">
                        <i class="fas fa-stethoscope"></i> API Diagnostics
                    </div>
                    <div style="font-size: 0.7rem; color: #64748b; line-height: 1.6;">
                        <div><strong>Endpoint:</strong> <code style="background: white; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem;">/v1/api/location/cities</code></div>
                        <div style="margin-top: 4px;"><strong>Status:</strong> <span id="api-status">Checking...</span></div>
                        <div style="margin-top: 4px;"><strong>Cache:</strong> <span id="cache-status">Checking...</span></div>
                        <button type="button" id="test-api-btn" style="margin-top: 8px; padding: 4px 8px; background: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.7rem; width: 100%;">
                            <i class="fas fa-vial"></i> Test API Connection
                        </button>
                    </div>
                </div>

                <!-- Toggle Diagnostics -->
                <div style="margin-top: 10px; text-align: center;">
                    <button type="button" id="toggle-diagnostics" style="background: none; border: none; color: #64748b; cursor: pointer; font-size: 0.75rem; text-decoration: underline;">
                        Show API Diagnostics
                    </button>
                </div>
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
    let citiesCache = null;
    let areasCache = {};
    let isotopeInstance = null;

    // ==================== CACHE CONFIGURATION ====================
    const CACHE_DURATION = 24 * 60 * 60 * 1000;
    const CITIES_CACHE_KEY = 'dream_haven_cities_cache';
    const CITIES_CACHE_TIMESTAMP_KEY = 'dream_haven_cities_cache_timestamp';
    const AREAS_CACHE_KEY = 'dream_haven_areas_cache';
    const AREAS_CACHE_TIMESTAMP_KEY = 'dream_haven_areas_cache_timestamp';
    const MAX_RETRIES = 3;

    // ==================== CACHE UTILITIES ====================
    function isCacheValid(timestampKey) {
        const timestamp = localStorage.getItem(timestampKey);
        if (!timestamp) return false;
        const cacheAge = Date.now() - parseInt(timestamp);
        return cacheAge < CACHE_DURATION;
    }

    // ==================== FETCH CITIES ====================
    async function fetchCities(retryCount = 0) {
        try {
            $('#retry-cities-btn').hide();

            if (isCacheValid(CITIES_CACHE_TIMESTAMP_KEY)) {
                const cachedCities = localStorage.getItem(CITIES_CACHE_KEY);
                if (cachedCities) {
                    console.log('✓ Loading cities from cache...');
                    citiesCache = JSON.parse(cachedCities);
                    populateCitiesDropdown();
                    return citiesCache;
                }
            }

            console.log(`⟳ Fetching cities from API (${retryCount + 1}/${MAX_RETRIES})...`);
            $('#city-dropdown').html('<option value="">Loading cities...</option>');

            const response = await fetch('/v1/api/location/cities', {
                method: 'GET',
                headers: {
                    'Accept-Language': 'en',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success && data.data && Array.isArray(data.data)) {
                citiesCache = data.data;

                try {
                    localStorage.setItem(CITIES_CACHE_KEY, JSON.stringify(citiesCache));
                    localStorage.setItem(CITIES_CACHE_TIMESTAMP_KEY, Date.now().toString());
                    console.log('✓ Cities cached');
                } catch (e) {
                    console.warn('⚠ Failed to cache cities:', e);
                }

                populateCitiesDropdown();
                console.log(`✓ Loaded ${citiesCache.length} cities`);
                return citiesCache;
            } else {
                throw new Error('Invalid API response format');
            }
        } catch (error) {
            console.error(`✗ Error fetching cities (${retryCount + 1}):`, error);

            if (retryCount < MAX_RETRIES - 1) {
                console.log(`⟳ Retrying in 2 seconds...`);
                $('#city-dropdown').html(`<option value="">Retrying... (${retryCount + 2}/${MAX_RETRIES})</option>`);
                await new Promise(resolve => setTimeout(resolve, 2000));
                return fetchCities(retryCount + 1);
            }

            const cachedCities = localStorage.getItem(CITIES_CACHE_KEY);
            if (cachedCities) {
                console.warn('⚠ Using expired cache');
                citiesCache = JSON.parse(cachedCities);
                populateCitiesDropdown();
                return citiesCache;
            }

            $('#city-dropdown').html('<option value="">⚠ Error loading cities</option>');
            $('#retry-cities-btn').show();
            throw error;
        }
    }

    function populateCitiesDropdown() {
        const cityDropdown = $('#city-dropdown');
        cityDropdown.html('<option value="">All Cities</option>');

        if (citiesCache && citiesCache.length > 0) {
            citiesCache.forEach(city => {
                cityDropdown.append(`
                    <option value="${city.id}"
                            data-name-en="${city.city_name_en || ''}"
                            data-name-ar="${city.city_name_ar || ''}"
                            data-name-ku="${city.city_name_ku || ''}">
                        ${city.city_name_en}
                    </option>
                `);
            });
        }
    }

    // ==================== FETCH AREAS ====================
    async function fetchAreasByCity(cityId) {
        if (areasCache[cityId]) {
            console.log(`Using cached areas for city ${cityId}`);
            populateAreasDropdown(areasCache[cityId]);
            return;
        }

        const areasCacheKey = `${AREAS_CACHE_KEY}_${cityId}`;
        const areasTimestampKey = `${AREAS_CACHE_TIMESTAMP_KEY}_${cityId}`;

        if (isCacheValid(areasTimestampKey)) {
            const cachedAreas = localStorage.getItem(areasCacheKey);
            if (cachedAreas) {
                console.log(`Loading cached areas for city ${cityId}`);
                const areas = JSON.parse(cachedAreas);
                areasCache[cityId] = areas;
                populateAreasDropdown(areas);
                return;
            }
        }

        try {
            console.log(`Fetching areas for city ${cityId}...`);
            const response = await fetch(`/v1/api/location/branches/${cityId}/areas`, {
                headers: {
                    'Accept-Language': 'en',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.data) {
                areasCache[cityId] = data.data;

                try {
                    localStorage.setItem(areasCacheKey, JSON.stringify(data.data));
                    localStorage.setItem(areasTimestampKey, Date.now().toString());
                } catch (e) {
                    console.warn('Failed to cache areas:', e);
                }

                populateAreasDropdown(data.data);
            } else {
                throw new Error('Invalid API response');
            }
        } catch (error) {
            console.error('Error fetching areas:', error);
            $('#area-dropdown').html('<option value="">Error loading areas</option>');
        }
    }

    function populateAreasDropdown(areas) {
        const areaDropdown = $('#area-dropdown');
        areaDropdown.html('<option value="">All Areas</option>');

        if (areas && areas.length > 0) {
            areas.forEach(area => {
                areaDropdown.append(`
                    <option value="${area.id}"
                            data-name-en="${area.area_name_en || ''}"
                            data-name-ar="${area.area_name_ar || ''}"
                            data-name-ku="${area.area_name_ku || ''}">
                        ${area.area_name_en}
                    </option>
                `);
            });
        } else {
            areaDropdown.html('<option value="">No areas available</option>');
        }
    }

    // ==================== UPDATE RESULTS COUNTER ====================
    function updateResultsCounter() {
        let visibleCount = $('.property-card:visible').length;
        let filterText = '';

        if ($('#purpose-dropdown').val() || $('#property-type-dropdown').val() ||
            $('#city-dropdown').val() || $('#area-dropdown').val() ||
            $('#search-keywords-input').val() || $('#property-id-input').val() ||
            $('#min-area-input').val() || $('#max-area-input').val() ||
            $('#min-price-input').val() || $('#max-price-input').val()) {
            filterText = ' (filtered)';
        }

        $('#results-counter').html(`Showing <strong>${visibleCount}</strong> of <strong>${totalProperties}</strong> properties${filterText}`);
    }

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

    // ==================== SEARCH AND FILTER (FIXED) ====================
    function performSearch() {
        if (!isotopeInstance) {
            console.warn('⚠ Isotope not initialized yet');
            return;
        }

        const debugMode = $('#debug-mode').is(':checked');

        // Get all filter values
        let searchTerm = $('#search-keywords-input').val().toLowerCase().trim();
        let propertyId = $('#property-id-input').val().toLowerCase().trim();
        let minArea = parseFloat($('#min-area-input').val());
        let maxArea = parseFloat($('#max-area-input').val());
        let minPrice = parseFloat($('#min-price-input').val());
        let maxPrice = parseFloat($('#max-price-input').val());
        let purposeFilter = $('#purpose-dropdown').val().toLowerCase().trim();
        let listingTypeFilter = $('#property-type-dropdown').val().toLowerCase().trim();
        
        let selectedCityName = ($('#city-dropdown option:selected').data('name-en') || '').toLowerCase();
        let selectedAreaName = ($('#area-dropdown option:selected').data('name-en') || '').toLowerCase();

        if (debugMode) {
            console.log('🔍 FILTERING WITH:', {
                searchTerm, propertyId, minArea, maxArea, minPrice, maxPrice,
                purposeFilter, listingTypeFilter, 
                selectedCityName, selectedAreaName
            });
        }

        let visibleCount = 0;
        let debugResults = [];

        // Hide existing no-results message
        $('.no-results').remove();

        // Apply Isotope filter
        isotopeInstance.arrange({
            filter: function() {
                let $card = $(this);

                // Extract card data
                let cardTitle = ($card.find('.card-title').text() || '').toLowerCase().trim();
                let cardLocation = ($card.find('.card-location span').text() || '').toLowerCase().trim();
                let cardPrice = parseFloat($card.attr('data-price')) || 0;
                
                let cardAreaText = ($card.find('.feature-value').last().text() || '').trim();
                let cardArea = parseFloat(cardAreaText) || 0;

                let cardType = ($card.attr('data-type') || '').toLowerCase().trim();
                let cardListing = ($card.attr('data-listing') || '').toLowerCase().trim();
                let cardHref = ($card.find('a').first().attr('href') || '').toLowerCase();

                // ===== FILTER LOGIC =====

                // 1. Search keywords
                let matchesSearch = !searchTerm || 
                                   cardTitle.includes(searchTerm) || 
                                   cardLocation.includes(searchTerm);

                // 2. Property ID
                let matchesPropertyId = !propertyId || cardHref.includes(propertyId);

                // 3. Area range
                let matchesArea = true;
                if (!isNaN(minArea) && cardArea < minArea) matchesArea = false;
                if (!isNaN(maxArea) && cardArea > maxArea) matchesArea = false;

                // 4. Price range
                let matchesPrice = true;
                if (!isNaN(minPrice) && cardPrice < minPrice) matchesPrice = false;
                if (!isNaN(maxPrice) && cardPrice > maxPrice) matchesPrice = false;

                // 5. Property Type
                let matchesPurpose = !purposeFilter || cardType === purposeFilter;

                // 6. Listing Type
                let matchesListingType = !listingTypeFilter || cardListing === listingTypeFilter;

                // 7. City filter
                let matchesCity = !selectedCityName || 
                                 cardLocation.includes(selectedCityName);

                // 8. Area filter
                let matchesSelectedArea = !selectedAreaName || 
                                          cardLocation.includes(selectedAreaName);

                // Combine all filters
                let isVisible = matchesSearch && matchesPropertyId && matchesArea && 
                               matchesPrice && matchesPurpose && matchesListingType && 
                               matchesCity && matchesSelectedArea;

                if (debugMode && debugResults.length < 5) {
                    debugResults.push({
                        title: cardTitle.substring(0, 40) + '...',
                        type: cardType,
                        listing: cardListing,
                        price: '$' + cardPrice.toLocaleString(),
                        area: cardArea + 'm²',
                        location: cardLocation.substring(0, 30) + '...',
                        matches: {
                            search: matchesSearch,
                            propertyId: matchesPropertyId,
                            areaRange: matchesArea,
                            priceRange: matchesPrice,
                            type: matchesPurpose,
                            listing: matchesListingType,
                            city: matchesCity,
                            area: matchesSelectedArea
                        },
                        visible: isVisible
                    });
                }

                if (isVisible) visibleCount++;

                return isVisible;
            }
        });

        if (debugMode) {
            console.log('📊 FILTER RESULTS:', `${visibleCount} of ${totalProperties} properties visible`);
            if (debugResults.length > 0) {
                console.table(debugResults);
            }
        }

        // Update counter
        updateResultsCounter();

        // Handle no results message
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

        // Reset dropdowns
        $('#purpose-dropdown').val('');
        $('#property-type-dropdown').val('');
        $('#city-dropdown').val('');
        $('#area-dropdown').html('<option value="">Select city first</option>');

        // Reset inputs
        $('#property-id-input').val('');
        $('#search-keywords-input').val('');
        $('#min-area-input').val('');
        $('#max-area-input').val('');
        $('#min-price-input').val('');
        $('#max-price-input').val('');

        // Reset sort toggle
        $('#toggle-switch').prop('checked', false);

        // Reset sort to date
        if (isotopeInstance) {
            isotopeInstance.arrange({
                sortBy: 'date',
                sortAscending: false,
                filter: '*' // Show all items
            });
        }

        // Update counter
        updateResultsCounter();

        console.log('✓ Filters cleared - showing all properties');
    });

    // ==================== EVENT LISTENERS ====================

    // City change
    $('#city-dropdown').on('change', function() {
        const cityId = $(this).val();
        const areaDropdown = $('#area-dropdown');

        if (cityId) {
            areaDropdown.html('<option value="">Loading areas...</option>');
            fetchAreasByCity(cityId);
        } else {
            areaDropdown.html('<option value="">Select city first</option>');
        }

        performSearch();
    });

    // Area change
    $('#area-dropdown').on('change', performSearch);

    // Debounce for text inputs
    let searchTimeout;
    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 400);
    }

    // Text inputs - debounced
    $('#search-keywords-input, #property-id-input, #min-area-input, #max-area-input, #min-price-input, #max-price-input')
        .on('input', debounceSearch);

    // Dropdowns - immediate
    $('#purpose-dropdown, #property-type-dropdown').on('change', function() {
        console.log('Filter changed:', $(this).attr('id'), '=', $(this).val());
        performSearch();
    });

    // Search button - force immediate search
    $('#search-button').click(function() {
        console.log('🔍 Search button clicked');
        performSearch();

        // Close mobile sidebar
        if (window.innerWidth <= 992) {
            $('#filterSidebar').removeClass('active');
            $('#sidebarOverlay').removeClass('active');
        }
    });

    // Enter key
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

    // ==================== CACHE MANAGEMENT ====================
    function clearLocationCache() {
        try {
            localStorage.removeItem(CITIES_CACHE_KEY);
            localStorage.removeItem(CITIES_CACHE_TIMESTAMP_KEY);

            Object.keys(localStorage).forEach(key => {
                if (key.startsWith(AREAS_CACHE_KEY) || key.startsWith(AREAS_CACHE_TIMESTAMP_KEY)) {
                    localStorage.removeItem(key);
                }
            });

            citiesCache = null;
            areasCache = {};

            console.log('✓ Cache cleared');
            alert('Cache cleared! Reloading...');
            location.reload();
            return true;
        } catch (e) {
            console.error('✗ Error clearing cache:', e);
            return false;
        }
    }

    window.clearLocationCache = clearLocationCache;

    $('#refreshCacheBtn').on('click', function(e) {
        e.preventDefault();
        if (confirm('Refresh cities and areas data?')) {
            clearLocationCache();
        }
    });

    $('#retry-cities-btn').on('click', function() {
        console.log('Manual retry requested');
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Retrying...').prop('disabled', true);

        fetchCities().then(() => {
            $('#retry-cities-btn').html('<i class="fas fa-check"></i> Success!');
            setTimeout(() => $('#retry-cities-btn').hide(), 2000);
        }).catch(() => {
            $('#retry-cities-btn').html('<i class="fas fa-redo"></i> Retry Loading Cities').prop('disabled', false);
        });
    });

    // ==================== DIAGNOSTICS ====================
    $('#toggle-diagnostics').on('click', function() {
        const panel = $('#api-diagnostics');
        const btn = $(this);

        if (panel.is(':visible')) {
            panel.slideUp();
            btn.text('Show API Diagnostics');
        } else {
            panel.slideDown();
            btn.text('Hide API Diagnostics');
            updateDiagnostics();
        }
    });

    function updateDiagnostics() {
        const citiesCacheData = localStorage.getItem(CITIES_CACHE_KEY);
        const timestamp = localStorage.getItem(CITIES_CACHE_TIMESTAMP_KEY);

        if (citiesCacheData && timestamp) {
            const age = Math.floor((Date.now() - parseInt(timestamp)) / (1000 * 60 * 60));
            const isValid = isCacheValid(CITIES_CACHE_TIMESTAMP_KEY);
            $('#cache-status').html(`<span style="color: ${isValid ? 'green' : 'orange'};">${isValid ? '✓ Valid' : '⚠ Expired'} (${age}h old)</span>`);
        } else {
            $('#cache-status').html('<span style="color: gray;">No cache</span>');
        }

        if (citiesCacheData) {
            const cities = JSON.parse(citiesCacheData);
            $('#api-status').html(`<span style="color: green;">✓ ${cities.length} cities loaded</span>`);
        } else {
            $('#api-status').html('<span style="color: orange;">⚠ Not loaded</span>');
        }
    }

    $('#test-api-btn').on('click', async function() {
        const btn = $(this);
        const originalHtml = btn.html();

        btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
        $('#api-status').html('<span style="color: blue;">⟳ Testing...</span>');

        try {
            const startTime = Date.now();
            const response = await fetch('/v1/api/location/cities', {
                method: 'GET',
                headers: {
                    'Accept-Language': 'en',
                    'Accept': 'application/json'
                }
            });
            const duration = Date.now() - startTime;

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.data) {
                    $('#api-status').html(`<span style="color: green;">✓ Working (${duration}ms, ${data.data.length} cities)</span>`);
                    btn.html('<i class="fas fa-check"></i> Passed!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    $('#api-status').html(`<span style="color: red;">✗ Invalid response</span>`);
                    btn.html('<i class="fas fa-times"></i> Failed');
                }
            } else {
                $('#api-status').html(`<span style="color: red;">✗ HTTP ${response.status}</span>`);
                btn.html('<i class="fas fa-times"></i> Error');
            }
        } catch (error) {
            $('#api-status').html(`<span style="color: red;">✗ ${error.message}</span>`);
            btn.html('<i class="fas fa-times"></i> Failed');
        } finally {
            setTimeout(() => {
                btn.html(originalHtml).prop('disabled', false);
            }, 3000);
        }
    });

    // ==================== INITIALIZE ====================
    console.log('🏠 Dream Haven - Initializing Property Listings...');
    console.log('📦 Total properties:', totalProperties);

    // Initialize in order
    initializeIsotope();
    initializeCarousel();
    
    fetchCities().then(() => {
        console.log('✓ Cities loaded successfully');
    }).catch(err => {
        console.error('✗ Failed to load cities:', err);
    });

    // Window resize
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
    console.log('💡 Tip: Enable Debug Mode checkbox to see detailed filter info');
});
    </script>
</body>
</html>