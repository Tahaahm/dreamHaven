@extends('layouts.office-layout')

@section('title', 'Dashboard - Dream Mulk')
@section('search-placeholder', 'Search properties...')

@section('styles')
<style>
    /* ============================================
       VARIABLES & BASE
    ============================================ */
    :root {
        --gradient-primary: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        --gradient-success: linear-gradient(135deg, #22c55e 0%, #10b981 100%);
        --gradient-warning: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
        --gradient-purple: linear-gradient(135deg, #a855f7 0%, #c084fc 100%);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ============================================
       HEADER
    ============================================ */
    .page-header {
        margin-bottom: 32px;
        animation: fadeInDown 0.5s ease;
    }

    .page-title {
        font-size: 36px;
        font-weight: 800;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }

    .page-subtitle {
        color: var(--text-muted);
        font-size: 15px;
    }

    /* ============================================
       STATISTICS CARDS
    ============================================ */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
        animation: fadeInUp 0.6s ease;
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 28px;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
        opacity: 0;
        transition: var(--transition-smooth);
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px var(--shadow);
        border-color: transparent;
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-card:nth-child(1)::before { background: var(--gradient-primary); }
    .stat-card:nth-child(2)::before { background: var(--gradient-success); }
    .stat-card:nth-child(3)::before { background: var(--gradient-warning); }
    .stat-card:nth-child(4)::before { background: var(--gradient-purple); }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 16px;
    }

    .stat-label {
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        letter-spacing: -1px;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition-smooth);
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .stat-growth {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        margin-top: 12px;
    }

    .stat-growth.positive {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }

    .stat-growth.negative {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .stat-growth.neutral {
        background: rgba(249, 115, 22, 0.1);
        color: #f97316;
    }

    /* ============================================
       CONTENT GRID
    ============================================ */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 24px;
        animation: fadeInUp 0.7s ease;
    }

    .main-content,
    .sidebar-content {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 32px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #6366f1;
    }

    .view-all-link {
        color: #6366f1;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: var(--transition-smooth);
    }

    .view-all-link:hover {
        color: #8b5cf6;
    }

    /* ============================================
       SEARCH BOX
    ============================================ */
    .search-box {
        margin-bottom: 24px;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 14px 48px 14px 16px;
        background: var(--bg-main);
        border: 2px solid var(--border-color);
        border-radius: 12px;
        color: var(--text-primary);
        font-size: 15px;
        transition: var(--transition-smooth);
    }

    .search-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .search-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    /* ============================================
       PROPERTY CARDS
    ============================================ */
    .properties-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .property-card {
        display: flex;
        gap: 20px;
        padding: 20px;
        background: var(--bg-main);
        border: 2px solid var(--border-color);
        border-radius: 14px;
        transition: var(--transition-smooth);
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .property-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--gradient-primary);
        transform: scaleY(0);
        transition: var(--transition-smooth);
    }

    .property-card:hover {
        transform: translateX(8px);
        border-color: #6366f1;
        box-shadow: 0 12px 24px var(--shadow);
    }

    .property-card:hover::before {
        transform: scaleY(1);
    }

    .property-image-wrapper {
        position: relative;
        width: 140px;
        height: 140px;
        flex-shrink: 0;
    }

    .property-image {
        width: 100%;
        height: 100%;
        border-radius: 12px;
        object-fit: cover;
        transition: var(--transition-smooth);
    }

    .property-card:hover .property-image {
        transform: scale(1.05);
    }

    .property-views-badge {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(10px);
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .property-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .property-name {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .property-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .property-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .property-badge.type {
        background: rgba(99, 102, 241, 0.12);
        color: #6366f1;
    }

    .property-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .property-status::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    .property-status.available {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }

    .property-status.available::before {
        background: #22c55e;
    }

    .property-status.sold {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }

    .property-status.sold::before {
        background: #ef4444;
    }

    .property-status.rented {
        background: rgba(249, 115, 22, 0.12);
        color: #f97316;
    }

    .property-status.rented::before {
        background: #f97316;
    }

    .property-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .property-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 500;
    }

    .property-meta-item i {
        color: var(--text-muted);
        font-size: 14px;
    }

    .property-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid var(--border-color);
    }

    .property-price {
        font-size: 24px;
        font-weight: 800;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.5px;
    }

    .property-action {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        transition: var(--transition-smooth);
    }

    .property-action:hover {
        background: #6366f1;
        color: white;
    }

    /* ============================================
       QUICK ACTIONS
    ============================================ */
    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .quick-action {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px;
        background: var(--bg-main);
        border: 2px solid var(--border-color);
        border-radius: 14px;
        text-decoration: none;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
    }

    .quick-action::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--gradient-primary);
        opacity: 0;
        transition: var(--transition-smooth);
    }

    .quick-action:hover {
        border-color: transparent;
        transform: translateX(8px);
        box-shadow: 0 8px 16px var(--shadow);
    }

    .quick-action:hover::before {
        opacity: 0.05;
    }

    .quick-action:nth-child(1) .quick-action-icon { background: rgba(99, 102, 241, 0.12); }
    .quick-action:nth-child(1) .quick-action-icon i { color: #6366f1; }
    .quick-action:nth-child(2) .quick-action-icon { background: rgba(34, 197, 94, 0.12); }
    .quick-action:nth-child(2) .quick-action-icon i { color: #22c55e; }
    .quick-action:nth-child(3) .quick-action-icon { background: rgba(168, 85, 247, 0.12); }
    .quick-action:nth-child(3) .quick-action-icon i { color: #a855f7; }
    .quick-action:nth-child(4) .quick-action-icon { background: rgba(249, 115, 22, 0.12); }
    .quick-action:nth-child(4) .quick-action-icon i { color: #f97316; }

    .quick-action-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: var(--transition-smooth);
        position: relative;
        z-index: 1;
    }

    .quick-action:hover .quick-action-icon {
        transform: scale(1.1) rotate(-5deg);
    }

    .quick-action-content {
        flex: 1;
        position: relative;
        z-index: 1;
    }

    .quick-action-text {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 15px;
        display: block;
        margin-bottom: 2px;
    }

    .quick-action-desc {
        font-size: 12px;
        color: var(--text-muted);
    }

    .quick-action-arrow {
        position: relative;
        z-index: 1;
        color: var(--text-muted);
        transition: var(--transition-smooth);
    }

    .quick-action:hover .quick-action-arrow {
        color: #6366f1;
        transform: translateX(4px);
    }

    /* ============================================
       PAGINATION
    ============================================ */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 32px;
    }

    .page-link {
        min-width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        background: var(--bg-card);
        border: 2px solid var(--border-color);
        border-radius: 10px;
        color: var(--text-primary);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: var(--transition-smooth);
    }

    .page-link:hover:not(.disabled):not(.active) {
        border-color: #6366f1;
        color: #6366f1;
        transform: translateY(-2px);
    }

    .page-link.active {
        background: var(--gradient-primary);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .page-link.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* ============================================
       EMPTY STATE
    ============================================ */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        animation: fadeIn 0.5s ease;
    }

    .empty-state-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto 24px;
        background: rgba(99, 102, 241, 0.08);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .empty-state-icon i {
        font-size: 48px;
        color: #6366f1;
        opacity: 0.5;
    }

    .empty-state h3 {
        color: var(--text-primary);
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: var(--text-muted);
        font-size: 15px;
        margin-bottom: 24px;
    }

    .empty-state-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: var(--gradient-primary);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition-smooth);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .empty-state-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
    }

    /* ============================================
       ANIMATIONS
    ============================================ */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* ============================================
       RESPONSIVE DESIGN
    ============================================ */
    @media (max-width: 1200px) {
        .content-grid {
            grid-template-columns: 1fr 320px;
        }
    }

    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }

        .sidebar-content {
            order: -1;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-title {
            font-size: 28px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .stat-value {
            font-size: 28px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
        }

        .main-content,
        .sidebar-content {
            padding: 20px;
        }

        .property-card {
            flex-direction: column;
        }

        .property-image-wrapper {
            width: 100%;
            height: 200px;
        }

        .quick-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .property-meta {
            gap: 12px;
        }

        .property-footer {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .property-action {
            justify-content: center;
        }

        .pagination {
            flex-wrap: wrap;
        }
    }
</style>
@endsection

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Welcome back! Here's what's happening with your properties today.</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <p class="stat-label">Total Properties</p>
                <h2 class="stat-value">{{ $totalProperties }}</h2>
            </div>
            <div class="stat-icon" style="background: rgba(99,102,241,0.12);">
                <i class="fas fa-building" style="color: #6366f1; font-size: 22px;"></i>
            </div>
        </div>
        <div class="stat-growth {{ $propertyGrowth >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $propertyGrowth >= 0 ? 'up' : 'down' }}"></i>
            <span>{{ abs($propertyGrowth) }}% from last month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div>
                <p class="stat-label">Active Agents</p>
                <h2 class="stat-value">{{ $totalAgents }}</h2>
            </div>
            <div class="stat-icon" style="background: rgba(34,197,94,0.12);">
                <i class="fas fa-user-tie" style="color: #22c55e; font-size: 22px;"></i>
            </div>
        </div>
        <div class="stat-growth {{ $agentGrowth >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $agentGrowth >= 0 ? 'up' : 'down' }}"></i>
            <span>{{ abs($agentGrowth) }}% from last month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div>
                <p class="stat-label">Total Revenue</p>
                <h2 class="stat-value">${{ number_format($totalRevenue) }}</h2>
            </div>
            <div class="stat-icon" style="background: rgba(249,115,22,0.12);">
                <i class="fas fa-dollar-sign" style="color: #f97316; font-size: 22px;"></i>
            </div>
        </div>
        <div class="stat-growth {{ $revenueGrowth >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ $revenueGrowth >= 0 ? 'up' : 'down' }}"></i>
            <span>{{ abs($revenueGrowth) }}% from last month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div>
                <p class="stat-label">Appointments</p>
                <h2 class="stat-value">{{ $totalAppointments }}</h2>
            </div>
            <div class="stat-icon" style="background: rgba(168,85,247,0.12);">
                <i class="fas fa-calendar-alt" style="color: #a855f7; font-size: 22px;"></i>
            </div>
        </div>
        <div class="stat-growth neutral">
            <i class="fas fa-clock"></i>
            <span>{{ $pendingAppointments }} pending today</span>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="content-grid">
    <!-- Recent Properties -->
    <div class="main-content">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-building"></i>
                Recent Properties
            </h3>
            <a href="{{ route('office.property.upload') }}" class="view-all-link">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

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
                <i class="fas fa-search search-icon"></i>
            </form>
        </div>

        @if($recentProperties->count() > 0)
            <!-- Properties List -->
            <div class="properties-list">
                @foreach($recentProperties as $property)
                    @php
                        $images = is_array($property->images) ? $property->images : json_decode($property->images, true);
                        $firstImage = is_array($images) && count($images) > 0 ? $images[0] : 'https://via.placeholder.com/140x140/6366f1/ffffff?text=No+Image';

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
                            <div class="property-views-badge">
                                <i class="fas fa-eye"></i>
                                {{ number_format($property->views ?? 0) }}
                            </div>
                        </div>

                        <div class="property-details">
                            <h4 class="property-name">{{ $name }}</h4>

                            <div class="property-badges">
                                <span class="property-badge type">
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

            <!-- Pagination -->
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
            <!-- Empty State -->
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

    <!-- Quick Actions Sidebar -->
    <div class="sidebar-content">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h3>
        </div>

        <div class="quick-actions">
            <a href="{{ route('office.property.upload') }}" class="quick-action">
                <div class="quick-action-icon">
                    <i class="fas fa-plus" style="font-size: 18px;"></i>
                </div>
                <div class="quick-action-content">
                    <span class="quick-action-text">Add Property</span>
                    <span class="quick-action-desc">List a new property</span>
                </div>
                <i class="fas fa-arrow-right quick-action-arrow"></i>
            </a>

            <a href="{{ route('office.agents.add') }}" class="quick-action">
                <div class="quick-action-icon">
                    <i class="fas fa-user-plus" style="font-size: 18px;"></i>
                </div>
                <div class="quick-action-content">
                    <span class="quick-action-text">Add Agent</span>
                    <span class="quick-action-desc">Invite team member</span>
                </div>
                <i class="fas fa-arrow-right quick-action-arrow"></i>
            </a>

            <a href="{{ route('office.appointments') }}" class="quick-action">
                <div class="quick-action-icon">
                    <i class="fas fa-calendar" style="font-size: 18px;"></i>
                </div>
                <div class="quick-action-content">
                    <span class="quick-action-text">View Calendar</span>
                    <span class="quick-action-desc">Manage appointments</span>
                </div>
                <i class="fas fa-arrow-right quick-action-arrow"></i>
            </a>

            <a href="{{ route('office.projects') }}" class="quick-action">
                <div class="quick-action-icon">
                    <i class="fas fa-folder-plus" style="font-size: 18px;"></i>
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
