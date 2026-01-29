@extends('layouts.agent-layout')

@section('title', 'Add Property - Dream Mulk')

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

    .section-title i {
        color: #303b97;
        font-size: 22px;
    }

    /* --- Grid System --- */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    .form-grid-full {
        grid-column: 1 / -1;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        .form-container {
            padding: 20px;
        }
    }

    /* --- Form Elements --- */
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

    .language-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* --- Map Section --- */
    .map-wrapper {
        transition: all 0.3s ease;
    }

    .map-wrapper.hidden {
        display: none;
    }

    .map-container {
        width: 100%;
        height: 400px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
        margin-bottom: 20px;
        position: relative;
    }

    #map {
        width: 100%;
        height: 100%;
    }

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

    .sort-instructions {
        background: linear-gradient(135deg, rgba(48,59,151,0.05), rgba(48,59,151,0.02));
        border: 1px dashed #303b97;
        border-radius: 12px;
        padding: 16px;
        margin-top: 20px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #303b97;
        font-weight: 600;
        font-size: 14px;
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
        animation: scaleUp 0.3s ease;
        cursor: move;
        cursor: grab;
        transition: all 0.3s ease;
    }

    .image-preview-item:active {
        cursor: grabbing;
    }

    .image-preview-item.dragging {
        opacity: 0.5;
        transform: scale(0.95);
        border-color: #303b97;
        box-shadow: 0 8px 24px rgba(48,59,151,0.3);
    }

    .image-preview-item.drag-over {
        border-color: #10b981;
        background: rgba(16,185,129,0.05);
        transform: scale(1.05);
    }

    .image-preview-item:first-child::after {
        content: 'COVER';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #303b97, #1e2875);
        color: white;
        padding: 6px;
        font-size: 11px;
        font-weight: 800;
        text-align: center;
        letter-spacing: 1px;
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
        pointer-events: none;
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

    .drag-handle {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 32px;
        height: 32px;
        background: rgba(48,59,151,0.9);
        border: none;
        border-radius: 8px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        z-index: 5;
        cursor: move;
        cursor: grab;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    /* --- Form Actions --- */
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

{{-- Main Form Start --}}
<form action="{{ route('agent.property.store') }}" method="POST" enctype="multipart/form-data" id="propertyForm">
    @csrf

    <div class="form-container">

        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i> Basic Information
            </h3>

            {{-- Language Switcher --}}
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

            {{-- English Fields --}}
            <div class="language-content active" data-content="en">
                <div class="form-group">
                    <label class="form-label">Property Title (English)</label>
                    <input type="text" name="title_en" class="form-input" placeholder="e.g., Luxury Villa in Erbil">
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">Description (English)</label>
                    <textarea name="description_en" class="form-textarea" placeholder="Describe your property in detail..."></textarea>
                </div>
            </div>

            {{-- Arabic Fields --}}
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

            {{-- Kurdish Fields --}}
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

            {{-- Core Details --}}
            <div class="form-grid" style="margin-top: 24px;">
                <div class="form-group">
                    <label class="form-label">Price (IQD)<span class="required">*</span></label>
                    <input type="text" name="price" class="form-input numeric-input" placeholder="e.g., 150000000" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Price (USD)<span class="required">*</span></label>
                    <input type="text" name="price_usd" class="form-input numeric-input" placeholder="e.g., 100000" required>
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
                    <label class="form-label">Area (m²)<span class="required">*</span></label>
                    <input type="text" name="area" class="form-input numeric-input" placeholder="e.g., 250" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Listing Type<span class="required">*</span></label>
                    <select name="listing_type" class="form-select" required>
                        <option value="sell">For Sale</option>
                        <option value="rent">For Rent</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-map-marker-alt"></i> Location Information
            </h3>

            <div class="form-grid" style="margin-bottom: 24px;">
                <div class="form-group">
                    <label class="form-label">Select City <span class="required">*</span></label>
                    <select id="location-city-select" class="form-select" required>
                        <option value="">Loading cities...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Select Area/District <span class="required">*</span></label>
                    <select id="location-area-select" class="form-select" disabled required>
                        <option value="">Select City First</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="display:flex; align-items:center; gap:12px; cursor:pointer; font-size: 16px;">
                    <input type="checkbox" name="has_map" id="has_map_toggle" value="1" checked style="width:20px; height:20px; accent-color: #303b97;">
                    <i class="fas fa-map-marked-alt" style="color:#303b97;"></i> Pin Location on Map
                </label>
            </div>

            <div id="map_content_wrapper" class="map-wrapper">
                <div class="map-instructions">
                    <i class="fas fa-info-circle" style="font-size: 20px;"></i>
                    <span>Select a City and Area above to auto-position the map. You can also drag the pin manually.</span>
                </div>

                <div class="map-container">
                    <div id="map"></div>
                </div>

                <input type="hidden" name="latitude" id="latitude" value="0">
                <input type="hidden" name="longitude" id="longitude" value="0">
            </div>

            {{-- HIDDEN location name fields - auto-filled by JS --}}
            <input type="hidden" name="city_en" id="city_en">
            <input type="hidden" name="district_en" id="district_en">
            <input type="hidden" name="city_ar" id="city_ar">
            <input type="hidden" name="district_ar" id="district_ar">
            <input type="hidden" name="city_ku" id="city_ku">
            <input type="hidden" name="district_ku" id="district_ku">

            <div class="form-grid">
                <div class="form-group form-grid-full">
                    <label class="form-label">Full Address Details <span class="required">*</span></label>
                    <input type="text" name="address" class="form-input" placeholder="Street number, building name, floor number, landmark..." required>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-home"></i> Property Details
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-bed"></i> Bedrooms</label>
                    <input type="text" name="bedrooms" class="form-input numeric-input" placeholder="e.g., 3" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-bath"></i> Bathrooms</label>
                    <input type="text" name="bathrooms" class="form-input numeric-input" placeholder="e.g., 2" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-layer-group"></i> Floors</label>
                    <input type="text" name="floor_number" class="form-input numeric-input" placeholder="e.g., 2">
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar-check"></i> Year Built</label>
                    <input type="text" name="year_built" class="form-input numeric-input" placeholder="e.g., 2020">
                </div>
            </div>

            <div class="form-grid" style="margin-top: 24px;">
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="furnished" value="1" style="width:20px; height:20px;">
                        Furnished
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="electricity" value="1" checked style="width:20px; height:20px;">
                        Electricity 24/7
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="water" value="1" checked style="width:20px; height:20px;">
                        Water System
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="internet" value="1" checked style="width:20px; height:20px;">
                        Internet/Fiber
                    </label>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-images"></i> Property Images
            </h3>

            <div class="image-upload-zone" id="uploadZone">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="upload-text">Click to upload or drag and drop</div>
                <div class="upload-hint">PNG, JPG, WEBP up to 30MB each (Min 1 required)</div>
                <input type="file" name="images[]" id="imageInput" accept="image/*" multiple hidden>
            </div>

            <div class="sort-instructions" id="sortInstructions" style="display: none;">
                <i class="fas fa-arrows-alt" style="font-size: 20px;"></i>
                <span>Drag and drop images to reorder. First image will be the cover photo.</span>
            </div>

            <div class="image-preview-grid" id="imagePreviewGrid">
            </div>
        </div>

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

{{-- Scripts --}}
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>

<script>
// --- GLOBAL VARS ---
let map, marker;

// --- UTILS ---
function normalizeNumber(value) {
    const arabicNumerals = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    const kurdishNumerals = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    const persianNumerals = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    let normalized = value.replace(/,/g, '');
    arabicNumerals.forEach((num, index) => { normalized = normalized.replace(new RegExp(num, 'g'), index.toString()); });
    persianNumerals.forEach((num, index) => { normalized = normalized.replace(new RegExp(num, 'g'), index.toString()); });
    return normalized;
}

// --- LOCATION SELECTOR CLASS ---
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
            const response = await fetch("/v1/api/location/branches", {
                headers: { "Accept-Language": "en" }
            });

            if (!response.ok) {
                let errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
            }

            const result = await response.json();
            if (result.success && result.data && Array.isArray(result.data)) {
                this.cities = result.data;
                this.populateCitySelect();
            } else {
                throw new Error("Invalid response format or no data");
            }
        } catch (error) {
            console.error("Error loading cities:", error);
            const citySelect = document.getElementById(this.citySelectId);
            if (citySelect) {
                citySelect.innerHTML = '<option value="">Error: Server issue</option>';
            }
            this.showError("Unable to load cities. Please try again later.");
        }
    }

    populateCitySelect() {
        const citySelect = document.getElementById(this.citySelectId);
        if (!citySelect) return;

        citySelect.innerHTML = '<option value="">Select City</option>';
        if (this.cities.length === 0) return;

        const sortedCities = [...this.cities].sort((a, b) => a.city_name_en.localeCompare(b.city_name_en));

        sortedCities.forEach((city) => {
            const option = document.createElement("option");
            option.value = city.id;
            option.textContent = `${city.city_name_en}`;

            option.dataset.nameEn = city.city_name_en;
            option.dataset.nameKu = city.city_name_ku;
            option.dataset.nameAr = city.city_name_ar;
            option.dataset.lat = city.coordinates?.lat || city.latitude || '';
            option.dataset.lng = city.coordinates?.lng || city.longitude || '';

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
            } else {
                throw new Error("Invalid response format");
            }
        } catch (error) {
            console.error("Error loading areas:", error);
            const areaSelect = document.getElementById(this.areaSelectId);
            if (areaSelect) areaSelect.innerHTML = '<option value="">Error loading areas</option>';
        } finally {
            const areaSelect = document.getElementById(this.areaSelectId);
            if (areaSelect) areaSelect.disabled = false;
        }
    }

    populateAreaSelect(areas) {
        const areaSelect = document.getElementById(this.areaSelectId);
        if (!areaSelect) return;

        areaSelect.innerHTML = '<option value="">Select Area</option>';
        const sortedAreas = [...areas].sort((a, b) => a.area_name_en.localeCompare(b.area_name_en));

        sortedAreas.forEach((area) => {
            const option = document.createElement("option");
            option.value = area.id;
            option.textContent = `${area.area_name_en}`;

            option.dataset.nameEn = area.area_name_en;
            option.dataset.nameKu = area.area_name_ku;
            option.dataset.nameAr = area.area_name_ar;
            option.dataset.fullLocation = area.full_location;
            option.dataset.lat = area.coordinates?.lat || area.latitude || '';
            option.dataset.lng = area.coordinates?.lng || area.longitude || '';

            if (area.id == this.currentAreaId) option.selected = true;
            areaSelect.appendChild(option);
        });
    }

    setupEventListeners() {
        const citySelect = document.getElementById(this.citySelectId);
        const areaSelect = document.getElementById(this.areaSelectId);
        const cityInput = document.getElementById(this.cityInputId);
        const districtInput = document.getElementById(this.districtInputId);

        if (citySelect) {
            citySelect.addEventListener("change", async (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                if (e.target.value) {
                    if (cityInput) cityInput.value = selectedOption.dataset.nameEn || "";
                    await this.loadAreas(e.target.value);
                    if (districtInput) districtInput.value = "";

                    if (this.onCityChange) {
                        this.onCityChange({
                            id: e.target.value,
                            nameEn: selectedOption.dataset.nameEn,
                            nameKu: selectedOption.dataset.nameKu,
                            nameAr: selectedOption.dataset.nameAr,
                            lat: selectedOption.dataset.lat,
                            lng: selectedOption.dataset.lng
                        });
                    }
                } else {
                    if (cityInput) cityInput.value = "";
                    if (districtInput) districtInput.value = "";
                    if (areaSelect) {
                        areaSelect.innerHTML = '<option value="">Select City First</option>';
                        areaSelect.disabled = true;
                    }
                }
            });
        }

        if (areaSelect) {
            areaSelect.addEventListener("change", (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                if (e.target.value) {
                    if (districtInput) districtInput.value = selectedOption.dataset.nameEn || "";
                    if (this.onAreaChange) {
                        this.onAreaChange({
                            id: e.target.value,
                            nameEn: selectedOption.dataset.nameEn,
                            nameKu: selectedOption.dataset.nameKu,
                            nameAr: selectedOption.dataset.nameAr,
                            lat: selectedOption.dataset.lat,
                            lng: selectedOption.dataset.lng
                        });
                    }
                } else {
                    if (districtInput) districtInput.value = "";
                }
            });
        }
    }

    showError(message) {
        console.error(message);
    }
}

// --- INIT SCRIPT ---
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Numeric Inputs
    const numericInputs = document.querySelectorAll('.numeric-input');
    numericInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = normalizeNumber(this.value);
        });
        input.addEventListener('blur', function() {
            this.value = normalizeNumber(this.value);
        });
    });

    // 2. Initialize Language Tabs
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

    // 3. Initialize Location Selector
    const locationSelector = new LocationSelector({
        citySelectId: 'location-city-select',
        areaSelectId: 'location-area-select',
        cityInputId: 'city_en',
        districtInputId: 'district_en',

        onCityChange: (data) => {
            document.getElementById('city_ar').value = data.nameAr || '';
            document.getElementById('city_ku').value = data.nameKu || '';

            if(data.lat && data.lng && window.map) {
                moveMapTo(data.lat, data.lng);
            }
        },

        onAreaChange: (data) => {
            document.getElementById('district_ar').value = data.nameAr || '';
            document.getElementById('district_ku').value = data.nameKu || '';

            if(data.lat && data.lng && window.map) {
                moveMapTo(data.lat, data.lng);
                window.map.setZoom(15);
            }
        }
    });

    locationSelector.init();

    // 4. Initialize Map Toggle
    const mapToggle = document.getElementById('has_map_toggle');
    if(mapToggle) {
        mapToggle.addEventListener('change', toggleMap);
        toggleMap();
    }

    // 5. Initialize Image Upload with Sortable
    setupImageUpload();

    // 6. Form Validation
    const form = document.getElementById('propertyForm');
    form.addEventListener('submit', validateForm);
});

// --- GOOGLE MAPS FUNCTIONS ---
function initMap() {
    const defaultLoc = { lat: 36.1911, lng: 44.0091 };
    window.map = new google.maps.Map(document.getElementById("map"), {
        zoom: 13,
        center: defaultLoc,
        styles: [
            { "featureType": "water", "elementType": "geometry", "stylers": [{"color": "#e9e9e9"}, {"lightness": 17}] },
            { "featureType": "landscape", "elementType": "geometry", "stylers": [{"color": "#f5f5f5"}, {"lightness": 20}] }
        ]
    });

    window.marker = new google.maps.Marker({
        position: defaultLoc,
        map: window.map,
        draggable: true,
        animation: google.maps.Animation.DROP,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: "#303b97",
            fillOpacity: 1,
            strokeWeight: 3,
            strokeColor: "#ffffff"
        }
    });

    google.maps.event.addListener(window.marker, 'dragend', function(event) {
        updateCoordinates(event.latLng.lat(), event.latLng.lng());
    });

    window.map.addListener('click', function(event) {
        window.marker.setPosition(event.latLng);
        updateCoordinates(event.latLng.lat(), event.latLng.lng());
    });

    updateCoordinates(defaultLoc.lat, defaultLoc.lng);
}

function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
}

function moveMapTo(lat, lng) {
    const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
    if (window.map && window.marker) {
        window.map.panTo(pos);
        window.map.setZoom(14);
        window.marker.setPosition(pos);
        updateCoordinates(lat, lng);
    }
}

function toggleMap() {
    const mapToggle = document.getElementById('has_map_toggle');
    const mapWrapper = document.getElementById('map_content_wrapper');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    if (mapToggle.checked) {
        mapWrapper.classList.remove('hidden');
        if(window.map) {
            setTimeout(() => {
                google.maps.event.trigger(window.map, "resize");
                window.map.setCenter(window.marker.getPosition());
            }, 100);
        }
    } else {
        mapWrapper.classList.add('hidden');
        latInput.value = '0';
        lngInput.value = '0';
    }
}

// --- IMAGE UPLOAD WITH DRAG & DROP SORTING ---
function setupImageUpload() {
    const uploadZone = document.getElementById('uploadZone');
    const imageInput = document.getElementById('imageInput');
    const imagePreviewGrid = document.getElementById('imagePreviewGrid');
    const sortInstructions = document.getElementById('sortInstructions');

    if (!uploadZone || !imageInput) return;

    let selectedFiles = [];
    let draggedItem = null;

    uploadZone.onclick = () => imageInput.click();
    imageInput.onchange = (e) => handleNewFiles(e.target.files);
    uploadZone.ondragover = (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); };
    uploadZone.ondragleave = (e) => { e.preventDefault(); uploadZone.classList.remove('dragover'); };
    uploadZone.ondrop = (e) => { e.preventDefault(); uploadZone.classList.remove('dragover'); handleNewFiles(e.dataTransfer.files); };

    function handleNewFiles(fileList) {
        if (!fileList.length) return;
        for (let i = 0; i < fileList.length; i++) {
            const file = fileList[i];
            if (!file.type.match('image.*')) {
                alert(file.name + ' is not an image');
                continue;
            }

            if (file.size > 30 * 1024 * 1024) {
                alert('⚠️ Error: ' + file.name + ' is too large! Maximum file size is 30MB.');
                continue;
            }
            selectedFiles.push(file);
        }
        renderPreviews();
        syncInputFiles();

        // Show sort instructions if we have images
        if (selectedFiles.length > 0) {
            sortInstructions.style.display = 'flex';
        }
    }

    function renderPreviews() {
        imagePreviewGrid.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-preview-item';
                div.draggable = true;
                div.dataset.index = index;

                div.innerHTML = `
                    <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="image-remove-btn" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                // Drag events
                div.addEventListener('dragstart', handleDragStart);
                div.addEventListener('dragover', handleDragOver);
                div.addEventListener('drop', handleDrop);
                div.addEventListener('dragend', handleDragEnd);
                div.addEventListener('dragenter', handleDragEnter);
                div.addEventListener('dragleave', handleDragLeave);

                // Remove button
                const removeBtn = div.querySelector('.image-remove-btn');
                removeBtn.addEventListener('click', () => removeImage(index));

                imagePreviewGrid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    function handleDragStart(e) {
        draggedItem = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDragEnter(e) {
        if (this !== draggedItem) {
            this.classList.add('drag-over');
        }
    }

    function handleDragLeave(e) {
        this.classList.remove('drag-over');
    }

    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }

        if (draggedItem !== this) {
            const draggedIndex = parseInt(draggedItem.dataset.index);
            const targetIndex = parseInt(this.dataset.index);

            // Swap files in array
            const temp = selectedFiles[draggedIndex];
            selectedFiles[draggedIndex] = selectedFiles[targetIndex];
            selectedFiles[targetIndex] = temp;

            // Re-render
            renderPreviews();
            syncInputFiles();
        }

        this.classList.remove('drag-over');
        return false;
    }

    function handleDragEnd(e) {
        this.classList.remove('dragging');
        document.querySelectorAll('.image-preview-item').forEach(item => {
            item.classList.remove('drag-over');
        });
    }

    function removeImage(index) {
        selectedFiles.splice(index, 1);
        renderPreviews();
        syncInputFiles();

        // Hide instructions if no images
        if (selectedFiles.length === 0) {
            sortInstructions.style.display = 'none';
        }
    }

    function syncInputFiles() {
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        imageInput.files = dt.files;
    }
}

// --- FORM VALIDATION WITH AUTO-FILL ---
function validateForm(e) {
    // Get all title values
    const titleEn = document.querySelector('input[name="title_en"]').value.trim();
    const titleAr = document.querySelector('input[name="title_ar"]').value.trim();
    const titleKu = document.querySelector('input[name="title_ku"]').value.trim();

    // Get all description values
    const descEn = document.querySelector('textarea[name="description_en"]').value.trim();
    const descAr = document.querySelector('textarea[name="description_ar"]').value.trim();
    const descKu = document.querySelector('textarea[name="description_ku"]').value.trim();

    // Check if at least one title exists
    if (!titleEn && !titleAr && !titleKu) {
        e.preventDefault();
        alert('⚠️ Please provide a property title in at least one language.');
        return false;
    }

    // AUTO-FILL TITLES: Priority order (English > Arabic > Kurdish)
    const primaryTitle = titleEn || titleAr || titleKu;

    if (!titleEn) document.querySelector('input[name="title_en"]').value = primaryTitle;
    if (!titleAr) document.querySelector('input[name="title_ar"]').value = primaryTitle;
    if (!titleKu) document.querySelector('input[name="title_ku"]').value = primaryTitle;

    // AUTO-FILL DESCRIPTIONS: Priority order (English > Arabic > Kurdish)
    const primaryDesc = descEn || descAr || descKu;

    if (primaryDesc) {
        if (!descEn) document.querySelector('textarea[name="description_en"]').value = primaryDesc;
        if (!descAr) document.querySelector('textarea[name="description_ar"]').value = primaryDesc;
        if (!descKu) document.querySelector('textarea[name="description_ku"]').value = primaryDesc;
    }

    // ENSURE LAT/LNG ARE SET (0 if map disabled or empty)
    const mapToggle = document.getElementById('has_map_toggle');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    if (!mapToggle.checked || !latInput.value || !lngInput.value) {
        latInput.value = '0';
        lngInput.value = '0';
    }

    // Validate images
    const imageInput = document.getElementById('imageInput');
    if(imageInput.files.length === 0) {
         e.preventDefault();
         alert('⚠️ Please upload at least one image of the property.');
         return false;
    }

    return true;
}
</script>
@endsection
