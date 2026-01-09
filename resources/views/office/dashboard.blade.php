@extends('layouts.office-layout')

@section('title', 'Dashboard - Dream Mulk')
@section('search-placeholder', 'Search properties...')

@section('styles')
<style>
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 32px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 32px; }
    .stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 24px; transition: all 0.3s; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px var(--shadow); }
    .stat-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; }
    .stat-label { color: var(--text-muted); font-size: 14px; margin-bottom: 8px; }
    .stat-value { font-size: 32px; font-weight: 700; color: var(--text-primary); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .stat-growth { color: var(--text-secondary); font-size: 13px; }
    .stat-growth.positive { color: #22c55e; }
    .stat-growth.negative { color: #ef4444; }

    .content-grid { display: grid; grid-template-columns: 1fr 350px; gap: 24px; }
    .main-content { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 28px; }
    .sidebar-content { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 28px; }
    .section-title { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; }

    .search-box { margin-bottom: 24px; }
    .search-input { width: 100%; padding: 12px 16px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-size: 15px; }
    .search-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }

    .property-card { display: flex; gap: 16px; padding: 20px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 16px; transition: all 0.3s; text-decoration: none; }
    .property-card:hover { transform: translateX(4px); border-color: #6366f1; box-shadow: 0 8px 16px var(--shadow); }
    .property-image { width: 120px; height: 120px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
    .property-details { flex: 1; display: flex; flex-direction: column; }
    .property-name { font-size: 17px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; }
    .property-info { display: flex; gap: 16px; margin-bottom: 8px; }
    .property-badge { display: inline-block; padding: 4px 10px; background: rgba(99,102,241,0.12); color: #6366f1; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
    .property-status { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
    .property-status.available { background: rgba(34,197,94,0.12); color: #22c55e; }
    .property-status.sold { background: rgba(239,68,68,0.12); color: #ef4444; }
    .property-status.rented { background: rgba(249,115,22,0.12); color: #f97316; }
    .property-meta { display: flex; gap: 16px; color: var(--text-secondary); font-size: 13px; margin-top: auto; }
    .property-meta-item { display: flex; align-items: center; gap: 6px; }
    .property-price { font-size: 20px; font-weight: 700; color: #6366f1; margin-top: auto; }

    .quick-action { display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; text-decoration: none; transition: all 0.3s; margin-bottom: 12px; }
    .quick-action:hover { border-color: #6366f1; transform: translateX(4px); }
    .quick-action-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .quick-action-text { font-weight: 600; color: var(--text-primary); }

    .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 24px; }
    .page-link { padding: 8px 14px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); text-decoration: none; transition: all 0.2s; }
    .page-link:hover { border-color: #6366f1; color: #6366f1; }
    .page-link.active { background: #6366f1; color: white; border-color: #6366f1; }
    .page-link.disabled { opacity: 0.5; cursor: not-allowed; }

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 64px; margin-bottom: 16px; opacity: 0.3; }

    @media (max-width: 1024px) {
        .content-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<h1 class="page-title">Dashboard</h1>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <p class="stat-label">Total Properties</p>
                <h2 class="stat-value">{{ $totalProperties }}</h2>
            </div>
            <div class="stat-icon" style="background: rgba(99,102,241,0.12);">
                <i class="fas fa-building" style="color: #6366f1; font-size: 20px;"></i>
            </div>
        </div>
        <p class="stat-growth {{ $propertyGrowth >= 0 ? 'positive' : 'negative' }}">
            {{ $propertyGrowth >= 0 ? '↑' : '↓' }} {{ abs($propertyGrowth) }}% from last month
        </p>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div>
                <p class="stat-label">Active Agents</p>
                <h2 class="stat-value">{{ $totalAgents }}</h2>
            </div>
            <div class="stat-icon" style="background: rgba(34,197,94,0.12);">
                <i class="fas fa-user-tie" style="color: #22c55e; font-size: 20px;"></i>
            </div>
        </div>
        <p class="stat-growth {{ $agentGrowth >= 0 ? 'positive' : 'negative' }}">
            {{ $agentGrowth >= 0 ? '↑' : '↓' }} {{ abs($agentGrowth) }}% from last month
        </p>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div>
                <p class="stat-label">Total Revenue</p>
                <h2 class="stat-value">${{ number_format($totalRevenue) }}</h2>
            </div>
            <div class="stat-icon" style="background: rgba(249,115,22,0.12);">
                <i class="fas fa-dollar-sign" style="color: #f97316; font-size: 20px;"></i>
            </div>
        </div>
        <p class="stat-growth {{ $revenueGrowth >= 0 ? 'positive' : 'negative' }}">
            {{ $revenueGrowth >= 0 ? '↑' : '↓' }} {{ abs($revenueGrowth) }}% from last month
        </p>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div>
                <p class="stat-label">Appointments</p>
                <h2 class="stat-value">{{ $totalAppointments }}</h2>
            </div>
            <div class="stat-icon" style="background: rgba(168,85,247,0.12);">
                <i class="fas fa-calendar-alt" style="color: #a855f7; font-size: 20px;"></i>
            </div>
        </div>
        <p class="stat-growth" style="color: #f97316;">
            {{ $pendingAppointments }} pending today
        </p>
    </div>
</div>

<!-- Main Content Grid -->
<div class="content-grid">
    <!-- Recent Properties -->
    <div class="main-content">
        <h3 class="section-title">Recent Properties</h3>

        <!-- Search Box -->
        <div class="search-box">
            <form action="{{ route('office.dashboard') }}" method="GET">
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Search by name, type, or status..."
                    value="{{ $search }}"
                >
            </form>
        </div>

        @if($recentProperties->count() > 0)
            <!-- Properties List -->
            <div>
                @foreach($recentProperties as $property)
                    @php
                        $images = is_array($property->images) ? $property->images : json_decode($property->images, true);
                        $firstImage = is_array($images) && count($images) > 0 ? $images[0] : 'https://via.placeholder.com/120x120/6366f1/ffffff?text=No+Image';

                        $name = is_array($property->name) ? ($property->name['en'] ?? 'N/A') : (json_decode($property->name, true)['en'] ?? $property->name ?? 'N/A');

                        $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
                        $priceUsd = $price['usd'] ?? 0;

                        $rooms = is_array($property->rooms) ? $property->rooms : json_decode($property->rooms, true);
                        $bedrooms = $rooms['bedroom']['count'] ?? 0;
                        $bathrooms = $rooms['bathroom']['count'] ?? 0;
                    @endphp

                    <a href="{{ route('office.property.edit', $property->id) }}" class="property-card">
                        <img src="{{ $firstImage }}" alt="{{ $name }}" class="property-image">

                        <div class="property-details">
                            <h4 class="property-name">{{ $name }}</h4>

                            <div class="property-info">
                                <span class="property-badge">{{ ucfirst($property->listing_type) }}</span>
                                <span class="property-status {{ $property->status }}">{{ ucfirst($property->status) }}</span>
                            </div>

                            <div class="property-meta">
                                <span class="property-meta-item">
                                    <i class="fas fa-bed"></i> {{ $bedrooms }} Beds
                                </span>
                                <span class="property-meta-item">
                                    <i class="fas fa-bath"></i> {{ $bathrooms }} Baths
                                </span>
                                <span class="property-meta-item">
                                    <i class="fas fa-ruler-combined"></i> {{ $property->area ?? 0 }} m²
                                </span>
                                <span class="property-meta-item">
                                    <i class="fas fa-eye"></i> {{ $property->views ?? 0 }} views
                                </span>
                            </div>

                            <div class="property-price">${{ number_format($priceUsd) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pagination">
                @if ($recentProperties->onFirstPage())
                    <span class="page-link disabled">Previous</span>
                @else
                    <a href="{{ $recentProperties->previousPageUrl() }}&search={{ $search }}" class="page-link">Previous</a>
                @endif

                @foreach ($recentProperties->getUrlRange(1, $recentProperties->lastPage()) as $page => $url)
                    <a href="{{ $url }}&search={{ $search }}" class="page-link {{ $page == $recentProperties->currentPage() ? 'active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if ($recentProperties->hasMorePages())
                    <a href="{{ $recentProperties->nextPageUrl() }}&search={{ $search }}" class="page-link">Next</a>
                @else
                    <span class="page-link disabled">Next</span>
                @endif
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-building"></i>
                <h3 style="color: var(--text-secondary); margin-bottom: 8px;">
                    {{ $search ? 'No properties found' : 'No properties yet' }}
                </h3>
                <p>{{ $search ? 'Try a different search term' : 'Start by adding your first property' }}</p>
                @if(!$search)
                    <a href="{{ route('office.property.upload') }}" style="display: inline-block; margin-top: 16px; padding: 10px 20px; background: #6366f1; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">
                        <i class="fas fa-plus"></i> Add Property
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Quick Actions Sidebar -->
    <div class="sidebar-content">
        <h3 class="section-title">Quick Actions</h3>

        <a href="{{ route('office.property.upload') }}" class="quick-action">
            <div class="quick-action-icon" style="background: rgba(99,102,241,0.12);">
                <i class="fas fa-plus" style="color: #6366f1;"></i>
            </div>
            <span class="quick-action-text">Add Property</span>
        </a>

        <a href="{{ route('office.agents.add') }}" class="quick-action">
            <div class="quick-action-icon" style="background: rgba(34,197,94,0.12);">
                <i class="fas fa-user-plus" style="color: #22c55e;"></i>
            </div>
            <span class="quick-action-text">Add Agent</span>
        </a>

        <a href="{{ route('office.appointments') }}" class="quick-action">
            <div class="quick-action-icon" style="background: rgba(168,85,247,0.12);">
                <i class="fas fa-calendar" style="color: #a855f7;"></i>
            </div>
            <span class="quick-action-text">View Calendar</span>
        </a>

        <a href="{{ route('office.projects') }}" class="quick-action">
            <div class="quick-action-icon" style="background: rgba(249,115,22,0.12);">
                <i class="fas fa-folder-plus" style="color: #f97316;"></i>
            </div>
            <span class="quick-action-text">Add Project</span>
        </a>
    </div>
</div>
@endsection
