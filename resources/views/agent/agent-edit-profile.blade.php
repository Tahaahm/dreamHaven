@extends('layouts.agent-layout')

@section('title', 'Refine Profile - Dream Mulk')

@section('styles')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.95);
        --brand-primary: #303b97;
        --brand-secondary: #1e2875;
        --accent-emerald: #10b981;
        --soft-gray: #f1f5f9;
        --text-dark: #0f172a;
        --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        --shadow-lg: 0 10px 25px -5px rgba(48, 59, 151, 0.1);
    }

    body { background-color: #f8fafc; color: var(--text-dark); }

    .luxury-container {
        max-width: 1100px;
        margin: 40px auto;
        padding: 0 20px;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .luxury-header {
        background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
        padding: 40px;
        border-radius: 24px;
        color: white;
        margin-bottom: 40px;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }

    .luxury-header::before {
        content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px;
        background: rgba(255,255,255,0.05); border-radius: 50%;
    }

    .luxury-header h1 { font-size: 32px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
    .luxury-header p { opacity: 0.8; font-size: 15px; margin-top: 8px; }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.4);
        border-radius: 24px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: var(--shadow-sm);
    }

    .section-head {
        display: flex; align-items: center; gap: 15px; margin-bottom: 30px;
    }

    .section-head i {
        width: 45px; height: 45px; background: #eef2ff; color: var(--brand-primary);
        display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 20px;
    }

    .section-head h3 { font-size: 20px; font-weight: 700; color: var(--text-dark); margin: 0; }

    .input-group { margin-bottom: 25px; position: relative; }
    .input-label { font-weight: 600; font-size: 14px; color: #475569; margin-bottom: 8px; display: block; }
    .input-label .required { color: red; margin-left: 3px; }

    .luxury-input, .luxury-select, .luxury-textarea {
        width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 14px;
        font-size: 15px; transition: 0.3s; background: white;
    }

    .luxury-input:focus, .luxury-textarea:focus, .luxury-select:focus {
        border-color: var(--brand-primary); box-shadow: 0 0 0 4px rgba(48, 59, 151, 0.08); outline: none;
    }

    .luxury-input:disabled { background: #f8fafc; cursor: not-allowed; border-style: dashed; }

    .error-message {
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .upload-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }

    .luxury-upload-box {
        position: relative; border: 2px dashed #cbd5e1; border-radius: 20px;
        padding: 30px; text-align: center; transition: 0.3s; cursor: pointer; background: #fff;
    }

    .luxury-upload-box:hover { border-color: var(--brand-primary); background: #f0f4ff; }

    .preview-circle {
        width: 100px; height: 100px; border-radius: 50%; margin: 0 auto 15px;
        border: 4px solid white; box-shadow: var(--shadow-sm); overflow: hidden;
        background: var(--soft-gray); display: flex; align-items: center; justify-content: center;
    }

    .preview-rect {
        width: 100%; height: 120px; border-radius: 12px; margin-bottom: 15px;
        overflow: hidden; background: var(--soft-gray); border: 2px solid white;
    }

    .preview-circle img, .preview-rect img { width: 100%; height: 100%; object-fit: cover; }

    .schedule-grid { display: grid; gap: 12px; }

    .schedule-item {
        display: grid; grid-template-columns: 1.5fr 2fr; align-items: center;
        padding: 16px 24px; border-radius: 16px; background: #fff;
        border: 1px solid #e2e8f0; transition: 0.3s;
    }

    .schedule-item.is-active { border-color: var(--brand-primary); background: #fdfdff; box-shadow: 0 4px 12px rgba(48,59,151,0.05); }

    .day-info { display: flex; align-items: center; gap: 12px; }

    .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1; transition: .4s; border-radius: 24px;
    }
    .slider:before {
        position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
        background-color: white; transition: .4s; border-radius: 50%;
    }
    input:checked + .slider { background-color: var(--brand-primary); }
    input:checked + .slider:before { transform: translateX(20px); }

    .time-inputs { display: flex; align-items: center; gap: 10px; justify-content: flex-end; }
    .time-field {
        padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1;
        font-weight: 600; font-size: 14px; color: var(--text-dark);
    }

    .map-wrapper {
        border-radius: 24px; overflow: hidden; border: 1px solid #e2e8f0;
        box-shadow: var(--shadow-sm); position: relative;
    }
    #map { width: 100%; height: 450px; }

    .map-overlay-info {
        position: absolute; top: 20px; left: 20px; background: white;
        padding: 12px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        z-index: 10; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px;
    }

    .sticky-actions {
        position: sticky; bottom: 30px; background: white; padding: 20px 30px;
        border-radius: 20px; display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 -10px 40px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; z-index: 100;
    }

    .btn-luxury {
        padding: 14px 32px; border-radius: 14px; font-weight: 700; font-size: 15px;
        transition: 0.3s; display: inline-flex; align-items: center; gap: 10px; border: none; cursor: pointer;
        text-decoration: none;
    }
    .btn-save { background: var(--brand-primary); color: white; }
    .btn-save:hover { background: var(--brand-secondary); transform: translateY(-2px); }
    .btn-cancel { background: var(--soft-gray); color: #64748b; }

    @media (max-width: 768px) {
        .upload-grid, .schedule-item { grid-template-columns: 1fr; }
        .time-inputs { justify-content: flex-start; margin-top: 15px; }
    }
</style>
@endsection

@section('content')
<div class="luxury-container">
    <div class="luxury-header">
        <h1>Refine Your Presence</h1>
        <p>Your profile is your digital business card. Keep it sharp and updated.</p>
    </div>

    @if($errors->any())
    <div style="background: #fee; border: 2px solid #f00; padding: 15px; border-radius: 12px; margin-bottom: 20px;">
        <strong style="color: #c00;">Please fix the following errors:</strong>
        <ul style="margin: 10px 0 0 20px; color: #c00;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('agent.profile.update') }}" method="POST" enctype="multipart/form-data" id="mainProfileForm">
        @csrf
        @method('PUT')

        <input type="hidden" name="working_hours" id="working_hours_json">

        <div class="glass-card">
            <div class="section-head">
                <i class="fas fa-camera-retro"></i>
                <h3>Visual Identity</h3>
            </div>
            <div class="upload-grid">
                <div class="luxury-upload-box" onclick="document.getElementById('pImg').click()">
                    <div class="preview-circle" id="pPrev">
                        @if($agent->profile_image)
                            @php
                                $profileUrl = str_starts_with($agent->profile_image, 'http')
                                    ? $agent->profile_image
                                    : asset('storage/' . $agent->profile_image);
                            @endphp
                            <img src="{{ $profileUrl }}" alt="Profile">
                        @else
                            <i class="fas fa-user fa-2x"></i>
                        @endif
                    </div>
                    <span class="input-label">Avatar Photo</span>
                    <input type="file" id="pImg" name="profile_image" accept="image/*" hidden>
                </div>

                <div class="luxury-upload-box" onclick="document.getElementById('bImg').click()">
                    <div class="preview-rect" id="bPrev">
                        @if($agent->bio_image)
                            @php
                                $bioUrl = str_starts_with($agent->bio_image, 'http')
                                    ? $agent->bio_image
                                    : asset('storage/' . $agent->bio_image);
                            @endphp
                            <img src="{{ $bioUrl }}" alt="Bio">
                        @else
                            <i class="fas fa-image fa-2x"></i>
                        @endif
                    </div>
                    <span class="input-label">Cover/Portfolio Image</span>
                    <input type="file" id="bImg" name="bio_image" accept="image/*" hidden>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="section-head"><i class="fas fa-id-card"></i><h3>Professional Details</h3></div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px;">

                <div class="input-group">
                    <label class="input-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="agent_name" class="luxury-input" value="{{ old('agent_name', $agent->agent_name) }}" required>
                    @error('agent_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label class="input-label">Primary Email <i class="fas fa-lock" style="font-size: 10px; margin-left: 5px;"></i></label>
                    <input type="email" class="luxury-input" value="{{ $agent->primary_email }}" disabled>
                </div>

                <div class="input-group">
                    <label class="input-label">Phone Number <span class="required">*</span></label>
                    <input type="text" name="primary_phone" class="luxury-input" value="{{ old('primary_phone', $agent->primary_phone) }}" required>
                    @error('primary_phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label class="input-label">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" class="luxury-input" value="{{ old('whatsapp_number', $agent->whatsapp_number) }}">
                    @error('whatsapp_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label class="input-label">City <span class="required">*</span></label>
                    <select name="city" id="agent-city" class="luxury-select" required>
                        <option value="">Loading cities...</option>
                    </select>
                    @error('city')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label class="input-label">District <span class="required">*</span></label>
                    <input type="text" name="district" class="luxury-input" value="{{ old('district', $agent->district) }}" required placeholder="Enter district name">
                    @error('district')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label class="input-label">License Number</label>
                    <input type="text" name="license_number" class="luxury-input" value="{{ old('license_number', $agent->license_number) }}">
                    @error('license_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label class="input-label">Years of Experience</label>
                    <input type="number" name="years_experience" class="luxury-input" value="{{ old('years_experience', $agent->years_experience) }}" min="0">
                    @error('years_experience')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group" style="grid-column: span 2;">
                    <label class="input-label">Office Address</label>
                    <input type="text" name="office_address" class="luxury-input" value="{{ old('office_address', $agent->office_address) }}" placeholder="Enter your office address">
                    @error('office_address')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group" style="grid-column: span 2;">
                    <label class="input-label">Professional Biography</label>
                    <textarea name="agent_bio" class="luxury-textarea" rows="4" placeholder="Tell us about your experience and expertise...">{{ old('agent_bio', $agent->agent_bio) }}</textarea>
                    @error('agent_bio')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="section-head"><i class="fas fa-calendar-alt"></i><h3>Availability Schedule</h3></div>
            <div class="schedule-grid" id="smartSchedule"></div>
        </div>

        <div class="glass-card">
            <div class="section-head"><i class="fas fa-map-marked-alt"></i><h3>Office Headquarters</h3></div>
            <div class="map-wrapper">
                <div class="map-overlay-info">
                    <i class="fas fa-mouse-pointer" style="color: var(--brand-primary);"></i>
                    Drag the marker to your exact office location
                </div>
                <div id="map"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div class="input-group">
                    <label class="input-label">Latitude</label>
                    <input type="text" name="latitude" id="lat" class="luxury-input" value="{{ old('latitude', $agent->latitude ?? 36.1911) }}" readonly>
                </div>
                <div class="input-group">
                    <label class="input-label">Longitude</label>
                    <input type="text" name="longitude" id="lng" class="luxury-input" value="{{ old('longitude', $agent->longitude ?? 44.0091) }}" readonly>
                </div>
            </div>
        </div>

        <div class="sticky-actions">
            <a href="{{ route('agent.dashboard') }}" class="btn-luxury btn-cancel">Discard Changes</a>
            <button type="submit" class="btn-luxury btn-save">
                <i class="fas fa-check-circle"></i> Publish Updates
            </button>
        </div>
    </form>
</div>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>

<script>
    // --- 1. DYNAMIC CITY LOADING ---
    document.addEventListener('DOMContentLoaded', async function() {
        const citySelect = document.getElementById('agent-city');
        const currentCity = "{{ old('city', $agent->city) }}";

        try {
            const response = await fetch("/v1/api/location/branches", {
                headers: { "Accept": "application/json", "Accept-Language": "en" }
            });
            const result = await response.json();

            citySelect.innerHTML = '<option value="">Select City</option>';

            if (result.success && result.data) {
                result.data.sort((a, b) => a.city_name_en.localeCompare(b.city_name_en));

                result.data.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.city_name_en;
                    option.textContent = city.city_name_en;

                    if (city.city_name_en === currentCity) {
                        option.selected = true;
                    }
                    citySelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error fetching cities:', error);
            citySelect.innerHTML = '<option value="">Error loading cities</option>';
        }
    });

    // --- 2. SCHEDULE LOGIC (FIXED) ---
    const days = [
        {id: 'monday', n: 'Monday'}, {id: 'tuesday', n: 'Tuesday'}, {id: 'wednesday', n: 'Wednesday'},
        {id: 'thursday', n: 'Thursday'}, {id: 'friday', n: 'Friday'}, {id: 'saturday', n: 'Saturday'}, {id: 'sunday', n: 'Sunday'}
    ];

    // Handle potential double-encoding or null values safely
    const rawSchedule = {!! json_encode($agent->working_hours) !!};
    let currentSchedule = {};

    if (typeof rawSchedule === 'string') {
        try {
            currentSchedule = JSON.parse(rawSchedule);
        } catch (e) { currentSchedule = {}; }
    } else if (typeof rawSchedule === 'object' && rawSchedule !== null) {
        currentSchedule = rawSchedule;
    }

    function initSchedule() {
        const grid = document.getElementById('smartSchedule');
        grid.innerHTML = ''; // Clear existing to prevent duplicates

        days.forEach(day => {
            const val = currentSchedule[day.id];
            // Check if open (exists and not 'closed')
            const isOpen = val && val !== 'closed';
            // Default times or saved times
            let [start, end] = isOpen ? val.split('-') : ["09:00", "18:00"];

            const item = document.createElement('div');
            item.className = `schedule-item ${isOpen ? 'is-active' : ''}`;
            item.id = `item-${day.id}`;
            item.innerHTML = `
                <div class="day-info">
                    <label class="switch">
                        <input type="checkbox" id="sw-${day.id}" ${isOpen ? 'checked' : ''} onchange="toggleDay('${day.id}')">
                        <span class="slider"></span>
                    </label>
                    <span style="font-weight:700;">${day.n}</span>
                </div>
                <div class="time-inputs">
                    <input type="time" class="time-field" id="s-${day.id}" value="${start}" ${!isOpen ? 'disabled' : ''}>
                    <span style="color:#94a3b8; font-weight:bold;">→</span>
                    <input type="time" class="time-field" id="e-${day.id}" value="${end}" ${!isOpen ? 'disabled' : ''}>
                </div>
            `;
            grid.appendChild(item);
        });
    }

    window.toggleDay = function(id) {
        const active = document.getElementById(`sw-${id}`).checked;
        document.getElementById(`item-${id}`).classList.toggle('is-active', active);
        document.getElementById(`s-${id}`).disabled = !active;
        document.getElementById(`e-${id}`).disabled = !active;
    };

    // --- 3. FORM SUBMIT HANDLER (FIXED) ---
    document.getElementById('mainProfileForm').addEventListener('submit', function(e) {
        // Prevent default temporarily to debug if needed, but here we just process data
        const schedule = {};
        days.forEach(d => {
            const sw = document.getElementById(`sw-${d.id}`);
            if(sw && sw.checked) {
                const start = document.getElementById(`s-${d.id}`).value;
                const end = document.getElementById(`e-${d.id}`).value;
                schedule[d.id] = `${start}-${end}`;
            } else {
                schedule[d.id] = "closed";
            }
        });

        // Populate the hidden input with the JSON string
        const jsonStr = JSON.stringify(schedule);
        document.getElementById('working_hours_json').value = jsonStr;

        console.log("Submitting Schedule:", jsonStr); // For debugging
    });

    // --- 4. GOOGLE MAPS ---
    function initMap() {
        const initialLat = parseFloat("{{ old('latitude', $agent->latitude ?? 36.1911) }}");
        const initialLng = parseFloat("{{ old('longitude', $agent->longitude ?? 44.0091) }}");
        const myLatLng = { lat: initialLat, lng: initialLng };

        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 15,
            center: myLatLng,
            styles: [{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#c2c7e9"}]}] // Simplified style
        });

        const marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            draggable: true,
            icon: {
                path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
                scale: 8,
                fillColor: "#303b97",
                fillOpacity: 1,
                strokeWeight: 2,
                strokeColor: "#ffffff"
            }
        });

        const updateInputs = (latLng) => {
            document.getElementById('lat').value = latLng.lat().toFixed(7);
            document.getElementById('lng').value = latLng.lng().toFixed(7);
        };

        google.maps.event.addListener(marker, 'dragend', (e) => updateInputs(e.latLng));
        map.addListener('click', (e) => {
            marker.setPosition(e.latLng);
            updateInputs(e.latLng);
        });
    }

    // --- 5. IMAGE PREVIEWS ---
    function setupPrev(input, prev) {
        const fileInput = document.getElementById(input);
        if(fileInput) {
            fileInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (ev) => document.getElementById(prev).innerHTML = `<img src="${ev.target.result}">`;
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
    }
    setupPrev('pImg', 'pPrev');
    setupPrev('bImg', 'bPrev');

    // Init everything
    document.addEventListener('DOMContentLoaded', initSchedule);
</script>
