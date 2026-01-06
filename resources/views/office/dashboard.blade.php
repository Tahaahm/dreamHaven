<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dream Mulk</title>
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
            /* Dark Mode */
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

        /* Sidebar - ALWAYS DARK */
        .sidebar { width: 240px; background: #16171d; display: flex; flex-direction: column; border-right: 1px solid rgba(255,255,255,0.06); }
        .logo { padding: 20px 24px; font-size: 20px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .logo i { font-size: 22px; color: #6366f1; }
        .nav-menu { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-item { padding: 11px 16px; color: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 14px; font-size: 14px; text-decoration: none; margin-bottom: 4px; border-radius: 8px; font-weight: 500; }
        .nav-item:hover { background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.9); }
        .nav-item.active { background: #6366f1; color: #fff; }
        .nav-item i { width: 20px; text-align: center; font-size: 16px; }
        .nav-bottom { border-top: 1px solid rgba(255,255,255,0.06); padding: 16px 12px; }

        /* Main Content - Theme Adaptive */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg-main); transition: background 0.3s; }

        /* Top Bar - Light */
        .top-bar { background: #ffffff; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e8eaed; }
        .search-bar { flex: 1; max-width: 420px; position: relative; }
        .search-bar input { width: 100%; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; padding: 11px 44px; color: #1a1a1a; font-size: 14px; font-weight: 400; }
        .search-bar input::placeholder { color: #9ca3af; }
        .search-bar i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 15px; }
        .top-actions { display: flex; align-items: center; gap: 14px; }
        .icon-btn { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; position: relative; }
        .icon-btn:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .icon-btn .badge { position: absolute; top: -5px; right: -5px; background: #6366f1; color: white; font-size: 10px; padding: 3px 6px; border-radius: 10px; font-weight: 700; }
        .add-btn { background: #6366f1; color: white; padding: 11px 22px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 9px; font-size: 14px; text-decoration: none; transition: all 0.2s; }
        .add-btn:hover { background: #5558e3; transform: translateY(-1px); }
        .theme-toggle { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .theme-toggle:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .user-profile { display: flex; align-items: center; gap: 11px; cursor: pointer; padding: 7px 13px; border-radius: 8px; transition: all 0.2s; }
        .user-profile:hover { background: #f8f9fb; }
        .user-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 15px; }

        /* Content Area - Theme Adaptive */
        .content-area { flex: 1; overflow-y: auto; padding: 32px; background: var(--bg-main); transition: background 0.3s; }
        .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px; transition: color 0.3s; }
        .page-subtitle { color: var(--text-muted); font-size: 15px; margin-bottom: 32px; transition: color 0.3s; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 32px; }
        .stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 26px; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 40px var(--shadow); border-color: rgba(99,102,241,0.25); }
        .stat-header { display: flex; justify-content: space-between; align-items: flex-start; }
        .stat-icon { width: 54px; height: 54px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .stat-icon.blue { background: rgba(99,102,241,0.12); color: #6366f1; }
        .stat-icon.green { background: rgba(34,197,94,0.12); color: #22c55e; }
        .stat-icon.orange { background: rgba(249,115,22,0.12); color: #f97316; }
        .stat-icon.purple { background: rgba(168,85,247,0.12); color: #a855f7; }
        .stat-value { font-size: 36px; font-weight: 700; color: var(--text-primary); margin-top: 18px; margin-bottom: 6px; transition: color 0.3s; }
        .stat-label { color: var(--text-muted); font-size: 15px; font-weight: 500; transition: color 0.3s; }

        /* Section */
        .section { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 28px; margin-bottom: 28px; transition: all 0.3s; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .section-title { font-size: 20px; font-weight: 700; color: var(--text-primary); transition: color 0.3s; }
        .view-all { color: #6366f1; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.2s; display: flex; align-items: center; gap: 7px; }
        .view-all:hover { gap: 11px; }

        /* Properties Grid */
        .properties-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 22px; }
        .property-mini { background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; transition: all 0.3s; cursor: pointer; }
        .property-mini:hover { transform: translateY(-5px); box-shadow: 0 12px 40px var(--shadow); border-color: rgba(99,102,241,0.4); }
        .property-img { position: relative; width: 100%; height: 160px; overflow: hidden; }
        .property-img img { width: 100%; height: 100%; object-fit: cover; }
        .property-badge { position: absolute; top: 10px; left: 10px; background: #6366f1; color: white; padding: 5px 12px; border-radius: 7px; font-size: 11px; font-weight: 700; }
        .img-dots { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); display: flex; gap: 5px; }
        .dot { width: 7px; height: 7px; background: rgba(255,255,255,0.35); border-radius: 50%; }
        .dot.active { width: 22px; border-radius: 4px; background: #fff; }
        .property-info { padding: 18px; }
        .property-price { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 7px; transition: color 0.3s; }
        .property-type { font-size: 13px; color: var(--text-secondary); margin-bottom: 5px; transition: color 0.3s; }
        .property-loc { font-size: 13px; color: var(--text-muted); margin-bottom: 14px; transition: color 0.3s; }
        .property-specs { display: flex; gap: 14px; font-size: 13px; color: var(--text-muted); padding-top: 14px; border-top: 1px solid var(--border-color); transition: all 0.3s; }

        /* Table */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 14px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border-color); transition: all 0.3s; }
        .data-table td { padding: 18px 14px; border-bottom: 1px solid var(--border-color); font-size: 14px; color: var(--text-secondary); transition: all 0.3s; }
        .data-table tr:hover { background: var(--bg-hover); }
        .status-badge { display: inline-block; padding: 5px 13px; border-radius: 13px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-badge.pending { background: rgba(249,115,22,0.12); color: #f97316; }
        .status-badge.confirmed { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .status-badge.completed { background: rgba(34,197,94,0.12); color: #22c55e; }
        .status-badge.cancelled { background: rgba(239,68,68,0.12); color: #ef4444; }

        /* Agents Grid */
        .agents-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 18px; }
        .agent-mini { background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 11px; padding: 22px; text-align: center; transition: all 0.3s; cursor: pointer; }
        .agent-mini:hover { transform: translateY(-3px); box-shadow: 0 12px 40px var(--shadow); border-color: rgba(99,102,241,0.25); }
        .agent-avatar { width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 26px; margin: 0 auto 14px; }
        .agent-name { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 5px; transition: color 0.3s; }
        .agent-count { font-size: 14px; color: var(--text-muted); transition: color 0.3s; }

        /* Empty */
        .empty { text-align: center; padding: 70px 20px; color: var(--text-muted); transition: color 0.3s; }
        .empty i { font-size: 52px; margin-bottom: 18px; opacity: 0.4; }
        .empty h3 { font-size: 19px; margin-bottom: 9px; }

        /* Scrollbar */
        .content-area::-webkit-scrollbar { width: 9px; }
        .content-area::-webkit-scrollbar-track { background: var(--bg-main); }
        .content-area::-webkit-scrollbar-thumb { background: var(--bg-card); border-radius: 5px; }
        .content-area::-webkit-scrollbar-thumb:hover { background: var(--bg-hover); }
    </style>
</head>
<body>
    <div class="sidebar">
    <div class="logo"><i class="fas fa-layer-group"></i> Dream Mulk</div>
    <div class="nav-menu">
    <a href="{{ route('office.dashboard') }}" class="nav-item {{ request()->routeIs('office.dashboard') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="{{ route('office.properties') }}" class="nav-item {{ request()->routeIs('office.properties*') ? 'active' : '' }}">
        <i class="fas fa-building"></i> Properties
    </a>
    <a href="{{ route('office.projects') }}" class="nav-item {{ request()->routeIs('office.projects*') ? 'active' : '' }}">
        <i class="fas fa-folder"></i> Projects
    </a>
    <a href="{{ route('office.leads') }}" class="nav-item {{ request()->routeIs('office.leads*') ? 'active' : '' }}">
        <i class="fas fa-user-friends"></i> Leads
    </a>
    <a href="{{ route('office.subscriptions') }}" class="nav-item {{ request()->routeIs('office.subscriptions*') ? 'active' : '' }}">
        <i class="fas fa-tag"></i> Offers
    </a>
    <a href="{{ route('office.agreements') }}" class="nav-item {{ request()->routeIs('office.agreements*') ? 'active' : '' }}">
        <i class="fas fa-file-contract"></i> Agreements
    </a>
    <a href="{{ route('office.appointments') }}" class="nav-item {{ request()->routeIs('office.appointments*') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt"></i> Calendar
    </a>
    <a href="{{ route('office.activities') }}" class="nav-item {{ request()->routeIs('office.activities*') ? 'active' : '' }}">
        <i class="fas fa-chart-bar"></i> Activities
    </a>
    <a href="{{ route('office.contacts') }}" class="nav-item {{ request()->routeIs('office.contacts*') ? 'active' : '' }}">
        <i class="fas fa-address-book"></i> Contacts
    </a>
    <a href="{{ route('office.agents') }}" class="nav-item {{ request()->routeIs('office.agents*') ? 'active' : '' }}">
        <i class="fas fa-user-tie"></i> Agents
    </a>
    <a href="{{ route('office.campaigns') }}" class="nav-item {{ request()->routeIs('office.campaigns*') ? 'active' : '' }}">
        <i class="fas fa-bullhorn"></i> Campaigns
    </a>
    <a href="{{ route('office.documents') }}" class="nav-item {{ request()->routeIs('office.documents*') ? 'active' : '' }}">
        <i class="fas fa-file-alt"></i> Documents
    </a>
</div>
    <div class="nav-bottom">
        <a href="{{ route('office.profile') }}" class="nav-item {{ request()->routeIs('office.profile') ? 'active' : '' }}">
            <i class="fas fa-cog"></i> Settings
        </a>
        <form action="{{ route('office.logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="nav-item" style="width: 100%; background: none; border: none; text-align: left; cursor: pointer; color: rgba(255,255,255,0.5); font-family: inherit; font-size: 14px; font-weight: 500;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</div>

    <div class="main-content">
        <div class="top-bar">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search properties, leads, contacts and more">
            </div>
            <div class="top-actions">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                <a href="{{ route('office.property.upload') }}" class="add-btn"><i class="fas fa-plus"></i> Add Property</a>
                <button class="icon-btn"><i class="fas fa-bell"></i><span class="badge">3</span></button>
                <button class="icon-btn"><i class="fas fa-envelope"></i><span class="badge">5</span></button>
                <div class="user-profile">
                    <div class="user-avatar">{{ strtoupper(substr($office->company_name, 0, 2)) }}</div>
                    <span style="font-size: 14px; color: #1a1a1a; font-weight: 600;">{{ $office->company_name }}</span>
                    <i class="fas fa-chevron-down" style="font-size: 12px; color: #9ca3af;"></i>
                </div>
            </div>
        </div>

        <div class="content-area">
            <h1 class="page-title">Welcome back, {{ $office->company_name }}!</h1>
            <p class="page-subtitle">Here's what's happening with your real estate business today.</p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">{{ $stats['total_agents'] }}</div>
                            <div class="stat-label">Total Agents</div>
                        </div>
                        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">{{ $stats['total_properties'] }}</div>
                            <div class="stat-label">Total Properties</div>
                        </div>
                        <div class="stat-icon green"><i class="fas fa-building"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">{{ $stats['active_listings'] }}</div>
                            <div class="stat-label">Active Listings</div>
                        </div>
                        <div class="stat-icon orange"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">{{ $stats['pending_appointments'] }}</div>
                            <div class="stat-label">Pending Appointments</div>
                        </div>
                        <div class="stat-icon purple"><i class="fas fa-calendar-alt"></i></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">Recent Properties</h2>
                    <a href="{{ route('office.properties') }}" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                @if($recentProperties->count() > 0)
                    <div class="properties-grid">
                        @foreach($recentProperties as $property)
                        <div class="property-mini">
                            <div class="property-img">
                                @php
                                    $images = json_decode($property->images, true);
                                    $firstImage = is_array($images) && count($images) > 0 ? $images[0] : 'https://via.placeholder.com/300x200/f8f9fb/6366f1?text=No+Image';
                                @endphp
                                <img src="{{ $firstImage }}" alt="Property">
                                <div class="property-badge">#{{ $property->id }}</div>
                                <div class="img-dots">
                                    @if(is_array($images))
                                        @foreach(array_slice($images, 0, 5) as $index => $image)
                                            <div class="dot {{ $index === 0 ? 'active' : '' }}"></div>
                                        @endforeach
                                    @else
                                        <div class="dot active"></div>
                                    @endif
                                </div>
                            </div>
                            <div class="property-info">
                                @php
                                    $price = json_decode($property->price, true);
                                    $rooms = json_decode($property->rooms, true);
                                @endphp
                                <div class="property-price">${{ number_format($price['usd'] ?? 0) }}</div>
                                <div class="property-type">{{ ucfirst($property->listing_type) }}</div>
                                <div class="property-loc">
                                    @php $address = json_decode($property->address_details, true); @endphp
                                    {{ $address['city']['en'] ?? '' }}, {{ $address['district']['en'] ?? '' }}
                                </div>
                                <div class="property-specs">
                                    <span><i class="fas fa-bed"></i> {{ $rooms['bedroom']['count'] ?? 0 }}</span>
                                    <span><i class="fas fa-bath"></i> {{ $rooms['bathroom']['count'] ?? 0 }}</span>
                                    <span><i class="fas fa-ruler-combined"></i> {{ $property->area ?? 0 }} m²</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty"><i class="fas fa-building"></i><h3>No Properties Yet</h3></div>
                @endif
            </div>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">Recent Appointments</h2>
                    <a href="{{ route('office.appointments') }}" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                @if($recentAppointments->count() > 0)
                    <table class="data-table">
                        <thead>
                            <tr><th>Client</th><th>Agent</th><th>Property</th><th>Date & Time</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentAppointments as $appointment)
                            <tr>
                                <td><strong>{{ $appointment->user->name ?? 'N/A' }}</strong><br><span style="font-size: 12px; opacity: 0.6;">{{ $appointment->user->phone_number ?? '' }}</span></td>
                                <td>{{ $appointment->agent->first_name ?? 'N/A' }} {{ $appointment->agent->last_name ?? '' }}</td>
                                <td>{{ json_decode($appointment->property->name ?? '{}')->en ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}<br><small style="opacity: 0.6;">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</small></td>
                                <td><span class="status-badge {{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty"><i class="fas fa-calendar-alt"></i><h3>No Appointments Yet</h3></div>
                @endif
            </div>

            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">Top Performing Agents</h2>
                    <a href="{{ route('office.agents') }}" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                @if($topAgents->count() > 0)
                    <div class="agents-grid">
                        @foreach($topAgents as $agent)
                        <div class="agent-mini">
                            <div class="agent-avatar">{{ strtoupper(substr($agent->first_name, 0, 1)) }}{{ strtoupper(substr($agent->last_name, 0, 1)) }}</div>
                            <div class="agent-name">{{ $agent->first_name }} {{ $agent->last_name }}</div>
                            <div class="agent-count">{{ $agent->owned_properties_count }} Properties</div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty"><i class="fas fa-users"></i><h3>No Agents Yet</h3></div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle Function
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

        // Load saved theme on page load
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
