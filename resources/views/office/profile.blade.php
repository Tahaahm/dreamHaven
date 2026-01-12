@extends('layouts.office-layout')

@section('title', 'Profile Settings - Dream Mulk')
@section('search-placeholder', 'Search...')

@section('styles')
<style>
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 32px; }
    .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; margin-bottom: 24px; }
    .form-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 24px; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; font-size: 14px; }
    .form-input, .form-textarea { width: 100%; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 8px; padding: 12px 16px; font-size: 15px; transition: all 0.3s; font-family: inherit; }
    .form-input:focus, .form-textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
    .form-input:read-only { background: var(--bg-hover); color: var(--text-muted); cursor: not-allowed; }
    .form-textarea { resize: vertical; min-height: 100px; }
    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .submit-btn { background: #6366f1; color: white; padding: 12px 28px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .submit-btn:hover { background: #5558e3; transform: translateY(-1px); }
    .alert { padding: 16px; border-radius: 8px; margin-bottom: 24px; }
    .alert-success { background: rgba(34,197,94,0.1); color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
    .alert-error { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }
    .helper-text { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
    .schedule-grid { display: grid; gap: 16px; margin-top: 12px; }
    .day-row { background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; }
    .day-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .day-checkbox { width: 20px; height: 20px; cursor: pointer; accent-color: #6366f1; }
    .day-name { font-weight: 600; color: var(--text-primary); font-size: 15px; }
    .time-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-left: 32px; }
    .time-input-group { display: flex; flex-direction: column; gap: 6px; }
    .time-input-label { font-size: 13px; color: var(--text-secondary); font-weight: 500; }
    .time-input { padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-main); color: var(--text-primary); font-size: 14px; }
    .time-input:disabled { opacity: 0.5; cursor: not-allowed; }
    .image-preview { width: 150px; height: 150px; border-radius: 8px; object-fit: cover; border: 2px solid var(--border-color); margin-top: 10px; }
    .file-input-label { display: inline-block; padding: 10px 20px; background: #6366f1; color: white; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s; margin-top: 10px; }
    .file-input-label:hover { background: #5558e3; }
    .file-input-label input[type="file"] { display: none; }
    .map-container { width: 100%; height: 400px; border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color); margin-top: 10px; }
    #map { width: 100%; height: 100%; }
    .location-info { background: var(--bg-main); padding: 12px; border-radius: 8px; margin-top: 10px; border: 1px solid var(--border-color); font-size: 13px; color: var(--text-secondary); }
    .plan-badge { display: inline-block; padding: 8px 16px; background: #6366f1; color: white; border-radius: 6px; font-weight: 600; text-transform: capitalize; }
</style>
@endsection

@section('content')
<h1 class="page-title">Profile Settings</h1>

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        @foreach($errors->all() as $error)
            <div><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
        @endforeach
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
    <h2 class="form-title"><i class="fas fa-image"></i> Images</h2>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Profile Image</label>
            @php
                $profileImageUrl = auth('office')->user()->profile_image;
                if ($profileImageUrl && !str_starts_with($profileImageUrl, 'http')) {
                    $profileImageUrl = asset('storage/' . $profileImageUrl);
                }
            @endphp
            <img src="{{ $profileImageUrl ?? 'https://via.placeholder.com/150/6366f1/ffffff?text=No+Image' }}"
                 alt="Profile"
                 class="image-preview"
                 id="profile-preview">
            <label class="file-input-label">
                <i class="fas fa-upload"></i> Upload Profile Image
                <input type="file" name="profile_image" accept="image/*" onchange="previewImage(event, 'profile-preview')">
            </label>
        </div>

        <div class="form-group">
            <label class="form-label">Company Bio Image</label>
            @php
                $bioImageUrl = auth('office')->user()->company_bio_image;
                if ($bioImageUrl && !str_starts_with($bioImageUrl, 'http')) {
                    $bioImageUrl = asset('storage/' . $bioImageUrl);
                }
            @endphp
            <img src="{{ $bioImageUrl ?? 'https://via.placeholder.com/150/6366f1/ffffff?text=No+Image' }}"
                 alt="Bio"
                 class="image-preview"
                 id="bio-preview">
            <label class="file-input-label">
                <i class="fas fa-upload"></i> Upload Bio Image
                <input type="file" name="company_bio_image" accept="image/*" onchange="previewImage(event, 'bio-preview')">
            </label>
        </div>
    </div>
</div>

    <!-- Contact -->
    <div class="form-card">
        <h2 class="form-title"><i class="fas fa-phone"></i> Contact</h2>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" class="form-input" value="{{ auth('office')->user()->email_address }}" readonly>
                <div class="helper-text">Email cannot be changed</div>
            </div>
            <div class="form-group">
                <label class="form-label">Phone *</label>
                <input type="text" name="phone_number" class="form-input" value="{{ old('phone_number', auth('office')->user()->phone_number) }}" required>
            </div>
        </div>
    </div>

    <!-- Location -->
    <div class="form-card">
        <h2 class="form-title"><i class="fas fa-map-marker-alt"></i> Location</h2>

        <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" name="office_address" class="form-input" value="{{ old('office_address', auth('office')->user()->office_address) }}">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-input" value="{{ old('city', auth('office')->user()->city) }}">
            </div>
            <div class="form-group">
                <label class="form-label">District</label>
                <input type="text" name="district" class="form-input" value="{{ old('district', auth('office')->user()->district) }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Select Location on Map</label>
            <div class="helper-text">Click or drag the marker to set your office location</div>
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

    <!-- Schedule -->
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
        </div>
        <div class="form-group">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="password_confirmation" class="form-input" required>
        </div>
        <button type="submit" class="submit-btn"><i class="fas fa-key"></i> Change Password</button>
    </form>
</div>
@endsection

@section('scripts')
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

        days.forEach(day => {
            const daySchedule = existingSchedule[day.name];
            const isChecked = daySchedule && daySchedule !== 'closed' ? 'checked' : '';

            let openTime = day.defaultOpen;
            let closeTime = day.defaultClose;

            if (daySchedule && daySchedule !== 'closed') {
                const [open, close] = daySchedule.split('-');
                if (open && close) {
                    openTime = open;
                    closeTime = close;
                }
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
                        <label class="time-input-label">Opening</label>
                        <input type="time" class="time-input" name="${day.name}_open" value="${openTime}" ${disabled}>
                    </div>
                    <div class="time-input-group">
                        <label class="time-input-label">Closing</label>
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
        });

        marker = new google.maps.Marker({
            position: officeLocation,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP,
        });

        map.addListener('click', function(e) {
            placeMarker(e.latLng);
        });

        marker.addListener('dragend', function(e) {
            updateLocation(e.latLng);
        });

        if (currentLat !== 36.1911 || currentLng !== 44.0091) {
            updateLocation(officeLocation);
        }
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
            <i class="fas fa-check-circle" style="color: #22c55e;"></i>
            Location selected: ${lat.toFixed(6)}, ${lng.toFixed(6)}
        `;
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        initializeSchedule();
        initMap();
    });
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>
@endsection
