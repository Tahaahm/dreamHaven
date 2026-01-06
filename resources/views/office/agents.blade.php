<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agents - Dream Mulk</title>
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
        .sidebar { width: 240px; background: #16171d; display: flex; flex-direction: column; border-right: 1px solid rgba(255,255,255,0.06); }
        .logo { padding: 20px 24px; font-size: 20px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .logo i { font-size: 22px; color: #6366f1; }
        .nav-menu { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-item { padding: 11px 16px; color: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 14px; font-size: 14px; text-decoration: none; margin-bottom: 4px; border-radius: 8px; font-weight: 500; }
        .nav-item:hover { background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.9); }
        .nav-item.active { background: #6366f1; color: #fff; }
        .nav-item i { width: 20px; text-align: center; font-size: 16px; }
        .nav-bottom { border-top: 1px solid rgba(255,255,255,0.06); padding: 16px 12px; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg-main); transition: background 0.3s; }
        .top-bar { background: #ffffff; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e8eaed; }
        .search-bar { flex: 1; max-width: 420px; position: relative; }
        .search-bar input { width: 100%; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; padding: 11px 44px; color: #1a1a1a; font-size: 14px; }
        .search-bar input::placeholder { color: #9ca3af; }
        .search-bar i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 15px; }
        .top-actions { display: flex; align-items: center; gap: 14px; }
        .icon-btn { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .icon-btn:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .add-btn { background: #6366f1; color: white; padding: 11px 22px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 9px; font-size: 14px; text-decoration: none; transition: all 0.2s; }
        .add-btn:hover { background: #5558e3; transform: translateY(-1px); }
        .theme-toggle { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .theme-toggle:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .user-profile { display: flex; align-items: center; gap: 11px; cursor: pointer; padding: 7px 13px; border-radius: 8px; transition: all 0.2s; }
        .user-profile:hover { background: #f8f9fb; }
        .user-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 15px; }
        .content-area { flex: 1; overflow-y: auto; padding: 32px; background: var(--bg-main); transition: background 0.3s; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); transition: color 0.3s; }
        .agents-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
        .agent-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 28px; text-align: center; transition: all 0.3s; position: relative; }
        .agent-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px var(--shadow); border-color: rgba(99,102,241,0.4); }
        .agent-avatar { width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 32px; margin: 0 auto 18px; }
        .agent-name { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; transition: color 0.3s; }
        .agent-email { font-size: 14px; color: var(--text-secondary); margin-bottom: 4px; transition: color 0.3s; }
        .agent-phone { font-size: 14px; color: var(--text-muted); margin-bottom: 18px; transition: color 0.3s; }
        .agent-stats { display: flex; justify-content: center; gap: 24px; padding-top: 18px; border-top: 1px solid var(--border-color); margin-bottom: 18px; transition: border-color 0.3s; }
        .stat { text-align: center; }
        .stat-value { font-size: 24px; font-weight: 700; color: var(--text-primary); transition: color 0.3s; }
        .stat-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; transition: color 0.3s; }
        .remove-btn { width: 100%; background: transparent; border: 1px solid #ef4444; color: #ef4444; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .remove-btn:hover { background: #ef4444; color: white; }
        .empty { text-align: center; padding: 80px 20px; color: var(--text-muted); }
        .empty i { font-size: 64px; margin-bottom: 20px; opacity: 0.4; }
        .content-area::-webkit-scrollbar { width: 9px; }
        .content-area::-webkit-scrollbar-track { background: var(--bg-main); }
        .content-area::-webkit-scrollbar-thumb { background: var(--bg-card); border-radius: 5px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fas fa-layer-group"></i> Dream Mulk</div>
        <div class="nav-menu">
            <a href="{{ route('office.dashboard') }}" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="{{ route('office.properties') }}" class="nav-item"><i class="fas fa-building"></i> Properties</a>
            <a href="#" class="nav-item"><i class="fas fa-folder"></i> Projects</a>
            <a href="#" class="nav-item"><i class="fas fa-user-friends"></i> Leads</a>
            <a href="#" class="nav-item"><i class="fas fa-tag"></i> Offers</a>
            <a href="#" class="nav-item"><i class="fas fa-file-contract"></i> Agreements</a>
            <a href="{{ route('office.appointments') }}" class="nav-item"><i class="fas fa-calendar-alt"></i> Calendar</a>
            <a href="#" class="nav-item"><i class="fas fa-chart-bar"></i> Activities</a>
            <a href="#" class="nav-item"><i class="fas fa-address-book"></i> Contacts</a>
            <a href="{{ route('office.agents') }}" class="nav-item active"><i class="fas fa-user-tie"></i> Agents</a>
            <a href="#" class="nav-item"><i class="fas fa-bullhorn"></i> Campaigns</a>
            <a href="#" class="nav-item"><i class="fas fa-file-alt"></i> Documents</a>
        </div>
        <div class="nav-bottom">
            <a href="{{ route('office.profile') }}" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
            <form action="{{ route('office.logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="nav-item" style="width: 100%; background: none; border: none; text-align: left; cursor: pointer; color: rgba(255,255,255,0.5); font-family: inherit; font-size: 14px; font-weight: 500;"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search agents">
            </div>
            <div class="top-actions">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                <a href="{{ route('office.agent.add') }}" class="add-btn"><i class="fas fa-plus"></i> Add Agent</a>
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
            <div class="page-header">
                <h1 class="page-title">Agents</h1>
            </div>

            @if($agents->count() > 0)
                <div class="agents-grid">
                    @foreach($agents as $agent)
                    <div class="agent-card">
                        <div class="agent-avatar">{{ strtoupper(substr($agent->first_name, 0, 1)) }}{{ strtoupper(substr($agent->last_name, 0, 1)) }}</div>
                        <div class="agent-name">{{ $agent->first_name }} {{ $agent->last_name }}</div>
                        <div class="agent-email">{{ $agent->email }}</div>
                        <div class="agent-phone">{{ $agent->phone_number }}</div>
                        <div class="agent-stats">
                            <div class="stat">
                                <div class="stat-value">{{ $agent->owned_properties_count }}</div>
                                <div class="stat-label">Properties</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">{{ $agent->appointments_count ?? 0 }}</div>
                                <div class="stat-label">Appointments</div>
                            </div>
                        </div>
                        <form action="{{ route('office.agent.remove', $agent->id) }}" method="POST" onsubmit="return confirm('Remove this agent?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="remove-btn"><i class="fas fa-trash"></i> Remove Agent</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty">
                    <i class="fas fa-users"></i>
                    <h3>No Agents Yet</h3>
                    <p>Start by adding your first agent</p>
                </div>
            @endif
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
    </script>
</body>
</html>
