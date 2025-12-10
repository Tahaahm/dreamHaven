<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Dream Haven</title>
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

        .about-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 40px;
        }

        .about-container {
            max-width: 1200px;
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

        .about-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .about-header h1 {
            font-size: 56px;
            color: #303b97;
            margin-bottom: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #303b97, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .about-header p {
            font-size: 20px;
            color: #666;
            line-height: 1.8;
        }

        .story-section {
            margin-bottom: 50px;
        }

        .story-section p {
            font-size: 18px;
            color: #555;
            line-height: 1.8;
            margin-bottom: 20px;
            text-align: justify;
        }

        .team-section {
            margin-top: 60px;
        }

        .team-section h2 {
            font-size: 36px;
            color: #303b97;
            text-align: center;
            margin-bottom: 40px;
            font-weight: 700;
        }

        .team-members {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 50px;
        }

        .team-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
        }

        .team-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            color: white;
        }

        .team-card h3 {
            font-size: 22px;
            color: #303b97;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .team-card p {
            font-size: 16px;
            color: #666;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 50px;
        }

        .info-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
            padding: 30px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .info-card:hover {
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

        .info-content p {
            font-size: 18px;
            color: #333;
            font-weight: 500;
            margin: 0;
        }

        /* Mobile Responsive */
        @media screen and (max-width: 768px) {
            .about-container {
                padding: 40px 30px;
            }

            .about-header h1 {
                font-size: 36px;
            }

            .about-header p {
                font-size: 16px;
            }

            .team-section h2 {
                font-size: 28px;
            }

            .team-members {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .about-wrapper {
                padding: 100px 15px 30px;
            }

            .story-section p {
                font-size: 16px;
                text-align: left;
            }
        }

        @media screen and (max-width: 480px) {
            .about-header h1 {
                font-size: 28px;
            }

            .team-icon {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }

            .team-card h3 {
                font-size: 18px;
            }

            .info-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
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

    <div class="about-wrapper">
        <div class="about-container">
            <!-- Header Section -->
            <div class="about-header">
                <h1>About Us</h1>
                <p>Welcome to Dream Haven - Where Real Estate Dreams Come True</p>
            </div>

            <!-- Story Section -->
            <div class="story-section">
                <p>We are a group of passionate students who came together to create something beautiful. Our project started as a simple idea and grew into something much more. It's a testament to our dedication, creativity, and teamwork.</p>
                
                <p>Through hard work and collaboration, we've built a platform that we're proud of. This project has been a journey of learning and growth for all of us. We believe in making real estate accessible, transparent, and exciting for everyone.</p>
                
                <p>Thank you for visiting and being a part of our story. We're committed to helping you find your dream property and making the process as smooth as possible.</p>
            </div>

            <!-- Team Section -->
            <div class="team-section">
                <h2>Meet Our Team</h2>
                <div class="team-members">
                    <div class="team-card">
                        <div class="team-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3>Ahmad Nyaz</h3>
                        <p>Co-Founder </p>
                    </div>

                    <div class="team-card">
                        <div class="team-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3>Taha Ahmed</h3>
                        <p>Co-Founder & Developer</p>
                    </div>

                    <div class="team-card">
                        <div class="team-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h3>Zana Goran</h3>
                        <p>Co-Founder & Developer</p>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Location</h3>
                        <p>Erbil, Kurdistan, Iraq</p>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Established</h3>
                        <p>April 3, 2024</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
