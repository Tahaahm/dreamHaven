<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plans - Dream Mulk</title>
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
        .theme-toggle:hover { background: #eff3ff; color: #6366f1; border-color: #6366f1; }
        .add-btn { background: #6366f1; color: white; padding: 11px 22px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 9px; font-size: 14px; text-decoration: none; transition: all 0.2s; }
        .add-btn:hover { background: #5558e3; }

        .content-area { flex: 1; overflow-y: auto; padding: 32px; background: var(--bg-main); transition: background 0.3s; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); transition: color 0.3s; }

        .tabs { display: flex; gap: 8px; margin-bottom: 28px; border-bottom: 2px solid var(--border-color); padding-bottom: 0; }
        .tab { padding: 12px 24px; background: transparent; border: none; color: var(--text-muted); font-size: 14px; font-weight: 600; cursor: pointer; position: relative; transition: all 0.2s; border-radius: 8px 8px 0 0; }
        .tab:hover { color: var(--text-secondary); background: var(--bg-hover); }
        .tab.active { color: #6366f1; }
        .tab.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: #6366f1; }

        .plans-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px; }
        .plan-card { background: var(--bg-card); border: 2px solid var(--border-color); border-radius: 16px; padding: 32px; transition: all 0.3s; position: relative; }
        .plan-card:hover { transform: translateY(-4px); box-shadow: 0 16px 48px var(--shadow); border-color: rgba(99,102,241,0.4); }
        .plan-card.featured { border-color: #6366f1; }
        .featured-badge { position: absolute; top: -12px; right: 24px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 6px 16px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .plan-header { margin-bottom: 24px; }
        .plan-name { font-size: 24px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
        .plan-duration { color: var(--text-muted); font-size: 14px; margin-bottom: 20px; }
        .plan-pricing { margin-bottom: 24px; }
        .price-main { display: flex; align-items: baseline; gap: 8px; margin-bottom: 8px; }
        .price-amount { font-size: 42px; font-weight: 800; color: #6366f1; }
        .price-currency { font-size: 18px; color: var(--text-secondary); }
        .price-period { font-size: 14px; color: var(--text-muted); }
        .price-original { text-decoration: line-through; color: var(--text-muted); font-size: 18px; }
        .savings-badge { background: rgba(34,197,94,0.12); color: #22c55e; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; display: inline-block; margin-top: 8px; }
        .plan-features { margin-bottom: 24px; }
        .feature-item { display: flex; align-items: start; gap: 12px; padding: 10px 0; color: var(--text-secondary); font-size: 14px; }
        .feature-item i { color: #22c55e; margin-top: 2px; flex-shrink: 0; }
        .plan-actions { display: flex; gap: 12px; }
        .btn { padding: 12px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s; border: none; flex: 1; text-align: center; text-decoration: none; display: inline-block; }
        .btn-primary { background: #6366f1; color: white; }
        .btn-primary:hover { background: #5558e3; }
        .btn-secondary { background: var(--bg-hover); color: var(--text-primary); border: 1px solid var(--border-color); }
        .btn-secondary:hover { border-color: #6366f1; color: #6366f1; }
        .btn-danger { background: rgba(239,68,68,0.12); color: #ef4444; }
        .btn-danger:hover { background: #ef4444; color: white; }

        .empty { text-align: center; padding: 70px 20px; color: var(--text-muted); }
        .empty i { font-size: 52px; margin-bottom: 18px; opacity: 0.4; }
        .empty h3 { font-size: 19px; margin-bottom: 9px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fas fa-layer-group"></i> Dream Mulk</div>
        <div class="nav-menu">
            <a href="{{ route('office.dashboard') }}" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="{{ route('office.properties') }}" class="nav-item"><i class="fas fa-building"></i> Properties</a>
            <a href="{{ route('office.projects') }}" class="nav-item"><i class="fas fa-folder"></i> Projects</a>
            <a href="#" class="nav-item"><i class="fas fa-user-friends"></i> Leads</a>
            <a href="#" class="nav-item"><i class="fas fa-tag"></i> Offers</a>
            <a href="#" class="nav-item"><i class="fas fa-file-contract"></i> Agreements</a>
            <a href="{{ route('office.appointments') }}" class="nav-item"><i class="fas fa-calendar-alt"></i> Calendar</a>
            <a href="#" class="nav-item"><i class="fas fa-chart-bar"></i> Activities</a>
            <a href="#" class="nav-item"><i class="fas fa-address-book"></i> Contacts</a>
            <a href="{{ route('office.agents') }}" class="nav-item"><i class="fas fa-user-tie"></i> Agents</a>
            <a href="#" class="nav-item"><i class="fas fa-bullhorn"></i> Campaigns</a>
            <a href="#" class="nav-item"><i class="fas fa-file-alt"></i> Documents</a>
            <a href="{{ route('office.subscriptions') }}" class="nav-item active"><i class="fas fa-crown"></i> Subscriptions</a>
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
                <input type="text" placeholder="Search subscription plans">
            </div>
            <div class="top-actions">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
            </div>
        </div>

        <div class="content-area">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Subscription Plans</h1>
                    <p style="color: var(--text-muted); margin-top: 8px;">Choose the perfect plan for your business needs</p>
                </div>
            </div>

            <div class="tabs">
                <button class="tab active" data-type="all">All Plans</button>
                <button class="tab" data-type="real_estate_office">Office Plans</button>
                <button class="tab" data-type="agent">Agent Plans</button>
                <button class="tab" data-type="banner">Banner Plans</button>
                <button class="tab" data-type="services">Service Plans</button>
            </div>

            @if($plans->count() > 0)
                <div class="plans-grid">
                    @foreach($plans as $plan)
                    <div class="plan-card {{ $plan->is_featured ? 'featured' : '' }}">
                        @if($plan->is_featured)
                            <div class="featured-badge">Most Popular</div>
                        @endif

                        <div class="plan-header">
                            <div class="plan-name">{{ $plan->name }}</div>
                            <div class="plan-duration">{{ $plan->duration_label }}</div>
                        </div>

                        <div class="plan-pricing">
                            <div class="price-main">
                                <span class="price-amount">${{ number_format($plan->final_price_usd, 0) }}</span>
                                <span class="price-currency">USD</span>
                            </div>
                            <div style="color: var(--text-muted); font-size: 13px;">
                                ${{ number_format($plan->price_per_month_usd, 2) }}/month
                            </div>
                            @if($plan->discount_percentage > 0)
                                <div class="price-original">${{ number_format($plan->original_price_usd, 0) }}</div>
                                <span class="savings-badge">Save {{ $plan->savings_percentage }}%</span>
                            @endif
                        </div>

                        @if($plan->description)
                            <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 20px;">{{ $plan->description }}</p>
                        @endif

                        @if($plan->features && count($plan->features) > 0)
                            <div class="plan-features">
                                @foreach($plan->features as $feature)
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>{{ $feature }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($plan->max_properties)
                            <div style="background: var(--bg-main); padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border-color);">
                                <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px;">Max Properties</div>
                                <div style="font-size: 18px; font-weight: 700; color: #6366f1;">{{ $plan->max_properties }} Properties</div>
                                @if($plan->price_per_property_usd)
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">${{ $plan->price_per_property_usd }}/property</div>
                                @endif
                            </div>
                        @endif

                        <div class="plan-actions">
                            <a href="{{ route('office.subscription.subscribe', $plan->id) }}" class="btn btn-primary">Subscribe Now</a>
                            <a href="{{ route('office.subscription.details', $plan->id) }}" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty">
                    <i class="fas fa-crown"></i>
                    <h3>No Subscription Plans Available</h3>
                    <p>Check back later for available plans</p>
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

            // Tab filtering
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    const type = this.dataset.type;
                    window.location.href = `{{ route('office.subscriptions') }}?type=${type}`;
                });
            });
        });
    </script>
</body>
</html>
