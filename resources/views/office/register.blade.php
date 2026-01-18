<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register Office - Dream Mulk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary: #303B97;
            --primary-hover: #252d75;
            --primary-light: rgba(48, 59, 151, 0.1);
            --card-bg: rgba(255, 255, 255, 0.98);
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --error: #ef4444;
            --border: #f3f4f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, #1a2052 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        body::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            z-index: -1;
        }

        .register-container {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 650px;
            padding: 50px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .logo-section { text-align: center; margin-bottom: 40px; }

        .office-badge {
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary);
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .logo-section h1 {
            font-size: 30px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 30px 0 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::after {
            content: "";
            height: 1px;
            background: var(--border);
            flex-grow: 1;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
            font-size: 13.5px;
            margin-left: 4px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--border);
            background: #f9fafb;
            border-radius: 14px;
            font-size: 15px;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .form-group select:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .register-button {
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 18px;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            box-shadow: 0 10px 20px -5px rgba(48, 59, 151, 0.4);
        }

        .register-button:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(48, 59, 151, 0.5);
        }

        .divider {
            text-align: center;
            margin: 35px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: #9ca3af;
            font-size: 13px;
        }

        .login-link {
            text-align: center;
            font-size: 14px;
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 25px;
            font-size: 14px;
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 640px) {
            .form-row { grid-template-columns: 1fr; }
            .register-container { padding: 35px 25px; }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo-section">
            <span class="office-badge">Partner Registration</span>
            <h1>Dream Mulk</h1>
            <p>Create your professional office account today.</p>
        </div>

        @if($errors->any())
            <div class="alert">
                <strong>Please correct the following:</strong>
                <ul style="margin-top: 8px; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('office.register.submit') }}" method="POST" id="registerForm">
            @csrf

            <div class="section-title">Company Information</div>

            <div class="form-group">
                <label for="company_name">Company Name *</label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. Dream Real Estate LLC">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Business Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="office@company.com">
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number *</label>
                    <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required placeholder="+964 7XX XXX XXXX">
                </div>
            </div>

            <div class="section-title">Security</div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required placeholder="Min. 8 characters">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Repeat password">
                </div>
            </div>

            <div class="section-title">Location & Experience</div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city-select">City *</label>
                    <select id="city-select" required>
                        <option value="">Loading cities...</option>
                    </select>
                    <input type="hidden" id="city" name="city" value="{{ old('city') }}">
                </div>
                <div class="form-group">
                    <label for="area-select">District *</label>
                    <select id="area-select" disabled required>
                        <option value="">Select City First</option>
                    </select>
                    <input type="hidden" id="district" name="district" value="{{ old('district') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="office_address">Full Office Address</label>
                <textarea id="office_address" name="office_address" rows="2" placeholder="Street, Building, Floor...">{{ old('office_address') }}</textarea>
            </div>

            <div class="form-group" style="max-width: 200px;">
                <label for="years_experience">Years of Experience</label>
                <input type="number" id="years_experience" name="years_experience" value="{{ old('years_experience', 0) }}" min="0">
            </div>

            <button type="submit" class="register-button">
                <i class="fas fa-user-plus"></i> Create Office Account
            </button>
        </form>

        <div class="divider">
            <span>Already registered?</span>
        </div>

        <div class="login-link">
            <a href="{{ route('office.login') }}"><i class="fas fa-arrow-left"></i> Back to Office Sign In</a>
        </div>
    </div>

    <script src="{{ asset('js/location-selector.js') }}"></script>
    <script>
        // Initialize LocationSelector for registration
        let locationSelector;

        window.addEventListener('DOMContentLoaded', async function() {
            console.log('=== Registration Location Selector Initialization ===');

            try {
                // Create LocationSelector instance
                locationSelector = new LocationSelector({
                    citySelectId: 'city-select',
                    areaSelectId: 'area-select',
                    cityInputId: 'city',
                    districtInputId: 'district'
                });

                // Initialize (loads cities and sets up event listeners)
                await locationSelector.init();
                console.log('✓ Location selector initialized successfully');

                // Restore old values if form validation failed
                const oldCity = "{{ old('city') }}";
                const oldDistrict = "{{ old('district') }}";

                if (oldCity && oldCity.trim() !== '') {
                    console.log('Restoring saved city:', oldCity);
                    const citySet = await locationSelector.setCityByName(oldCity);

                    if (citySet) {
                        console.log('✓ City restored successfully');

                        // Wait for areas to load, then restore district
                        if (oldDistrict && oldDistrict.trim() !== '') {
                            await new Promise(resolve => setTimeout(resolve, 500));
                            console.log('Restoring saved district:', oldDistrict);
                            const districtSet = locationSelector.setAreaByName(oldDistrict);

                            if (districtSet) {
                                console.log('✓ District restored successfully');
                            } else {
                                console.warn('✗ Failed to restore district');
                            }
                        }
                    } else {
                        console.warn('✗ Failed to restore city');
                    }
                } else {
                    console.log('No saved values to restore');
                }

                console.log('=== Initialization Complete ===');

            } catch (error) {
                console.error('!!! Failed to initialize location selector:', error);

                // Show user-friendly error
                const citySelect = document.getElementById('city-select');
                if (citySelect) {
                    citySelect.innerHTML = '<option value="">Failed to load cities - Please refresh</option>';
                }
            }
        });
    </script>
</body>
</html>
