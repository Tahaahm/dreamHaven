<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dream Mulk Office')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-main: #ffffff;
            --bg-card: #f8f9fb;
            --bg-hover: #f1f3f5;
            --text-primary: #1a1a1a;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e8eaed;
            --shadow: rgba(0,0,0,0.08);
        }
        [data-theme="dark"] {
            --bg-main: #0a0b0f;
            --bg-card: #16171d;
            --bg-hover: #1f2028;
            --text-primary: #ffffff;
            --text-secondary: rgba(255,255,255,0.8);
            --text-muted: rgba(255,255,255,0.5);
            --border-color: rgba(255,255,255,0.08);
            --shadow: rgba(0,0,0,0.4);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg-main); transition: background 0.3s; }
        .top-bar { background: #ffffff; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e8eaed; }
        .search-bar { flex: 1; max-width: 420px; position: relative; }
        .search-bar input { width: 100%; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; padding: 11px 44px; color: #1a1a1a; font-size: 14px; }
        .search-bar input::placeholder { color: #9ca3af; }
        .search-bar i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 15px; }
        .top-actions { display: flex; align-items: center; gap: 14px; }
        .icon-btn { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .icon-btn:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .theme-toggle { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .theme-toggle:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .add-btn { background: #6366f1; color: white; padding: 11px 22px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 9px; font-size: 14px; text-decoration: none; transition: all 0.2s; }
        .add-btn:hover { background: #5558e3; transform: translateY(-1px); }
        .user-profile { display: flex; align-items: center; gap: 11px; cursor: pointer; padding: 7px 13px; border-radius: 8px; transition: all 0.2s; }
        .user-profile:hover { background: #f8f9fb; }
        .user-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 15px; }
        .content-area { flex: 1; overflow-y: auto; padding: 32px; background: var(--bg-main); transition: background 0.3s; }
        .content-area::-webkit-scrollbar { width: 9px; }
        .content-area::-webkit-scrollbar-track { background: var(--bg-main); }
        .content-area::-webkit-scrollbar-thumb { background: var(--bg-card); border-radius: 5px; }
        @yield('styles')
    </style>
</head>
<body>
    @include('components.sidebar')

    <div class="main-content">
        <div class="top-bar">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="@yield('search-placeholder', 'Search...')">
            </div>
            <div class="top-actions">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                @yield('top-actions')
                <button class="icon-btn"><i class="fas fa-bell"></i></button>
                <button class="icon-btn"><i class="fas fa-envelope"></i></button>
                <div class="user-profile">
                    <div class="user-avatar">{{ strtoupper(substr(auth('office')->user()->company_name, 0, 2)) }}</div>
                    <span style="font-size: 14px; color: #1a1a1a; font-weight: 600;">{{ auth('office')->user()->company_name }}</span>
                    <i class="fas fa-chevron-down" style="font-size: 12px; color: #9ca3af;"></i>
                </div>
            </div>
        </div>

        <div class="content-area">
            @yield('content')
        </div>
    </div>

    <script>
        function toggleTheme() {
            const mainContent = document.querySelector('.main-content');
            const icon = document.getElementById('theme-icon');
            const currentTheme = mainContent.getAttribute('data-theme');
            if (currentTheme === 'dark') {
                mainContent.removeAttribute('data-theme');
                icon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                mainContent.setAttribute('data-theme', 'dark');
                icon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const mainContent = document.querySelector('.main-content');
            const icon = document.getElementById('theme-icon');
            if (savedTheme === 'dark') {
                mainContent.setAttribute('data-theme', 'dark');
                icon.className = 'fas fa-sun';
            }
        });
        @yield('scripts')
    </script>
</body>
</html>
