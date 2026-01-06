<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Dream Mulk</title>
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
        .theme-toggle { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .theme-toggle:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .user-profile { display: flex; align-items: center; gap: 11px; cursor: pointer; padding: 7px 13px; border-radius: 8px; transition: all 0.2s; }
        .user-profile:hover { background: #f8f9fb; }
        .user-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 15px; }
        .content-area { flex: 1; overflow-y: auto; padding: 32px; background: var(--bg-main); transition: background 0.3s; }
        .page-header { margin-bottom: 32px; }
        .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); transition: color 0.3s; }
        .table-container { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; transition: all 0.3s; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 18px 20px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; background: var(--bg-card); border-bottom: 1px solid var(--border-color); transition: all 0.3s; }
        .data-table td { padding: 20px; border-bottom: 1px solid var(--border-color); font-size: 15px; color: var(--text-secondary); transition: all 0.3s; }
        .data-table tr:hover { background: var(--bg-hover); }
        .data-table tr:last-child td { border-bottom: none; }
        .client-info { display: flex; flex-direction: column; }
        .client-name { font-weight: 600; color: var(--text-primary); margin-bottom: 4px; transition: color 0.3s; }
        .client-phone { font-size: 13px; color: var(--text-muted); transition: color 0.3s; }
        .status-badge { display: inline-block; padding: 6px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .status-badge.pending { background: rgba(249,115,22,0.15); color: #f97316; }
        .status-badge.confirmed { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .status-badge.completed { background: rgba(34,197,94,0.15); color: #22c55e; }
        .status-badge.cancelled { background: rgba(239,68,68,0.15); color: #ef4444; }
        .action-select { background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); padding: 8px 12px; border-radius: 8px; font-size: 14px; cursor: pointer; transition: all 0.3s; }
        .action-select:hover { border-color: #6366f1; }
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
            <a href="{{ route('office.appointments') }}" class="nav-item active"><i class="fas fa-calendar-alt"></i> Calendar</a>
            <a href="#" class="nav-item"><i class="fas fa-chart-bar"></i> Activities</a>
            <a href="#" class="nav-item"><i class="fas fa-address-book"></i> Contacts</a>
            <a href="{{ route('office.agents') }}" class="nav-item"><i class="fas fa-user-tie"></i> Agents</a>
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
                <input type="text" placeholder="Search appointments">
            </div>
            <div class="top-actions">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
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
                <h1 class="page-title">Appointments</h1>
            </div>

            @if($appointments->count() > 0)
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Agent</th>
                                <th>Property</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointments as $appointment)
                            <tr>
                                <td>
                                    <div class="client-info">
                                        <span class="client-name">{{ $appointment->user->name ?? 'N/A' }}</span>
                                        <span class="client-phone">{{ $appointment->user->phone_number ?? '' }}</span>
                                    </div>
                                </td>
                                <td>{{ $appointment->agent->first_name ?? 'N/A' }} {{ $appointment->agent->last_name ?? '' }}</td>
                                <td>{{ json_decode($appointment->property->name ?? '{}')->en ?? 'N/A' }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}<br>
                                    <small style="opacity: 0.7;">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</small>
                                </td>
                                <td><span class="status-badge {{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></td>
                                <td>
                                    <form action="{{ route('office.appointment.status', $appointment->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="action-select" onchange="this.form.submit()">
                                            <option value="">Change Status</option>
                                            <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed" {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>No Appointments Yet</h3>
                    <p>Appointments will appear here once scheduled</p>
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
