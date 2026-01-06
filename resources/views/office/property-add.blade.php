<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - Dream Mulk</title>
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
        .search-bar i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 15px; }
        .top-actions { display: flex; align-items: center; gap: 14px; }
        .theme-toggle { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .theme-toggle:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .user-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 15px; }
        .content-area { flex: 1; overflow-y: auto; padding: 32px; background: var(--bg-main); transition: background 0.3s; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); transition: color 0.3s; }
        .back-btn { background: var(--bg-card); color: var(--text-secondary); padding: 11px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; border: 1px solid var(--border-color); transition: all 0.3s; }
        .back-btn:hover { background: var(--bg-hover); }
        .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; transition: all 0.3s; }
        .form-section { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--border-color); }
        .form-section:last-of-type { border-bottom: none; }
        .form-section-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; transition: color 0.3s; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; font-size: 14px; transition: color 0.3s; }
        .form-input, .form-select, .form-textarea { width: 100%; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 8px; padding: 12px 16px; font-size: 15px; transition: all 0.3s; font-family: inherit; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        .form-textarea { resize: vertical; min-height: 120px; }
        .form-helper { font-size: 13px; color: var(--text-muted); margin-top: 6px; }
        .image-upload-area { border: 2px dashed var(--border-color); border-radius: 12px; padding: 40px; text-align: center; background: var(--bg-main); transition: all 0.3s; cursor: pointer; }
        .image-upload-area:hover { border-color: #6366f1; background: var(--bg-hover); }
        .image-upload-icon { font-size: 48px; color: var(--text-muted); margin-bottom: 16px; }
        .image-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 16px; margin-top: 20px; }
        .image-preview-item { position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 2px solid var(--border-color); }
        .image-preview-item img { width: 100%; height: 100%; object-fit: cover; }
        .remove-image-btn { position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .submit-btn { background: #6366f1; color: white; padding: 14px 32px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .submit-btn:hover { background: #5558e3; transform: translateY(-1px); }
        .cancel-btn { background: var(--bg-hover); color: var(--text-secondary); padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; border: 1px solid var(--border-color); display: inline-block; }
        .form-actions { display: flex; gap: 12px; margin-top: 32px; }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 24px; }
        .alert-error { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }
        .map-container { width: 100%; height: 400px; border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color); margin-top: 10px; }
        #map { width: 100%; height: 100%; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fas fa-home"></i> Dream Mulk</div>
        <div class="nav-menu">
            <a href="{{ route('office.dashboard') }}" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="{{ route('office.properties') }}" class="nav-item active"><i class="fas fa-building"></i> Properties</a>
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
            <a href="{{ route('office.profile') }}" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
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
                <div class="user-avatar">{{ strtoupper(substr(auth('office')->user()->company_name, 0, 2)) }}</div>
            </div>
        </div>

        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-plus-circle"></i> Add New Property</h1>
                <a href="{{ route('office.properties') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Properties
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin: 8px 0 0 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('office.property.store') }}" method="POST" enctype="multipart/form-data" id="propertyForm">
                @csrf

                <div class="form-card">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-info-circle"></i> Basic Information</h3>

                        <div class="form-group">
                            <label class="form-label">Property Title (English) *</label>
                            <input type="text" name="name_en" class="form-input" value="{{ old('name_en') }}" required placeholder="e.g., Luxury Villa in Erbil">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Property Title (Arabic)</label>
                                <input type="text" name="name_ar" class="form-input" value="{{ old('name_ar') }}" placeholder="مثال: فيلا فاخرة في أربيل">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Property Title (Kurdish)</label>
                                <input type="text" name="name_ku" class="form-input" value="{{ old('name_ku') }}" placeholder="نموونە: ڤیلایەکی خۆشی لە هەولێر">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description (English) *</label>
                            <textarea name="description_en" class="form-textarea" required placeholder="Describe your property in detail...">{{ old('description_en') }}</textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Description (Arabic)</label>
                                <textarea name="description_ar" class="form-textarea" placeholder="صف الممتلكات الخاصة بك بالتفصيل...">{{ old('description_ar') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description (Kurdish)</label>
                                <textarea name="description_ku" class="form-textarea" placeholder="موڵکەکەت بە ووردی باس بکە...">{{ old('description_ku') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Property Details -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-home"></i> Property Details</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Listing Type *</label>
                                <select name="listing_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="sell" {{ old('listing_type') == 'sell' ? 'selected' : '' }}>For Sale</option>
                                    <option value="rent" {{ old('listing_type') == 'rent' ? 'selected' : '' }}>For Rent</option>
                                </select>
                            </div>
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
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Price (USD) *</label>
                                <input type="number" name="price_usd" class="form-input" value="{{ old('price_usd') }}" required min="0" step="0.01" placeholder="150000">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Price (IQD) *</label>
                                <input type="number" name="price_iqd" class="form-input" value="{{ old('price_iqd') }}" required min="0" step="0.01" placeholder="197250000">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Bedrooms *</label>
                                <input type="number" name="bedrooms" class="form-input" value="{{ old('bedrooms', 0) }}" required min="0" placeholder="3">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Bathrooms *</label>
                                <input type="number" name="bathrooms" class="form-input" value="{{ old('bathrooms', 0) }}" required min="0" placeholder="2">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Area (m²) *</label>
                                <input type="number" name="area" class="form-input" value="{{ old('area') }}" required min="1" step="0.01" placeholder="250">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Furnished</label>
                                <select name="furnished" class="form-select">
                                    <option value="0">Unfurnished</option>
                                    <option value="1">Furnished</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Floor Number</label>
                                <input type="number" name="floor_number" class="form-input" value="{{ old('floor_number') }}" min="0" placeholder="2">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Year Built</label>
                                <input type="number" name="year_built" class="form-input" value="{{ old('year_built') }}" min="1900" max="2030" placeholder="2020">
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Location</h3>

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
                                <input type="text" name="city_ar" class="form-input" value="{{ old('city_ar') }}" placeholder="أربيل">
                            </div>
                            <div class="form-group">
                                <label class="form-label">District (Arabic)</label>
                                <input type="text" name="district_ar" class="form-input" value="{{ old('district_ar') }}" placeholder="وسط المدينة">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">City (Kurdish)</label>
                                <input type="text" name="city_ku" class="form-input" value="{{ old('city_ku') }}" placeholder="هەولێر">
                            </div>
                            <div class="form-group">
                                <label class="form-label">District (Kurdish)</label>
                                <input type="text" name="district_ku" class="form-input" value="{{ old('district_ku') }}" placeholder="ناوەڕاستی شار">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Full Address</label>
                            <input type="text" name="address" class="form-input" value="{{ old('address') }}" placeholder="Street name, building number...">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Select Location on Map *</label>
                            <div class="form-helper">Click on the map to set the exact location</div>
                            <div class="map-container">
                                <div id="map"></div>
                            </div>
                        </div>

                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', '36.1911') }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', '44.0091') }}">
                    </div>

                    <!-- Property Images -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-images"></i> Property Images</h3>
                        <div class="form-helper" style="margin-bottom: 16px;">Upload at least 3 high-quality images (Max 10 images, 2MB each)</div>

                        <div class="image-upload-area" onclick="document.getElementById('images').click()">
                            <div class="image-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div style="font-size: 16px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Click to Upload Images</div>
                            <div style="font-size: 14px; color: var(--text-muted);">or drag and drop images here</div>
                        </div>
                        <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;" onchange="previewImages(event)">

                        <div class="image-preview-grid" id="imagePreviewGrid"></div>
                    </div>

                    <!-- Utilities & Features -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-check-square"></i> Utilities & Features</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="electricity" value="1" checked style="width: 20px; height: 20px;">
                                    <span class="form-label" style="margin: 0;">Electricity</span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="water" value="1" checked style="width: 20px; height: 20px;">
                                    <span class="form-label" style="margin: 0;">Water</span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="internet" value="1" style="width: 20px; height: 20px;">
                                    <span class="form-label" style="margin: 0;">Internet</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-check-circle"></i> Publish Property
                        </button>
                        <a href="{{ route('office.properties') }}" class="cancel-btn">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
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
            initMap();
        });

        // Image Preview
        let selectedFiles = [];
        function previewImages(event) {
            const files = Array.from(event.target.files);
            selectedFiles = files.slice(0, 10); // Max 10 images

            const grid = document.getElementById('imagePreviewGrid');
            grid.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="remove-image-btn" onclick="removeImage(${index})">
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

            // Re-render previews
            const event = { target: { files: selectedFiles } };
            previewImages(event);
        }

        // Google Maps
        let map, marker;
        function initMap() {
            const lat = parseFloat(document.getElementById('latitude').value) || 36.1911;
            const lng = parseFloat(document.getElementById('longitude').value) || 44.0091;
            const center = { lat, lng };

            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: center,
            });

            marker = new google.maps.Marker({
                position: center,
                map: map,
                draggable: true,
            });

            map.addListener('click', function(e) {
                marker.setPosition(e.latLng);
                updateCoordinates(e.latLng);
            });

            marker.addListener('dragend', function(e) {
                updateCoordinates(e.latLng);
            });
        }

        function updateCoordinates(location) {
            document.getElementById('latitude').value = location.lat().toFixed(8);
            document.getElementById('longitude').value = location.lng().toFixed(8);
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWAA1UqFQG8BzniCVqVZrvCzWHz72yoOA&callback=initMap" async defer></script>
</body>
</html>
