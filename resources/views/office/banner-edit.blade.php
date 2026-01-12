@extends('layouts.office-layout')

@section('title', 'Edit Campaign | Dream Haven')

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

    .edit-container { display: grid; grid-template-columns: 1fr 350px; gap: 2rem; align-items: start; }

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
    .input-premium:focus:not([readonly]) {
        background: white; border-color: var(--primary); outline: none;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    /* Locked Field Style */
    .input-locked { background: #f1f5f9 !important; color: #94a3b8 !important; cursor: not-allowed; border-color: #e2e8f0; }

    /* Current Image Display */
    .current-image-wrapper {
        position: relative; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;
        background: #f8fafc; margin-bottom: 15px; padding: 10px;
    }
    .image-tag {
        position: absolute; top: 15px; left: 15px; background: rgba(0,0,0,0.6);
        color: white; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;
    }
    #image-preview { width: 100%; max-height: 250px; object-fit: contain; display: block; }

    .upload-btn {
        display: inline-flex; align-items: center; gap: 8px; background: white;
        border: 1px solid var(--primary); color: var(--primary); padding: 8px 16px;
        border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s;
    }
    .upload-btn:hover { background: var(--primary-soft); }

    .checkbox-item {
        display: flex; align-items: center; gap: 10px; padding: 12px;
        background: #f8fafc; border-radius: 10px; cursor: pointer;
    }
    .checkbox-item input { width: 18px; height: 18px; accent-color: var(--primary); }

    .btn-save {
        background: var(--primary); color: white !important; padding: 0.85rem 2.5rem;
        border-radius: 12px; font-weight: 700; border: none; cursor: pointer;
        transition: all 0.3s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        display: inline-flex; align-items: center; gap: 10px;
    }
    .btn-save:hover { background: var(--primary-hover); transform: translateY(-2px); }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Campaign</h1>
</div>

<form action="{{ route('office.banner.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="edit-container">
        <div class="main-form">
            <div class="form-card">
                <div class="section-header">
                    <div class="step-badge">01</div>
                    <h3 class="section-title">Campaign Details</h3>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Banner Title *</label>
                        <input type="text" name="title" class="input-premium" required value="{{ old('title', $banner->title) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Banner Type *</label>
                        <select name="banner_type" class="input-premium" required>
                            <option value="property_listing" {{ $banner->banner_type == 'property_listing' ? 'selected' : '' }}>Property Listing</option>
                            <option value="agency_branding" {{ $banner->banner_type == 'agency_branding' ? 'selected' : '' }}>Agency Branding</option>
                            <option value="service_promotion" {{ $banner->banner_type == 'service_promotion' ? 'selected' : '' }}>Service Promotion</option>
                            <option value="event_announcement" {{ $banner->banner_type == 'event_announcement' ? 'selected' : '' }}>Event Announcement</option>
                            <option value="general_marketing" {{ $banner->banner_type == 'general_marketing' ? 'selected' : '' }}>General Marketing</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="input-premium" style="min-height: 80px;">{{ old('description', $banner->description) }}</textarea>
                </div>

                <div class="section-header" style="margin-top: 2.5rem;">
                    <div class="step-badge">02</div>
                    <h3 class="section-title">Campaign Visual</h3>
                </div>

                <div class="form-group">
                    <label class="form-label">Banner Image</label>
                    <div class="current-image-wrapper">
                        <span class="image-tag">CURRENT IMAGE</span>
                        <img id="image-preview" src="{{ asset('storage/' . $banner->image) }}" alt="Current Banner">
                    </div>

                    <div style="text-align: right;">
                        <input type="file" id="banner-image" name="image" accept="image/*" hidden onchange="previewImage(this)">
                        <button type="button" class="upload-btn" onclick="document.getElementById('banner-image').click()">
                            <i class="fas fa-sync-alt"></i> Replace Image
                        </button>
                    </div>
                </div>

                <div class="section-header" style="margin-top: 2.5rem;">
                    <div class="step-badge">03</div>
                    <h3 class="section-title">Display Settings</h3>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Banner Size</label>
                        <select name="banner_size" class="input-premium">
                            <option value="banner" {{ $banner->banner_size == 'banner' ? 'selected' : '' }}>Standard (728x90)</option>
                            <option value="rectangle" {{ $banner->banner_size == 'rectangle' ? 'selected' : '' }}>Rectangle (300x250)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position</label>
                        <select name="position" class="input-premium">
                            <option value="header" {{ $banner->position == 'header' ? 'selected' : '' }}>Header</option>
                            <option value="sidebar_top" {{ $banner->position == 'sidebar_top' ? 'selected' : '' }}>Sidebar Top</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Link URL</label>
                        <input type="url" name="link_url" class="input-premium" value="{{ old('link_url', $banner->link_url) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Call to Action Text</label>
                        <input type="text" name="call_to_action" class="input-premium" value="{{ old('call_to_action', $banner->call_to_action) }}">
                    </div>
                </div>

                <div class="section-header" style="margin-top: 2.5rem;">
                    <div class="step-badge">04</div>
                    <h3 class="section-title">Campaign Period</h3>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date (Read-Only)</label>
                        <input type="date" class="input-premium input-locked" value="{{ $banner->start_date }}" readonly>
                        <input type="hidden" name="start_date" value="{{ $banner->start_date }}">
                        <p class="helper-text"><i class="fas fa-lock"></i> Dates cannot be changed once active</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date (Read-Only)</label>
                        <input type="date" class="input-premium input-locked" value="{{ $banner->end_date }}" readonly>
                        <input type="hidden" name="end_date" value="{{ $banner->end_date }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-item">
                        <input type="checkbox" name="show_contact_info" value="1" {{ $banner->show_contact_info ? 'checked' : '' }}>
                        <span style="font-size: 0.9rem; font-weight: 600;">Show office contact info on banner</span>
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #f1f5f9;">
                    <a href="{{ route('office.banners') }}" style="text-decoration: none; color: #64748b; font-weight: 700; padding: 0.85rem 1.5rem;">Discard</a>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>

        <aside style="position: sticky; top: 20px;">
            <div style="background: var(--primary-soft); border-radius: 20px; padding: 1.5rem; border: 1px solid rgba(99, 102, 241, 0.2);">
                <h4 style="font-weight: 800; color: var(--primary); margin-bottom: 1rem;">Why are dates locked?</h4>
                <p style="font-size: 0.85rem; color: #4338ca; line-height: 1.5;">To ensure consistent campaign tracking and billing, the start and end dates are permanent. If you need a different period, please create a new campaign.</p>
            </div>
        </aside>
    </div>
</form>

<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            // Update the tag to show it's a "New" image
            document.querySelector('.image-tag').innerText = 'NEW PREVIEW';
            document.querySelector('.image-tag').style.background = '#6366f1';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
