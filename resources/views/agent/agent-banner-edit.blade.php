@extends('layouts.agent-layout')

@section('title', 'Edit Banner - Dream Mulk')

@section('styles')
<style>
    .page-title { font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px; }
    .form-card { background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 32px; margin-bottom: 24px; }
    .form-section { margin-bottom: 32px; }
    .section-title { font-size: 18px; font-weight: 600; color: #1a202c; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e5e7eb; }

    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
    .form-input, .form-select, .form-textarea { padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 15px; color: #1a202c; background: white; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #303b97; }
    .form-textarea { min-height: 100px; resize: vertical; }

    .image-upload { border: 2px dashed #e5e7eb; border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; }
    .image-upload:hover { border-color: #303b97; background: #f9fafb; }
    .image-upload input { display: none; }
    .image-preview { max-width: 100%; max-height: 300px; margin-top: 20px; border-radius: 8px; }

    .current-image { margin-bottom: 20px; }
    .current-image img { max-width: 100%; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb; }
    .current-image p { margin-top: 10px; font-size: 13px; color: #64748b; }

    .checkbox-group { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
    .checkbox-group input { width: 18px; height: 18px; cursor: pointer; }

    .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; }
    .btn { padding: 12px 32px; border-radius: 8px; font-size: 15px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary { background: #303b97; color: white; }
    .btn-primary:hover { background: #1e2875; }
    .btn-secondary { background: white; color: #64748b; border: 1px solid #e5e7eb; }

    .helper-text { font-size: 13px; color: #64748b; margin-top: 6px; }

    .alert { padding: 16px; border-radius: 10px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
    .alert-warning { background: #fef3c7; border: 2px solid #d97706; color: #92400e; }
</style>
@endsection

@section('content')
<h1 class="page-title">Edit Banner</h1>

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

@if($banner->status === 'active')
<div class="alert alert-warning">
    <i class="fas fa-info-circle"></i>
    <span>This banner is currently active. Changes will be reflected immediately after update.</span>
</div>
@endif

<form action="{{ route('agent.banner.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-card">
        <div class="form-section">
            <h3 class="section-title">Basic Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Banner Title *</label>
                    <input type="text" name="title" class="form-input" required
                           value="{{ old('title', is_array($banner->title) ? $banner->title['en'] : (is_string($banner->title) ? json_decode($banner->title, true)['en'] ?? $banner->title : '')) }}"
                           placeholder="Enter banner title" minlength="5" maxlength="255">
                    <span class="helper-text">Minimum 5 characters</span>
                    @error('title')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Banner Type *</label>
                    <select name="banner_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="property_listing" {{ old('banner_type', $banner->banner_type) == 'property_listing' ? 'selected' : '' }}>Property Listing</option>
                        <option value="agency_branding" {{ old('banner_type', $banner->banner_type) == 'agency_branding' ? 'selected' : '' }}>Agency Branding</option>
                        <option value="service_promotion" {{ old('banner_type', $banner->banner_type) == 'service_promotion' ? 'selected' : '' }}>Service Promotion</option>
                        <option value="event_announcement" {{ old('banner_type', $banner->banner_type) == 'event_announcement' ? 'selected' : '' }}>Event Announcement</option>
                        <option value="general_marketing" {{ old('banner_type', $banner->banner_type) == 'general_marketing' ? 'selected' : '' }}>General Marketing</option>
                    </select>
                    @error('banner_type')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" placeholder="Enter banner description" maxlength="1000">{{ old('description', is_array($banner->description) ? $banner->description['en'] : (is_string($banner->description) ? json_decode($banner->description, true)['en'] ?? '' : '')) }}</textarea>
                <span class="helper-text">Maximum 1000 characters</span>
                @error('description')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">Banner Image</h3>

            <div class="current-image">
                <p style="font-weight: 600; margin-bottom: 10px; color: #374151;">Current Image:</p>
                <img src="{{ $banner->image_url }}" alt="Current banner image">
                <p>Upload a new image to replace the current one (optional)</p>
            </div>

            <div class="image-upload" onclick="document.getElementById('banner-image').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #64748b; margin-bottom: 16px;"></i>
                <p style="color: #374151; font-size: 15px;">Click to upload new banner image</p>
                <p class="helper-text">Recommended size: 728x90px for standard banner</p>
                <input type="file" id="banner-image" name="image" accept="image/*" onchange="previewImage(this)">
                <img id="image-preview" class="image-preview" style="display: none;">
            </div>
            @error('image')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-section">
            <h3 class="section-title">Display Settings</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Banner Size *</label>
                    <select name="banner_size" class="form-select" required>
                        <option value="">Select Size</option>
                        <option value="banner" {{ old('banner_size', $banner->banner_size) == 'banner' ? 'selected' : '' }}>Standard Banner (728x90)</option>
                        <option value="leaderboard" {{ old('banner_size', $banner->banner_size) == 'leaderboard' ? 'selected' : '' }}>Leaderboard (970x250)</option>
                        <option value="rectangle" {{ old('banner_size', $banner->banner_size) == 'rectangle' ? 'selected' : '' }}>Rectangle (300x250)</option>
                        <option value="sidebar" {{ old('banner_size', $banner->banner_size) == 'sidebar' ? 'selected' : '' }}>Sidebar (300x600)</option>
                        <option value="mobile" {{ old('banner_size', $banner->banner_size) == 'mobile' ? 'selected' : '' }}>Mobile (320x100)</option>
                    </select>
                    @error('banner_size')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Position *</label>
                    <select name="position" class="form-select" required>
                        <option value="">Select Position</option>
                        <option value="header" {{ old('position', $banner->position) == 'header' ? 'selected' : '' }}>Header</option>
                        <option value="sidebar_top" {{ old('position', $banner->position) == 'sidebar_top' ? 'selected' : '' }}>Sidebar Top</option>
                        <option value="sidebar_bottom" {{ old('position', $banner->position) == 'sidebar_bottom' ? 'selected' : '' }}>Sidebar Bottom</option>
                        <option value="content_top" {{ old('position', $banner->position) == 'content_top' ? 'selected' : '' }}>Content Top</option>
                        <option value="content_middle" {{ old('position', $banner->position) == 'content_middle' ? 'selected' : '' }}>Content Middle</option>
                        <option value="content_bottom" {{ old('position', $banner->position) == 'content_bottom' ? 'selected' : '' }}>Content Bottom</option>
                        <option value="footer" {{ old('position', $banner->position) == 'footer' ? 'selected' : '' }}>Footer</option>
                    </select>
                    @error('position')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Link URL</label>
                    <input type="url" name="link_url" class="form-input"
                           value="{{ old('link_url', $banner->link_url) }}"
                           placeholder="https://example.com" maxlength="500">
                    @error('link_url')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
                    <div class="checkbox-group">
                        <input type="checkbox" id="new-tab" name="link_opens_new_tab" value="1"
                               {{ old('link_opens_new_tab', $banner->link_opens_new_tab) ? 'checked' : '' }}>
                        <label for="new-tab" style="cursor: pointer;">Open in new tab</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Call to Action</label>
                    <input type="text" name="call_to_action" class="form-input"
                           value="{{ old('call_to_action', is_array($banner->call_to_action) ? $banner->call_to_action['en'] : (is_string($banner->call_to_action) ? json_decode($banner->call_to_action, true)['en'] ?? '' : '')) }}"
                           placeholder="Learn More, Contact Us, etc." maxlength="50">
                    <span class="helper-text">Maximum 50 characters</span>
                    @error('call_to_action')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Link to Property (Optional)</label>
                <select name="property_id" class="form-select">
                    <option value="">No Property Link</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}" {{ old('property_id', $banner->property_id) == $property->id ? 'selected' : '' }}>
                            {{ is_array($property->name) ? $property->name['en'] : $property->name }}
                        </option>
                    @endforeach
                </select>
                @error('property_id')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">Schedule</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" class="form-input" required
                           value="{{ old('start_date', $banner->start_date) }}"
                           min="{{ date('Y-m-d') }}">
                    @error('start_date')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">End Date (Optional)</label>
                    <input type="date" name="end_date" class="form-input"
                           value="{{ old('end_date', $banner->end_date) }}">
                    <span class="helper-text">Leave empty for no end date</span>
                    @error('end_date')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">Additional Options</h3>

            <div class="checkbox-group">
                <input type="checkbox" id="show-contact" name="show_contact_info" value="1"
                       {{ old('show_contact_info', $banner->show_contact_info) ? 'checked' : '' }}>
                <label for="show-contact" style="cursor: pointer;">Show my contact information on banner</label>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('agent.banners') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Update Banner
        </button>
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
</script>
@endsection
