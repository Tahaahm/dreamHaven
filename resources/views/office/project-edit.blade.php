@extends('layouts.office-layout')

@section('title', 'Edit Project - Dream Mulk')
@section('search-placeholder', 'Search...')

@section('styles')
<style>
    .page-header { margin-bottom: 32px; }
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
    .page-subtitle { color: var(--text-muted); font-size: 15px; }
    .back-btn { padding: 10px 20px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-secondary); text-decoration: none; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 24px; }
    .back-btn:hover { border-color: #6366f1; color: #6366f1; }

    .project-preview { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 24px; margin-bottom: 32px; }
    .preview-grid { display: grid; grid-template-columns: 300px 1fr; gap: 24px; }
    .main-image { width: 100%; height: 200px; border-radius: 12px; overflow: hidden; margin-bottom: 12px; }
    .main-image img { width: 100%; height: 100%; object-fit: cover; }

    .project-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .stat-box { background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; text-align: center; }
    .stat-icon { font-size: 24px; color: #6366f1; margin-bottom: 8px; }
    .stat-label { font-size: 13px; color: var(--text-muted); margin-bottom: 6px; }
    .stat-value { font-size: 20px; font-weight: 700; color: var(--text-primary); }

    .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; }
    .form-section { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--border-color); }
    .form-section:last-of-type { border-bottom: none; }
    .section-title { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; font-size: 14px; }
    .form-input, .form-select, .form-textarea { width: 100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 15px; background: var(--bg-main); color: var(--text-primary); transition: all 0.3s; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
    .form-textarea { resize: vertical; min-height: 120px; font-family: inherit; }

    .current-images { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px; }
    .current-image { position: relative; height: 120px; border-radius: 10px; overflow: hidden; }
    .current-image img { width: 100%; height: 100%; object-fit: cover; }
    .remove-image-btn { position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: rgba(220,38,38,0.9); border: none; border-radius: 6px; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
    .remove-image-btn:hover { background: #dc2626; transform: scale(1.1); }

    .image-upload-zone { border: 2px dashed var(--border-color); border-radius: 12px; padding: 32px; text-align: center; background: var(--bg-main); cursor: pointer; transition: all 0.3s; }
    .image-upload-zone:hover { border-color: #6366f1; background: rgba(99,102,241,0.05); }
    .upload-icon { font-size: 48px; color: var(--text-muted); margin-bottom: 16px; }

    .images-section-title { font-size: 14px; font-weight: 600; color: var(--text-secondary); margin: 20px 0 12px 0; }

    .form-actions { display: flex; gap: 12px; justify-content: flex-end; padding-top: 24px; border-top: 2px solid var(--border-color); }
    .btn { padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s; border: none; display: flex; align-items: center; gap: 8px; }
    .btn-primary { background: #6366f1; color: white; }
    .btn-primary:hover { background: #5558e3; transform: translateY(-2px); }
    .btn-danger { background: #dc2626; color: white; }
    .btn-danger:hover { background: #b91c1c; transform: translateY(-2px); }
    .btn-secondary { background: transparent; color: var(--text-primary); border: 1px solid var(--border-color); text-decoration: none; }
    .btn-secondary:hover { border-color: #6366f1; color: #6366f1; }
</style>
@endsection

@section('content')
<a href="{{ route('office.projects') }}" class="back-btn">
    <i class="fas fa-arrow-left"></i> Back to Projects
</a>

<div class="page-header">
    <h1 class="page-title">Edit Project</h1>
    <p class="page-subtitle">Project ID: {{ $project->id }}</p>
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

<!-- Project Preview -->
<div class="project-preview">
    <div class="preview-grid">
        <div>
            @php
                $images = is_array($project->images) ? $project->images : json_decode($project->images, true);
                $mainImage = is_array($images) && count($images) > 0 ? $images[0] : 'https://via.placeholder.com/300x200';
                $name = is_array($project->name) ? $project->name : json_decode($project->name, true);
            @endphp
            <div class="main-image">
                <img src="{{ $mainImage }}" alt="Project">
            </div>
        </div>

        <div>
            <h2 style="font-size: 24px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px;">
                {{ $name['en'] ?? 'N/A' }}
            </h2>

            <div class="project-stats">
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-building"></i></div>
                    <div class="stat-label">Total Units</div>
                    <div class="stat-value">{{ $project->total_units ?? 0 }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-label">Available</div>
                    <div class="stat-value">{{ $project->available_units ?? 0 }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-home"></i></div>
                    <div class="stat-label">Properties</div>
                    <div class="stat-value">{{ $project->properties_count ?? 0 }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                    <div class="stat-label">Completion</div>
                    <div class="stat-value">{{ $project->completion_percentage ?? 0 }}%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Form -->
<form action="{{ route('office.project.update', $project->id) }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf
    @method('PUT')

    <!-- Basic Information -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h3>

        <div class="form-group">
            <label class="form-label">Project Name (English) *</label>
            <input type="text" name="name_en" class="form-input" value="{{ $name['en'] ?? '' }}" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Project Name (Arabic)</label>
                <input type="text" name="name_ar" class="form-input" value="{{ $name['ar'] ?? '' }}" dir="rtl">
            </div>
            <div class="form-group">
                <label class="form-label">Project Name (Kurdish)</label>
                <input type="text" name="name_ku" class="form-input" value="{{ $name['ku'] ?? '' }}">
            </div>
        </div>

        @php
            $description = is_array($project->description) ? $project->description : json_decode($project->description, true);
        @endphp

        <div class="form-group">
            <label class="form-label">Description (English) *</label>
            <textarea name="description_en" class="form-textarea" required>{{ $description['en'] ?? '' }}</textarea>
        </div>

        <div class="form-row">
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

    <!-- Project Details -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-building"></i> Project Details</h3>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Project Type *</label>
                <select name="project_type" class="form-select" required>
                    <option value="residential" {{ $project->project_type == 'residential' ? 'selected' : '' }}>Residential</option>
                    <option value="commercial" {{ $project->project_type == 'commercial' ? 'selected' : '' }}>Commercial</option>
                    <option value="mixed_use" {{ $project->project_type == 'mixed_use' ? 'selected' : '' }}>Mixed Use</option>
                    <option value="industrial" {{ $project->project_type == 'industrial' ? 'selected' : '' }}>Industrial</option>
                    <option value="retail" {{ $project->project_type == 'retail' ? 'selected' : '' }}>Retail</option>
                    <option value="office" {{ $project->project_type == 'office' ? 'selected' : '' }}>Office</option>
                    <option value="hospitality" {{ $project->project_type == 'hospitality' ? 'selected' : '' }}>Hospitality</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select" required>
                    <option value="planning" {{ $project->status == 'planning' ? 'selected' : '' }}>Planning</option>
                    <option value="under_construction" {{ $project->status == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                    <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="delivered" {{ $project->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Total Units *</label>
                <input type="number" name="total_units" class="form-input" value="{{ $project->total_units ?? 0 }}" required min="1">
            </div>
            <div class="form-group">
                <label class="form-label">Available Units *</label>
                <input type="number" name="available_units" class="form-input" value="{{ $project->available_units ?? 0 }}" required min="0">
            </div>
            <div class="form-group">
                <label class="form-label">Total Floors</label>
                <input type="number" name="total_floors" class="form-input" value="{{ $project->total_floors ?? '' }}" min="1">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Total Area (m²)</label>
                <input type="number" name="total_area" class="form-input" value="{{ $project->total_area ?? '' }}" min="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Built Area (m²)</label>
                <input type="number" name="built_area" class="form-input" value="{{ $project->built_area ?? '' }}" min="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Buildings Count</label>
                <input type="number" name="buildings_count" class="form-input" value="{{ $project->buildings_count ?? '' }}" min="1">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Developer/Contractor</label>
                <input type="text" name="contractor" class="form-input" value="{{ $project->contractor ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Architect</label>
                <input type="text" name="architect" class="form-input" value="{{ $project->architect ?? '' }}">
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-dollar-sign"></i> Pricing</h3>

        @php
            $priceRange = is_array($project->price_range) ? $project->price_range : json_decode($project->price_range, true);
        @endphp

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Min Price (IQD) *</label>
                <input type="number" name="min_price" class="form-input" value="{{ $priceRange['min'] ?? 0 }}" required min="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Max Price (IQD) *</label>
                <input type="number" name="max_price" class="form-input" value="{{ $priceRange['max'] ?? 0 }}" required min="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Currency</label>
                <select name="pricing_currency" class="form-select">
                    <option value="IQD" {{ $project->pricing_currency == 'IQD' ? 'selected' : '' }}>IQD</option>
                    <option value="USD" {{ $project->pricing_currency == 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="EUR" {{ $project->pricing_currency == 'EUR' ? 'selected' : '' }}>EUR</option>
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
                <input type="date" name="launch_date" class="form-input" value="{{ $project->launch_date ? $project->launch_date->format('Y-m-d') : '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Construction Start</label>
                <input type="date" name="construction_start_date" class="form-input" value="{{ $project->construction_start_date ? $project->construction_start_date->format('Y-m-d') : '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Expected Completion</label>
                <input type="date" name="expected_completion_date" class="form-input" value="{{ $project->expected_completion_date ? $project->expected_completion_date->format('Y-m-d') : '' }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Handover Date</label>
                <input type="date" name="handover_date" class="form-input" value="{{ $project->handover_date ? $project->handover_date->format('Y-m-d') : '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Completion Year</label>
                <input type="number" name="completion_year" class="form-input" value="{{ $project->completion_year ?? '' }}" min="{{ date('Y') }}" max="{{ date('Y') + 20 }}">
            </div>
            <div class="form-group">
                <label class="form-label">Completion % *</label>
                <input type="number" name="completion_percentage" class="form-input" value="{{ $project->completion_percentage ?? 0 }}" required min="0" max="100">
            </div>
        </div>
    </div>

    <!-- Location -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Location</h3>

        @php
            $addressDetails = is_array($project->address_details) ? $project->address_details : json_decode($project->address_details, true);
        @endphp

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">City (English) *</label>
                <input type="text" name="city_en" class="form-input" value="{{ $addressDetails['city']['en'] ?? '' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">District (English) *</label>
                <input type="text" name="district_en" class="form-input" value="{{ $addressDetails['district']['en'] ?? '' }}" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">City (Arabic)</label>
                <input type="text" name="city_ar" class="form-input" value="{{ $addressDetails['city']['ar'] ?? '' }}" dir="rtl">
            </div>
            <div class="form-group">
                <label class="form-label">District (Arabic)</label>
                <input type="text" name="district_ar" class="form-input" value="{{ $addressDetails['district']['ar'] ?? '' }}" dir="rtl">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Full Address *</label>
            <input type="text" name="full_address" class="form-input" value="{{ $project->full_address ?? '' }}" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Latitude *</label>
                <input type="number" name="latitude" class="form-input" value="{{ $project->latitude ?? '' }}" required step="0.000001">
            </div>
            <div class="form-group">
                <label class="form-label">Longitude *</label>
                <input type="number" name="longitude" class="form-input" value="{{ $project->longitude ?? '' }}" required step="0.000001">
            </div>
        </div>
    </div>

    <!-- Images -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-images"></i> Project Images</h3>

        @if(is_array($images) && count($images) > 0)
            <div class="images-section-title">Current Images</div>
            <div class="current-images" id="existingImagesGrid">
                @foreach($images as $index => $image)
                    <div class="current-image" id="existing-img-{{ $index }}">
                        <img src="{{ $image }}" alt="Image {{ $index + 1 }}">
                        <button type="button" class="remove-image-btn" onclick="removeExistingImage({{ $index }})" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="image-upload-zone" onclick="document.getElementById('imageUpload').click()">
            <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div style="font-size: 16px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Click to upload new images</div>
            <div style="font-size: 14px; color: var(--text-muted);">JPG, PNG, GIF (Max: 5MB each)</div>
            <input type="file" id="imageUpload" name="images[]" multiple accept="image/*" style="display: none;" onchange="previewNewImages(event)">
        </div>

        <div id="newImagesContainer" style="display: none;">
            <div class="images-section-title">New Images to Upload</div>
            <div class="current-images" id="newImagesGrid"></div>
        </div>

        <input type="hidden" name="remove_images" id="removeImagesInput" value="">
    </div>

    <!-- Features -->
    <div class="form-section">
        <h3 class="section-title"><i class="fas fa-star"></i> Features</h3>

        <div class="form-row">
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" {{ $project->is_featured ? 'checked' : '' }} style="width: 20px; height: 20px;">
                    <span class="form-label" style="margin: 0;">Featured Project</span>
                </label>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_premium" value="1" {{ $project->is_premium ? 'checked' : '' }} style="width: 20px; height: 20px;">
                    <span class="form-label" style="margin: 0;">Premium Project</span>
                </label>
            </div>

        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <a href="{{ route('office.projects') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
        <button type="button" class="btn btn-danger" onclick="if(confirm('Delete this project?')) document.getElementById('deleteForm').submit()">
            <i class="fas fa-trash"></i> Delete
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </div>
</form>

<!-- Hidden Delete Form -->
<form id="deleteForm" action="{{ route('office.project.delete', $project->id) }}" method="POST" style="display: none;">
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
