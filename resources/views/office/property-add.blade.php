@extends('layouts.office-layout')

@section('title', 'Add Property - Dream Mulk')

@section('styles')
<style>
    /* YOUR CSS (Unchanged) */
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); }
    .back-btn { padding: 10px 20px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-secondary); text-decoration: none; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .back-btn:hover { border-color: #6366f1; color: #6366f1; }
    .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; }
    .form-section { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--border-color); }
    .form-section:last-of-type { border-bottom: none; }
    .section-title { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; font-size: 14px; }
    .form-input, .form-select, .form-textarea { width: 100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 15px; background: var(--bg-main); color: var(--text-primary); transition: all 0.3s; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
    .form-textarea { resize: vertical; min-height: 120px; }

    /* UPLOAD STYLES */
    .image-upload-zone { border: 2px dashed var(--border-color); border-radius: 12px; padding: 40px; text-align: center; background: var(--bg-main); cursor: pointer; transition: all 0.3s; position: relative; }
    .image-upload-zone:hover { border-color: #6366f1; background: rgba(99,102,241,0.05); }
    .image-upload-zone.dragover { border-color: #6366f1; background: rgba(99,102,241,0.05); }
    .image-upload-zone * { pointer-events: none; }
    .upload-icon { font-size: 48px; color: var(--text-muted); margin-bottom: 16px; }
    .image-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 16px; margin-top: 20px; }
    .preview-item { position: relative; aspect-ratio: 1; border-radius: 10px; overflow: hidden; border: 2px solid var(--border-color); }
    .preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .remove-preview-btn { position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: rgba(220,38,38,0.9); border: none; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; z-index: 10; }
    .remove-preview-btn:hover { background: #dc2626; transform: scale(1.1); }
    .feature-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .checkbox-group { display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; }
    .checkbox-group:hover { border-color: #6366f1; }
    .checkbox-group input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: #6366f1; }
    .checkbox-group label { cursor: pointer; font-size: 14px; color: var(--text-primary); margin: 0; }
    .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 2px solid var(--border-color); }
    .btn-primary { background: #6366f1; color: white; padding: 14px 32px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
    .btn-primary:hover { background: #5558e3; transform: translateY(-1px); }
    .btn-secondary { padding: 14px 32px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); text-decoration: none; font-weight: 600; transition: all 0.3s; }
    .btn-secondary:hover { border-color: #6366f1; color: #6366f1; }
    @media (max-width: 768px) { .form-row, .feature-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-plus-circle"></i> Add New Property</h1>
    <a href="{{ route('office.properties') }}" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

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

<form action="{{ route('office.property.store') }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf

    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h3>

        <div class="form-group">
            <label class="form-label">Property Name (English) *</label>
            <input type="text" name="name_en" class="form-input" value="{{ old('name_en') }}" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Property Name (Arabic)</label>
                <input type="text" name="name_ar" class="form-input" value="{{ old('name_ar') }}" dir="rtl">
            </div>
            <div class="form-group">
                <label class="form-label">Property Name (Kurdish)</label>
                <input type="text" name="name_ku" class="form-input" value="{{ old('name_ku') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description (English) *</label>
            <textarea name="description_en" class="form-textarea" required>{{ old('description_en') }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Description (Arabic)</label>
                <textarea name="description_ar" class="form-textarea" dir="rtl">{{ old('description_ar') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Description (Kurdish)</label>
                <textarea name="description_ku" class="form-textarea">{{ old('description_ku') }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-home"></i> Property Details</h3>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Property Type *</label>
                <select name="property_type" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="apartment">Apartment</option>
                    <option value="house">House</option>
                    <option value="villa">Villa</option>
                    <option value="land">Land</option>
                    <option value="commercial">Commercial</option>
                    <option value="office">Office</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Listing Type *</label>
                <select name="listing_type" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="sell">For Sale</option>
                    <option value="rent">For Rent</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Area (m²) *</label>
                <input type="number" name="area" class="form-input" value="{{ old('area') }}" step="0.01" required>
            </div>
            <div class="form-group">
                <label class="form-label">Bedrooms *</label>
                <input type="number" name="bedrooms" class="form-input" value="{{ old('bedrooms', 0) }}" min="0" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Bathrooms *</label>
                <input type="number" name="bathrooms" class="form-input" value="{{ old('bathrooms', 0) }}" min="0" required>
            </div>
            <div class="form-group">
                <label class="form-label">Floor Number</label>
                <input type="number" name="floor_number" class="form-input" value="{{ old('floor_number') }}" min="0">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Year Built</label>
            <input type="number" name="year_built" class="form-input" value="{{ old('year_built') }}" min="1900" max="2030">
        </div>
    </div>

    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-dollar-sign"></i> Pricing</h3>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Price (USD) *</label>
                <input type="number" name="price_usd" class="form-input" value="{{ old('price_usd') }}" step="0.01" required>
            </div>
            <div class="form-group">
                <label class="form-label">Price (IQD) *</label>
                <input type="number" name="price_iqd" class="form-input" value="{{ old('price_iqd') }}" step="0.01" required>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Location</h3>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">City (English) *</label>
                <input type="text" name="city_en" class="form-input" value="{{ old('city_en') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">District (English) *</label>
                <input type="text" name="district_en" class="form-input" value="{{ old('district_en') }}" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">City (Arabic)</label>
                <input type="text" name="city_ar" class="form-input" value="{{ old('city_ar') }}" dir="rtl">
            </div>
            <div class="form-group">
                <label class="form-label">District (Arabic)</label>
                <input type="text" name="district_ar" class="form-input" value="{{ old('district_ar') }}" dir="rtl">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">City (Kurdish)</label>
                <input type="text" name="city_ku" class="form-input" value="{{ old('city_ku') }}">
            </div>
            <div class="form-group">
                <label class="form-label">District (Kurdish)</label>
                <input type="text" name="district_ku" class="form-input" value="{{ old('district_ku') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Full Address</label>
            <input type="text" name="address" class="form-input" value="{{ old('address') }}">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Latitude *</label>
                <input type="number" name="latitude" class="form-input" value="{{ old('latitude', 36.1911) }}" step="0.000001" required>
            </div>
            <div class="form-group">
                <label class="form-label">Longitude *</label>
                <input type="number" name="longitude" class="form-input" value="{{ old('longitude', 44.0091) }}" step="0.000001" required>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-star"></i> Features & Utilities</h3>

        <div class="feature-grid">
            <div class="checkbox-group">
                <input type="checkbox" name="furnished" id="furnished" value="1">
                <label for="furnished">Furnished</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="electricity" id="electricity" value="1">
                <label for="electricity">Electricity</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="water" id="water" value="1">
                <label for="water">Water</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" name="internet" id="internet" value="1">
                <label for="internet">Internet</label>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-images"></i> Property Images *</h3>

        <div class="image-upload-zone" id="uploadZone">
            <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div style="font-size: 16px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Click to Upload Images</div>
            <div style="font-size: 14px; color: var(--text-muted);">Upload 3-10 images (Max 5MB each)</div>
        </div>

        <input type="file" id="filePicker" multiple accept="image/*" style="display: none;">

        <input type="file" name="images[]" id="submissionInput" multiple style="display: none;">

        <div class="image-preview-grid" id="imagePreviewGrid"></div>
    </div>

    <div class="form-actions">
        <a href="{{ route('office.properties') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-check-circle"></i> Add Property
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadZone = document.getElementById('uploadZone');
        const filePicker = document.getElementById('filePicker'); // The one we click
        const submissionInput = document.getElementById('submissionInput'); // The one we submit
        const previewGrid = document.getElementById('imagePreviewGrid');

        let selectedFiles = [];

        // 1. CLICK TO OPEN PICKER
        if(uploadZone) {
            uploadZone.addEventListener('click', function() {
                filePicker.click();
            });
        }

        // 2. HANDLE FILE SELECTION (From Click)
        if(filePicker) {
            filePicker.addEventListener('change', function(e) {
                handleFiles(e.target.files);
                filePicker.value = ''; // Safe to clear THIS one now
            });
        }

        // 3. DRAG & DROP
        if(uploadZone) {
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });
            uploadZone.addEventListener('dragleave', () => {
                uploadZone.classList.remove('dragover');
            });
            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
                handleFiles(e.dataTransfer.files);
            });
        }

        // 4. MAIN FILE LOGIC
        function handleFiles(files) {
            const newFiles = Array.from(files);

            // Validation
            for (let file of newFiles) {
                if (!file.type.startsWith('image/')) {
                    alert('Only images are allowed');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('File too large (Max 5MB)');
                    return;
                }
            }

            newFiles.forEach(file => {
                if (selectedFiles.length < 10) {
                    selectedFiles.push(file);
                }
            });

            updateUI();
        }

        // 5. UPDATE PREVIEWS AND SUBMISSION INPUT
        function updateUI() {
            previewGrid.innerHTML = '';

            // Visual Preview
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}">
                        <button type="button" class="remove-preview-btn" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    previewGrid.appendChild(div);
                };
                reader.readAsDataURL(file);
            });

            // CRITICAL: Update the HIDDEN Submission Input
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            submissionInput.files = dt.files;

            console.log('Submission input now has:', submissionInput.files.length, 'files');
        }

        // 6. REMOVE IMAGE
        previewGrid.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-preview-btn');
            if (removeBtn) {
                const index = parseInt(removeBtn.getAttribute('data-index'));
                selectedFiles.splice(index, 1);
                updateUI();
            }
        });
    });
</script>
@endsection
