@extends('layouts.agent-layout')

@section('title', 'My Profile - Dream Mulk')

@section('styles')
<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .profile-header {
        background: linear-gradient(135deg, #303b97, #1e2875);
        border-radius: 16px;
        padding: 40px;
        color: white;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .profile-header-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 32px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: 700;
        color: #303b97;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }

    .profile-info h1 {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .profile-meta {
        display: flex;
        gap: 24px;
        font-size: 14px;
        opacity: 0.95;
    }

    .profile-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .profile-card {
        background: white;
        border-radius: 14px;
        padding: 32px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .card-title {
        font-size: 20px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #303b97;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-title i {
        color: #303b97;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .info-item {
        padding: 16px;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }

    .info-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .info-value {
        font-size: 16px;
        color: #1a202c;
        font-weight: 600;
    }

    .stat-card {
        padding: 20px;
        background: linear-gradient(135deg, rgba(48,59,151,0.05), rgba(48,59,151,0.02));
        border-radius: 12px;
        border: 1px solid rgba(48,59,151,0.1);
        margin-bottom: 16px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 800;
        color: #303b97;
        margin-bottom: 6px;
    }

    .stat-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: #303b97;
        color: white;
        box-shadow: 0 4px 12px rgba(48,59,151,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(48,59,151,0.4);
    }

    .btn-outline {
        background: white;
        color: #303b97;
        border: 2px solid #303b97;
    }

    .btn-outline:hover {
        background: #303b97;
        color: white;
    }

    .subscription-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        margin-top: 12px;
    }

    @media (max-width: 1024px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .profile-header-content {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .profile-meta {
            flex-direction: column;
            gap: 12px;
        }
    }
</style>
@endsection

@section('content')
<div class="profile-container">
    <div class="profile-header">
        <div class="profile-header-content">
            <div class="profile-avatar">
                {{ strtoupper(substr($agent->agent_name, 0, 1)) }}
            </div>
            <div class="profile-info">
                <h1>{{ $agent->agent_name }}</h1>
                <div class="profile-meta">
                    <div class="profile-meta-item">
                        <i class="fas fa-envelope"></i>
                        <span>{{ $agent->primary_email }}</span>
                    </div>
                    <div class="profile-meta-item">
                        <i class="fas fa-phone"></i>
                        <span>{{ $agent->primary_phone }}</span>
                    </div>
                    <div class="profile-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ $agent->city }}</span>
                    </div>
                </div>
                @if($agent->Subscription)
                <div class="subscription-badge">
                    <i class="fas fa-crown"></i>
                    <span>{{ $agent->Subscription->currentPlan->name ?? 'Free Plan' }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="profile-grid">
        <div class="profile-card">
            <h2 class="card-title">
                <i class="fas fa-user"></i>
                Personal Information
            </h2>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-value">{{ $agent->agent_name }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $agent->primary_email }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">{{ $agent->primary_phone }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">WhatsApp</div>
                    <div class="info-value">{{ $agent->whatsapp_number ?? 'Not Set' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">City</div>
                    <div class="info-value">{{ $agent->city }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">District</div>
                    <div class="info-value">{{ $agent->district ?? 'Not Set' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">License Number</div>
                    <div class="info-value">{{ $agent->license_number ?? 'Not Set' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Years Experience</div>
                    <div class="info-value">{{ $agent->years_experience ?? 0 }} Years</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Properties Sold</div>
                    <div class="info-value">{{ $agent->properties_sold ?? 0 }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        @if($agent->is_verified)
                            <span style="color: #22c55e;">✓ Verified</span>
                        @else
                            <span style="color: #f59e0b;">⚠ Pending</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($agent->agent_bio)
            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <div class="info-label">About Me</div>
                <p style="color: #64748b; margin-top: 8px; line-height: 1.6;">{{ $agent->agent_bio }}</p>
            </div>
            @endif

            <div style="margin-top: 24px; display: flex; gap: 12px;">
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
                <a href="#" class="btn btn-outline">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>
        </div>

        <div>
            <div class="profile-card" style="margin-bottom: 24px;">
    <h2 class="card-title">
        <i class="fas fa-chart-bar"></i>
        Statistics
    </h2>

    <div class="stat-card">
        <div class="stat-value">
            @php
                try {
                    echo $agent->properties()->count();
                } catch (\Exception $e) {
                    echo '0';
                }
            @endphp
        </div>
        <div class="stat-label">Total Properties</div>
    </div>

    <div class="stat-card">
        <div class="stat-value">
            @php
                try {
                    echo $agent->properties()->where('status', 'available')->count();
                } catch (\Exception $e) {
                    echo '0';
                }
            @endphp
        </div>
        <div class="stat-label">Active Listings</div>
    </div>

    <div class="stat-card">
        <div class="stat-value">{{ $agent->properties_sold ?? 0 }}</div>
        <div class="stat-label">Properties Sold</div>
    </div>

    <div class="stat-card">
        <div class="stat-value">{{ number_format($agent->overall_rating ?? 0, 1) }}</div>
        <div class="stat-label">Overall Rating</div>
    </div>
</div>

            @if($agent->company)
            <div class="profile-card">
                <h2 class="card-title">
                    <i class="fas fa-building"></i>
                    Company
                </h2>

                <div class="info-item">
                    <div class="info-label">Company Name</div>
                    <div class="info-value">{{ $agent->company->office_name ?? 'Not Set' }}</div>
                </div>

                <div class="info-item" style="margin-top: 12px;">
                    <div class="info-label">Employment Status</div>
                    <div class="info-value">{{ ucfirst($agent->employment_status ?? 'Independent') }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
