<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Office Login - Dream Mulk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --brand-color: #303B97;
            --brand-hover: #252d75;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --error: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--brand-color) 0%, #1a2052 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 450px;
            padding: 50px 40px;
        }

        .logo-section { text-align: center; margin-bottom: 35px; }

        .office-badge {
            display: inline-block;
            background: rgba(48, 59, 151, 0.1);
            color: var(--brand-color);
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .logo-section h1 {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -1px;
        }

        .form-group { margin-bottom: 22px; }
        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #f3f4f6;
            background: #f9fafb;
            border-radius: 14px;
            font-size: 15px;
            transition: all 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--brand-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(48, 59, 151, 0.1);
        }

        .login-button {
            width: 100%;
            background: var(--brand-color);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 15px -3px rgba(48, 59, 151, 0.3);
        }

        .login-button:hover {
            background: var(--brand-hover);
            transform: translateY(-1px);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0 30px;
            font-size: 14px;
        }

        .forgot-password, .register-link a {
            color: var(--brand-color);
            text-decoration: none;
            font-weight: 600;
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
            border-top: 1px solid #e5e7eb;
        }

        .divider span {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 15px;
            color: #9ca3af;
            font-size: 13px;
        }

        .alert-error {
            background: #fef2f2;
            color: var(--error);
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #fee2e2;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <span class="office-badge">Office Portal</span>
            <h1>Dream Mulk</h1>
        </div>

        @if(session('error') || $errors->any())
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> Invalid credentials.
            </div>
        @endif

        <form action="{{ route('office.login.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Office Email</label>
                <input type="email" name="email" required placeholder="office@dreammulk.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <div class="remember-forgot">
                <label style="cursor:pointer; color: var(--text-muted);">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="login-button">Sign In</button>
        </form>

        <div class="divider"><span>OR</span></div>

        <div class="register-link" style="text-align:center; font-size: 14px; color: var(--text-muted);">
            Don't have an office? <a href="{{ route('office.register') }}">Register Now</a>
        </div>
    </div>
</body>
</html>
