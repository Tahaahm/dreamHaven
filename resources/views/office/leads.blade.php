<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Management - Dream Mulk</title>
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
        .search-bar i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
        .top-actions { display: flex; align-items: center; gap: 14px; }
        .theme-toggle { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .theme-toggle:hover { background: #eff3ff; color: #6366f1; }
        .add-btn { background: #6366f1; color: white; padding: 11px 22px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 9px; font-size: 14px; transition: all 0.2s; }
        .add-btn:hover { background: #5558e3; }

        .content-area { flex: 1; overflow-y: auto; padding: 32px; background: var(--bg-main); }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
        .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); }

        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 28px; }
        .stat-box { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; }
        .stat-value { font-size: 28px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; }
        .stat-label { color: var(--text-muted); font-size: 13px; }

        .filters { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
        .filter-btn { padding: 10px 18px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-secondary); font-size: 14px; cursor: pointer; transition: all 0.2s; }
        .filter-btn:hover, .filter-btn.active { background: #6366f1; color: white; border-color: #6366f1; }

        .section { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 28px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 14px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-color); }
        .data-table td { padding: 18px 14px; border-bottom: 1px solid var(--border-color); font-size: 14px; color: var(--text-secondary); }
        .data-table tr:hover { background: var(--bg-hover); }

        .lead-name { font-weight: 600; color: var(--text-primary); margin-bottom: 4px; }
        .lead-email { font-size: 12px; color: var(--text-muted); }
        .status-badge { display: inline-block; padding: 5px 13px; border-radius: 13px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-badge.new { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .status-badge.contacted { background: rgba(249,115,22,0.12); color: #f97316; }
        .status-badge.qualified { background: rgba(168,85,247,0.12); color: #a855f7; }
        .status-badge.converted { background: rgba(34,197,94,0.12); color: #22c55e; }
        .status-badge.lost { background: rgba(239,68,68,0.12); color: #ef4444; }

        .priority-badge { display: inline-block; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .priority-badge.high { background: rgba(239,68,68,0.12); color: #ef4444; }
        .priority-badge.medium { background: rgba(249,115,22,0.12); color: #f97316; }
        .priority-badge.low { background: rgba(34,197,94,0.12); color: #22c55e; }

        .action-btns { display: flex; gap: 8px; }
        .action-btn { width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-secondary); cursor: pointer; transition: all 0.2s; }
        .action-btn:hover { border-color: #6366f1; color: #6366f1; background: rgba(99,102,241,0.08); }

        .empty { text-align: center; padding: 70px 20px; color: var(--text-muted); }
        .empty i { font-size: 52px; margin-bottom: 18px; opacity: 0.4; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fas fa-layer-group"></i> Dream Mulk</div>
        <div class="nav-menu">
            <a href="{{ route('office.dashboard') }}" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="{{ route('office.properties') }}" class="nav-item"><i class="fas fa-building"></i> Properties</a>
            <a href="{{ route('office.projects') }}" class="nav-item"><i class="fas fa-folder"></i> Projects</a>
            <a href="{{ route('office.leads') }}" class="nav-item active"><i class="fas fa-user-friends"></i> Leads</a>
            <a href="{{ route('office.offers') }}" class="nav-item"><i class="fas fa-tag"></i> Offers</a>
            <a href="{{ route('office.agreements') }}" class="nav-item"><i class="fas fa-file-contract"></i> Agreements</a>
            <a href="{{ route('office.appointments') }}" class="nav-item"><i class="fas fa-calendar-alt"></i> Calendar</a>
            <a href="{{ route('office.activities') }}" class="nav-item"><i class="fas fa-chart-bar"></i> Activities</a>
            <a href="{{ route('office.contacts') }}" class="nav-item"><i class="fas fa-address-book"></i> Contacts</a>
            <a href="{{ route('office.agents') }}" class="nav-item"><i class="fas fa-user-tie"></i> Agents</a>
            <a href="{{ route('office.campaigns') }}" class="nav-item"><i class="fas fa-bullhorn"></i> Campaigns</a>
            <a href="{{ route('office.documents') }}" class="nav-item"><i class="fas fa-file-alt"></i> Documents</a>
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
                <input type="text" placeholder="Search leads by name, email, phone...">
            </div>
            <div class="top-actions">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                <button class="add-btn"><i class="fas fa-plus"></i> Add Lead</button>
            </div>
        </div>

        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title">Leads Management</h1>
            </div>

            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                    <div class="stat-label">Total Leads</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $stats['new'] ?? 0 }}</div>
                    <div class="stat-label">New Leads</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $stats['qualified'] ?? 0 }}</div>
                    <div class="stat-label">Qualified</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $stats['converted'] ?? 0 }}</div>
                    <div class="stat-label">Converted</div>
                </div>
            </div>

            <div class="filters">
                <button class="filter-btn active">All Leads</button>
                <button class="filter-btn">New</button>
                <button class="filter-btn">Contacted</button>
                <button class="filter-btn">Qualified</button>
                <button class="filter-btn">Converted</button>
                <button class="filter-btn">Lost</button>
            </div>

            <div class="section">
                @if(isset($leads) && $leads->count() > 0)
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Lead</th>
                                <th>Property Interest</th>
                                <th>Source</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leads as $lead)
                            <tr>
                                <td>
                                    <div class="lead-name">{{ $lead->name }}</div>
                                    <div class="lead-email">{{ $lead->email }}</div>
                                    <div class="lead-email">{{ $lead->phone }}</div>
                                </td>
                                <td>{{ $lead->property_name ?? 'General Inquiry' }}</td>
                                <td>{{ ucfirst($lead->source ?? 'Website') }}</td>
                                <td><span class="priority-badge {{ $lead->priority ?? 'medium' }}">{{ ucfirst($lead->priority ?? 'Medium') }}</span></td>
                                <td><span class="status-badge {{ $lead->status ?? 'new' }}">{{ ucfirst($lead->status ?? 'New') }}</span></td>
                                <td>{{ $lead->assigned_agent ?? 'Unassigned' }}</td>
                                <td>{{ \Carbon\Carbon::parse($lead->created_at)->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn" title="View"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn" title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn" title="Contact"><i class="fas fa-phone"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty">
                        <i class="fas fa-user-friends"></i>
                        <h3>No Leads Yet</h3>
                        <p>Your leads will appear here once you start receiving inquiries</p>
                    </div>
                @endif
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
            if (savedTheme === 'dark') {
                document.querySelector('.main-content').setAttribute('data-theme', 'dark');
                document.getElementById('theme-icon').className = 'fas fa-sun';
            }
        });
    </script>
</body>
</html>
