<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>Dream Mulk - Premium Real Estate</title>
    <meta content="Luxury real estate platform in Kurdistan" name="description" />
    <meta content="real estate, kurdistan, erbil, property" name="keywords" />

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet"/>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #303b97;
            --primary-dark: #1e2456;
            --accent: #d4af37;
            --accent-dark: #b8941f;
            --text-dark: #1a1a2e;
            --text-light: #eef1f5;
            --white: #ffffff;
            --glass-dark: rgba(48, 59, 151, 0.95);
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            background: #0a0e27;
            color: var(--text-light);
        }

        /* =========================================
           NEW HEADER CSS (Integrated)
           ========================================= */
        .unique-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 90px;
            width: 100%;
            z-index: 1100;
            padding: 0 40px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            background: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, transparent 100%);
            display: flex;
            align-items: center;
        }

        .unique-header.scrolled {
            background: var(--glass-dark);
            backdrop-filter: blur(12px);
            height: 80px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .unique-nav {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            height: 100%;
            align-items: center;
            justify-content: space-between;
        }

        /* --- Logo Styling --- */
        .unique-nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            color: var(--white);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
            letter-spacing: 0.5px;
            text-decoration: none;
        }

        .brand-logo-img {
            height: 55px;
            width: 55px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid var(--white);
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            background: var(--white);
            transition: transform 0.3s ease;
        }

        .unique-nav-logo:hover .brand-logo-img {
            transform: scale(1.05) rotate(5deg);
            border-color: var(--accent);
        }

        /* --- Desktop Links --- */
        .unique-nav-items { display: flex; align-items: center; gap: 40px; }
        .unique-nav-item { display: flex; gap: 35px; align-items: center; list-style: none; margin: 0; }

        .unique-nav-link {
            color: rgba(255,255,255,0.85);
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 400;
            position: relative;
            padding: 5px 0;
            letter-spacing: 0.5px;
            text-decoration: none;
        }

        .unique-nav-link:hover, .unique-nav-link.active {
            color: var(--accent);
        }

        .unique-nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--accent);
            transition: width 0.3s ease;
        }

        .unique-nav-link:hover::after, .unique-nav-link.active::after {
            width: 100%;
        }

        /* --- Buttons & User --- */
        .unique-button {
            padding: 10px 28px;
            border: 1px solid var(--accent);
            background: transparent;
            border-radius: 50px;
            cursor: pointer;
            color: var(--accent);
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .unique-button:hover {
            background: var(--accent);
            color: var(--primary-dark);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
        }

        .user-initial-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--accent);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
            cursor: pointer;
            transition: transform .2s ease;
            border: 2px solid rgba(255,255,255,0.2);
            text-decoration: none;
        }

        .user-initial-circle:hover {
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
        }

        /* --- Notification Bell --- */
        .notification-bell-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 15px;
            margin: 0 20px;
        }

        .notification-bell-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: var(--white);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .notification-bell-link:hover {
            color: var(--accent);
            transform: translateY(-2px);
        }

        .notification-badge-hdr {
            position: absolute;
            top: 0;
            right: 0;
            background: #e74c3c;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 6px;
            border: 1px solid var(--primary);
        }

        /* --- Mobile Toggle --- */
        .menu-toggle {
            display: none;
            background: transparent;
            border: none;
            font-size: 28px;
            color: var(--accent);
            cursor: pointer;
        }

        /* --- Mobile Drawer --- */
        .nav-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s ease;
            z-index: 1090;
            backdrop-filter: blur(4px);
        }
        .nav-backdrop.show { opacity: 1; pointer-events: auto; }

        .nav-drawer {
            position: fixed;
            top: 0;
            right: -100%;
            height: 100vh;
            width: min(380px, 85%);
            background: var(--primary);
            z-index: 1100;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            transition: right .4s cubic-bezier(.2,.9,.3,1);
            box-shadow: -10px 0 30px rgba(0,0,0,0.5);
        }

        .nav-drawer::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 4px;
            background: var(--accent);
        }

        .nav-drawer.open { right: 0; }

        .drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .drawer-title {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--white);
        }

        .drawer-links a {
            font-size: 18px;
            padding: 15px 0;
            color: rgba(255,255,255,0.8);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: block;
            transition: all 0.3s;
            text-decoration: none;
        }
        .drawer-links a:hover, .drawer-links a.active {
            color: var(--accent);
            padding-left: 10px;
        }

        /* Responsive Fixes for Header */
        @media (max-width: 992px) {
            .unique-nav-items, .notification-bell-wrapper, .user-initial-circle, .btn-login-desktop { display: none !important; }
            .menu-toggle { display: block; }
            .unique-header { padding: 0 20px; height: 70px; }
            .unique-header.scrolled { height: 70px; }
        }

        /* =========================================
           EXISTING PAGE CSS (Preserved)
           ========================================= */

        /* HERO SECTION */
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
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* GLASS SEARCH BAR */
        .glass-search-container { animation: fadeInUp 0.8s ease 0.6s both; }
        .glass-search { position: relative; max-width: 600px; margin: 0 auto; }

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

        .glass-search-input::placeholder { color: rgba(255, 255, 255, 0.6); }

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

        /* SCROLL INDICATOR */
        .scroll-indicator {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
        }
        .scroll-indicator i { font-size: 32px; color: rgba(255, 255, 255, 0.7); }
        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(10px); }
        }

        /* SERVICES SECTION */
        .services-section {
            position: relative;
            padding: 120px 20px;
            background: linear-gradient(180deg, #0a0e27 0%, #1a1e3e 100%);
        }

        .section-header { text-align: center; margin-bottom: 80px; }
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
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.9) 100%);
            transition: all 0.5s ease;
            z-index: 1;
        }

        .service-card:hover::before {
            background: linear-gradient(180deg, transparent 0%, rgba(48, 59, 151, 0.95) 100%);
        }

        .service-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .service-card:hover .service-bg { transform: scale(1.1); }

        .service-content {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            padding: 40px;
            z-index: 2;
            transform: translateY(0);
            transition: all 0.5s ease;
        }

        .service-icon {
            width: 70px; height: 70px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .service-icon i { font-size: 32px; color: var(--accent); }

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

        .service-card:hover .service-text { max-height: 200px; opacity: 1; }

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

        /* ABOUT SECTION */
        .about-section {
            position: relative;
            padding: 120px 20px;
            background: linear-gradient(180deg, #1a1e3e 0%, #0a0e27 100%);
        }
        .about-container { max-width: 1400px; margin: 0 auto; }

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

        .values-list { display: flex; flex-direction: column; gap: 25px; }

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
            width: 70px; height: 70px;
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

        /* AGENT FAB */
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
        .agent-fab i { font-size: 20px; }

        /* BACK TO TOP */
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
            text-decoration: none;
        }
        .back-to-top.active { opacity: 1; visibility: visible; }
        .back-to-top:hover {
            background: var(--accent);
            color: var(--primary-dark);
            transform: translateY(-5px);
        }
    </style>
</head>

<body>

    <header class="unique-header" id="navbar">
      <nav class="unique-nav" role="navigation" aria-label="Primary">
        <a href="{{ route('newindex') }}" class="unique-nav-logo">
            <img src="{{ asset('logo_dream_mulk.png') }}" alt="Dream Mulk" class="brand-logo-img">
            <span>Dream Mulk</span>
        </a>

        <div class="unique-nav-items" id="desktop-links">
          <div class="unique-nav-item">
            <a class="unique-nav-link {{ request()->routeIs('newindex') ? ' active' : '' }}" href="{{ route('newindex') }}">Home</a>
            <a class="unique-nav-link {{ request()->routeIs('property.list') ? ' active' : '' }}" href="{{ route('property.list') }}">Properties</a>
            <a class="unique-nav-link {{ request()->routeIs('about-us') ? ' active' : '' }}" href="{{ route('about-us') }}">About Us</a>
            <a class="unique-nav-link {{ request()->routeIs('contact-us') ? ' active' : '' }}" href="{{ route('contact-us') }}">Contact</a>
          </div>
        </div>

        <div style="display:flex; align-items:center;">
            @php
              $user = \Illuminate\Support\Facades\Auth::user();
              $agent = \Illuminate\Support\Facades\Auth::guard('agent')->user();

              $unreadCount = 0;
              if ($user) {
                  $unreadCount = \DB::table('notifications')
                      ->where('user_id', $user->id)
                      ->where('is_read', false)
                      ->where(function($query) {
                          $query->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                      })
                      ->count();
              }
            @endphp

            @if($user || $agent)
              <div class="notification-bell-wrapper">
                <a href="{{ route('user.appointments') }}" class="notification-bell-link" title="My Appointments">
                  <i class="far fa-calendar-alt"></i>
                </a>

                <a href="{{ route('user.notifications') }}" class="notification-bell-link" title="Notifications">
                  <i class="far fa-bell"></i>
                  @if($unreadCount > 0)
                    <span class="notification-badge-hdr">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                  @endif
                </a>
              </div>

              @php
                if ($user) {
                    $displayName = $user->username ?? $user->name ?? 'User';
                    $redirectRoute = route('user.profile');
                } else {
                    $displayName = $agent->agent_name;
                    $redirectRoute = route('agent.profile.page');
                }
              @endphp

              <a href="{{ $redirectRoute }}" aria-label="Go to profile" class="user-initial-circle" title="{{ $displayName }}">
                {{ strtoupper(substr($displayName, 0, 1)) }}
              </a>
            @else
              <div class="unique-nav-items btn-login-desktop">
                  <a href="{{ route('login-page') }}" aria-label="Login">
                    <button class="unique-button">Client Login</button>
                  </a>
              </div>
            @endif

            <button id="hamburger" class="menu-toggle" aria-controls="mobile-drawer" aria-expanded="false" aria-label="Open menu">
              <i class="uil uil-bars" id="hamburger-icon"></i>
            </button>
        </div>
      </nav>
    </header>

    <div id="nav-backdrop" class="nav-backdrop" tabindex="-1" aria-hidden="true"></div>

    <aside id="mobile-drawer" class="nav-drawer" role="dialog" aria-labelledby="drawer-title" aria-hidden="true">
      <div class="drawer-header">
        <div class="drawer-title" id="drawer-title">Dream Mulk</div>
        <button id="drawer-close" aria-label="Close menu" style="background:transparent;border:none;color:var(--accent);font-size:24px;cursor:pointer;">
          <i class="uil uil-times"></i>
        </button>
      </div>

      <nav class="drawer-links" aria-label="Mobile links">
        <a href="{{ route('newindex') }}" class="{{ request()->routeIs('newindex') ? 'active' : '' }}" data-close>Home</a>
        <a href="{{ route('property.list') }}" class="{{ request()->routeIs('property.list') ? 'active' : '' }}" data-close>Properties</a>
        <a href="{{ route('about-us') }}" class="{{ request()->routeIs('about-us') ? 'active' : '' }}" data-close>About Us</a>
        <a href="{{ route('contact-us') }}" class="{{ request()->routeIs('contact-us') ? 'active' : '' }}" data-close>Contact</a>

        @if($user || $agent)
          <div style="height:1px; background:rgba(255,255,255,0.1); margin:15px 0;"></div>

          <a href="{{ route('user.appointments') }}" data-close style="display:flex; justify-content:space-between; align-items:center;">
            <span>My Appointments</span>
            <i class="fas fa-calendar-check" style="color:var(--accent)"></i>
          </a>

          <a href="{{ route('user.notifications') }}" data-close style="display:flex; justify-content:space-between; align-items:center;">
            <span>Notifications</span>
            @if($unreadCount > 0)
              <span class="notification-badge-hdr" style="position:static;">{{ $unreadCount }}</span>
            @else
              <i class="fas fa-bell" style="color:var(--accent)"></i>
            @endif
          </a>
        @endif
      </nav>

      <div style="margin-top:auto;">
        @if($user || $agent)
            @php
            if ($user) {
                $displayName = $user->username ?? $user->name ?? 'User';
                $redirectRoute = route('user.profile');
            } else {
                $displayName = $agent->agent_name;
                $redirectRoute = route('agent.profile.page');
            }
            @endphp
            <a href="{{ $redirectRoute }}" style="display:flex; align-items:center; gap:15px; background:rgba(255,255,255,0.05); padding:15px; border-radius:12px; text-decoration:none;">
                <div class="user-initial-circle" style="width:35px; height:35px; font-size:14px;">{{ strtoupper(substr($displayName, 0, 1)) }}</div>
                <div style="color:white; font-weight:500;">{{ \Illuminate\Support\Str::limit($displayName, 16) }}</div>
            </a>
        @else
          <a href="{{ route('login-page') }}">
            <button class="unique-button" style="width:100%; text-align:center;">Client Login</button>
          </a>
        @endif
      </div>
    </aside>

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

    <a href="{{ route('agent.login') }}" class="agent-fab" data-aos="fade-left">
        <i class="fas fa-user-shield"></i>
        <span>Agent Portal</span>
    </a>

    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // AOS Init
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // 1. Updated Header Scroll Effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // 2. Updated Mobile Drawer Logic
        (function(){
            const hamburger = document.getElementById('hamburger');
            const hamburgerIcon = document.getElementById('hamburger-icon');
            const drawer = document.getElementById('mobile-drawer');
            const backdrop = document.getElementById('nav-backdrop');
            const closeBtn = document.getElementById('drawer-close');

            function openDrawer() {
                drawer.classList.add('open');
                backdrop.classList.add('show');
                hamburger.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeDrawer() {
                drawer.classList.remove('open');
                backdrop.classList.remove('show');
                hamburger.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            if(hamburger) {
                hamburger.addEventListener('click', function(){
                    if (drawer.classList.contains('open')) closeDrawer(); else openDrawer();
                });
            }

            if(closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if(backdrop) backdrop.addEventListener('click', closeDrawer);

            drawer.querySelectorAll('[data-close]').forEach(el => {
                el.addEventListener('click', closeDrawer);
            });

            window.addEventListener('resize', function(){
                if (window.innerWidth > 992 && drawer.classList.contains('open')) {
                    closeDrawer();
                }
            });
        })();

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
    </script>

</body>
</html>
