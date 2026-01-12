@extends('layouts.office-layout')

@section('title', 'Design Campaign | Dream Haven')

@section('styles')
<style>
    :root {
        --primary: #6366f1;
        --primary-hover: #4f46e5;
        --primary-soft: #eef2ff;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --danger: #ef4444;
    }

    .page-header { margin-bottom: 2.5rem; }
    .page-title { font-size: 2rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.025em; }

    .create-container { display: grid; grid-template-columns: 1fr 350px; gap: 2rem; align-items: start; }

    .form-card {
        background: white; border-radius: 20px; padding: 2.5rem;
        border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .section-header {
        display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;
        padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;
    }
    .step-badge {
        width: 32px; height: 32px; background: var(--primary-soft); color: var(--primary);
        border-radius: 8px; display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.875rem;
    }
    .section-title { font-size: 1.25rem; font-weight: 700; color: var(--text-main); }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    .form-group { margin-bottom: 1.5rem; display: flex; flex-direction: column; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #475569; margin-bottom: 0.6rem; }

    .input-premium {
        width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid #e2e8f0;
        background: #f8fafc; transition: all 0.2s; font-size: 0.95rem; color: var(--text-main);
    }
    .input-premium:focus {
        background: white; border-color: var(--primary); outline: none;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .upload-zone {
        border: 2px dashed #cbd5e1; border-radius: 16px; padding: 2rem;
        text-align: center; cursor: pointer; transition: all 0.3s ease;
        background: #fcfcfd; position: relative;
    }
    .upload-zone:hover { border-color: var(--primary); background: var(--primary-soft); }

    .preview-container {
        margin-top: 1.5rem; border-radius: 12px; overflow: hidden;
        border: 1px solid #e2e8f0; display: none;
    }
    #image-preview { width: 100%; display: block; max-height: 250px; object-fit: contain; }

    .checkbox-item {
        display: flex; align-items: center; gap: 10px; padding: 12px;
        background: #f8fafc; border-radius: 10px; cursor: pointer; transition: 0.2s;
    }
    .checkbox-item:hover { background: var(--primary-soft); }
    .checkbox-item input { width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary); }

    .btn-save {
        background: var(--primary); color: white !important; padding: 0.85rem 2.5rem;
        border-radius: 12px; font-weight: 700; border: none; cursor: pointer;
        transition: all 0.3s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        display: inline-flex; align-items: center; gap: 10px;
    }
    .btn-save:hover { background: var(--primary-hover); transform: translateY(-2px); }

    .error-msg { color: var(--danger); font-size: 0.8rem; margin-top: 5px; font-weight: 500; }
    .helper-text { font-size: 0.8rem; color: var(--text-muted); margin-top: 6px; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Create New Banner</h1>
    <p style="color: var(--text-muted);">Launch a high-impact advertising campaign on the platform.</p>
</div>

<form action="{{ route('office.banner.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="create-container">
        <div class="main-form">
            <div class="form-card">
                <div class="section-header">
                    <div class="step-badge">01</div>
                    <h3 class="section-title">Basic Information</h3>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Banner Title *</label>
                        <input type="text" name="title" class="input-premium" required value="{{ old('title') }}" placeholder="e.g. Luxury Penthouse Promo">
                        @error('title') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Banner Type *</label>
                        <select name="banner_type" class="input-premium" required>
                            <option value="">Select Type</option>
                            <option value="property_listing">Property Listing</option>
                            <option value="agency_branding">Agency Branding</option>
                            <option value="service_promotion">Service Promotion</option>
                            <option value="event_announcement">Event Announcement</option>
                            <option value="general_marketing">General Marketing</option>
                        </select>
                        @error('banner_type') <span class="error-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="input-premium" style="min-height: 100px; resize: vertical;" placeholder="Brief details about this campaign">{{ old('description') }}</textarea>
                </div>

                <div class="section-header" style="margin-top: 2.5rem;">
                    <div class="step-badge">02</div>
                    <h3 class="section-title">Banner Creative</h3>
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Image *</label>
                    <div class="upload-zone" onclick="document.getElementById('banner-image').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                        <p style="font-weight: 600; color: #475569;">Click to upload creative asset</p>
                        <p class="helper-text">Recommended: 728x90px for Standard Banner</p>
                        <input type="file" id="banner-image" name="image" accept="image/*" hidden required onchange="previewImage(this)">

                        <div class="preview-container" id="preview-box">
                            <img id="image-preview">
                        </div>
                    </div>
                    @error('image') <span class="error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="section-header" style="margin-top: 2.5rem;">
                    <div class="step-badge">03</div>
                    <h3 class="section-title">Display Settings</h3>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Banner Size *</label>
                        <select name="banner_size" class="input-premium" required>
                            <option value="banner">Standard Banner (728x90)</option>
                            <option value="leaderboard">Leaderboard (970x250)</option>
                            <option value="rectangle">Rectangle (300x250)</option>
                            <option value="sidebar">Sidebar (300x600)</option>
                            <option value="mobile">Mobile (320x100)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position *</label>
                        <select name="position" class="input-premium" required>
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
                        <input type="url" name="link_url" class="input-premium" value="{{ old('link_url') }}" placeholder="https://">
                        <label class="checkbox-item" style="margin-top: 10px;">
                            <input type="checkbox" name="link_opens_new_tab" value="1">
                            <span style="font-size: 0.85rem; font-weight: 600;">Open in new tab</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Call to Action Text</label>
                        <input type="text" name="call_to_action" class="input-premium" value="{{ old('call_to_action') }}" placeholder="e.g. Learn More">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Link to Internal Property (Optional)</label>
                    <select name="property_id" class="input-premium">
                        <option value="">No Specific Property</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}">
                                {{ is_array($property->name) ? $property->name['en'] : $property->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="section-header" style="margin-top: 2.5rem;">
                    <div class="step-badge">04</div>
                    <h3 class="section-title">Schedule</h3>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" class="input-premium" required
                               value="{{ old('start_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" onchange="updateEndDate()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date (Automatic 30 Days)</label>
                        <input type="date" id="end_date" name="end_date" class="input-premium" style="background: #f0fdf4; border-color: #bbf7d0;" required>
                        <p class="helper-text" style="color: #16a34a;"><i class="fas fa-magic"></i> Set to 30 days by default</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-item">
                        <input type="checkbox" name="show_contact_info" value="1">
                        <span style="font-size: 0.9rem; font-weight: 600;">Show office contact information on banner</span>
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #f1f5f9;">
                    <a href="{{ route('office.banners') }}" style="text-decoration: none; color: #64748b; font-weight: 700; padding: 0.85rem 1.5rem;">Cancel</a>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-check"></i> Create & Launch Campaign
                    </button>
                </div>
            </div>
        </div>

        <aside class="tips-card" style="background: #f8fafc; border-radius: 20px; padding: 1.5rem; border: 1px solid #e2e8f0; position: sticky; top: 20px;">
            <h4 style="font-weight: 800; color: #1e293b; margin-bottom: 1.5rem;">Campaign Preview</h4>
            <div style="padding: 15px; background: white; border-radius: 12px; border: 1px dashed #cbd5e1; text-align: center;">
                <p style="color: #94a3b8; font-size: 0.85rem;">Your asset will appear here once uploaded.</p>
            </div>

            <div style="margin-top: 2rem;">
                <h5 style="font-weight: 700; font-size: 0.9rem; margin-bottom: 10px;">Why 30 Days?</h5>
                <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">Our data shows that 30-day campaigns have a 40% higher conversion rate than shorter bursts.</p>
            </div>
        </aside>
    </div>
</form>

<script>
function previewImage(input) {
    const box = document.getElementById('preview-box');
    const preview = document.getElementById('image-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            box.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function updateEndDate() {
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    if (startInput.value) {
        let date = new Date(startInput.value);
        date.setDate(date.getDate() + 30);

        let year = date.getFullYear();
        let month = String(date.getMonth() + 1).padStart(2, '0');
        let day = String(date.getDate()).padStart(2, '0');

        endInput.value = `${year}-${month}-${day}`;
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', updateEndDate);
</script>
@endsection
