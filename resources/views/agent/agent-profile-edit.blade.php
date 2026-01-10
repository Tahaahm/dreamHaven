@extends('layouts.agent-layout')

@section('title', 'Edit Property - Dream Mulk')

@section('styles')
<style>
    .page-header {
        background: white;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .page-subtitle {
        color: #64748b;
        font-size: 14px;
    }

    .form-container {
        background: white;
        border-radius: 14px;
        padding: 32px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .form-section {
        margin-bottom: 32px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #303b97;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #303b97;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .form-grid-full {
        grid-column: 1 / -1;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-label .required {
        color: #ef4444;
        margin-left: 4px;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s;
        background: white;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #303b97;
        box-shadow: 0 0 0 3px rgba(48,59,151,0.1);
    }

    .form-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .existing-images {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .existing-image-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 1;
        border: 2px solid #e5e7eb;
    }

    .existing-image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-remove-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 32px;
        height: 32px;
        background: #ef4444;
        border: none;
        border-radius: 8px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.3s;
    }

    .image-remove-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .image-upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s;
        cursor: pointer;
    }

    .image-upload-zone:hover {
        border-color: #303b97;
        background: #f1f5f9;
    }

    .upload-icon {
        width: 64px;
        height: 64px;
        background: #303b97;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        margin-bottom: 16px;
    }

    .upload-text {
        font-size: 16px;
        color: #1f2937;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .upload-hint {
        font-size: 13px;
        color: #64748b;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: #303b97;
        color: white;
        box-shadow: 0 4px 12px rgba(48,59,151,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(48,59,151,0.4);
    }

    .btn-secondary {
        background: white;
        color: #64748b;
        border: 2px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .language-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
    }

    .language-tab {
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        background: #f1f5f9;
        color: #64748b;
        border: none;
        transition: all 0.3s;
    }

    .language-tab.active {
        background: #303b97;
        color: white;
    }

    .language-content {
        display: none;
    }

    .language-content.active {
        display: block;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-container {
            padding: 20px;
        }
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Property</h1>
    <p class="page-subtitle">Update property details</p>
</div>

<form action="{{ route('agent.property.update', $property->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <input type="hidden" name="remove_images" id="removeImagesInput" value="[]">

    <div class="form-container">
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i>
                Basic Information
            </h3>

            <div class="language-tabs">
                <button type="button" class="language-tab active" data-lang="en">English</button>
                <button type="button" class="language-tab" data-lang="ar">Arabic</button>
                <button type="button" class="language-tab" data-lang="ku">Kurdish</button>
            </div>

            <div class="language-content active" data-content="en">
                <div class="form-group">
                    <label class="form-label">Property Title (English)<span class="required">*</span></label>
                    <input type="text" name="title_en" class="form-input" value="{{ $property->title['en'] ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description (English)</label>
                    <textarea name="description_en" class="form-textarea">{{ $property->description['en'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="language-content" data-content="ar">
                <div class="form-group">
                    <label class="form-label">Property Title (Arabic)</label>
                    <input type="text" name="title_ar" class="form-input" value="{{ $property->title['ar'] ?? '' }}" dir="rtl">
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Arabic)</label>
                    <textarea name="description_ar" class="form-textarea" dir="rtl">{{ $property->description['ar'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="language-content" data-content="ku">
                <div class="form-group">
                    <label class="form-label">Property Title (Kurdish)</label>
                    <input type="text" name="title_ku" class="form-input" value="{{ $property->title['ku'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Kurdish)</label>
                    <textarea name="description_ku" class="form-textarea">{{ $property->description['ku'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Price (IQD)<span class="required">*</span></label>
                    <input type="number" name="price" class="form-input" value="{{ $property->price }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Property Type<span class="required">*</span></label>
                    <select name="property_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="apartment" {{ $property->property_type == 'apartment' ? 'selected' : '' }}>Apartment</option>
                        <option value="villa" {{ $property->property_type == 'villa' ? 'selected' : '' }}>Villa</option>
                        <option value="house" {{ $property->property_type == 'house' ? 'selected' : '' }}>House</option>
                        <option value="land" {{ $property->property_type == 'land' ? 'selected' : '' }}>Land</option>
                        <option value="commercial" {{ $property->property_type == 'commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="office" {{ $property->property_type == 'office' ? 'selected' : '' }}>Office</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Status<span class="required">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="available" {{ $property->status == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="sold" {{ $property->status == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="rented" {{ $property->status == 'rented' ? 'selected' : '' }}>Rented</option>
                        <option value="pending" {{ $property->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Area (m²)</label>
                    <input type="number" name="area" class="form-input" value="{{ $property->area }}" min="0">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-map-marker-alt"></i>
                Location
            </h3>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">City (English)<span class="required">*</span></label>
                    <input type="text" name="city_en" class="form-input" value="{{ $property->city['en'] ?? '' }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">District (English)<span class="required">*</span></label>
                    <input type="text" name="district_en" class="form-input" value="{{ $property->district['en'] ?? '' }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">City (Arabic)</label>
                    <input type="text" name="city_ar" class="form-input" value="{{ $property->city['ar'] ?? '' }}" dir="rtl">
                </div>

                <div class="form-group">
                    <label class="form-label">District (Arabic)</label>
                    <input type="text" name="district_ar" class="form-input" value="{{ $property->district['ar'] ?? '' }}" dir="rtl">
                </div>

                <div class="form-group">
                    <label class="form-label">City (Kurdish)</label>
                    <input type="text" name="city_ku" class="form-input" value="{{ $property->city['ku'] ?? '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label">District (Kurdish)</label>
                    <input type="text" name="district_ku" class="form-input" value="{{ $property->district['ku'] ?? '' }}">
                </div>

                <div class="form-group form-grid-full">
                    <label class="form-label">Full Address</label>
                    <input type="text" name="address" class="form-input" value="{{ $property->address }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Latitude</label>
                    <input type="number" name="latitude" class="form-input" step="0.0000001" value="{{ $property->latitude }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Longitude</label>
                    <input type="number" name="longitude" class="form-input" step="0.0000001" value="{{ $property->longitude }}">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-home"></i>
                Property Details
            </h3>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Bedrooms</label>
                    <input type="number" name="bedrooms" class="form-input" min="0" value="{{ $property->bedrooms }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Bathrooms</label>
                    <input type="number" name="bathrooms" class="form-input" min="0" value="{{ $property->bathrooms }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Floors</label>
                    <input type="number" name="floors" class="form-input" min="0" value="{{ $property->floors }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Parking Spaces</label>
                    <input type="number" name="parking_spaces" class="form-input" min="0" value="{{ $property->parking_spaces }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Year Built</label>
                    <input type="number" name="year_built" class="form-input" min="1900" max="2100" value="{{ $property->year_built }}">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-images"></i>
                Property Images
            </h3>

            @if($property->images && count($property->images) > 0)
                <div class="existing-images" id="existingImages">
                    @foreach($property->images as $index => $image)
                    <div class="existing-image-item" data-index="{{ $index }}">
                        <img src="{{ $image }}" alt="Property Image">
                        <button type="button" class="image-remove-btn" onclick="markImageForRemoval({{ $index }})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
            @endif

            <div class="image-upload-zone" id="uploadZone">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="upload-text">Add more images</div>
                <div class="upload-hint">PNG, JPG, WEBP up to 5MB each</div>
                <input type="file" name="images[]" id="imageInput" accept="image/*" multiple hidden>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('agent.properties') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Property
            </button>
        </div>
    </div>
</form>

<script>
document.querySelectorAll('.language-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const lang = this.dataset.lang;
        document.querySelectorAll('.language-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.language-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.querySelector(`[data-content="${lang}"]`).classList.add('active');
    });
});

let imagesToRemove = [];

function markImageForRemoval(index) {
    if (confirm('Are you sure you want to remove this image?')) {
        imagesToRemove.push(index);
        document.querySelector(`.existing-image-item[data-index="${index}"]`).style.opacity = '0.3';
        document.getElementById('removeImagesInput').value = JSON.stringify(imagesToRemove);
    }
}

const uploadZone = document.getElementById('uploadZone');
const imageInput = document.getElementById('imageInput');

uploadZone.addEventListener('click', () => imageInput.click());
</script>
@endsection
