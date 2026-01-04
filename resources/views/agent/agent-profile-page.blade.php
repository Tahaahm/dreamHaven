<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profile Settings - Dream Mulk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #303b97;
            --primary-dark: #1e2660;
            --primary-light: #4b68ff;
            --secondary: #FFE24B;
            --accent: #B0C7FF;
            --success: #388e3c;
            --warning: #f57c00;
            --danger: #d32f2f;
            --info: #1976d2;
            --gray-50: #f9f9f9;
            --gray-100: #f4f4f4;
            --gray-200: #e0e0e0;
            --gray-300: #d0d0d0;
            --gray-400: #939393;
            --gray-500: #6c757d;
            --gray-600: #4f4f4f;
            --gray-700: #333333;
            --gray-800: #272727;
            --gray-900: #1c1b1b;
            --background-light: #f3f5ff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--background-light);
            color: var(--gray-900);
            line-height: 1.6;
            min-height: 100vh;
            padding-top: 80px;
        }

        /* Header Section */
        .profile-header {
            background: linear-gradient(135deg, rgba(48, 59, 151, 0.95) 0%, rgba(30, 38, 96, 0.95) 100%);
            backdrop-filter: blur(10px);
            padding: 40px 0 60px;
            position: relative;
            overflow: visible;
            margin-top: 0;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.4;
            z-index: 0;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 10;
        }

        .profile-hero {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .avatar-container {
            position: relative;
            z-index: 10;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 800;
            color: var(--primary);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2), 0 0 0 6px rgba(255, 255, 255, 0.1);
            border: 3px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .profile-avatar:hover {
            transform: translateY(-4px);
        }

        .verified-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--success) 0%, #2e7d32 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255, 255, 255, 0.9);
            box-shadow: var(--shadow-lg);
            animation: pulse 2s infinite;
            z-index: 11;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .verified-badge i {
            color: white;
            font-size: 14px;
        }

        .profile-info {
            z-index: 10;
        }

        .profile-info h1 {
            font-size: 2.25rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-subtitle {
            font-size: 1.0625rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Main Content */
        .main-wrapper {
            max-width: 1400px;
            margin: -30px auto 4rem;
            padding: 0 2rem;
            position: relative;
            z-index: 20;
        }

        .content-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 2rem;
        }

        /* Sidebar */
        .sidebar-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-xl);
            height: fit-content;
            position: sticky;
            top: 100px;
            border: 1px solid var(--gray-200);
        }

        .stat-grid {
            display: grid;
            gap: 1rem;
        }

        .stat-item {
            background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
            padding: 1.5rem;
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-900);
            display: block;
            margin-bottom: 0.25rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Main Cards */
        .main-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-xl);
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .main-card:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-100);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-title i {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-radius: 12px;
            font-size: 1rem;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control, .form-select, textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
            background: white;
            color: var(--gray-900);
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus, .form-select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(48, 59, 151, 0.1);
            background: white;
        }

        .form-control::placeholder {
            color: var(--gray-400);
        }

        .form-control:disabled {
            background-color: var(--gray-100);
            cursor: not-allowed;
            opacity: 0.7;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Buttons */
        .btn-primary {
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px rgba(48, 59, 151, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(48, 59, 151, 0.3);
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-warning {
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, var(--warning) 0%, #e65100 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px rgba(245, 124, 0, 0.2);
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 124, 0, 0.3);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9375rem;
            font-weight: 500;
            border: 2px solid;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
            border-color: var(--success);
            color: #1b5e20;
        }

        .alert-success i {
            color: var(--success);
        }

        .alert-danger {
            background: linear-gradient(135deg, #ffcdd2 0%, #ef9a9a 100%);
            border-color: var(--danger);
            color: #b71c1c;
        }

        .alert-danger i {
            color: var(--danger);
        }

        /* Grid Layout */
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .form-full {
            grid-column: 1 / -1;
        }

        /* Input Icons */
        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            z-index: 5;
        }

        .input-group .form-control {
            padding-left: 2.75rem;
        }

        .text-muted {
            font-size: 0.8125rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
            display: block;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .content-layout {
                grid-template-columns: 280px 1fr;
            }
        }

        @media (max-width: 992px) {
            .content-layout {
                grid-template-columns: 1fr;
            }

            .sidebar-card {
                position: relative;
                top: 0;
            }

            .stat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }

            .profile-hero {
                flex-direction: column;
                text-align: center;
            }

            .profile-info h1 {
                font-size: 1.75rem;
            }

            .form-row, .form-row-3 {
                grid-template-columns: 1fr;
            }

            .stat-grid {
                grid-template-columns: 1fr;
            }

            .main-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

@include('navbar', ['navbarStyle' => 'navbar-light'])

<!-- Header -->
<div class="profile-header">
    <div class="header-content">
        <div class="profile-hero">
            <div class="avatar-container">
                <div class="profile-avatar">
                    {{ strtoupper(substr(auth()->guard('agent')->user()->agent_name, 0, 1)) }}
                </div>
                @if(auth()->guard('agent')->user()->is_verified)
                <div class="verified-badge">
                    <i class="fas fa-check"></i>
                </div>
                @endif
            </div>

            <div class="profile-info">
                <h1>{{ auth()->guard('agent')->user()->agent_name }}</h1>
                <p class="profile-subtitle">Real Estate Professional</p>
                @if(auth()->guard('agent')->user()->is_verified)
                <span class="badge-verified">
                    <i class="fas fa-shield-check"></i>
                    Verified Agent
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main-wrapper">
    <div class="content-layout">
        <!-- Sidebar -->
        <aside>
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i class="fas fa-chart-line"></i>
                    Your Statistics
                </h3>
                <div class="stat-grid">
                    <div class="stat-item">
                        <span class="stat-value">{{ \DB::table('properties')->where('owner_id', auth()->guard('agent')->id())->where('owner_type', 'Agent')->count() }}</span>
                        <span class="stat-label">Properties</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ auth()->guard('agent')->user()->appointments()->count() }}</span>
                        <span class="stat-label">Appointments</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">{{ auth()->guard('agent')->user()->years_experience ?? 0 }}</span>
                        <span class="stat-label">Years Exp.</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main>
            @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Profile Information Card -->
            <div class="main-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-user"></i>
                        Profile Information
                    </h2>
                </div>

                <form method="POST" action="{{ route('agent.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" class="form-control" name="agent_name"
                                       value="{{ auth()->guard('agent')->user()->agent_name }}"
                                       placeholder="Enter your full name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" class="form-control"
                                       value="{{ auth()->guard('agent')->user()->primary_email }}"
                                       placeholder="your@email.com" disabled>
                            </div>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <div class="input-group">
                                <i class="fas fa-phone input-icon"></i>
                                <input type="text" class="form-control" name="phone"
                                       value="{{ auth()->guard('agent')->user()->primary_phone ?? '' }}"
                                       placeholder="+964 750 000 0000">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">License Number</label>
                            <div class="input-group">
                                <i class="fas fa-id-card input-icon"></i>
                                <input type="text" class="form-control" name="license_number"
                                       value="{{ auth()->guard('agent')->user()->license_number ?? '' }}"
                                       placeholder="License #">
                            </div>
                        </div>

                        <div class="form-group form-full">
                            <label class="form-label">Professional Bio</label>
                            <textarea class="form-control" name="bio" rows="4"
                                      placeholder="Tell clients about your expertise and experience...">{{ auth()->guard('agent')->user()->agent_bio ?? '' }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </form>
            </div>

            <!-- Security Card -->
            <div class="main-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-shield-alt"></i>
                        Security Settings
                    </h2>
                </div>

                <form method="POST" action="{{ route('agent.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" class="form-control" name="current_password"
                                       placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <i class="fas fa-key input-icon"></i>
                                <input type="password" class="form-control" name="password"
                                       placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <i class="fas fa-check-circle input-icon"></i>
                                <input type="password" class="form-control" name="password_confirmation"
                                       placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-warning">
                        <i class="fas fa-key"></i>
                        Update Password
                    </button>
                </form>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-hide alerts after 5 seconds
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
});
</script>

<style>
@keyframes slideOut {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}
</style>

</body>
</html>
