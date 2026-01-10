<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Agent Registration - Dream Mulk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #303b97 0%, #1e2875 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
            padding: 48px;
        }

        .logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo i {
            font-size: 48px;
            color: #303b97;
            margin-bottom: 16px;
        }

        .logo h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .logo p {
            color: #6b7280;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #303b97;
            box-shadow: 0 0 0 3px rgba(48,59,151,0.1);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #303b97 0%, #1e2875 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(48,59,151,0.4);
        }

        .alert {
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: start;
            gap: 10px;
            font-size: 14px;
        }

        .alert-error {
            background: rgba(239,68,68,0.1);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,0.2);
        }

        .login-link {
            text-align: center;
            margin-top: 24px;
            color: #6b7280;
            font-size: 14px;
        }

        .login-link a {
            color: #303b97;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <i class="fas fa-user-plus"></i>
            <h1>Agent Registration</h1>
            <p>Join Dream Mulk as a real estate agent</p>
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('agent.register.submit') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="agent_name">Full Name *</label>
                <input type="text" id="agent_name" name="agent_name" value="{{ old('agent_name') }}" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label for="primary_email">Email Address *</label>
                <input type="email" id="primary_email" name="primary_email" value="{{ old('primary_email') }}" placeholder="agent@example.com" required>
            </div>

            <div class="form-group">
                <label for="primary_phone">Phone Number *</label>
                <input type="text" id="primary_phone" name="primary_phone" value="{{ old('primary_phone') }}" placeholder="+964 750 123 4567" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
                </div>
            </div>

            <div class="form-group">
                <label for="license_number">License Number (Optional)</label>
                <input type="text" id="license_number" name="license_number" value="{{ old('license_number') }}" placeholder="RE-123456">
            </div>

            <div class="form-group">
                <label for="city">City *</label>
                <select id="city" name="city" required>
                    <option value="">Select City</option>
                    <option value="Erbil" {{ old('city') == 'Erbil' ? 'selected' : '' }}>Erbil</option>
                    <option value="Sulaymaniyah" {{ old('city') == 'Sulaymaniyah' ? 'selected' : '' }}>Sulaymaniyah</option>
                    <option value="Duhok" {{ old('city') == 'Duhok' ? 'selected' : '' }}>Duhok</option>
                    <option value="Baghdad" {{ old('city') == 'Baghdad' ? 'selected' : '' }}>Baghdad</option>
                    <option value="Basra" {{ old('city') == 'Basra' ? 'selected' : '' }}>Basra</option>
                    <option value="Mosul" {{ old('city') == 'Mosul' ? 'selected' : '' }}>Mosul</option>
                </select>
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-user-check"></i> Create Agent Account
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="{{ route('agent.login') }}">Login here</a>
        </div>
    </div>
</body>
</html>
