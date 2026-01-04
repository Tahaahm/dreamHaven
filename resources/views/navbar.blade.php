<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Dream Haven Navigation</title>

  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;500;600;700&display=swap");
    * { box-sizing: border-box; margin:0; padding:0; font-family:"Inter",sans-serif; }
    a { text-decoration: none; color: inherit; }

    /* ---------- Header / Desktop layout ---------- */
    .unique-header {
      position: fixed;
      top: 0;
      left: 0;
      right:0;
      height: 80px;
      width: 100%;
      z-index: 1100;
      padding: 0 20px;
      transition: transform 0.3s ease, background-color 0.25s ease;
      background: transparent;
      display: flex;
      align-items: center;
    }

    .unique-nav {
      max-width: 1100px;
      width: 100%;
      margin: 0 auto;
      display: flex;
      height: 100%;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .unique-nav-left { display:flex; align-items:center; gap:12px; }
    .unique-nav-logo {
      font-size: 25px;
      color:#fff;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .unique-nav-items { display:flex; align-items:center; gap:8px; }
    .unique-nav-item { display:flex; gap:25px; align-items:center; }
    .unique-nav-link {
      color:#fff;
      padding:6px 12px;
      border-radius: 6px;
      transition: all 0.3s ease;
    }

    .unique-nav-link:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .unique-button {
      padding: 8px 24px;
      border: 2px solid #fff;
      background: transparent;
      border-radius: 8px;
      cursor: pointer;
      color:#fff;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .unique-button:hover {
      background: #fff;
      color: #667eea;
    }

    .user-initial-circle {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:600;
      text-transform:uppercase;
      cursor:pointer;
      transition: transform .15s ease, box-shadow .15s ease;
      font-size: 16px;
    }
    .user-initial-circle:hover {
      transform: translateY(-2px) scale(1.05);
      box-shadow:0 6px 18px rgba(102, 126, 234, 0.4);
    }

    /* ---------- Notification Bell Styles ---------- */
    .notification-bell-wrapper {
      position: relative;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin: 0 12px;
    }

    .notification-bell-link {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
      transition: all 0.3s ease;
    }

    .notification-bell-link:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: scale(1.1);
    }

    .notification-bell-link i {
      font-size: 1.2rem;
    }

    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: linear-gradient(135deg, #ff2d20 0%, #ff5540 100%);
      color: white;
      font-size: 0.65rem;
      font-weight: bold;
      padding: 2px 5px;
      border-radius: 10px;
      min-width: 18px;
      text-align: center;
      animation: pulse 2s infinite;
      box-shadow: 0 2px 8px rgba(255, 45, 32, 0.5);
      line-height: 1.2;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.15); }
    }

    /* Light mode notification bell */
    .unique-header.navbar-light .notification-bell-link {
      background: rgba(102, 126, 234, 0.1);
      color: #667eea;
    }

    .unique-header.navbar-light .notification-bell-link:hover {
      background: rgba(102, 126, 234, 0.2);
    }

    /* ---------- Mobile: hamburger + drawer ---------- */
    .menu-toggle {
      display: none;
      background: transparent;
      border: none;
      font-size: 24px;
      color: #fff;
      cursor: pointer;
      align-items: center;
    }

    .nav-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.45);
      opacity: 0;
      pointer-events: none;
      transition: opacity .25s ease;
      z-index: 1090;
      backdrop-filter: blur(2px);
    }
    .nav-backdrop.show { opacity: 1; pointer-events: auto; }

    .nav-drawer {
      position: fixed;
      top: 0;
      right: -100%;
      height: 100vh;
      width: min(420px, 86%);
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.98), rgba(118, 75, 162, 0.98));
      color: #fff;
      z-index: 1100;
      padding: 34px 22px;
      display: flex;
      flex-direction: column;
      gap: 18px;
      transition: right .32s cubic-bezier(.2,.9,.3,1);
      box-shadow: -8px 30px 60px rgba(0,0,0,0.45);
      overflow-y: auto;
    }
    .nav-drawer.open { right: 0; }

    .drawer-header {
      display:flex;
      align-items:center;
      justify-content:space-between;
    }
    .drawer-title { font-size:20px; font-weight:700; color:#fff; }

    .drawer-links { display:flex; flex-direction:column; gap:14px; margin-top:12px; }
    .drawer-links a {
      font-size:16px;
      padding:12px 16px;
      border-radius:8px;
      color:#fff;
      transition: all 0.3s ease;
    }
    .drawer-links a:hover { background: rgba(255,255,255,0.15); }

    .drawer-cta { margin-top: 12px; display:flex; gap:10px; align-items:center; }
    .drawer-cta .unique-button { padding:10px 18px; }

    .drawer-divider { height:1px; background: rgba(255,255,255,0.15); margin:12px 0; border-radius:2px; }

    /* Notification bell in mobile drawer */
    .drawer-notification-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 16px;
      border-radius: 8px;
      color: #fff;
      transition: all 0.3s ease;
    }
    .drawer-notification-item:hover {
      background: rgba(255,255,255,0.15);
    }
    .drawer-notification-content {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    /* ---------- Responsive rules ---------- */
    @media (max-width: 900px) {
      .unique-nav-items { display: none; }
      .menu-toggle { display: inline-flex; }
      .unique-header { padding: 0 14px; }
      .notification-bell-wrapper { display: none; }
    }

    @media (max-width: 900px) {
      .unique-header a[aria-label="Login"],
      .unique-header a[aria-label="Go to dashboard"] {
        display: none !important;
      }
    }

    button:focus, a:focus { outline: 3px solid rgba(102, 126, 234, 0.3); outline-offset:2px; border-radius:6px; }

    /* ---------- LIGHT MODE ---------- */
    .unique-header.navbar-light {
      background: white !important;
      border-bottom: 1px solid rgba(0,0,0,0.08);
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .unique-header.navbar-light .unique-nav-logo,
    .unique-header.navbar-light .unique-nav-link,
    .unique-header.navbar-light .menu-toggle,
    .unique-header.navbar-light .uil {
      color: #1e293b !important;
    }

    .unique-header.navbar-light .unique-nav-link:hover {
      background: rgba(102, 126, 234, 0.1);
    }

    .unique-header.navbar-light .unique-button {
      color: #667eea !important;
      border-color: #667eea !important;
    }

    .unique-header.navbar-light .unique-button:hover {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
      color: #fff !important;
    }

    .nav-drawer.light {
      background: white !important;
      color: #1e293b !important;
    }

    .nav-drawer.light .drawer-title,
    .nav-drawer.light a,
    .nav-drawer.light i {
      color: #1e293b !important;
    }

    .nav-drawer.light .drawer-links a:hover,
    .nav-drawer.light .drawer-notification-item:hover {
      background: rgba(102, 126, 234, 0.1) !important;
    }

    .nav-drawer.light .drawer-divider {
      background: rgba(0,0,0,0.12) !important;
    }
  </style>
</head>
<body>

<header class="unique-header {{ $navbarStyle ?? '' }}" id="navbar">

  <nav class="unique-nav" role="navigation" aria-label="Primary">
    <div class="unique-nav-left">
      <a href="{{ route('newindex') }}" class="unique-nav-logo">
        <i class="fas fa-home"></i>
        <span>Dream Mulk</span>
      </a>
    </div>

    <div class="unique-nav-items" id="desktop-links">
      <div class="unique-nav-item">
        <a class="unique-nav-link {{ request()->routeIs('newindex') ? ' active' : '' }}" href="{{ route('newindex') }}">Home</a>
        <a class="unique-nav-link {{ request()->routeIs('property.list') ? ' active' : '' }}" href="{{ route('property.list') }}">Properties</a>
        <a class="unique-nav-link {{ request()->routeIs('about-us') ? ' active' : '' }}" href="{{ route('about-us') }}">About Us</a>
        <a class="unique-nav-link {{ request()->routeIs('contact-us') ? ' active' : '' }}" href="{{ route('contact-us') }}">Contact</a>
      </div>
    </div>

    @php
      $user = \Illuminate\Support\Facades\Auth::user();
      $agent = \Illuminate\Support\Facades\Auth::guard('agent')->user();

      // Get unread notifications count
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
          <i class="fas fa-calendar-check"></i>
        </a>

        <a href="{{ route('user.notifications') }}" class="notification-bell-link" title="Notifications">
          <i class="fas fa-bell"></i>
          @if($unreadCount > 0)
            <span class="notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
          @endif
        </a>
      </div>
    @endif

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

      <a href="{{ $redirectRoute }}" aria-label="Go to profile">
        <div class="user-initial-circle" title="{{ $displayName }}">
          {{ strtoupper(substr($displayName, 0, 1)) }}
        </div>
      </a>
    @else
      <a href="{{ route('login-page') }}" aria-label="Login">
        <button class="unique-button">Login</button>
      </a>
    @endif

    <button id="hamburger" class="menu-toggle" aria-controls="mobile-drawer" aria-expanded="false" aria-label="Open menu">
      <i class="uil uil-bars" id="hamburger-icon"></i>
    </button>

  </nav>
</header>

<div id="nav-backdrop" class="nav-backdrop" tabindex="-1" aria-hidden="true"></div>

<aside id="mobile-drawer" class="nav-drawer" role="dialog" aria-labelledby="drawer-title" aria-hidden="true">
  <div class="drawer-header">
    <div class="drawer-title" id="drawer-title">Menu</div>
    <button id="drawer-close" aria-label="Close menu" style="background:transparent;border:none;color:#fff;font-size:20px;cursor:pointer;">
      <i class="uil uil-times"></i>
    </button>
  </div>

  <nav class="drawer-links" aria-label="Mobile links">
    <a href="{{ route('newindex') }}" class="drawer-link" data-close>Home</a>
    <a href="{{ route('property.list') }}" class="drawer-link" data-close>Properties</a>
    <a href="{{ route('about-us') }}" class="drawer-link" data-close>About Us</a>
    <a href="{{ route('contact-us') }}" class="drawer-link" data-close>Contact</a>

    @if($user || $agent)
      <div class="drawer-divider" aria-hidden="true"></div>

      <a href="{{ route('user.appointments') }}" class="drawer-notification-item" data-close>
        <div class="drawer-notification-content">
          <i class="fas fa-calendar-check"></i>
          <span>My Appointments</span>
        </div>
      </a>

      <a href="{{ route('user.notifications') }}" class="drawer-notification-item" data-close>
        <div class="drawer-notification-content">
          <i class="fas fa-bell"></i>
          <span>Notifications</span>
        </div>
        @if($unreadCount > 0)
          <span class="notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
      </a>
    @endif
  </nav>

  <div class="drawer-divider" aria-hidden="true"></div>

  <div class="drawer-cta" aria-hidden="false">
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
      <a href="{{ $redirectRoute }}" style="display:inline-flex; align-items:center; gap:10px;">
        <div class="user-initial-circle">{{ strtoupper(substr($displayName, 0, 1)) }}</div>
        <div style="font-weight:600;">{{ \Illuminate\Support\Str::limit($displayName, 16) }}</div>
      </a>
    @else
      <a href="{{ route('login-page') }}">
        <button class="unique-button">Login</button>
      </a>
    @endif
  </div>

  <div style="margin-top:auto; font-size:12px; color:rgba(255,255,255,0.6); text-align: center;">
    Dream Haven &nbsp;•&nbsp; Real Estate Platform
  </div>
</aside>

<script>
  let lastScrollTop = 0;
  const navbar = document.getElementById('navbar');

  window.addEventListener('scroll', function() {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    if (currentScroll > lastScrollTop && currentScroll > 100) {
      navbar.style.transform = 'translateY(-100%)';
    } else {
      navbar.style.transform = 'translateY(0)';
    }
    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
  }, false);
</script>

<script>
  (function(){
    const hamburger = document.getElementById('hamburger');
    const hamburgerIcon = document.getElementById('hamburger-icon');
    const drawer = document.getElementById('mobile-drawer');
    const backdrop = document.getElementById('nav-backdrop');
    const closeBtn = document.getElementById('drawer-close');
    const focusablesSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';
    let lastFocused = null;

    function openDrawer() {
      lastFocused = document.activeElement;
      drawer.classList.add('open');
      backdrop.classList.add('show');
      drawer.setAttribute('aria-hidden', 'false');
      backdrop.setAttribute('aria-hidden', 'false');
      hamburger.setAttribute('aria-expanded', 'true');
      hamburgerIcon.className = 'uil uil-times';
      const focusables = drawer.querySelectorAll(focusablesSelector);
      if (focusables.length) focusables[0].focus();
      document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
      drawer.classList.remove('open');
      backdrop.classList.remove('show');
      drawer.setAttribute('aria-hidden', 'true');
      backdrop.setAttribute('aria-hidden', 'true');
      hamburger.setAttribute('aria-expanded', 'false');
      hamburgerIcon.className = 'uil uil-bars';
      document.body.style.overflow = '';
      if (lastFocused) lastFocused.focus();
    }

    hamburger.addEventListener('click', function(e){
      const expanded = hamburger.getAttribute('aria-expanded') === 'true';
      if (expanded) closeDrawer(); else openDrawer();
    });

    closeBtn.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);

    drawer.querySelectorAll('[data-close]').forEach(el => {
      el.addEventListener('click', function(){ closeDrawer(); });
    });

    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape' && drawer.classList.contains('open')) {
        closeDrawer();
      }
    });

    window.addEventListener('resize', function(){
      if (window.innerWidth > 900 && drawer.classList.contains('open')) {
        closeDrawer();
      }
    });
  })();
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const navbar = document.getElementById("navbar");
    const drawer = document.getElementById("mobile-drawer");

    if (navbar.classList.contains("navbar-light")) {
        drawer.classList.add("light");
    }
});
</script>

</body>
</html>
