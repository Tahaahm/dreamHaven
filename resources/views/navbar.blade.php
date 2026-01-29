<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>

  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #303b97;       /* Dream Mulk Blue */
      --primary-dark: #1a225a;
      --gold: #d4af37;          /* Luxury Gold */
      --gold-hover: #b5952f;
      --white: #ffffff;
      --glass: rgba(255, 255, 255, 0.95);
      --glass-dark: rgba(48, 59, 151, 0.95);
    }

    * { box-sizing: border-box; margin:0; padding:0; }
    body { font-family: 'Poppins', sans-serif; }
    a { text-decoration: none; color: inherit; transition: all 0.3s ease; }

    /* ---------- Header Layout ---------- */
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

    /* ---------- Logo Styling ---------- */
    .unique-nav-logo {
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      color: var(--white);
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 15px;
      letter-spacing: 0.5px;
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
        border-color: var(--gold);
    }

    /* ---------- Desktop Links ---------- */
    .unique-nav-items { display: flex; align-items: center; gap: 40px; }
    .unique-nav-item { display: flex; gap: 35px; align-items: center; }

    .unique-nav-link {
      color: rgba(255,255,255,0.85);
      font-size: 15px;
      font-weight: 400;
      position: relative;
      padding: 5px 0;
      letter-spacing: 0.5px;
    }

    .unique-nav-link:hover, .unique-nav-link.active {
      color: var(--gold);
    }

    .unique-nav-link::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 0;
      background-color: var(--gold);
      transition: width 0.3s ease;
    }

    .unique-nav-link:hover::after, .unique-nav-link.active::after {
      width: 100%;
    }

    /* ---------- Buttons & User ---------- */
    .unique-button {
      padding: 10px 28px;
      border: 1px solid var(--gold);
      background: transparent;
      border-radius: 50px;
      cursor: pointer;
      color: var(--gold);
      font-weight: 500;
      font-size: 14px;
      letter-spacing: 1px;
      text-transform: uppercase;
      transition: all 0.3s ease;
    }

    .unique-button:hover {
      background: var(--gold);
      color: var(--primary);
      box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
    }

    .user-initial-circle {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: var(--gold);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-family: 'Playfair Display', serif;
      cursor: pointer;
      transition: transform .2s ease;
      border: 2px solid rgba(255,255,255,0.2);
    }

    .user-initial-circle:hover {
      transform: scale(1.1);
      box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
    }

    /* ---------- Notification Bell ---------- */
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
    }

    .notification-bell-link:hover {
      color: var(--gold);
      transform: translateY(-2px);
    }

    .notification-badge {
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

    /* ---------- Mobile Toggle ---------- */
    .menu-toggle {
      display: none;
      background: transparent;
      border: none;
      font-size: 28px;
      color: var(--gold);
      cursor: pointer;
    }

    /* ---------- Mobile Drawer ---------- */
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
        background: var(--gold);
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
    }
    .drawer-links a:hover, .drawer-links a.active {
        color: var(--gold);
        padding-left: 10px;
    }

    /* Light Mode Overrides */
    .unique-header.navbar-light {
      background: rgba(255,255,255,0.95) !important;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

    .unique-header.navbar-light .unique-nav-logo { color: var(--primary) !important; }
    .unique-header.navbar-light .brand-logo-img { border-color: var(--primary); box-shadow:none; }
    .unique-header.navbar-light .unique-nav-link { color: var(--text-dark) !important; }
    .unique-header.navbar-light .unique-nav-link:hover { color: var(--primary) !important; }
    .unique-header.navbar-light .unique-nav-link::after { background-color: var(--primary); }
    .unique-header.navbar-light .notification-bell-link { color: var(--primary); }
    .unique-header.navbar-light .menu-toggle { color: var(--primary); }

    .unique-header.navbar-light .unique-button {
      border-color: var(--primary);
      color: var(--primary);
    }
    .unique-header.navbar-light .unique-button:hover {
      background: var(--primary);
      color: var(--white);
    }

    @media (max-width: 900px) {
      .unique-nav-items, .notification-bell-wrapper, .user-initial-circle { display: none !important; }
      .menu-toggle { display: block; }
      .unique-header { padding: 0 20px; height: 70px; }
      .unique-header.scrolled { height: 70px; }
    }
  </style>
</head>
<body>

<header class="unique-header {{ $navbarStyle ?? '' }}" id="navbar">

  <nav class="unique-nav" role="navigation" aria-label="Primary">
    <div class="unique-nav-left">
      <a href="{{ route('newindex') }}" class="unique-nav-logo">
        <img src="{{ asset('logo_dream_mulk.png') }}" alt="Dream Mulk" class="brand-logo-img">
        <span>Dream Mulk</span>
      </a>
    </div>

    <div class="unique-nav-items" id="desktop-links">
      <div class="unique-nav-item">
        <a class="unique-nav-link {{ request()->routeIs('newindex') ? ' active' : '' }}" href="{{ route('newindex') }}">Home</a>
        <a class="unique-nav-link {{ request()->routeIs('property.list') ? ' active' : '' }}" href="{{ route('property.list') }}">Properties</a>
        <a class="unique-nav-link {{ request()->routeIs('about-us') ? ' active' : '' }}" href="{{ route('about-us') }}">About Us</a>
        <a class="unique-nav-link {{ request()->routeIs('contact-us') ? ' active' : '' }}" href="{{ route('contact-us') }}">Contact Us</a>
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
                <span class="notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
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
          <div class="unique-nav-items">
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
    <button id="drawer-close" aria-label="Close menu" style="background:transparent;border:none;color:var(--gold);font-size:24px;cursor:pointer;">
      <i class="uil uil-times"></i>
    </button>
  </div>

  <nav class="drawer-links" aria-label="Mobile links">
    <a href="{{ route('newindex') }}" class="{{ request()->routeIs('newindex') ? 'active' : '' }}" data-close>Home</a>
    <a href="{{ route('property.list') }}" class="{{ request()->routeIs('property.list') ? 'active' : '' }}" data-close>Properties</a>
    <a href="{{ route('about-us') }}" class="{{ request()->routeIs('about-us') ? 'active' : '' }}" data-close>About Us</a>
    <a href="{{ route('contact-us') }}" class="{{ request()->routeIs('contact-us') ? 'active' : '' }}" data-close>Contact Us</a>

    @if($user || $agent)
      <div style="height:1px; background:rgba(255,255,255,0.1); margin:15px 0;"></div>

      <a href="{{ route('user.appointments') }}" data-close style="display:flex; justify-content:space-between; align-items:center;">
        <span>My Appointments</span>
        <i class="fas fa-calendar-check" style="color:var(--gold)"></i>
      </a>

      <a href="{{ route('user.notifications') }}" data-close style="display:flex; justify-content:space-between; align-items:center;">
        <span>Notifications</span>
        @if($unreadCount > 0)
          <span class="notification-badge" style="position:static;">{{ $unreadCount }}</span>
        @else
          <i class="fas fa-bell" style="color:var(--gold)"></i>
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
        <a href="{{ $redirectRoute }}" style="display:flex; align-items:center; gap:15px; background:rgba(255,255,255,0.05); padding:15px; border-radius:12px;">
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

<script>
  const navbar = document.getElementById('navbar');

  window.addEventListener('scroll', function() {
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });

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

    hamburger.addEventListener('click', function(){
      if (drawer.classList.contains('open')) closeDrawer(); else openDrawer();
    });

    closeBtn.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);

    drawer.querySelectorAll('[data-close]').forEach(el => {
      el.addEventListener('click', closeDrawer);
    });

    window.addEventListener('resize', function(){
      if (window.innerWidth > 900 && drawer.classList.contains('open')) {
        closeDrawer();
      }
    });
  })();

  document.addEventListener("DOMContentLoaded", () => {
    if (navbar.classList.contains("navbar-light")) {
        // Light mode overrides handled by CSS
    }
  });
</script>

</body>
</html>
