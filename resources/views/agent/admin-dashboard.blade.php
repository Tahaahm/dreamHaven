<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
body {
    font-family: Arial, Helvetica, sans-serif;
    margin: 0;
    padding: 0;
    background: #F5F7F8;
    width: 100%;
    min-height: 100vh;
}

.unique-header {
    position: fixed;
    height: 80px;
    width: 100%;
    z-index: 100;
    padding: 0 20px;
    background: #303b97;
}

.allin {
    padding-top: 100px;
}

.content {
    border-radius: 15px;
    margin-left: 270px;
    padding: 20px;
    border: none;
}

.header {
    background: #f9fcff;
    padding: 20px;
    box-shadow: 0px 2px 15px rgba(133, 133, 133, 0.1);
    margin-bottom: 30px;
    border-radius: 15px;
    border: none;
}

.header h2 {
    margin: 0;
    color: #333;
    font-weight: 600;
}

.section-header {
    margin: 30px 0 20px 0;
    padding: 15px 0;
    border-bottom: 2px solid #303b97;
}

.section-header h4 {
    color: #303b97;
    font-weight: 600;
    margin: 0;
}

.stat-card {
    background: #f9fcff;
    border-radius: 15px;
    margin-bottom: 20px;
    border: none;
    box-shadow: 0px 2px 15px rgba(133, 133, 133, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 5px 25px rgba(133, 133, 133, 0.2);
}

.stat-card .card-body {
    padding: 25px;
}

.stat-card .card-icon {
    font-size: 2.5rem;
    margin-bottom: 15px;
    color: #303b97;
}

.stat-card .card-title {
    font-size: 14px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.stat-card .card-value {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.stat-card .card-subtitle {
    font-size: 12px;
    color: #999;
}

.chart-card {
    background: #f9fcff;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    border: none;
    box-shadow: 0px 2px 15px rgba(133, 133, 133, 0.1);
}

.chart-card .chart-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

.mini-chart-card {
    background: #f9fcff;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    margin-bottom: 20px;
    border: none;
    box-shadow: 0px 2px 10px rgba(133, 133, 133, 0.1);
    height: 100%;
}

.mini-chart-card h6 {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #666;
    text-transform: uppercase;
}

.mini-chart-card .value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #12be07;
    margin-bottom: 5px;
}

.mini-chart-card .change {
    font-size: 12px;
    color: #999;
    margin-bottom: 15px;
}

.mini-chart-card canvas {
    height: 100px !important;
}

.top-items-card {
    background: #f9fcff;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    border: none;
    box-shadow: 0px 2px 15px rgba(133, 133, 133, 0.1);
}

.top-items-card .list-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

.top-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #fff;
    border-radius: 10px;
    margin-bottom: 10px;
    box-shadow: 0px 1px 5px rgba(0, 0, 0, 0.05);
}

.top-item .rank {
    width: 30px;
    height: 30px;
    background: #303b97;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.top-item .item-info {
    flex: 1;
    margin-left: 15px;
}

.top-item .item-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 3px;
}

.top-item .item-detail {
    font-size: 12px;
    color: #999;
}

.top-item .item-value {
    font-size: 18px;
    font-weight: bold;
    color: #12be07;
}

.percentage-bar {
    width: 100%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 10px;
}

.percentage-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #303b97, #5a67d8);
    border-radius: 4px;
    transition: width 0.3s ease;
}

@media (max-width: 768px) {
    .content {
        margin-left: 0;
    }
}
    </style>
</head>
<body>

@include('layouts.sidebar')

<div class="allin">
    <div class="content">
        <div class="header">
            <h2><i class="fas fa-chart-line"></i> Analytics Dashboard</h2>
            <small>Last updated: {{ date('F d, Y') }}</small>
        </div>

        <div class="container-fluid">
            
            <!-- OVERVIEW SECTION -->
            <div class="section-header">
                <h4><i class="fas fa-tachometer-alt"></i> Overview</h4>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon"><i class="fas fa-users"></i></div>
                            <h6 class="card-title">Total Users</h6>
                            <p class="card-value">{{ number_format($analytics->total_users ?? $totals['users'] ?? 0) }}</p>
                            <p class="card-subtitle">Registered Users</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon"><i class="fas fa-user-tie"></i></div>
                            <h6 class="card-title">Total Agents</h6>
                            <p class="card-value">{{ number_format($analytics->total_agents ?? $totals['agents'] ?? 0) }}</p>
                            <p class="card-subtitle">Active: {{ number_format($analytics->active_agents ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon"><i class="fas fa-building"></i></div>
                            <h6 class="card-title">Total Offices</h6>
                            <p class="card-value">{{ number_format($analytics->total_offices ?? $totals['offices'] ?? 0) }}</p>
                            <p class="card-subtitle">With Listings: {{ number_format($analytics->offices_with_listings ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon"><i class="fas fa-home"></i></div>
                            <h6 class="card-title">Total Properties</h6>
                            <p class="card-value">{{ number_format($analytics->total_properties ?? $totals['properties'] ?? 0) }}</p>
                            <p class="card-subtitle">All Listings</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROPERTY INVENTORY SECTION -->
            <div class="section-header">
                <h4><i class="fas fa-warehouse"></i> Property Inventory</h4>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #28a745;"><i class="fas fa-store"></i></div>
                            <h6 class="card-title">For Sale</h6>
                            <p class="card-value">{{ number_format($analytics->properties_for_sale ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #17a2b8;"><i class="fas fa-key"></i></div>
                            <h6 class="card-title">For Rent</h6>
                            <p class="card-value">{{ number_format($analytics->properties_for_rent ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #ffc107;"><i class="fas fa-handshake"></i></div>
                            <h6 class="card-title">Properties Sold</h6>
                            <p class="card-value">{{ number_format($analytics->properties_sold ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #6f42c1;"><i class="fas fa-file-contract"></i></div>
                            <h6 class="card-title">Properties Rented</h6>
                            <p class="card-value">{{ number_format($analytics->properties_rented ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROPERTY PERFORMANCE SECTION -->
            <div class="section-header">
                <h4><i class="fas fa-chart-bar"></i> Property Performance</h4>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="chart-card">
                        <h5 class="chart-title"><i class="fas fa-eye"></i> Views Analytics</h5>
                        <canvas id="views-chart"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="stat-card">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Views</h6>
                                    <p class="card-value">{{ number_format($analytics->total_views ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="stat-card">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Unique Views</h6>
                                    <p class="card-value">{{ number_format($analytics->unique_views ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="stat-card">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Returning Views</h6>
                                    <p class="card-value">{{ number_format($analytics->returning_views ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #ff6384;"><i class="fas fa-clock"></i></div>
                            <h6 class="card-title">Avg Time on Listing</h6>
                            <p class="card-value">{{ $analytics && $analytics->average_time_on_listing ? gmdate("i:s", $analytics->average_time_on_listing) : '0:00' }}</p>
                            <p class="card-subtitle">Minutes:Seconds</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #36a2eb;"><i class="fas fa-percent"></i></div>
                            <h6 class="card-title">Bounce Rate</h6>
                            <p class="card-value">{{ $analytics ? number_format($analytics->bounce_rate, 1) : '0.0' }}%</p>
                            <div class="percentage-bar">
                                <div class="percentage-bar-fill" style="width: {{ $analytics ? $analytics->bounce_rate : 0 }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #ff9f40;"><i class="fas fa-heart"></i></div>
                            <h6 class="card-title">Total Favorites</h6>
                            <p class="card-value">{{ number_format($analytics->favorites_count ?? 0) }}</p>
                            <p class="card-subtitle">User Favorites</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AGENT PERFORMANCE SECTION -->
            <div class="section-header">
                <h4><i class="fas fa-user-check"></i> Agent Performance</h4>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #28a745;"><i class="fas fa-user-check"></i></div>
                            <h6 class="card-title">Active Agents</h6>
                            <p class="card-value">{{ number_format($analytics->active_agents ?? 0) }}</p>
                            <p class="card-subtitle">Currently Active</p>
                            <div class="percentage-bar">
                                @php
                                    $totalAgents = $analytics->total_agents ?? $totals['agents'] ?? 1;
                                    $activeAgents = $analytics->active_agents ?? 0;
                                    $percentage = $totalAgents > 0 ? ($activeAgents / $totalAgents) * 100 : 0;
                                @endphp
                                <div class="percentage-bar-fill" style="width: {{ $percentage }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #17a2b8;"><i class="fas fa-clipboard-list"></i></div>
                            <h6 class="card-title">Agents with Properties</h6>
                            <p class="card-value">{{ number_format($analytics->agents_with_properties ?? 0) }}</p>
                            <p class="card-subtitle">Have Active Listings</p>
                            <div class="percentage-bar">
                                @php
                                    $totalAgents2 = $analytics->total_agents ?? $totals['agents'] ?? 1;
                                    $agentsWithProps = $analytics->agents_with_properties ?? 0;
                                    $percentage2 = $totalAgents2 > 0 ? ($agentsWithProps / $totalAgents2) * 100 : 0;
                                @endphp
                                <div class="percentage-bar-fill" style="width: {{ $percentage2 }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BANNER ANALYTICS SECTION -->
            <div class="section-header">
                <h4><i class="fas fa-ad"></i> Banner Analytics</h4>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #fd7e14;"><i class="fas fa-flag"></i></div>
                            <h6 class="card-title">Active Banners</h6>
                            <p class="card-value">{{ number_format($analytics->active_banners ?? $totals['activeBanners'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #20c997;"><i class="fas fa-mouse-pointer"></i></div>
                            <h6 class="card-title">Banner Clicks</h6>
                            <p class="card-value">{{ number_format($analytics->banners_clicked ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="card-body text-center">
                            <div class="card-icon" style="color: #e83e8c;"><i class="fas fa-eye"></i></div>
                            <h6 class="card-title">Banner Impressions</h6>
                            <p class="card-value">{{ number_format($analytics->banners_impressions ?? 0) }}</p>
                            @php
                                $clicks = $analytics->banners_clicked ?? 0;
                                $impressions = $analytics->banners_impressions ?? 1;
                                $ctr = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
                            @endphp
                            <p class="card-subtitle">CTR: {{ number_format($ctr, 2) }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOP PERFORMERS SECTION -->
            <div class="section-header">
                <h4><i class="fas fa-trophy"></i> Top Performers</h4>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="top-items-card">
                        <h5 class="list-title"><i class="fas fa-home"></i> Top Properties</h5>
                        @php
                            $topProperties = $analytics && $analytics->top_properties ? $analytics->top_properties : [];
                        @endphp
                        @if(count($topProperties) > 0)
                            @foreach($topProperties as $index => $property)
                            <div class="top-item">
                                <div class="rank">{{ $index + 1 }}</div>
                                <div class="item-info">
                                    <div class="item-name">{{ $property['name'] ?? 'Property ' . ($index + 1) }}</div>
                                    <div class="item-detail">{{ $property['metric'] ?? 'Total Views' }}</div>
                                </div>
                                <div class="item-value">{{ number_format($property['value'] ?? $property['views'] ?? 0) }}</div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-center text-muted">No data available</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="top-items-card">
                        <h5 class="list-title"><i class="fas fa-user-tie"></i> Top Agents</h5>
                        @php
                            $topAgents = $analytics && $analytics->top_agents ? $analytics->top_agents : [];
                        @endphp
                        @if(count($topAgents) > 0)
                            @foreach($topAgents as $index => $agent)
                            <div class="top-item">
                                <div class="rank">{{ $index + 1 }}</div>
                                <div class="item-info">
                                    <div class="item-name">{{ $agent['name'] ?? 'Agent ' . ($index + 1) }}</div>
                                    <div class="item-detail">{{ $agent['metric'] ?? 'Properties Sold' }}</div>
                                </div>
                                <div class="item-value">{{ number_format($agent['value'] ?? $agent['sales'] ?? 0) }}</div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-center text-muted">No data available</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="top-items-card">
                        <h5 class="list-title"><i class="fas fa-building"></i> Top Offices</h5>
                        @php
                            $topOffices = $analytics && $analytics->top_offices ? $analytics->top_offices : [];
                        @endphp
                        @if(count($topOffices) > 0)
                            @foreach($topOffices as $index => $office)
                            <div class="top-item">
                                <div class="rank">{{ $index + 1 }}</div>
                                <div class="item-info">
                                    <div class="item-name">{{ $office['name'] ?? 'Office ' . ($index + 1) }}</div>
                                    <div class="item-detail">{{ $office['metric'] ?? 'Active Listings' }}</div>
                                </div>
                                <div class="item-value">{{ number_format($office['value'] ?? $office['listings'] ?? 0) }}</div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-center text-muted">No data available</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="top-items-card">
                        <h5 class="list-title"><i class="fas fa-ad"></i> Top Banners</h5>
                        @php
                            $topBanners = $analytics && $analytics->top_banners ? $analytics->top_banners : [];
                        @endphp
                        @if(count($topBanners) > 0)
                            @foreach($topBanners as $index => $banner)
                            <div class="top-item">
                                <div class="rank">{{ $index + 1 }}</div>
                                <div class="item-info">
                                    <div class="item-name">{{ $banner['name'] ?? 'Banner ' . ($index + 1) }}</div>
                                    <div class="item-detail">{{ $banner['metric'] ?? 'Total Clicks' }}</div>
                                </div>
                                <div class="item-value">{{ number_format($banner['value'] ?? $banner['clicks'] ?? 0) }}</div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-center text-muted">No data available</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
@php
    // Fetch last 12 months of analytics data for the chart
    $monthlyData = \App\Models\AdminAnalytics::orderBy('date', 'asc')
        ->take(12)
        ->get();
    
    $chartLabels = [];
    $totalViewsData = [];
    $uniqueViewsData = [];
    $returningViewsData = [];
    
    foreach($monthlyData as $data) {
        $chartLabels[] = $data->date ? date('M', strtotime($data->date)) : 'N/A';
        $totalViewsData[] = $data->total_views ?? 0;
        $uniqueViewsData[] = $data->unique_views ?? 0;
        $returningViewsData[] = $data->returning_views ?? 0;
    }
    
    // If no data, show empty chart
    if(count($chartLabels) == 0) {
        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $totalViewsData = array_fill(0, 12, 0);
        $uniqueViewsData = array_fill(0, 12, 0);
        $returningViewsData = array_fill(0, 12, 0);
    }
@endphp

// Views Analytics Chart
const viewsCtx = document.getElementById('views-chart').getContext('2d');
new Chart(viewsCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [
            {
                label: 'Total Views',
                data: {!! json_encode($totalViewsData) !!},
                borderColor: '#303b97',
                backgroundColor: 'rgba(48, 59, 151, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Unique Views',
                data: {!! json_encode($uniqueViewsData) !!},
                borderColor: '#12be07',
                backgroundColor: 'rgba(18, 190, 7, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Returning Views',
                data: {!! json_encode($returningViewsData) !!},
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    display: true,
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>

</body>
</html>