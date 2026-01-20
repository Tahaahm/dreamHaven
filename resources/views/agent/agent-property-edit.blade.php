@extends('layouts.agent-layout')

@section('title', 'Edit Property - Dream Mulk')

@section('styles')
<style>
    /* --- Luxury Theme Variables (Matches Add Property) --- */
    :root {
        --glass-bg: rgba(255, 255, 255, 0.95);
        --brand-primary: #303b97;
        --brand-secondary: #1e2875;
        --soft-gray: #f1f5f9;
        --text-dark: #0f172a;
        --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        --shadow-lg: 0 10px 25px -5px rgba(48, 59, 151, 0.1);
    }

    body { background-color: #f8fafc; color: var(--text-dark); }

    .luxury-container {
        max-width: 1100px;
        margin: 40px auto;
        padding: 0 20px;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .luxury-header {
        background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
        padding: 40px;
        border-radius: 24px;
        color: white;
        margin-bottom: 40px;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .luxury-header::before {
        content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px;
        background: rgba(255,255,255,0.05); border-radius: 50%;
    }

    .header-content h1 { font-size: 32px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
    .header-content p { opacity: 0.8; font-size: 15px; margin-top: 8px; }

    .header-badge {
        background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 12px;
        backdrop-filter: blur(5px); font-family: monospace; font-size: 14px;
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.4);
        border-radius: 24px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: var(--shadow-sm);
    }

    .section-head {
        display: flex; align-items: center; gap: 15px; margin-bottom: 30px;
        padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;
    }

    .section-head i {
        width: 45px; height: 45px; background: #eef2ff; color: var(--brand-primary);
        display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 20px;
    }

    .section-head h3 { font-size: 20px; font-weight: 700; color: var(--text-dark); margin: 0; }

    /* --- Inputs & Grid --- */
    .input-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
    .input-grid-full { grid-column: 1 / -1; }

    .input-group { margin-bottom: 0; position: relative; }
    .input-label { font-weight: 600; font-size: 14px; color: #475569; margin-bottom: 8px; display: block; }
    .input-label .required { color: #ef4444; margin-left: 3px; }

    .luxury-input, .luxury-select, .luxury-textarea {
        width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 14px;
        font-size: 15px; transition: 0.3s; background: white; color: #334155;
    }

    .luxury-input:focus, .luxury-textarea:focus, .luxury-select:focus {
        border-color: var(--brand-primary); box-shadow: 0 0 0 4px rgba(48, 59, 151, 0.08); outline: none;
    }

    .luxury-input[readonly] { background: #f8fafc; border-color: #cbd5e1; color: #94a3b8; cursor: not-allowed; }

    /* --- Language Tabs --- */
    .language-tabs {
        display: flex; gap: 10px; margin-bottom: 25px; background: #f1f5f9; padding: 6px; border-radius: 16px;
    }
    .language-tab {
        flex: 1; padding: 10px; border: none; background: transparent; border-radius: 12px;
        font-weight: 600; color: #64748b; cursor: pointer; transition: 0.3s;
    }
    .language-tab.active { background: white; color: var(--brand-primary); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .language-content { display: none; animation: fadeIn 0.3s ease; }
    .language-content.active { display: block; }

    /* --- Toggle Switch for Map --- */
    .toggle-wrapper { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; cursor: pointer; }
    .toggle-switch {
        width: 50px; height: 28px; background: #cbd5e1; border-radius: 30px; position: relative; transition: 0.3s;
    }
    .toggle-switch::after {
        content: ''; position: absolute; width: 22px; height: 22px; background: white; border-radius: 50%;
        top: 3px; left: 3px; transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    input:checked + .toggle-switch { background: var(--brand-primary); }
    input:checked + .toggle-switch::after { transform: translateX(22px); }

    .map-section { transition: all 0.3s ease; overflow: hidden; }
    .map-section.hidden { height: 0; opacity: 0; margin: 0; border: none; }

    .map-wrapper {
        border-radius: 20px; overflow: hidden; border: 2px solid #e2e8f0; height: 400px; position: relative;
    }
    #map { width: 100%; height: 100%; }

    /* --- Image Upload & Existing --- */
    .luxury-upload-box {
        border: 2px dashed #cbd5e1; border-radius: 20px; padding: 40px; text-align: center;
        transition: 0.3s; cursor: pointer; background: #f8fafc;
    }
    .luxury-upload-box:hover { border-color: var(--brand-primary); background: #f0f4ff; }
    .upload-icon {
        width: 60px; height: 60px; background: #eef2ff; color: var(--brand-primary);
        border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        font-size: 24px; margin-bottom: 15px;
    }

    .image-section-label {
        font-size: 14px; font-weight: 700; color: #64748b; margin: 25px 0 15px; text-transform: uppercase; letter-spacing: 0.5px;
    }

    .image-preview-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 15px; margin-bottom: 20px;
    }
    .preview-item {
        aspect-ratio: 1; border-radius: 12px; overflow: hidden; position: relative; border: 2px solid #e2e8f0; box-shadow: var(--shadow-sm);
    }
    .preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .remove-btn {
        position: absolute; top: 5px; right: 5px; background: rgba(239,68,68,0.9); color: white;
        width: 26px; height: 26px; border-radius: 50%; border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center; font-size: 12px; transition: 0.2s;
    }
    .remove-btn:hover { transform: scale(1.1); background: #dc2626; }

    /* --- Actions --- */
    .sticky-actions {
        position: sticky; bottom: 30px; background: white; padding: 20px 30px;
        border-radius: 20px; display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 -10px 40px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; z-index: 100; margin-top: 40px;
    }
    .btn-luxury {
        padding: 14px 32px; border-radius: 14px; font-weight: 700; font-size: 15px;
        transition: 0.3s; display: inline-flex; align-items: center; gap: 10px; border: none; cursor: pointer;
        text-decoration: none;
    }
    .btn-save { background: var(--brand-primary); color: white; }
    .btn-save:hover { background: var(--brand-secondary); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(48,59,151,0.3); }
    .btn-cancel { background: var(--soft-gray); color: #64748b; }
    .btn-cancel:hover { background: #e2e8f0; }
    .btn-delete { background: #fee2e2; color: #ef4444; }
    .btn-delete:hover { background: #ef4444; color: white; }

    @media (max-width: 768px) {
        .input-grid { grid-template-columns: 1fr; }
        .luxury-header { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>
@endsection

@section('content')
@php
    // --- Safe Data Extraction ---
    $name = is_array($property->name) ? $property->name : json_decode($property->name, true);
    $description = is_array($property->description) ? $property->description : json_decode($property->description, true);
    $type = is_array($property->type) ? $property->type : json_decode($property->type, true);
    $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
    $rooms = is_array($property->rooms) ? $property->rooms : json_decode($property->rooms, true);
    $address = is_array($property->address_details) ? $property->address_details : json_decode($property->address_details, true);
    $locations = is_array($property->locations) ? $property->locations : json_decode($property->locations, true);
    $images = is_array($property->images) ? $property->images : json_decode($property->images, true);

    // Map Logic: Has map if locations array is not empty
    $hasMap = !empty($locations) && isset($locations[0]['lat']);
    $lat = $hasMap ? $locations[0]['lat'] : 36.1911;
    $lng = $hasMap ? $locations[0]['lng'] : 44.0091;

    // Existing Location Names
    $currentCity = $address['city']['en'] ?? '';
    $currentDistrict = $address['district']['en'] ?? '';
@endphp

<div class="luxury-container">
    <div class="luxury-header">
        <div class="header-content">
            <h1><i class="fas fa-edit"></i> Edit Property</h1>
            <p>Update property details, pricing, and location.</p>
        </div>
        <div class="header-badge">
            ID: {{ $property->id }}
        </div>
    </div>

    @if($errors->any())
    <div style="background: #fee; border: 2px solid #f00; padding: 15px; border-radius: 12px; margin-bottom: 20px;">
        <strong style="color: #c00;">Please fix the following errors:</strong>
        <ul style="margin: 10px 0 0 20px; color: #c00;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('agent.property.update', $property->id) }}" method="POST" enctype="multipart/form-data" id="propertyForm">
        @csrf
        @method('PUT')

        {{-- 1. Basic Info Card --}}
        <div class="glass-card">
            <div class="section-head">
                <i class="fas fa-heading"></i>
                <h3>Basic Information</h3>
            </div>

            <div class="language-tabs">
                <button type="button" class="language-tab active" data-lang="en">English</button>
                <button type="button" class="language-tab" data-lang="ar">العربية</button>
                <button type="button" class="language-tab" data-lang="ku">کوردی</button>
            </div>

            {{-- English --}}
            <div class="language-content active" data-content="en">
                <div class="input-grid">
                    <div class="input-group input-grid-full">
                        <label class="input-label">Property Title (English) <span class="required">*</span></label>
                        <input type="text" name="title_en" class="luxury-input" value="{{ $name['en'] ?? '' }}" required>
                    </div>
                    <div class="input-group input-grid-full">
                        <label class="input-label">Description (English)</label>
                        <textarea name="description_en" class="luxury-textarea" rows="4">{{ $description['en'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Arabic --}}
            <div class="language-content" data-content="ar">
                <div class="input-grid">
                    <div class="input-group input-grid-full">
                        <label class="input-label">عنوان العقار (العربية)</label>
                        <input type="text" name="title_ar" class="luxury-input" value="{{ $name['ar'] ?? '' }}" dir="rtl">
                    </div>
                    <div class="input-group input-grid-full">
                        <label class="input-label">الوصف (العربية)</label>
                        <textarea name="description_ar" class="luxury-textarea" rows="4" dir="rtl">{{ $description['ar'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Kurdish --}}
            <div class="language-content" data-content="ku">
                <div class="input-grid">
                    <div class="input-group input-grid-full">
                        <label class="input-label">ناونیشانی خانووبەرە (کوردی)</label>
                        <input type="text" name="title_ku" class="luxury-input" value="{{ $name['ku'] ?? '' }}">
                    </div>
                    <div class="input-group input-grid-full">
                        <label class="input-label">وەسف (کوردی)</label>
                        <textarea name="description_ku" class="luxury-textarea" rows="4">{{ $description['ku'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Price & Details --}}
        <div class="glass-card">
            <div class="section-head">
                <i class="fas fa-tags"></i>
                <h3>Price & Details</h3>
            </div>
            <div class="input-grid">
                {{-- Manual IQD (Using name="price" to match controller) --}}
                <div class="input-group">
                    <label class="input-label">Price (IQD) <span class="required">*</span></label>
                    <input type="number" name="price" class="luxury-input" value="{{ $price['iqd'] ?? 0 }}" required>
                </div>
                {{-- Manual USD --}}
                <div class="input-group">
                    <label class="input-label">Price (USD) <span class="required">*</span></label>
                    <input type="number" name="price_usd" class="luxury-input" value="{{ $price['usd'] ?? 0 }}" required>
                </div>

                <div class="input-group">
                    <label class="input-label">Property Type <span class="required">*</span></label>
                    <select name="property_type" class="luxury-select" required>
                        <option value="apartment" {{ ($type['category'] ?? '') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                        <option value="villa" {{ ($type['category'] ?? '') == 'villa' ? 'selected' : '' }}>Villa</option>
                        <option value="house" {{ ($type['category'] ?? '') == 'house' ? 'selected' : '' }}>House</option>
                        <option value="land" {{ ($type['category'] ?? '') == 'land' ? 'selected' : '' }}>Land</option>
                        <option value="commercial" {{ ($type['category'] ?? '') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="office" {{ ($type['category'] ?? '') == 'office' ? 'selected' : '' }}>Office</option>
                    </select>
                </div>

                <div class="input-group">
                    <label class="input-label">Status <span class="required">*</span></label>
                    <select name="status" class="luxury-select" required>
                        <option value="available" {{ $property->status == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="sold" {{ $property->status == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="rented" {{ $property->status == 'rented' ? 'selected' : '' }}>Rented</option>
                        <option value="pending" {{ $property->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <div class="input-group">
                    <label class="input-label">Area (m²) <span class="required">*</span></label>
                    <input type="number" name="area" class="luxury-input" value="{{ $property->area }}" placeholder="e.g. 250">
                </div>

                <div class="input-group">
                    <label class="input-label">Listing Type <span class="required">*</span></label>
                    <select name="listing_type" class="luxury-select" required>
                        <option value="sell" {{ $property->listing_type == 'sell' ? 'selected' : '' }}>For Sale</option>
                        <option value="rent" {{ $property->listing_type == 'rent' ? 'selected' : '' }}>For Rent</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- 3. Location Card (With Map Toggle) --}}
        <div class="glass-card">
            <div class="section-head">
                <i class="fas fa-map-marked-alt"></i>
                <h3>Location</h3>
            </div>

            <div class="input-grid" style="margin-bottom: 25px;">
                <div class="input-group">
                    <label class="input-label">City <span class="required">*</span></label>
                    <select id="location-city-select" class="luxury-select" required>
                        <option value="">Loading...</option>
                    </select>
                </div>
                <div class="input-group">
                    <label class="input-label">District/Area <span class="required">*</span></label>
                    <select id="location-area-select" class="luxury-select" required>
                        <option value="">Select City First</option>
                    </select>
                </div>
            </div>

            {{-- Full Address --}}
            <div class="input-group" style="margin-bottom: 25px;">
                <label class="input-label">Full Address <span class="required">*</span></label>
                <input type="text" name="address" class="luxury-input" value="{{ $property->address }}" placeholder="Street, Building, etc." required>
            </div>

            {{-- Map Toggle --}}
            <label class="toggle-wrapper">
                <input type="checkbox" name="has_map" id="mapToggle" value="1" {{ $hasMap ? 'checked' : '' }} hidden>
                <div class="toggle-switch"></div>
                <span style="font-weight: 600; color: var(--brand-primary);">Pin Exact Location on Map</span>
            </label>

            {{-- Map Content --}}
            <div id="mapSection" class="map-section {{ $hasMap ? '' : 'hidden' }}">
                <div class="map-wrapper">
                    <div id="map"></div>
                </div>
                <div class="input-grid" style="margin-top: 20px;">
                    <div class="input-group">
                        <label class="input-label">Latitude</label>
                        <input type="text" name="latitude" id="lat" class="luxury-input" value="{{ $lat }}" readonly>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Longitude</label>
                        <input type="text" name="longitude" id="lng" class="luxury-input" value="{{ $lng }}" readonly>
                    </div>
                </div>
            </div>

            {{-- Hidden location names --}}
            <input type="hidden" name="city_en" id="city_en" value="{{ $currentCity }}">
            <input type="hidden" name="district_en" id="district_en" value="{{ $currentDistrict }}">
            <input type="hidden" name="city_ar" id="city_ar" value="{{ $address['city']['ar'] ?? '' }}">
            <input type="hidden" name="district_ar" id="district_ar" value="{{ $address['district']['ar'] ?? '' }}">
            <input type="hidden" name="city_ku" id="city_ku" value="{{ $address['city']['ku'] ?? '' }}">
            <input type="hidden" name="district_ku" id="district_ku" value="{{ $address['district']['ku'] ?? '' }}">
        </div>

        {{-- 4. Features --}}
        <div class="glass-card">
            <div class="section-head">
                <i class="fas fa-couch"></i>
                <h3>Features</h3>
            </div>
            <div class="input-grid">
                <div class="input-group">
                    <label class="input-label">Bedrooms</label>
                    <input type="number" name="bedrooms" class="luxury-input" value="{{ $rooms['bedroom']['count'] ?? 0 }}">
                </div>
                <div class="input-group">
                    <label class="input-label">Bathrooms</label>
                    <input type="number" name="bathrooms" class="luxury-input" value="{{ $rooms['bathroom']['count'] ?? 0 }}">
                </div>
                <div class="input-group">
                    <label class="input-label">Floors</label>
                    <input type="number" name="floor_number" class="luxury-input" value="{{ $property->floor_number }}">
                </div>
                <div class="input-group">
                    <label class="input-label">Year Built</label>
                    <input type="number" name="year_built" class="luxury-input" value="{{ $property->year_built }}">
                </div>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 20px; flex-wrap: wrap;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="furnished" value="1" {{ $property->furnished ? 'checked' : '' }} style="width: 18px; height: 18px;"> Furnished
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="electricity" value="1" {{ $property->electricity ? 'checked' : '' }} style="width: 18px; height: 18px;"> Electricity 24/7
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="water" value="1" {{ $property->water ? 'checked' : '' }} style="width: 18px; height: 18px;"> Water System
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="internet" value="1" {{ $property->internet ? 'checked' : '' }} style="width: 18px; height: 18px;"> Internet
                </label>
            </div>
        </div>

        {{-- 5. Images --}}
        <div class="glass-card">
            <div class="section-head">
                <i class="fas fa-images"></i>
                <h3>Gallery</h3>
            </div>

            {{-- Existing Images --}}
            @if(count($images) > 0)
                <div class="image-section-label">Current Images</div>
                <div class="image-preview-grid">
                    @foreach($images as $index => $img)
                    <div class="preview-item" id="existing-img-{{ $index }}">
                        <img src="{{ $img }}">
                        <button type="button" class="remove-btn" onclick="removeExistingImage({{ $index }})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
                {{-- Input to track removed images --}}
                <input type="hidden" name="remove_images" id="removeImagesInput">
            @endif

            {{-- Upload New --}}
            <div class="image-section-label">Add New Images</div>
            <div class="luxury-upload-box" onclick="document.getElementById('imageInput').click()">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div style="font-weight: 700;">Click to upload photos</div>
                <div style="color: #64748b; font-size: 13px;">Max 5MB per image. JPG, PNG, WEBP.</div>
                <input type="file" name="images[]" id="imageInput" accept="image/*" multiple hidden>
            </div>
            <div class="image-preview-grid" id="newPreviewGrid"></div>
        </div>

        {{-- Actions --}}
        <div class="sticky-actions">
            <button type="button" class="btn-luxury btn-delete" onclick="if(confirm('Are you sure? This cannot be undone.')) document.getElementById('deleteForm').submit()">
                <i class="fas fa-trash"></i> Delete
            </button>
            <div style="display: flex; gap: 15px;">
                <a href="{{ route('agent.properties') }}" class="btn-luxury btn-cancel">Cancel</a>
                <button type="submit" class="btn-luxury btn-save">
                    <i class="fas fa-check"></i> Update Property
                </button>
            </div>
        </div>
    </form>

    {{-- Delete Form (Hidden) --}}
    <form id="deleteForm" action="{{ route('agent.property.delete', $property->id) }}" method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>
</div>

{{-- Scripts --}}
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>

<script>
    // --- 1. LANGUAGE TABS ---
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

    // --- 2. MAP LOGIC & TOGGLE ---
    let map, marker;
    const mapSection = document.getElementById('mapSection');
    const mapToggle = document.getElementById('mapToggle');
    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');

    function initMap() {
        const initialPos = { lat: parseFloat(latInput.value), lng: parseFloat(lngInput.value) };

        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 14, center: initialPos,
            styles: [ { "featureType": "water", "stylers": [{"color": "#e9e9e9"}] } ]
        });

        marker = new google.maps.Marker({
            position: initialPos, map: map, draggable: true,
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 10, fillColor: "#303b97", fillOpacity: 1, strokeWeight: 3, strokeColor: "#fff" }
        });

        const updateInputs = (latLng) => {
            latInput.value = latLng.lat().toFixed(7);
            lngInput.value = latLng.lng().toFixed(7);
        };

        google.maps.event.addListener(marker, 'dragend', (e) => updateInputs(e.latLng));
        map.addListener('click', (e) => { marker.setPosition(e.latLng); updateInputs(e.latLng); });
    }

    function moveMap(lat, lng) {
        if(map && marker) {
            const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
            map.panTo(pos); marker.setPosition(pos); latInput.value=lat; lngInput.value=lng;
        }
    }

    // Toggle Handler
    mapToggle.addEventListener('change', function() {
        if(this.checked) {
            mapSection.classList.remove('hidden');
            latInput.setAttribute('required', 'required');
            lngInput.setAttribute('required', 'required');
            if(map) { setTimeout(() => { google.maps.event.trigger(map, "resize"); map.setCenter(marker.getPosition()); }, 100); }
        } else {
            mapSection.classList.add('hidden');
            latInput.removeAttribute('required');
            lngInput.removeAttribute('required');
        }
    });

    // --- 3. DYNAMIC LOCATION (With Pre-fill) ---
    document.addEventListener('DOMContentLoaded', async function() {
        const citySelect = document.getElementById('location-city-select');
        const areaSelect = document.getElementById('location-area-select');
        const savedCity = "{{ $currentCity }}";
        const savedDistrict = "{{ $currentDistrict }}";

        // Fetch Cities
        try {
            const res = await fetch("/v1/api/location/branches", { headers: { "Accept": "application/json", "Accept-Language": "en" }});
            const result = await res.json();
            citySelect.innerHTML = '<option value="">Select City</option>';

            if(result.success && result.data) {
                result.data.sort((a, b) => a.city_name_en.localeCompare(b.city_name_en));
                result.data.forEach(city => {
                    const opt = document.createElement('option');
                    opt.value = city.id; opt.textContent = city.city_name_en;
                    // Datasets
                    opt.dataset.lat = city.coordinates?.lat || city.latitude;
                    opt.dataset.lng = city.coordinates?.lng || city.longitude;
                    opt.dataset.nameEn = city.city_name_en;
                    opt.dataset.nameAr = city.city_name_ar;
                    opt.dataset.nameKu = city.city_name_ku;

                    if(city.city_name_en === savedCity) {
                        opt.selected = true;
                        loadAreas(city.id, savedDistrict); // Trigger area load
                    }
                    citySelect.appendChild(opt);
                });
            }
        } catch(e) { console.error(e); }

        // City Change
        citySelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if(this.value) {
                document.getElementById('city_en').value = opt.dataset.nameEn;
                document.getElementById('city_ar').value = opt.dataset.nameAr;
                document.getElementById('city_ku').value = opt.dataset.nameKu;

                // Only move map if enabled
                if(opt.dataset.lat && mapToggle.checked) {
                    moveMap(opt.dataset.lat, opt.dataset.lng);
                }
                loadAreas(this.value);
            }
        });

        // Area Load Function
        async function loadAreas(cityId, preSelected = null) {
            areaSelect.innerHTML = '<option value="">Loading...</option>'; areaSelect.disabled = true;
            try {
                const res = await fetch(`/v1/api/location/branches/${cityId}/areas`, { headers: {"Accept": "application/json", "Accept-Language": "en"} });
                const data = await res.json();
                areaSelect.innerHTML = '<option value="">Select Area</option>'; areaSelect.disabled = false;

                if(data.success && data.data) {
                    data.data.sort((a,b)=>a.area_name_en.localeCompare(b.area_name_en));
                    data.data.forEach(area => {
                        const aOpt = document.createElement('option');
                        aOpt.value = area.id; aOpt.textContent = area.area_name_en;
                        aOpt.dataset.lat = area.coordinates?.lat || area.latitude;
                        aOpt.dataset.lng = area.coordinates?.lng || area.longitude;
                        aOpt.dataset.nameEn = area.area_name_en;
                        aOpt.dataset.nameAr = area.area_name_ar;
                        aOpt.dataset.nameKu = area.area_name_ku;

                        if(preSelected && area.area_name_en === preSelected) aOpt.selected = true;
                        areaSelect.appendChild(aOpt);
                    });
                }
            } catch(e) { console.error(e); }
        }

        // Area Change
        areaSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if(this.value) {
                document.getElementById('district_en').value = opt.dataset.nameEn;
                document.getElementById('district_ar').value = opt.dataset.nameAr;
                document.getElementById('district_ku').value = opt.dataset.nameKu;

                if(opt.dataset.lat && mapToggle.checked) {
                    moveMap(opt.dataset.lat, opt.dataset.lng);
                    map.setZoom(15);
                }
            }
        });
    });

    // --- 4. IMAGE HANDLING ---

    // Remove Existing
    let removedImages = [];
    window.removeExistingImage = function(index) {
        if(confirm('Remove this image?')) {
            removedImages.push(index);
            document.getElementById('removeImagesInput').value = JSON.stringify(removedImages);
            document.getElementById('existing-img-' + index).style.display = 'none';
        }
    };

    // Upload New
    (function(){
        const input = document.getElementById('imageInput');
        const grid = document.getElementById('newPreviewGrid');
        let files = [];

        input.addEventListener('change', function(e) {
            Array.from(e.target.files).forEach(file => {
                if(!file.type.startsWith('image/')) return;
                files.push(file);
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `<img src="${ev.target.result}"><button type="button" class="remove-btn" onclick="removeNewImg(this)"><i class="fas fa-times"></i></button>`;
                    grid.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
            updateInput();
        });

        window.removeNewImg = function(btn) {
            const div = btn.parentElement;
            const idx = Array.from(grid.children).indexOf(div);
            files.splice(idx, 1);
            div.remove();
            updateInput();
        };

        function updateInput() {
            const dt = new DataTransfer();
            files.forEach(f => dt.items.add(f));
            input.files = dt.files;
        }
    })();
</script>
@endsection
