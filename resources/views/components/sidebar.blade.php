<style>
    .sidebar {
        width: 240px;
        background: #16171d;
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(255,255,255,0.06);
    }
    .logo {
        padding: 20px 24px;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .logo i {
        font-size: 22px;
        color: #6366f1;
    }
    .nav-menu {
        flex: 1;
        padding: 16px 12px;
        overflow-y: auto;
    }
    .nav-item {
        padding: 11px 16px;
        color: rgba(255,255,255,0.5);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: 14px;
        text-decoration: none;
        margin-bottom: 4px;
        border-radius: 8px;
        font-weight: 500;
    }
    .nav-item:hover {
        background: rgba(255,255,255,0.04);
        color: rgba(255,255,255,0.9);
    }
    .nav-item.active {
        background: #6366f1;
        color: #fff;
    }
    .nav-item i {
        width: 20px;
        text-align: center;
        font-size: 16px;
    }
    .nav-bottom {
        border-top: 1px solid rgba(255,255,255,0.06);
        padding: 16px 12px;
    }
</style>

<div class="sidebar">
    <div class="logo">
        <i class="fas fa-layer-group"></i> Dream Mulk
    </div>

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


        <a href="{{ route('office.subscriptions') }}" class="nav-item {{ request()->routeIs('office.subscriptions*') ? 'active' : '' }}">
            <i class="fas fa-crown"></i> Subscriptions
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
            @csrf
            <button type="submit" class="nav-item" style="width: 100%; background: none; border: none; text-align: left; cursor: pointer; color: rgba(255,255,255,0.5); font-family: inherit; font-size: 14px; font-weight: 500;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</div>
