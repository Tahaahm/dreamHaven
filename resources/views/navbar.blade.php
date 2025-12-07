<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Website Navbar</title>

  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>

  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");
    * { box-sizing: border-box; margin:0; padding:0; font-family:"Poppins",sans-serif; }
    a { text-decoration: none; color: inherit; }

    /* ---------- Header / Desktop layout (unchanged appearance) ---------- */
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
    .unique-nav-logo { font-size: 25px; color:#fff; }

    .unique-nav-items { display:flex; align-items:center; gap:8px; }
    .unique-nav-item { display:flex; gap:25px; align-items:center; }
    .unique-nav-link { color:#fff; padding:6px 2px; }

    .unique-button {
      padding: 6px 24px;
      border: 2px solid #fff;
      background: transparent;
      border-radius: 6px;
      cursor: pointer;
      color:#fff;
    }

    .user-initial-circle {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background-color: #fff;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#000;
      font-weight:600;
      text-transform:uppercase;
      cursor:pointer;
      transition: transform .15s ease, box-shadow .15s ease;
    }
    .user-initial-circle:hover { transform: translateY(-2px); box-shadow:0 6px 18px rgba(0,0,0,0.25); }

    /* ---------- Mobile: hamburger + drawer ---------- */
    .menu-toggle {
      display: none; /* visible only on mobile */
      background: transparent;
      border: none;
      font-size: 24px;
      color: #fff;
      cursor: pointer;
      align-items: center;
    }

    /* drawer and backdrop */
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
      background: linear-gradient(180deg, rgba(12,12,12,0.98), rgba(8,8,8,0.98));
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
    .drawer-title { font-size:20px; font-weight:600; color:#fff; }

    .drawer-links { display:flex; flex-direction:column; gap:14px; margin-top:12px; }
    .drawer-links a { font-size:18px; padding:12px 10px; border-radius:8px; color:#fff; }
    .drawer-links a:hover { background: rgba(255,255,255,0.03); }

    .drawer-cta { margin-top: 12px; display:flex; gap:10px; align-items:center; }
    .drawer-cta .unique-button { padding:10px 18px; }

    /* Decorative small divider */
    .drawer-divider { height:1px; background: rgba(255,255,255,0.06); margin:12px 0; border-radius:2px; }

    /* ---------- Responsive rules ---------- */
    @media (max-width: 900px) {
      .unique-nav-items { display: none; } /* hide desktop links on small screens */
      .menu-toggle { display: inline-flex; }
      /* keep the profile/login visible in header on mobile (single source) */
      .unique-header { padding: 0 14px; }
    }


    /* keep a11y focus styles */
    button:focus, a:focus { outline: 3px solid rgba(255,255,255,0.12); outline-offset:2px; border-radius:6px; }
    /* Hide login/profile in header on mobile */
@media (max-width: 900px) {
  .unique-header a[aria-label="Login"],
  .unique-header a[aria-label="Go to dashboard"] {
    display: none !important;
  }
}



/* ---------------------------------------------
   LIGHT MODE NAVBAR (for white background pages)
   --------------------------------------------- */
.unique-header.navbar-light {
  background: white !important;
  border-bottom: 1px solid rgba(0,0,0,0.08);
}

.unique-header.navbar-light .unique-nav-logo,
.unique-header.navbar-light .unique-nav-link,
.unique-header.navbar-light .menu-toggle,
.unique-header.navbar-light .uil {
  color: #000 !important;
}

.unique-header.navbar-light .unique-button {
  color: #000 !important;
  border-color: #000 !important;
}

.unique-header.navbar-light .unique-button:hover {
  background: #000 !important;
  color: #fff !important;
}

.unique-header.navbar-light .user-initial-circle {
  background: #000 !important;
  color: #fff !important;
}

/* MOBILE DRAWER LIGHT MODE */
.nav-drawer.light {
  background: white !important;
  color: #000 !important;
}

.nav-drawer.light .drawer-title,
.nav-drawer.light a,
.nav-drawer.light i {
  color: #000 !important;
}

.nav-drawer.light .drawer-links a:hover {
  background: rgba(0,0,0,0.05) !important;
}

.nav-drawer.light .drawer-divider {
  background: rgba(0,0,0,0.12) !important;
}

/* Light mode profile circle inside drawer */
.nav-drawer.light .user-initial-circle {
  background: #000 !important;
  color: #fff !important;
}

  </style>
</head>
<body>

<header class="unique-header {{ $navbarStyle ?? '' }}" id="navbar">

  <nav class="unique-nav" role="navigation" aria-label="Primary">
    <div class="unique-nav-left">
      <!-- Logo (unchanged) -->
      <a href="#" class="unique-nav-logo">Dream Haven</a>
    </div>

    <!-- Desktop links (kept intact for desktop) -->
    <div class="unique-nav-items" id="desktop-links">
      <div class="unique-nav-item">
        <a class="unique-nav-link {{ request()->routeIs('newindex') ? ' active' : '' }}" href="{{ route('newindex') }}">Home</a>
        <a class="unique-nav-link" href="{{ route('property.list') }}">Properties</a>
        <a class="unique-nav-link" href="{{ route('about-us') }}">About Us</a>
        <a class="unique-nav-link" href="{{ route('contact-us') }}">Contact</a>
      </div>
    </div>

    <!-- Right side: profile/login (single instance, same logic as before) -->
    @php
      $user = Auth::user();
      $agent = Auth::guard('agent')->user();
    @endphp

    @if($user || $agent)
      @php
        $displayName = $user ? $user->name : $agent->agent_name;
        $redirectRoute = $user ? route('admin.dashboard') : route('agent.admin-dashboard');
      @endphp

      <!-- profile circle (visible on desktop & mobile) -->
      <a href="{{ $redirectRoute }}" aria-label="Go to dashboard">
        <div class="user-initial-circle" title="Profile">
          {{ strtoupper(substr($displayName, 0, 1)) }}
        </div>
      </a>
    @else
      <!-- login button (single instance) -->
      <a href="{{ route('login-page') }}" aria-label="Login">
        <button class="unique-button">Login</button>
      </a>
    @endif

    <!-- Mobile hamburger (only shows on small screens) -->
    <button id="hamburger" class="menu-toggle" aria-controls="mobile-drawer" aria-expanded="false" aria-label="Open menu">
      <i class="uil uil-bars" id="hamburger-icon"></i>
    </button>

  </nav>
</header>

<!-- BACKDROP for drawer -->
<div id="nav-backdrop" class="nav-backdrop" tabindex="-1" aria-hidden="true"></div>

<!-- MOBILE DRAWER (keeps same route links but not the login/profile - single source kept in header) -->
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
  </nav>

  <div class="drawer-divider" aria-hidden="true"></div>

  <!-- Optional CTA area — not a duplicate of login/profile (we intentionally keep login/profile in header only) -->
  <div class="drawer-cta" aria-hidden="false">
    @if($user || $agent)
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

  <div style="margin-top:auto; font-size:12px; color:rgba(255,255,255,0.45);">
    Dream Haven &nbsp; • &nbsp; Real Estate
  </div>
</aside>

<!-- Your original scroll-hide script (kept intact) -->
<script>
  let lastScrollTop = 0;
  const navbar = document.getElementById('navbar');

  window.addEventListener('scroll', function() {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    if (currentScroll > lastScrollTop) {
      navbar.style.transform = 'translateY(-100%)';
    } else {
      navbar.style.transform = 'translateY(0)';
    }
    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
  }, false);
</script>

<!-- Mobile drawer control script -->
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
      // trap focus inside drawer
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

    // close when clicking any link that has data-close
    drawer.querySelectorAll('[data-close]').forEach(el => {
      el.addEventListener('click', function(){ closeDrawer(); });
    });

    // close on escape key
    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape' && drawer.classList.contains('open')) {
        closeDrawer();
      }
    });

    // keep drawer hidden on window resize to desktop (in case user resizes)
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
