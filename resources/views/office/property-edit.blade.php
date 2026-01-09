@extends('layouts.office-layout')

@section('title', 'Edit Property - Dream Mulk')
@section('search-placeholder', 'Search...')

@section('styles')
<style>
    .page-header { margin-bottom: 32px; }
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
    .page-subtitle { color: var(--text-muted); font-size: 15px; }
    .back-btn { padding: 10px 20px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-secondary); text-decoration: none; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 24px; }
    .back-btn:hover { border-color: #6366f1; color: #6366f1; }

    .property-preview { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 24px; margin-bottom: 32px; }
    .preview-grid { display: grid; grid-template-columns: 300px 1fr; gap: 24px; }
    .main-image { width: 100%; height: 250px; border-radius: 12px; overflow: hidden; margin-bottom: 12px; }
    .main-image img { width: 100%; height: 100%; object-fit: cover; }
    .image-gallery { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .gallery-img { width: 100%; height: 65px; border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.3s; }
    .gallery-img:hover { border-color: #6366f1; }
    .gallery-img img { width: 100%; height: 100%; object-fit: cover; }

    .analytics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .analytics-card { background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; text-align: center; }
    .analytics-icon { font-size: 24px; color: #6366f1; margin-bottom: 8px; }
    .analytics-label { font-size: 13px; color: var(--text-muted); margin-bottom: 6px; }
    .analytics-value { font-size: 24px; font-weight: 700; color: var(--text-primary); }

    .info-row { display: flex; gap: 24px; margin-bottom: 16px; }
    .info-item { flex: 1; }
    .info-label { font-size: 13px; color: var(--text-muted); margin-bottom: 6px; font-weight: 600; }
    .info-value { font-size: 16px; color: var(--text-primary); font-weight: 600; }
    .status-badge { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; }
    .status-available { background: #d1fae5; color: #065f46; }
    .status-sold { background: #fee2e2; color: #991b1b; }
    .status-rented { background: #dbeafe; color: #1e40af; }

    .edit-form { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; }
    .form-section { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--border-color); }
    .form-section:last-of-type { border-bottom: none; }
    .section-title { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-group.full-width { grid-column: 1 / -1; }
    .form-label { display: block; font-size: 14px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; }
    .form-input, .form-select, .form-textarea { width: 100%; padding: 12px 16px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px; transition: all 0.3s; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
    .form-textarea { min-height: 120px; resize: vertical; font-family: inherit; }

    .current-images { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px; }
    .current-image { position: relative; height: 120px; border-radius: 10px; overflow: hidden; }
    .current-image img { width: 100%; height: 100%; object-fit: cover; }
    .remove-image-btn { position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: rgba(220,38,38,0.9); border: none; border-radius: 6px; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
    .remove-image-btn:hover { background: #dc2626; transform: scale(1.1); }

    .image-upload-zone { border: 2px dashed var(--border-color); border-radius: 12px; padding: 32px; text-align: center; background: var(--bg-main); cursor: pointer; transition: all 0.3s; }
    .image-upload-zone:hover { border-color: #6366f1; background: rgba(99,102,241,0.05); }
    .upload-icon { font-size: 48px; color: var(--text-muted); margin-bottom: 16px; }

    .images-section-title { font-size: 14px; font-weight: 600; color: var(--text-secondary); margin: 20px 0 12px 0; }

    .feature-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .checkbox-group { display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; transition: all 0.3s; }
    .checkbox-group:hover { border-color: #6366f1; }
    .checkbox-group input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: #6366f1; }
    .checkbox-group label { cursor: pointer; font-size: 14px; color: var(--text-primary); margin: 0; }

    .form-actions { display: flex; gap: 12px; justify-content: flex-end; padding-top: 24px; border-top: 2px solid var(--border-color); }
    .btn { padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s; border: none; display: flex; align-items: center; gap: 8px; }
    .btn-primary { background: #6366f1; color: white; }
    .btn-primary:hover { background: #5558e3; transform: translateY(-2px); }
    .btn-danger { background: #dc2626; color: white; }
    .btn-danger:hover { background: #b91c1c; transform: translateY(-2px); }
    .btn-secondary { background: transparent; color: var(--text-primary); border: 1px solid var(--border-color); text-decoration: none; }
    .btn-secondary:hover { border-color: #6366f1; color: #6366f1; }

    @media (max-width: 1024px) {
        .form-grid { grid-template-columns: 1fr; }
        .preview-grid { grid-template-columns: 1fr; }
        .analytics-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endsection

@section('content')
<a href="{{ route('office.properties') }}" class="back-btn">
    <i class="fas fa-arrow-left"></i> Back to Properties
</a>

<div class="page-header">
    <h1 class="page-title">Edit Property</h1>
    <p class="page-subtitle">Property ID: {{ $property->id }}</p>
</div>

@if(session('success'))
    <div style="padding: 16px; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); border-radius: 10px; color: #22c55e; margin-bottom: 24px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="padding: 16px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); border-radius: 10px; color: #ef4444; margin-bottom: 24px;">
        <strong><i class="fas fa-exclamation-circle"></i> Errors:</strong>
        <ul style="margin: 8px 0 0 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Property Preview -->
<div class="property-preview">
    <div class="preview-grid">
        <div>
            @php
                $images = is_array($property->images) ? $property->images : json_decode($property->images, true);
                $mainImage = is_array($images) && count($images) > 0 ? $images[0] : 'https://via.placeholder.com/300x250';
            @endphp
            <div class="main-image">
                <img src="{{ $mainImage }}" alt="Property" id="mainImg">
            </div>
            <div class="image-gallery">
                @if(is_array($images))
                    @foreach(array_slice($images, 0, 4) as $image)
                        <div class="gallery-img" onclick="document.getElementById('mainImg').src='{{ $image }}'">
                            <img src="{{ $image }}" alt="Gallery">
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div>
            <div class="analytics-grid">
                <div class="analytics-card">
                    <div class="analytics-icon"><i class="fas fa-eye"></i></div>
                    <div class="analytics-label">Views</div>
                    <div class="analytics-value">{{ number_format($property->views ?? 0) }}</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-icon"><i class="fas fa-heart"></i></div>
                    <div class="analytics-label">Favorites</div>
                    <div class="analytics-value">{{ number_format($property->favorites_count ?? 0) }}</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-icon"><i class="fas fa-star"></i></div>
                    <div class="analytics-label">Rating</div>
                    <div class="analytics-value">{{ number_format($property->rating ?? 0, 1) }}</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-icon"><i class="fas fa-calendar"></i></div>
                    <div class="analytics-label">Listed</div>
                    <div class="analytics-value" style="font-size: 14px;">{{ $property->created_at->diffForHumans() }}</div>
                </div>
            </div>

            @php
                $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
                $name = is_array($property->name) ? $property->name : json_decode($property->name, true);
                $type = is_array($property->type) ? $property->type : json_decode($property->type, true);
            @endphp

            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Property Name</div>
                    <div class="info-value">{{ $name['en'] ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Price</div>
                    <div class="info-value">${{ number_format($price['usd'] ?? 0) }}</div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Type</div>
                    <div class="info-value">{{ ucfirst($type['category'] ?? 'N/A') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Listing Type</div>
                    <div class="info-value">{{ ucfirst($property->listing_type) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $property->status }}">
                            {{ ucfirst($property->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Form -->
<form action="{{ route('office.property.update', $property->id) }}" method="POST" enctype="multipart/form-data" class="edit-form">
    @csrf
    @method('PUT')

    <!-- Basic Information -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h3>
        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label">Property Name (English) *</label>
                <input type="text" name="name_en" class="form-input" value="{{ $name['en'] ?? '' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Property Name (Arabic)</label>
                <input type="text" name="name_ar" class="form-input" value="{{ $name['ar'] ?? '' }}" dir="rtl">
            </div>
            <div class="form-group">
                <label class="form-label">Property Name (Kurdish)</label>
                <input type="text" name="name_ku" class="form-input" value="{{ $name['ku'] ?? '' }}">
            </div>
            @php
                $description = is_array($property->description) ? $property->description : json_decode($property->description, true);
            @endphp
            <div class="form-group full-width">
                <label class="form-label">Description (English) *</label>
                <textarea name="description_en" class="form-textarea" required>{{ $description['en'] ?? '' }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Description (Arabic)</label>
                <textarea name="description_ar" class="form-textarea" dir="rtl">{{ $description['ar'] ?? '' }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Description (Kurdish)</label>
                <textarea name="description_ku" class="form-textarea">{{ $description['ku'] ?? '' }}</textarea>
            </div>
        </div>
    </div>

    <!-- Property Details -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-home"></i> Property Details</h3>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Property Type *</label>
                <select name="property_type" class="form-select" required>
                    <option value="apartment" {{ ($type['category'] ?? '') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                    <option value="house" {{ ($type['category'] ?? '') == 'house' ? 'selected' : '' }}>House</option>
                    <option value="villa" {{ ($type['category'] ?? '') == 'villa' ? 'selected' : '' }}>Villa</option>
                    <option value="land" {{ ($type['category'] ?? '') == 'land' ? 'selected' : '' }}>Land</option>
                    <option value="commercial" {{ ($type['category'] ?? '') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                    <option value="office" {{ ($type['category'] ?? '') == 'office' ? 'selected' : '' }}>Office</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Listing Type *</label>
                <select name="listing_type" class="form-select" required>
                    <option value="sell" {{ $property->listing_type == 'sell' ? 'selected' : '' }}>For Sale</option>
                    <option value="rent" {{ $property->listing_type == 'rent' ? 'selected' : '' }}>For Rent</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select" required>
                    <option value="available" {{ $property->status == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="sold" {{ $property->status == 'sold' ? 'selected' : '' }}>Sold</option>
                    <option value="rented" {{ $property->status == 'rented' ? 'selected' : '' }}>Rented</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Area (m²) *</label>
                <input type="number" name="area" class="form-input" value="{{ $property->area ?? '' }}" step="0.01" required>
            </div>
            @php
                $rooms = is_array($property->rooms) ? $property->rooms : json_decode($property->rooms, true);
            @endphp
            <div class="form-group">
                <label class="form-label">Bedrooms *</label>
                <input type="number" name="bedrooms" class="form-input" value="{{ $rooms['bedroom']['count'] ?? 0 }}" min="0" required>
            </div>
            <div class="form-group">
                <label class="form-label">Bathrooms *</label>
                <input type="number" name="bathrooms" class="form-input" value="{{ $rooms['bathroom']['count'] ?? 0 }}" min="0" required>
            </div>
            <div class="form-group">
                <label class="form-label">Floor Number</label>
                <input type="number" name="floor_number" class="form-input" value="{{ $property->floor_number ?? '' }}" min="0">
            </div>
            <div class="form-group">
                <label class="form-label">Year Built</label>
                <input type="number" name="year_built" class="form-input" value="{{ $property->year_built ?? '' }}" min="1900" max="2030">
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-dollar-sign"></i> Pricing</h3>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Price (USD) *</label>
                <input type="number" name="price_usd" class="form-input" value="{{ $price['usd'] ?? '' }}" step="0.01" required>
            </div>
            <div class="form-group">
                <label class="form-label">Price (IQD) *</label>
                <input type="number" name="price_iqd" class="form-input" value="{{ $price['iqd'] ?? '' }}" step="0.01" required>
            </div>
        </div>
    </div>

    <!-- Location -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Location</h3>
        @php
            $address = is_array($property->address_details) ? $property->address_details : json_decode($property->address_details, true);
            $locations = is_array($property->locations) ? $property->locations : json_decode($property->locations, true);
        @endphp
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">City (English) *</label>
                <input type="text" name="city_en" class="form-input" value="{{ $address['city']['en'] ?? '' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">District (English) *</label>
                <input type="text" name="district_en" class="form-input" value="{{ $address['district']['en'] ?? '' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">City (Arabic)</label>
                <input type="text" name="city_ar" class="form-input" value="{{ $address['city']['ar'] ?? '' }}" dir="rtl">
            </div>
            <div class="form-group">
                <label class="form-label">District (Arabic)</label>
                <input type="text" name="district_ar" class="form-input" value="{{ $address['district']['ar'] ?? '' }}" dir="rtl">
            </div>
            <div class="form-group">
                <label class="form-label">City (Kurdish)</label>
                <input type="text" name="city_ku" class="form-input" value="{{ $address['city']['ku'] ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">District (Kurdish)</label>
                <input type="text" name="district_ku" class="form-input" value="{{ $address['district']['ku'] ?? '' }}">
            </div>
            <div class="form-group full-width">
                <label class="form-label">Full Address</label>
                <input type="text" name="address" class="form-input" value="{{ $property->address ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Latitude *</label>
                <input type="number" name="latitude" class="form-input" value="{{ $locations[0]['lat'] ?? '' }}" step="0.000001" required>
            </div>
            <div class="form-group">
                <label class="form-label">Longitude *</label>
                <input type="number" name="longitude" class="form-input" value="{{ $locations[0]['lng'] ?? '' }}" step="0.000001" required>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-star"></i> Features & Utilities</h3>
        <div class="feature-grid">
            <div class="checkbox-group">
                <input type="checkbox" name="furnished" id="furnished" value="1" {{ $property->furnished ? 'checked' : '' }}>
                <label for="furnished">Furnished</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="electricity" id="electricity" value="1" {{ $property->electricity ? 'checked' : '' }}>
                <label for="electricity">Electricity</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="water" id="water" value="1" {{ $property->water ? 'checked' : '' }}>
                <label for="water">Water</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="internet" id="internet" value="1" {{ $property->internet ? 'checked' : '' }}>
                <label for="internet">Internet</label>
            </div>
        </div>
    </div>

    <!-- Images -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-images"></i> Property Images</h3>

        <!-- Existing Images -->
        @if(is_array($images) && count($images) > 0)
            <div class="images-section-title">Current Images</div>
            <div class="current-images" id="existingImagesGrid">
                @foreach($images as $index => $image)
                    <div class="current-image" id="existing-img-{{ $index }}" data-image-index="{{ $index }}">
                        <img src="{{ $image }}" alt="Image {{ $index + 1 }}">
                        <button type="button" class="remove-image-btn" onclick="removeExistingImage({{ $index }})" title="Remove image">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Upload New Images -->
        <div class="image-upload-zone" onclick="document.getElementById('imageUpload').click()">
            <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div style="font-size: 16px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Click to upload new images</div>
            <div style="font-size: 14px; color: var(--text-muted);">JPG, PNG, GIF (Max: 5MB each)</div>
            <input type="file" id="imageUpload" name="images[]" multiple accept="image/*" style="display: none;" onchange="previewNewImages(event)">
        </div>

        <!-- Preview New Images -->
        <div id="newImagesContainer" style="display: none;">
            <div class="images-section-title">New Images to Upload</div>
            <div class="current-images" id="newImagesGrid"></div>
        </div>

        <!-- Hidden field to track removed images -->
        <input type="hidden" name="remove_images" id="removeImagesInput" value="">
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <a href="{{ route('office.properties') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
        <button type="button" class="btn btn-danger" onclick="if(confirm('Delete this property?')) document.getElementById('deleteForm').submit()">
            <i class="fas fa-trash"></i> Delete
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </div>
</form>

<!-- Hidden Delete Form -->
<form id="deleteForm" action="{{ route('office.property.delete', $property->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
    let imagesToRemove = [];
    let newFiles = [];

    function removeExistingImage(index) {
        const imageElement = document.getElementById(`existing-img-${index}`);
        if (imageElement) {
            imagesToRemove.push(index);
            document.getElementById('removeImagesInput').value = JSON.stringify(imagesToRemove);

            imageElement.style.transition = 'all 0.3s ease';
            imageElement.style.opacity = '0';
            imageElement.style.transform = 'scale(0.8)';

            setTimeout(() => {
                imageElement.remove();
                const grid = document.getElementById('existingImagesGrid');
                if (grid && grid.children.length === 0) {
                    const title = document.querySelector('.images-section-title');
                    if (title) title.remove();
                    grid.remove();
                }
            }, 300);
        }
    }

    function previewNewImages(event) {
        const files = Array.from(event.target.files);
        if (files.length === 0) return;

        newFiles = files;
        const grid = document.getElementById('newImagesGrid');
        const container = document.getElementById('newImagesContainer');

        grid.innerHTML = '';
        container.style.display = 'block';

        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'current-image';
                div.id = `new-img-${index}`;
                div.innerHTML = `
                    <img src="${e.target.result}" alt="New ${index + 1}">
                    <button type="button" class="remove-image-btn" onclick="removeNewImage(${index})" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                grid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    function removeNewImage(index) {
        newFiles.splice(index, 1);

        const dt = new DataTransfer();
        newFiles.forEach(file => dt.items.add(file));
        document.getElementById('imageUpload').files = dt.files;

        const grid = document.getElementById('newImagesGrid');
        grid.innerHTML = '';

        if (newFiles.length === 0) {
            document.getElementById('newImagesContainer').style.display = 'none';
        } else {
            newFiles.forEach((file, newIndex) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'current-image';
                    div.id = `new-img-${newIndex}`;
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="New ${newIndex + 1}">
                        <button type="button" class="remove-image-btn" onclick="removeNewImage(${newIndex})" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    grid.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
    }
</script>
@endsection
