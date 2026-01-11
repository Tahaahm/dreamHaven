@extends('layouts.agent-layout')

@section('title', 'Edit Profile - Dream Mulk')

@section('styles')
<style>
    .profile-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        background: linear-gradient(135deg, #303b97 0%, #1e2875 100%);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        color: white;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .page-subtitle {
        font-size: 14px;
        opacity: 0.9;
    }

    .form-container {
        background: white;
        border-radius: 16px;
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
        margin-bottom: 0;
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

    .form-label .restricted {
        color: #64748b;
        font-size: 12px;
        font-weight: 500;
        margin-left: 8px;
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

    .form-input:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .form-textarea {
        min-height: 120px;
        resize: vertical;
        font-family: inherit;
    }

    .image-upload-section {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    .image-upload-box {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s;
        cursor: pointer;
    }

    .image-upload-box:hover {
        border-color: #303b97;
        background: #f1f5f9;
    }

    .image-preview {
        width: 120px;
        height: 120px;
        margin: 0 auto 16px;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid #303b97;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .bio-image-preview {
        width: 100%;
        height: 160px;
        margin: 0 auto 16px;
        border-radius: 12px;
        overflow: hidden;
        border: 3px solid #303b97;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
    }

    .bio-image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .upload-icon {
        width: 56px;
        height: 56px;
        background: #303b97;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin: 0 auto 12px;
    }

    .upload-text {
        font-size: 14px;
        color: #1f2937;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .upload-hint {
        font-size: 12px;
        color: #64748b;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
        margin-top: 24px;
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

    .info-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 24px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .info-box i {
        color: #3b82f6;
        font-size: 20px;
        margin-top: 2px;
    }

    .info-box-text {
        font-size: 13px;
        color: #1e40af;
        line-height: 1.5;
    }

    .map-container {
        width: 100%;
        height: 400px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
        margin-bottom: 0;
    }

    #map {
        width: 100%;
        height: 100%;
    }

    @media (max-width: 768px) {
        .form-grid,
        .image-upload-section {
            grid-template-columns: 1fr;
        }

        .profile-container {
            padding: 16px;
        }
    }
</style>
@endsection

@section('content')
<div class="profile-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-user-edit"></i> Edit Profile
        </h1>
        <p class="page-subtitle">Update your personal information and profile images</p>
    </div>

    <form action="{{ route('agent.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-container">
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <div class="info-box-text">
                    <strong>Note:</strong> Some fields like email address and verification status can only be changed by administrators for security reasons.
                </div>
            </div>

            <!-- Profile Images -->
            <div class="form-section">
                <h3 class="section-title">Profile Images</h3>

                <div class="image-upload-section">
                    <div>
                        <label class="form-label">Profile Picture</label>
                        <div class="image-upload-box" onclick="document.getElementById('profileImageInput').click()">
                            <div class="image-preview" id="profilePreview">
                                @if($agent->profile_image)
                                    <img src="{{ $agent->profile_image }}" alt="Profile">
                                @else
                                    <div class="upload-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="upload-text">Click to upload</div>
                            <div class="upload-hint">JPG, PNG (Max 2MB)</div>
                            <input type="file" id="profileImageInput" name="profile_image" accept="image/*" hidden>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Bio/Cover Image</label>
                        <div class="image-upload-box" onclick="document.getElementById('bioImageInput').click()">
                            <div class="bio-image-preview" id="bioPreview">
                                @if($agent->bio_image)
                                    <img src="{{ $agent->bio_image }}" alt="Bio">
                                @else
                                    <div class="upload-icon">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="upload-text">Click to upload</div>
                            <div class="upload-hint">JPG, PNG (Max 2MB)</div>
                            <input type="file" id="bioImageInput" name="bio_image" accept="image/*" hidden>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="form-section">
                <h3 class="section-title">Basic Information</h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name<span class="required">*</span></label>
                        <input type="text" name="agent_name" class="form-input" value="{{ $agent->agent_name }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Email Address
                            <span class="restricted">(Admin only)</span>
                        </label>
                        <input type="email" class="form-input" value="{{ $agent->primary_email }}" disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number<span class="required">*</span></label>
                        <input type="text" name="primary_phone" class="form-input" value="{{ $agent->primary_phone }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-input" value="{{ $agent->whatsapp_number }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">City<span class="required">*</span></label>
                        <select name="city" class="form-select" required>
                            <option value="">Select City</option>
                            <option value="Erbil" {{ $agent->city == 'Erbil' ? 'selected' : '' }}>Erbil</option>
                            <option value="Sulaymaniyah" {{ $agent->city == 'Sulaymaniyah' ? 'selected' : '' }}>Sulaymaniyah</option>
                            <option value="Duhok" {{ $agent->city == 'Duhok' ? 'selected' : '' }}>Duhok</option>
                            <option value="Baghdad" {{ $agent->city == 'Baghdad' ? 'selected' : '' }}>Baghdad</option>
                            <option value="Basra" {{ $agent->city == 'Basra' ? 'selected' : '' }}>Basra</option>
                            <option value="Mosul" {{ $agent->city == 'Mosul' ? 'selected' : '' }}>Mosul</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">District</label>
                        <input type="text" name="district" class="form-input" value="{{ $agent->district }}">
                    </div>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="form-section">
                <h3 class="section-title">Professional Information</h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number" class="form-input" value="{{ $agent->license_number }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Years of Experience</label>
                        <input type="number" name="years_experience" class="form-input" min="0" value="{{ $agent->years_experience }}">
                    </div>

                    <div class="form-group form-grid-full">
                        <label class="form-label">Office Address</label>
                        <input type="text" name="office_address" class="form-input" value="{{ $agent->office_address }}">
                    </div>

                    <div class="form-group form-grid-full">
                        <label class="form-label">About Me / Bio (Max 1000 characters)</label>
                        <textarea name="agent_bio" class="form-textarea" maxlength="1000">{{ $agent->agent_bio }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Office Location
                </h3>

                <div class="info-box" style="background: #fef3c7; border-color: #fcd34d;">
                    <i class="fas fa-map-marked-alt" style="color: #f59e0b;"></i>
                    <div class="info-box-text" style="color: #92400e;">
                        <strong>Set Your Office Location:</strong> Click on the map or drag the marker to set your exact office location. This helps clients find you easily.
                    </div>
                </div>

                <div class="map-container">
                    <div id="map"></div>
                </div>

                <div class="form-grid" style="margin-top: 16px;">
                    <div class="form-group">
                        <label class="form-label">Latitude</label>
                        <input type="number" name="latitude" id="latitude" class="form-input" step="0.0000001" value="{{ $agent->latitude ?? 36.1911 }}" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Longitude</label>
                        <input type="number" name="longitude" id="longitude" class="form-input" step="0.0000001" value="{{ $agent->longitude ?? 44.0091 }}" readonly>
                    </div>
                </div>
            </div>

            <!-- Restricted Fields Info -->
            <div class="form-section">
                <h3 class="section-title">Restricted Information</h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            Verification Status
                            <span class="restricted">(Admin only)</span>
                        </label>
                        <input type="text" class="form-input"
                               value="{{ $agent->is_verified ? 'Verified ✓' : 'Pending Verification' }}"
                               disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Properties Sold
                            <span class="restricted">(Auto-calculated)</span>
                        </label>
                        <input type="text" class="form-input" value="{{ $agent->properties_sold ?? 0 }}" disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Overall Rating
                            <span class="restricted">(Auto-calculated)</span>
                        </label>
                        <input type="text" class="form-input" value="{{ number_format($agent->overall_rating ?? 0, 1) }}" disabled>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('agent.profile', $agent->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>

<script>
let map, marker;
const initialLat = {{ $agent->latitude ?? 36.1911 }};
const initialLng = {{ $agent->longitude ?? 44.0091 }};

// Initialize Google Map
function initMap() {
    const initialPosition = { lat: initialLat, lng: initialLng };

    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 15,
        center: initialPosition,
        styles: [
            {
                "featureType": "water",
                "elementType": "geometry",
                "stylers": [{"color": "#e9e9e9"}, {"lightness": 17}]
            },
            {
                "featureType": "landscape",
                "elementType": "geometry",
                "stylers": [{"color": "#f5f5f5"}, {"lightness": 20}]
            }
        ]
    });

    marker = new google.maps.Marker({
        position: initialPosition,
        map: map,
        draggable: true,
        animation: google.maps.Animation.DROP,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 12,
            fillColor: "#303b97",
            fillOpacity: 1,
            strokeWeight: 4,
            strokeColor: "#ffffff"
        }
    });

    // Update coordinates on marker drag
    google.maps.event.addListener(marker, 'dragend', function(event) {
        updateCoordinates(event.latLng.lat(), event.latLng.lng());
    });

    // Update coordinates on map click
    map.addListener('click', function(event) {
        marker.setPosition(event.latLng);
        updateCoordinates(event.latLng.lat(), event.latLng.lng());
    });
}

function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(7);
    document.getElementById('longitude').value = lng.toFixed(7);
}

// Profile image preview
document.getElementById('profileImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').innerHTML = `<img src="${e.target.result}" alt="Profile">`;
        };
        reader.readAsDataURL(file);
    }
});

// Bio image preview
document.getElementById('bioImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('bioPreview').innerHTML = `<img src="${e.target.result}" alt="Bio">`;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
