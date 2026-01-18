@extends('layouts.office-layout')

@section('title', 'Add New Property - Dream Mulk')

@section('styles')
<style>
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --primary-light: #818cf8;
        --success: #10b981;
        --danger: #ef4444;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-500: #6b7280;
        --gray-700: #374151;
        --gray-800: #1f2937;
    }

    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 20px; }
    .container { max-width: 1400px; margin: 0 auto; }

    /* Header */
    .page-header {
        background: white;
        border-radius: 24px;
        padding: 32px 40px;
        margin-bottom: 32px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: slideDown 0.6s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-content h1 {
        font-size: 32px;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 8px;
    }

    .header-content p { color: var(--gray-500); font-size: 15px; }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 24px;
        background: var(--gray-100);
        color: var(--gray-700);
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .back-button:hover { background: var(--primary); color: white; transform: translateX(-5px); }

    /* Form Container */
    .form-container {
        background: white;
        border-radius: 24px;
        padding: 48px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        animation: fadeInUp 0.6s ease 0.2s both;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Progress Steps */
    .progress-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 48px;
        position: relative;
    }

    .progress-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--gray-200);
        z-index: 0;
    }

    .progress-line {
        position: absolute;
        top: 20px;
        left: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
        transition: width 0.4s ease;
        z-index: 1;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
        flex: 1;
    }

    .step-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: white;
        border: 3px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #9ca3af;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .step.active .step-circle {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        border-color: var(--primary);
        color: white;
        box-shadow: 0 8px 20px rgba(99,102,241,0.3);
        transform: scale(1.1);
    }

    .step.completed .step-circle {
        background: var(--success);
        border-color: var(--success);
        color: white;
    }

    .step-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-500);
        text-align: center;
    }

    .step.active .step-label { color: var(--primary); }

    /* Form Sections */
    .form-section {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .form-section.active { display: block; }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .section-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--gray-800);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-title i { color: var(--primary); font-size: 28px; }
    .section-subtitle { color: var(--gray-500); font-size: 14px; margin-top: -16px; margin-bottom: 32px; }

    /* Form Elements */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: 1 / -1; }

    .form-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .required { color: var(--danger); }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        font-size: 15px;
        color: var(--gray-800);
        background: var(--gray-50);
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
    }

    .form-textarea { min-height: 120px; resize: vertical; }
    .input-hint { font-size: 12px; color: var(--gray-500); margin-top: 6px; }

    /* Language Tabs */
    .language-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        background: var(--gray-100);
        padding: 6px;
        border-radius: 12px;
        width: fit-content;
    }

    .lang-tab {
        padding: 10px 20px;
        border: none;
        background: transparent;
        color: #6b7280;
        font-weight: 600;
        font-size: 14px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .lang-tab.active {
        background: white;
        color: var(--primary);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .lang-content { display: none; }
    .lang-content.active { display: block; animation: fadeIn 0.3s ease; }

    /* Image Upload */
    .image-upload-area {
        border: 3px dashed var(--gray-200);
        border-radius: 20px;
        padding: 60px 40px;
        text-align: center;
        background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .image-upload-area:hover {
        border-color: var(--primary);
        background: white;
    }

    .upload-icon {
        font-size: 64px;
        color: var(--primary);
        margin-bottom: 20px;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .upload-text {
        font-size: 18px;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 8px;
    }

    .upload-hint { color: var(--gray-500); font-size: 14px; }

    /* Image Preview */
    .image-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
        margin-top: 24px;
    }

    .image-preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        animation: scaleIn 0.3s ease;
    }

    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }

    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .remove-image {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(239,68,68,0.95);
        border: none;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        opacity: 0;
    }

    .image-preview-item:hover .remove-image { opacity: 1; }
    .remove-image:hover { background: var(--danger); transform: scale(1.1); }

    /* Features */
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .feature-checkbox {
        position: relative;
        cursor: pointer;
    }

    .feature-checkbox input {
        position: absolute;
        opacity: 0;
    }

    .feature-box {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        background: var(--gray-50);
        transition: all 0.3s ease;
    }

    .feature-checkbox input:checked + .feature-box {
        border-color: var(--primary);
        background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(99,102,241,0.05));
    }

    .feature-box:hover {
        border-color: #818cf8;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99,102,241,0.2);
    }

    .feature-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .feature-label {
        font-weight: 600;
        color: var(--gray-700);
        font-size: 15px;
    }

    /* Buttons */
    .form-navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 48px;
        padding-top: 32px;
        border-top: 2px solid var(--gray-100);
    }

    .btn {
        padding: 14px 32px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-secondary {
        background: var(--gray-100);
        color: var(--gray-700);
    }

    .btn-secondary:hover {
        background: var(--gray-200);
        transform: translateX(-5px);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        box-shadow: 0 4px 16px rgba(99,102,241,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(99,102,241,0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success), #34d399);
        color: white;
        box-shadow: 0 4px 16px rgba(16,185,129,0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(16,185,129,0.4);
    }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .page-header { flex-direction: column; gap: 20px; text-align: center; }
        .form-container { padding: 32px 24px; }
        .feature-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="page-header">
        <div class="header-content">
            <h1>Add New Property</h1>
            <p>Fill in the details to list your property</p>
        </div>
        <a href="{{ route('office.properties') }}" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="form-container">
        <div class="progress-steps">
            <div class="progress-line" id="progressLine" style="width: 0%"></div>
            <div class="step active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label">Basic</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label">Details</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label">Location</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-label">Features</div>
            </div>
            <div class="step" data-step="5">
                <div class="step-circle">5</div>
                <div class="step-label">Images</div>
            </div>
        </div>

        <form action="{{ route('office.property.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="form-section active" data-section="1">
                <h2 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h2>
                <p class="section-subtitle">Enter the primary details</p>

                <div class="language-tabs">
                    <button type="button" class="lang-tab active" onclick="switchLang(event, 'en')">English</button>
                    <button type="button" class="lang-tab" onclick="switchLang(event, 'ar')">العربية</button>
                    <button type="button" class="lang-tab" onclick="switchLang(event, 'ku')">کوردی</button>
                </div>

                <div class="lang-content active" id="lang-en">
                    <div class="form-group">
                        <label class="form-label">Name (EN) <span class="required">*</span></label>
                        <input type="text" name="name_en" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (EN) <span class="required">*</span></label>
                        <textarea name="description_en" class="form-textarea" required></textarea>
                    </div>
                </div>

                <div class="lang-content" id="lang-ar">
                    <div class="form-group">
                        <label class="form-label">الاسم (AR)</label>
                        <input type="text" name="name_ar" class="form-input" dir="rtl">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الوصف (AR)</label>
                        <textarea name="description_ar" class="form-textarea" dir="rtl"></textarea>
                    </div>
                </div>

                <div class="lang-content" id="lang-ku">
                    <div class="form-group">
                        <label class="form-label">ناو (KU)</label>
                        <input type="text" name="name_ku" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">وەسف (KU)</label>
                        <textarea name="description_ku" class="form-textarea"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section" data-section="2">
                <h2 class="section-title"><i class="fas fa-home"></i> Property Details</h2>
                <p class="section-subtitle">Specify type, size, and pricing</p>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Type <span class="required">*</span></label>
                        <select name="property_type" class="form-select" required>
                            <option value="">Select...</option>
                            <option value="apartment">Apartment</option>
                            <option value="house">House</option>
                            <option value="villa">Villa</option>
                            <option value="land">Land</option>
                            <option value="commercial">Commercial</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Listing <span class="required">*</span></label>
                        <select name="listing_type" class="form-select" required>
                            <option value="sell">For Sale</option>
                            <option value="rent">For Rent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Area (m²) <span class="required">*</span></label>
                        <input type="number" name="area" class="form-input" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="sold">Sold</option>
                            <option value="rented">Rented</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bedrooms <span class="required">*</span></label>
                        <input type="number" name="bedrooms" class="form-input" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bathrooms <span class="required">*</span></label>
                        <input type="number" name="bathrooms" class="form-input" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Floor</label>
                        <input type="number" name="floor_number" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Year Built</label>
                        <input type="number" name="year_built" class="form-input" min="1900" max="2030">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (USD) <span class="required">*</span></label>
                        <input type="number" name="price_usd" class="form-input" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (IQD) <span class="required">*</span></label>
                        <input type="number" name="price_iqd" class="form-input" step="0.01" required>
                    </div>
                </div>
            </div>

            <div class="form-section" data-section="3">
                <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Location</h2>
                <p class="section-subtitle">Where is the property located</p>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">City <span class="required">*</span></label>
                        <select id="city-select" class="form-select" required>
                            <option value="">Loading cities...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Area/District <span class="required">*</span></label>
                        <select id="area-select" class="form-select" disabled required>
                            <option value="">Select City First</option>
                        </select>
                    </div>

                    <input type="hidden" id="city-en" name="city_en">
                    <input type="hidden" id="city-ar" name="city_ar">
                    <input type="hidden" id="city-ku" name="city_ku">
                    <input type="hidden" id="district-en" name="district_en">
                    <input type="hidden" id="district-ar" name="district_ar">
                    <input type="hidden" id="district-ku" name="district_ku">

                    <div class="form-group full-width">
                        <label class="form-label">Full Address</label>
                        <input type="text" name="address" class="form-input" placeholder="Street, building number, etc.">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Latitude <span class="required">*</span></label>
                        <input type="number" id="latitude" name="latitude" class="form-input" step="0.000001" required>
                        <span class="input-hint">Drag pin, click map, or type manually</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Longitude <span class="required">*</span></label>
                        <input type="number" id="longitude" name="longitude" class="form-input" step="0.000001" required>
                        <span class="input-hint">Drag pin, click map, or type manually</span>
                    </div>

                    <div class="form-group full-width">
                        <div id="map-preview" style="height: 350px; width: 100%; border-radius: 12px; border: 2px solid var(--gray-200); margin-top: 10px;"></div>
                    </div>
                </div>
            </div>

            <div class="form-section" data-section="4">
                <h2 class="section-title"><i class="fas fa-star"></i> Features</h2>
                <p class="section-subtitle">Select available amenities</p>

                <div class="feature-grid">
                    <label class="feature-checkbox">
                        <input type="checkbox" name="furnished" value="1">
                        <div class="feature-box">
                            <div class="feature-icon"><i class="fas fa-couch"></i></div>
                            <span class="feature-label">Furnished</span>
                        </div>
                    </label>
                    <label class="feature-checkbox">
                        <input type="checkbox" name="electricity" value="1">
                        <div class="feature-box">
                            <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                            <span class="feature-label">Electricity</span>
                        </div>
                    </label>
                    <label class="feature-checkbox">
                        <input type="checkbox" name="water" value="1">
                        <div class="feature-box">
                            <div class="feature-icon"><i class="fas fa-tint"></i></div>
                            <span class="feature-label">Water</span>
                        </div>
                    </label>
                    <label class="feature-checkbox">
                        <input type="checkbox" name="internet" value="1">
                        <div class="feature-box">
                            <div class="feature-icon"><i class="fas fa-wifi"></i></div>
                            <span class="feature-label">Internet</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-section" data-section="5">
                <h2 class="section-title"><i class="fas fa-images"></i> Images</h2>
                <p class="section-subtitle">Upload property photos</p>

                <div class="image-upload-area" onclick="document.getElementById('imageInput').click()">
                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="upload-text">Click to upload images</div>
                    <div class="upload-hint">JPG, PNG (Max: 5MB each)</div>
                    <input type="file" id="imageInput" name="images[]" multiple accept="image/*" style="display:none" onchange="previewImages(event)">
                </div>

                <div id="imagePreview" class="image-preview-grid"></div>
            </div>

            <div class="form-navigation">
                <button type="button" class="btn btn-secondary" onclick="prevStep()" id="prevBtn" style="display:none">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                <div></div>
                <button type="button" class="btn btn-primary" onclick="nextStep()" id="nextBtn">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn btn-success" id="submitBtn" style="display:none">
                    <i class="fas fa-check"></i> Create Property
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>
<script>
class LocationSelector {
    constructor(options = {}) {
        this.citySelectId = options.citySelectId || "city-select";
        this.areaSelectId = options.areaSelectId || "area-select";
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
            console.log("Fetching cities from API...");

            const response = await fetch("/v1/api/location/branches", {
                headers: {
                    "Accept-Language": "en",
                    "Accept": "application/json"
                },
            });

            if (!response.ok) {
                let errorText = await response.text();
                console.error("Response status:", response.status);
                console.error("Response body:", errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log("API Response:", result);

            if (result.success && result.data && Array.isArray(result.data)) {
                this.cities = result.data;
                console.log(`Loaded ${this.cities.length} cities`);
                this.populateCitySelect();
            } else {
                throw new Error("Invalid response format or no data");
            }
        } catch (error) {
            console.error("Error loading cities:", error);
            const citySelect = document.getElementById(this.citySelectId);
            if (citySelect) {
                citySelect.innerHTML = '<option value="">Error loading cities</option>';
            }
            this.showError("Unable to load cities. Please try again later.");
            throw error;
        }
    }

    populateCitySelect() {
        const citySelect = document.getElementById(this.citySelectId);
        if (!citySelect) {
            console.error("City select element not found:", this.citySelectId);
            return;
        }

        citySelect.innerHTML = '<option value="">Select City</option>';

        if (this.cities.length === 0) {
            console.warn("No cities to populate");
            return;
        }

        const sortedCities = [...this.cities].sort((a, b) =>
            a.city_name_en.localeCompare(b.city_name_en)
        );

        sortedCities.forEach((city) => {
            const option = document.createElement("option");
            option.value = city.id;
            option.textContent = `${city.city_name_en} - ${city.city_name_ku} - ${city.city_name_ar}`;
            option.dataset.nameEn = city.city_name_en;
            option.dataset.nameKu = city.city_name_ku;
            option.dataset.nameAr = city.city_name_ar;
            option.dataset.lat = city.coordinates?.lat || city.latitude;
            option.dataset.lng = city.coordinates?.lng || city.longitude;

            if (city.id == this.currentCityId) {
                option.selected = true;
            }

            citySelect.appendChild(option);
        });

        console.log("City select populated successfully");
    }

    async loadAreas(cityId) {
        try {
            const areaSelect = document.getElementById(this.areaSelectId);
            if (!areaSelect) return;

            areaSelect.innerHTML = '<option value="">Loading areas...</option>';
            areaSelect.disabled = true;

            const response = await fetch(`/v1/api/location/branches/${cityId}/areas`, {
                headers: {
                    "Accept-Language": "en",
                    "Accept": "application/json"
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success && result.data) {
                this.populateAreaSelect(result.data);
            } else {
                throw new Error("Invalid response format");
            }
        } catch (error) {
            console.error("Error loading areas:", error);
            const areaSelect = document.getElementById(this.areaSelectId);
            if (areaSelect) {
                areaSelect.innerHTML = '<option value="">Error loading areas</option>';
            }
        } finally {
            const areaSelect = document.getElementById(this.areaSelectId);
            if (areaSelect) {
                areaSelect.disabled = false;
            }
        }
    }

    populateAreaSelect(areas) {
        const areaSelect = document.getElementById(this.areaSelectId);
        if (!areaSelect) return;

        areaSelect.innerHTML = '<option value="">Select Area</option>';

        const sortedAreas = [...areas].sort((a, b) =>
            a.area_name_en.localeCompare(b.area_name_en)
        );

        sortedAreas.forEach((area) => {
            const option = document.createElement("option");
            option.value = area.id;
            option.textContent = `${area.area_name_en} - ${area.area_name_ku} - ${area.area_name_ar}`;
            option.dataset.nameEn = area.area_name_en;
            option.dataset.nameKu = area.area_name_ku;
            option.dataset.nameAr = area.area_name_ar;
            option.dataset.lat = area.coordinates?.lat || area.latitude;
            option.dataset.lng = area.coordinates?.lng || area.longitude;
            option.dataset.fullLocation = area.full_location;

            if (area.id == this.currentAreaId) {
                option.selected = true;
            }

            areaSelect.appendChild(option);
        });
    }

    showError(message) {
        console.error(message);
        const alertDiv = document.createElement("div");
        alertDiv.style.cssText = "position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 16px 24px; background: #fee2e2; border: 2px solid #ef4444; color: #dc2626; border-radius: 12px; max-width: 400px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); animation: slideIn 0.3s ease;";
        alertDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-exclamation-circle" style="font-size: 20px;"></i>
                <div style="font-weight: 600;">${message}</div>
            </div>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }
}

// Main form logic
let currentStep = 1;
const totalSteps = 5;
let selectedFiles = [];
let locationSelector;
let map = null;
let marker = null;

// Initialize Google Map
function initMap(lat, lng) {
    const mapContainer = document.getElementById('map-preview');
    if (!mapContainer) return;

    // Fix for NaN error: Check if lat/lng are valid numbers
    let latVal = parseFloat(lat);
    let lngVal = parseFloat(lng);

    // Default to Erbil if values are missing or invalid
    if (isNaN(latVal) || isNaN(lngVal)) {
        latVal = 36.1901;
        lngVal = 44.0091;
    }

    const position = { lat: latVal, lng: lngVal };

    if (!map) {
        map = new google.maps.Map(mapContainer, {
            center: position,
            zoom: 14,
            mapTypeControl: true,
            streetViewControl: false,
            fullscreenControl: true
        });

        marker = new google.maps.Marker({
            position: position,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP
        });

        // Event: Update coordinates when marker is dragged
        marker.addListener('dragend', function() {
            const pos = marker.getPosition();
            document.getElementById('latitude').value = pos.lat();
            document.getElementById('longitude').value = pos.lng();
        });

        // Event: Click on map to move marker
        map.addListener('click', function(e) {
            const pos = e.latLng;
            marker.setPosition(pos);
            document.getElementById('latitude').value = pos.lat();
            document.getElementById('longitude').value = pos.lng();
            map.panTo(pos);
        });

        // Event: Update marker when manual inputs change
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        const updateMarkerFromInputs = () => {
            const newLat = parseFloat(latInput.value);
            const newLng = parseFloat(lngInput.value);
            if (!isNaN(newLat) && !isNaN(newLng)) {
                const newPos = { lat: newLat, lng: newLng };
                marker.setPosition(newPos);
                map.panTo(newPos);
            }
        };

        latInput.addEventListener('change', updateMarkerFromInputs);
        lngInput.addEventListener('change', updateMarkerFromInputs);

    } else {
        map.setCenter(position);
        marker.setPosition(position);
    }
}

// Initialize location selector when page loads
document.addEventListener('DOMContentLoaded', async function() {
    console.log('Initializing location selector...');

    locationSelector = new LocationSelector({
        citySelectId: 'city-select',
        areaSelectId: 'area-select',
        onCityChange: function(city) {
            console.log('City changed:', city);

            // Update all language inputs for city
            document.getElementById('city-en').value = city.nameEn || '';
            document.getElementById('city-ar').value = city.nameAr || '';
            document.getElementById('city-ku').value = city.nameKu || '';

            // Clear district inputs when city changes
            document.getElementById('district-en').value = '';
            document.getElementById('district-ar').value = '';
            document.getElementById('district-ku').value = '';

            // Update coordinates from city if available
            const citySelect = document.getElementById('city-select');
            const selectedOption = citySelect.options[citySelect.selectedIndex];
            if (selectedOption.dataset.lat && selectedOption.dataset.lng) {
                const lat = selectedOption.dataset.lat;
                const lng = selectedOption.dataset.lng;
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                // Initialize or update map
                if (typeof google !== 'undefined') {
                    initMap(lat, lng);
                }
            }
        },
        onAreaChange: function(area) {
            console.log('Area changed:', area);

            // Update all language inputs for district/area
            document.getElementById('district-en').value = area.nameEn || '';
            document.getElementById('district-ar').value = area.nameAr || '';
            document.getElementById('district-ku').value = area.nameKu || '';

            // Update coordinates from area
            const areaSelect = document.getElementById('area-select');
            const selectedOption = areaSelect.options[areaSelect.selectedIndex];
            if (selectedOption.dataset.lat && selectedOption.dataset.lng) {
                const lat = selectedOption.dataset.lat;
                const lng = selectedOption.dataset.lng;
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                // Update map
                if (typeof google !== 'undefined') {
                    initMap(lat, lng);
                }
            }
        }
    });

    // Initialize the location selector
    try {
        await locationSelector.init();
        console.log('Location selector initialized successfully');
    } catch (error) {
        console.error('Failed to initialize location selector:', error);
    }

    // Setup city select change listener
    const citySelect = document.getElementById('city-select');
    if (citySelect) {
        citySelect.addEventListener('change', async function(e) {
            const selectedOption = e.target.options[e.target.selectedIndex];

            if (e.target.value) {
                // Call the onCityChange callback
                if (locationSelector.onCityChange) {
                    locationSelector.onCityChange({
                        id: e.target.value,
                        nameEn: selectedOption.dataset.nameEn,
                        nameKu: selectedOption.dataset.nameKu,
                        nameAr: selectedOption.dataset.nameAr
                    });
                }

                // Load areas for selected city
                await locationSelector.loadAreas(e.target.value);
            } else {
                // Clear everything
                document.getElementById('city-en').value = '';
                document.getElementById('city-ar').value = '';
                document.getElementById('city-ku').value = '';
                document.getElementById('district-en').value = '';
                document.getElementById('district-ar').value = '';
                document.getElementById('district-ku').value = '';
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';

                // Clear map if exists
                if(marker) marker.setMap(null);

                const areaSelect = document.getElementById('area-select');
                if (areaSelect) {
                    areaSelect.innerHTML = '<option value="">Select City First</option>';
                    areaSelect.disabled = true;
                }
            }
        });
    }

    // Setup area select change listener
    const areaSelect = document.getElementById('area-select');
    if (areaSelect) {
        areaSelect.addEventListener('change', function(e) {
            const selectedOption = e.target.options[e.target.selectedIndex];

            if (e.target.value && locationSelector.onAreaChange) {
                locationSelector.onAreaChange({
                    id: e.target.value,
                    nameEn: selectedOption.dataset.nameEn,
                    nameKu: selectedOption.dataset.nameKu,
                    nameAr: selectedOption.dataset.nameAr,
                    fullLocation: selectedOption.dataset.fullLocation
                });
            }
        });
    }
});

function switchLang(e, lang) {
    e.preventDefault();
    document.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.lang-content').forEach(c => c.classList.remove('active'));
    e.target.classList.add('active');
    document.getElementById('lang-' + lang).classList.add('active');
}

function updateProgress() {
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
    document.getElementById('progressLine').style.width = progress + '%';

    document.querySelectorAll('.step').forEach((step, idx) => {
        step.classList.remove('active', 'completed');
        if (idx + 1 < currentStep) step.classList.add('completed');
        if (idx + 1 === currentStep) step.classList.add('active');
    });

    document.querySelectorAll('.form-section').forEach((section, idx) => {
        section.classList.remove('active');
        if (idx + 1 === currentStep) section.classList.add('active');
    });

    document.getElementById('prevBtn').style.display = currentStep > 1 ? 'flex' : 'none';
    document.getElementById('nextBtn').style.display = currentStep < totalSteps ? 'flex' : 'none';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'flex' : 'none';
}

function nextStep() {
    if (currentStep < totalSteps) {
        // Validate current step before proceeding
        const currentSection = document.querySelector(`.form-section[data-section="${currentStep}"]`);
        const inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
        let valid = true;

        inputs.forEach(input => {
            if (!input.value) {
                input.style.borderColor = '#ef4444';
                valid = false;
            } else {
                input.style.borderColor = '#e5e7eb';
            }
        });

        // Special check for Step 3 (Location)
        if (currentStep === 3) {
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            if (!lat || !lng) {
                document.getElementById('latitude').style.borderColor = '#ef4444';
                document.getElementById('longitude').style.borderColor = '#ef4444';
                alert("Please select a location on the map.");
                valid = false;
            }
        }

        // Only return if invalid.
        if (!valid) return;

        currentStep++;
        updateProgress();

        // Trigger map resize when reaching step 3
        if (currentStep === 3 && map) {
            setTimeout(() => {
                google.maps.event.trigger(map, 'resize');
                if(marker) map.setCenter(marker.getPosition());
            }, 100);
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateProgress();
    }
}

function previewImages(e) {
    const files = Array.from(e.target.files);
    selectedFiles = [...selectedFiles, ...files];

    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    selectedFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-image" onclick="removeImage(${idx})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeImage(idx) {
    selectedFiles.splice(idx, 1);
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    document.getElementById('imageInput').files = dt.files;

    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    selectedFiles.forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-image" onclick="removeImage(${i})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

updateProgress();
</script>
@endsection
