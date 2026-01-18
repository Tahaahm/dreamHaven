@extends('layouts.agent-layout')

@section('title', 'Edit Property - Dream Mulk')

@section('styles')
<style>
    /* --- Page Layout & Header --- */
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
        display: flex;
        justify-content: space-between;
        align-items: center;
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

    /* --- Form Containers --- */
    .form-container {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .form-section {
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 1px solid #f1f5f9;
    }

    .form-section:last-child {
        border-bottom: none;
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

    .section-title i { color: #303b97; font-size: 22px; }

    /* --- Grid System --- */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    .form-grid-full { grid-column: 1 / -1; }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-container { padding: 20px; }
        .page-header-content { flex-direction: column; align-items: flex-start; gap: 16px; }
    }

    /* --- Form Elements --- */
    .form-group { margin-bottom: 0; }

    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .form-label .required { color: #ef4444; margin-left: 4px; }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s;
        background: white;
        color: #1f2937;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #303b97;
        box-shadow: 0 0 0 4px rgba(48,59,151,0.1);
    }

    .form-input[readonly] {
        background-color: #f8fafc;
        color: #64748b;
        cursor: not-allowed;
        border-color: #e2e8f0;
    }

    .form-textarea {
        min-height: 120px;
        resize: vertical;
        font-family: inherit;
    }

    /* --- Language Tabs --- */
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
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .language-tab.active {
        background: #303b97;
        color: white;
        box-shadow: 0 2px 8px rgba(48,59,151,0.3);
    }

    .language-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .language-content.active { display: block; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* --- Map Section --- */
    .map-container {
        width: 100%;
        height: 400px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
        margin-bottom: 20px;
        position: relative;
    }

    #map { width: 100%; height: 100%; }

    .map-instructions {
        background: linear-gradient(135deg, rgba(48,59,151,0.05), rgba(48,59,151,0.02));
        border: 1px dashed #303b97;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #303b97;
        font-weight: 600;
        font-size: 14px;
    }

    /* --- Image Upload --- */
    .image-upload-zone {
        border: 3px dashed #cbd5e1;
        border-radius: 16px;
        padding: 50px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s;
        cursor: pointer;
        position: relative;
    }

    .image-upload-zone:hover {
        border-color: #303b97;
        background: #f1f5f9;
    }

    .image-upload-zone.dragover {
        border-color: #303b97;
        background: rgba(48,59,151,0.05);
        transform: scale(1.01);
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

    /* Image Grid & Badges */
    .image-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #64748b;
        margin: 24px 0 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .image-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .image-preview-item {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        aspect-ratio: 1;
        border: 3px solid #e5e7eb;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        background: #f3f4f6;
        animation: scaleUp 0.3s ease;
    }

    @keyframes scaleUp {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
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
        width: 32px;
        height: 32px;
        background: #ef4444;
        border: none;
        border-radius: 8px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(239,68,68,0.4);
        z-index: 10;
    }

    .image-remove-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .new-badge {
        position: absolute;
        bottom: 10px;
        left: 10px;
        background: #10b981;
        color: white;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 700;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* --- Form Actions --- */
    .form-actions {
        display: flex;
        gap: 16px;
        justify-content: space-between;
        padding-top: 32px;
        border-top: 2px solid #e5e7eb;
        margin-top: 32px;
    }

    .action-right { display: flex; gap: 16px; }

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

    .btn-danger {
        background: #fee2e2;
        color: #ef4444;
        border: 1px solid #fecaca;
    }

    .btn-danger:hover {
        background: #ef4444;
        color: white;
    }
</style>
@endsection

@section('content')
@php
    // Safe Data Extraction
    $name = is_array($property->name) ? $property->name : json_decode($property->name, true);
    $description = is_array($property->description) ? $property->description : json_decode($property->description, true);
    $type = is_array($property->type) ? $property->type : json_decode($property->type, true);
    $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
    $rooms = is_array($property->rooms) ? $property->rooms : json_decode($property->rooms, true);
    $address = is_array($property->address_details) ? $property->address_details : json_decode($property->address_details, true);
    $locations = is_array($property->locations) ? $property->locations : json_decode($property->locations, true);
    $images = is_array($property->images) ? $property->images : json_decode($property->images, true);

    // Default coordinates if missing
    $lat = $locations[0]['lat'] ?? 36.1911;
    $lng = $locations[0]['lng'] ?? 44.0091;

    // Existing Location Names
    $currentCity = $address['city']['en'] ?? '';
    $currentDistrict = $address['district']['en'] ?? '';
@endphp

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">
                <i class="fas fa-edit"></i> Edit Property
            </h1>
            <p class="page-subtitle">Update property details, price, or location</p>
        </div>
        <div style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 12px; backdrop-filter: blur(5px);">
            <i class="fas fa-hashtag"></i> ID: {{ $property->id }}
        </div>
    </div>
</div>

<form action="{{ route('agent.property.update', $property->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-container">
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h3>

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
                    <label class="form-label">Property Title (English)</label>
                    <input type="text" name="name_en" class="form-input" value="{{ $name['en'] ?? '' }}" required>
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">Description (English)</label>
                    <textarea name="description_en" class="form-textarea" required>{{ $description['en'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="language-content" data-content="ar">
                <div class="form-group">
                    <label class="form-label">عنوان العقار (العربية)</label>
                    <input type="text" name="name_ar" class="form-input" value="{{ $name['ar'] ?? '' }}" dir="rtl">
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">الوصف (العربية)</label>
                    <textarea name="description_ar" class="form-textarea" dir="rtl">{{ $description['ar'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="language-content" data-content="ku">
                <div class="form-group">
                    <label class="form-label">ناونیشانی خانووبەرە (کوردی)</label>
                    <input type="text" name="name_ku" class="form-input" value="{{ $name['ku'] ?? '' }}">
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">وەسف (کوردی)</label>
                    <textarea name="description_ku" class="form-textarea">{{ $description['ku'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="form-grid" style="margin-top: 24px;">
                <div class="form-group">
                    <label class="form-label">Price (IQD)</label>
                    <input type="number" name="price_iqd" class="form-input" value="{{ $price['iqd'] ?? 0 }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Price (USD)</label>
                    <input type="number" name="price_usd" class="form-input" value="{{ $price['usd'] ?? 0 }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Property Type</label>
                    <select name="property_type" class="form-select" required>
                        <option value="apartment" {{ ($type['category'] ?? '') == 'apartment' ? 'selected' : '' }}>🏢 Apartment</option>
                        <option value="villa" {{ ($type['category'] ?? '') == 'villa' ? 'selected' : '' }}>🏰 Villa</option>
                        <option value="house" {{ ($type['category'] ?? '') == 'house' ? 'selected' : '' }}>🏠 House</option>
                        <option value="land" {{ ($type['category'] ?? '') == 'land' ? 'selected' : '' }}>🌍 Land</option>
                        <option value="commercial" {{ ($type['category'] ?? '') == 'commercial' ? 'selected' : '' }}>🏪 Commercial</option>
                        <option value="office" {{ ($type['category'] ?? '') == 'office' ? 'selected' : '' }}>🏢 Office</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="available" {{ $property->status == 'available' ? 'selected' : '' }}>✅ Available</option>
                        <option value="sold" {{ $property->status == 'sold' ? 'selected' : '' }}>❌ Sold</option>
                        <option value="rented" {{ $property->status == 'rented' ? 'selected' : '' }}>🔑 Rented</option>
                        <option value="pending" {{ $property->status == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Area (m²)</label>
                    <input type="number" name="area" class="form-input" value="{{ $property->area }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Listing Type</label>
                    <select name="listing_type" class="form-select" required>
                        <option value="sell" {{ $property->listing_type == 'sell' ? 'selected' : '' }}>For Sale</option>
                        <option value="rent" {{ $property->listing_type == 'rent' ? 'selected' : '' }}>For Rent</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Location</h3>

            <div class="form-grid" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label">City <span style="color:red">*</span></label>
                    <select id="location-city-select" class="form-select" required>
                        <option value="">Loading...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">District / Area <span style="color:red">*</span></label>
                    <select id="location-area-select" class="form-select" required>
                        <option value="">Select City First</option>
                    </select>
                </div>
            </div>

            <div class="map-container">
                <div id="map" style="width:100%; height:100%;"></div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Latitude <span style="color:red">*</span></label>
                    {{-- FIX: Added step="any" to allow map precision --}}
                    <input type="number" name="latitude" id="latitude" class="form-input" value="{{ $lat }}" step="any" readonly required>
                </div>
                <div class="form-group">
                    <label class="form-label">Longitude <span style="color:red">*</span></label>
                    {{-- FIX: Added step="any" to allow map precision --}}
                    <input type="number" name="longitude" id="longitude" class="form-input" value="{{ $lng }}" step="any" readonly required>
                </div>

                <div class="form-group">
                    <label class="form-label">Selected City (EN)</label>
                    <input type="text" name="city_en" id="city_en" class="form-input" value="{{ $currentCity }}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Selected District (EN)</label>
                    <input type="text" name="district_en" id="district_en" class="form-input" value="{{ $currentDistrict }}" readonly>
                </div>

                <input type="hidden" name="city_ar" id="city_ar" value="{{ $address['city']['ar'] ?? '' }}">
                <input type="hidden" name="city_ku" id="city_ku" value="{{ $address['city']['ku'] ?? '' }}">
                <input type="hidden" name="district_ar" id="district_ar" value="{{ $address['district']['ar'] ?? '' }}">
                <input type="hidden" name="district_ku" id="district_ku" value="{{ $address['district']['ku'] ?? '' }}">

                <div class="form-group form-grid-full">
                    <label class="form-label">Full Address Details</label>
                    <input type="text" name="address" class="form-input" value="{{ $property->address }}" placeholder="Street number, building name...">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-home"></i> Property Details</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-bed"></i> Bedrooms</label>
                    <input type="number" name="bedrooms" class="form-input" value="{{ $rooms['bedroom']['count'] ?? 0 }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-bath"></i> Bathrooms</label>
                    <input type="number" name="bathrooms" class="form-input" value="{{ $rooms['bathroom']['count'] ?? 0 }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-layer-group"></i> Floors</label>
                    <input type="number" name="floor_number" class="form-input" value="{{ $property->floor_number }}" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-car"></i> Parking Spaces</label>
                    <input type="number" name="parking_spaces" class="form-input" value="{{ $property->parking_spaces ?? 0 }}" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar-check"></i> Year Built</label>
                    <input type="number" name="year_built" class="form-input" value="{{ $property->year_built }}" min="1900" max="2100">
                </div>
            </div>

            {{-- Amenities --}}
            <div class="form-grid" style="margin-top: 24px;">
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="furnished" value="1" {{ $property->furnished ? 'checked' : '' }} style="width:20px; height:20px;">
                        Furnished
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="electricity" value="1" {{ $property->electricity ? 'checked' : '' }} style="width:20px; height:20px;">
                        Electricity 24/7
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="water" value="1" {{ $property->water ? 'checked' : '' }} style="width:20px; height:20px;">
                        Water System
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="internet" value="1" {{ $property->internet ? 'checked' : '' }} style="width:20px; height:20px;">
                        Internet/Fiber
                    </label>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-images"></i> Property Images</h3>

            @if(count($images) > 0)
                <div class="image-section-title">Current Images</div>
                <div class="image-preview-grid" id="existingImages">
                    @foreach($images as $index => $img)
                    <div class="image-preview-item" id="existing-img-{{ $index }}">
                        <img src="{{ $img }}">
                        <button type="button" class="image-remove-btn" onclick="removeExistingImage({{ $index }})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
            @endif

            <input type="hidden" name="remove_images" id="removeImagesInput">

            <div class="image-section-title">Add New Images</div>
            <div class="image-upload-zone" id="uploadZone">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="upload-text">Click to upload or drag and drop</div>
                <div class="upload-hint">PNG, JPG, WEBP up to 5MB each</div>
                <input type="file" name="images[]" id="imageInput" accept="image/*" multiple hidden>
            </div>

            <div class="image-preview-grid" id="imagePreviewGrid"></div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-danger" onclick="if(confirm('Delete this property?')) document.getElementById('deleteForm').submit()">
                <i class="fas fa-trash"></i> Delete
            </button>
            <div class="action-right">
                <a href="{{ route('agent.properties') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Property</button>
            </div>
        </div>
    </div>
</form>

<form id="deleteForm" action="{{ route('agent.property.delete', $property->id) }}" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

{{-- SCRIPTS --}}
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>

<script>
let map, marker;
let removedImages = [];

// --- 1. GOOGLE MAPS ---
function initMap() {
    const initialPos = { lat: parseFloat({{ $lat }}), lng: parseFloat({{ $lng }}) };

    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 14,
        center: initialPos
    });

    marker = new google.maps.Marker({
        position: initialPos,
        map: map,
        draggable: true
    });

    // Update inputs on drag
    marker.addListener('dragend', function(event) {
        document.getElementById('latitude').value = event.latLng.lat();
        document.getElementById('longitude').value = event.latLng.lng();
    });

    // Update inputs on click
    map.addListener('click', function(event) {
        marker.setPosition(event.latLng);
        document.getElementById('latitude').value = event.latLng.lat();
        document.getElementById('longitude').value = event.latLng.lng();
    });
}

function moveMap(lat, lng) {
    const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
    if(map && marker) {
        map.panTo(pos);
        marker.setPosition(pos);
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    }
}

// --- 2. DYNAMIC LOCATION (Pre-filling Existing Data) ---
document.addEventListener('DOMContentLoaded', async function() {
    const citySelect = document.getElementById('location-city-select');
    const areaSelect = document.getElementById('location-area-select');

    const savedCity = "{{ $currentCity }}";
    const savedArea = "{{ $currentDistrict }}";

    // 1. Fetch Cities
    try {
        const response = await fetch("/v1/api/location/branches", {
            headers: { "Accept": "application/json", "Accept-Language": "en" }
        });
        const result = await response.json();

        citySelect.innerHTML = '<option value="">Select City</option>';

        if (result.success && result.data) {
            result.data.forEach(city => {
                const opt = document.createElement('option');
                opt.value = city.id;
                opt.textContent = city.city_name_en;

                // Store Data
                opt.dataset.lat = city.coordinates?.lat || city.latitude;
                opt.dataset.lng = city.coordinates?.lng || city.longitude;
                opt.dataset.nameEn = city.city_name_en;
                opt.dataset.nameAr = city.city_name_ar;
                opt.dataset.nameKu = city.city_name_ku;

                // Pre-select saved city
                if (city.city_name_en === savedCity) {
                    opt.selected = true;
                    // Trigger area load immediately
                    loadAreas(city.id, savedArea);
                }

                citySelect.appendChild(opt);
            });
        }
    } catch (err) {
        console.error("Error loading cities:", err);
    }

    // 2. City Change Listener
    citySelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (this.value) {
            // Update Hidden Inputs
            document.getElementById('city_en').value = selected.dataset.nameEn;
            document.getElementById('city_ar').value = selected.dataset.nameAr;
            document.getElementById('city_ku').value = selected.dataset.nameKu;

            // Move Map
            if(selected.dataset.lat) moveMap(selected.dataset.lat, selected.dataset.lng);

            // Load Areas
            loadAreas(this.value);
        }
    });

    // 3. Load Areas Function
    async function loadAreas(cityId, preSelectedArea = null) {
        areaSelect.innerHTML = '<option value="">Loading...</option>';
        areaSelect.disabled = true;

        try {
            const res = await fetch(`/v1/api/location/branches/${cityId}/areas`, {
                headers: { "Accept": "application/json", "Accept-Language": "en" }
            });
            const data = await res.json();

            areaSelect.innerHTML = '<option value="">Select Area</option>';
            areaSelect.disabled = false;

            if (data.success && data.data) {
                data.data.forEach(area => {
                    const opt = document.createElement('option');
                    opt.value = area.id;
                    opt.textContent = area.area_name_en;

                    // Store Data
                    opt.dataset.lat = area.coordinates?.lat || area.latitude;
                    opt.dataset.lng = area.coordinates?.lng || area.longitude;
                    opt.dataset.nameEn = area.area_name_en;
                    opt.dataset.nameAr = area.area_name_ar;
                    opt.dataset.nameKu = area.area_name_ku;

                    // Pre-select saved area
                    if (preSelectedArea && area.area_name_en === preSelectedArea) {
                        opt.selected = true;
                    }

                    areaSelect.appendChild(opt);
                });
            }
        } catch (err) {
            console.error("Error loading areas:", err);
            areaSelect.innerHTML = '<option value="">Error</option>';
        }
    }

    // 4. Area Change Listener
    areaSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (this.value) {
            document.getElementById('district_en').value = selected.dataset.nameEn;
            document.getElementById('district_ar').value = selected.dataset.nameAr;
            document.getElementById('district_ku').value = selected.dataset.nameKu;

            if(selected.dataset.lat) {
                moveMap(selected.dataset.lat, selected.dataset.lng);
                map.setZoom(15);
            }
        }
    });
});

// --- 3. EXISTING IMAGE REMOVAL ---
function removeExistingImage(index) {
    if(confirm('Remove this image?')) {
        removedImages.push(index);
        document.getElementById('removeImagesInput').value = JSON.stringify(removedImages);
        document.getElementById('existing-img-' + index).style.display = 'none';
    }
}

// --- 4. LANGUAGE TABS (Same as Add) ---
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.language-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.dataset.lang;
            document.querySelectorAll('.language-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.language-content').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.querySelector(`[data-content="${lang}"]`).classList.add('active');
        });
    });
});

// --- 5. NEW IMAGE UPLOAD (Same as Add) ---
(function() {
    'use strict';
    const uploadZone = document.getElementById('uploadZone');
    const imageInput = document.getElementById('imageInput');
    const imagePreviewGrid = document.getElementById('imagePreviewGrid');

    if (!uploadZone || !imageInput) return;

    let selectedFiles = [];

    uploadZone.onclick = () => imageInput.click();
    imageInput.onchange = (e) => handleNewFiles(e.target.files);
    uploadZone.ondragover = (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); };
    uploadZone.ondragleave = (e) => { e.preventDefault(); uploadZone.classList.remove('dragover'); };
    uploadZone.ondrop = (e) => { e.preventDefault(); uploadZone.classList.remove('dragover'); handleNewFiles(e.dataTransfer.files); };

    function handleNewFiles(fileList) {
        if (!fileList.length) return;
        for (let i = 0; i < fileList.length; i++) {
            const file = fileList[i];
            if (!file.type.match('image.*') || file.size > 5 * 1024 * 1024) continue;
            selectedFiles.push(file);
            showPreview(file, selectedFiles.length - 1);
        }
        syncInputFiles();
    }

    function showPreview(file, index) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <div class="new-badge">NEW</div>
                <button type="button" class="image-remove-btn" onclick="removeNewPreview(${index}, this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            imagePreviewGrid.appendChild(div);
        };
        reader.readAsDataURL(file);
    }

    window.removeNewPreview = function(index, btn) {
        selectedFiles.splice(index, 1);
        btn.parentElement.remove();
        syncInputFiles();
    };

    function syncInputFiles() {
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        imageInput.files = dt.files;
    }
})();
</script>
@endsection
