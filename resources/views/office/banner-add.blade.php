@extends('layouts.office-layout')

@section('title', 'Create Banner - Dream Haven')

@section('styles')
<style>
    .page-title { font-size: 28px; font-weight: 700; color: var(--text-primary); margin-bottom: 30px; }
    .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; margin-bottom: 24px; }
    .form-section { margin-bottom: 32px; }
    .section-title { font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid var(--border-color); }

    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-label { font-size: 14px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; }
    .form-input, .form-select, .form-textarea { padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 15px; color: var(--text-primary); background: var(--bg-main); }
    .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #6366f1; }
    .form-textarea { min-height: 100px; resize: vertical; }

    .image-upload { border: 2px dashed var(--border-color); border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; }
    .image-upload:hover { border-color: #6366f1; background: var(--bg-main); }
    .image-upload input { display: none; }
    .image-preview { max-width: 100%; max-height: 300px; margin-top: 20px; border-radius: 8px; }

    .checkbox-group { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
    .checkbox-group input { width: 18px; height: 18px; cursor: pointer; }

    .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; }
    .btn { padding: 12px 32px; border-radius: 8px; font-size: 15px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary { background: #6366f1; color: white; }
    .btn-primary:hover { background: #5558e3; }
    .btn-secondary { background: var(--bg-main); color: var(--text-secondary); border: 1px solid var(--border-color); }

    .helper-text { font-size: 13px; color: var(--text-muted); margin-top: 6px; }
</style>
@endsection

@section('content')
<h1 class="page-title">Create New Banner</h1>

<form action="{{ route('office.banner.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="form-card">
        <div class="form-section">
            <h3 class="section-title">Basic Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Banner Title *</label>
                    <input type="text" name="title" class="form-input" required value="{{ old('title') }}" placeholder="Enter banner title">
                    @error('title')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Banner Type *</label>
                    <select name="banner_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="property_listing">Property Listing</option>
                        <option value="agency_branding">Agency Branding</option>
                        <option value="service_promotion">Service Promotion</option>
                        <option value="event_announcement">Event Announcement</option>
                        <option value="general_marketing">General Marketing</option>
                    </select>
                    @error('banner_type')<span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" placeholder="Enter banner description">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">Banner Image *</h3>

            <div class="image-upload" onclick="document.getElementById('banner-image').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                <p style="color: var(--text-secondary); font-size: 15px;">Click to upload banner image</p>
                <p class="helper-text">Recommended size: 728x90px for standard banner</p>
                <input type="file" id="banner-image" name="image" accept="image/*" required onchange="previewImage(this)">
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
                        <option value="banner">Standard Banner (728x90)</option>
                        <option value="leaderboard">Leaderboard (970x250)</option>
                        <option value="rectangle">Rectangle (300x250)</option>
                        <option value="sidebar">Sidebar (300x600)</option>
                        <option value="mobile">Mobile (320x100)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Position *</label>
                    <select name="position" class="form-select" required>
                        <option value="">Select Position</option>
                        <option value="header">Header</option>
                        <option value="sidebar_top">Sidebar Top</option>
                        <option value="sidebar_bottom">Sidebar Bottom</option>
                        <option value="content_top">Content Top</option>
                        <option value="content_middle">Content Middle</option>
                        <option value="content_bottom">Content Bottom</option>
                        <option value="footer">Footer</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Link URL</label>
                    <input type="url" name="link_url" class="form-input" value="{{ old('link_url') }}" placeholder="https://example.com">
                    <div class="checkbox-group">
                        <input type="checkbox" id="new-tab" name="link_opens_new_tab" value="1">
                        <label for="new-tab" style="cursor: pointer;">Open in new tab</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Call to Action</label>
                    <input type="text" name="call_to_action" class="form-input" value="{{ old('call_to_action') }}" placeholder="Learn More, Contact Us, etc." maxlength="50">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Link to Property (Optional)</label>
                <select name="property_id" class="form-select">
                    <option value="">No Property Link</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}">{{ is_array($property->name) ? $property->name['en'] : $property->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">Schedule</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" class="form-input" required value="{{ old('start_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">End Date (Optional)</label>
                    <input type="date" name="end_date" class="form-input" value="{{ old('end_date') }}">
                    <span class="helper-text">Leave empty for no end date</span>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">Additional Options</h3>

            <div class="checkbox-group">
                <input type="checkbox" id="show-contact" name="show_contact_info" value="1">
                <label for="show-contact" style="cursor: pointer;">Show office contact information on banner</label>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('office.banners') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-check"></i> Create Banner
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
