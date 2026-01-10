<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Property Listing Form</title>
 
<body class="black-navbar">
@include('layouts.sidebar')

<!-- Main Content Wrapper with Sidebar Offset -->
<div class="main-content-wrapper">
    <div class="container-fluid px-4 py-5">
        <div class="row justify-content-center">
            <div class="col-xl-11 col-lg-12">
            <!-- Header -->
            <div class="mb-5">
                <h1 class="display-5 fw-bold text-dark mb-2">Edit Property</h1>
                <p class="text-muted">Update your property details and manage images</p>
            </div>

            <!-- Alert Messages -->
            <div id="error-messages"></div>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('property.update', $property->id) }}" method="POST" enctype="multipart/form-data" id="propertyForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="removed_photos" name="removed_photos">

                <!-- Owner Information Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Owner Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="owner_id" class="form-label fw-semibold">Owner ID</label>
                                <input type="text" class="form-control form-control-lg" id="owner_id" name="owner_id" 
                                       value="{{ old('owner_id', $property->owner_id) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="owner_type" class="form-label fw-semibold">Owner Type</label>
                                <select class="form-select form-select-lg" id="owner_type" name="owner_type" required>
                                    <option value="User" {{ old('owner_type', $property->owner_type) === 'User' ? 'selected' : '' }}>User</option>
                                    <option value="Agent" {{ old('owner_type', $property->owner_type) === 'Agent' ? 'selected' : '' }}>Agent</option>
                                    <option value="RealEstateOffice" {{ old('owner_type', $property->owner_type) === 'RealEstateOffice' ? 'selected' : '' }}>Real Estate Office</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Title Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-house-door me-2"></i>Property Title (Multilingual)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="name_en" class="form-label fw-semibold">Title (English) *</label>
                                <input type="text" class="form-control form-control-lg" id="name_en" name="name[en]" 
                                       value="{{ old('name.en', $property->name['en'] ?? '') }}" required placeholder="Enter English title">
                            </div>
                            <div class="col-md-4">
                                <label for="name_ar" class="form-label fw-semibold">Title (Arabic)</label>
                                <input type="text" class="form-control form-control-lg" id="name_ar" name="name[ar]" 
                                       value="{{ old('name.ar', $property->name['ar'] ?? '') }}" placeholder="أدخل العنوان بالعربية" dir="rtl">
                            </div>
                            <div class="col-md-4">
                                <label for="name_ku" class="form-label fw-semibold">Title (Kurdish)</label>
                                <input type="text" class="form-control form-control-lg" id="name_ku" name="name[ku]" 
                                       value="{{ old('name.ku', $property->name['ku'] ?? '') }}" placeholder="ناونیشان بە کوردی بنووسە">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-info text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-card-text me-2"></i>Property Description</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="description_en" class="form-label fw-semibold">Description (English)</label>
                                <textarea class="form-control" id="description_en" name="description[en]" rows="4" placeholder="Enter detailed description">{{ old('description.en', $property->description['en'] ?? '') }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="description_ar" class="form-label fw-semibold">Description (Arabic)</label>
                                <textarea class="form-control" id="description_ar" name="description[ar]" rows="4" placeholder="أدخل الوصف التفصيلي" dir="rtl">{{ old('description.ar', $property->description['ar'] ?? '') }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="description_ku" class="form-label fw-semibold">Description (Kurdish)</label>
                                <textarea class="form-control" id="description_ku" name="description[ku]" rows="4" placeholder="وەسفی تەواو بنووسە">{{ old('description.ku', $property->description['ku'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Details Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-warning text-dark py-3">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Property Details</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="type_category" class="form-label fw-semibold">Category *</label>
                                <input type="text" class="form-control form-control-lg" id="type_category" name="type[category]" 
                                       value="{{ old('type.category', $property->type['category'] ?? '') }}" required placeholder="e.g., Apartment">
                            </div>
                            <div class="col-md-3">
                                <label for="area" class="form-label fw-semibold">Area (m²) *</label>
                                <input type="number" class="form-control form-control-lg" id="area" name="area" 
                                       value="{{ old('area', $property->area ?? '') }}" required placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label for="bedroom_count" class="form-label fw-semibold">Bedrooms</label>
                                <input type="number" class="form-control form-control-lg" id="bedroom_count" name="rooms[bedroom][count]" 
                                       value="{{ old('rooms.bedroom.count', $property->rooms['bedroom']['count'] ?? '') }}" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label for="bathroom_count" class="form-label fw-semibold">Bathrooms</label>
                                <input type="number" class="form-control form-control-lg" id="bathroom_count" name="rooms[bathroom][count]" 
                                       value="{{ old('rooms.bathroom.count', $property->rooms['bathroom']['count'] ?? '') }}" placeholder="0">
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <label for="furnished" class="form-label fw-semibold">Furnished</label>
                                <select class="form-select form-select-lg" id="furnished" name="furnished" required>
                                    <option value="1" {{ old('furnished', $property->furnished ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('furnished', $property->furnished ?? 0) == 0 ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Utilities</label>
                                <div class="d-flex gap-3 mt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="electricity" name="electricity" value="1" {{ old('electricity', $property->electricity ?? 0) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="electricity">Electricity</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="water" name="water" value="1" {{ old('water', $property->water ?? 0) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="water">Water</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="internet" name="internet" value="1" {{ old('internet', $property->internet ?? 0) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="internet">Internet</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Listing Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-danger text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pricing & Listing Type</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="price_usd" class="form-label fw-semibold">Price (USD) *</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price_usd" name="price[usd]" 
                                           value="{{ old('price.usd', $property->price['usd'] ?? '') }}" required placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="price_iqd" class="form-label fw-semibold">Price (IQD)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">IQD</span>
                                    <input type="number" class="form-control" id="price_iqd" name="price[iqd]" 
                                           value="{{ old('price.iqd', $property->price['iqd'] ?? '') }}" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="listing_type" class="form-label fw-semibold">Listing Type *</label>
                                <select class="form-select form-select-lg" id="listing_type" name="listing_type" required>
                                    <option value="rent" {{ old('listing_type', $property->listing_type ?? '') == 'rent' ? 'selected' : '' }}>For Rent</option>
                                    <option value="sell" {{ old('listing_type', $property->listing_type ?? '') == 'sell' ? 'selected' : '' }}>For Sale</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="rental_period" class="form-label fw-semibold">Rental Period</label>
                                <select class="form-select form-select-lg" id="rental_period" name="rental_period">
                                    <option value="">Not Applicable</option>
                                    <option value="monthly" {{ old('rental_period', $property->rental_period ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ old('rental_period', $property->rental_period ?? '') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media Links Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-secondary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-play-circle me-2"></i>Media & Virtual Tours</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="virtual_tour_url" class="form-label fw-semibold">Virtual Tour URL</label>
                                <input type="url" class="form-control form-control-lg" id="virtual_tour_url" name="virtual_tour_url" 
                                       value="{{ old('virtual_tour_url', $property->virtual_tour_url ?? '') }}" placeholder="https://example.com/tour">
                            </div>
                            <div class="col-md-6">
                                <label for="floor_plan_url" class="form-label fw-semibold">Floor Plan URL</label>
                                <input type="url" class="form-control form-control-lg" id="floor_plan_url" name="floor_plan_url" 
                                       value="{{ old('floor_plan_url', $property->floor_plan_url ?? '') }}" placeholder="https://example.com/floorplan">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Management Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-images me-2"></i>Property Images</h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Existing Images -->
                        <div class="mb-4" id="existingImagesSection">
                            <h6 class="text-muted mb-3">Current Images</h6>
                            <div class="row g-3" id="existingImagesContainer">
                                @php
                                    $images = is_string($property->images) ? json_decode($property->images, true) : $property->images;
                                @endphp
                                @foreach ($images as $index => $photo)
                                <div class="col-md-3" data-photo="{{ $photo }}">
                                    <div class="position-relative image-preview-card">
                                        <img src="{{ asset($photo) }}" alt="Property Image" class="img-fluid rounded">
                                        <div class="image-overlay">
                                            <button type="button" class="btn btn-danger btn-sm remove-existing-image" data-photo="{{ $photo }}">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                        <div class="image-number-badge">{{ $index + 1 }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Upload New Images -->
                        <div>
                            <h6 class="text-muted mb-3">Upload New Images</h6>
                            <div id="drop-zone" class="drop-zone-modern">
                                <div class="drop-zone-content">
                                    <i class="bi bi-cloud-upload display-1 text-primary mb-3"></i>
                                    <h5>Drag & Drop Images Here</h5>
                                    <p class="text-muted mb-3">or click to browse</p>
                                    <button type="button" class="btn btn-primary btn-lg" onclick="document.getElementById('fileInputButton').click()">
                                        <i class="bi bi-folder2-open me-2"></i>Choose Files
                                    </button>
                                    <input type="file" id="fileInputButton" name="images[]" multiple accept="image/*" style="display: none;">
                                    <p class="text-muted mt-3 mb-0"><small>Max total size: 20MB | Supported formats: JPG, PNG, WEBP</small></p>
                                </div>
                            </div>
                            
                            <!-- New Images Preview -->
                            <div id="preview" class="row g-3 mt-3"></div>
                        </div>
                    </div>
                    <!-- Submit Button Section - Beautiful Floating Footer -->
<div class="submit-section">
    <div class="submit-container">
        <button type="button" class="btn btn-cancel" onclick="window.history.back()">
            <i class="bi bi-x-circle me-2"></i>Cancel Changes
        </button>
        <button type="submit" class="btn btn-submit" id="submitBtn">
            <i class="bi bi-save me-2"></i>Update Property
            <span class="submit-arrow">→</span>
        </button>
    </div>
</div>
                </div>

            </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Main Content Wrapper - Offset for Sidebar */
.main-content-wrapper {
    margin-left: 250px; /* Adjust based on your sidebar width */
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

/* Responsive sidebar offset */
@media (max-width: 991px) {
    .main-content-wrapper {
        margin-left: 0;
    }
}

/* Modern Styling */
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.card {
    border-radius: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border: none;
}

.form-control, .form-select {
    border-radius: 10px;
    border: 2px solid #e0e6ed;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #4361ee;
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.1);
}

/* Drop Zone Styling */
.drop-zone-modern {
    border: 3px dashed #cbd5e1;
    border-radius: 15px;
    padding: 3rem;
    text-align: center;
    transition: all 0.3s ease;
    background: #f8fafc;
    cursor: pointer;
}

.drop-zone-modern:hover, .drop-zone-modern.highlight {
    border-color: #4361ee;
    background: #eff6ff;
    transform: scale(1.01);
}

.drop-zone-content i {
    transition: transform 0.3s ease;
}

.drop-zone-modern:hover .drop-zone-content i {
    transform: scale(1.1);
}

/* Image Preview Cards */
.image-preview-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    background: white;
}

.image-preview-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.image-preview-card:hover img {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-preview-card:hover .image-overlay {
    opacity: 1;
}

.image-number-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.85rem;
}

/* New Preview Items */
.preview-item-modern {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    background: white;
}

.preview-item-modern img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.preview-item-modern .remove-button {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: bold;
}

.preview-item-modern .remove-button:hover {
    background: #dc2626;
    transform: scale(1.1);
}

/* Buttons */
.btn {
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Submit Section - Beautiful Floating Footer */
.submit-section {
    position: sticky;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(255,255,255,0.98) 0%, rgba(255,255,255,0.95) 100%);
    backdrop-filter: blur(10px);
    padding: 1.5rem 0;
    margin-top: 3rem;
    border-top: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
    z-index: 100;
}

.submit-container {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 1rem;
}

.btn-cancel {
    background: #f1f5f9;
    color: #64748b;
    border: 2px solid #e2e8f0;
    padding: 0.875rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #e2e8f0;
    color: #475569;
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.btn-submit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.875rem 2.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1.1rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.btn-submit:hover::before {
    left: 100%;
}

.btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

.btn-submit:active {
    transform: translateY(-1px);
}

.btn-submit .submit-arrow {
    display: inline-block;
    margin-left: 0.5rem;
    transition: transform 0.3s ease;
}

.btn-submit:hover .submit-arrow {
    transform: translateX(5px);
}

.btn-submit:disabled {
    background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
    cursor: not-allowed;
    box-shadow: none;
}

.btn-submit:disabled:hover {
    transform: none;
}

/* Alert Styling */
.alert {
    border-radius: 12px;
    border: none;
}

/* Responsive */
@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem !important;
    }
    
    .drop-zone-modern {
        padding: 2rem 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById("drop-zone");
    const preview = document.getElementById("preview");
    const fileInput = document.getElementById("fileInputButton");
    const errorMessages = document.getElementById("error-messages");
    const removedPhotosInput = document.getElementById("removed_photos");
    const listingTypeSelect = document.getElementById("listing_type");
    const rentalPeriodSelect = document.getElementById("rental_period");

    let totalFileSize = 0;
    let removedPhotos = [];
    let newFiles = [];

    // Show/hide rental period based on listing type
    listingTypeSelect.addEventListener('change', function() {
        rentalPeriodSelect.disabled = this.value !== 'rent';
        if (this.value !== 'rent') {
            rentalPeriodSelect.value = '';
        }
    });
    
    // Initialize on load
    rentalPeriodSelect.disabled = listingTypeSelect.value !== 'rent';

    // Drag & drop events
    dropZone.addEventListener("dragover", (e) => { 
        e.preventDefault(); 
        dropZone.classList.add("highlight"); 
    });
    
    dropZone.addEventListener("dragleave", (e) => { 
        e.preventDefault(); 
        dropZone.classList.remove("highlight"); 
    });
    
    dropZone.addEventListener("drop", (e) => {
        e.preventDefault();
        dropZone.classList.remove("highlight");
        Array.from(e.dataTransfer.files).forEach(file => processFile(file));
    });

    // Click to upload
    dropZone.addEventListener("click", (e) => {
        if (e.target === dropZone || e.target.closest('.drop-zone-content')) {
            fileInput.click();
        }
    });

    // File input change
    fileInput.addEventListener("change", (e) => {
        Array.from(e.target.files).forEach(file => processFile(file));
        fileInput.value = ''; // Reset input
    });

    // Process a single file
    function processFile(file) {
        if (!file.type.startsWith('image/')) {
            displayError("Please upload only image files.");
            return;
        }

        const maxSize = 20 * 1024 * 1024; // 20 MB
        if (totalFileSize + file.size > maxSize) {
            displayError("Total file size exceeds 20 MB. Please choose smaller files.");
            return;
        }
        
        totalFileSize += file.size;
        newFiles.push(file);
        previewFile(file);
    }

    // Preview file in the preview container
    function previewFile(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const col = document.createElement("div");
            col.classList.add("col-md-3");

            const container = document.createElement("div");
            container.classList.add("preview-item-modern");

            const img = document.createElement("img");
            img.src = e.target.result;

            const removeBtn = document.createElement("button");
            removeBtn.type = "button";
            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
            removeBtn.classList.add("remove-button");

            removeBtn.addEventListener("click", () => {
                const index = newFiles.indexOf(file);
                if (index > -1) {
                    newFiles.splice(index, 1);
                }
                preview.removeChild(col);
                totalFileSize -= file.size;
                updateFileInput();
            });

            container.appendChild(img);
            container.appendChild(removeBtn);
            col.appendChild(container);
            preview.appendChild(col);
        };
        reader.readAsDataURL(file);
    }

    // Update file input with current files
    function updateFileInput() {
        const dataTransfer = new DataTransfer();
        newFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    // Display error messages
    function displayError(message) {
        errorMessages.innerHTML = "";
        const div = document.createElement("div");
        div.className = "alert alert-danger alert-dismissible fade show";
        div.innerHTML = `
            <i class="bi bi-exclamation-triangle-fill me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        errorMessages.appendChild(div);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            div.remove();
        }, 5000);
    }

    // Handle removal of existing images
    document.querySelectorAll('.remove-existing-image').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const photoPath = this.getAttribute('data-photo');
            const container = this.closest('[data-photo]');
            
            if (confirm('Are you sure you want to remove this image?')) {
                removedPhotos.push(photoPath);
                removedPhotosInput.value = JSON.stringify(removedPhotos);
                container.style.opacity = '0';
                setTimeout(() => container.remove(), 300);
            }
        });
    });

    // Form submission
    document.getElementById('propertyForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving Changes<span class="submit-arrow">→</span>';
    });
});
</script>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>