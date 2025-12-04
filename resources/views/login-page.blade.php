<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Registration</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap");
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
        }

        a {
            text-decoration: none;
        }



        /* Home Section */
        .home {
            position: relative;
            height: 100vh;
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .home::before {
            content: "";
            position: absolute;
            height: 100%;
            width: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 100;
            opacity: 1;
            pointer-events: auto;
            transition: opacity 0.4s ease;
        }

        /* Form Container */
        .form_container {
            position: fixed;
            max-width: 420px;
            width: 90%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(1);
            z-index: 101;
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            opacity: 1;
            pointer-events: auto;
        }

        .signup_form {
            display: none;
        }

        .form_container.active .signup_form {
            display: block;
        }

        .form_container.active .login_form {
            display: none;
        }

        .form_close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 32px;
            height: 32px;
            display: none; /* Hidden by default since there's no overlay to close */
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border-radius: 8px;
            color: #6b7280;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .form_close:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .form_container h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .form_subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 30px;
        }

        /* Input Box */
        .input_box {
            position: relative;
            margin-top: 24px;
            width: 100%;
        }

        .input_box label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input_box input {
            height: 48px;
            width: 100%;
            border: 2px solid #e5e7eb;
            outline: none;
            padding: 0 48px 0 16px;
            color: #1f2937;
            font-size: 15px;
            background: #f9fafb;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .input_box input[type="file"] {
            padding: 12px 16px;
            height: auto;
        }

        .input_box input:focus {
            border-color: #667eea;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input_box i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #9ca3af;
            transition: color 0.2s ease;
        }

        .input_box i.email,
        .input_box i.password,
        .input_box i.uil-user,
        .input_box i.uil-phone,
        .input_box i.uil-image {
            left: 16px;
        }

        .input_box input:focus ~ i {
            color: #667eea;
        }

        .input_box i.pw_hide {
            right: 16px;
            font-size: 18px;
            cursor: pointer;
        }

        /* Option Field */
        .option_field {
            margin-top: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form_container a {
            color: #667eea;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .form_container a:hover {
            color: #764ba2;
        }

        .checkbox {
            display: flex;
            align-items: center;
            column-gap: 8px;
        }

        .checkbox input {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
            cursor: pointer;
        }

        .checkbox label {
            font-size: 14px;
            color: #4b5563;
            cursor: pointer;
            user-select: none;
        }

        /* Submit Button */
        .form_container .button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin-top: 28px;
            width: 100%;
            padding: 14px 0;
            border-radius: 10px;
            font-size: 16px;
        }

        .login_signup {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
            margin-top: 24px;
        }

        .login_signup a {
            font-weight: 600;
        }

        /* Error Messages */
        .text-danger {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }

        .error-message,
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 16px;
            border-left: 3px solid #dc2626;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 16px;
            border-left: 3px solid #16a34a;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 0 20px;
            }

            .nav_item {
                column-gap: 20px;
            }

            .nav_link {
                font-size: 14px;
            }

            .form_container {
                padding: 32px 24px;
                max-width: 380px;
            }

            .form_container h2 {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .nav_items {
                display: none;
            }
        }
    </style>
</head>
<body>
<!-- Home -->
    <section class="home">
        <div class="form_container">
            <i class="uil uil-times form_close"></i>

            <!-- Login Form -->
            <form class="login_form" action="{{ route('login') }}" method="POST">
                @csrf
                <h2>Welcome Back</h2>
                <p class="form_subtitle">Enter your credentials to access your account</p>

                <div class="input_box">
                    <label for="login_email">Email Address</label>
                    <input type="email" id="login_email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required />
                    <i class="uil uil-envelope-alt email"></i>
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input_box">
                    <label for="login_password">Password</label>
                    <input type="password" id="login_password" name="password" placeholder="Enter your password" required />
                    <i class="uil uil-lock password"></i>
                    <i class="uil uil-eye-slash pw_hide"></i>
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="option_field">
                    <span class="checkbox">
                        <input type="checkbox" id="check" />
                        <label for="check">Remember me</label>
                    </span>
                    <a href="#" class="forgot_pw">Forgot password?</a>
                </div>

                @if(session('error') && old('active_form') === 'login-section')
                    <div class="error-message">
                        {{ session('error') }}
                    </div>
                @endif

                <button class="button">Sign In</button>

<div id="google_button" style="margin-top: 20px;"></div>

<script>
    window.onload = function () {
        google.accounts.id.initialize({
            client_id: "YOUR_GOOGLE_CLIENT_ID",
            callback: handleGoogleResponse
        });

        google.accounts.id.renderButton(
            document.getElementById("google_button"),
            { theme: "outline", size: "large", width: "100%" }
        );
    };

    function handleGoogleResponse(response) {
        // This returns the ID TOKEN from Google
        let id_token = response.credential;

        fetch("{{ route('auth.google') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                id_token: id_token,
                device_name: navigator.userAgent,
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Save token if needed
                console.log("Login success:", data);

                // Redirect user after login
                window.location.href = "/dashboard";
            } else {
                alert(data.message || "Google login failed");
            }
        })
        .catch(err => console.error(err));
    }
</script>


                <div class="login_signup">
                    Don't have an account? <a href="#" id="signup">Create Account</a>
                </div>
            </form>

            <!-- Signup Form -->
            <div class="form signup_form">
                <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h2>Create Account</h2>
                    <p class="form_subtitle">Fill in your details to get started</p>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="input_box">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="johndoe" required />
                        <i class="uil uil-user"></i>
                        @error('username')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input_box">
                        <label for="signup_email">Email Address</label>
                        <input type="email" id="signup_email" name="email" placeholder="you@example.com" value="{{ old('email', session('email')) }}" required />
                        <i class="uil uil-envelope-alt email"></i>
                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input_box">
                        <label for="signup_password">Password</label>
                        <input type="password" id="signup_password" name="password" placeholder="Create a password" required />
                        <i class="uil uil-lock password"></i>
                        <i class="uil uil-eye-slash pw_hide"></i>
                        @error('password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <input type="hidden" name="role" value="user">

                    <div class="input_box">
                        <label for="password_confirm">Confirm Password</label>
                        <input type="password" id="password_confirm" name="password_confirmation" placeholder="Confirm your password" required />
                        <i class="uil uil-lock password"></i>
                        <i class="uil uil-eye-slash pw_hide"></i>
                        @error('password_confirmation')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input_box">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" pattern="[0-9]{10,15}" placeholder="1234567890" required />
                        <i class="uil uil-phone"></i>
                        @error('phone')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input_box">
                        <label for="image">Profile Image (Optional)</label>
                        <input type="file" id="image" name="image" accept="image/*" />
                        @error('image')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    @if(session('error') && old('active_form') === 'signup-section')
                        <div class="error-message">
                            {{ session('error') }}
                        </div>
                    @endif

                    <button class="button">Create Account</button>

                    <div class="login_signup">
                        Already have an account? <a href="#" id="login">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        const formContainer = document.querySelector(".form_container"),
            formCloseBtn = document.querySelector(".form_close"),
            signupBtn = document.querySelector("#signup"),
            loginBtn = document.querySelector("#login"),
            pwShowHide = document.querySelectorAll(".pw_hide");

        formCloseBtn.addEventListener("click", () => {
            window.location.href = '/'; // Redirect to home or previous page
        });

        pwShowHide.forEach((icon) => {
            icon.addEventListener("click", () => {
                let getPwInput = icon.parentElement.querySelector("input");
                if (getPwInput.type === "password") {
                    getPwInput.type = "text";
                    icon.classList.replace("uil-eye-slash", "uil-eye");
                } else {
                    getPwInput.type = "password";
                    icon.classList.replace("uil-eye", "uil-eye-slash");
                }
            });
        });

        signupBtn.addEventListener("click", (e) => {
            e.preventDefault();
            formContainer.classList.add("active");
        });

        loginBtn.addEventListener("click", (e) => {
            e.preventDefault();
            formContainer.classList.remove("active");
        });
    </script>
</body>
</html>