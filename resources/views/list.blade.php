<!DOCTYPE html>
<html>
<head>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.4.6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">

    <title>Sorting List</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('../css/list-style.css') }}">
</head>
<body class="black-navbar">
    @include('navbar')
    <div class="allin">
    <div class="search-sort-container">
        <h3>ADVANCED SEARCH</h3>
        <div class="dropdown-container">
            <select id="purpose-dropdown" class="filter-dropdown">
                <option value="">All Home Types</option>
                <option value="house">House</option>
                <option value="building">Building</option>
                <option value="apartment">Apartment</option>
                <option value="property">Property</option>
            </select>
            <select id="property-type-dropdown" class="filter-dropdown">
                <option value="">All Types</option>
                <option value="sell">Sell</option>
                <option value="rent">Rent</option>
            </select>
            <select class="filter-dropdown">
                <option value="">City</option>
                <option value="new-york">New York</option>
                <option value="los-angeles">Los Angeles</option>
            </select>
            <select class="filter-dropdown">
                <option value="">Region</option>
                <option value="region-1">Region 1</option>
                <option value="region-2">Region 2</option>
            </select>
            <select class="filter-dropdown">
                <option value="">Project</option>
                <option value="project-1">Project 1</option>
                <option value="project-2">Project 2</option>
            </select>
        </div>
        <input class="search-input" id="property-id-input" type="text" placeholder="Property id">
        <input class="search-input" id="search-keywords-input" type="text" placeholder="Search keywords">
        <div class="input-group">
            <input class="search-input" id="min-area-input" type="text" placeholder="Min area (m²)">
            <input class="search-input" id="max-area-input" type="text" placeholder="Max area (m²)">
        </div>
        <div class="input-group">
            <input class="search-input" id="min-price-input" type="text" placeholder="Min price ($)">
            <input class="search-input" id="max-price-input" type="text" placeholder="Max price ($)">
        </div>
        <div class="switch-button-group">
            <span class="switch-text">SORT BY DATE</span>
            <label class="toggle-switch">
                <input type="checkbox" id="toggle-switch">
                <span class="slider"></span>
            </label>
            <span class="switch-text">CHEAPEST PRICE</span>
        </div>
        <a href="#" style="color: #1ea69a; text-decoration: none;">+ Show more options</a>
        <button class="search-button" id="search-button">SEARCH</button>
    </div>
   
  <div class="container">
    @if($properties->isEmpty())
        <div id="no-products-message">No properties found</div>
    @endif

    <ul>
        @foreach($properties as $property)
        
            <li>



                <div class="item-container">
                    {{-- Link wrapper --}}
                    <a href="{{ route('property.PropertyDetail', ['property_id' => $property->id]) }}">
                        {{-- Image carousel --}}
                        <div class="background-image-container">
                            
<div class="background-image-container">
<div class="background-image-container">
    @foreach($property->images as $index => $photo)
        <div class="background-image{{ $index == 0 ? ' active' : '' }}" 
             style="background-image: url('{{ $photo }}');">
        </div>
    @endforeach
</div>


</div>












                            <div class="arrow prev"><i class="fas fa-chevron-left"></i></div>
                            <div class="arrow next"><i class="fas fa-chevron-right"></i></div>
                            <div class="item-date">
                                <i class="fas fa-calendar-alt"></i> {{ $property->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                    </a>

                    {{-- Property type & listing type --}}
                    <div class="item-home-type">{{ $property->type['category'] ?? 'N/A' }}</div>
                    <div class="item-type-of-home">{{ ucfirst($property->listing_type) }}</div>

                    {{-- Details section --}}
<a href="{{ route('property.PropertyDetail', ['property_id' => $property->id]) }}">
                            <div class="detail-of-home">
                            <div class="item-price">
                                ${{ number_format($property->price['usd'] ?? 0) }}
                            </div>

                            <div class="title">
                                {{ $property->name['en'] ?? 'Unnamed Property' }}
                            </div>

                            <div class="item-location">
                                <i class="fas fa-map-marker-alt"></i> {{ $property->address ?? 'N/A' }}
                            </div>

                            <div class="item-info">
                                <span><i class="fas fa-bed"></i> {{ $property->rooms['bedroom']['count'] ?? 0 }} Bed</span>
                                <span><i class="fas fa-bath"></i> {{ $property->rooms['bathroom']['count'] ?? 0 }} Bath</span>
                                <span><i class="fas fa-ruler-combined"></i> {{ $property->area ?? 'N/A' }} m²</span>
                            </div>
                        </div>
                    </a>
                </div>
            </li>
        @endforeach
    </ul>

    {{-- Pagination links --}}
    <div class="mt-6">
        {{ $properties->links() }}
    </div>
</div>



    <script>
     $(document).ready(function () {
    let container = $('.container');

    function initializeIsotope() {
        container.isotope({
            itemSelector: '.item-container',
            getSortData: {
                date: function (item) {
                    const dateStr = $(item).find('.item-date').text();
                    const dateParts = dateStr.split('/');
                    const year = parseInt(dateParts[2]);
                    const month = parseInt(dateParts[1]) - 1;
                    const day = parseInt(dateParts[0]);
                    return new Date(year, month, day);
                },
                number: function (item) {
                    const priceStr = $(item).find('.item-price').text();
                    const price = parseFloat(priceStr.replace(/[^0-9.-]+/g, ""));
                    return price;
                }
            }
        });
        container.isotope({ sortBy: 'date', sortAscending: false });
    }

    function sortByDate() {
        container.isotope({ sortBy: 'date', sortAscending: false });
    }

    function sortByPrice() {
        container.isotope({ sortBy: 'number', sortAscending: true });
    }

    function initializeSwipe() {
        $('.background-image-container').each(function() {
            let $this = $(this);
            let images = $this.find('.background-image');
            let currentIndex = 0;

            function showImage(index) {
                images.removeClass('active');
                images.eq(index).addClass('active');
            }

            $this.find('.next').on('click', function() {
                currentIndex = (currentIndex + 1) % images.length;
                showImage(currentIndex);
            });

            $this.find('.prev').on('click', function() {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                showImage(currentIndex);
            });

            showImage(currentIndex); // Ensure the first image is shown on load
        });
    }

    function performSearch() {
        let searchTerm = $('#search-keywords-input').val().toLowerCase();
        let propertyId = $('#property-id-input').val().toLowerCase();
        let minArea = parseFloat($('#min-area-input').val());
        let maxArea = parseFloat($('#max-area-input').val());
        let minPrice = parseFloat($('#min-price-input').val().replace(/[^0-9.-]+/g, ""));
        let maxPrice = parseFloat($('#max-price-input').val().replace(/[^0-9.-]+/g, ""));

        // Collect all properties for Fuse.js
        let items = [];
        $('.item-container').each(function () {
            let title = $(this).find('.title').text();
            let location = $(this).find('.item-location').text();
            items.push({
                element: this,
                title: title,
                location: location
            });
        });

        let fuse = new Fuse(items, {
            keys: ['title', 'location'],
            threshold: 0.4 // Adjust this value for more or less fuzzy matching
        });

        let result = fuse.search(searchTerm);
        let filteredItems = result.map(res => res.item.element);

        container.isotope({
            filter: function () {
                let propertyTitle = $(this).find('.title').text().toLowerCase();
                let propertyLocation = $(this).find('.item-location').text().toLowerCase();
                let propertyPrice = parseFloat($(this).find('.item-price').text().replace(/[^0-9.-]+/g, ""));
                let propertyArea = parseFloat($(this).find('.item-info span:nth-child(3)').text().replace(' m²', ''));
                let matchesSearch = searchTerm === "" || filteredItems.includes(this);
                let matchesPropertyId = propertyId === "" || $(this).find('.details-link').attr('href').toLowerCase().includes(propertyId);
                let matchesArea = (isNaN(minArea) || propertyArea >= minArea) && (isNaN(maxArea) || propertyArea <= maxArea);
                let matchesPrice = (isNaN(minPrice) || propertyPrice >= minPrice) && (isNaN(maxPrice) || propertyPrice <= maxPrice);

                return matchesSearch && matchesPropertyId && matchesArea && matchesPrice;
            }
        });

        // Show or hide the no-products-message based on the filtered items
        if ($('.item-container:visible').length === 0) {
            $('#no-products-message').show();
        } else {
            $('#no-products-message').hide();
        }

        initializeSwipe(); // Reinitialize swipe functionality after filtering
    }

    function filterProperties() {
        let purpose = $('#purpose-dropdown').val();
        let propertyType = $('#property-type-dropdown').val();

        container.isotope({
            filter: function () {
                let propertyPurpose = $(this).find('.item-home-type').text().toLowerCase();
                let propertyTypeOfHome = $(this).find('.item-type-of-home').text().toLowerCase();

                let matchesPurpose = purpose === "" || propertyPurpose === purpose.toLowerCase();
                let matchesPropertyType = propertyType === "" || propertyTypeOfHome === propertyType.toLowerCase();

                return matchesPurpose && matchesPropertyType;
            }
        });

        initializeSwipe(); // Reinitialize swipe functionality after filtering
    }

    $('#toggle-switch').change(function () {
        if ($(this).is(':checked')) {
            sortByPrice();
        } else {
            sortByDate();
        }
    });

    $('.filter-dropdown').change(filterProperties);
    $('#search-keywords-input, #property-id-input, #min-area-input, #max-area-input, #min-price-input, #max-price-input').on('input', performSearch);

    initializeIsotope();
    initializeSwipe(); // Initial swipe functionality setup
});

    </script>
</body>
</html>
