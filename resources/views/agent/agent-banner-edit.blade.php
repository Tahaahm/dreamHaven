@extends('layouts.agent-layout')

@section('title', 'Edit Banner - Dream Mulk')

@section('styles')
<style>
    /* Same clean styles as your Edit page */
    .page-title { font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px; }
    .form-card { background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 32px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .form-section { margin-bottom: 32px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px; }
    .section-title { font-size: 18px; font-weight: 600; color: #1a202c; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #303b97; display: flex; align-items: center; gap: 10px; }
    .section-title i { color: #303b97; }

    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 20px; }
    .form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
    .form-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
    .form-input, .form-select, .form-textarea { padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 15px; color: #1a202c; background: white; transition: all 0.3s; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #303b97; box-shadow: 0 0 0 4px rgba(48,59,151,0.1); }
    .form-textarea { min-height: 120px; resize: vertical; }

    .form-input:disabled { background: #f1f5f9; color: #64748b; cursor: not-allowed; border-color: #d1d5db; }

    .lang-badge { font-size: 10px; background: #303b97; color: white; padding: 2px 6px; border-radius: 4px; margin-left: 8px; text-transform: uppercase; vertical-align: middle; }

    .current-image-wrapper { margin-bottom: 24px; padding: 16px; background: #f8fafc; border-radius: 12px; border: 1px solid #e5e7eb; text-align: center; }
    .current-image-wrapper img { max-width: 100%; max-height: 150px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }

    .image-upload { border: 2px dashed #303b97; border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; background: #f8fafc; }
    .image-upload:hover { background: #f1f5f9; border-color: #1e2875; }
    .image-upload input { display: none; }
    .image-preview { max-width: 100%; max-height: 300px; margin-top: 20px; border-radius: 8px; display: none; border: 2px solid #303b97; }

    .checkbox-group { display: flex; align-items: center; gap: 10px; margin-top: 12px; }
    .checkbox-group input { width: 20px; height: 20px; cursor: pointer; accent-color: #303b97; }

    .form-actions { display: flex; gap: 16px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb; }
    .btn { padding: 12px 32px; border-radius: 10px; font-size: 15px; font-weight: 700; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; }
    .btn-primary { background: linear-gradient(135deg, #303b97, #1e2875); color: white; box-shadow: 0 4px 12px rgba(48,59,151,0.3); }
    .btn-secondary { background: white; color: #64748b; border: 2px solid #e5e7eb; }

    .helper-text { font-size: 13px; color: #64748b; margin-top: 6px; }
    .auto-calculated { font-size: 12px; color: #10b981; margin-top: 6px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .auto-calculated i { color: #10b981; }
</style>
@endsection

@section('content')
<h1 class="page-title"><i class="fas fa-edit"></i> Edit Banner Ad</h1>

@php
    // --- 1. AGGRESSIVE JSON DECODER ---
    // This loop peels back layers of JSON strings until it hits a real array.
    function cleanDecode($data) {
        if (is_array($data)) return $data;

        $decoded = $data;
        // Try decoding up to 3 times to handle double/triple stringified data
        for ($i = 0; $i < 3; $i++) {
            if (!is_string($decoded)) break;
            $attempt = json_decode($decoded, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $decoded = $attempt;
            } else {
                break;
            }
        }

        // Ensure we always return a valid array structure
        return is_array($decoded) ? $decoded : ['en' => (string)$decoded];
    }

    $titles = cleanDecode($banner->title);
    $descriptions = cleanDecode($banner->description);
    $ctas = cleanDecode($banner->call_to_action);

    // --- 2. DATE FORMATTER ---
    // Force date format to Y-m-d so the HTML input accepts it correctly
    $startDateVal = $banner->start_date instanceof \DateTime
        ? $banner->start_date->format('Y-m-d')
        : date('Y-m-d', strtotime($banner->start_date));

    $endDateVal = '';
    if($banner->end_date) {
        $endDateVal = $banner->end_date instanceof \DateTime
            ? $banner->end_date->format('Y-m-d')
            : date('Y-m-d', strtotime($banner->end_date));
    }
@endphp

@if($errors->any())
<div style="background: #fee2e2; border: 2px solid #ef4444; color: #dc2626; padding: 16px; border-radius: 10px; margin-bottom: 24px;">
    <strong><i class="fas fa-exclamation-circle"></i> Please fix the following errors:</strong>
    <ul style="margin: 8px 0 0 20px;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('agent.banner.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-card">
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-heading"></i> Banner Titles</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Title (English) * <span class="lang-badge">EN</span></label>
                    <input type="text" name="title_en" class="form-input" required
                           value="{{ old('title_en', $titles['en'] ?? '') }}"
                           placeholder="English Title">
                </div>
                <div class="form-group">
                    <label class="form-label">Title (Arabic) <span class="lang-badge">AR</span></label>
                    <input type="text" name="title_ar" class="form-input"
                           value="{{ old('title_ar', $titles['ar'] ?? '') }}"
                           dir="rtl" placeholder="العنوان">
                </div>
                <div class="form-group">
                    <label class="form-label">Title (Kurdish) <span class="lang-badge">KU</span></label>
                    <input type="text" name="title_ku" class="form-input"
                           value="{{ old('title_ku', $titles['ku'] ?? '') }}"
                           dir="rtl" placeholder="ناونیشان">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-align-left"></i> Descriptions</h3>
            <div class="form-group">
                <label class="form-label">Description (English) <span class="lang-badge">EN</span></label>
                <textarea name="description_en" class="form-textarea">{{ old('description_en', $descriptions['en'] ?? '') }}</textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Description (Arabic) <span class="lang-badge">AR</span></label>
                    <textarea name="description_ar" class="form-textarea" dir="rtl">{{ old('description_ar', $descriptions['ar'] ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Kurdish) <span class="lang-badge">KU</span></label>
                    <textarea name="description_ku" class="form-textarea" dir="rtl">{{ old('description_ku', $descriptions['ku'] ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-image"></i> Banner Image</h3>
            <div class="current-image-wrapper">
                <p class="form-label">Current Active Image:</p>
                <img src="{{ $banner->image_url }}" alt="Banner">
            </div>
            <div class="image-upload" onclick="document.getElementById('banner-image').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: #303b97; margin-bottom: 12px;"></i>
                <p style="font-weight: 600;">Click to upload a new image (Optional)</p>
                <input type="file" id="banner-image" name="image" accept="image/*" onchange="previewImage(this)">
                <img id="image-preview" class="image-preview">
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-cog"></i> Schedule & Display</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Banner Type</label>
                    <select name="banner_type" class="form-select">
                        <option value="property_listing" {{ $banner->banner_type == 'property_listing' ? 'selected' : '' }}>Property Listing</option>
                        <option value="agency_branding" {{ $banner->banner_type == 'agency_branding' ? 'selected' : '' }}>Agency Branding</option>
                        <option value="service_promotion" {{ $banner->banner_type == 'service_promotion' ? 'selected' : '' }}>Service Promotion</option>
                        <option value="general_marketing" {{ $banner->banner_type == 'general_marketing' ? 'selected' : '' }}>General Marketing</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <select name="position" class="form-select">
                        <option value="header" {{ $banner->position == 'header' ? 'selected' : '' }}>Header</option>
                        <option value="sidebar_top" {{ $banner->position == 'sidebar_top' ? 'selected' : '' }}>Sidebar Top</option>
                        <option value="content_top" {{ $banner->position == 'content_top' ? 'selected' : '' }}>Content Top</option>
                        <option value="footer" {{ $banner->position == 'footer' ? 'selected' : '' }}>Footer</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Link URL</label>
                    <input type="url" name="link_url" class="form-input" value="{{ old('link_url', $banner->link_url) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Call to Action (English)</label>
                    <input type="text" name="call_to_action" class="form-input" value="{{ old('call_to_action', $ctas['en'] ?? '') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" id="start-date" class="form-input" required
                           value="{{ old('start_date', $startDateVal) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date (Auto-calculated) <i class="fas fa-lock" style="color: #64748b; font-size: 12px;"></i></label>
                    <input type="date" name="end_date" id="end-date" class="form-input" readonly disabled
                           value="{{ old('end_date', $endDateVal) }}">
                    <div class="auto-calculated">
                        <i class="fas fa-info-circle"></i>
                        <span>Automatically set to 30 days after start date</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('agent.banners') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
    </div>
</form>

<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Auto-calculate end date (30 days after start date)
document.getElementById('start-date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    if (this.value) {
        // Add 30 days
        startDate.setDate(startDate.getDate() + 30);

        // Format as YYYY-MM-DD
        const year = startDate.getFullYear();
        const month = String(startDate.getMonth() + 1).padStart(2, '0');
        const day = String(startDate.getDate()).padStart(2, '0');
        const endDateFormatted = `${year}-${month}-${day}`;

        // Set the end date
        const endDateInput = document.getElementById('end-date');
        endDateInput.value = endDateFormatted;
        endDateInput.disabled = false; // Enable briefly to set value
        setTimeout(() => {
            endDateInput.disabled = true; // Re-disable after setting
        }, 10);
    }
});

// Trigger the calculation on page load if there's a start date value
window.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start-date');
    if (startDateInput.value) {
        startDateInput.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
