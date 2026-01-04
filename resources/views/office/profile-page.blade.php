<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Office Profile - Dream Mulk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
        }

        /* Navbar */
        nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Profile Container */
        .profile-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 48px;
            border-radius: 24px;
            text-align: center;
            color: white;
            margin-bottom: 32px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            color: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 700;
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .profile-name {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .profile-type {
            font-size: 16px;
            opacity: 0.9;
        }

        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-title i {
            color: #667eea;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .error-message {
            color: #ef4444;
            font-size: 13px;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .profile-header {
                padding: 32px 24px;
            }

            .profile-card {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <div class="nav-content">
            <a href="{{ route('newindex') }}" class="logo">
                <i class="fas fa-home"></i> Dream Mulk
            </a>
            <div class="nav-actions">
                <a href="{{ route('office.dashboard') }}" class="nav-btn">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <form action="{{ route('office.logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Profile Content -->
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                {{ strtoupper(substr($office->company_name, 0, 1)) }}
            </div>
            <div class="profile-name">{{ $office->company_name }}</div>
            <div class="profile-type">
                <i class="fas fa-building"></i> Real Estate Office
                @if($office->is_verified)
                    <i class="fas fa-check-circle" style="color: #10b981;"></i>
                @endif
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Profile Information Form -->
        <div class="profile-card">
            <h2 class="card-title">
                <i class="fas fa-user-circle"></i> Office Information
            </h2>
            <form action="{{ route('office.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="company_name">Company Name *</label>
                    <input
                        type="text"
                        id="company_name"
                        name="company_name"
                        value="{{ old('company_name', $office->company_name) }}"
                        required
                    >
                    @error('company_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email_address">Email Address *</label>
                        <input
                            type="email"
                            id="email_address"
                            name="email_address"
                            value="{{ old('email_address', $office->email_address) }}"
                            required
                        >
                        @error('email_address')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Phone Number *</label>
                        <input
                            type="tel"
                            id="phone_number"
                            name="phone_number"
                            value="{{ old('phone_number', $office->phone_number) }}"
                            required
                        >
                        @error('phone_number')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input
                            type="text"
                            id="city"
                            name="city"
                            value="{{ old('city', $office->city) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="district">District</label>
                        <input
                            type="text"
                            id="district"
                            name="district"
                            value="{{ old('district', $office->district) }}"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="office_address">Office Address</label>
                    <textarea
                        id="office_address"
                        name="office_address"
                        rows="3"
                    >{{ old('office_address', $office->office_address) }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="years_experience">Years of Experience</label>
                        <input
                            type="number"
                            id="years_experience"
                            name="years_experience"
                            value="{{ old('years_experience', $office->years_experience) }}"
                            min="0"
                        >
                    </div>

                    <div class="form-group">
                        <label for="properties_sold">Properties Sold</label>
                        <input
                            type="number"
                            id="properties_sold"
                            name="properties_sold"
                            value="{{ old('properties_sold', $office->properties_sold) }}"
                            min="0"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="company_bio">Company Bio</label>
                    <textarea
                        id="company_bio"
                        name="company_bio"
                        rows="4"
                    >{{ old('company_bio', $office->company_bio) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="about_company">About Company</label>
                    <textarea
                        id="about_company"
                        name="about_company"
                        rows="5"
                    >{{ old('about_company', $office->about_company) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="profile_image">Profile Image</label>
                    <input
                        type="file"
                        id="profile_image"
                        name="profile_image"
                        accept="image/jpeg,image/png,image/jpg"
                    >
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>

        <!-- Password Change Form -->
        <div class="profile-card">
            <h2 class="card-title">
                <i class="fas fa-lock"></i> Change Password
            </h2>
            <form action="{{ route('office.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                    >
                    @error('current_password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">New Password *</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                        >
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password *</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-key"></i> Update Password
                </button>
            </form>
        </div>
    </div>
</body>
</html>
