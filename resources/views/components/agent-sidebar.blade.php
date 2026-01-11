<!-- Mobile Toggle Button -->
<button class="sidebar-toggle-btn" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<div class="sidebar-nav">
    <div class="sidebar-header">
        <div class="logo-section">
            <i class="fas fa-building"></i>
            <h4>Dream Mulk</h4>
        </div>
    </div>

    <!-- Agent Info Card -->
    <div class="agent-info-card">
        <div class="agent-avatar">
            <span>{{ strtoupper(substr(auth('agent')->user()->agent_name ?? 'A', 0, 1)) }}</span>
        </div>
        <div class="agent-details">
            <div class="agent-name">{{ auth('agent')->user()->agent_name ?? 'Agent' }}</div>
            <div class="agent-role">
                <span class="status-dot"></span>
                Real Estate Agent
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="nav-menu">
        <div class="nav-section-title">MAIN MENU</div>

        <a href="{{ route('agent.dashboard') }}" class="nav-item {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('agent.properties') }}" class="nav-item {{ request()->routeIs('agent.properties*') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>My Properties</span>
        </a>

        <a href="{{ route('agent.property.add') }}" class="nav-item {{ request()->routeIs('agent.property.add') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i>
            <span>Add Property</span>
        </a>

        <a href="{{ route('agent.appointments') }}" class="nav-item {{ request()->routeIs('agent.appointments') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i>
            <span>Appointments</span>
        </a>

        <a href="{{ route('agent.banners') }}" class="nav-item {{ request()->routeIs('agent.banners*') ? 'active' : '' }}">
            <i class="fas fa-bullhorn"></i>
            <span>Banner Ads</span>
        </a>

        <div class="nav-section-title">ACCOUNT</div>

        <a href="{{ route('agent.subscriptions') }}" class="nav-item {{ request()->routeIs('agent.subscriptions') ? 'active' : '' }}">
            <i class="fas fa-crown"></i>
            <span>Subscriptions</span>
        </a>

        <a href="{{ route('agent.profile', auth('agent')->user()->id) }}" class="nav-item {{ request()->routeIs('agent.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>My Profile</span>
        </a>

        <div class="nav-section-title">OTHER</div>

        <a href="{{ route('newindex') }}" class="nav-item">
            <i class="fas fa-external-link-alt"></i>
            <span>Go To Website</span>
        </a>
    </nav>

    <!-- Logout Section -->
    <div class="sidebar-footer">
        <form id="logout-form" action="{{ route('agent.logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <a href="#" class="nav-item logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<style>
/* Sidebar Container */
.sidebar-nav {
    height: 100vh;
    width: 280px;
    position: fixed;
    top: 0;
    left: 0;
    background: #000000;
    padding: 0;
    box-shadow: 4px 0 24px rgba(0,0,0,0.3);
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 1000;
    display: flex;
    flex-direction: column;
}

.sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(48,59,151,0.3);
    border-radius: 10px;
}

/* Header */
.sidebar-header {
    padding: 32px 24px 24px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.logo-section {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-section i {
    font-size: 28px;
    color: #303b97;
}

.sidebar-header h4 {
    color: #ffffff;
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0;
}

/* Agent Info Card */
.agent-info-card {
    background: rgba(48,59,151,0.1);
    border: 1px solid rgba(48,59,151,0.2);
    border-radius: 16px;
    padding: 18px;
    margin: 20px 20px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
}

.agent-avatar {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, #303b97, #4a5bc5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 22px;
    box-shadow: 0 4px 16px rgba(48,59,151,0.4);
}

.agent-details {
    flex: 1;
}

.agent-name {
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 6px;
}

.agent-role {
    font-size: 13px;
    color: rgba(255,255,255,0.7);
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-dot {
    width: 7px;
    height: 7px;
    background: #10b981;
    border-radius: 50%;
    display: inline-block;
    animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Navigation Menu */
.nav-menu {
    padding: 8px 20px 20px;
    flex: 1;
}

.nav-section-title {
    font-size: 10px;
    font-weight: 700;
    color: rgba(255,255,255,0.4);
    letter-spacing: 1.2px;
    margin: 20px 0 10px 12px;
    text-transform: uppercase;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: rgba(255,255,255,0.7) !important;
    padding: 12px 14px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 4px;
    border-radius: 12px;
    position: relative;
    transition: all 0.3s;
}

.nav-item i {
    font-size: 17px;
    width: 20px;
    text-align: center;
    color: rgba(255,255,255,0.7) !important;
}

.nav-item:hover {
    color: #ffffff !important;
    background: rgba(48,59,151,0.2);
    transform: translateX(2px);
}

.nav-item:hover i {
    color: #ffffff !important;
}

.nav-item.active {
    color: #ffffff !important;
    background: #303b97 !important;
    font-weight: 700 !important;
    box-shadow: 0 6px 20px rgba(48,59,151,0.6) !important;
}

.nav-item.active i {
    color: #ffffff !important;
}

.nav-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 70%;
    background: #ffffff;
    border-radius: 0 4px 4px 0;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 16px 20px 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.logout-btn {
    color: #ef4444 !important;
}

.logout-btn i {
    color: #ef4444 !important;
}

.logout-btn:hover {
    background: rgba(239,68,68,0.12) !important;
}

/* Mobile Toggle Button */
.sidebar-toggle-btn {
    position: fixed;
    top: 20px;
    left: 20px;
    background: #000000;
    color: #303b97;
    border: 2px solid #303b97;
    font-size: 20px;
    width: 50px;
    height: 50px;
    display: none;
    justify-content: center;
    align-items: center;
    border-radius: 14px;
    cursor: pointer;
    z-index: 10000;
    box-shadow: 0 4px 20px rgba(48,59,151,0.4);
}

.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    display: none;
    z-index: 9998;
}

.sidebar-overlay.show {
    display: block;
}

/* Mobile Responsive */
@media (max-width: 900px) {
    .sidebar-nav {
        transform: translateX(-300px);
        transition: transform 0.4s;
        z-index: 9999;
    }

    .sidebar-nav.open {
        transform: translateX(0);
    }

    .sidebar-toggle-btn {
        display: flex;
    }
}
</style>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar-nav');
    const overlay = document.getElementById('sidebar-overlay');

    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}

document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => {
        if (window.innerWidth <= 900) {
            const sidebar = document.querySelector('.sidebar-nav');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }
    });
});
</script>
