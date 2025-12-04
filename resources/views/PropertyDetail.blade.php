    <!-- resources/views/portofilio.blade.php-->
    <!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>House Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/portofilio.css') }}">
    <!-- Bootstrap CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrQd9GmMJG8eZ5Aq/eJrNQ8u3lH5Z++sCD4yy3Qbs3a+nKPllXKkBOJ5npES5WgZRv3N8A5IJ9ow3LsTjw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
@include('navbar')


<div class="allin">
    <div class="property-container">
        <!-- Photo Slider -->
        <div class="property-images">
        <div
          id="propertyCarousel"
          class="carousel slide"
          data-bs-ride="carousel"
          data-bs-interval="5000">
          <div class="carousel-inner">
            @foreach($property->images as $index => $photo)
            <div class="carousel-item{{ $index === 0 ? ' active' : '' }}">
              <img src="{{ asset($photo) }}" alt="Property Photo" />
            </div>
            @endforeach
          </div>
          <button
            class="carousel-control-prev"
            type="button"
            data-bs-target="#propertyCarousel"
            data-bs-slide="prev"
          >
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button
            class="carousel-control-next"
            type="button"
            data-bs-target="#propertyCarousel"
            data-bs-slide="next"
          >
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>

        <!-- Thumbnails -->
        <div class="property-thumbnails">
          @foreach($property->images as $index => $photo)
          <div
            class="thumbnail-item"
            data-bs-target="#propertyCarousel"
            data-bs-slide-to="{{ $index }}"
          >
            <img
              src="{{ asset($photo) }}"
              class="img-thumbnail property-thumbnail"
              alt="Thumbnail {{ $index + 1 }}"
            />
          </div>
          @endforeach
        </div>
      </div>
      
      <!-- 🏠 Property Details -->
        <div class="property-details">
            <div class="title-title-container">
                <div class="property-title">{{ $property->name['en'] ?? $property->name ?? 'Untitled Property' }}</div>
                <div class="property-address">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $property->address_details['city']['en'] ?? $property->address ?? 'Unknown Address' }}
                </div>
            </div>

            <!-- 💰 Price -->
            <div class="price-tag">
                ${{ number_format($property->price['usd'] ?? 0) }}
                <span class="text-muted"> / {{ $property->listing_type ?? '' }}</span>
            </div>

            <!-- 📋 Basic Info -->
            <div class="other-property-info">
                <div class="property-detail-item">
                    <span class="light-text">Property Type</span>
                    <span>{{ ucfirst($property->type['category'] ?? $property->property_type ?? 'N/A') }} <i class="fas fa-home"></i></span>
                </div>

                <div class="property-detail-item">
                    <span class="light-text">Listing Type</span>
                    <span>{{ ucfirst($property->listing_type ?? 'N/A') }} <i class="fas fa-calendar-alt"></i></span>
                </div>

                <div class="property-detail-item">
                    <span class="light-text">Bedrooms</span>
                    <span>{{ $property->rooms['bedroom']['count'] ?? 0 }} <i class="fas fa-bed"></i></span>
                </div>

                <div class="property-detail-item">
                    <span class="light-text">Bathrooms</span>
                    <span>{{ $property->rooms['bathroom']['count'] ?? 0 }} <i class="fas fa-bath"></i></span>
                </div>

                <div class="property-detail-item">
                    <span class="light-text">Area</span>
                    <span>{{ $property->area ?? $property->square_footage ?? 'N/A' }} m² <i class="fas fa-ruler-combined"></i></span>
                </div>

                <div class="property-detail-item">
                    <span class="light-text">Floor Number</span>
                    <span>{{ $property->floor_number ?? 'N/A' }} <i class="fas fa-layer-group"></i></span>
                </div>

                <div class="property-detail-item">
                    <span class="light-text">Furnished</span>
                    <span>{{ $property->furnished ? 'Yes' : 'No' }} <i class="fas fa-couch"></i></span>
                </div>

                <div class="property-detail-item">
                    <span class="light-text">Year Built</span>
                    <span>{{ $property->year_built ?? 'N/A' }} <i class="fas fa-calendar-check"></i></span>
                </div>
            </div>

            <!-- 📝 Description -->
            <div class="property-description">
                <h5>Description</h5>
                <p>{{ $property->description['en'] ?? 'No description provided.' }}</p>
            </div>

            <!-- 🧱 Features & Amenities -->
            @if(!empty($property->features))
                <div class="property-features">
                    <h5>Features</h5>
                    <ul>
                        @foreach($property->features as $feature)
                            <li>{{ ucfirst($feature) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($property->amenities))
                <div class="property-amenities">
                    <h5>Amenities</h5>
                    <ul>
                        @foreach($property->amenities as $amenity)
                            <li>{{ ucfirst($amenity) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- ⚡ Utilities -->
            <div class="property-utilities">
                <h5>Utilities</h5>
                <ul>
                    <li>Electricity: {{ $property->electricity ? 'Available' : 'Not Available' }}</li>
                    <li>Water: {{ $property->water ? 'Available' : 'Not Available' }}</li>
                    <li>Internet: {{ $property->internet ? 'Available' : 'Not Available' }}</li>
                </ul>
            </div>
        </div>

         <!-- Agent Info Section -->
<!-- Agent Info Section -->
@php
    $owner = $property->owner;

    // Get owner phone number dynamically
    $phone = $owner->primary_phone ?? $owner->phone_number ?? $owner->phone ?? null;

    // Prepare property name and link
    $propertyName = $property->name['en'] ?? $property->name ?? 'this property';
    $propertyUrl = url()->current();

    // Prepare WhatsApp message
    $message = "I am interested in {$propertyName}. {$propertyUrl}";
    $encodedMessage = urlencode($message);
@endphp

@if($owner && $phone)
<div class="agent-info">
    <div class="agent-name">Listed By</div>

   <img 
    src="{{ $owner->profile_image 
            ? asset('storage/' . ltrim($owner->profile_image, '/')) 
            : asset('property_images/IMG_0697.JPG') }}"
    alt="Agent Photo"
    class="agent-photo"
/>

    <div class="agent-details">
        <div class="agent-name">
            {{ $owner->username ?? $owner->agent_name ?? $owner->company_name ?? 'Property Owner' }}
        </div>

        <div class="company-name">
            {{ $owner->city ?? $owner->role ?? '' }}
        </div>

        <div class="show-agent-properties">
           <a 
    href="{{ 'https://api.whatsapp.com/send?phone=' . preg_replace('/\D/', '', $phone) . '&text=' . $encodedMessage }}" 
    target="_blank"
>
    <button>Contact via WhatsApp</button>
</a>

        </div>
    </div>
</div>
@else
    <p>Agent contact information not available.</p>
@endif






    </div>

   <!-- Property Info -->
<div class="property-info">
    <!-- Container for map and contact form -->
    <div class="contact-us-container">
        <!-- Map Placeholder -->
         
        <div id="map" class="map-placeholder"></div>

        <!-- Contact Us Form -->
        <div class="contact-us-form">
            <h2>Contact Us</h2>
            <form action="/submit-contact" method="post">
                @csrf
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="name">Phone Number:</label>
                    <input type="number" id="phone-number" name="phone-number" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
</div>


    <!-- Report Form -->
    <div class="row">
        <div class="col-lg-12">
            <div class="report-form">
                <form method="post" action="{{ route('report.store') }}">
                    @csrf
                    <div>
                        <label for="report">Report:</label>
                        <textarea id="report" name="report"></textarea>
                    </div>
                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                    <button type="submit">Submit Report</button>
                </form>
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
            </div>
        </div>
    </div>
    </div>
   <!-- JavaScript for carousel and active thumbnail border -->
   <script>
   <script>
function initMap() {
    @if(!empty($property->location) && isset($property->location['lat'], $property->location['lng']))
        var propertyLocation = {
            lat: {{ $property->location['lat'] }},
            lng: {{ $property->location['lng'] }}
        };
    @else
        var propertyLocation = { lat: 0, lng: 0 }; // fallback location
    @endif

    var map = new google.maps.Map(document.getElementById("map"), {
        zoom: 14,
        center: propertyLocation
    });

    new google.maps.Marker({
        position: propertyLocation,
        map: map
    });
}
</script>

</script>




    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap&libraries=&v=weekly"
      async
      defer
    ></script>
  </body>
</html>