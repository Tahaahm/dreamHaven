<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>
    <script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <title>Dream Mulk - Luxury Properties</title>

    <style>
        :root {
            --primary: #1E3A8A;
            --primary-dark: #172554;
            --primary-light: #3B82F6;
            --accent: #D97706;
            --bg-body: #F3F4F6;
            --bg-card: #FFFFFF;
            --bg-input: #F9FAFB;
            --text-main: #111827;
            --text-secondary: #6B7280;
            --border: #E5E7EB;
            --sidebar-width: 340px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; outline: none; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-main); line-height: 1.5; }
        h1, h2, h3 { font-family: 'Playfair Display', serif; }
        a { text-decoration: none; color: inherit; transition: 0.2s; }

        .layout-container { display: flex; min-height: 100vh; padding-top: 80px; }

        /* Sidebar */
        .filter-sidebar {
            width: var(--sidebar-width); position: fixed; top: 80px; left: 0; bottom: 0;
            background: var(--bg-card); border-right: 1px solid var(--border); z-index: 40;
            overflow-y: auto; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-header { position: sticky; top: 0; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); padding: 24px; border-bottom: 1px solid var(--border); z-index: 10; }
        .filter-content { padding: 24px; }
        .filter-group { margin-bottom: 24px; }
        .filter-label { display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 8px; }
        .filter-input, .filter-select {
            width: 100%; padding: 12px 16px; font-size: 0.95rem; color: var(--text-main);
            background: var(--bg-input); border: 1px solid var(--border); border-radius: var(--radius-md); transition: 0.2s;
        }
        .range-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .btn { display: flex; align-items: center; justify-content: center; padding: 14px; font-weight: 600; border-radius: var(--radius-md); cursor: pointer; border: none; width: 100%; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; margin-bottom: 10px; }
        .btn-secondary { background: white; border: 1px solid var(--border); color: var(--text-secondary); }

        /* Main Content */
        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 40px; transition: margin-left 0.3s ease; }
        .page-header { text-align: center; margin-bottom: 40px; }
        .results-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 15px 25px; border-radius: var(--radius-md); border: 1px solid var(--border); box-shadow: var(--shadow-sm); }

        /* Property Grid & Cards */
        .properties-grid { display: block; width: 100%; min-height: 500px; }
        .property-card { width: 33.333%; padding: 0 15px; margin-bottom: 30px; float: left; box-sizing: border-box; }
        .card-content-wrapper {
            background: white; border-radius: var(--radius-lg); border: 1px solid var(--border);
            overflow: hidden; height: 100%; display: flex; flex-direction: column;
            transition: transform 0.3s, box-shadow 0.3s; box-shadow: var(--shadow-sm);
        }
        .card-content-wrapper:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); border-color: var(--primary-light); }
        .card-image-container { position: relative; width: 100%; height: 260px; background: #e5e7eb; overflow: hidden; }
        .carousel-image { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0; transition: opacity 0.4s, transform 0.8s; }
        .carousel-image.active { opacity: 1; }
        .card-content-wrapper:hover .carousel-image { transform: scale(1.05); }

        .card-badges { position: absolute; top: 15px; left: 15px; right: 15px; display: flex; justify-content: space-between; z-index: 5; }
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; backdrop-filter: blur(8px); }
        .badge-type { background: var(--primary); color: white; }
        .badge-listing { background: rgba(255,255,255,0.9); color: var(--primary); }

        .card-body { padding: 24px; flex: 1; display: flex; flex-direction: column; }
        .card-price { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--primary); font-weight: 700; margin-bottom: 8px; }
        .card-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 8px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; }
        .card-location { font-size: 0.9rem; color: var(--text-secondary); display: flex; align-items: center; gap: 5px; margin-bottom: 20px; }
        .card-features { margin-top: auto; display: flex; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 15px; }
        .feature { font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 5px; }

        /* Pagination Fix */
        .pagination-container { margin-top: 50px; text-align: center; }
        .pagination-container svg { width: 16px !important; height: 16px !important; display: inline-block; }
        .pagination-container nav { display: inline-flex; gap: 5px; background: white; padding: 10px; border-radius: 8px; box-shadow: var(--shadow-sm); }
        .pagination-container span, .pagination-container a { padding: 8px 14px; border-radius: 6px; font-size: 0.9rem; }
        .pagination-container .active span { background: var(--primary); color: white; }

        /* Mobile */
        .mobile-filter-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary); color: white; border-radius: 50%; align-items: center; justify-content: center; font-size: 1.2rem; z-index: 100; border: none; box-shadow: var(--shadow-lg); }

        @media (max-width: 1200px) { .property-card { width: 50%; } }
        @media (max-width: 992px) {
            .filter-sidebar { transform: translateX(-100%); } .filter-sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 20px; } .mobile-filter-toggle { display: flex; }
        }
        @media (max-width: 768px) { .property-card { width: 100%; } }
    </style>
</head>
<body>

    @php $navbarStyle = 'navbar-light'; @endphp
    @include('navbar')

    <div class="layout-container">

        <aside class="filter-sidebar" id="filterSidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-sliders"></i> Filters</h2>
                <p>Refine your search</p>
            </div>
            <div class="filter-content">
                <div class="filter-group">
                    <label class="filter-label">Listing Type</label>
                    <select id="property-type-dropdown" class="filter-select">
                        <option value="">Buy or Rent</option>
                        <option value="sell">Buy</option>
                        <option value="rent">Rent</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Location</label>

                    <select id="city-dropdown" class="filter-select" style="margin-bottom: 10px;">
                        <option value="">Loading cities...</option>
                    </select>

                    <select id="area-dropdown" class="filter-select" disabled>
                        <option value="">Select City First</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Property Type</label>
                    <select id="purpose-dropdown" class="filter-select">
                        <option value="">All Types</option>
                        <option value="villa">Villa</option>
                        <option value="house">House</option>
                        <option value="apartment">Apartment</option>
                        <option value="commercial">Commercial</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Search</label>
                    <input type="text" id="search-keywords-input" class="filter-input" placeholder="Keywords (e.g. Pool)">
                </div>

                <div class="filter-group">
                    <label class="filter-label">Price Range ($)</label>
                    <div class="range-inputs">
                        <input type="number" id="min-price-input" class="filter-input" placeholder="Min">
                        <input type="number" id="max-price-input" class="filter-input" placeholder="Max">
                    </div>
                </div>

                <button class="btn btn-primary" id="search-button"><i class="fas fa-search"></i> Apply Filters</button>
                <button class="btn btn-secondary" id="clear-filters">Reset</button>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Dream Mulk Collection</h1>
                <p>Discover exclusive properties curated for you.</p>
            </div>

            <div class="results-bar">
                <div class="results-count" id="results-counter">
                    Showing <strong>{{ $properties->count() }}</strong> properties
                </div>
            </div>

            <div class="properties-grid" id="propertiesGrid">
                @foreach($properties as $property)
                <div class="property-card"
                     data-type="{{ strtolower($property->type['category'] ?? '') }}"
                     data-listing="{{ strtolower($property->listing_type ?? '') }}"
                     data-price="{{ $property->price['usd'] ?? 0 }}"
                     data-date="{{ $property->created_at->timestamp }}">

                    <div class="card-content-wrapper">
                        <div class="card-image-container">
                            <div class="card-badges">
                                <span class="badge badge-type">{{ $property->type['category'] ?? 'Property' }}</span>
                                <span class="badge badge-listing">{{ ucfirst($property->listing_type ?? 'N/A') }}</span>
                            </div>
                            @if(!empty($property->images) && count($property->images) > 0)
                                <div class="carousel-image active" style="background-image: url('{{ $property->images[0] }}');"></div>
                            @else
                                <div class="carousel-image active" style="background-image: url('https://via.placeholder.com/400x300?text=No+Image');"></div>
                            @endif
                        </div>

                        <div class="card-body">
                            <a href="{{ route('property.PropertyDetail', ['property_id' => $property->id]) }}">
                                <div class="card-price">${{ number_format($property->price['usd'] ?? 0) }}</div>
                                <h3 class="card-title">{{ $property->name['en'] ?? 'Exclusive Property' }}</h3>
                                <div class="card-location">
                                    <i class="fa-solid fa-location-dot"></i>
                                    {{ $property->address ?? 'Location info unavailable' }}
                                </div>
                            </a>
                            <div class="card-features">
                                <div class="feature"><i class="fa-solid fa-bed"></i> {{ $property->rooms['bedroom']['count'] ?? 0 }}</div>
                                <div class="feature"><i class="fa-solid fa-bath"></i> {{ $property->rooms['bathroom']['count'] ?? 0 }}</div>
                                <div class="feature"><i class="fa-solid fa-ruler-combined"></i> {{ $property->area ?? 0 }} m²</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="pagination-container">
                {{ $properties->links() }}
            </div>
        </main>

        <button class="mobile-filter-toggle" id="mobileFilterToggle"><i class="fas fa-filter"></i></button>
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:30;display:none;" id="sidebarOverlay"></div>
    </div>

    <script>
    /**
     * Dream Mulk - Location Selector Component
     */
    class LocationSelector {
        constructor(options = {}) {
            this.citySelectId = options.citySelectId || "city-select";
            this.areaSelectId = options.areaSelectId || "area-select";
            this.cityInputId = options.cityInputId || "city";
            this.districtInputId = options.districtInputId || "district";
            this.onCityChange = options.onCityChange || null;
            this.onAreaChange = options.onAreaChange || null;

            this.cities = [];
            this.currentCityId = options.selectedCityId || null;
            this.currentAreaId = options.selectedAreaId || null;
            this.initialized = false;
            this.isLoading = false;
        }

        async init() {
            if (this.isLoading) return;
            this.isLoading = true;
            try {
                await this.loadCities();
                this.setupEventListeners();
                if (this.currentCityId) {
                    await this.loadAreas(this.currentCityId);
                }
                this.initialized = true;
            } catch (error) {
                console.error("Failed to initialize LocationSelector:", error);
                this.showError("Failed to load location data. Please refresh the page.");
            } finally {
                this.isLoading = false;
            }
        }

        async loadCities() {
            try {
                console.log("Fetching cities...");
                const response = await fetch("/v1/api/location/branches", {
                    headers: { "Accept-Language": "en" },
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const result = await response.json();

                if (result.success && result.data && Array.isArray(result.data)) {
                    this.cities = result.data;
                    this.populateCitySelect();
                } else {
                    throw new Error("Invalid response format");
                }
            } catch (error) {
                console.error("Error loading cities:", error);
                const citySelect = document.getElementById(this.citySelectId);
                if (citySelect) citySelect.innerHTML = '<option value="">Error: Server issue</option>';
                throw error;
            }
        }

        populateCitySelect() {
            const citySelect = document.getElementById(this.citySelectId);
            if (!citySelect) return;
            citySelect.innerHTML = '<option value="">All Cities</option>';

            const sortedCities = [...this.cities].sort((a, b) => a.city_name_en.localeCompare(b.city_name_en));

            sortedCities.forEach((city) => {
                const option = document.createElement("option");
                option.value = city.id;
                option.textContent = `${city.city_name_en}`;
                // Set data attributes used by filtering logic
                option.dataset.nameEn = city.city_name_en;
                if (city.id == this.currentCityId) option.selected = true;
                citySelect.appendChild(option);
            });
        }

        async loadAreas(cityId) {
            try {
                const areaSelect = document.getElementById(this.areaSelectId);
                if (!areaSelect) return;
                areaSelect.innerHTML = '<option value="">Loading areas...</option>';
                areaSelect.disabled = true;

                const response = await fetch(`/v1/api/location/branches/${cityId}/areas`, {
                    headers: { "Accept-Language": "en" },
                });
                const result = await response.json();

                if (result.success && result.data) {
                    this.populateAreaSelect(result.data);
                }
            } catch (error) {
                console.error("Error loading areas:", error);
            } finally {
                const areaSelect = document.getElementById(this.areaSelectId);
                if (areaSelect) areaSelect.disabled = false;
            }
        }

        populateAreaSelect(areas) {
            const areaSelect = document.getElementById(this.areaSelectId);
            if (!areaSelect) return;
            areaSelect.innerHTML = '<option value="">All Areas</option>';

            const sortedAreas = [...areas].sort((a, b) => a.area_name_en.localeCompare(b.area_name_en));

            sortedAreas.forEach((area) => {
                const option = document.createElement("option");
                option.value = area.id;
                option.textContent = `${area.area_name_en}`;
                // Set data attributes used by filtering logic
                option.dataset.nameEn = area.area_name_en;
                if (area.id == this.currentAreaId) option.selected = true;
                areaSelect.appendChild(option);
            });
        }

        setupEventListeners() {
            const citySelect = document.getElementById(this.citySelectId);
            const areaSelect = document.getElementById(this.areaSelectId);

            if (citySelect) {
                citySelect.addEventListener("change", async (e) => {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    if (e.target.value) {
                        await this.loadAreas(e.target.value);
                        if (this.onCityChange) this.onCityChange(e.target.value);
                    } else {
                        if (areaSelect) {
                            areaSelect.innerHTML = '<option value="">Select City First</option>';
                            areaSelect.disabled = true;
                        }
                        if (this.onCityChange) this.onCityChange(null);
                    }
                });
            }

            if (areaSelect) {
                areaSelect.addEventListener("change", (e) => {
                    if (this.onAreaChange) this.onAreaChange(e.target.value);
                });
            }
        }

        showError(message) {
            console.error(message);
        }
    }
    </script>

    <script>
    $(document).ready(function() {
        var $grid = $('#propertiesGrid');
        var isotopeInitialized = false;

        // Isotope Init
        $grid.imagesLoaded(function() {
            $grid.isotope({
                itemSelector: '.property-card',
                percentPosition: true,
                layoutMode: 'fitRows'
            });
            isotopeInitialized = true;
        });

        // Filter Logic
        function performFilter() {
            if(!isotopeInitialized) return;

            var keyword = $('#search-keywords-input').val().toLowerCase();
            var minPrice = parseFloat($('#min-price-input').val()) || 0;
            var maxPrice = parseFloat($('#max-price-input').val()) || Infinity;
            var type = $('#purpose-dropdown').val().toLowerCase();
            var listing = $('#property-type-dropdown').val().toLowerCase();

            // Get location names from dataset (populated by LocationSelector)
            var cityName = ($('#city-dropdown option:selected').data('nameEn') || '').toLowerCase();
            var areaName = ($('#area-dropdown option:selected').data('nameEn') || '').toLowerCase();

            $grid.isotope({
                filter: function() {
                    var $this = $(this);
                    var text = $this.text().toLowerCase(); // Contains title, address, features
                    var price = parseFloat($this.attr('data-price'));
                    var pType = $this.attr('data-type');
                    var pListing = $this.attr('data-listing');

                    var checkKeyword = !keyword || text.indexOf(keyword) > -1;
                    var checkPrice = price >= minPrice && price <= maxPrice;
                    var checkType = !type || pType === type;
                    var checkListing = !listing || pListing === listing;

                    // Filter by location name presence in the card text/address
                    var checkCity = !cityName || text.indexOf(cityName) > -1;
                    var checkArea = !areaName || text.indexOf(areaName) > -1;

                    return checkKeyword && checkPrice && checkType && checkListing && checkCity && checkArea;
                }
            });

            setTimeout(function(){
                $('#results-counter strong').text($grid.data('isotope').filteredItems.length);
            }, 100);
        }

        // Initialize Location Selector
        const locSelector = new LocationSelector({
            citySelectId: "city-dropdown",
            areaSelectId: "area-dropdown",
            onCityChange: function() { performFilter(); }, // Auto-filter on change
            onAreaChange: function() { performFilter(); }  // Auto-filter on change
        });

        locSelector.init();

        // Bind other inputs
        $('#search-button').on('click', performFilter);
        $('#clear-filters').on('click', function() {
            $('input').val('');
            $('select').val('');
            // Reset location dropdowns manually if needed or reload page
            $grid.isotope({ filter: '*' });
        });

        // Mobile Toggle
        $('#mobileFilterToggle, #sidebarOverlay').on('click', function() {
            $('#filterSidebar').toggleClass('active');
            $('#sidebarOverlay').toggle();
        });

        // Resize Fix
        $(window).resize(function() {
            if(window.innerWidth > 992) {
                $('#filterSidebar').removeClass('active');
                $('#sidebarOverlay').hide();
            }
            if(isotopeInitialized) $grid.isotope('layout');
        });
    });
    </script>
</body>
</html>
