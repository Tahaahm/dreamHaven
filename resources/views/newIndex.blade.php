<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>Dream Mulk - Premium Real Estate</title>
    <meta content="Luxury real estate platform in Kurdistan" name="description" />
    <meta content="real estate, kurdistan, erbil, property" name="keywords" />

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #303b97;
            --primary-dark: #1e2456;
            --primary-light: #4a56c4;
            --accent: #d4af37;
            --accent-dark: #b8941f;
            --text-dark: #1a1a2e;
            --text-light: #eef1f5;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            background: #0a0e27;
            color: var(--text-light);
        }

        /* ============ GLASSMORPHIC NAVBAR ============ */
        .glass-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            padding: 15px 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-navbar.scrolled {
            background: rgba(10, 14, 39, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            padding: 10px 0;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-logo i {
            font-size: 32px;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-logo:hover {
            transform: translateY(-2px);
        }

        .nav-menu {
            display: flex;
            gap: 40px;
            align-items: center;
            list-style: none;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            position: relative;
            padding: 8px 0;
            transition: all 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--accent), #f0d077);
            transition: width 0.3s ease;
        }

        .nav-link:hover {
            color: var(--accent);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-cta {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .notification-icon {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            transition: all 0.3s ease;
            text-decoration: none;
            backdrop-filter: blur(10px);
        }

        .notification-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(135deg, #ff2d20, #ff5540);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(255, 45, 32, 0.5);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .user-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.6);
        }

        .btn-login {
            padding: 10px 30px;
            background: transparent;
            border: 2px solid var(--accent);
            border-radius: 50px;
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, var(--accent), #f0d077);
            color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 28px;
            cursor: pointer;
        }

        /* ============ HERO SECTION ============ */
        .hero-section {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(48, 59, 151, 0.9), rgba(30, 36, 86, 0.95)),
                        url('{{ asset('images/design-house-modern-villa-with-open-plan-living-private-bedroom-wing-large-terrace-with-privacy.jpg') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .hero-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, transparent 0%, rgba(10, 14, 39, 0.6) 100%);
        }

        .hero-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: rgba(212, 175, 55, 0.3);
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-100px) translateX(100px); }
        }

        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 900px;
            padding: 0 20px;
        }

        .hero-subtitle {
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 20px;
            animation: fadeInUp 0.8s ease;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(48px, 8vw, 92px);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #fff, #e0e7ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .hero-description {
            font-size: 20px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 50px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============ GLASS SEARCH BAR ============ */
        .glass-search-container {
            animation: fadeInUp 0.8s ease 0.6s both;
        }

        .glass-search {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }

        .glass-search-input {
            width: 100%;
            padding: 20px 70px 20px 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 60px;
            color: #fff;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .glass-search-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--accent);
            box-shadow: 0 8px 32px rgba(212, 175, 55, 0.3);
        }

        .glass-search-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .glass-search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            border: none;
            border-radius: 50%;
            color: var(--primary-dark);
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .glass-search-btn:hover {
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.5);
        }

        /* ============ SCROLL INDICATOR ============ */
        .scroll-indicator {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
        }

        .scroll-indicator i {
            font-size: 32px;
            color: rgba(255, 255, 255, 0.7);
        }

        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(10px); }
        }

        /* ============ SERVICES SECTION ============ */
        .services-section {
            position: relative;
            padding: 120px 20px;
            background: linear-gradient(180deg, #0a0e27 0%, #1a1e3e 100%);
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }

        .section-subtitle {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 15px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(36px, 5vw, 56px);
            font-weight: 700;
            color: #fff;
            margin-bottom: 20px;
        }

        .section-description {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .service-card {
            position: relative;
            height: 550px;
            border-radius: 30px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.9) 100%);
            transition: all 0.5s ease;
            z-index: 1;
        }

        .service-card:hover::before {
            background: linear-gradient(180deg, transparent 0%, rgba(48, 59, 151, 0.95) 100%);
        }

        .service-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .service-card:hover .service-bg {
            transform: scale(1.1);
        }

        .service-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 40px;
            z-index: 2;
            transform: translateY(0);
            transition: all 0.5s ease;
        }

        .service-icon {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .service-icon i {
            font-size: 32px;
            color: var(--accent);
        }

        .service-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 15px;
        }

        .service-text {
            font-size: 16px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 25px;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: all 0.5s ease;
        }

        .service-card:hover .service-text {
            max-height: 200px;
            opacity: 1;
        }

        .service-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            border: none;
            border-radius: 50px;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .service-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.5);
        }

        /* ============ ABOUT SECTION ============ */
        .about-section {
            position: relative;
            padding: 120px 20px;
            background: linear-gradient(180deg, #1a1e3e 0%, #0a0e27 100%);
        }

        .about-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            padding: 80px 60px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .about-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(36px, 5vw, 52px);
            font-weight: 700;
            color: #fff;
            margin-bottom: 30px;
            line-height: 1.2;
        }

        .about-content p {
            font-size: 18px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 25px;
        }

        .quote-box {
            background: rgba(212, 175, 55, 0.1);
            border-left: 4px solid var(--accent);
            padding: 25px 30px;
            border-radius: 12px;
            margin-top: 30px;
            font-style: italic;
            color: var(--accent);
            font-weight: 600;
            font-size: 18px;
        }

        .values-list {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .value-item {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
        }

        .value-item:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            transform: translateX(10px);
        }

        .value-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 28px;
            flex-shrink: 0;
        }

        .value-text h4 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }

        .value-text span {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
        }

        /* ============ AGENT FAB ============ */
        .agent-fab {
            position: fixed;
            bottom: 40px;
            right: 40px;
            background: linear-gradient(135deg, var(--accent), #f0d077);
            color: var(--primary-dark);
            padding: 18px 35px;
            border-radius: 60px;
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.5);
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .agent-fab:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(212, 175, 55, 0.7);
            color: var(--primary-dark);
        }

        .agent-fab i {
            font-size: 20px;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 992px) {
            .nav-menu {
                display: none;
            }

            .mobile-toggle {
                display: block;
            }

            .about-grid {
                grid-template-columns: 1fr;
                gap: 50px;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .glass-card {
                padding: 50px 30px;
            }

            .navbar-container {
                padding: 0 20px;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 48px;
            }

            .hero-description {
                font-size: 16px;
            }

            .section-title {
                font-size: 36px;
            }

            .agent-fab {
                bottom: 20px;
                right: 20px;
                padding: 14px 24px;
                font-size: 14px;
            }
        }

        /* ============ BACK TO TOP ============ */
        .back-to-top {
            position: fixed;
            bottom: 40px;
            left: 40px;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 998;
        }

        .back-to-top.active {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: var(--accent);
            color: var(--primary-dark);
            transform: translateY(-5px);
        }
    </style>
</head>

<body>

    <!-- GLASSMORPHIC NAVBAR -->
    <nav class="glass-navbar" id="navbar">
        <div class="navbar-container">
            <a href="{{ route('newindex') }}" class="nav-logo">
                <i class="fas fa-gem"></i>
                <span>Dream Mulk</span>
            </a>

            <ul class="nav-menu">
                <li><a href="{{ route('newindex') }}" class="nav-link">Home</a></li>
                <li><a href="{{ route('property.list') }}" class="nav-link">Properties</a></li>
                <li><a href="{{ route('about-us') }}" class="nav-link">About</a></li>
                <li><a href="{{ route('contact-us') }}" class="nav-link">Contact</a></li>
            </ul>

            @php
                $user = \Illuminate\Support\Facades\Auth::user();
                $agent = \Illuminate\Support\Facades\Auth::guard('agent')->user();
                $unreadCount = 0;
                if ($user) {
                    $unreadCount = \DB::table('notifications')
                        ->where('user_id', $user->id)
                        ->where('is_read', false)
                        ->where(function($query) {
                            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                        })->count();
                }
            @endphp

            <div class="nav-cta">
                @if($user || $agent)
                    <a href="{{ route('user.appointments') }}" class="notification-icon">
                        <i class="fas fa-calendar-check"></i>
                    </a>

                    <a href="{{ route('user.notifications') }}" class="notification-icon">
                        <i class="fas fa-bell"></i>
                        @if($unreadCount > 0)
                            <span class="notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                        @endif
                    </a>

                    @php
                        if ($user) {
                            $displayName = $user->username ?? $user->name ?? 'User';
                            $redirectRoute = route('user.profile');
                        } else {
                            $displayName = $agent->agent_name;
                            $redirectRoute = route('agent.profile.page');
                        }
                    @endphp

                    <a href="{{ $redirectRoute }}">
                        <div class="user-avatar">{{ strtoupper(substr($displayName, 0, 1)) }}</div>
                    </a>
                @else
                    <a href="{{ route('login-page') }}" class="btn-login">Login</a>
                @endif

                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="hero-background"></div>

        <div class="hero-particles">
            <div class="particle" style="width: 4px; height: 4px; top: 20%; left: 10%; animation-delay: 0s;"></div>
            <div class="particle" style="width: 6px; height: 6px; top: 60%; left: 80%; animation-delay: 2s;"></div>
            <div class="particle" style="width: 3px; height: 3px; top: 40%; left: 30%; animation-delay: 4s;"></div>
            <div class="particle" style="width: 5px; height: 5px; top: 70%; left: 60%; animation-delay: 6s;"></div>
        </div>

        <div class="hero-content">
            <div class="hero-subtitle" data-aos="fade-up">Premium Real Estate</div>
            <h1 class="hero-title" data-aos="fade-up" data-aos-delay="100">DREAM Mulk</h1>
            <p class="hero-description" data-aos="fade-up" data-aos-delay="200">
                A revolutionary platform to buy, sell, and rent premium properties across Kurdistan without any agent fees or commissions.
            </p>

            <div class="glass-search-container" data-aos="fade-up" data-aos-delay="300">
                <div class="glass-search">
                    <input
                        type="text"
                        class="glass-search-input"
                        placeholder="Search by city, area, or property type..."
                        id="searchInput"
                    >
                    <button class="glass-search-btn" id="searchBtn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- SERVICES SECTION -->
    <section class="services-section">
        <div class="section-header" data-aos="fade-up">
            <div class="section-subtitle">Our Services</div>
            <h2 class="section-title">What We Offer</h2>
            <p class="section-description">
                Explore our comprehensive real estate services designed to make your property journey seamless and rewarding.
            </p>
        </div>

        <div class="services-grid">
            <div class="service-card" data-aos="fade-up" data-aos-delay="100">
                <img src="{{ asset('images/AdobeStock_565645717.jpeg') }}" alt="Buy Property" class="service-bg">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3 class="service-title">Buy a Property</h3>
                    <p class="service-text">
                        Discover your dream home with our advanced search filters. Browse exclusive listings across Kurdistan with detailed insights.
                    </p>
                    <a href="{{ route('property.list') }}" class="service-btn">
                        <span>Explore Properties</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="service-card" data-aos="fade-up" data-aos-delay="200">
                <img src="{{ asset('images/house.jpg') }}" alt="Sell Property" class="service-bg">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3 class="service-title">Sell a Property</h3>
                    <p class="service-text">
                        List your property and connect with serious buyers. Our platform ensures maximum visibility and competitive pricing.
                    </p>
                    @php
                        $user = Auth::user();
                        $agentId = session('agent_id');
                    @endphp
                    <a href="{{ ($user || $agentId) ? route('property.upload') : route('login-page') }}" class="service-btn">
                        <span>List Your Property</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="service-card" data-aos="fade-up" data-aos-delay="300">
                <img src="{{ asset('images/giving house keys.webp') }}" alt="Rent Property" class="service-bg">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="service-title">Rent a Property</h3>
                    <p class="service-text">
                        Find the perfect rental that fits your lifestyle and budget. Browse verified listings with transparent pricing.
                    </p>
                    <a href="{{ route('property.list', ['type' => 'rent']) }}" class="service-btn">
                        <span>Find Rentals</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about-section">
        <div class="about-container" data-aos="fade-up">
            <div class="glass-card">
                <div class="about-grid">
                    <div class="about-content">
                        <h2>The Dream Mulk Standard</h2>
                        <p>
                            Dream Mulk was established with a singular, powerful ambition: to elevate the standard of real estate in Kurdistan. We are not merely agents; we are the architects of your next chapter.
                        </p>
                        <p>
                            In a market often defined by complexity, we serve as your beacon of clarity and sophistication. Our journey is fueled by a commitment to modern technology and timeless integrity.
                        </p>
                        <div class="quote-box">
                            "Property is land, but 'Mulk' is legacy. We help you build yours."
                        </div>
                    </div>

                    <div class="values-list">
                        <div class="value-item" data-aos="fade-left" data-aos-delay="100">
                            <div class="value-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="value-text">
                                <h4>Exclusivity</h4>
                                <span>Curated Portfolio</span>
                            </div>
                        </div>

                        <div class="value-item" data-aos="fade-left" data-aos-delay="200">
                            <div class="value-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="value-text">
                                <h4>Integrity</h4>
                                <span>Radical Transparency</span>
                            </div>
                        </div>

                        <div class="value-item" data-aos="fade-left" data-aos-delay="300">
                            <div class="value-icon">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <div class="value-text">
                                <h4>Erbil Based</h4>
                                <span>Est. 2026</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- AGENT FAB -->
    <a href="{{ route('agent.login') }}" class="agent-fab" data-aos="fade-left">
        <i class="fas fa-user-shield"></i>
        <span>Agent Portal</span>
    </a>

    <!-- BACK TO TOP -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- SCRIPTS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // AOS Init
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Navbar Scroll Effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Search Functionality
        document.getElementById('searchBtn').addEventListener('click', () => {
            const query = document.getElementById('searchInput').value;
            if (query.trim()) {
                window.location.href = `{{ route('properties.search') }}?q=${encodeURIComponent(query)}`;
            }
        });

        document.getElementById('searchInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const query = e.target.value;
                if (query.trim()) {
                    window.location.href = `{{ route('properties.search') }}?q=${encodeURIComponent(query)}`;
                }
            }
        });

        // Back to Top
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.classList.add('active');
            } else {
                backToTop.classList.remove('active');
            }
        });

        backToTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Mobile Toggle (placeholder - implement drawer if needed)
        document.getElementById('mobileToggle').addEventListener('click', () => {
            alert('Mobile menu - implement drawer navigation here');
        });
    </script>

</body>
</html>
