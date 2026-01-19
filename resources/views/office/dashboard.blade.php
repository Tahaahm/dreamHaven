@extends('layouts.office-layout')

@section('title', 'Dashboard - Dream Mulk')

@section('styles')
<style>
    .page-title { font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px; }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; }
    .stat-value { font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 8px; }
    .stat-label { font-size: 14px; color: #64748b; }
    .stat-growth { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; margin-top: 8px; }
    .stat-growth.positive { background: #dcfce7; color: #16a34a; }
    .stat-growth.negative { background: #fee2e2; color: #dc2626; }
    .stat-growth.neutral { background: #fef3c7; color: #d97706; }

    .content-grid { display: grid; grid-template-columns: 1fr 350px; gap: 24px; }

    .section-card { background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 32px; }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .section-title { font-size: 20px; font-weight: 700; color: #1a202c; }
    .view-all-link { color: #303b97; text-decoration: none; font-size: 14px; font-weight: 600; }
    .view-all-link:hover { text-decoration: underline; }

    .search-box { margin-bottom: 24px; position: relative; }
    .search-input { width: 100%; padding: 12px 16px; background: #f8fafc; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; color: #1a202c; }
    .search-input:focus { outline: none; border-color: #303b97; box-shadow: 0 0 0 3px rgba(48,59,151,0.1); }

    .properties-list { display: flex; flex-direction: column; gap: 16px; }

    .property-card { display: flex; gap: 20px; padding: 20px; background: white; border: 2px solid #e5e7eb; border-radius: 12px; text-decoration: none; transition: all 0.3s; }
    .property-card:hover { border-color: #303b97; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

    .property-image-wrapper { position: relative; width: 140px; height: 140px; flex-shrink: 0; }
    .property-image { width: 100%; height: 100%; border-radius: 10px; object-fit: cover; }
    .property-views-badge { position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.75); color: white; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; display: flex; align-items: center; gap: 4px; }

    .property-details { flex: 1; display: flex; flex-direction: column; gap: 12px; }
    .property-name { font-size: 18px; font-weight: 700; color: #1a202c; }

    .property-badges { display: flex; gap: 8px; flex-wrap: wrap; }
    .property-badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; background: #eff6ff; color: #303b97; }

    .property-status { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; }
    .property-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .property-status.available { background: #dcfce7; color: #16a34a; }
    .property-status.available::before { background: #16a34a; }
    .property-status.sold { background: #fee2e2; color: #dc2626; }
    .property-status.sold::before { background: #dc2626; }
    .property-status.rented { background: #fef3c7; color: #d97706; }
    .property-status.rented::before { background: #d97706; }

    .property-meta { display: flex; gap: 20px; flex-wrap: wrap; }
    .property-meta-item { display: flex; align-items: center; gap: 6px; color: #64748b; font-size: 13px; font-weight: 500; }
    .property-meta-item i { color: #94a3b8; }

    .property-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 12px; border-top: 1px solid #f1f5f9; }
    .property-price { font-size: 22px; font-weight: 700; color: #303b97; }
    .property-action { display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: #eff6ff; color: #303b97; border-radius: 8px; font-size: 13px; font-weight: 600; }

    .quick-actions { display: flex; flex-direction: column; gap: 12px; }
    .quick-action { display: flex; align-items: center; gap: 14px; padding: 16px; background: white; border: 2px solid #e5e7eb; border-radius: 12px; text-decoration: none; transition: all 0.3s; }
    .quick-action:hover { border-color: #303b97; transform: translateX(4px); }

    .quick-action-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .quick-action:nth-child(1) .quick-action-icon { background: #eff6ff; color: #303b97; }
    .quick-action:nth-child(2) .quick-action-icon { background: #dcfce7; color: #16a34a; }
    .quick-action:nth-child(3) .quick-action-icon { background: #fef3c7; color: #d97706; }
    .quick-action:nth-child(4) .quick-action-icon { background: #f3e8ff; color: #a855f7; }

    .quick-action-content { flex: 1; }
    .quick-action-text { font-weight: 700; color: #1a202c; font-size: 14px; display: block; margin-bottom: 2px; }
    .quick-action-desc { font-size: 12px; color: #64748b; }
    .quick-action-arrow { color: #94a3b8; }

    .pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 24px; }
    .page-link { min-width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; padding: 0 12px; background: white; border: 2px solid #e5e7eb; border-radius: 8px; color: #1a202c; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.3s; }
    .page-link:hover:not(.disabled):not(.active) { border-color: #303b97; color: #303b97; }
    .page-link.active { background: #303b97; color: white; border-color: #303b97; }
    .page-link.disabled { opacity: 0.4; cursor: not-allowed; }

    .empty-state { text-align: center; padding: 80px 24px; }
    .empty-state-icon { width: 80px; height: 80px; margin: 0 auto 24px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .empty-state-icon i { font-size: 32px; color: #94a3b8; }
    .empty-state h3 { font-size: 18px; font-weight: 700; color: #1a202c; margin-bottom: 8px; }
    .empty-state p { color: #64748b; font-size: 14px; margin-bottom: 24px; }
    .empty-state-action { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: #303b97; color: white; border-radius: 10px; font-weight: 600; font-size: 14px; text-decoration: none; transition: all 0.3s; }
    .empty-state-action:hover { background: #1e2875; transform: translateY(-2px); }

    @media (max-width: 1024px) {
        .content-grid { grid-template-columns: 1fr; }
        .quick-actions { display: grid; grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .property-card { flex-direction: column; }
        .property-image-wrapper { width: 100%; height: 200px; }
        .quick-actions { grid-template-columns: 1fr; }
    }

    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr; }
        .property-footer { flex-direction: column; align-items: stretch; gap: 12px; }
    }
</style>
@endsection

@section('content')
<h1 class="page-title">Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $totalProperties }}</div>
        <div class="stat-label">Total Properties</div>
        <div class="stat-growth {{ $propertyGrowth >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $propertyGrowth >= 0 ? 'up' : 'down' }}"></i>
            <span>{{ abs($propertyGrowth) }}% from last month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-value">{{ $totalAgents }}</div>
        <div class="stat-label">Active Agents</div>
        <div class="stat-growth {{ $agentGrowth >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $agentGrowth >= 0 ? 'up' : 'down' }}"></i>
            <span>{{ abs($agentGrowth) }}% from last month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-value">${{ number_format($totalRevenue) }}</div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-growth {{ $revenueGrowth >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $revenueGrowth >= 0 ? 'up' : 'down' }}"></i>
            <span>{{ abs($revenueGrowth) }}% from last month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-value">{{ $totalAppointments }}</div>
        <div class="stat-label">Appointments</div>
        <div class="stat-growth neutral">
            <i class="fas fa-clock"></i>
            <span>{{ $pendingAppointments }} pending today</span>
        </div>
    </div>
</div>

<div class="content-grid">
    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">Recent Properties</h3>
            <a href="{{ route('office.properties') }}" class="view-all-link">View All →</a>
        </div>

        <div class="search-box">
            <form action="{{ route('office.dashboard') }}" method="GET">
                <input type="text" name="search" class="search-input" placeholder="Search by name, type, or status..." value="{{ $search }}">
            </form>
        </div>

        @if($recentProperties->count() > 0)
            <div class="properties-list">
                @foreach($recentProperties as $property)
                    @php
                        $images = is_array($property->images) ? $property->images : json_decode($property->images, true);
                        $firstImage = is_array($images) && count($images) > 0 ? $images[0] : 'https://via.placeholder.com/140x140/303b97/ffffff?text=No+Image';

                        $name = is_array($property->name) ? ($property->name['en'] ?? 'N/A') : (json_decode($property->name, true)['en'] ?? $property->name ?? 'N/A');

                        $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
                        $priceUsd = $price['usd'] ?? 0;

                        $rooms = is_array($property->rooms) ? $property->rooms : json_decode($property->rooms, true);
                        $bedrooms = $rooms['bedroom']['count'] ?? 0;
                        $bathrooms = $rooms['bathroom']['count'] ?? 0;
                    @endphp

                    <a href="{{ route('office.property.edit', $property->id) }}" class="property-card">
                        <div class="property-image-wrapper">
                            <img src="{{ $firstImage }}" alt="{{ $name }}" class="property-image">
                            {{-- <div class="property-views-badge">
                                <i class="fas fa-eye"></i>
                                {{ number_format($property->views ?? 0) }}
                            </div> --}}
                        </div>

                        <div class="property-details">
                            <h4 class="property-name">{{ $name }}</h4>

                            <div class="property-badges">
                                <span class="property-badge">
                                    <i class="fas fa-tag"></i>
                                    {{ ucfirst($property->listing_type) }}
                                </span>
                                <span class="property-status {{ $property->status }}">
                                    {{ ucfirst($property->status) }}
                                </span>
                            </div>

                            <div class="property-meta">
                                <span class="property-meta-item">
                                    <i class="fas fa-bed"></i>
                                    {{ $bedrooms }} Beds
                                </span>
                                <span class="property-meta-item">
                                    <i class="fas fa-bath"></i>
                                    {{ $bathrooms }} Baths
                                </span>
                                <span class="property-meta-item">
                                    <i class="fas fa-ruler-combined"></i>
                                    {{ number_format($property->area ?? 0) }} m²
                                </span>
                            </div>

                            <div class="property-footer">
                                <div class="property-price">${{ number_format($priceUsd) }}</div>
                                <div class="property-action">
                                    Edit Details
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="pagination">
                @if ($recentProperties->onFirstPage())
                    <span class="page-link disabled">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $recentProperties->previousPageUrl() }}&search={{ $search }}" class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @endif

                @foreach ($recentProperties->getUrlRange(1, $recentProperties->lastPage()) as $page => $url)
                    <a href="{{ $url }}&search={{ $search }}" class="page-link {{ $page == $recentProperties->currentPage() ? 'active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if ($recentProperties->hasMorePages())
                    <a href="{{ $recentProperties->nextPageUrl() }}&search={{ $search }}" class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <span class="page-link disabled">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                @endif
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-{{ $search ? 'search' : 'building' }}"></i>
                </div>
                <h3>{{ $search ? 'No properties found' : 'No properties yet' }}</h3>
                <p>{{ $search ? 'Try adjusting your search terms or filters' : 'Start by adding your first property to the system' }}</p>
                @if(!$search)
                    <a href="{{ route('office.property.upload') }}" class="empty-state-action">
                        <i class="fas fa-plus"></i>
                        Add Your First Property
                    </a>
                @endif
            </div>
        @endif
    </div>

    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">Quick Actions</h3>
        </div>

        <div class="quick-actions">
            <a href="{{ route('office.property.upload') }}" class="quick-action">
                <div class="quick-action-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="quick-action-content">
                    <span class="quick-action-text">Add Property</span>
                    <span class="quick-action-desc">List a new property</span>
                </div>
                <i class="fas fa-arrow-right quick-action-arrow"></i>
            </a>

            <a href="{{ route('office.agents.add') }}" class="quick-action">
                <div class="quick-action-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="quick-action-content">
                    <span class="quick-action-text">Add Agent</span>
                    <span class="quick-action-desc">Invite team member</span>
                </div>
                <i class="fas fa-arrow-right quick-action-arrow"></i>
            </a>

            <a href="{{ route('office.appointments') }}" class="quick-action">
                <div class="quick-action-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="quick-action-content">
                    <span class="quick-action-text">View Calendar</span>
                    <span class="quick-action-desc">Manage appointments</span>
                </div>
                <i class="fas fa-arrow-right quick-action-arrow"></i>
            </a>

            <a href="{{ route('office.projects') }}" class="quick-action">
                <div class="quick-action-icon">
                    <i class="fas fa-folder-plus"></i>
                </div>
                <div class="quick-action-content">
                    <span class="quick-action-text">Add Project</span>
                    <span class="quick-action-desc">Create new project</span>
                </div>
                <i class="fas fa-arrow-right quick-action-arrow"></i>
            </a>
        </div>
    </div>
</div>
@endsection
