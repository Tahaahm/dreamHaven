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

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }

    .card-box {
        background: white;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        border: 1px solid #e2e8f0;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .card-title {
        font-size: 16px;
        font-weight: 800;
        color: #1a202c;
    }

    .view-all-link {
        color: #303b97;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s;
    }

    .view-all-link:hover {
        gap: 10px;
    }

    /* Activity List */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .activity-item {
        display: flex;
        gap: 16px;
        padding: 14px;
        border-radius: 12px;
        transition: all 0.3s;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .activity-item:hover {
        background: white;
        border-color: #303b97;
        transform: translateX(4px);
    }

    .activity-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: #303b97;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(48,59,151,0.3);
    }

    .activity-content {
        flex: 1;
    }

    .activity-text {
        font-size: 14px;
        color: #1a202c;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .activity-time {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 30px;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: #303b97;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        color: white;
        box-shadow: 0 8px 24px rgba(48,59,151,0.3);
    }

    .empty-title {
        font-size: 18px;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .empty-text {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 20px;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #303b97;
        color: white;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(48,59,151,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(48,59,151,0.4);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .content-grid {
            grid-template-columns: 1fr;
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
                    <div class="stat-value">{{ $stats['total_properties'] ?? 0 }}</div>
                    <div class="stat-label">Total Properties</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-home"></i>
                </div>
            </div>
            <span class="stat-trend">
                <i class="fas fa-arrow-up"></i> +{{ $stats['new_this_month'] ?? 0 }} this month
            </span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $stats['active_properties'] ?? 0 }}</div>
                    <div class="stat-label">Active Listings</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <span class="stat-trend">
                <i class="fas fa-chart-line"></i> {{ $stats['active_percentage'] ?? 0 }}% of total
            </span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $stats['total_views'] ?? 0 }}</div>
                    <div class="stat-label">Total Views</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
            <span class="stat-trend">
                <i class="fas fa-arrow-up"></i> +{{ $stats['views_this_week'] ?? 0 }} this week
            </span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">{{ $stats['properties_sold'] ?? 0 }}</div>
                    <div class="stat-label">Properties Sold</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
            <span class="stat-trend">
                <i class="fas fa-arrow-up"></i> +{{ $stats['sold_this_year'] ?? 0 }} this year
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

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Recent Activity -->
        <div class="card-box">
            <div class="card-header">
                <h3 class="card-title">Recent Activity</h3>
                <a href="{{ route('agent.properties') }}" class="view-all-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            @if($recentProperties && $recentProperties->count() > 0)
                <div class="activity-list">
                    @foreach($recentProperties->take(5) as $property)
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">{{ $property->title['en'] ?? 'Property' }}</div>
                            <div class="activity-time">
                                <i class="fas fa-clock"></i> Listed {{ $property->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="empty-title">No Properties Yet</div>
                    <div class="empty-text">Start adding properties to see your activity here</div>
                    <a href="{{ route('agent.property.add') }}" class="btn-primary">
                        <i class="fas fa-plus"></i> Add Your First Property
                    </a>
                </div>
            @endif
        </div>

        <!-- This Month Summary -->
        <div class="card-box">
            <div class="card-header">
                <h3 class="card-title">This Month Summary</h3>
            </div>

            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">{{ $stats['new_this_month'] ?? 0 }} New Listings</div>
                        <div class="activity-time">Added this month</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">{{ $stats['views_this_week'] ?? 0 }} Views</div>
                        <div class="activity-time">This week</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">{{ $stats['sold_this_year'] ?? 0 }} Sold</div>
                        <div class="activity-time">This year</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
