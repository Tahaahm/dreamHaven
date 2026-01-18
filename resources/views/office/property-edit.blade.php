@extends('layouts.office-layout')

@section('title', 'Edit Property - Dream Mulk')

@section('styles')
<style>
    /* ... (Keeping your existing Edit Page Styles exactly as they were) ... */
    :root {
        --primary: #303b97;
        --primary-dark: #252e7a;
        --primary-light: #4a56c4;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    .page-container {
        max-width: 1600px;
        margin: 0 auto;
        padding: 24px;
    }

    /* Animated Header */
    .property-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 24px;
        padding: 40px;
        margin-bottom: 32px;
        color: white;
        position: sticky;
        top: 20px;
        z-index: 100;
        box-shadow: 0 20px 60px rgba(48, 59, 151, 0.3);
        animation: fadeInDown 0.6s ease;
    }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 32px;
        align-items: center;
    }

    .property-info h1 {
        font-size: 32px;
        font-weight: 900;
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }

    .property-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        font-size: 14px;
        opacity: 0.95;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.15);
        padding: 8px 16px;
        border-radius: 12px;
        backdrop-filter: blur(10px);
    }

    .meta-item i {
        font-size: 16px;
    }

    .header-actions {
        display: flex;
        gap: 12px;
    }

    .btn {
        padding: 14px 28px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 15px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .btn:hover::before {
        width: 300px;
        height: 300px;
    }

    .btn-white {
        background: white;
        color: var(--primary);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .btn-white:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(255,255,255,0.4);
    }

    .btn-danger {
        background: var(--danger);
        color: white;
        box-shadow: 0 4px 12px rgba(239,68,68,0.3);
    }

    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(239,68,68,0.5);
    }

    .btn-primary {
        background: var(--success);
        color: white;
        box-shadow: 0 4px 12px rgba(16,185,129,0.3);
    }

    .btn-primary:hover {
        background: #059669;
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(16,185,129,0.5);
    }

    /* Main Grid */
    .main-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 32px;
        margin-bottom: 32px;
    }

    /* Sidebar */
    .sidebar {
        position: sticky;
        top: 160px;
        height: fit-content;
        animation: fadeInLeft 0.6s ease;
    }

    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-30px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .preview-card {
        background: white;
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 8px 32px rgba(48, 59, 151, 0.12);
        border: 1px solid var(--gray-200);
    }

    .preview-image {
        width: 100%;
        height: 260px;
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 20px;
        position: relative;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }

    .preview-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .preview-image:hover img {
        transform: scale(1.05);
    }

    .preview-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 800;
        backdrop-filter: blur(20px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-available { background: rgba(16,185,129,0.95); color: white; }
    .badge-sold { background: rgba(239,68,68,0.95); color: white; }
    .badge-rented { background: rgba(48,59,151,0.95); color: white; }

    .preview-thumbnails {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 24px;
    }

    .thumbnail {
        width: 100%;
        height: 70px;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        border: 3px solid transparent;
        transition: all 0.3s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .thumbnail:hover {
        border-color: var(--primary);
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(48,59,151,0.3);
    }

    .thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    .stat-item {
        background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
        padding: 20px;
        border-radius: 16px;
        text-align: center;
        border: 2px solid var(--gray-100);
        transition: all 0.3s;
    }

    .stat-item:hover {
        border-color: var(--primary);
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(48,59,151,0.15);
    }

    .stat-icon {
        font-size: 24px;
        color: var(--primary);
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 900;
        color: var(--gray-900);
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 12px;
        color: var(--gray-600);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Form Container */
    .form-container {
        background: white;
        border-radius: 24px;
        box-shadow: 0 8px 32px rgba(48, 59, 151, 0.12);
        border: 1px solid var(--gray-200);
        animation: fadeInRight 0.6s ease;
    }

    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(30px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .form-section {
        padding: 40px;
        border-bottom: 2px solid var(--gray-100);
    }

    .form-section:last-child {
        border-bottom: none;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 32px;
    }

    .section-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        box-shadow: 0 8px 20px rgba(48,59,151,0.3);
    }

    .section-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--gray-900);
        letter-spacing: -0.5px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    .form-group {
        position: relative;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: var(--gray-700);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .required {
        color: var(--danger);
        margin-left: 4px;
    }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 16px 20px;
        background: var(--gray-50);
        border: 2px solid var(--gray-200);
        border-radius: 14px;
        color: var(--gray-900);
        font-size: 15px;
        font-family: inherit;
        font-weight: 500;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(48,59,151,0.1), 0 4px 12px rgba(48,59,151,0.15);
        transform: translateY(-2px);
    }

    .form-textarea {
        min-height: 140px;
        resize: vertical;
    }

    .form-input.error, .form-select.error, .form-textarea.error {
        border-color: var(--danger);
        background: #fef2f2;
    }

    .error-message {
        display: block;
        color: var(--danger);
        font-size: 13px;
        margin-top: 8px;
        font-weight: 600;
    }

    /* Features */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .feature-box {
        background: var(--gray-50);
        border: 2px solid var(--gray-200);
        border-radius: 14px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .feature-box:hover {
        border-color: var(--primary);
        background: rgba(48,59,151,0.05);
        transform: translateY(-2px);
    }

    .feature-box input[type="checkbox"]:checked + label {
        color: var(--primary);
        font-weight: 700;
    }

    .feature-box input[type="checkbox"] {
        width: 22px;
        height: 22px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .feature-box label {
        font-size: 15px;
        font-weight: 600;
        color: var(--gray-700);
        cursor: pointer;
        margin: 0;
    }

    /* Images */
    .images-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .image-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 14px;
        overflow: hidden;
        border: 2px solid var(--gray-200);
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .image-item:hover {
        border-color: var(--primary);
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(48,59,151,0.2);
    }

    .image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .remove-img-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 36px;
        height: 36px;
        background: rgba(239,68,68,0.95);
        border: none;
        border-radius: 10px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        opacity: 0;
        font-size: 16px;
    }

    .image-item:hover .remove-img-btn {
        opacity: 1;
    }

    .remove-img-btn:hover {
        background: #dc2626;
        transform: scale(1.15);
    }

    .upload-zone {
        border: 3px dashed var(--gray-300);
        border-radius: 20px;
        padding: 56px;
        text-align: center;
        background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
        cursor: pointer;
        transition: all 0.3s;
    }

    .upload-zone:hover {
        border-color: var(--primary);
        background: linear-gradient(135deg, rgba(48,59,151,0.05) 0%, rgba(48,59,151,0.02) 100%);
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(48,59,151,0.15);
    }

    .upload-icon {
        font-size: 56px;
        color: var(--primary);
        margin-bottom: 20px;
        opacity: 0.7;
    }

    .upload-text {
        font-size: 18px;
        font-weight: 800;
        color: var(--gray-900);
        margin-bottom: 10px;
    }

    .upload-hint {
        font-size: 14px;
        color: var(--gray-600);
        font-weight: 500;
    }

    /* Alerts */
    .alert {
        position: fixed;
        top: 24px;
        right: 24px;
        min-width: 420px;
        max-width: 520px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        border: 2px solid transparent;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(400px) scale(0.9); }
        to { opacity: 1; transform: translateX(0) scale(1); }
    }

    @keyframes slideOut {
        from { opacity: 1; transform: translateX(0) scale(1); }
        to { opacity: 0; transform: translateX(400px) scale(0.9); }
    }

    .alert-success { border-color: var(--success); }
    .alert-error { border-color: var(--danger); }

    .alert-content {
        display: flex;
        align-items: flex-start;
        gap: 18px;
        padding: 28px;
    }

    .alert-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
    }

    .alert-success .alert-icon {
        background: linear-gradient(135deg, var(--success), #34d399);
        color: white;
        box-shadow: 0 8px 20px rgba(16,185,129,0.4);
    }

    .alert-error .alert-icon {
        background: linear-gradient(135deg, var(--danger), #f87171);
        color: white;
        box-shadow: 0 8px 20px rgba(239,68,68,0.4);
    }

    .alert-text {
        flex: 1;
    }

    .alert-title {
        font-size: 19px;
        font-weight: 800;
        color: var(--gray-900);
        margin-bottom: 6px;
    }

    .alert-message {
        font-size: 14px;
        color: var(--gray-700);
        line-height: 1.6;
        font-weight: 500;
    }

    .alert-close {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        background: var(--gray-100);
        color: var(--gray-600);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-size: 16px;
    }

    .alert-close:hover {
        background: var(--gray-200);
        transform: scale(1.1);
    }

    .alert-progress {
        height: 5px;
        width: 100%;
        background: rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }

    .alert-progress::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        animation: progress 5s linear forwards;
    }

    .alert-success .alert-progress::after {
        background: linear-gradient(90deg, var(--success), #34d399);
    }

    .alert-error .alert-progress::after {
        background: linear-gradient(90deg, var(--danger), #f87171);
    }

    @keyframes progress {
        from { transform: translateX(-100%); }
        to { transform: translateX(0); }
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 16px;
        justify-content: space-between;
        padding: 32px 40px;
        background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
        border-radius: 0 0 24px 24px;
    }

    /* Responsive */
    @media (max-width: 1400px) {
        .main-grid {
            grid-template-columns: 320px 1fr;
        }
    }

    @media (max-width: 1200px) {
        .main-grid {
            grid-template-columns: 1fr;
        }

        .sidebar {
            position: relative;
            top: 0;
        }
    }

    @media (max-width: 768px) {
        .form-grid, .features-grid, .images-grid {
            grid-template-columns: 1fr;
        }

        .header-grid {
            grid-template-columns: 1fr;
        }

        .alert {
            min-width: auto;
            max-width: calc(100% - 32px);
            left: 16px;
            right: 16px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="page-container">
    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success" id="successAlert">
        <div class="alert-content">
            <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
            <div class="alert-text">
                <div class="alert-title">Success!</div>
                <div class="alert-message">{{ session('success') }}</div>
            </div>
            <button class="alert-close" onclick="closeAlert('successAlert')"><i class="fas fa-times"></i></button>
        </div>
        <div class="alert-progress"></div>
    </div>
    @endif

    @if(session('error') || $errors->any())
    <div class="alert alert-error" id="errorAlert">
        <div class="alert-content">
            <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="alert-text">
                <div class="alert-title">Error!</div>
                <div class="alert-message">
                    @if(session('error')){{ session('error') }}@endif
                    @if($errors->any())
                        <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <button class="alert-close" onclick="closeAlert('errorAlert')"><i class="fas fa-times"></i></button>
        </div>
    </div>
    @endif

    @php
        $name = is_array($property->name) ? $property->name : json_decode($property->name, true);
        $description = is_array($property->description) ? $property->description : json_decode($property->description, true);
        $type = is_array($property->type) ? $property->type : json_decode($property->type, true);
        $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
        $rooms = is_array($property->rooms) ? $property->rooms : json_decode($property->rooms, true);
        $address = is_array($property->address_details) ? $property->address_details : json_decode($property->address_details, true);
        $locations = is_array($property->locations) ? $property->locations : json_decode($property->locations, true);
        $images = is_array($property->images) ? $property->images : json_decode($property->images, true);
    @endphp

    {{-- Header --}}
    <div class="property-header">
        <div class="header-grid">
            <div class="property-info">
                <h1>{{ $name['en'] ?? 'Property' }}</h1>
                <div class="property-meta">
                    <div class="meta-item">
                        <i class="fas fa-fingerprint"></i>
                        <span>{{ $property->id }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>{{ number_format($price['usd'] ?? 0) }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>{{ $property->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span>{{ ucfirst($property->status) }}</span>
                    </div>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('office.properties') }}" class="btn btn-white">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="main-grid">
        {{-- Sidebar --}}
        <div class="sidebar">
            <div class="preview-card">
                <div class="preview-image">
                    <img src="{{ is_array($images) && count($images) > 0 ? $images[0] : 'https://via.placeholder.com/380x260' }}" id="mainPreview" alt="Property">
                    <div class="preview-badge badge-{{ $property->status }}">{{ ucfirst($property->status) }}</div>
                </div>

                @if(is_array($images) && count($images) > 0)
                <div class="preview-thumbnails">
                    @foreach(array_slice($images, 0, 4) as $img)
                    <div class="thumbnail" onclick="document.getElementById('mainPreview').src='{{ $img }}'">
                        <img src="{{ $img }}" alt="Thumb">
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-eye"></i></div>
                        <div class="stat-value">{{ number_format($property->views ?? 0) }}</div>
                        <div class="stat-label">Views</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-heart"></i></div>
                        <div class="stat-value">{{ number_format($property->favorites_count ?? 0) }}</div>
                        <div class="stat-label">Favorites</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-star"></i></div>
                        <div class="stat-value">{{ number_format($property->rating ?? 0, 1) }}</div>
                        <div class="stat-label">Rating</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-home"></i></div>
                        <div class="stat-value">{{ ucfirst($type['category'] ?? 'N/A') }}</div>
                        <div class="stat-label">Type</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Form --}}
        <form action="{{ route('office.property.update', $property->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-container">
                {{-- Basic Info --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-info-circle"></i></div>
                        <div class="section-title">Basic Information</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Property Name (EN)<span class="required">*</span></label>
                            <input type="text" name="name_en" class="form-input @error('name_en') error @enderror" value="{{ old('name_en', $name['en'] ?? '') }}" required>
                            @error('name_en')<span class="error-message">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Name (AR)</label>
                            <input type="text" name="name_ar" class="form-input" value="{{ old('name_ar', $name['ar'] ?? '') }}" dir="rtl">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Name (KU)</label>
                            <input type="text" name="name_ku" class="form-input" value="{{ old('name_ku', $name['ku'] ?? '') }}">
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Description (EN)<span class="required">*</span></label>
                            <textarea name="description_en" class="form-textarea @error('description_en') error @enderror" required>{{ old('description_en', $description['en'] ?? '') }}</textarea>
                            @error('description_en')<span class="error-message">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description (AR)</label>
                            <textarea name="description_ar" class="form-textarea" dir="rtl">{{ old('description_ar', $description['ar'] ?? '') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description (KU)</label>
                            <textarea name="description_ku" class="form-textarea">{{ old('description_ku', $description['ku'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Property Details --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-home"></i></div>
                        <div class="section-title">Property Details</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Property Type<span class="required">*</span></label>
                            <select name="property_type" class="form-select" required>
                                <option value="apartment" {{ old('property_type', $type['category'] ?? '') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                                <option value="house" {{ old('property_type', $type['category'] ?? '') == 'house' ? 'selected' : '' }}>House</option>
                                <option value="villa" {{ old('property_type', $type['category'] ?? '') == 'villa' ? 'selected' : '' }}>Villa</option>
                                <option value="land" {{ old('property_type', $type['category'] ?? '') == 'land' ? 'selected' : '' }}>Land</option>
                                <option value="commercial" {{ old('property_type', $type['category'] ?? '') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                                <option value="office" {{ old('property_type', $type['category'] ?? '') == 'office' ? 'selected' : '' }}>Office</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Listing Type<span class="required">*</span></label>
                            <select name="listing_type" class="form-select" required>
                                <option value="sell" {{ old('listing_type', $property->listing_type) == 'sell' ? 'selected' : '' }}>For Sale</option>
                                <option value="rent" {{ old('listing_type', $property->listing_type) == 'rent' ? 'selected' : '' }}>For Rent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status<span class="required">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="available" {{ old('status', $property->status) == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="sold" {{ old('status', $property->status) == 'sold' ? 'selected' : '' }}>Sold</option>
                                <option value="rented" {{ old('status', $property->status) == 'rented' ? 'selected' : '' }}>Rented</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Area (m²)<span class="required">*</span></label>
                            <input type="number" name="area" class="form-input" value="{{ old('area', $property->area) }}" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bedrooms<span class="required">*</span></label>
                            <input type="number" name="bedrooms" class="form-input" value="{{ old('bedrooms', $rooms['bedroom']['count'] ?? 0) }}" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bathrooms<span class="required">*</span></label>
                            <input type="number" name="bathrooms" class="form-input" value="{{ old('bathrooms', $rooms['bathroom']['count'] ?? 0) }}" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Floor Number</label>
                            <input type="number" name="floor_number" class="form-input" value="{{ old('floor_number', $property->floor_number) }}" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Year Built</label>
                            <input type="number" name="year_built" class="form-input" value="{{ old('year_built', $property->year_built) }}" min="1900" max="2030">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Price (USD)<span class="required">*</span></label>
                            <input type="number" name="price_usd" class="form-input" value="{{ old('price_usd', $price['usd'] ?? 0) }}" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Price (IQD)<span class="required">*</span></label>
                            <input type="number" name="price_iqd" class="form-input" value="{{ old('price_iqd', $price['iqd'] ?? 0) }}" step="0.01" required>
                        </div>
                    </div>
                </div>

                {{-- Location --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="section-title">Location</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">City<span class="required">*</span></label>
                            <select id="city-select" class="form-select" required>
                                <option value="">Loading cities...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">District<span class="required">*</span></label>
                            <select id="area-select" class="form-select" required disabled>
                                <option value="">Select City First</option>
                            </select>
                        </div>
                        <input type="hidden" name="city_en" id="city" value="{{ old('city_en', $address['city']['en'] ?? '') }}">
                        <input type="hidden" name="district_en" id="district" value="{{ old('district_en', $address['district']['en'] ?? '') }}">
                        <div class="form-group">
                            <label class="form-label">City (AR)</label>
                            <input type="text" name="city_ar" id="city_ar" class="form-input" value="{{ old('city_ar', $address['city']['ar'] ?? '') }}" dir="rtl" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">District (AR)</label>
                            <input type="text" name="district_ar" id="district_ar" class="form-input" value="{{ old('district_ar', $address['district']['ar'] ?? '') }}" dir="rtl" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">City (KU)</label>
                            <input type="text" name="city_ku" id="city_ku" class="form-input" value="{{ old('city_ku', $address['city']['ku'] ?? '') }}" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">District (KU)</label>
                            <input type="text" name="district_ku" id="district_ku" class="form-input" value="{{ old('district_ku', $address['district']['ku'] ?? '') }}" readonly>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Full Address</label>
                            <input type="text" name="address" class="form-input" value="{{ old('address', $property->address) }}">
                        </div>
                        <div class="form-group">
    <label class="form-label">Latitude<span class="required">*</span></label>
    <input type="number" name="latitude" id="latitude" class="form-input"
           value="{{ old('latitude', $locations[0]['lat'] ?? 36.1911) }}"
           step="any" required>
</div>
                        <div class="form-group">
    <label class="form-label">Longitude<span class="required">*</span></label>
    <input type="number" name="longitude" id="longitude" class="form-input"
           value="{{ old('longitude', $locations[0]['lng'] ?? 44.0094) }}"
           step="any" required>
</div>
                        <div class="form-group full-width">
                            <label class="form-label">Map Location</label>
                            <div id="map-preview" style="height: 400px; width: 100%; border-radius: 14px; border: 2px solid var(--gray-200); margin-top: 5px;"></div>
                        </div>
                    </div>
                </div>

                {{-- Features --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-star"></i></div>
                        <div class="section-title">Features & Utilities</div>
                    </div>
                    <div class="features-grid">
                        <div class="feature-box">
                            <input type="checkbox" name="furnished" id="furnished" value="1" {{ old('furnished', $property->furnished) ? 'checked' : '' }}>
                            <label for="furnished">Furnished</label>
                        </div>
                        <div class="feature-box">
                            <input type="checkbox" name="electricity" id="electricity" value="1" {{ old('electricity', $property->electricity) ? 'checked' : '' }}>
                            <label for="electricity">Electricity</label>
                        </div>
                        <div class="feature-box">
                            <input type="checkbox" name="water" id="water" value="1" {{ old('water', $property->water) ? 'checked' : '' }}>
                            <label for="water">Water</label>
                        </div>
                        <div class="feature-box">
                            <input type="checkbox" name="internet" id="internet" value="1" {{ old('internet', $property->internet) ? 'checked' : '' }}>
                            <label for="internet">Internet</label>
                        </div>
                    </div>
                </div>

                {{-- Images --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon"><i class="fas fa-images"></i></div>
                        <div class="section-title">Property Images</div>
                    </div>

                    @if(is_array($images) && count($images) > 0)
                    <div style="margin-bottom: 32px;">
                        <label class="form-label">Current Images</label>
                        <div class="images-grid" id="existingImages">
                            @foreach($images as $idx => $img)
                            <div class="image-item" id="img-{{ $idx }}">
                                <img src="{{ $img }}">
                                <button type="button" class="remove-img-btn" onclick="removeImage({{ $idx }})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <label class="form-label">Add New Images</label>
                    <div class="upload-zone" onclick="document.getElementById('newImages').click()">
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="upload-text">Click to upload images</div>
                        <div class="upload-hint">JPG, PNG, GIF (Max 5MB each, up to 10 images)</div>
                        <input type="file" id="newImages" name="images[]" multiple accept="image/*" style="display:none" onchange="previewNew(event)">
                    </div>
                    <div id="newPreview" class="images-grid" style="margin-top: 24px; display: none;"></div>
                    <input type="hidden" name="remove_images" id="removeImages" value="">
                </div>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <button type="button" class="btn btn-danger" onclick="if(confirm('Are you sure you want to delete this property? This action cannot be undone.')) document.getElementById('delForm').submit()">
                    <i class="fas fa-trash"></i> Delete Property
                </button>
                <div style="display: flex; gap: 12px;">
                    <a href="{{ route('office.properties') }}" class="btn btn-white" style="background: var(--gray-100); color: var(--gray-700);">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>

    <form id="delForm" action="{{ route('office.property.delete', $property->id) }}" method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>
<script src="{{ asset('js/location-selector.js') }}"></script>
<script>
let removed = [];
let newFiles = [];
let locationSelector;
let map = null;
let marker = null;

// Initialize Google Map
function initMap() {
    // Get initial coordinates from inputs (from DB/Blade) or default to Erbil
    let latVal = parseFloat(document.getElementById('latitude').value);
    let lngVal = parseFloat(document.getElementById('longitude').value);

    // Fallback if NaN
    if (isNaN(latVal) || isNaN(lngVal)) {
        latVal = 36.1901;
        lngVal = 44.0091;
    }

    const position = { lat: latVal, lng: lngVal };
    const mapContainer = document.getElementById('map-preview');

    if (mapContainer) {
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
    }
}

// Initialize LocationSelector and Page scripts
document.addEventListener('DOMContentLoaded', async function() {
    // Init LocationSelector
    locationSelector = new LocationSelector({
        citySelectId: 'city-select',
        areaSelectId: 'area-select',
        cityInputId: 'city',
        districtInputId: 'district',
        onCityChange: (city) => {
            document.getElementById('city_ar').value = city.nameAr || '';
            document.getElementById('city_ku').value = city.nameKu || '';

            // If selecting a new city, center map on it
            if(map && city.id && city.lat && city.lng) {
                const newPos = { lat: parseFloat(city.lat), lng: parseFloat(city.lng) };
                map.setCenter(newPos);
                marker.setPosition(newPos);
                document.getElementById('latitude').value = newPos.lat;
                document.getElementById('longitude').value = newPos.lng;
            }
        },
        onAreaChange: (area) => {
            document.getElementById('district_ar').value = area.nameAr || '';
            document.getElementById('district_ku').value = area.nameKu || '';

            // If selecting a new area, center map on it
            if(map && area.id && area.lat && area.lng) {
                const newPos = { lat: parseFloat(area.lat), lng: parseFloat(area.lng) };
                map.setCenter(newPos);
                marker.setPosition(newPos);
                document.getElementById('latitude').value = newPos.lat;
                document.getElementById('longitude').value = newPos.lng;
            }
        }
    });

    try {
        await locationSelector.init();

        // Set initial values from Blade
        const initialCity = "{{ $address['city']['en'] ?? '' }}";
        const initialDistrict = "{{ $address['district']['en'] ?? '' }}";

        if (initialCity) {
            // Note: Use setCityByName if available in your class, or match manually
            await locationSelector.setCityByName(initialCity);

            if (initialDistrict) {
                setTimeout(() => {
                    locationSelector.setAreaByName(initialDistrict);
                }, 500);
            }
        }
    } catch (error) {
        console.error('Failed to initialize location selector:', error);
    }

    // Alert auto-close
    if (document.getElementById('successAlert')) setTimeout(() => closeAlert('successAlert'), 5000);
    if (document.getElementById('errorAlert')) setTimeout(() => closeAlert('errorAlert'), 8000);
});

function removeImage(idx) {
    removed.push(idx);
    document.getElementById('removeImages').value = JSON.stringify(removed);
    const el = document.getElementById('img-' + idx);
    el.style.transform = 'scale(0)';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 300);
}

function previewNew(e) {
    newFiles = Array.from(e.target.files);
    const grid = document.getElementById('newPreview');
    grid.innerHTML = '';
    grid.style.display = 'grid';

    newFiles.forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = function(ev) {
            const div = document.createElement('div');
            div.className = 'image-item';
            div.style.animation = 'fadeInUp 0.4s ease';
            div.innerHTML = `<img src="${ev.target.result}"><button type="button" class="remove-img-btn" onclick="removeNew(${i})"><i class="fas fa-times"></i></button>`;
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeNew(idx) {
    newFiles.splice(idx, 1);
    const dt = new DataTransfer();
    newFiles.forEach(f => dt.items.add(f));
    document.getElementById('newImages').files = dt.files;
    previewNew({ target: { files: newFiles } });
}

function closeAlert(id) {
    const el = document.getElementById(id);
    if (el) {
        el.style.animation = 'slideOut 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
        setTimeout(() => el.remove(), 500);
    }
}
</script>
@endsection
