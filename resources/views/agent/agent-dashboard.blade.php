@extends('layouts.agent-layout')

@section('title', 'Dashboard - Dream Mulk')

@section('styles')
<style>
    body { background: #f5f7fa; }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Welcome Section */
    .welcome-section {
        background: linear-gradient(135deg, #303b97 0%, #1e2875 100%);
        border-radius: 16px;
        padding: 28px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(48,59,151,0.2);
        position: relative;
        overflow: hidden;
    }

    .welcome-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .welcome-content {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
    }

    .welcome-text h1 {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .welcome-text p {
        font-size: 14px;
        opacity: 0.95;
    }

    .welcome-actions {
        display: flex;
        gap: 12px;
    }

    .btn-add-property {
        background: white;
        color: #303b97;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-add-property:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255,255,255,0.3);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s;
        border: 1px solid #e2e8f0;
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
        background: #303b97;
        transform: scaleX(0);
        transition: transform 0.3s;
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(48,59,151,0.1);
        border-color: #303b97;
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 800;
        color: #1a202c;
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: #303b97;
        color: white;
        box-shadow: 0 4px 12px rgba(48,59,151,0.3);
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        color: #059669;
        background: #d1fae5;
        padding: 6px 10px;
        border-radius: 8px;
    }

    /* Section Header */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 800;
        color: #1a202c;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 24px;
        background: #303b97;
        border-radius: 4px;
    }

    /* Quick Actions Grid */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }

    .action-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(48,59,151,0.1);
        border-color: #303b97;
    }

    .action-icon-box {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: #303b97;
        color: white;
        box-shadow: 0 4px 12px rgba(48,59,151,0.3);
        transition: all 0.3s;
    }

    .action-card:hover .action-icon-box {
        transform: scale(1.1);
    }

    .action-label {
        font-size: 14px;
        font-weight: 700;
        color: #1a202c;
    }

    /* Property Cards Grid */
    .properties-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .property-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .property-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(48,59,151,0.15);
        border-color: #303b97;
    }

    .property-image {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: #f1f5f9;
    }

    .property-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .property-card:hover .property-image img {
        transform: scale(1.05);
    }

    .property-status {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .status-available { color: #059669; background: #d1fae5; }
    .status-sold { color: #dc2626; background: #fee2e2; }
    .status-rented { color: #2563eb; background: #dbeafe; }
    .status-pending { color: #d97706; background: #fed7aa; }

    .property-content {
        padding: 18px;
    }

    .property-price {
        font-size: 22px;
        font-weight: 800;
        color: #303b97;
        margin-bottom: 10px;
    }

    .property-price-usd {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        margin-left: 6px;
    }

    .property-title {
        font-size: 15px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
        min-height: 42px;
    }

    .property-location {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .property-features {
        display: flex;
        gap: 16px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
    }

    .property-feature {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
    }

    .property-feature i {
        color: #303b97;
        font-size: 14px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 30px;
        background: white;
        border-radius: 16px;
        border: 2px dashed #e2e8f0;
    }

    .empty-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 24px;
        background: linear-gradient(135deg, #303b97, #1e2875);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: white;
        box-shadow: 0 8px 24px rgba(48,59,151,0.3);
    }

    .empty-title {
        font-size: 24px;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 10px;
    }

    .empty-text {
        font-size: 15px;
        color: #64748b;
        margin-bottom: 28px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #303b97, #1e2875);
        color: white;
        padding: 14px 32px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        font-size: 15px;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(48,59,151,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(48,59,151,0.4);
    }

    .view-all-link {
        color: #303b97;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .view-all-link:hover {
        gap: 10px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .properties-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 16px;
        }

        .welcome-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .quick-actions-grid {
            grid-template-columns: 1fr;
        }

        .properties-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-content">
            <div class="welcome-text">
                <h1>Welcome back, {{ auth('agent')->user()->agent_name }}! 👋</h1>
                <p>Track your real estate performance and manage your listings</p>
            </div>
            <div class="welcome-actions">
                <a href="{{ route('agent.property.add') }}" class="btn-add-property">
                    <i class="fas fa-plus-circle"></i> Add Property
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $stats['total_properties'] }}</div>
                    <div class="stat-label">Total Properties</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-home"></i>
                </div>
            </div>
            <span class="stat-trend">
                <i class="fas fa-arrow-up"></i> +{{ $stats['new_this_month'] }} this month
            </span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $stats['active_properties'] }}</div>
                    <div class="stat-label">Active Listings</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <span class="stat-trend">
                <i class="fas fa-chart-line"></i> {{ $stats['active_percentage'] }}% of total
            </span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ number_format($stats['total_views']) }}</div>
                    <div class="stat-label">Total Views</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
            <span class="stat-trend">
                <i class="fas fa-arrow-up"></i> +{{ $stats['views_this_week'] }} this week
            </span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $stats['properties_sold'] }}</div>
                    <div class="stat-label">Properties Sold</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
            <span class="stat-trend">
                <i class="fas fa-arrow-up"></i> +{{ $stats['sold_this_year'] }} this year
            </span>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="section-header">
        <h2 class="section-title">Quick Actions</h2>
    </div>
    <div class="quick-actions-grid">
        <a href="{{ route('agent.property.add') }}" class="action-card">
            <div class="action-icon-box">
                <i class="fas fa-plus-circle"></i>
            </div>
            <div class="action-label">Add Property</div>
        </a>

        <a href="{{ route('agent.properties') }}" class="action-card">
            <div class="action-icon-box">
                <i class="fas fa-list-alt"></i>
            </div>
            <div class="action-label">My Listings</div>
        </a>

        <a href="{{ route('agent.subscriptions') }}" class="action-card">
            <div class="action-icon-box">
                <i class="fas fa-crown"></i>
            </div>
            <div class="action-label">Subscriptions</div>
        </a>

        <a href="{{ route('agent.profile', auth('agent')->user()->id) }}" class="action-card">
            <div class="action-icon-box">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="action-label">My Profile</div>
        </a>
    </div>

    <!-- Recent Properties -->
    <div class="section-header">
        <h2 class="section-title">Recent Properties</h2>
        @if($recentProperties && $recentProperties->count() > 0)
            <a href="{{ route('agent.properties') }}" class="view-all-link">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        @endif
    </div>

    @if($recentProperties && $recentProperties->count() > 0)
        <div class="properties-grid">
            @foreach($recentProperties as $property)
            <a href="{{ route('agent.property.edit', $property->id) }}" class="property-card">
                <div class="property-image">
                    @if(isset($property->images) && is_array($property->images) && count($property->images) > 0)
                        <img src="{{ $property->images[0] }}" alt="{{ $property->name['en'] ?? 'Property' }}">
                    @else
                        <img src="https://via.placeholder.com/400x300/303b97/ffffff?text=No+Image" alt="No Image">
                    @endif

                    <span class="property-status status-{{ $property->status }}">
                        {{ ucfirst($property->status) }}
                    </span>
                </div>

                <div class="property-content">
                    <div class="property-price">
                        ${{ number_format($property->price['usd'] ?? 0) }}
                        <span class="property-price-usd">${{ number_format($property->price['usd'] ?? 0) }}</span>
                    </div>

                    <div class="property-title">
                        {{ $property->name['en'] ?? 'Untitled Property' }}
                    </div>

                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $property->address_details['city']['en'] ?? 'Unknown' }}, {{ $property->address_details['district']['en'] ?? '' }}
                    </div>

                    <div class="property-features">
                        <div class="property-feature">
                            <i class="fas fa-bed"></i>
                            {{ $property->rooms['bedroom']['count'] ?? 0 }} Beds
                        </div>
                        <div class="property-feature">
                            <i class="fas fa-bath"></i>
                            {{ $property->rooms['bathroom']['count'] ?? 0 }} Baths
                        </div>
                        <div class="property-feature">
                            <i class="fas fa-ruler-combined"></i>
                            {{ number_format($property->area ?? 0) }} m²
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-home"></i>
            </div>
            <div class="empty-title">No Properties Yet</div>
            <div class="empty-text">Start building your real estate portfolio by adding your first property</div>
            <a href="{{ route('agent.property.add') }}" class="btn-primary">
                <i class="fas fa-plus"></i> Add Your First Property
            </a>
        </div>
    @endif
</div>
@endsection
