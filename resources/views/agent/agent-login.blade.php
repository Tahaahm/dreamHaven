<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Agent Login - Dream Mulk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #303b97;
            --primary-light: #4c58c0;
            --secondary: #1e2875;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --error: #ef4444;
            --success: #10b981;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
            color: var(--text-main);
        }

        /* Decorative background elements */
        body::before {
            content: "";
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(48, 59, 151, 0.3) 0%, rgba(48, 59, 151, 0) 70%);
            top: -10%;
            right: -10%;
            z-index: -1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 460px;
            padding: 50px;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 20px rgba(48, 59, 151, 0.3);
            transform: rotate(-5deg);
            transition: transform 0.3s ease;
        }

        .login-card:hover .icon-circle {
            transform: rotate(0deg) scale(1.05);
        }

        .icon-circle i {
            font-size: 32px;
            color: white;
        }

        .logo-section h1 {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .logo-section p {
            color: var(--text-muted);
            font-size: 15px;
            font-weight: 500;
        }

        /* Alerts */
        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error { background: #fef2f2; color: var(--error); border: 1px solid #fee2e2; }
        .alert-success { background: #ecfdf5; color: var(--success); border: 1px solid #d1fae5; }
        .alert i { margin-right: 12px; font-size: 18px; }

        /* Form Styling */
        .input-group {
            margin-bottom: 24px;
            position: relative;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 8px;
            margin-left: 4px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            transition: color 0.3s;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: #f9fafb;
            border: 2px solid #f3f4f6;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .input-wrapper input:focus {
            outline: none;
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(48, 59, 151, 0.08);
        }

        .input-wrapper input:focus + i {
            color: var(--primary);
        }

        /* Button styling */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(48, 59, 151, 0.3);
            filter: brightness(1.1);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Footer links */
        .footer-links {
            text-align: center;
            margin-top: 32px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            margin-left: 5px;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-section">
            <div class="icon-circle">
                <i class="fas fa-building-user"></i>
            </div>
            <h1>Dream Mulk</h1>
            <p>Agent Portal Access</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('agent.login.submit') }}" method="POST">
            @csrf

            <div class="input-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required>
                </div>
            </div>

            <div class="input-group">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label for="password">Password</label>
                </div>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Sign In
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="footer-links">
            Don't have an agent account?
            <a href="{{ route('agent.register') }}">Create one now</a>
        </div>
    </div>

</body>
</html>
