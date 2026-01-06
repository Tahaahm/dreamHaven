<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Agent - Dream Mulk</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .back-btn { background: #f8f9fb; color: #6b7280; padding: 11px 22px; border-radius: 8px; border: 1px solid #e8eaed; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 9px; font-size: 14px; text-decoration: none; transition: all 0.2s; }
        .back-btn:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .theme-toggle { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .theme-toggle:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .user-profile { display: flex; align-items: center; gap: 11px; cursor: pointer; padding: 7px 13px; border-radius: 8px; transition: all 0.2s; }
        .user-profile:hover { background: #f8f9fb; }
        .user-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 15px; }
        .content-area { flex: 1; overflow-y: auto; padding: 32px; background: var(--bg-main); transition: background 0.3s; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); transition: color 0.3s; }
        .form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; transition: all 0.3s; margin-bottom: 24px; }
        .form-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 24px; transition: color 0.3s; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; font-size: 14px; transition: color 0.3s; }
        .form-input { width: 100%; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 8px; padding: 12px 16px; font-size: 15px; transition: all 0.3s; }
        .form-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 24px; }
        .alert-success { background: rgba(34,197,94,0.1); color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
        .alert-error { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }
        .content-area::-webkit-scrollbar { width: 9px; }
        .content-area::-webkit-scrollbar-track { background: var(--bg-main); }
        .content-area::-webkit-scrollbar-thumb { background: var(--bg-card); border-radius: 5px; }

        /* Agent Cards */
        .agents-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        .agent-card { background: var(--bg-main); border: 2px solid var(--border-color); border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; transition: all 0.3s; cursor: pointer; }
        .agent-card:hover { border-color: #6366f1; transform: translateY(-3px); box-shadow: 0 8px 24px var(--shadow); }
        .agent-card.selected { border-color: #22c55e; background: rgba(34,197,94,0.05); }
        .agent-avatar-large { width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 24px; flex-shrink: 0; }
        .agent-info { flex: 1; min-width: 0; }
        .agent-name { font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; transition: color 0.3s; }
        .agent-email { font-size: 14px; color: var(--text-secondary); margin-bottom: 3px; transition: color 0.3s; }
        .agent-phone { font-size: 13px; color: var(--text-muted); transition: color 0.3s; }
        .agent-stats { display: flex; gap: 16px; margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--border-color); }
        .stat-small { font-size: 12px; color: var(--text-muted); }
        .stat-small strong { color: var(--text-primary); font-weight: 600; }
        .btn-add { background: #6366f1; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; flex-shrink: 0; }
        .btn-add:hover { background: #5558e3; transform: scale(1.05); }
        .btn-added { background: #22c55e; color: white; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: default; flex-shrink: 0; }
        .empty-state { text-align: center; padding: 80px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 64px; margin-bottom: 20px; opacity: 0.4; }
        .empty-state h3 { font-size: 20px; margin-bottom: 10px; color: var(--text-secondary); }
        .search-box { background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px 20px; font-size: 15px; color: var(--text-primary); transition: all 0.3s; width: 100%; }
        .search-box:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        .helper-text { font-size: 13px; color: var(--text-muted); margin-top: 8px; }
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
                <input type="text" placeholder="Search" id="quick-search" onkeyup="quickSearch()">
            </div>
            <div class="top-actions">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                <a href="{{ route('office.agents') }}" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
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
                <h1 class="page-title">Add Agents to Your Office</h1>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        <div><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <!-- Search Section -->
            <div class="form-card">
                <h2 class="form-title"><i class="fas fa-search"></i> Search Available Agents</h2>
                <input type="text" class="search-box" id="agent-search" placeholder="Search by name, email, or phone..." onkeyup="searchAgents()">
                <div class="helper-text">
                    <i class="fas fa-info-circle"></i> Only agents without an office assignment will appear
                </div>
            </div>

            <!-- Available Agents -->
            <div class="form-card">
                <h2 class="form-title"><i class="fas fa-users"></i> Available Agents</h2>
                <div id="agents-list">
                    @if(isset($availableAgents) && $availableAgents->count() > 0)
                        <div class="agents-grid">
                            @foreach($availableAgents as $agent)
                            <div class="agent-card" data-agent-id="{{ $agent->id }}">
                                <div class="agent-avatar-large">
                                    {{ strtoupper(substr($agent->first_name ?? $agent->agent_name, 0, 1)) }}{{ strtoupper(substr($agent->last_name ?? '', 0, 1)) }}
                                </div>
                                <div class="agent-info">
                                    <div class="agent-name">{{ $agent->first_name ?? $agent->agent_name }} {{ $agent->last_name ?? '' }}</div>
                                    <div class="agent-email">{{ $agent->primary_email ?? $agent->email }}</div>
                                    <div class="agent-phone">{{ $agent->primary_phone ?? $agent->phone_number }}</div>
                                    <div class="agent-stats">
                                        <div class="stat-small"><strong>{{ $agent->properties_sold ?? 0 }}</strong> sold</div>
                                        <div class="stat-small"><strong>{{ number_format($agent->overall_rating ?? 0, 1) }}</strong> rating</div>
                                    </div>
                                </div>
                                <form action="{{ route('office.agent.add', $agent->id) }}" method="POST" onsubmit="return handleAddAgent(event, '{{ $agent->id }}')">
                                    @csrf
                                    <button type="submit" class="btn-add">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <h3>No Available Agents</h3>
                            <p>All agents are currently assigned to offices or there are no registered agents yet.</p>
                        </div>
                    @endif
                </div>
            </div>
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

        // Quick search in top bar
        function quickSearch() {
            const query = document.getElementById('quick-search').value.toLowerCase();
            const cards = document.querySelectorAll('.agent-card');

            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? '' : 'none';
            });
        }

        // Main agent search
        function searchAgents() {
            const query = document.getElementById('agent-search').value.toLowerCase();
            const cards = document.querySelectorAll('.agent-card');

            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? '' : 'none';
            });
        }

        // Handle add agent
        function handleAddAgent(event, agentId) {
            const confirmed = confirm('Add this agent to your office?');
            if (confirmed) {
                const card = document.querySelector(`[data-agent-id="${agentId}"]`);
                if (card) {
                    card.classList.add('selected');
                    const btn = card.querySelector('button');
                    if (btn) {
                        btn.className = 'btn-added';
                        btn.innerHTML = '<i class="fas fa-check"></i> Added';
                        btn.disabled = true;
                    }
                }
            }
            return confirmed;
        }
    </script>
</body>
</html>
