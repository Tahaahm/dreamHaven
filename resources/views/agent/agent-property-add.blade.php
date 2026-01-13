@extends('layouts.agent-layout')

@section('title', 'Add Property - Dream Mulk')

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

    .image-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 20px;
        margin-top: 24px;
    }

    .image-preview-item {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        aspect-ratio: 1;
        border: 3px solid #e5e7eb;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        background: #f3f4f6;
    }

    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
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
        z-index: 10;
    }

    .image-remove-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
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

        .image-preview-grid {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        }
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i> Add New Property
        </h1>
        <p class="page-subtitle">Fill in the details below to list your property on Dream Mulk</p>
    </div>
</div>

<form action="{{ route('agent.property.store') }}" method="POST" enctype="multipart/form-data" id="propertyForm">
    @csrf

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
                    <input type="text" name="title_en" class="form-input" placeholder="e.g., Luxury Villa in Erbil" required>
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">Description (English)</label>
                    <textarea name="description_en" class="form-textarea" placeholder="Describe your property in detail..."></textarea>
                </div>
            </div>

            <div class="language-content" data-content="ar">
                <div class="form-group">
                    <label class="form-label">عنوان العقار (العربية)</label>
                    <input type="text" name="title_ar" class="form-input" placeholder="مثال: فيلا فاخرة في أربيل" dir="rtl">
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">الوصف (العربية)</label>
                    <textarea name="description_ar" class="form-textarea" placeholder="صف الممتلكات الخاصة بك بالتفصيل..." dir="rtl"></textarea>
                </div>
            </div>

            <div class="language-content" data-content="ku">
                <div class="form-group">
                    <label class="form-label">ناونیشانی خانووبەرە (کوردی)</label>
                    <input type="text" name="title_ku" class="form-input" placeholder="نموونە: ڤیلای گەورە لە هەولێر">
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">وەسف (کوردی)</label>
                    <textarea name="description_ku" class="form-textarea" placeholder="خانووبەرەکەت بە وردی باس بکە..."></textarea>
                </div>
            </div>

            <div class="form-grid" style="margin-top: 24px;">
                <div class="form-group">
                    <label class="form-label">Price (IQD)<span class="required">*</span></label>
                    <input type="number" name="price" class="form-input" placeholder="e.g., 150000000" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Price (USD)</label>
                    <input type="number" name="price_usd" class="form-input" placeholder="e.g., 100000" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Property Type<span class="required">*</span></label>
                    <select name="property_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="apartment">🏢 Apartment</option>
                        <option value="villa">🏰 Villa</option>
                        <option value="house">🏠 House</option>
                        <option value="land">🌍 Land</option>
                        <option value="commercial">🏪 Commercial</option>
                        <option value="office">🏢 Office</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Status<span class="required">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="available">✅ Available</option>
                        <option value="sold">❌ Sold</option>
                        <option value="rented">🔑 Rented</option>
                        <option value="pending">⏳ Pending</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Area (m²)</label>
                    <input type="number" name="area" class="form-input" placeholder="e.g., 250" min="0">
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
                <span>Click on the map to select the exact location of your property. The coordinates will be filled automatically.</span>
            </div>

            <div class="map-container">
                <div id="map"></div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Latitude<span class="required">*</span></label>
                    <input type="number" name="latitude" id="latitude" class="form-input" step="0.0000001" placeholder="36.1911" readonly required>
                </div>

                <div class="form-group">
                    <label class="form-label">Longitude<span class="required">*</span></label>
                    <input type="number" name="longitude" id="longitude" class="form-input" step="0.0000001" placeholder="44.0091" readonly required>
                </div>

                <div class="form-group">
                    <label class="form-label">City (English)<span class="required">*</span></label>
                    <input type="text" name="city_en" class="form-input" placeholder="e.g., Erbil" required>
                </div>

                <div class="form-group">
                    <label class="form-label">District (English)<span class="required">*</span></label>
                    <input type="text" name="district_en" class="form-input" placeholder="e.g., Dream City" required>
                </div>

                <div class="form-group">
                    <label class="form-label">City (Arabic)</label>
                    <input type="text" name="city_ar" class="form-input" placeholder="أربيل" dir="rtl">
                </div>

                <div class="form-group">
                    <label class="form-label">District (Arabic)</label>
                    <input type="text" name="district_ar" class="form-input" placeholder="مدينة الأحلام" dir="rtl">
                </div>

                <div class="form-group">
                    <label class="form-label">City (Kurdish)</label>
                    <input type="text" name="city_ku" class="form-input" placeholder="هەولێر">
                </div>

                <div class="form-group">
                    <label class="form-label">District (Kurdish)</label>
                    <input type="text" name="district_ku" class="form-input" placeholder="شاری خەون">
                </div>

                <div class="form-group form-grid-full">
                    <label class="form-label">Full Address</label>
                    <input type="text" name="address" class="form-input" placeholder="Enter complete street address">
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
                    <input type="number" name="bedrooms" class="form-input" min="0" placeholder="e.g., 3">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-bath"></i> Bathrooms
                    </label>
                    <input type="number" name="bathrooms" class="form-input" min="0" placeholder="e.g., 2">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-layer-group"></i> Floors
                    </label>
                    <input type="number" name="floors" class="form-input" min="0" placeholder="e.g., 2">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-car"></i> Parking Spaces
                    </label>
                    <input type="number" name="parking_spaces" class="form-input" min="0" placeholder="e.g., 1">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-calendar-check"></i> Year Built
                    </label>
                    <input type="number" name="year_built" class="form-input" min="1900" max="2100" placeholder="e.g., 2020">
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-images"></i>
                Property Images
            </h3>

            <div class="image-upload-zone" id="uploadZone">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="upload-text">Click to upload or drag and drop</div>
                <div class="upload-hint">PNG, JPG, WEBP up to 5MB each (Multiple files supported)</div>
                <input type="file" name="images[]" id="imageInput" accept="image/*" multiple hidden>
            </div>

            <div class="image-preview-grid" id="imagePreviewGrid"></div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('agent.properties') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> Add Property
            </button>
        </div>
    </div>
</form>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>

<script>
let map, marker;

// Initialize Google Map
function initMap() {
    const erbil = { lat: 36.1911, lng: 44.0091 };

    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 12,
        center: erbil,
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
        position: erbil,
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

    // Update coordinates on marker drag
    google.maps.event.addListener(marker, 'dragend', function(event) {
        updateCoordinates(event.latLng.lat(), event.latLng.lng());
    });

    // Update coordinates on map click
    map.addListener('click', function(event) {
        marker.setPosition(event.latLng);
        updateCoordinates(event.latLng.lat(), event.latLng.lng());
    });

    // Set initial coordinates
    updateCoordinates(erbil.lat, erbil.lng);
}

function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(7);
    document.getElementById('longitude').value = lng.toFixed(7);
}

// Language tabs functionality
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.language-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.dataset.lang;

            // Remove active class from all tabs and contents
            document.querySelectorAll('.language-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.language-content').forEach(c => c.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.querySelector(`[data-content="${lang}"]`).classList.add('active');
        });
    });
});

// Image upload functionality - SIMPLIFIED AND BULLETPROOF
(function() {
    'use strict';

    console.log('Image upload script loaded'); // Debug

    const uploadZone = document.getElementById('uploadZone');
    const imageInput = document.getElementById('imageInput');
    const imagePreviewGrid = document.getElementById('imagePreviewGrid');

    if (!uploadZone || !imageInput || !imagePreviewGrid) {
        console.error('Required elements not found!');
        return;
    }

    let selectedFiles = [];

    // Simple click handler
    uploadZone.onclick = function(e) {
        console.log('Upload zone clicked');
        imageInput.click();
    };

    // File input change - THE MAIN HANDLER
    imageInput.onchange = function(e) {
        console.log('File input changed, files:', this.files.length);
        handleNewFiles(this.files);
    };

    // Drag and drop
    uploadZone.ondragover = function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    };

    uploadZone.ondragleave = function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    };

    uploadZone.ondrop = function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        console.log('Files dropped:', e.dataTransfer.files.length);
        handleNewFiles(e.dataTransfer.files);
    };

    function handleNewFiles(fileList) {
        if (!fileList || fileList.length === 0) {
            console.log('No files provided');
            return;
        }

        console.log('Processing', fileList.length, 'files');

        for (let i = 0; i < fileList.length; i++) {
            const file = fileList[i];
            console.log('File:', file.name, file.type, file.size);

            // Validate
            if (!file.type.match('image.*')) {
                alert(file.name + ' is not an image');
                continue;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert(file.name + ' is too large (max 5MB)');
                continue;
            }

            // Add to array
            selectedFiles.push(file);

            // Show preview
            showPreview(file, selectedFiles.length - 1);
        }

        // Update the input
        syncInputFiles();
    }

    function showPreview(file, index) {
        console.log('Creating preview for:', file.name);

        const reader = new FileReader();

        reader.onload = function(e) {
            console.log('Preview loaded for:', file.name);

            const wrapper = document.createElement('div');
            wrapper.className = 'image-preview-item';
            wrapper.setAttribute('data-index', index);

            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Preview';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'image-remove-btn';
            btn.innerHTML = '<i class="fas fa-times"></i>';
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Remove clicked for index:', index);
                removePreview(index);
            };

            wrapper.appendChild(img);
            wrapper.appendChild(btn);
            imagePreviewGrid.appendChild(wrapper);

            console.log('Preview added to DOM');
        };

        reader.onerror = function(err) {
            console.error('FileReader error:', err);
        };

        reader.readAsDataURL(file);
    }

    function removePreview(index) {
        console.log('Removing file at index:', index);

        // Remove from array
        selectedFiles.splice(index, 1);

        // Clear and rebuild
        imagePreviewGrid.innerHTML = '';
        selectedFiles.forEach((file, i) => showPreview(file, i));

        // Update input
        syncInputFiles();
    }

    function syncInputFiles() {
        try {
            const dt = new DataTransfer();
            selectedFiles.forEach(f => dt.items.add(f));
            imageInput.files = dt.files;
            console.log('Input synced, total files:', imageInput.files.length);
        } catch (err) {
            console.error('Failed to sync files:', err);
        }
    }

    console.log('Image upload handlers attached successfully');
})();
</script>
@endsection
