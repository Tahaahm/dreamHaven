@extends('layouts.office-layout')

@section('title', 'Profile Settings - Dream Mulk')

@section('styles')
<style>
    .page-title { font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px; }

    .form-card { background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 32px; margin-bottom: 24px; }
    .form-title { font-size: 18px; font-weight: 700; color: #1a202c; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #303b97; display: flex; align-items: center; gap: 10px; }
    .form-title i { color: #303b97; }

    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px; }
    .form-input, .form-textarea, .form-select { width: 100%; background: white; border: 2px solid #e5e7eb; color: #1a202c; border-radius: 10px; padding: 12px 16px; font-size: 15px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-textarea:focus, .form-select:focus { outline: none; border-color: #303b97; box-shadow: 0 0 0 4px rgba(48,59,151,0.1); }
    .form-input:read-only { background: #f1f5f9; color: #64748b; cursor: not-allowed; }
    .form-textarea { resize: vertical; min-height: 120px; }
    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }

    .submit-btn { background: linear-gradient(135deg, #303b97, #1e2875); color: white; padding: 12px 32px; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(48,59,151,0.3); }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(48,59,151,0.4); }

    .alert { padding: 16px; border-radius: 10px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
    .alert-success { background: #d1fae5; border: 2px solid #059669; color: #059669; font-weight: 600; }
    .alert-error { background: #fee2e2; border: 2px solid #ef4444; color: #dc2626; }

    .helper-text { font-size: 13px; color: #64748b; margin-top: 6px; }

    .schedule-grid { display: grid; gap: 12px; margin-top: 12px; }
    .day-row { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; }
    .day-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .day-checkbox { width: 20px; height: 20px; cursor: pointer; accent-color: #303b97; }
    .day-name { font-weight: 600; color: #1a202c; font-size: 15px; }
    .time-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-left: 32px; }
    .time-input-group { display: flex; flex-direction: column; gap: 6px; }
    .time-input-label { font-size: 13px; color: #64748b; font-weight: 600; }
    .time-input { padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 8px; background: white; color: #1a202c; font-size: 14px; }
    .time-input:disabled { opacity: 0.5; cursor: not-allowed; background: #f1f5f9; }

    .image-upload-section { background: #f8fafc; border: 2px dashed #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; }
    .image-preview { width: 150px; height: 150px; border-radius: 12px; object-fit: cover; border: 2px solid #e5e7eb; margin: 0 auto 16px; display: block; }
    .file-input-label { display: inline-block; padding: 10px 20px; background: #303b97; color: white; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s; }
    .file-input-label:hover { background: #1e2875; }
    .file-input-label input[type="file"] { display: none; }

    .map-container { width: 100%; height: 400px; border-radius: 12px; overflow: hidden; border: 2px solid #e5e7eb; margin-top: 10px; }
    #map { width: 100%; height: 100%; }
    .location-info { background: #eff6ff; padding: 12px; border-radius: 8px; margin-top: 10px; border: 1px solid #bfdbfe; font-size: 13px; color: #1e40af; }

    .plan-badge { display: inline-block; padding: 8px 16px; background: #303b97; color: white; border-radius: 8px; font-weight: 700; text-transform: capitalize; }
</style>
@endsection

@section('content')
<h1 class="page-title">Profile Settings</h1>

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <div>
            @foreach($errors->all() as $error)
                <div><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<form action="{{ route('office.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
    @csrf
    @method('PUT')

    <!-- Company Information -->
    <div class="form-card">
        <h2 class="form-title"><i class="fas fa-building"></i> Company Information</h2>

        <div class="form-group">
            <label class="form-label">Company Name *</label>
            <input type="text" name="company_name" class="form-input" value="{{ old('company_name', auth('office')->user()->company_name) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Company Bio</label>
            <textarea name="company_bio" class="form-textarea">{{ old('company_bio', auth('office')->user()->company_bio) }}</textarea>
            <div class="helper-text">Brief description of your company</div>
        </div>

        <div class="form-group">
            <label class="form-label">About Company</label>
            <textarea name="about_company" class="form-textarea">{{ old('about_company', auth('office')->user()->about_company) }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Properties Sold</label>
                <input type="number" name="properties_sold" class="form-input" value="{{ old('properties_sold', auth('office')->user()->properties_sold ?? 0) }}" min="0">
            </div>
            <div class="form-group">
                <label class="form-label">Years Experience</label>
                <input type="number" name="years_experience" class="form-input" value="{{ old('years_experience', auth('office')->user()->years_experience ?? 0) }}" min="0">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Current Plan</label>
            <div>
                <span class="plan-badge">{{ ucfirst(auth('office')->user()->current_plan ?? 'starter') }}</span>
            </div>
            <div class="helper-text">Contact admin to change your plan</div>
        </div>
    </div>

    <!-- Images -->
    <div class="form-card">
        <h2 class="form-title"><i class="fas fa-image"></i> Company Images</h2>

        <div class="form-group">
            <label class="form-label">Profile Image *</label>
            <div class="image-upload-section">
                @php
                    $profileImageUrl = auth('office')->user()->profile_image;
                    if ($profileImageUrl && !str_starts_with($profileImageUrl, 'http')) {
                        $profileImageUrl = asset('storage/' . $profileImageUrl);
                    }
                @endphp
                <img src="{{ $profileImageUrl ?? 'https://via.placeholder.com/150/303b97/ffffff?text=Profile' }}"
                     alt="Profile"
                     class="image-preview"
                     id="profile-preview">
                <label class="file-input-label">
                    <i class="fas fa-upload"></i> Upload Profile Image
                    <input type="file" name="profile_image" accept="image/*" onchange="previewImage(event, 'profile-preview')">
                </label>
                <div class="helper-text" style="margin-top: 10px;">Your company profile photo (Square image, min 400x400px)</div>
            </div>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <label class="form-label">Company Bio Image</label>
            <div class="image-upload-section">
                @php
                    $bioImageUrl = auth('office')->user()->company_bio_image;
                    if ($bioImageUrl && !str_starts_with($bioImageUrl, 'http')) {
                        $bioImageUrl = asset('storage/' . $bioImageUrl);
                    }
                @endphp
                <img src="{{ $bioImageUrl ?? 'https://via.placeholder.com/600x200/303b97/ffffff?text=Bio+Image' }}"
                     alt="Bio"
                     style="width: 100%; max-width: 600px; height: 200px; object-fit: cover; border-radius: 12px; border: 2px solid #e5e7eb; margin: 0 auto 16px; display: block;"
                     id="bio-preview">
                <label class="file-input-label">
                    <i class="fas fa-upload"></i> Upload Bio Image
                    <input type="file" name="company_bio_image" accept="image/*" onchange="previewImage(event, 'bio-preview')">
                </label>
                <div class="helper-text" style="margin-top: 10px;">Banner image for your company page (Recommended: 1200x400px)</div>
            </div>
        </div>
    </div>

    <!-- Contact -->
    <div class="form-card">
        <h2 class="form-title"><i class="fas fa-phone"></i> Contact Information</h2>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" class="form-input" value="{{ auth('office')->user()->email_address }}" readonly>
                <div class="helper-text">Email cannot be changed</div>
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input type="text" name="phone_number" class="form-input" value="{{ old('phone_number', auth('office')->user()->phone_number) }}" required>
            </div>
        </div>
    </div>

    <!-- Location -->
    <div class="form-card">
        <h2 class="form-title"><i class="fas fa-map-marker-alt"></i> Office Location</h2>

        <div class="form-group">
            <label class="form-label">Office Address</label>
            <input type="text" name="office_address" class="form-input" value="{{ old('office_address', auth('office')->user()->office_address) }}">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">City *</label>
                <select id="city-select" class="form-select">
                    <option value="">Select City</option>
                </select>
                <input type="hidden" name="city" id="city" value="{{ old('city', auth('office')->user()->city) }}">
                <div class="helper-text">Select your office city</div>
            </div>
            <div class="form-group">
                <label class="form-label">Area/District *</label>
                <select id="area-select" class="form-select" disabled>
                    <option value="">Select City First</option>
                </select>
                <input type="hidden" name="district" id="district" value="{{ old('district', auth('office')->user()->district) }}">
                <div class="helper-text">Select your office area</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Set Location on Map</label>
            <div class="helper-text">Click on the map or drag the marker to set your office location</div>
            <div class="map-container">
                <div id="map"></div>
            </div>
            <div class="location-info" id="location-info">
                <i class="fas fa-info-circle"></i> Click on the map to select your office location
            </div>
        </div>

        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', auth('office')->user()->latitude ?? '36.1911') }}">
        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', auth('office')->user()->longitude ?? '44.0091') }}">
    </div>

    <!-- Working Hours -->
    <div class="form-card">
        <h2 class="form-title"><i class="fas fa-clock"></i> Working Hours</h2>
        <div class="helper-text" style="margin-bottom: 16px;">Select the days you're available and set your working hours</div>

        <div class="schedule-grid" id="scheduleGrid"></div>

        <input type="hidden" name="availability_schedule" id="availability_schedule">
    </div>

    <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Update Profile</button>
</form>

<!-- Change Password -->
<div class="form-card" style="margin-top: 24px;">
    <h2 class="form-title"><i class="fas fa-key"></i> Change Password</h2>
    <form action="{{ route('office.password.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label class="form-label">Current Password *</label>
            <input type="password" name="current_password" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">New Password *</label>
            <input type="password" name="password" class="form-input" required>
            <div class="helper-text">Minimum 8 characters</div>
        </div>
        <div class="form-group">
            <label class="form-label">Confirm New Password *</label>
            <input type="password" name="password_confirmation" class="form-input" required>
        </div>
        <button type="submit" class="submit-btn"><i class="fas fa-key"></i> Change Password</button>
    </form>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/location-selector.js') }}"></script>
<script>
    // Image preview
    function previewImage(event, previewId) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}

    // Initialize Location Selector
    let locationSelector;

    // Initialize Schedule
    function initializeSchedule() {
    const days = [
        {name: 'monday', label: 'Monday', defaultOpen: '09:00', defaultClose: '18:00'},
        {name: 'tuesday', label: 'Tuesday', defaultOpen: '09:00', defaultClose: '18:00'},
        {name: 'wednesday', label: 'Wednesday', defaultOpen: '09:00', defaultClose: '18:00'},
        {name: 'thursday', label: 'Thursday', defaultOpen: '09:00', defaultClose: '18:00'},
        {name: 'friday', label: 'Friday', defaultOpen: '09:00', defaultClose: '18:00'},
        {name: 'saturday', label: 'Saturday', defaultOpen: '10:00', defaultClose: '15:00'},
        {name: 'sunday', label: 'Sunday', defaultOpen: '10:00', defaultClose: '15:00'}
    ];

    let existingSchedule = {};
    try {
        const scheduleData = @json(auth('office')->user()->availability_schedule);
        if (scheduleData && typeof scheduleData === 'object') {
            existingSchedule = scheduleData;
        } else if (typeof scheduleData === 'string') {
            existingSchedule = JSON.parse(scheduleData);
        }
    } catch(e) {
        console.log('No existing schedule');
    }

    const scheduleGrid = document.getElementById('scheduleGrid');
    scheduleGrid.innerHTML = ''; // Clear existing content

    days.forEach(day => {
        const daySchedule = existingSchedule[day.name];
        const isChecked = daySchedule && (daySchedule.open || daySchedule !== 'closed') ? 'checked' : '';

        let openTime = day.defaultOpen;
        let closeTime = day.defaultClose;

        if (daySchedule && typeof daySchedule === 'object') {
            openTime = daySchedule.open || openTime;
            closeTime = daySchedule.close || closeTime;
        }

        const disabled = isChecked ? '' : 'disabled';

        const dayRow = document.createElement('div');
        dayRow.className = 'day-row';
        dayRow.innerHTML = `
            <div class="day-header">
                <input type="checkbox" class="day-checkbox" id="${day.name}" ${isChecked} onchange="toggleDay('${day.name}')">
                <label for="${day.name}" class="day-name">${day.label}</label>
            </div>
            <div class="time-inputs" id="${day.name}-times">
                <div class="time-input-group">
                    <label class="time-input-label">Opening Time</label>
                    <input type="time" class="time-input" name="${day.name}_open" value="${openTime}" ${disabled}>
                </div>
                <div class="time-input-group">
                    <label class="time-input-label">Closing Time</label>
                    <input type="time" class="time-input" name="${day.name}_close" value="${closeTime}" ${disabled}>
                </div>
            </div>
        `;
        scheduleGrid.appendChild(dayRow);
    });
}

    // Toggle day
    function toggleDay(day) {
    const checkbox = document.getElementById(day);
    const openInput = document.querySelector(`input[name="${day}_open"]`);
    const closeInput = document.querySelector(`input[name="${day}_close"]`);

    if (checkbox.checked) {
        openInput.disabled = false;
        closeInput.disabled = false;
    } else {
        openInput.disabled = true;
        closeInput.disabled = true;
    }
}

    // Build schedule before submit
    document.getElementById('profileForm').addEventListener('submit', function(e) {
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    const schedule = {};

    days.forEach(day => {
        const checkbox = document.getElementById(day);
        if (checkbox && checkbox.checked) {
            const open = document.querySelector(`input[name="${day}_open"]`)?.value;
            const close = document.querySelector(`input[name="${day}_close"]`)?.value;
            if (open && close) {
                schedule[day] = { open, close };
            }
        }
    });

    document.getElementById('availability_schedule').value = JSON.stringify(schedule);
});

    // Google Maps
    let map;
    let marker;

    function initMap() {
        const currentLat = parseFloat(document.getElementById('latitude').value) || 36.1911;
        const currentLng = parseFloat(document.getElementById('longitude').value) || 44.0091;

        const officeLocation = { lat: currentLat, lng: currentLng };

        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 15,
            center: officeLocation,
            mapTypeControl: true,
            streetViewControl: false,
            fullscreenControl: true,
        });

        marker = new google.maps.Marker({
            position: officeLocation,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP,
            title: "Office Location"
        });

        map.addListener('click', function(e) {
            placeMarker(e.latLng);
        });

        marker.addListener('dragend', function(e) {
            updateLocation(e.latLng);
        });

        updateLocation(officeLocation);
    }

    function placeMarker(location) {
        marker.setPosition(location);
        updateLocation(location);
    }

    function updateLocation(location) {
        const lat = location.lat();
        const lng = location.lng();

        document.getElementById('latitude').value = lat.toFixed(8);
        document.getElementById('longitude').value = lng.toFixed(8);
        document.getElementById('location-info').innerHTML = `
            <i class="fas fa-check-circle" style="color: #16a34a;"></i>
            Location selected: Latitude ${lat.toFixed(6)}, Longitude ${lng.toFixed(6)}
        `;
    }

    // Initialize location selector and find city
   async function initializeLocationSelector() {
    try {
        const currentCity = "{{ old('city', auth('office')->user()->city) }}";
        const currentDistrict = "{{ old('district', auth('office')->user()->district) }}";

        console.log('=== Location Selector Initialization ===');
        console.log('Current City:', currentCity);
        console.log('Current District:', currentDistrict);

        // Verify elements exist
        const citySelect = document.getElementById('city-select');
        const areaSelect = document.getElementById('area-select');

        if (!citySelect) {
            console.error('City select element not found!');
            return;
        }
        if (!areaSelect) {
            console.error('Area select element not found!');
            return;
        }

        console.log('Elements found successfully');

        // Create the location selector WITHOUT pre-selected values
        locationSelector = new LocationSelector({
            citySelectId: 'city-select',
            areaSelectId: 'area-select',
            cityInputId: 'city',
            districtInputId: 'district'
        });

        console.log('LocationSelector instance created');

        // Wait for initialization to complete
        await locationSelector.init();
        console.log('LocationSelector initialized, cities loaded:', locationSelector.cities.length);

        // Check if cities were loaded
        if (locationSelector.cities.length === 0) {
            console.error('No cities loaded!');
            return;
        }

        // Now set the city if it exists
        if (currentCity && currentCity.trim() !== '') {
            console.log('Attempting to set city:', currentCity);
            const citySet = await locationSelector.setCityByName(currentCity);

            if (citySet) {
                console.log('✓ City set successfully');

                // Wait a bit for areas to load
                await new Promise(resolve => setTimeout(resolve, 500));

                // Now set the area if it exists
                if (currentDistrict && currentDistrict.trim() !== '') {
                    console.log('Attempting to set area:', currentDistrict);
                    const areaSet = locationSelector.setAreaByName(currentDistrict);
                    if (areaSet) {
                        console.log('✓ Area set successfully');
                    } else {
                        console.warn('✗ Failed to set area');
                    }
                }
            } else {
                console.warn('✗ Failed to set city - city not found in list');
            }
        } else {
            console.log('No saved city to restore');
        }

        console.log('=== Initialization Complete ===');

    } catch (error) {
        console.error('!!! Error initializing location selector:', error);
        console.error('Error details:', error.message);
        console.error('Stack:', error.stack);
    }
}

    // Initialize on load
    window.addEventListener('load', async function() {
        console.log('Page loaded, initializing...');

        // Initialize schedule
        initializeSchedule();

        // Initialize location selector
        await initializeLocationSelector();

        console.log('Initialization complete');
    });
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>
@endsection
