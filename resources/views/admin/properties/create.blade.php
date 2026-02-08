@extends('layouts.admin-layout')

@section('title', 'Add New Property')

@push('styles')
<style>
    /* Property Form Custom Styles */
    .property-form-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 16px;
        padding: 24px 32px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
    }

    .page-header-card h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .page-header-card p {
        color: #6b7280;
        font-size: 14px;
    }

    .owner-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        margin-top: 8px;
    }

    /* Style for Office */
    .owner-badge.office {
        background: linear-gradient(135deg, rgba(48,59,151,0.1), rgba(75,86,178,0.05));
        border: 2px solid #303b97;
        color: #303b97;
    }

    /* Style for Agent */
    .owner-badge.agent {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(52, 211, 153, 0.05));
        border: 2px solid #059669;
        color: #059669;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        background: #f3f4f6;
        color: #374151;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }

    .back-button:hover {
        background: #303b97;
        color: white;
        border-color: #303b97;
        transform: translateX(-3px);
    }

    /* Form Container */
    .form-container {
        background: white;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
    }

    /* Alert Messages */
    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: start;
        gap: 12px;
        font-weight: 500;
    }

    .alert-danger {
        background: #fef2f2;
        border: 2px solid #ef4444;
        color: #991b1b;
    }

    .alert i { font-size: 20px; margin-top: 2px; }

    /* Section Headers */
    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin: 32px 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 3px solid #303b97;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title:first-child { margin-top: 0; }
    .section-title i { color: #303b97; font-size: 22px; }

    .section-subtitle {
        color: #6b7280;
        font-size: 13px;
        margin-top: -14px;
        margin-bottom: 24px;
    }

    /* Form Elements */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .form-group.full-width { grid-column: 1 / -1; }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .required { color: #ef4444; font-size: 14px; }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        color: #1f2937;
        background: #f9fafb;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #303b97;
        background: white;
        box-shadow: 0 0 0 3px rgba(48,59,151,0.1);
    }

    .form-input.error, .form-select.error, .form-textarea.error {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .form-input.valid, .form-select.valid, .form-textarea.valid {
        border-color: #10b981;
    }

    .form-textarea { min-height: 110px; resize: vertical; }
    .input-hint { font-size: 11px; color: #6b7280; margin-top: 4px; }

    /* Language Tabs */
    .language-tabs {
        display: flex;
        gap: 6px;
        margin-bottom: 16px;
        background: #f3f4f6;
        padding: 5px;
        border-radius: 10px;
        width: fit-content;
    }

    .lang-tab {
        padding: 8px 18px;
        border: none;
        background: transparent;
        color: #6b7280;
        font-weight: 600;
        font-size: 13px;
        border-radius: 7px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .lang-tab.active {
        background: white;
        color: #303b97;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }

    .lang-content { display: none; }
    .lang-content.active { display: block; }

    /* Toggle Switch */
    .toggle-wrapper-box {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        padding: 14px 18px;
        background: #f9fafb;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        margin-bottom: 16px;
    }

    .toggle-wrapper-box:hover {
        background: white;
        border-color: #303b97;
    }

    .toggle-wrapper-box input[type="checkbox"] { display: none; }

    .toggle-switch {
        position: relative;
        width: 52px;
        height: 28px;
        background: #cbd5e1;
        border-radius: 28px;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        background: white;
        border-radius: 50%;
        top: 3px;
        left: 3px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .toggle-wrapper-box input[type="checkbox"]:checked + .toggle-switch {
        background: #303b97;
    }

    .toggle-wrapper-box input[type="checkbox"]:checked + .toggle-switch::after {
        transform: translateX(24px);
    }

    .toggle-label {
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    /* Map Section */
    .map-section {
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .map-section.hidden {
        display: none;
        opacity: 0;
        max-height: 0;
    }

    /* Image Upload */
    .image-upload-area {
        border: 3px dashed #e5e7eb;
        border-radius: 16px;
        padding: 50px 30px;
        text-align: center;
        background: linear-gradient(135deg, #f9fafb 0%, white 100%);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .image-upload-area:hover {
        border-color: #303b97;
        background: white;
    }

    .image-upload-area.error {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .upload-icon {
        font-size: 56px;
        color: #303b97;
        margin-bottom: 16px;
    }

    .upload-text {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .upload-hint { color: #6b7280; font-size: 13px; }

    .sort-instructions {
        background: linear-gradient(135deg, rgba(48,59,151,0.05), rgba(48,59,151,0.02));
        border: 1px dashed #303b97;
        border-radius: 10px;
        padding: 14px;
        margin-top: 16px;
        margin-bottom: 8px;
        display: none;
        align-items: center;
        gap: 10px;
        color: #303b97;
        font-weight: 600;
        font-size: 13px;
    }

    .sort-instructions.show { display: flex; }

    /* Image Preview */
    .image-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 14px;
        margin-top: 20px;
    }

    .image-preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        cursor: move;
        cursor: grab;
        transition: all 0.3s ease;
        border: 3px solid #e5e7eb;
    }

    .image-preview-item:active { cursor: grabbing; }

    .image-preview-item.dragging {
        opacity: 0.5;
        transform: scale(0.95);
        border-color: #303b97;
        box-shadow: 0 8px 20px rgba(48,59,151,0.3);
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
        background: linear-gradient(135deg, #303b97, #4b56b2);
        color: white;
        padding: 5px;
        font-size: 10px;
        font-weight: 800;
        text-align: center;
        letter-spacing: 1px;
    }

    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        pointer-events: none;
    }

    .remove-image {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(239,68,68,0.95);
        border: none;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 10;
    }

    .remove-image:hover {
        background: #ef4444;
        transform: scale(1.1);
    }

    .drag-handle {
        position: absolute;
        top: 8px;
        left: 8px;
        width: 30px;
        height: 30px;
        background: rgba(48,59,151,0.9);
        border: none;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        z-index: 5;
        cursor: move;
        cursor: grab;
    }

    .drag-handle:active { cursor: grabbing; }

    /* Features */
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
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
        gap: 10px;
        padding: 14px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        background: #f9fafb;
        transition: all 0.3s ease;
    }

    .feature-checkbox input:checked + .feature-box {
        border-color: #303b97;
        background: linear-gradient(135deg, rgba(48,59,151,0.1), rgba(48,59,151,0.05));
    }

    .feature-box:hover {
        border-color: #4b56b2;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(48,59,151,0.15);
    }

    .feature-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #303b97, #4b56b2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }

    .feature-label {
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    /* Submit Section */
    .submit-section {
        margin-top: 40px;
        padding-top: 24px;
        border-top: 2px solid #f3f4f6;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn {
        padding: 14px 40px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
        box-shadow: 0 4px 14px rgba(16,185,129,0.3);
    }

    .btn-success:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16,185,129,0.4);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    /* Loading Spinner */
    .spinner {
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top: 3px solid white;
        width: 18px;
        height: 18px;
        animation: spin 1s linear infinite;
        display: none;
    }

    .btn:disabled .spinner { display: block; }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Validation Summary */
    .validation-summary {
        background: #fef2f2;
        border: 2px solid #ef4444;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 24px;
        display: none;
    }

    .validation-summary.show { display: block; }

    .validation-summary h4 {
        color: #ef4444;
        font-size: 16px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
    }

    .validation-summary ul {
        margin: 0;
        padding-left: 20px;
    }

    .validation-summary li {
        color: #991b1b;
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .page-header-card {
            flex-direction: column;
            gap: 16px;
            text-align: center;
        }
        .form-container { padding: 24px 20px; }
        .feature-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="property-form-container">

    {{--
        ========================================================
        DYNAMIC HEADER: Handles BOTH Agents and Offices
        ========================================================
    --}}
    <div class="page-header-card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <div>
            <h1>Add New Property</h1>
            <p>Fill in all required details to list the property</p>

            {{-- Check if this property is for an Office --}}
            @if(isset($office) && $office)
                <div class="owner-badge office">
                    <i class="fas fa-building"></i>
                    Property for Office: {{ $office->company_name }}
                </div>

            {{-- Check if this property is for an Agent --}}
            @elseif(isset($agent) && $agent)
                <div class="owner-badge agent">
                    <i class="fas fa-user-tie"></i>
                    Property for Agent: {{ $agent->agent_name }}
                </div>
            @endif
        </div>

        {{-- Dynamic Back Button --}}
        @php
            $backRoute = route('admin.properties.index');
            if(isset($office) && $office) {
                $backRoute = route('admin.offices.edit', $office->id);
            } elseif(isset($agent) && $agent) {
                $backRoute = route('admin.agents.edit', $agent->id);
            }
        @endphp

        <a href="{{ $backRoute }}" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>

    <div class="form-container">

        <div class="validation-summary" id="validationSummary">
            <h4><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h4>
            <ul id="errorList"></ul>
        </div>

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

        <form action="{{ route('admin.properties.store') }}" method="POST" enctype="multipart/form-data" id="propertyForm" novalidate>
            @csrf

            {{--
                ========================================================
                DYNAMIC HIDDEN FIELDS: Injects Owner Type & ID
                ========================================================
            --}}
            @if(isset($office) && $office)
                <input type="hidden" name="owner_type" value="App\Models\RealEstateOffice">
                <input type="hidden" name="owner_id" value="{{ $office->id }}">
            @elseif(isset($agent) && $agent)
                <input type="hidden" name="owner_type" value="App\Models\Agent">
                <input type="hidden" name="owner_id" value="{{ $agent->id }}">
            @endif

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
                    <input type="number" name="bedrooms" id="bedrooms" class="form-input" value="{{ old('bedrooms', 0) }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Bathrooms <span class="required">*</span></label>
                    <input type="number" name="bathrooms" id="bathrooms" class="form-input" value="{{ old('bathrooms', 0) }}" min="0" required>
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

                <div class="form-group full-width">
                    <label class="toggle-wrapper-box">
                        <input type="checkbox" name="has_map" id="mapToggle" value="1" checked>
                        <div class="toggle-switch"></div>
                        <span class="toggle-label"><i class="fas fa-map-marked-alt"></i> Pin Exact Location on Map</span>
                    </label>
                </div>

                <div id="mapSection" class="form-group full-width map-section">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Latitude</label>
                            <input type="number" id="latitude" name="latitude" class="form-input" value="{{ old('latitude', '0') }}" step="0.000001" readonly>
                            <span class="input-hint">Click on the map to set location</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Longitude</label>
                            <input type="number" id="longitude" name="longitude" class="form-input" value="{{ old('longitude', '0') }}" step="0.000001" readonly>
                            <span class="input-hint">Click on the map to set location</span>
                        </div>
                    </div>

                    <div id="map-preview" style="height: 400px; width: 100%; border-radius: 12px; border: 2px solid #e5e7eb; margin-top: 10px;"></div>
                </div>
            </div>

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

            <h2 class="section-title"><i class="fas fa-images"></i> Property Images</h2>
            <p class="section-subtitle">Upload property photos - Minimum 1, Maximum 10 images (JPG or PNG only, max 5MB each)</p>

            <div class="image-upload-area" id="uploadArea" onclick="document.getElementById('imageInput').click()">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="upload-text">Click to upload images</div>
                <div class="upload-hint">JPG or PNG only (Max: 5MB per image)</div>
                <input type="file" id="imageInput" name="images[]" multiple accept="image/jpeg,image/jpg,image/png" style="display:none" onchange="previewImages(event)">
            </div>

            <div class="sort-instructions" id="sortInstructions">
                <i class="fas fa-arrows-alt" style="font-size: 18px;"></i>
                <span>Drag and drop images to reorder. First image will be the cover photo.</span>
            </div>

            <div id="imagePreview" class="image-preview-grid"></div>

            <div class="submit-section">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ $backRoute }}'">
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

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>
<script>
let map, marker, selectedImages = [], draggedIndex = null;

function switchLang(e, lang) {
    e.preventDefault();
    document.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
    e.target.classList.add('active');
    document.querySelectorAll('.lang-content').forEach(c => c.classList.remove('active'));
    document.getElementById('lang-' + lang).classList.add('active');
}

function initMap() {
    const erbil = {lat: 36.1911, lng: 44.0091};
    map = new google.maps.Map(document.getElementById('map-preview'), {
        center: erbil, zoom: 12,
        styles: [{featureType: "poi", elementType: "labels", stylers: [{visibility: "off"}]}]
    });
    marker = new google.maps.Marker({position: erbil, map: map, draggable: true, animation: google.maps.Animation.DROP});
    google.maps.event.addListener(marker, 'dragend', e => updateLatLng(e.latLng.lat(), e.latLng.lng()));
    map.addListener('click', e => {
        marker.setPosition(e.latLng);
        updateLatLng(e.latLng.lat(), e.latLng.lng());
    });
}

function updateLatLng(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
}

document.addEventListener('DOMContentLoaded', function() {
    loadCities();

    const mapToggle = document.getElementById('mapToggle');
    if (mapToggle) {
        mapToggle.addEventListener('change', function() {
            const section = document.getElementById('mapSection');
            if (this.checked) {
                section.classList.remove('hidden');
                setTimeout(() => {
                    if (map) {
                        google.maps.event.trigger(map, 'resize');
                        map.setCenter(marker.getPosition());
                    }
                }, 100);
            } else {
                section.classList.add('hidden');
            }
        });
    }

    const citySelect = document.getElementById('city-select');
    if (citySelect) {
        citySelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt.value) {
                const data = JSON.parse(opt.dataset.city);
                document.getElementById('city-en').value = data.name.en;
                document.getElementById('city-ar').value = data.name.ar;
                document.getElementById('city-ku').value = data.name.ku;
                loadAreas(opt.value);
            } else {
                document.getElementById('area-select').disabled = true;
                document.getElementById('area-select').innerHTML = '<option value="">Select City First</option>';
            }
        });
    }

    const areaSelect = document.getElementById('area-select');
    if (areaSelect) {
        areaSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt.value) {
                const data = JSON.parse(opt.dataset.area);
                document.getElementById('district-en').value = data.name.en;
                document.getElementById('district-ar').value = data.name.ar;
                document.getElementById('district-ku').value = data.name.ku;
            }
        });
    }
});

async function loadCities() {
    try {
        // NOTE: Adjust this API endpoint to match your routes if different
        const res = await fetch('/v1/api/location/branches');
        const data = await res.json();
        const sel = document.getElementById('city-select');
        sel.innerHTML = '<option value="">-- Select City --</option>';
        if (data.success && data.data) {
            data.data.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = b.name.en;
                opt.dataset.city = JSON.stringify(b);
                sel.appendChild(opt);
            });
        }
    } catch (e) {
        console.error('Error loading cities:', e);
    }
}

async function loadAreas(branchId) {
    try {
        const res = await fetch(`/v1/api/location/branches/${branchId}/areas`);
        const data = await res.json();
        const sel = document.getElementById('area-select');
        sel.innerHTML = '<option value="">-- Select Area --</option>';
        if (data.success && data.data) {
            data.data.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = a.name.en;
                opt.dataset.area = JSON.stringify(a);
                sel.appendChild(opt);
            });
            sel.disabled = false;
        }
    } catch (e) {
        console.error('Error loading areas:', e);
    }
}

function previewImages(event) {
    const files = Array.from(event.target.files);
    if (selectedImages.length + files.length > 10) {
        alert('Maximum 10 images allowed!');
        event.target.value = '';
        return;
    }

    let hasError = false;
    files.forEach(file => {
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
            alert(`Invalid file type: ${file.name}`);
            hasError = true;
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert(`File too large: ${file.name}`);
            hasError = true;
            return;
        }
        selectedImages.push(file);
    });

    if (hasError) {
        event.target.value = '';
        return;
    }

    if (selectedImages.length > 0) {
        document.getElementById('sortInstructions').classList.add('show');
        document.getElementById('uploadArea').classList.remove('error');
    }

    renderImagePreviews();
}

function renderImagePreviews() {
    const container = document.getElementById('imagePreview');
    container.innerHTML = '';

    selectedImages.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.draggable = true;
            div.dataset.index = idx;
            div.innerHTML = `
                <button type="button" class="drag-handle" title="Drag"><i class="fas fa-arrows-alt"></i></button>
                <img src="${e.target.result}" alt="Preview ${idx + 1}">
                <button type="button" class="remove-image" onclick="removeImage(${idx})"><i class="fas fa-times"></i></button>
            `;
            div.addEventListener('dragstart', handleDragStart);
            div.addEventListener('dragover', handleDragOver);
            div.addEventListener('drop', handleDrop);
            div.addEventListener('dragend', handleDragEnd);
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeImage(idx) {
    selectedImages.splice(idx, 1);
    renderImagePreviews();
    if (selectedImages.length === 0) {
        document.getElementById('sortInstructions').classList.remove('show');
    }
    updateFileInput();
}

function updateFileInput() {
    const dt = new DataTransfer();
    selectedImages.forEach(f => dt.items.add(f));
    document.getElementById('imageInput').files = dt.files;
}

function handleDragStart(e) {
    draggedIndex = parseInt(this.dataset.index);
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragOver(e) {
    if (e.preventDefault) e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    this.classList.add('drag-over');
    return false;
}

function handleDrop(e) {
    if (e.stopPropagation) e.stopPropagation();
    const dropIdx = parseInt(this.dataset.index);
    if (draggedIndex !== dropIdx) {
        const item = selectedImages[draggedIndex];
        selectedImages.splice(draggedIndex, 1);
        selectedImages.splice(dropIdx, 0, item);
        renderImagePreviews();
        updateFileInput();
    }
    return false;
}

function handleDragEnd() {
    document.querySelectorAll('.image-preview-item').forEach(i => i.classList.remove('dragging', 'drag-over'));
}

document.getElementById('propertyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const errors = [];

    if (!document.getElementById('name_en').value.trim() || document.getElementById('name_en').value.length < 3) {
        errors.push('Property name (English) must be at least 3 characters');
    }
    if (!document.getElementById('description_en').value.trim() || document.getElementById('description_en').value.length < 5) {
        errors.push('Description (English) must be at least 5 characters');
    }
    if (!document.getElementById('property_type').value) errors.push('Please select a property type');
    if (!document.getElementById('area').value || parseFloat(document.getElementById('area').value) < 1) {
        errors.push('Area must be at least 1 m²');
    }
    if (document.getElementById('bedrooms').value === '' || document.getElementById('bedrooms').value < 0) {
        errors.push('Please enter number of bedrooms');
    }
    if (document.getElementById('bathrooms').value === '' || document.getElementById('bathrooms').value < 0) {
        errors.push('Please enter number of bathrooms');
    }
    if (!document.getElementById('price_usd').value || parseFloat(document.getElementById('price_usd').value) < 0) {
        errors.push('Please enter a valid price in USD');
    }
    if (!document.getElementById('price_iqd').value || parseFloat(document.getElementById('price_iqd').value) < 0) {
        errors.push('Please enter a valid price in IQD');
    }
    // Location validation is strict
    if (!document.getElementById('city-en').value) errors.push('Please select a city');
    if (!document.getElementById('district-en').value) errors.push('Please select a district/area');

    if (selectedImages.length === 0) {
        errors.push('Please upload at least 1 property image');
        document.getElementById('uploadArea').classList.add('error');
    } else if (selectedImages.length > 10) {
        errors.push('Maximum 10 images allowed');
    }

    if (errors.length > 0) {
        document.getElementById('errorList').innerHTML = errors.map(e => `<li>${e}</li>`).join('');
        document.getElementById('validationSummary').classList.add('show');
        window.scrollTo({top: 0, behavior: 'smooth'});
        return false;
    }

    document.getElementById('validationSummary').classList.remove('show');
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner" style="display:block;"></div> Creating...';
    this.submit();
});

document.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(input => {
    input.addEventListener('blur', function() {
        const val = this.value.trim();
        let valid = true;
        if (this.hasAttribute('required') && !val) valid = false;
        if (this.hasAttribute('minlength') && val.length < parseInt(this.getAttribute('minlength'))) valid = false;
        if (this.type === 'number') {
            const min = parseFloat(this.getAttribute('min'));
            if (min !== null && parseFloat(val) < min) valid = false;
        }
        this.classList.toggle('error', !valid);
        this.classList.toggle('valid', valid);
    });
});

console.log('Property form initialized');
</script>
@endpush
