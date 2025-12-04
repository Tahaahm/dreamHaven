<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            background: #ffffff;
            padding: 48px 40px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 440px;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .icon-container {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .icon-container svg {
            width: 32px;
            height: 32px;
            color: white;
        }

        h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 12px;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 15px;
            color: #64748b;
            text-align: center;
            margin-bottom: 32px;
        }

        .message {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .message.success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        .message.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .message-icon {
            flex-shrink: 0;
            margin-top: 1px;
        }

        form {
            margin-top: 28px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            font-size: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            color: #1a202c;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        input[type="text"]::placeholder {
            color: #94a3b8;
        }

        button {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .help-text {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }

        .help-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .help-text a:hover {
            color: #764ba2;
        }

        @media (max-width: 480px) {
            .container {
                padding: 36px 28px;
            }

            h2 {
                font-size: 24px;
            }
        }

        .resend-link {
    background: none;
    border: none;
    color: #0b73dbff;
    text-decoration: underline;
    cursor: pointer;
    font-size: 14px;
    margin-top: 10px;
    padding: 10px;
}

.resend-link:hover {
    color: #004999;
}
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-container">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>

        <h2>Verify Your Email</h2>
        <p class="subtitle">Enter the verification code sent to your email address.</p>

        @if(session('success'))
            <div class="message success">
                <span class="message-icon">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="message error">
                <span class="message-icon">!</span>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

      <!-- Verification Form -->
<form method="POST" action="{{ route('verify.code') }}">
    @csrf
    <!-- Remove this hidden user_id input -->
    <!-- <input type="hidden" name="user_id" value="{{ $user->id }}"> -->

    <div class="input-group">
        <label for="code">Verification Code</label>
        <input type="text" id="code" name="code" placeholder="Enter 6-digit code" required maxlength="6">
    </div>

    <button type="submit" class="verify-btn">Verify Email Address</button>
</form>


<!-- Resend Code as text link -->
<form method="POST" action="{{ route('resend.code') }}">
    @csrf
    <input type="hidden" name="user_id" value="{{ $user->id }}">
    <button type="submit" class="resend-link">Resend Code</button>
</form>


    </div>
</body>
</html>