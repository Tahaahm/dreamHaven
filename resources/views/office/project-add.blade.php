@extends('layouts.office-layout')

@section('title', 'Add Project - Dream Mulk')
@section('search-placeholder', 'Search...')

@section('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); }
    .back-btn { padding: 10px 20px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-secondary); text-decoration: none; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .back-btn:hover { border-color: #6366f1; color: #6366f1; }

    .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; margin-bottom: 24px; }
    .form-section { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--border-color); }
    .form-section:last-of-type { border-bottom: none; }
    .section-title { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; font-size: 14px; }
    .form-input, .form-select, .form-textarea { width: 100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 15px; background: var(--bg-main); color: var(--text-primary); transition: all 0.3s; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
    .form-textarea { resize: vertical; min-height: 120px; font-family: inherit; }
    .form-helper { font-size: 13px; color: var(--text-muted); margin-top: 6px; }

    .image-upload-zone { border: 2px dashed var(--border-color); border-radius: 12px; padding: 40px; text-align: center; background: var(--bg-main); cursor: pointer; transition: all 0.3s; }
    .image-upload-zone:hover { border-color: #6366f1; background: rgba(99,102,241,0.05); }
    .upload-icon { font-size: 48px; color: var(--text-muted); margin-bottom: 16px; }

    .image-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 16px; margin-top: 20px; }
    .preview-item { position: relative; aspect-ratio: 1; border-radius: 10px; overflow: hidden; border: 2px solid var(--border-color); }
    .preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .remove-preview-btn { position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: rgba(220,38,38,0.9); border: none; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
    .remove-preview-btn:hover { background: #dc2626; transform: scale(1.1); }

    .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 2px solid var(--border-color); }
    .btn-primary { background: #6366f1; color: white; padding: 14px 32px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
    .btn-primary:hover { background: #5558e3; transform: translateY(-1px); }
    .btn-secondary { padding: 14px 32px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); text-decoration: none; font-weight: 600; transition: all 0.3s; }
    .btn-secondary:hover { border-color: #6366f1; color: #6366f1; }

    .alert-error { padding: 16px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); border-radius: 10px; color: #ef4444; margin-bottom: 24px; }
    .alert-error ul { margin: 8px 0 0 20px; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-plus-circle"></i> Add New Project</h1>
    <a href="{{ route('office.projects') }}" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

@if($errors->any())
    <div class="alert-error">
        <strong><i class="fas fa-exclamation-circle"></i> Please fix the following errors:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('office.project.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="form-card">
        <!-- Basic Information -->
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h3>

            <div class="form-group">
                <label class="form-label">Project Name (English) *</label>
                <input type="text" name="name_en" class="form-input" value="{{ old('name_en') }}" required placeholder="e.g., Dream City Residences">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Project Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-input" value="{{ old('name_ar') }}" placeholder="مثال: مساكن مدينة الأحلام" dir="rtl">
                </div>
                <div class="form-group">
                    <label class="form-label">Project Name (Kurdish)</label>
                    <input type="text" name="name_ku" class="form-input" value="{{ old('name_ku') }}" placeholder="نموونە: شارەمەندی شاری خەون">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description (English) *</label>
                <textarea name="description_en" class="form-textarea" required placeholder="Describe your project in detail...">{{ old('description_en') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Description (Arabic)</label>
                    <textarea name="description_ar" class="form-textarea" placeholder="صف المشروع..." dir="rtl">{{ old('description_ar') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Kurdish)</label>
                    <textarea name="description_ku" class="form-textarea" placeholder="پڕۆژەکە باس بکە...">{{ old('description_ku') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Project Details -->
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-building"></i> Project Details</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Project Type *</label>
                    <select name="project_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="residential">Residential</option>
                        <option value="commercial">Commercial</option>
                        <option value="mixed_use">Mixed Use</option>
                        <option value="industrial">Industrial</option>
                        <option value="retail">Retail</option>
                        <option value="office">Office</option>
                        <option value="hospitality">Hospitality</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="planning">Planning</option>
                        <option value="under_construction">Under Construction</option>
                        <option value="completed">Completed</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Total Units *</label>
                    <input type="number" name="total_units" class="form-input" value="{{ old('total_units') }}" required min="1" placeholder="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Available Units *</label>
                    <input type="number" name="available_units" class="form-input" value="{{ old('available_units') }}" required min="0" placeholder="75">
                </div>
                <div class="form-group">
                    <label class="form-label">Total Floors</label>
                    <input type="number" name="total_floors" class="form-input" value="{{ old('total_floors') }}" min="1" placeholder="10">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Total Area (m²)</label>
                    <input type="number" name="total_area" class="form-input" value="{{ old('total_area') }}" min="0" step="0.01" placeholder="50000">
                </div>
                <div class="form-group">
                    <label class="form-label">Built Area (m²)</label>
                    <input type="number" name="built_area" class="form-input" value="{{ old('built_area') }}" min="0" step="0.01" placeholder="35000">
                </div>
                <div class="form-group">
                    <label class="form-label">Buildings Count</label>
                    <input type="number" name="buildings_count" class="form-input" value="{{ old('buildings_count') }}" min="1" placeholder="5">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Developer/Contractor</label>
                    <input type="text" name="contractor" class="form-input" value="{{ old('contractor') }}" placeholder="Company Name">
                </div>
                <div class="form-group">
                    <label class="form-label">Architect</label>
                    <input type="text" name="architect" class="form-input" value="{{ old('architect') }}" placeholder="Architect Name">
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-dollar-sign"></i> Pricing</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Min Price (IQD) *</label>
                    <input type="number" name="min_price" class="form-input" value="{{ old('min_price') }}" required min="0" step="0.01" placeholder="50000000">
                </div>
                <div class="form-group">
                    <label class="form-label">Max Price (IQD) *</label>
                    <input type="number" name="max_price" class="form-input" value="{{ old('max_price') }}" required min="0" step="0.01" placeholder="200000000">
                </div>
                <div class="form-group">
                    <label class="form-label">Currency</label>
                    <select name="pricing_currency" class="form-select">
                        <option value="IQD" selected>IQD</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-calendar"></i> Important Dates</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Launch Date</label>
                    <input type="date" name="launch_date" class="form-input" value="{{ old('launch_date') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Construction Start</label>
                    <input type="date" name="construction_start_date" class="form-input" value="{{ old('construction_start_date') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Expected Completion</label>
                    <input type="date" name="expected_completion_date" class="form-input" value="{{ old('expected_completion_date') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Handover Date</label>
                    <input type="date" name="handover_date" class="form-input" value="{{ old('handover_date') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Completion Year</label>
                    <input type="number" name="completion_year" class="form-input" value="{{ old('completion_year') }}" min="{{ date('Y') }}" max="{{ date('Y') + 20 }}" placeholder="{{ date('Y') + 2 }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Completion % *</label>
                    <input type="number" name="completion_percentage" class="form-input" value="{{ old('completion_percentage', 0) }}" required min="0" max="100" placeholder="0">
                </div>
            </div>
        </div>

        <!-- Location -->
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Location</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">City (English) *</label>
                    <input type="text" name="city_en" class="form-input" value="{{ old('city_en', auth('office')->user()->city) }}" required placeholder="Erbil">
                </div>
                <div class="form-group">
                    <label class="form-label">District (English) *</label>
                    <input type="text" name="district_en" class="form-input" value="{{ old('district_en', auth('office')->user()->district) }}" required placeholder="Downtown">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">City (Arabic)</label>
                    <input type="text" name="city_ar" class="form-input" value="{{ old('city_ar') }}" placeholder="أربيل" dir="rtl">
                </div>
                <div class="form-group">
                    <label class="form-label">District (Arabic)</label>
                    <input type="text" name="district_ar" class="form-input" value="{{ old('district_ar') }}" placeholder="وسط المدينة" dir="rtl">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Full Address *</label>
                <input type="text" name="full_address" class="form-input" value="{{ old('full_address') }}" required placeholder="Street name, area...">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Latitude *</label>
                    <input type="number" name="latitude" class="form-input" value="{{ old('latitude', '36.1911') }}" required step="0.000001" placeholder="36.1911">
                </div>
                <div class="form-group">
                    <label class="form-label">Longitude *</label>
                    <input type="number" name="longitude" class="form-input" value="{{ old('longitude', '44.0091') }}" required step="0.000001" placeholder="44.0091">
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-images"></i> Project Images</h3>
            <div class="form-helper" style="margin-bottom: 16px;">Upload 3-10 high-quality images (Max 5MB each)</div>

            <div class="image-upload-zone" onclick="document.getElementById('images').click()">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div style="font-size: 16px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Click to Upload Images</div>
                <div style="font-size: 14px; color: var(--text-muted);">or drag and drop</div>
            </div>
            <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;" onchange="previewImages(event)">

            <div class="image-preview-grid" id="imagePreviewGrid"></div>
        </div>

        <!-- Features -->
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-star"></i> Features</h3>

            <div class="form-row">
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_featured" value="1" style="width: 20px; height: 20px;">
                        <span class="form-label" style="margin: 0;">Featured Project</span>
                    </label>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_premium" value="1" style="width: 20px; height: 20px;">
                        <span class="form-label" style="margin: 0;">Premium Project</span>
                    </label>
                </div>

            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('office.projects') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-check-circle"></i> Create Project
            </button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    let selectedFiles = [];

    function previewImages(event) {
        const files = Array.from(event.target.files);
        selectedFiles = files.slice(0, 10);

        const grid = document.getElementById('imagePreviewGrid');
        grid.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <button type="button" class="remove-preview-btn" onclick="removeImage(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                grid.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }

    function removeImage(index) {
        selectedFiles.splice(index, 1);
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        document.getElementById('images').files = dt.files;
        previewImages({ target: { files: selectedFiles } });
    }
</script>
@endsection
