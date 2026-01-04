<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register Office - Dream Mulk</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .register-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            padding: 48px 40px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-section h1 {
            font-size: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }

        .logo-section p {
            color: #6b7280;
            font-size: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-group input.error,
        .form-group select.error,
        .form-group textarea.error {
            border-color: #ef4444;
        }

        .error-message {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }

        .register-button {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            margin-top: 8px;
        }

        .register-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .divider {
            text-align: center;
            margin: 32px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            background: white;
            padding: 0 16px;
            position: relative;
            color: #9ca3af;
            font-size: 14px;
        }

        .login-link {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }

        .login-link a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .register-container {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo-section">
            <h1>🏢 Register Your Office</h1>
            <p>Join Dream Mulk Real Estate Platform</p>
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('office.register.submit') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="company_name">Company Name *</label>
                <input
                    type="text"
                    id="company_name"
                    name="company_name"
                    value="{{ old('company_name') }}"
                    required
                    class="{{ $errors->has('company_name') ? 'error' : '' }}"
                    placeholder="Dream Real Estate LLC"
                >
                @error('company_name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="{{ $errors->has('email') ? 'error' : '' }}"
                        placeholder="office@example.com"
                    >
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone_number">Phone Number *</label>
                    <input
                        type="tel"
                        id="phone_number"
                        name="phone_number"
                        value="{{ old('phone_number') }}"
                        required
                        class="{{ $errors->has('phone_number') ? 'error' : '' }}"
                        placeholder="+964 750 123 4567"
                    >
                    @error('phone_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="{{ $errors->has('password') ? 'error' : '' }}"
                        placeholder="Min. 8 characters"
                    >
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password *</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        placeholder="Re-enter password"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City *</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="{{ old('city') }}"
                        required
                        class="{{ $errors->has('city') ? 'error' : '' }}"
                        placeholder="Erbil"
                    >
                    @error('city')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="district">District</label>
                    <input
                        type="text"
                        id="district"
                        name="district"
                        value="{{ old('district') }}"
                        placeholder="Downtown"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="office_address">Office Address</label>
                <textarea
                    id="office_address"
                    name="office_address"
                    rows="3"
                    placeholder="Full office address..."
                >{{ old('office_address') }}</textarea>
            </div>

            <div class="form-group">
                <label for="years_experience">Years of Experience</label>
                <input
                    type="number"
                    id="years_experience"
                    name="years_experience"
                    value="{{ old('years_experience', 0) }}"
                    min="0"
                    max="100"
                    placeholder="0"
                >
            </div>

            <button type="submit" class="register-button">
                Create Office Account
            </button>
        </form>

        <div class="divider">
            <span>Already have an account?</span>
        </div>

        <div class="login-link">
            <a href="{{ route('office.login') }}">Sign in to your office portal</a>
        </div>
    </div>
</body>
</html>
