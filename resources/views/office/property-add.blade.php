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
        --warning: #f59e0b;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-500: #6b7280;
        --gray-700: #374151;
        --gray-800: #1f2937;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 40px 20px;
    }

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

    .back-button:hover {
        background: var(--primary);
        color: white;
        transform: translateX(-5px);
    }

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

    /* Alert Messages */
    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        animation: slideDown 0.4s ease;
    }

    .alert-danger {
        background: #fee2e2;
        border: 2px solid #ef4444;
        color: #991b1b;
    }

    .alert-success {
        background: #d1fae5;
        border: 2px solid #10b981;
        color: #065f46;
    }

    .alert i { font-size: 24px; }

    /* Section Headers */
    .section-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--gray-800);
        margin: 40px 0 24px 0;
        padding-bottom: 12px;
        border-bottom: 3px solid var(--primary);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-title:first-child { margin-top: 0; }
    .section-title i { color: var(--primary); font-size: 28px; }
    .section-subtitle {
        color: var(--gray-500);
        font-size: 14px;
        margin-top: -16px;
        margin-bottom: 32px;
    }

    /* Form Elements */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        position: relative;
    }

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

    .required {
        color: var(--danger);
        font-size: 16px;
    }

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

    .form-input.error, .form-select.error, .form-textarea.error {
        border-color: var(--danger);
        background: #fef2f2;
    }

    .form-input.valid, .form-select.valid, .form-textarea.valid {
        border-color: var(--success);
    }

    .form-textarea { min-height: 120px; resize: vertical; }
    .input-hint { font-size: 12px; color: var(--gray-500); margin-top: 6px; }

    /* Validation Messages */
    .error-msg {
        font-size: 13px;
        color: var(--danger);
        margin-top: 6px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        animation: fadeIn 0.3s ease;
    }

    .error-msg i { font-size: 14px; }

    .success-msg {
        font-size: 13px;
        color: var(--success);
        margin-top: 6px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

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

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

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

    .image-upload-area.error {
        border-color: var(--danger);
        background: #fef2f2;
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

    /* Submit Button */
    .submit-section {
        margin-top: 48px;
        padding-top: 32px;
        border-top: 2px solid var(--gray-100);
        display: flex;
        justify-content: flex-end;
        gap: 16px;
    }

    .btn {
        padding: 16px 48px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 12px;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success), #34d399);
        color: white;
        box-shadow: 0 4px 16px rgba(16,185,129,0.3);
    }

    .btn-success:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(16,185,129,0.4);
    }

    .btn-secondary {
        background: var(--gray-200);
        color: var(--gray-700);
    }

    .btn-secondary:hover {
        background: var(--gray-300);
    }

    /* Loading Spinner */
    .spinner {
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top: 3px solid white;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
        display: none;
    }

    .btn:disabled .spinner {
        display: block;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Validation Summary */
    .validation-summary {
        background: #fef2f2;
        border: 2px solid var(--danger);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        display: none;
    }

    .validation-summary.show {
        display: block;
        animation: slideDown 0.4s ease;
    }

    .validation-summary h4 {
        color: var(--danger);
        font-size: 18px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .validation-summary ul {
        margin: 0;
        padding-left: 20px;
    }

    .validation-summary li {
        color: #991b1b;
        margin-bottom: 6px;
        font-weight: 600;
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
            <p>Fill in all required details to list your property</p>
        </div>
        <a href="{{ route('office.properties') }}" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Properties
        </a>
    </div>

    <div class="form-container">
        <!-- Validation Summary -->
        <div class="validation-summary" id="validationSummary">
            <h4><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h4>
            <ul id="errorList"></ul>
        </div>

        <!-- Server-side Errors -->
        @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Error!</strong> Please check the form for errors.
                <ul style="margin: 8px 0 0 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('office.property.store') }}" method="POST" enctype="multipart/form-data" id="propertyForm" novalidate>
            @csrf

            <!-- SECTION 1: Basic Information -->
            <h2 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h2>
            <p class="section-subtitle">Enter the primary details in English (required) and optionally in Arabic and Kurdish</p>

            <div class="language-tabs">
                <button type="button" class="lang-tab active" onclick="switchLang(event, 'en')">English *</button>
                <button type="button" class="lang-tab" onclick="switchLang(event, 'ar')">العربية</button>
                <button type="button" class="lang-tab" onclick="switchLang(event, 'ku')">کوردی</button>
            </div>

            <div class="lang-content active" id="lang-en">
                <div class="form-group">
                    <label class="form-label">Property Name (English) <span class="required">*</span></label>
                    <input type="text" name="name_en" id="name_en" class="form-input" value="{{ old('name_en') }}" required minlength="3" maxlength="255">
                    <span class="input-hint">Minimum 3 characters, maximum 255</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Description (English) <span class="required">*</span></label>
                    <textarea name="description_en" id="description_en" class="form-textarea" required minlength="5">{{ old('description_en') }}</textarea>
                    <span class="input-hint">Minimum 5 characters - Describe the property features and location</span>
                </div>
            </div>

            <div class="lang-content" id="lang-ar">
                <div class="form-group">
                    <label class="form-label">اسم العقار (عربي)</label>
                    <input type="text" name="name_ar" id="name_ar" class="form-input" value="{{ old('name_ar') }}" dir="rtl" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-label">الوصف (عربي)</label>
                    <textarea name="description_ar" id="description_ar" class="form-textarea" dir="rtl">{{ old('description_ar') }}</textarea>
                </div>
            </div>

            <div class="lang-content" id="lang-ku">
                <div class="form-group">
                    <label class="form-label">ناوی موڵک (کوردی)</label>
                    <input type="text" name="name_ku" id="name_ku" class="form-input" value="{{ old('name_ku') }}" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-label">وەسف (کوردی)</label>
                    <textarea name="description_ku" id="description_ku" class="form-textarea">{{ old('description_ku') }}</textarea>
                </div>
            </div>

            <!-- SECTION 2: Property Details -->
            <h2 class="section-title"><i class="fas fa-home"></i> Property Details</h2>
            <p class="section-subtitle">Specify property type, size, rooms, and pricing</p>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Property Type <span class="required">*</span></label>
                    <select name="property_type" id="property_type" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        <option value="apartment" {{ old('property_type') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                        <option value="house" {{ old('property_type') == 'house' ? 'selected' : '' }}>House</option>
                        <option value="villa" {{ old('property_type') == 'villa' ? 'selected' : '' }}>Villa</option>
                        <option value="land" {{ old('property_type') == 'land' ? 'selected' : '' }}>Land</option>
                        <option value="commercial" {{ old('property_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Listing Type <span class="required">*</span></label>
                    <select name="listing_type" id="listing_type" class="form-select" required>
                        <option value="sell" {{ old('listing_type') == 'sell' ? 'selected' : '' }}>For Sale</option>
                        <option value="rent" {{ old('listing_type') == 'rent' ? 'selected' : '' }}>For Rent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Area (m²) <span class="required">*</span></label>
                    <input type="number" name="area" id="area" class="form-input" value="{{ old('area') }}" step="0.01" required min="1">
                    <span class="input-hint">Must be at least 1 m²</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="sold" {{ old('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="rented" {{ old('status') == 'rented' ? 'selected' : '' }}>Rented</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Bedrooms <span class="required">*</span></label>
                    <input type="number" name="bedrooms" id="bedrooms" class="form-input" value="{{ old('bedrooms') }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Bathrooms <span class="required">*</span></label>
                    <input type="number" name="bathrooms" id="bathrooms" class="form-input" value="{{ old('bathrooms') }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Floor Number</label>
                    <input type="number" name="floor_number" id="floor_number" class="form-input" value="{{ old('floor_number') }}" min="0">
                    <span class="input-hint">Optional</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Year Built</label>
                    <input type="number" name="year_built" id="year_built" class="form-input" value="{{ old('year_built') }}" min="1900" max="2030">
                    <span class="input-hint">Between 1900 and 2030</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Price (USD) <span class="required">*</span></label>
                    <input type="number" name="price_usd" id="price_usd" class="form-input" value="{{ old('price_usd') }}" step="0.01" required min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Price (IQD) <span class="required">*</span></label>
                    <input type="number" name="price_iqd" id="price_iqd" class="form-input" value="{{ old('price_iqd') }}" step="0.01" required min="0">
                </div>
            </div>

            <!-- SECTION 3: Location -->
            <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Location</h2>
            <p class="section-subtitle">Specify where the property is located</p>

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
                    <input type="text" name="address" id="address" class="form-input" value="{{ old('address') }}" placeholder="Street name, building number, additional details...">
                    <span class="input-hint">Optional - Additional address details</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Latitude <span class="required">*</span></label>
                    <input type="number" id="latitude" name="latitude" class="form-input" value="{{ old('latitude') }}" step="0.000001" required readonly>
                    <span class="input-hint">Click on the map to set location</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Longitude <span class="required">*</span></label>
                    <input type="number" id="longitude" name="longitude" class="form-input" value="{{ old('longitude') }}" step="0.000001" required readonly>
                    <span class="input-hint">Click on the map to set location</span>
                </div>

                <div class="form-group full-width">
                    <div id="map-preview" style="height: 400px; width: 100%; border-radius: 12px; border: 2px solid var(--gray-200); margin-top: 10px;"></div>
                </div>
            </div>

            <!-- SECTION 4: Features -->
            <h2 class="section-title"><i class="fas fa-star"></i> Features & Amenities</h2>
            <p class="section-subtitle">Select available amenities and utilities</p>

            <div class="feature-grid">
                <label class="feature-checkbox">
                    <input type="checkbox" name="furnished" value="1" {{ old('furnished') ? 'checked' : '' }}>
                    <div class="feature-box">
                        <div class="feature-icon"><i class="fas fa-couch"></i></div>
                        <span class="feature-label">Furnished</span>
                    </div>
                </label>
                <label class="feature-checkbox">
                    <input type="checkbox" name="electricity" value="1" {{ old('electricity') ? 'checked' : '' }}>
                    <div class="feature-box">
                        <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                        <span class="feature-label">Electricity</span>
                    </div>
                </label>
                <label class="feature-checkbox">
                    <input type="checkbox" name="water" value="1" {{ old('water') ? 'checked' : '' }}>
                    <div class="feature-box">
                        <div class="feature-icon"><i class="fas fa-tint"></i></div>
                        <span class="feature-label">Water</span>
                    </div>
                </label>
                <label class="feature-checkbox">
                    <input type="checkbox" name="internet" value="1" {{ old('internet') ? 'checked' : '' }}>
                    <div class="feature-box">
                        <div class="feature-icon"><i class="fas fa-wifi"></i></div>
                        <span class="feature-label">Internet</span>
                    </div>
                </label>
            </div>

            <!-- SECTION 5: Images -->
            <h2 class="section-title"><i class="fas fa-images"></i> Property Images</h2>
            <p class="section-subtitle">Upload property photos - Minimum 1, Maximum 10 images (JPG or PNG only, max 5MB each)</p>

            <div class="image-upload-area" id="uploadArea" onclick="document.getElementById('imageInput').click()">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="upload-text">Click to upload images</div>
                <div class="upload-hint">JPG or PNG only (Max: 5MB per image)</div>
                <input type="file" id="imageInput" name="images[]" multiple accept="image/jpeg,image/jpg,image/png" style="display:none" onchange="previewImages(event)">
            </div>

            <div id="imagePreview" class="image-preview-grid"></div>

            <!-- Submit Section -->
            <div class="submit-section">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('office.properties') }}'">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-success" id="submitBtn">
                    <div class="spinner"></div>
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
// ==================== LOCATION SELECTOR ====================
class LocationSelector {
    constructor(options = {}) {
        this.citySelectId = options.citySelectId || "city-select";
        this.areaSelectId = options.areaSelectId || "area-select";
        this.onCityChange = options.onCityChange || null;
        this.onAreaChange = options.onAreaChange || null;
        this.cities = [];
        this.currentCityId = null;
        this.isLoading = false;
    }

    async init() {
        if (this.isLoading) return;
        this.isLoading = true;
        try {
            await this.loadCities();
        } catch (error) {
            console.error("Failed to initialize LocationSelector:", error);
            this.showError("Failed to load location data.");
        } finally {
            this.isLoading = false;
        }
    }

    async loadCities() {
        try {
            const response = await fetch("/v1/api/location/branches", {
                headers: { "Accept-Language": "en", "Accept": "application/json" }
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();
            if (result.success && result.data) {
                this.cities = result.data;
                this.populateCitySelect();
            }
        } catch (e) {
            console.error(e);
        }
    }

    populateCitySelect() {
        const citySelect = document.getElementById(this.citySelectId);
        if (!citySelect) return;
        citySelect.innerHTML = '<option value="">-- Select City --</option>';
        this.cities.forEach((city) => {
            const option = document.createElement("option");
            option.value = city.id;
            option.textContent = `${city.city_name_en} - ${city.city_name_ar}`;
            option.dataset.nameEn = city.city_name_en;
            option.dataset.nameAr = city.city_name_ar;
            option.dataset.nameKu = city.city_name_ku;
            option.dataset.lat = city.coordinates?.lat || city.latitude;
            option.dataset.lng = city.coordinates?.lng || city.longitude;
            citySelect.appendChild(option);
        });
    }

    async loadAreas(cityId) {
        const areaSelect = document.getElementById(this.areaSelectId);
        areaSelect.innerHTML = '<option value="">Loading...</option>';
        areaSelect.disabled = true;
        try {
            const response = await fetch(`/v1/api/location/branches/${cityId}/areas`, {
                headers: { "Accept-Language": "en", "Accept": "application/json" }
            });
            const result = await response.json();
            if (result.success && result.data) {
                areaSelect.innerHTML = '<option value="">-- Select Area --</option>';
                result.data.forEach((area) => {
                    const option = document.createElement("option");
                    option.value = area.id;
                    option.textContent = `${area.area_name_en}`;
                    option.dataset.nameEn = area.area_name_en;
                    option.dataset.nameAr = area.area_name_ar;
                    option.dataset.nameKu = area.area_name_ku;
                    option.dataset.lat = area.coordinates?.lat || area.latitude;
                    option.dataset.lng = area.coordinates?.lng || area.longitude;
                    areaSelect.appendChild(option);
                });
                areaSelect.disabled = false;
            }
        } catch (e) {
            areaSelect.innerHTML = '<option value="">Error loading areas</option>';
        }
    }

    showError(msg) { alert(msg); }
}

// ==================== GLOBAL VARIABLES ====================
let selectedFiles = [];
let locationSelector;
let map = null;
let marker = null;
const validationErrors = [];

// ==================== MAP INITIALIZATION ====================
function initMap(lat, lng) {
    const mapContainer = document.getElementById('map-preview');
    if (!mapContainer) return;

    let latVal = parseFloat(lat);
    let lngVal = parseFloat(lng);
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
            streetViewControl: false
        });

        marker = new google.maps.Marker({
            position: position,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP
        });

        marker.addListener('dragend', function() {
            const pos = marker.getPosition();
            document.getElementById('latitude').value = pos.lat().toFixed(6);
            document.getElementById('longitude').value = pos.lng().toFixed(6);
            validateField(document.getElementById('latitude'));
        });

        map.addListener('click', function(e) {
            const pos = e.latLng;
            marker.setPosition(pos);
            document.getElementById('latitude').value = pos.lat().toFixed(6);
            document.getElementById('longitude').value = pos.lng().toFixed(6);
            map.panTo(pos);
            validateField(document.getElementById('latitude'));
        });
    } else {
        map.setCenter(position);
        marker.setPosition(position);
    }
}

// ==================== PAGE LOAD ====================
document.addEventListener('DOMContentLoaded', async function() {
    locationSelector = new LocationSelector({
        onCityChange: function(city) {
            document.getElementById('city-en').value = city.nameEn || '';
            document.getElementById('city-ar').value = city.nameAr || '';
            document.getElementById('city-ku').value = city.nameKu || '';
            const cityOpt = document.querySelector(`#city-select option[value="${city.id}"]`);
            if(cityOpt && cityOpt.dataset.lat) {
                initMap(cityOpt.dataset.lat, cityOpt.dataset.lng);
            }
            validateField(document.getElementById('city-select'));
        },
        onAreaChange: function(area) {
            document.getElementById('district-en').value = area.nameEn || '';
            document.getElementById('district-ar').value = area.nameAr || '';
            document.getElementById('district-ku').value = area.nameKu || '';
            const areaOpt = document.querySelector(`#area-select option[value="${area.id}"]`);
            if(areaOpt && areaOpt.dataset.lat) {
                const lat = parseFloat(areaOpt.dataset.lat);
                const lng = parseFloat(areaOpt.dataset.lng);
                if(marker) {
                    const pos = { lat: lat, lng: lng };
                    marker.setPosition(pos);
                    map.panTo(pos);
                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                }
            }
            validateField(document.getElementById('area-select'));
        }
    });

    await locationSelector.init();

    document.getElementById('city-select')?.addEventListener('change', (e) => {
        const opt = e.target.options[e.target.selectedIndex];
        locationSelector.onCityChange({
            id: e.target.value,
            nameEn: opt.dataset.nameEn,
            nameAr: opt.dataset.nameAr,
            nameKu: opt.dataset.nameKu
        });
        locationSelector.loadAreas(e.target.value);

        // Reset area selection
        document.getElementById('area-select').value = '';
        document.getElementById('district-en').value = '';
        document.getElementById('district-ar').value = '';
        document.getElementById('district-ku').value = '';
    });

    document.getElementById('area-select')?.addEventListener('change', (e) => {
        const opt = e.target.options[e.target.selectedIndex];
        locationSelector.onAreaChange({
            id: e.target.value,
            nameEn: opt.dataset.nameEn,
            nameAr: opt.dataset.nameAr,
            nameKu: opt.dataset.nameKu
        });
    });

    // Add real-time validation to all inputs
    attachRealTimeValidation();
});

// ==================== VALIDATION HELPERS ====================
function showError(input, message) {
    if (!input) return;

    input.classList.add('error');
    input.classList.remove('valid');

    // Remove existing error message
    const existingError = input.parentNode.querySelector('.error-msg');
    if (existingError) existingError.remove();

    // Add new error message
    const errorSpan = document.createElement('div');
    errorSpan.className = 'error-msg';
    errorSpan.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    input.parentNode.appendChild(errorSpan);
}

function clearError(input) {
    if (!input) return;

    input.classList.remove('error');
    input.classList.add('valid');

    const errorSpan = input.parentNode.querySelector('.error-msg');
    if (errorSpan) errorSpan.remove();
}

function validateField(field) {
    if (!field) return true;

    const fieldName = field.name || field.id;
    const value = field.value?.trim();

    // Required field check
    if (field.hasAttribute('required') && !value) {
        showError(field, 'This field is required');
        return false;
    }

    // Specific validations
    switch(fieldName) {
        case 'name_en':
            if (value.length < 3) {
                showError(field, 'Minimum 3 characters required');
                return false;
            }
            if (value.length > 255) {
                showError(field, 'Maximum 255 characters allowed');
                return false;
            }
            break;

        case 'description_en':
            if (value.length < 5) {
                showError(field, 'Minimum 5 characters required');
                return false;
            }
            break;

        case 'property_type':
        case 'listing_type':
        case 'status':
            if (!value) {
                showError(field, 'Please select an option');
                return false;
            }
            break;

        case 'area':
            if (parseFloat(value) < 1) {
                showError(field, 'Area must be at least 1 m²');
                return false;
            }
            break;

        case 'bedrooms':
        case 'bathrooms':
            if (parseInt(value) < 0) {
                showError(field, 'Cannot be negative');
                return false;
            }
            break;

        case 'price_usd':
        case 'price_iqd':
            if (parseFloat(value) < 0) {
                showError(field, 'Price must be positive');
                return false;
            }
            break;

        case 'year_built':
            if (value) {
                const year = parseInt(value);
                if (year < 1900 || year > 2030) {
                    showError(field, 'Year must be between 1900 and 2030');
                    return false;
                }
            }
            break;

        case 'city-select':
            if (!value) {
                showError(field, 'Please select a city');
                return false;
            }
            break;

        case 'area-select':
            if (!value) {
                showError(field, 'Please select an area/district');
                return false;
            }
            break;

        case 'latitude':
        case 'longitude':
            if (!value || value == '0' || value == '0.000000') {
                showError(field, 'Please select location on the map');
                document.getElementById('map-preview').style.borderColor = '#ef4444';
                return false;
            } else {
                document.getElementById('map-preview').style.borderColor = '#10b981';
            }
            break;
    }

    clearError(field);
    return true;
}

function attachRealTimeValidation() {
    const fields = document.querySelectorAll('input[required], select[required], textarea[required], input[type="number"]');

    fields.forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            if (field.classList.contains('error')) {
                validateField(field);
            }
        });
    });
}

// ==================== FORM VALIDATION ====================
function validateForm() {
    validationErrors.length = 0;
    let isValid = true;

    // 1. Basic Information
    if (!validateField(document.getElementById('name_en'))) {
        validationErrors.push('Property name (English) is required (minimum 3 characters)');
        isValid = false;
    }

    if (!validateField(document.getElementById('description_en'))) {
        validationErrors.push('Property description (English) is required (minimum 5 characters)');
        isValid = false;
    }

    // 2. Property Details
    if (!validateField(document.getElementById('property_type'))) {
        validationErrors.push('Property type must be selected');
        isValid = false;
    }

    if (!validateField(document.getElementById('listing_type'))) {
        validationErrors.push('Listing type (Sale/Rent) must be selected');
        isValid = false;
    }

    if (!validateField(document.getElementById('area'))) {
        validationErrors.push('Area is required (minimum 1 m²)');
        isValid = false;
    }

    if (!validateField(document.getElementById('bedrooms'))) {
        validationErrors.push('Number of bedrooms is required');
        isValid = false;
    }

    if (!validateField(document.getElementById('bathrooms'))) {
        validationErrors.push('Number of bathrooms is required');
        isValid = false;
    }

    if (!validateField(document.getElementById('price_usd'))) {
        validationErrors.push('Price in USD is required');
        isValid = false;
    }

    if (!validateField(document.getElementById('price_iqd'))) {
        validationErrors.push('Price in IQD is required');
        isValid = false;
    }

    // 3. Location
    const citySelect = document.getElementById('city-select');
    if (!citySelect.value) {
        showError(citySelect, 'Please select a city');
        validationErrors.push('City must be selected');
        isValid = false;
    }

    const areaSelect = document.getElementById('area-select');
    if (!areaSelect.value) {
        showError(areaSelect, 'Please select an area/district');
        validationErrors.push('Area/District must be selected');
        isValid = false;
    }

    const latitude = document.getElementById('latitude');
    const longitude = document.getElementById('longitude');
    if (!latitude.value || !longitude.value || latitude.value == '0' || longitude.value == '0') {
        showError(latitude, 'Please select location on the map');
        validationErrors.push('Location must be selected on the map');
        document.getElementById('map-preview').style.borderColor = '#ef4444';
        isValid = false;
    }

    // 4. Images
    if (selectedFiles.length === 0) {
        validationErrors.push('At least 1 property image is required');
        document.getElementById('uploadArea').classList.add('error');
        isValid = false;
    } else {
        document.getElementById('uploadArea').classList.remove('error');
    }

    // Show validation summary
    if (!isValid) {
        const summary = document.getElementById('validationSummary');
        const errorList = document.getElementById('errorList');
        errorList.innerHTML = '';
        validationErrors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        summary.classList.add('show');
        summary.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        document.getElementById('validationSummary').classList.remove('show');
    }

    return isValid;
}

// ==================== LANGUAGE SWITCHER ====================
function switchLang(e, lang) {
    e.preventDefault();
    document.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.lang-content').forEach(c => c.classList.remove('active'));
    e.target.classList.add('active');
    document.getElementById('lang-' + lang).classList.add('active');
}

// ==================== IMAGE HANDLING ====================
const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png'];
const MAX_SIZE = 5 * 1024 * 1024; // 5MB

function previewImages(e) {
    const files = Array.from(e.target.files);
    const uploadArea = document.getElementById('uploadArea');
    uploadArea.classList.remove('error');

    let hasError = false;
    let validFiles = [];
    let errorMessages = [];

    files.forEach(file => {
        if (!ALLOWED_TYPES.includes(file.type)) {
            errorMessages.push(`❌ ${file.name}: Invalid file type. Only JPG and PNG allowed.`);
            hasError = true;
        } else if (file.size > MAX_SIZE) {
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            errorMessages.push(`❌ ${file.name}: File too large (${sizeMB}MB). Maximum 5MB allowed.`);
            hasError = true;
        } else {
            validFiles.push(file);
        }
    });

    if (hasError) {
        alert(errorMessages.join('\n\n'));
        uploadArea.classList.add('error');
        if (validFiles.length === 0) {
            document.getElementById('imageInput').value = '';
            return;
        }
    }

    if (validFiles.length > 0) {
        selectedFiles = [...selectedFiles, ...validFiles];

        if (selectedFiles.length > 10) {
            selectedFiles = selectedFiles.slice(0, 10);
            alert('⚠️ Maximum 10 images allowed. Extra images were removed.');
        }

        renderPreviews();
    }
}

function renderPreviews() {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    selectedFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${idx + 1}">
                <button type="button" class="remove-image" onclick="removeImage(${idx})" title="Remove image">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });

    updateFileInput();
}

function removeImage(idx) {
    selectedFiles.splice(idx, 1);
    renderPreviews();
}

function updateFileInput() {
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    document.getElementById('imageInput').files = dt.files;
}

// ==================== FORM SUBMISSION ====================
document.getElementById('propertyForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!validateForm()) {
        return false;
    }

    // Disable submit button and show loading
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="spinner" style="display:block"></div> Creating Property...';

    // Submit the form
    this.submit();
});
</script>
@endsection
