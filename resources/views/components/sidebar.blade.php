<style>
    /* Sidebar Container */
    .sidebar {
        width: 260px; /* Slightly wider for better breathing room */
        background: #0f1116; /* Deeper, more premium dark */
        display: flex;
        flex-direction: column;
        height: 100vh;
        position: sticky;
        top: 0;
        border-right: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }

    /* Logo Section */
    .logo {
        padding: 32px 24px;
        font-size: 20px;
        font-weight: 800;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.5px;
    }

    .logo-icon {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .logo-icon i {
        font-size: 18px;
        color: white;
    }

    /* Menu Section */
    .nav-menu {
        flex: 1;
        padding: 0 14px;
        overflow-y: auto;
    }

    /* Section Label */
    .menu-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.3);
        font-weight: 700;
        margin: 24px 0 12px 16px;
    }

    /* Nav Items */
    .nav-item {
        padding: 12px 16px;
        color: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        text-decoration: none;
        margin-bottom: 4px;
        border-radius: 12px;
        font-weight: 500;
    }

    .nav-item i {
        font-size: 18px;
        transition: transform 0.2s ease;
    }

    /* Hover State */
    .nav-item:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
    }

    .nav-item:hover i {
        transform: translateX(2px);
    }

    /* Active State */
    .nav-item.active {
        background: rgba(99, 102, 241, 0.1);
        color: #818cf8; /* Softer indigo for dark background */
        position: relative;
    }

    .nav-item.active::before {
        content: '';
        position: absolute;
        left: -14px;
        top: 20%;
        height: 60%;
        width: 4px;
        background: #6366f1;
        border-radius: 0 4px 4px 0;
        box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
    }

    .nav-item.active i {
        color: #6366f1;
    }

    /* Badge (e.g., for subscriptions or alerts) */
    .nav-badge {
        margin-left: auto;
        background: rgba(99, 102, 241, 0.2);
        color: #818cf8;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
    }

    /* Bottom Section */
    .nav-bottom {
        padding: 20px 14px;
        margin-top: auto;
        background: rgba(0, 0, 0, 0.2);
    }

    .logout-btn {
        width: 100%;
        background: none;
        border: none;
        text-align: left;
        cursor: pointer;
        font-family: inherit;
        color: #f87171; /* Subtle red for logout */
        opacity: 0.8;
        transition: 0.2s;
    }

    .logout-btn:hover {
        background: rgba(248, 113, 113, 0.05);
        opacity: 1;
    }

    /* Custom Scrollbar for the menu */
    .nav-menu::-webkit-scrollbar {
        width: 4px;
    }
    .nav-menu::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
    }
</style>

<div class="sidebar">
    <div class="logo">
        <div class="logo-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <span>Dream Mulk</span>
    </div>

    <div class="nav-menu">
        <div class="menu-label">Main Menu</div>

        <a href="{{ route('office.dashboard') }}" class="nav-item {{ request()->routeIs('office.dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i> Dashboard
        </a>

        <a href="{{ route('office.properties') }}" class="nav-item {{ request()->routeIs('office.properties*') ? 'active' : '' }}">
            <i class="fas fa-building"></i> Properties
        </a>

        <a href="{{ route('office.projects') }}" class="nav-item {{ request()->routeIs('office.projects*') ? 'active' : '' }}">
            <i class="fas fa-city"></i> Projects
        </a>

        <div class="menu-label">Management</div>

        <a href="{{ route('office.agents') }}" class="nav-item {{ request()->routeIs('office.agents*') ? 'active' : '' }}">
            <i class="fas fa-user-tie"></i> Agents
        </a>

        <a href="{{ route('office.appointments') }}" class="nav-item {{ request()->routeIs('office.appointments*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> Calendar
        </a>

        <a href="{{ route('office.agreements') }}" class="nav-item {{ request()->routeIs('office.agreements*') ? 'active' : '' }}">
            <i class="fas fa-file-contract"></i> Agreements
        </a>

        <a href="{{ route('office.contacts') }}" class="nav-item {{ request()->routeIs('office.contacts*') ? 'active' : '' }}">
            <i class="fas fa-address-book"></i> Contacts
        </a>

        <div class="menu-label">Growth</div>

        <a href="{{ route('office.subscriptions') }}" class="nav-item {{ request()->routeIs('office.subscriptions*') ? 'active' : '' }}">
            <i class="fas fa-crown"></i> Subscriptions
            <span class="nav-badge">PRO</span>
        </a>

        <a href="{{ route('office.banners') }}" class="nav-item {{ request()->routeIs('office.banners*') ? 'active' : '' }}">
            <i class="fas fa-bullhorn"></i> Banner Ads
        </a>
    </div>

    <div class="nav-bottom">
        <a href="{{ route('office.profile') }}" class="nav-item {{ request()->routeIs('office.profile') ? 'active' : '' }}">
            <i class="fas fa-cog"></i> Settings
        </a>

        <form action="{{ route('office.logout') }}" method="POST" style="margin: 0;">
            @csrf<style></style>
            <button type="submit" class="nav-item logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</div>
