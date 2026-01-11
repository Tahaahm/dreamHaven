@extends('layouts.agent-layout')

@section('title', 'Edit Property - Dream Mulk')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #303b97 0%, #1e2875 100%);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 32px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .page-header-content {
        position: relative;
        z-index: 2;
    }

    .page-title {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .page-subtitle {
        font-size: 16px;
        opacity: 0.9;
    }

    .form-container {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .form-section {
        margin-bottom: 40px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 3px solid #303b97;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-title i {
        color: #303b97;
        font-size: 22px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    .form-grid-full {
        grid-column: 1 / -1;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .form-label .required {
        color: #ef4444;
        margin-left: 4px;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s;
        background: white;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #303b97;
        box-shadow: 0 0 0 4px rgba(48,59,151,0.1);
    }

    .form-textarea {
        min-height: 120px;
        resize: vertical;
        font-family: inherit;
    }

    .language-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        background: #f8fafc;
        padding: 8px;
        border-radius: 12px;
    }

    .language-tab {
        flex: 1;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        background: transparent;
        color: #64748b;
        border: none;
        transition: all 0.3s;
    }

    .language-tab.active {
        background: #303b97;
        color: white;
        box-shadow: 0 2px 8px rgba(48,59,151,0.3);
    }

    .language-content {
        display: none;
    }

    .language-content.active {
        display: block;
    }

    .map-container {
        width: 100%;
        height: 400px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
        margin-bottom: 20px;
    }

    #map {
        width: 100%;
        height: 100%;
    }

    .map-instructions {
        background: linear-gradient(135deg, rgba(48,59,151,0.1), rgba(48,59,151,0.05));
        border: 2px dashed #303b97;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #303b97;
        font-weight: 600;
    }

    .existing-images {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .existing-image-item {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        aspect-ratio: 1;
        border: 3px solid #e5e7eb;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .existing-image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-remove-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 36px;
        height: 36px;
        background: #ef4444;
        border: none;
        border-radius: 10px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(239,68,68,0.4);
    }

    .image-remove-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .image-upload-zone {
        border: 3px dashed #cbd5e1;
        border-radius: 16px;
        padding: 50px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s;
        cursor: pointer;
    }

    .image-upload-zone:hover {
        border-color: #303b97;
        background: #f1f5f9;
    }

    .image-upload-zone.dragover {
        border-color: #303b97;
        background: rgba(48,59,151,0.05);
    }

    .image-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 20px;
    }

    .image-preview-item {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        aspect-ratio: 1;
        border: 3px solid #e5e7eb;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .upload-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #303b97, #1e2875);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(48,59,151,0.3);
    }

    .upload-text {
        font-size: 18px;
        color: #1f2937;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .upload-hint {
        font-size: 14px;
        color: #64748b;
    }

    .form-actions {
        display: flex;
        gap: 16px;
        justify-content: flex-end;
        padding-top: 32px;
        border-top: 2px solid #e5e7eb;
        margin-top: 32px;
    }

    .btn {
        padding: 14px 32px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #303b97, #1e2875);
        color: white;
        box-shadow: 0 4px 12px rgba(48,59,151,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(48,59,151,0.4);
    }

    .btn-secondary {
        background: white;
        color: #64748b;
        border: 2px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-container {
            padding: 24px;
        }

        .page-header {
            padding: 24px;
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <i class="fas fa-edit"></i> Edit Property
        </h1>
        <p class="page-subtitle">Update your property details</p>
    </div>
</div>

<form action="{{ route('agent.property.update', $property->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <input type="hidden" name="remove_images" id="removeImagesInput" value="[]">

    <div class="form-container">
        <!-- Basic Information -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i>
                Basic Information
            </h3>

            <div class="language-tabs">
                <button type="button" class="language-tab active" data-lang="en">
                    <i class="fas fa-globe"></i> English
                </button>
                <button type="button" class="language-tab" data-lang="ar">
                    <i class="fas fa-globe"></i> العربية
                </button>
                <button type="button" class="language-tab" data-lang="ku">
                    <i class="fas fa-globe"></i> کوردی
                </button>
            </div>

            <div class="language-content active" data-content="en">
                <div class="form-group">
                    <label class="form-label">Property Title (English)<span class="required">*</span></label>
                    <input type="text" name="title_en" class="form-input" value="{{ $property->name['en'] ?? '' }}" required>
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">Description (English)</label>
                    <textarea name="description_en" class="form-textarea">{{ $property->description['en'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="language-content" data-content="ar">
                <div class="form-group">
                    <label class="form-label">عنوان العقار (العربية)</label>
                    <input type="text" name="title_ar" class="form-input" value="{{ $property->name['ar'] ?? '' }}" dir="rtl">
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">الوصف (العربية)</label>
                    <textarea name="description_ar" class="form-textarea" dir="rtl">{{ $property->description['ar'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="language-content" data-content="ku">
                <div class="form-group">
                    <label class="form-label">ناونیشانی خانووبەرە (کوردی)</label>
                    <input type="text" name="title_ku" class="form-input" value="{{ $property->name['ku'] ?? '' }}">
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">وەسف (کوردی)</label>
                    <textarea name="description_ku" class="form-textarea">{{ $property->description['ku'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="form-grid" style="margin-top: 24px;">
                <div class="form-group">
                    <label class="form-label">Price (IQD)<span class="required">*</span></label>
                    <input type="number" name="price" class="form-input" value="{{ $property->price['iqd'] ?? 0 }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Property Type<span class="required">*</span></label>
                    <select name="property_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="apartment" {{ ($property->type['category'] ?? '') == 'apartment' ? 'selected' : '' }}>🏢 Apartment</option>
                        <option value="villa" {{ ($property->type['category'] ?? '') == 'villa' ? 'selected' : '' }}>🏰 Villa</option>
                        <option value="house" {{ ($property->type['category'] ?? '') == 'house' ? 'selected' : '' }}>🏠 House</option>
                        <option value="land" {{ ($property->type['category'] ?? '') == 'land' ? 'selected' : '' }}>🌍 Land</option>
                        <option value="commercial" {{ ($property->type['category'] ?? '') == 'commercial' ? 'selected' : '' }}>🏪 Commercial</option>
                        <option value="office" {{ ($property->type['category'] ?? '') == 'office' ? 'selected' : '' }}>🏢 Office</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Status<span class="required">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="available" {{ $property->status == 'available' ? 'selected' : '' }}>✅ Available</option>
                        <option value="sold" {{ $property->status == 'sold' ? 'selected' : '' }}>❌ Sold</option>
                        <option value="rented" {{ $property->status == 'rented' ? 'selected' : '' }}>🔑 Rented</option>
                        <option value="pending" {{ $property->status == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Area (m²)</label>
                    <input type="number" name="area" class="form-input" value="{{ $property->area ?? 0 }}" min="0">
                </div>
            </div>
        </div>

        <!-- Location -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-map-marker-alt"></i>
                Location Information
            </h3>

            <div class="map-instructions">
                <i class="fas fa-info-circle" style="font-size: 24px;"></i>
                <span>Click on the map to update the property location. The coordinates will be updated automatically.</span>
            </div>

            <div class="map-container">
                <div id="map"></div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Latitude<span class="required">*</span></label>
                    <input type="number" name="latitude" id="latitude" class="form-input" step="0.0000001" value="{{ $property->locations[0]['lat'] ?? 36.1911 }}" readonly required>
                </div>

                <div class="form-group">
                    <label class="form-label">Longitude<span class="required">*</span></label>
                    <input type="number" name="longitude" id="longitude" class="form-input" step="0.0000001" value="{{ $property->locations[0]['lng'] ?? 44.0091 }}" readonly required>
                </div>

                <div class="form-group">
                    <label class="form-label">City (English)<span class="required">*</span></label>
                    <input type="text" name="city_en" class="form-input" value="{{ $property->address_details['city']['en'] ?? '' }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">District (English)<span class="required">*</span></label>
                    <input type="text" name="district_en" class="form-input" value="{{ $property->address_details['district']['en'] ?? '' }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">City (Arabic)</label>
                    <input type="text" name="city_ar" class="form-input" value="{{ $property->address_details['city']['ar'] ?? '' }}" dir="rtl">
                </div>

                <div class="form-group">
                    <label class="form-label">District (Arabic)</label>
                    <input type="text" name="district_ar" class="form-input" value="{{ $property->address_details['district']['ar'] ?? '' }}" dir="rtl">
                </div>

                <div class="form-group">
                    <label class="form-label">City (Kurdish)</label>
                    <input type="text" name="city_ku" class="form-input" value="{{ $property->address_details['city']['ku'] ?? '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label">District (Kurdish)</label>
                    <input type="text" name="district_ku" class="form-input" value="{{ $property->address_details['district']['ku'] ?? '' }}">
                </div>

                <div class="form-group form-grid-full">
                    <label class="form-label">Full Address</label>
                    <input type="text" name="address" class="form-input" value="{{ $property->address ?? '' }}">
                </div>
            </div>
        </div>

        <!-- Property Details -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-home"></i>
                Property Details
            </h3>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-bed"></i> Bedrooms
                    </label>
                    <input type="number" name="bedrooms" class="form-input" min="0" value="{{ $property->rooms['bedroom']['count'] ?? 0 }}">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-bath"></i> Bathrooms
                    </label>
                    <input type="number" name="bathrooms" class="form-input" min="0" value="{{ $property->rooms['bathroom']['count'] ?? 0 }}">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-layer-group"></i> Floors
                    </label>
                    <input type="number" name="floors" class="form-input" min="0" value="{{ $property->floor_number ?? 0 }}">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-car"></i> Parking Spaces
                    </label>
                    <input type="number" name="parking_spaces" class="form-input" min="0" value="0">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-calendar-check"></i> Year Built
                    </label>
                    <input type="number" name="year_built" class="form-input" min="1900" max="2100" value="{{ $property->year_built ?? '' }}">
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-images"></i>
                Property Images
            </h3>

            @if($property->images && count($property->images) > 0)
                <div class="existing-images" id="existingImages">
                    @foreach($property->images as $index => $image)
                    <div class="existing-image-item" data-index="{{ $index }}">
                        <img src="{{ $image }}" alt="Property Image">
                        <button type="button" class="image-remove-btn" onclick="markImageForRemoval({{ $index }})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
            @endif

            <div class="image-upload-zone" id="uploadZone">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="upload-text">Add more images</div>
                <div class="upload-hint">PNG, JPG, WEBP up to 5MB each</div>
                <input type="file" name="images[]" id="imageInput" accept="image/*" multiple hidden>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('agent.properties') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Property
            </button>
        </div>
    </div>
</form>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>

<script>
let map, marker;
const initialLat = {{ $property->locations[0]['lat'] ?? 36.1911 }};
const initialLng = {{ $property->locations[0]['lng'] ?? 44.0091 }};

function initMap() {
    const initialPosition = { lat: initialLat, lng: initialLng };

    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 15,
        center: initialPosition,
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

    marker = new google.maps.Marker({
        position: initialPosition,
        map: map,
        draggable: true,
        animation: google.maps.Animation.DROP,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 12,
            fillColor: "#303b97",
            fillOpacity: 1,
            strokeWeight: 4,
            strokeColor: "#ffffff"
        }
    });

    google.maps.event.addListener(marker, 'dragend', function(event) {
        updateCoordinates(event.latLng.lat(), event.latLng.lng());
    });

    map.addListener('click', function(event) {
        marker.setPosition(event.latLng);
        updateCoordinates(event.latLng.lat(), event.latLng.lng());
    });
}

function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(7);
    document.getElementById('longitude').value = lng.toFixed(7);
}

document.querySelectorAll('.language-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const lang = this.dataset.lang;
        document.querySelectorAll('.language-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.language-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.querySelector(`[data-content="${lang}"]`).classList.add('active');
    });
});

let imagesToRemove = [];

function markImageForRemoval(index) {
    if (confirm('Are you sure you want to remove this image?')) {
        imagesToRemove.push(index);
        document.querySelector(`.existing-image-item[data-index="${index}"]`).style.opacity = '0.3';
        document.getElementById('removeImagesInput').value = JSON.stringify(imagesToRemove);
    }
}

const uploadZone = document.getElementById('uploadZone');
const imageInput = document.getElementById('imageInput');
let selectedFiles = [];

uploadZone.addEventListener('click', () => imageInput.click());

uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

imageInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    // Create preview container if it doesn't exist
    let previewContainer = document.getElementById('newImagePreviewGrid');
    if (!previewContainer) {
        previewContainer = document.createElement('div');
        previewContainer.id = 'newImagePreviewGrid';
        previewContainer.className = 'image-preview-grid';
        previewContainer.style.marginTop = '24px';
        uploadZone.parentNode.insertBefore(previewContainer, uploadZone.nextSibling);
    }

    Array.from(files).forEach(file => {
        if (file.type.startsWith('image/') && file.size <= 5 * 1024 * 1024) {
            selectedFiles.push(file);
            displayNewImage(file, selectedFiles.length - 1);
        }
    });
    updateFileInput();
}

function displayNewImage(file, index) {
    const reader = new FileReader();
    reader.onload = (e) => {
        const previewContainer = document.getElementById('newImagePreviewGrid');
        const div = document.createElement('div');
        div.className = 'image-preview-item';
        div.setAttribute('data-new-index', index);
        div.innerHTML = `
            <img src="${e.target.result}" alt="New Image Preview">
            <button type="button" class="image-remove-btn" onclick="removeNewImage(${index})">
                <i class="fas fa-times"></i>
            </button>
        `;
        previewContainer.appendChild(div);
    };
    reader.readAsDataURL(file);
}

function removeNewImage(index) {
    selectedFiles.splice(index, 1);
    const previewContainer = document.getElementById('newImagePreviewGrid');
    previewContainer.innerHTML = '';
    selectedFiles.forEach((file, i) => displayNewImage(file, i));
    updateFileInput();
}

function updateFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    imageInput.files = dataTransfer.files;
}
</script>
@endsection
