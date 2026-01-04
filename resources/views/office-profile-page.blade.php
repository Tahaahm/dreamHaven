<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Office Profile - Dream Mulk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .profile-header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 40px 0; }
        .profile-card { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); padding: 30px; margin-top: -50px; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: bold; color: #f5576c; border: 5px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .stat-box { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-box h3 { font-size: 32px; margin-bottom: 5px; }
        .stat-box p { margin: 0; opacity: 0.9; }
    </style>
</head>
<body>

@include('navbar', ['navbarStyle' => 'navbar-light'])

<div class="profile-header">
    <div class="container text-center">
        <h1>Office Profile</h1>
        <p>Manage your real estate office information</p>
    </div>
</div>

<div class="container mb-5">
    <div class="profile-card">
        <div class="row">
            <div class="col-md-3 text-center">
                <div class="profile-avatar mx-auto">
                    {{ strtoupper(substr($office->company_name, 0, 1)) }}
                </div>
                <h4 class="mt-3">{{ $office->company_name }}</h4>
                <p class="text-muted">Real Estate Office</p>
                @if($office->is_verified)
                    <span class="badge bg-success">Verified Office</span>
                @endif
            </div>

            <div class="col-md-9">
                <h3 class="mb-4">Company Information</h3>

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

                <form method="POST" action="{{ route('office.profile.update', $office->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" name="company_name" value="{{ $office->company_name }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="{{ $office->email }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" value="{{ $office->phone ?? '' }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">License Number</label>
                            <input type="text" class="form-control" name="license_number" value="{{ $office->license_number ?? '' }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2">{{ $office->address ?? '' }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3">{{ $office->description ?? '' }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg mt-3">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>

                <hr class="my-4">

                <h3 class="mb-4">Change Password</h3>
                <form method="POST" action="{{ route('office.password.update', $office->id) }}">
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
                    <h3>{{ \DB::table('agents')->where('office_id', $office->id)->count() }}</h3>
                    <p>Total Agents</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <h3>{{ \DB::table('properties')->where('office_id', $office->id)->count() }}</h3>
                    <p>Properties Listed</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <h3>{{ $office->years_experience ?? 0 }}</h3>
                    <p>Years Experience</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
