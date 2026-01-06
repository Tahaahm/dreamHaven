<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Dream Mulk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #ffffff; --bg-card: #f8f9fb; --bg-hover: #f1f3f5; --text-primary: #1a1a1a; --text-secondary: #6b7280; --text-muted: #9ca3af; --border-color: #e8eaed; --shadow: rgba(0,0,0,0.08); }
        [data-theme="dark"] { --bg-main: #0a0b0f; --bg-card: #16171d; --bg-hover: #1f2028; --text-primary: #ffffff; --text-secondary: rgba(255,255,255,0.8); --text-muted: rgba(255,255,255,0.5); --border-color: rgba(255,255,255,0.08); --shadow: rgba(0,0,0,0.4); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 240px; background: #16171d; display: flex; flex-direction: column; border-right: 1px solid rgba(255,255,255,0.06); }
        .logo { padding: 20px 24px; font-size: 20px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .logo i { font-size: 22px; color: #6366f1; }
        .nav-menu { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-item { padding: 11px 16px; color: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 14px; font-size: 14px; text-decoration: none; margin-bottom: 4px; border-radius: 8px; font-weight: 500; }
        .nav-item:hover { background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.9); }
        .nav-item.active { background: #6366f1; color: #fff; }
        .nav-item i { width: 20px; text-align: center; font-size: 16px; }
        .nav-bottom { border-top: 1px solid rgba(255,255,255,0.06); padding: 16px 12px; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg-main); transition: background 0.3s; }
        .top-bar { background: #ffffff; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e8eaed; }
        .search-bar { flex: 1; max-width: 420px; position: relative; }
        .search-bar input { width: 100%; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; padding: 11px 44px; color: #1a1a1a; font-size: 14px; }
        .search-bar input::placeholder { color: #9ca3af; }
        .search-bar i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 15px; }
        .top-actions { display: flex; align-items: center; gap: 14px; }
        .icon-btn { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .icon-btn:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .theme-toggle { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .theme-toggle:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .user-profile { display: flex; align-items: center; gap: 11px; cursor: pointer; padding: 7px 13px; border-radius: 8px; transition: all 0.2s; }
        .user-profile:hover { background: #f8f9fb; }
        .user-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 15px; }
        .content-area { flex: 1; overflow-y: auto; padding: 32px; background: var(--bg-main); transition: background 0.3s; }
        .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 32px; transition: color 0.3s; }
        .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; margin-bottom: 24px; transition: all 0.3s; }
        .form-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 24px; transition: color 0.3s; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; font-size: 14px; transition: color 0.3s; }
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
        .content-area::-webkit-scrollbar { width: 9px; }
        .content-area::-webkit-scrollbar-track { background: var(--bg-main); }
        .content-area::-webkit-scrollbar-thumb { background: var(--bg-card); border-radius: 5px; }
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
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fas fa-home"></i> Dream Mulk</div>
        <div class="nav-menu">
            <a href="{{ route('office.dashboard') }}" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="{{ route('office.properties') }}" class="nav-item"><i class="fas fa-building"></i> Properties</a>
            <a href="#" class="nav-item"><i class="fas fa-folder"></i> Projects</a>
            <a href="#" class="nav-item"><i class="fas fa-user-friends"></i> Leads</a>
            <a href="#" class="nav-item"><i class="fas fa-tag"></i> Offers</a>
            <a href="#" class="nav-item"><i class="fas fa-file-contract"></i> Agreements</a>
            <a href="{{ route('office.appointments') }}" class="nav-item"><i class="fas fa-calendar-alt"></i> Calendar</a>
            <a href="#" class="nav-item"><i class="fas fa-chart-bar"></i> Activities</a>
            <a href="#" class="nav-item"><i class="fas fa-address-book"></i> Contacts</a>
            <a href="{{ route('office.agents') }}" class="nav-item"><i class="fas fa-user-tie"></i> Agents</a>
            <a href="#" class="nav-item"><i class="fas fa-bullhorn"></i> Campaigns</a>
            <a href="#" class="nav-item"><i class="fas fa-file-alt"></i> Documents</a>
        </div>
        <div class="nav-bottom">
            <a href="{{ route('office.profile') }}" class="nav-item active"><i class="fas fa-cog"></i> Settings</a>
            <form action="{{ route('office.logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="nav-item" style="width: 100%; background: none; border: none; text-align: left; cursor: pointer; color: rgba(255,255,255,0.5); font-family: inherit; font-size: 14px; font-weight: 500;"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search">
            </div>
            <div class="top-actions">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                <button class="icon-btn"><i class="fas fa-bell"></i></button>
                <button class="icon-btn"><i class="fas fa-envelope"></i></button>
                <div class="user-profile">
                    <div class="user-avatar">{{ strtoupper(substr(auth('office')->user()->company_name, 0, 2)) }}</div>
                    <span style="font-size: 14px; color: #1a1a1a; font-weight: 600;">{{ auth('office')->user()->company_name }}</span>
                    <i class="fas fa-chevron-down" style="font-size: 12px; color: #9ca3af;"></i>
                </div>
            </div>
        </div>

        <div class="content-area">
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

                    <!-- ✅ SHOW CURRENT PLAN (READ-ONLY) -->
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
                            @if(auth('office')->user()->profile_image)
                                <img src="{{ asset('storage/' . auth('office')->user()->profile_image) }}" alt="Profile" class="image-preview" id="profile-preview">
                            @else
                                <img src="https://via.placeholder.com/150/6366f1/ffffff?text=No+Image" alt="Profile" class="image-preview" id="profile-preview">
                            @endif
                            <label class="file-input-label">
                                <i class="fas fa-upload"></i> Upload Profile Image
                                <input type="file" name="profile_image" accept="image/*" onchange="previewImage(event, 'profile-preview')">
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Company Bio Image</label>
                            @if(auth('office')->user()->company_bio_image)
                                <img src="{{ asset('storage/' . auth('office')->user()->company_bio_image) }}" alt="Bio" class="image-preview" id="bio-preview">
                            @else
                                <img src="https://via.placeholder.com/150/6366f1/ffffff?text=No+Image" alt="Bio" class="image-preview" id="bio-preview">
                            @endif
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

                <!-- Location with Google Maps -->
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

                <!-- Schedule Selector -->
                <div class="form-card">
                    <h2 class="form-title"><i class="fas fa-clock"></i> Working Hours</h2>
                    <div class="helper-text" style="margin-bottom: 16px;">Select the days you're available and set your working hours</div>

                    <div class="schedule-grid" id="scheduleGrid">
                        <!-- JavaScript will populate this -->
                    </div>

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
        </div>
    </div>

    <script>
        // Theme toggle
        function toggleTheme() {
            const mainContent = document.querySelector('.main-content');
            const icon = document.getElementById('theme-icon');
            const currentTheme = mainContent.getAttribute('data-theme');
            if (currentTheme === 'dark') {
                mainContent.removeAttribute('data-theme');
                icon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                mainContent.setAttribute('data-theme', 'dark');
                icon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.querySelector('.main-content').setAttribute('data-theme', 'dark');
                document.getElementById('theme-icon').className = 'fas fa-sun';
            }

            // ✅ Initialize schedule
            initializeSchedule();

            // ✅ Initialize Google Map
            initMap();
        });

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

        // ✅ FIXED: Initialize Schedule with existing data
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

            // ✅ Parse existing schedule from database
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
                const isChecked = existingSchedule[day.name] ? 'checked' : '';
                const openTime = existingSchedule[day.name]?.open || day.defaultOpen;
                const closeTime = existingSchedule[day.name]?.close || day.defaultClose;
                const disabled = existingSchedule[day.name] ? '' : 'disabled';

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

        // ✅ FIXED: Build schedule before submit
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

            console.log('Schedule being saved:', schedule); // Debug
            document.getElementById('availability_schedule').value = JSON.stringify(schedule);
        });

        // ✅ Google Maps
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

            // Update info if location already set
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
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>
</body>
</html>
