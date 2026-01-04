<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile - Dream Mulk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .profile-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 0; }
        .profile-card { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); padding: 30px; margin-top: -50px; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: bold; color: #667eea; border: 5px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .stat-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-box h3 { font-size: 32px; margin-bottom: 5px; }
        .stat-box p { margin: 0; opacity: 0.9; }
    </style>
</head>
<body>

@include('navbar', ['navbarStyle' => 'navbar-light'])

<div class="profile-header">
    <div class="container text-center">
        <h1>My Profile</h1>
        <p>Manage your personal information</p>
    </div>
</div>

<div class="container mb-5">
    <div class="profile-card">
        <div class="row">
            <div class="col-md-3 text-center">
                <div class="profile-avatar mx-auto">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <h4 class="mt-3">{{ auth()->user()->name }}</h4>
                <p class="text-muted">User Account</p>
            </div>

            <div class="col-md-9">
                <h3 class="mb-4">Personal Information</h3>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('user.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" value="{{ auth()->user()->name }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" value="{{ auth()->user()->phone ?? '' }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->username }}" disabled>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg mt-3">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>

                <hr class="my-4">

                <h3 class="mb-4">Change Password</h3>
                <form method="POST" action="{{ route('user.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg mt-3">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-4">
                <div class="stat-box">
                    <h3>{{ \DB::table('appointments')->where('user_id', auth()->id())->count() }}</h3>
                    <p>Total Appointments</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <h3>{{ \DB::table('notifications')->where('user_id', auth()->id())->count() }}</h3>
                    <p>Notifications</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <h3>{{ \DB::table('users')->where('id', auth()->id())->value('created_at') ? \Carbon\Carbon::parse(\DB::table('users')->where('id', auth()->id())->value('created_at'))->format('M Y') : 'N/A' }}</h3>
                    <p>Member Since</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
