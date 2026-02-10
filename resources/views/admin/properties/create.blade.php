@extends('layouts.admin-layout')

@section('title', 'Add New Property')

@push('styles')
<style>
    /* ============================================
       PROPERTY FORM - COMPLETE STYLING
       ============================================ */

    /* Container */
    .property-form-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Page Header */
    .page-header-card {
        background: white;
        border-radius: 16px;
        padding: 24px 32px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-header-card h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 4px 0;
    }

    .page-header-card p {
        color: #6b7280;
        font-size: 14px;
        margin: 0;
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

    .owner-badge.office {
        background: linear-gradient(135deg, rgba(48,59,151,0.1), rgba(75,86,178,0.05));
        border: 2px solid #303b97;
        color: #303b97;
    }

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

    /* AI Helper Badge */
    .ai-helper-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        color: white;
        border-radius: 12px;
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }

    .ai-helper-badge i {
        font-size: 18px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    /* Alerts */
    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: start;
        gap: 12px;
        font-weight: 500;
    }

    .alert-info {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(96, 165, 250, 0.05));
        border: 2px solid #3b82f6;
        color: #1e40af;
    }

    .alert-danger {
        background: #fef2f2;
        border: 2px solid #ef4444;
        color: #991b1b;
    }

    .alert i {
        font-size: 20px;
        margin-top: 2px;
    }

    .alert ul {
        margin: 8px 0 0 20px;
        padding: 0;
    }

    .alert li {
        margin-bottom: 4px;
    }

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

    .section-title:first-of-type {
        margin-top: 0;
    }

    .section-title i {
        color: #303b97;
        font-size: 22px;
    }

    .section-subtitle {
        color: #6b7280;
        font-size: 13px;
        margin-top: -14px;
        margin-bottom: 24px;
    }

    /* Smart Input Wrapper */
    .smart-input-wrapper {
        position: relative;
        margin-bottom: 24px;
    }

    .language-indicator {
        position: absolute;
        top: -8px;
        right: 12px;
        background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        color: white;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        z-index: 10;
        display: none;
        box-shadow: 0 2px 6px rgba(139, 92, 246, 0.3);
    }

    .language-indicator.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Translation Loader */
    .translation-loader {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
        pointer-events: none;
    }

    .translation-loader.show {
        display: block;
    }

    .translation-loader i {
        color: #8b5cf6;
        font-size: 18px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Form Grid */
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

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    /* Form Labels */
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .required {
        color: #ef4444;
        font-size: 14px;
    }

    /* Form Inputs */
    .form-input,
    .form-select,
    .form-textarea {
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

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #303b97;
        background: white;
        box-shadow: 0 0 0 3px rgba(48,59,151,0.1);
    }

    .form-input.processing {
        border-color: #8b5cf6;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), white);
    }

    .form-input.error,
    .form-select.error,
    .form-textarea.error {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .form-input.valid,
    .form-select.valid,
    .form-textarea.valid {
        border-color: #10b981;
    }

    .form-textarea {
        min-height: 110px;
        resize: vertical;
    }

    .input-hint {
        font-size: 11px;
        color: #6b7280;
        margin-top: 4px;
    }

    .input-hint.ai-hint {
        color: #8b5cf6;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Manual Override Section */
    details {
        margin-top: 16px;
        margin-bottom: 24px;
    }

    details summary {
        cursor: pointer;
        color: #6b7280;
        font-size: 13px;
        font-weight: 600;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        list-style: none;
    }

    details summary::-webkit-details-marker {
        display: none;
    }

    details summary:hover {
        background: white;
        border-color: #303b97;
        color: #303b97;
    }

    details summary i {
        margin-right: 8px;
    }

    details[open] summary {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        border-bottom-color: transparent;
    }

    .manual-edit-panel {
        padding: 20px;
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-top: none;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    /* Hidden Fields */
    .hidden-translations {
        display: none;
    }

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

    .toggle-wrapper-box input[type="checkbox"] {
        display: none;
    }

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
    }

    .map-section.hidden {
        display: none;
        opacity: 0;
    }

    #map-preview {
        height: 400px;
        width: 100%;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        margin-top: 10px;
    }

    /* Image Upload Area */
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
        transform: translateY(-2px);
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

    .upload-hint {
        color: #6b7280;
        font-size: 13px;
    }

    /* Sort Instructions */
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

    .sort-instructions.show {
        display: flex;
    }

    /* Image Preview Grid */
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

    .image-preview-item:active {
        cursor: grabbing;
    }

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

    .drag-handle:active {
        cursor: grabbing;
    }

    /* Features Grid */
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
        flex-wrap: wrap;
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
        animation: spinRotate 1s linear infinite;
        display: none;
    }

    .btn:disabled .spinner {
        display: block;
    }

    @keyframes spinRotate {
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

    .validation-summary.show {
        display: block;
    }

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

    /* Responsive Design */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .page-header-card {
            flex-direction: column;
            text-align: center;
        }

        .form-container {
            padding: 24px 20px;
        }

        .feature-grid {
            grid-template-columns: 1fr;
        }

        .submit-section {
            flex-direction: column-reverse;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="property-form-container">

    <!-- Page Header -->
    <div class="page-header-card">
        <div>
            <h1>Add New Property</h1>
            <p>Enter details in any language - translations handled automatically</p>

            @if(isset($office) && $office)
                <div class="owner-badge office">
                    <i class="fas fa-building"></i>
                    Property for Office: {{ $office->company_name }}
                </div>
            @elseif(isset($agent) && $agent)
                <div class="owner-badge agent">
                    <i class="fas fa-user-tie"></i>
                    Property for Agent: {{ $agent->agent_name }}
                </div>
            @endif
        </div>

        @php
            $backRoute = route('admin.properties.index');
            if(isset($office) && $office) {
                $backRoute = route('admin.offices.edit', $office->id);
            } elseif(isset($agent) && $agent) {
                $backRoute = route('admin.agents.edit', $agent->id);
            }
        @endphp

        <a href="{{ $backRoute }}" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Form Container -->
    <div class="form-container">

        <!-- AI Helper Badge -->
        <div class="ai-helper-badge">
            <i class="fas fa-magic"></i>
            <span>AI-Powered Form - Write in Kurdish, English, or Arabic!</span>
        </div>

        <!-- Info Alert -->
        <div class="alert alert-info">
            <i class="fas fa-lightbulb"></i>
            <div>
                <strong>Smart Entry:</strong> Type property name/description in <strong>any language</strong> (Kurdish, English, Arabic).
                Other languages will be auto-translated. Just fill what you know!
            </div>
        </div>

        <!-- Validation Summary -->
        <div class="validation-summary" id="validationSummary">
            <h4><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h4>
            <ul id="errorList"></ul>
        </div>

        <!-- Laravel Errors -->
        @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Error!</strong> Please check the form for errors.
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('admin.properties.store') }}" method="POST" enctype="multipart/form-data" id="propertyForm" novalidate>
            @csrf

            <!-- Owner Type (Hidden or Visible based on context) -->
            @if(isset($office) && $office)
                <input type="hidden" name="owner_type" value="RealEstateOffice">
                <input type="hidden" name="owner_id" value="{{ $office->id }}">
            @elseif(isset($agent) && $agent)
                <input type="hidden" name="owner_type" value="Agent">
                <input type="hidden" name="owner_id" value="{{ $agent->id }}">
            @else
                <!-- Admin adding property -->
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Owner Type <span class="required">*</span></label>
                        <select name="owner_type" id="owner_type" class="form-select" required>
                            <option value="">-- Select Owner --</option>
                            <option value="Agent">Agent</option>
                            <option value="RealEstateOffice">Real Estate Office</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Select Owner <span class="required">*</span></label>
                        <select name="owner_id" id="owner_id" class="form-select" required disabled>
                            <option value="">Select owner type first</option>
                        </select>
                    </div>
                </div>
            @endif

            <!-- ========================================
                 SECTION 1: BASIC INFORMATION
                 ======================================== -->
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i> Basic Information
            </h2>
            <p class="section-subtitle">Type in any language - we'll auto-translate (or enter manually below)</p>

            <!-- Smart Property Name Input -->
            <div class="form-group smart-input-wrapper">
                <label class="form-label">Property Name <span class="required">*</span></label>
                <div class="language-indicator" id="nameLanguageIndicator"></div>
                <input
                    type="text"
                    id="smartPropertyName"
                    class="form-input"
                    placeholder="مثال: شقة فاخرة في أربيل | Example: Luxury Apartment in Erbil | نموونە: شوقەی لوکس لە هەولێر"
                    minlength="3"
                    maxlength="255"
                >
                <div class="translation-loader"><i class="fas fa-spinner fa-spin"></i></div>
                <span class="input-hint ai-hint">
                    <i class="fas fa-magic"></i>
                    Type in any language - auto-translates OR manually fill fields below
                </span>
            </div>

            <!-- Manual Name Override -->
            <details>
                <summary>
                    <i class="fas fa-edit"></i> Manually Edit Name Translations (Optional)
                </summary>
                <div class="manual-edit-panel">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Name (English)</label>
                            <input type="text" name="name[en]" id="name_en_manual" class="form-input" placeholder="e.g., Luxury Apartment">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Name (العربية)</label>
                            <input type="text" name="name[ar]" id="name_ar_manual" class="form-input" placeholder="مثال: شقة فاخرة" dir="rtl">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Name (کوردی)</label>
                            <input type="text" name="name[ku]" id="name_ku_manual" class="form-input" placeholder="نموونە: شوقەی لوکس">
                        </div>
                    </div>
                </div>
            </details>

            <!-- Smart Description Input -->
            <div class="form-group smart-input-wrapper">
                <label class="form-label">Description <span class="required">*</span></label>
                <div class="language-indicator" id="descLanguageIndicator"></div>
                <textarea
                    id="smartDescription"
                    class="form-textarea"
                    placeholder="Describe the property in your preferred language..."
                    minlength="5"
                ></textarea>
                <div class="translation-loader"><i class="fas fa-spinner fa-spin"></i></div>
                <span class="input-hint ai-hint">
                    <i class="fas fa-magic"></i>
                    Describe property features, location, nearby facilities
                </span>
            </div>

            <!-- Manual Description Override -->
            <details>
                <summary>
                    <i class="fas fa-edit"></i> Manually Edit Description Translations (Optional)
                </summary>
                <div class="manual-edit-panel">
                    <div class="form-group">
                        <label class="form-label">Description (English)</label>
                        <textarea name="description[en]" id="description_en_manual" class="form-textarea" placeholder="Describe in English..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (العربية)</label>
                        <textarea name="description[ar]" id="description_ar_manual" class="form-textarea" placeholder="وصف بالعربية..." dir="rtl"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (کوردی)</label>
                        <textarea name="description[ku]" id="description_ku_manual" class="form-textarea" placeholder="وەسفکردن بە کوردی..."></textarea>
                    </div>
                </div>
            </details>

            <!-- Hidden Auto-Translation Fields -->
            <div class="hidden-translations">
                <input type="hidden" id="name_en" value="">
                <input type="hidden" id="name_ar" value="">
                <input type="hidden" id="name_ku" value="">
                <input type="hidden" id="description_en" value="">
                <input type="hidden" id="description_ar" value="">
                <input type="hidden" id="description_ku" value="">
            </div>

            <!-- ========================================
                 SECTION 2: PROPERTY DETAILS
                 ======================================== -->
            <h2 class="section-title">
                <i class="fas fa-home"></i> Property Details
            </h2>
            <p class="section-subtitle">Basic property information</p>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Property Type <span class="required">*</span></label>
                    <select name="type[category]" id="property_type" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        <option value="apartment">🏢 Apartment / شقة / شوقە</option>
                        <option value="house">🏠 House / منزل / خانوو</option>
                        <option value="villa">🏰 Villa / فيلا / ڤیلا</option>
                        <option value="land">🌍 Land / أرض / زەوی</option>
                        <option value="commercial">🏪 Commercial / تجاري / بازرگانی</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Listing Type <span class="required">*</span></label>
                    <select name="listing_type" id="listing_type" class="form-select" required>
                        <option value="sell">For Sale / للبيع / بۆ فرۆشتن</option>
                        <option value="rent">For Rent / للإيجار / بۆ کرێ</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Area (m²) <span class="required">*</span></label>
                    <input type="number" name="area" id="area" class="form-input" placeholder="e.g., 120" step="0.01" required min="1">
                    <span class="input-hint">Property size in square meters</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="available">✅ Available</option>
                        <option value="pending">⏳ Pending</option>
                        <option value="sold">💰 Sold</option>
                        <option value="rented">🔑 Rented</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Bedrooms <span class="required">*</span></label>
                    <input type="number" name="rooms[bedroom][count]" id="bedrooms" class="form-input" value="0" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Bathrooms <span class="required">*</span></label>
                    <input type="number" name="rooms[bathroom][count]" id="bathrooms" class="form-input" value="0" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Living Rooms</label>
                    <input type="number" name="rooms[living_room][count]" id="living_rooms" class="form-input" value="0" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Floor Number</label>
                    <input type="number" name="floor_number" id="floor_number" class="form-input" placeholder="e.g., 3" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Year Built</label>
                    <input type="number" name="year_built" id="year_built" class="form-input" placeholder="e.g., 2020" min="1900" max="2030">
                </div>

                <div class="form-group">
                    <label class="form-label">Price (USD) <span class="required">*</span></label>
                    <input type="number" name="price_usd" id="price_usd" class="form-input" placeholder="e.g., 150000" step="0.01" required min="0">
                    <span class="input-hint">Price in US Dollars</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Price (IQD) <span class="required">*</span></label>
                    <input type="number" name="price" id="price_iqd" class="form-input" placeholder="e.g., 196500000" step="0.01" required min="0">
                    <span class="input-hint">Price in Iraqi Dinar</span>
                </div>
            </div>

            <!-- ========================================
                 SECTION 3: LOCATION
                 ======================================== -->
            <h2 class="section-title">
                <i class="fas fa-map-marker-alt"></i> Location
            </h2>
            <p class="section-subtitle">Where is the property located?</p>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">City <span class="required">*</span></label>
                    <select id="city-select" class="form-select" required>
                        <option value="">Loading cities...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">District/Area <span class="required">*</span></label>
                    <select id="area-select" class="form-select" disabled required>
                        <option value="">Select City First</option>
                    </select>
                </div>

                <!-- Hidden Location Fields -->
                <input type="hidden" name="address_details[city][en]" id="city-en">
                <input type="hidden" name="address_details[city][ar]" id="city-ar">
                <input type="hidden" name="address_details[city][ku]" id="city-ku">
                <input type="hidden" name="address_details[district][en]" id="district-en">
                <input type="hidden" name="address_details[district][ar]" id="district-ar">
                <input type="hidden" name="address_details[district][ku]" id="district-ku">

                <div class="form-group full-width">
                    <label class="form-label">Street Address (Optional)</label>
                    <input type="text" name="address" id="address" class="form-input" placeholder="Building number, street name...">
                </div>

                <div class="form-group full-width">
                    <label class="toggle-wrapper-box">
                        <input type="checkbox" name="has_map" id="mapToggle" value="1" checked>
                        <div class="toggle-switch"></div>
                        <span class="toggle-label">
                            <i class="fas fa-map-marked-alt"></i> Pin Location on Map
                        </span>
                    </label>
                </div>

                <div id="mapSection" class="form-group full-width map-section">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Latitude</label>
                            <input type="number" name="locations[0][lat]" id="latitude" class="form-input" value="0" step="0.000001" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Longitude</label>
                            <input type="number" name="locations[0][lng]" id="longitude" class="form-input" value="0" step="0.000001" readonly>
                        </div>
                    </div>
                    <div id="map-preview"></div>
                </div>
            </div>

            <!-- ========================================
                 SECTION 4: FEATURES
                 ======================================== -->
            <h2 class="section-title">
                <i class="fas fa-star"></i> Features & Amenities
            </h2>
            <p class="section-subtitle">What utilities are available?</p>

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

            <!-- ========================================
                 SECTION 5: IMAGES
                 ======================================== -->
            <h2 class="section-title">
                <i class="fas fa-images"></i> Property Images
            </h2>
            <p class="section-subtitle">Upload 1-10 images (JPG/PNG, max 5MB each) - First image = Cover Photo</p>

            <div class="image-upload-area" id="uploadArea" onclick="document.getElementById('imageInput').click()">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="upload-text">Click to Upload Images</div>
                <div class="upload-hint">Drag & drop to reorder after upload</div>
                <input type="file" id="imageInput" name="images[]" multiple accept="image/jpeg,image/jpg,image/png" style="display:none" onchange="previewImages(event)">
            </div>

            <div class="sort-instructions" id="sortInstructions">
                <i class="fas fa-arrows-alt" style="font-size: 18px;"></i>
                <span>Drag images to reorder. First image = Cover photo</span>
            </div>

            <div id="imagePreview" class="image-preview-grid"></div>

            <!-- ========================================
                 SUBMIT SECTION
                 ======================================== -->
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
// ============================================
// GLOBAL VARIABLES
// ============================================
let map, marker, selectedImages = [], draggedIndex = null, translationTimeout = null;

// ============================================
// LANGUAGE DETECTION
// ============================================
function detectLanguage(text) {
    const arabicPattern = /[\u0600-\u06FF]/;
    const kurdishPattern = /[ئابپتجچحخدرڕزژسشعغفڤقکگلڵمنوۆهھەیێ]/;

    if (arabicPattern.test(text) && !kurdishPattern.test(text)) return 'ar';
    if (kurdishPattern.test(text)) return 'ku';
    return 'en';
}

// ============================================
// SMART TRANSLATION SYSTEM
// ============================================

// Property Name Handler
document.getElementById('smartPropertyName')?.addEventListener('input', function(e) {
    const value = this.value.trim();
    const indicator = document.getElementById('nameLanguageIndicator');

    if (!value) {
        indicator.classList.remove('show');
        return;
    }

    const detectedLang = detectLanguage(value);
    const langNames = { 'en': 'English', 'ar': 'العربية', 'ku': 'کوردی' };

    indicator.textContent = langNames[detectedLang];
    indicator.classList.add('show');
    this.classList.add('processing');

    clearTimeout(translationTimeout);
    translationTimeout = setTimeout(() => {
        translateText(value, detectedLang, 'name');
    }, 1000);
});

// Description Handler
document.getElementById('smartDescription')?.addEventListener('input', function(e) {
    const value = this.value.trim();
    const indicator = document.getElementById('descLanguageIndicator');

    if (!value) {
        indicator.classList.remove('show');
        return;
    }

    const detectedLang = detectLanguage(value);
    const langNames = { 'en': 'English', 'ar': 'العربية', 'ku': 'کوردی' };

    indicator.textContent = langNames[detectedLang];
    indicator.classList.add('show');
    this.classList.add('processing');

    clearTimeout(translationTimeout);
    translationTimeout = setTimeout(() => {
        translateText(value, detectedLang, 'description');
    }, 1500);
});

// Translation Function
async function translateText(text, sourceLang, fieldType) {
    const inputId = fieldType === 'name' ? 'smartPropertyName' : 'smartDescription';
    const loader = document.querySelector(`#${inputId} + .translation-loader`);
    loader?.classList.add('show');

    try {
        const targetLangs = ['en', 'ar', 'ku'].filter(lang => lang !== sourceLang);

        // Set source language in hidden field
        document.getElementById(`${fieldType}_${sourceLang}`).value = text;

        // Translate to other languages
        for (const targetLang of targetLangs) {
            const response = await fetch('/v1/api/translate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    text: text,
                    source: sourceLang,
                    target: targetLang
                })
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success && result.data.translated_text) {
                    document.getElementById(`${fieldType}_${targetLang}`).value = result.data.translated_text;
                }
            } else {
                // Fallback: copy source text
                document.getElementById(`${fieldType}_${targetLang}`).value = text;
            }
        }

        // Sync to manual fields
        syncToManualFields(fieldType);

        loader?.classList.remove('show');
        document.getElementById(inputId).classList.remove('processing');

        console.log(`✓ ${fieldType} translated from ${sourceLang}`);
    } catch (error) {
        console.error('Translation error:', error);
        loader?.classList.remove('show');

        // Fallback: copy to all languages
        ['en', 'ar', 'ku'].forEach(lang => {
            document.getElementById(`${fieldType}_${lang}`).value = text;
        });
        syncToManualFields(fieldType);
    }
}

// Sync hidden fields to manual input fields
function syncToManualFields(fieldType) {
    ['en', 'ar', 'ku'].forEach(lang => {
        const hiddenValue = document.getElementById(`${fieldType}_${lang}`).value;
        const manualField = document.getElementById(`${fieldType}_${lang}_manual`);
        if (manualField && hiddenValue && !manualField.value) {
            manualField.value = hiddenValue;
        }
    });
}

// ============================================
// OWNER TYPE HANDLER (Admin Only)
// ============================================
@if(!isset($office) && !isset($agent))
document.getElementById('owner_type')?.addEventListener('change', async function() {
    const ownerSelect = document.getElementById('owner_id');
    const ownerType = this.value;

    ownerSelect.innerHTML = '<option value="">Loading...</option>';
    ownerSelect.disabled = true;

    if (!ownerType) {
        ownerSelect.innerHTML = '<option value="">Select owner type first</option>';
        return;
    }

    try {
        const endpoint = ownerType === 'Agent' ? '/admin/api/agents' : '/admin/api/offices';
        const response = await fetch(endpoint);
        const data = await response.json();

        let options = '<option value="">-- Select --</option>';
        data.forEach(item => {
            const name = ownerType === 'Agent' ? item.agent_name : item.company_name;
            options += `<option value="${item.id}">${name}</option>`;
        });

        ownerSelect.innerHTML = options;
        ownerSelect.disabled = false;
    } catch (error) {
        console.error('Error loading owners:', error);
        ownerSelect.innerHTML = '<option value="">Error loading options</option>';
    }
});
@endif

// ============================================
// MAP INITIALIZATION
// ============================================
function initMap() {
    const erbil = {lat: 36.1911, lng: 44.0091};
    map = new google.maps.Map(document.getElementById('map-preview'), {
        center: erbil,
        zoom: 12,
        styles: [{featureType: "poi", elementType: "labels", stylers: [{visibility: "off"}]}]
    });

    marker = new google.maps.Marker({
        position: erbil,
        map: map,
        draggable: true,
        animation: google.maps.Animation.DROP
    });

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

// ============================================
// LOCATION SYSTEM
// ============================================
document.addEventListener('DOMContentLoaded', async function() {
    await loadCities();

    // Map Toggle
    const mapToggle = document.getElementById('mapToggle');
    mapToggle?.addEventListener('change', function() {
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

    // City Selection
    document.getElementById('city-select')?.addEventListener('change', async function() {
        const cityId = this.value;
        const selectedOption = this.options[this.selectedIndex];

        document.getElementById('area-select').innerHTML = '<option value="">Select city first</option>';
        document.getElementById('area-select').disabled = true;

        if (!cityId) return;

        document.getElementById('city-en').value = selectedOption.dataset.nameEn || '';
        document.getElementById('city-ar').value = selectedOption.dataset.nameAr || '';
        document.getElementById('city-ku').value = selectedOption.dataset.nameKu || '';

        await loadAreas(cityId);

        if (selectedOption.dataset.lat && selectedOption.dataset.lng && mapToggle.checked) {
            const lat = parseFloat(selectedOption.dataset.lat);
            const lng = parseFloat(selectedOption.dataset.lng);
            if (marker && map) {
                const pos = { lat, lng };
                marker.setPosition(pos);
                map.panTo(pos);
                map.setZoom(13);
                updateLatLng(lat, lng);
            }
        }
    });

    // Area Selection
    document.getElementById('area-select')?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        if (!this.value) return;

        document.getElementById('district-en').value = selectedOption.dataset.nameEn || '';
        document.getElementById('district-ar').value = selectedOption.dataset.nameAr || '';
        document.getElementById('district-ku').value = selectedOption.dataset.nameKu || '';

        if (selectedOption.dataset.lat && selectedOption.dataset.lng && mapToggle.checked) {
            const lat = parseFloat(selectedOption.dataset.lat);
            const lng = parseFloat(selectedOption.dataset.lng);
            if (marker && map) {
                const pos = { lat, lng };
                marker.setPosition(pos);
                map.panTo(pos);
                map.setZoom(15);
                updateLatLng(lat, lng);
            }
        }
    });
});

async function loadCities() {
    try {
        const citySelect = document.getElementById('city-select');
        citySelect.innerHTML = '<option value="">Loading...</option>';

        const response = await fetch('/v1/api/location/branches', {
            headers: { 'Accept-Language': 'en', 'Accept': 'application/json' }
        });

        if (!response.ok) throw new Error('Failed to load cities');

        const result = await response.json();
        if (!result.success || !result.data) throw new Error('Invalid response');

        const cities = result.data;
        let options = '<option value="">-- Select City --</option>';
        cities.forEach(city => {
            options += `<option value="${city.id}"
                data-name-en="${city.city_name_en || ''}"
                data-name-ar="${city.city_name_ar || ''}"
                data-name-ku="${city.city_name_ku || ''}"
                data-lat="${city.coordinates?.lat || city.latitude || ''}"
                data-lng="${city.coordinates?.lng || city.longitude || ''}"
            >${city.city_name_en} - ${city.city_name_ar}</option>`;
        });

        citySelect.innerHTML = options;
        console.log(`✓ Loaded ${cities.length} cities`);
    } catch (error) {
        console.error('Error loading cities:', error);
        document.getElementById('city-select').innerHTML = '<option value="">Error loading cities</option>';
    }
}

async function loadAreas(cityId) {
    try {
        const areaSelect = document.getElementById('area-select');
        areaSelect.innerHTML = '<option value="">Loading...</option>';
        areaSelect.disabled = true;

        const response = await fetch(`/v1/api/location/branches/${cityId}/areas`, {
            headers: { 'Accept-Language': 'en', 'Accept': 'application/json' }
        });

        if (!response.ok) throw new Error('Failed to load areas');

        const result = await response.json();

        if (result.success && result.data) {
            let options = '<option value="">-- Select Area --</option>';
            result.data.forEach(area => {
                options += `<option value="${area.id}"
                    data-name-en="${area.area_name_en || ''}"
                    data-name-ar="${area.area_name_ar || ''}"
                    data-name-ku="${area.area_name_ku || ''}"
                    data-lat="${area.coordinates?.lat || area.latitude || ''}"
                    data-lng="${area.coordinates?.lng || area.longitude || ''}"
                >${area.area_name_en}</option>`;
            });

            areaSelect.innerHTML = options;
            areaSelect.disabled = false;
        }
    } catch (error) {
        console.error('Error loading areas:', error);
        document.getElementById('area-select').innerHTML = '<option value="">Error</option>';
    }
}

// ============================================
// IMAGE HANDLING
// ============================================
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
            alert(`Invalid file: ${file.name}`);
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
                <button type="button" class="drag-handle"><i class="fas fa-arrows-alt"></i></button>
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

// ============================================
// FORM VALIDATION & SUBMISSION
// ============================================
document.getElementById('propertyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const errors = [];

    // Check manual fields first, then hidden fields
    const nameEn = document.getElementById('name_en_manual').value || document.getElementById('name_en').value;
    const nameAr = document.getElementById('name_ar_manual').value || document.getElementById('name_ar').value;
    const nameKu = document.getElementById('name_ku_manual').value || document.getElementById('name_ku').value;

    const descEn = document.getElementById('description_en_manual').value || document.getElementById('description_en').value;
    const descAr = document.getElementById('description_ar_manual').value || document.getElementById('description_ar').value;
    const descKu = document.getElementById('description_ku_manual').value || document.getElementById('description_ku').value;

    if (!nameEn && !nameAr && !nameKu) {
        errors.push('Please enter property name in at least one language');
    }

    if (!descEn && !descAr && !descKu) {
        errors.push('Please enter property description in at least one language');
    }

    if (!document.getElementById('property_type').value) {
        errors.push('Please select property type');
    }

    if (!document.getElementById('area').value || parseFloat(document.getElementById('area').value) < 1) {
        errors.push('Area must be at least 1 m²');
    }

    if (!document.getElementById('price_usd').value || parseFloat(document.getElementById('price_usd').value) < 0) {
        errors.push('Please enter valid USD price');
    }

    if (!document.getElementById('price_iqd').value || parseFloat(document.getElementById('price_iqd').value) < 0) {
        errors.push('Please enter valid IQD price');
    }

    if (!document.getElementById('city-en').value) {
        errors.push('Please select city');
    }

    if (!document.getElementById('district-en').value) {
        errors.push('Please select district');
    }

    if (selectedImages.length === 0) {
        errors.push('Please upload at least 1 image');
        document.getElementById('uploadArea').classList.add('error');
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

console.log('✓ Smart property form initialized');
</script>
@endpush
