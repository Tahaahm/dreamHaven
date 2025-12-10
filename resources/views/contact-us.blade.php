<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Dream Haven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            overflow-x: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .unique-header {
            position: fixed;
            height: 80px;
            width: 100%;
            z-index: 100;
            padding: 0 20px;
            background: #303b97;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .bg-animation span {
            position: absolute;
            display: block;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            animation: float 25s infinite;
            bottom: -150px;
        }

        .bg-animation span:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
        .bg-animation span:nth-child(2) { left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
        .bg-animation span:nth-child(3) { left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
        .bg-animation span:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
        .bg-animation span:nth-child(5) { left: 65%; width: 20px; height: 20px; animation-delay: 0s; }
        .bg-animation span:nth-child(6) { left: 75%; width: 110px; height: 110px; animation-delay: 3s; }
        .bg-animation span:nth-child(7) { left: 35%; width: 150px; height: 150px; animation-delay: 7s; }
        .bg-animation span:nth-child(8) { left: 50%; width: 25px; height: 25px; animation-delay: 15s; animation-duration: 45s; }
        .bg-animation span:nth-child(9) { left: 20%; width: 15px; height: 15px; animation-delay: 2s; animation-duration: 35s; }
        .bg-animation span:nth-child(10) { left: 85%; width: 150px; height: 150px; animation-delay: 0s; animation-duration: 11s; }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 0;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 50%;
            }
        }

        .contact-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 40px;
        }

        .contact-container {
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 60px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .contact-info h1 {
            font-size: 52px;
            color: #303b97;
            margin-bottom: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #303b97, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .contact-info p {
            font-size: 18px;
            color: #666;
            margin-bottom: 50px;
            line-height: 1.6;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .info-item:hover {
            transform: translateX(10px);
            box-shadow: 0 10px 30px rgba(48, 59, 151, 0.2);
        }

        .info-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        .info-content h3 {
            font-size: 16px;
            color: #303b97;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .info-content a {
            font-size: 18px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
        }

        .info-content a:hover {
            color: #667eea;
        }

        .contact-form {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #303b97;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 18px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 50px;
            justify-content: center;
        }

        .social-link {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-5px) rotate(360deg);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        /* Mobile Responsive */
        @media screen and (max-width: 768px) {
            .contact-container {
                padding: 40px 30px;
            }

            .contact-info h1 {
                font-size: 36px;
            }

            .info-item {
                padding: 15px;
            }

            .info-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .contact-wrapper {
                padding: 100px 15px 30px;
            }
        }

        @media screen and (max-width: 480px) {
            .contact-info h1 {
                font-size: 28px;
            }

            .contact-info p {
                font-size: 16px;
            }

            .info-content a {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    @include('navbar')
    <!-- Animated Background -->
    <div class="bg-animation">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="contact-wrapper">
        <div class="contact-container">
            <!-- Contact Info Section -->
            <div class="contact-info">
                <h1>Get In Touch</h1>
                <p>Have questions about your dream property? We're here to help! Reach out to us and let's make your real estate dreams come true.</p>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3>Email Us</h3>
                        <a href="mailto:kurdmulk@gmail.com">kurdmulk@gmail.com</a>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h3>Call Us</h3>
                        <a href="tel:9647501911315">9647501911315</a> -
                           <a href="tel:9647501911315">9647517812988</a>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Visit Us</h3>
                        <a href="#">Erbil, Iraq</a>
                    </div>
                </div>

                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
