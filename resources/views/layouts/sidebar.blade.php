<!-- resources/views/partials/sidebar.blade.php -->
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<style>
    .sidebar-nav {
        border-radius: 3px;
        height: 100vh; /* Full viewport height */
        width: 250px; /* Fixed width */
        position: fixed;
        top: 0px; /* Adjust as needed for your layout */
        left: 0;
        background-color: #fff;
        padding: 15px;
        box-shadow: 0px 0px 10px rgba(133,133,133, 0.1);
        border: none;
        overflow-y: auto; /* Allows scrolling if content overflows */
    }
    .sidebar-nav a {
        display: block;
        color: #333;
        padding: 10px;
        text-decoration: none;
        font-size: 16px;
        margin-bottom: 10px;
    }
    .sidebar-nav a:hover,
    .sidebar-nav a.active {
        border-radius: 10px;
        background-color: #cfcfcf;
    }
    .sidebar-nav i {
        margin-right: 10px;
    }
    h4 {
        display: block;
        margin-block-start: 1.33em;
        margin-block-end: 1.33em;
        margin-inline-start: 0px;
        margin-inline-end: 0px;
        font-size: 1.4rem;
        font-weight: bold;
        unicode-bidi: isolate;
    }
    .h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
        margin-bottom: .5rem;
        font-family: inherit;
        font-weight: 500;
        line-height: 1.2;
        color: inherit;
    }
</style>
<div class="sidebar-nav">
    <h4><br> Dashboard</h4>
    <a href="{{ route('agent.admin-dashboard') }}" class="{{ request()->routeIs('agent.admin-dashboard') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="{{ route('admin.property-list') }}" class="{{ request()->routeIs('admin.property-list') ? 'active' : '' }}">
        <i class="fas fa-home"></i>My Properties

        <a href="{{ route('property.upload') }}" class="{{ request()->routeIs('admin.property-list') ? 'active' : '' }}">
        <i class="fas fa-plus-circle"></i> Add Properties

    </a>

{{--
@if(session()->has('agent_id'))
    <a href="{{ route('agent.review') }}" class="{{ request()->routeIs('agent.review') ? 'active' : '' }}">
        <i class="fas fa-star"></i> Reviews
    </a>
@endif
--}}




@php
    $user = Auth::user();                // normal user
    $agent = Auth::guard('agent')->user(); // agent
@endphp

{{-- Agent-only section --}}
@if($agent)
    <a href="{{ route('admin.profile') }}"
       class="{{ request()->routeIs('admin.profile') ? 'active' : '' }}">
        <i class="fas fa-user"></i> My Profile
    </a>

    <a href="{{ route('agents.list') }}"
       class="{{ request()->routeIs('agents.list') ? 'active' : '' }}">
        <i class="fas fa-users"></i> Agents
    </a>

<a href="{{ route('projects') }}"
   class="{{ request()->routeIs('projects') ? 'active' : '' }}">
    <i class="fas fa-building"></i> Projects
</a>

@endif


  

    <a href="{{ route('notifications') }}" class="{{ request()->routeIs('notifications') ? 'active' : '' }}">
    <i class="fas fa-bell"></i> Notifications
</a>

<a href="{{ route('schedule') }}" class="{{ request()->routeIs('schedule') ? 'active' : '' }}">
    <i class="fas fa-calendar-alt"></i> Schedule
</a>



<a href="{{ route('projects') }}" class="{{ request()->routeIs('projects') ? 'active' : '' }}">
    <i class="fas fa-tasks"></i> Projects
</a>

<a class="unique-nav-link {{ request()->routeIs('newindex') ? ' active' : '' }}" href="{{ route('newindex') }}">
    <i class="fas fa-external-link-alt"></i> Go To Website
    </a>




@php
    $user = Auth::user(); // default user (admin, normal user, etc.)
    $agent = Auth::guard('agent')->user(); // agent login
@endphp

{{-- 1️⃣ ADMIN USER LOGGED IN --}}
@if($user && $user->role === 'admin')
    <a href="{{ route('agent.real-estate-office') }}"
       class="{{ request()->routeIs('real-estate-offices.create') ? 'active' : '' }}">
        <i class="fas fa-building"></i> Real Estate Offices
    </a>
@endif


{{-- 2️⃣ AGENT LOGGED IN --}}
@if($agent)
    <a href="{{ $agent->company_id
                ? route('agent.office.profile', $agent->company_id)
                : route('agent.real-estate-office') }}"
       class="{{ request()->routeIs('agent.real-estate-office*') ? 'active' : '' }}">
        <i class="fas fa-building"></i> Real Estate Office
    </a>
@endif







  
@auth
    @if(Auth::check() && Auth::user()->role === 'admin')
        <a href="{{ route('admin.users') }}" 
           class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Manage Users
        </a>

              <a href="{{ route('admin.properties') }}" 
           class="{{ request()->routeIs('admin.properties') ? 'active' : '' }}">
            <i class="fas fa-building"></i> Manage Properties
        </a>
    @endif
@endauth




    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
        @method('POST')
    </form>
    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>