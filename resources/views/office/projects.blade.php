<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - Dream Mulk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #ffffff; --bg-card: #f8f9fb; --bg-hover: #f1f3f5; --text-primary: #1a1a1a; --text-secondary: #6b7280; --text-muted: #9ca3af; --border-color: #e8eaed; --shadow: rgba(0,0,0,0.08); }
        [data-theme="dark"] { --bg-main: #0a0b0f; --bg-card: #16171d; --bg-hover: #1f2028; --text-primary: #ffffff; --text-secondary: rgba(255,255,255,0.8); --text-muted: rgba(255,255,255,0.5); --border-color: rgba(255,255,255,0.08); --shadow: rgba(0,0,0,0.4); }
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
        .search-bar i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 15px; }
        .top-actions { display: flex; align-items: center; gap: 14px; }
        .add-btn { background: #6366f1; color: white; padding: 11px 22px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 9px; font-size: 14px; text-decoration: none; transition: all 0.2s; }
        .add-btn:hover { background: #5558e3; transform: translateY(-1px); }
        .theme-toggle { width: 42px; height: 42px; background: #f8f9fb; border: 1px solid #e8eaed; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all 0.2s; }
        .theme-toggle:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .user-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 15px; }
        .content-area { flex: 1; overflow-y: auto; padding: 32px; background: var(--bg-main); transition: background 0.3s; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); transition: color 0.3s; }
        .projects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px; }
        .project-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; transition: all 0.3s; cursor: pointer; }
        .project-card:hover { transform: translateY(-3px); box-shadow: 0 12px 40px var(--shadow); border-color: rgba(99,102,241,0.25); }
        .project-image { width: 100%; height: 220px; overflow: hidden; position: relative; }
        .project-image img { width: 100%; height: 100%; object-fit: cover; }
        .project-badge { position: absolute; top: 12px; right: 12px; background: #6366f1; color: white; padding: 6px 14px; border-radius: 7px; font-size: 12px; font-weight: 700; }
        .project-status { position: absolute; top: 12px; left: 12px; padding: 6px 14px; border-radius: 7px; font-size: 12px; font-weight: 700; }
        .status-planning { background: rgba(249,115,22,0.9); color: white; }
        .status-construction { background: rgba(59,130,246,0.9); color: white; }
        .status-completed { background: rgba(34,197,94,0.9); color: white; }
        .project-info { padding: 20px; }
        .project-name { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; transition: color 0.3s; }
        .project-type { font-size: 13px; color: var(--text-secondary); margin-bottom: 12px; transition: color 0.3s; }
        .project-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px; }
        .stat-item { display: flex; flex-direction: column; }
        .stat-label { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; }
        .stat-value { font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .project-progress { margin-bottom: 16px; }
        .progress-label { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; color: var(--text-secondary); }
        .progress-bar { height: 8px; background: var(--bg-hover); border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: #6366f1; border-radius: 4px; transition: width 0.3s; }
        .project-actions { display: flex; gap: 8px; padding-top: 16px; border-top: 1px solid var(--border-color); }
        .action-btn { flex: 1; padding: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-secondary); border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s; }
        .action-btn:hover { background: var(--bg-hover); }
        .action-btn.edit { border-color: #6366f1; color: #6366f1; }
        .action-btn.edit:hover { background: rgba(99,102,241,0.1); }
        .action-btn.delete { border-color: #ef4444; color: #ef4444; }
        .action-btn.delete:hover { background: rgba(239,68,68,0.1); }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 24px; }
        .alert-success { background: rgba(34,197,94,0.1); color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
        .empty-state { text-align: center; padding: 80px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 64px; margin-bottom: 20px; opacity: 0.3; }
        .empty-state h3 { font-size: 20px; margin-bottom: 12px; color: var(--text-secondary); }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fas fa-home"></i> Dream Mulk</div>
        <div class="nav-menu">
            <a href="{{ route('office.dashboard') }}" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="{{ route('office.properties') }}" class="nav-item"><i class="fas fa-building"></i> Properties</a>
            <a href="{{ route('office.projects') }}" class="nav-item active"><i class="fas fa-folder"></i> Projects</a>
            <a href="#" class="nav-item"><i class="fas fa-user-friends"></i> Leads</a>
            <a href="#" class="nav-item"><i class="fas fa-tag"></i> Offers</a>
            <a href="#" class="nav-item"><i class="fas fa-file-contract"></i> Agreements</a>
            <a href="{{ route('office.appointments') }}" class="nav-item"><i class="fas fa-calendar-alt"></i> Calendar</a>
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
                <input type="text" placeholder="Search projects...">
            </div>
            <div class="top-actions">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                <a href="{{ route('office.project.add') }}" class="add-btn">
                    <i class="fas fa-plus"></i> Add Project
                </a>
                <div class="user-avatar">{{ strtoupper(substr(auth('office')->user()->company_name, 0, 2)) }}</div>
            </div>
        </div>

        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-folder"></i> Projects</h1>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if($projects->count() > 0)
                <div class="projects-grid">
                    @foreach($projects as $project)
                        <div class="project-card">
                            <div class="project-image">
                                @php
                                    $coverImage = $project->cover_image_url ??
                                                 (is_array($project->images) && count($project->images) > 0 ? $project->images[0] :
                                                 'https://via.placeholder.com/400x220/6366f1/ffffff?text=No+Image');
                                @endphp
                                <img src="{{ $coverImage }}" alt="{{ $project->name['en'] ?? 'Project' }}">

                                @if($project->is_featured)
                                    <div class="project-badge">Featured</div>
                                @elseif($project->is_hot_project)
                                    <div class="project-badge" style="background: #ef4444;">Hot</div>
                                @elseif($project->is_premium)
                                    <div class="project-badge" style="background: #f59e0b;">Premium</div>
                                @endif

                                <div class="project-status status-{{ strtolower(explode('_', $project->status)[0]) }}">
                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                </div>
                            </div>

                            <div class="project-info">
                                <div class="project-name">{{ $project->name['en'] ?? 'Unnamed Project' }}</div>
                                <div class="project-type">{{ ucfirst(str_replace('_', ' ', $project->project_type)) }}</div>

                                <div class="project-stats">
                                    <div class="stat-item">
                                        <div class="stat-label">Total Units</div>
                                        <div class="stat-value">{{ $project->total_units ?? 0 }}</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">Available</div>
                                        <div class="stat-value">{{ $project->available_units ?? 0 }}</div>
                                    </div>
                                </div>

                                <div class="project-progress">
                                    <div class="progress-label">
                                        <span>Completion</span>
                                        <span><strong>{{ $project->completion_percentage ?? 0 }}%</strong></span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: {{ $project->completion_percentage ?? 0 }}%"></div>
                                    </div>
                                </div>

                                <div class="project-actions">
                                    <a href="{{ route('office.project.edit', $project->id) }}" class="action-btn edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('office.project.delete', $project->id) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Delete this project?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" style="width: 100%;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>No Projects Yet</h3>
                    <p>Start by adding your first project</p>
                    <a href="{{ route('office.project.add') }}" class="add-btn" style="margin-top: 20px; display: inline-flex;">
                        <i class="fas fa-plus"></i> Add Project
                    </a>
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
            if (savedTheme === 'dark') {
                document.querySelector('.main-content').setAttribute('data-theme', 'dark');
                document.getElementById('theme-icon').className = 'fas fa-sun';
            }
        });
    </script>
</body>
</html>
