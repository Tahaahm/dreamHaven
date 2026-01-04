<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Office Dashboard - Dream Mulk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
        }

        /* Navbar styles same as agent profile page */
        nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Dashboard Content */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .dashboard-header {
            margin-bottom: 40px;
        }

        .dashboard-header h1 {
            font-size: 32px;
            color: #111827;
            margin-bottom: 8px;
        }

        .dashboard-header p {
            color: #6b7280;
            font-size: 16px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }

        /* Section Titles */
        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: #667eea;
        }

        /* Properties Grid */
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .property-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .property-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .property-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .property-content {
            padding: 20px;
        }

        .property-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }

        .property-price {
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
        }

        .property-details {
            display: flex;
            gap: 16px;
            color: #6b7280;
            font-size: 14px;
        }

        /* Appointments Table */
        .appointments-table {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9fafb;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        td {
            padding: 16px 12px;
            border-top: 1px solid #f3f4f6;
            color: #6b7280;
            font-size: 14px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-completed {
            background: #e0e7ff;
            color: #3730a3;
        }

        /* Top Agents */
        .agents-list {
            display: grid;
            gap: 16px;
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .agent-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            border-radius: 12px;
            background: #f9fafb;
            transition: all 0.3s;
        }

        .agent-item:hover {
            background: #f3f4f6;
        }

        .agent-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }

        .agent-info {
            flex: 1;
        }

        .agent-name {
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .agent-stats {
            color: #6b7280;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .nav-content {
                flex-direction: column;
                gap: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .properties-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <div class="nav-content">
            <a href="{{ route('newindex') }}" class="logo">
                <i class="fas fa-home"></i> Dream Mulk
            </a>
            <div class="nav-actions">
                <a href="{{ route('office.profile.page') }}" class="nav-btn">
                    <i class="fas fa-user"></i> Profile
                </a>
                <form action="{{ route('office.logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome back, {{ $office->company_name }}! 👋</h1>
            <p>Here's what's happening with your real estate office today.</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value">{{ $stats['total_agents'] }}</div>
                <div class="stat-label">Total Agents</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-value">{{ $stats['total_properties'] }}</div>
                <div class="stat-label">Total Properties</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value">{{ $stats['active_listings'] }}</div>
                <div class="stat-label">Active Listings</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-value">{{ $stats['pending_appointments'] }}</div>
                <div class="stat-label">Pending Appointments</div>
            </div>
        </div>

        <!-- Recent Properties -->
        <h2 class="section-title">
            <i class="fas fa-building"></i> Recent Properties
        </h2>
        <div class="properties-grid">
            @forelse($recentProperties as $property)
                <div class="property-card">
                    @if($property->images && count(json_decode($property->images, true)) > 0)
                        <img src="{{ asset('storage/' . json_decode($property->images, true)[0]) }}" alt="{{ $property->name }}" class="property-image">
                    @else
                        <img src="https://via.placeholder.com/400x300?text=No+Image" alt="No image" class="property-image">
                    @endif
                    <div class="property-content">
                        <div class="property-title">{{ $property->name }}</div>
                        <div class="property-price">${{ number_format($property->price) }}</div>
                        <div class="property-details">
                            <span><i class="fas fa-bed"></i> {{ $property->rooms ?? 'N/A' }}</span>
                            <span><i class="fas fa-ruler-combined"></i> {{ $property->area ?? 'N/A' }} m²</span>
                        </div>
                    </div>
                </div>
            @empty
                <p style="grid-column: 1/-1; text-align: center; color: #6b7280;">No properties yet</p>
            @endforelse
        </div>

        <!-- Recent Appointments -->
        <h2 class="section-title">
            <i class="fas fa-calendar"></i> Recent Appointments
        </h2>
        <div class="appointments-table">
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Agent</th>
                        <th>Property</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAppointments as $appointment)
                        <tr>
                            <td>{{ $appointment->client_name }}</td>
                            <td>{{ $appointment->agent->full_name ?? 'N/A' }}</td>
                            <td>{{ $appointment->property->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->appointment_date }} {{ $appointment->appointment_time }}</td>
                            <td>
                                <span class="status-badge status-{{ $appointment->status }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;">No appointments yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Top Agents -->
        <h2 class="section-title" style="margin-top: 40px;">
            <i class="fas fa-star"></i> Top Performing Agents
        </h2>
        <div class="agents-list">
            @forelse($topAgents as $agent)
                <div class="agent-item">
                    <div class="agent-avatar">
                        {{ strtoupper(substr($agent->full_name, 0, 1)) }}
                    </div>
                    <div class="agent-info">
                        <div class="agent-name">{{ $agent->full_name }}</div>
                        <div class="agent-stats">{{ $agent->properties_count }} properties</div>
                    </div>
                </div>
            @empty
                <p style="text-align: center; color: #6b7280;">No agents yet</p>
            @endforelse
        </div>
    </div>
</body>
</html>
