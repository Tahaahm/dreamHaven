@extends('layouts.agent-layout')

@section('title', 'Banner Ads - Dream Mulk')

@section('styles')
<style>
    /* Styling Updates - Modern & Clean */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .stat-value { font-size: 32px; font-weight: 700; color: #303b97; margin-bottom: 8px; }
    .stat-label { font-size: 14px; color: #64748b; font-weight: 600; }

    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title { font-size: 28px; font-weight: 800; color: #1a202c; }
    .btn-primary { background: linear-gradient(135deg, #303b97, #1e2875); color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; transition: all 0.3s; box-shadow: 0 4px 12px rgba(48,59,151,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(48,59,151,0.4); }

    .filters { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; gap: 16px; flex-wrap: wrap; }
    .filter-group { flex: 1; min-width: 200px; }
    .filter-label { font-size: 13px; color: #374151; margin-bottom: 8px; font-weight: 700; }
    .filter-select { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; background: white; color: #1a202c; font-weight: 600; }
    .filter-select:focus { border-color: #303b97; outline: none; }

    .banners-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px; }
    .banner-card { background: white; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; transition: all 0.3s; }
    .banner-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }

    .banner-image-container { position: relative; width: 100%; height: 180px; background: #f3f4f6; }
    .banner-image { width: 100%; height: 100%; object-fit: cover; }

    .banner-content { padding: 20px; }
    .banner-title { font-size: 18px; font-weight: 700; color: #1a202c; margin-bottom: 12px; line-height: 1.4; }

    .banner-meta { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
    .banner-badge { padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-active { background: #dcfce7; color: #16a34a; }
    .badge-draft { background: #fef3c7; color: #d97706; }
    .badge-paused { background: #e0e7ff; color: #6366f1; }
    .badge-rejected { background: #fee2e2; color: #dc2626; }

    .banner-stats { display: flex; gap: 12px; margin: 16px 0; padding: 16px; background: #f8fafc; border-radius: 12px; border: 1px solid #f1f5f9; }
    .stat-item { flex: 1; text-align: center; }
    .stat-item-value { font-size: 18px; font-weight: 800; color: #1a202c; }
    .stat-item-label { font-size: 11px; color: #64748b; margin-top: 2px; font-weight: 600; }

    .banner-actions { display: flex; gap: 8px; margin-top: 20px; flex-wrap: wrap; border-top: 1px solid #f1f5f9; padding-top: 16px; }
    .btn { padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; transition: all 0.2s; }
    .btn-edit { background: #e0e7ff; color: #4338ca; }
    .btn-pause { background: #fef3c7; color: #92400e; }
    .btn-resume { background: #dcfce7; color: #15803d; }
    .btn-delete { background: #fee2e2; color: #b91c1c; }
    .btn-analytics { background: #f1f5f9; color: #475569; }
    .btn:hover { opacity: 0.8; transform: scale(1.02); }

    .alert { padding: 16px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
    .alert-success { background: #ecfdf5; border: 1px solid #10b981; color: #065f46; font-weight: 600; }
</style>
@endsection

@section('content')
@php
    $stats = $stats ?? [];
    $stats['total'] = $stats['total'] ?? 0;
    $stats['active'] = $stats['active'] ?? 0;
    $stats['draft'] = $stats['draft'] ?? 0;
    $stats['paused'] = $stats['paused'] ?? 0;

    // Get current locale (default to 'en' if not set)
    $currentLocale = app()->getLocale() ?? 'en';
@endphp

@if(session('success'))
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    {{ session('success') }}
</div>
@endif

<div class="page-header">
    <h1 class="page-title">Manage Banners</h1>
    <a href="{{ route('agent.banner.add') }}" class="btn-primary">
        <i class="fas fa-plus-circle"></i> Create New Banner
    </a>
</div>

{{-- Statistics Summary --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
        <div class="stat-label">Total Campaigns</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['active']) }}</div>
        <div class="stat-label">Currently Live</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['draft']) }}</div>
        <div class="stat-label">Pending Approval</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['paused']) }}</div>
        <div class="stat-label">Paused Ads</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="filters">
    <div class="filter-group">
        <div class="filter-label">Filter by Status</div>
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft / Pending</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
    </div>

    <div class="filter-group">
        <div class="filter-label">Search Campaigns</div>
        <input type="text" name="search" class="filter-select" placeholder="Search by title..." value="{{ request('search') }}">
    </div>
</form>

{{-- Banners Grid --}}
<div class="banners-grid">
    @forelse($banners as $banner)
        <div class="banner-card">
            <div class="banner-image-container">
                <img src="{{ $banner->image_url }}" alt="Campaign Image" class="banner-image">
            </div>

            <div class="banner-content">
                <h3 class="banner-title">
                    @php
                        // === THE FIX ===
                        // Aggressively decode until we find an array or a plain string
                        $rawTitle = $banner->title;

                        // Keep peeling back the JSON layers (handles double stringified data)
                        while (is_string($rawTitle)) {
                            $decoded = json_decode($rawTitle, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $rawTitle = $decoded;
                            } else {
                                break; // Not JSON anymore, it's just a string
                            }
                        }

                        // Display Logic: Prefer array key 'en', otherwise fallback
                        if (is_array($rawTitle)) {
                            // Try to get English explicitly
                            $displayTitle = $rawTitle['en']
                                            ?? $rawTitle['ar']
                                            ?? $rawTitle['ku']
                                            ?? 'Untitled Banner';
                        } else {
                            // If it wasn't an array (just a plain string), show it directly
                            $displayTitle = $rawTitle ?? 'Untitled Banner';
                        }
                    @endphp
                    {{ $displayTitle }}
                </h3>

                <div class="banner-meta">
                    <span class="banner-badge badge-{{ $banner->status }}">
                        {{ ucfirst($banner->status) }}
                    </span>
                    <span class="banner-badge" style="background: #f1f5f9; color: #475569;">
                        {{ strtoupper(str_replace('_', ' ', $banner->banner_size ?? 'STANDARD')) }}
                    </span>
                </div>

                <div class="banner-stats">
                    <div class="stat-item">
                        <div class="stat-item-value">{{ number_format($banner->views ?? 0) }}</div>
                        <div class="stat-item-label">Impressions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-value">{{ number_format($banner->clicks ?? 0) }}</div>
                        <div class="stat-item-label">Clicks</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-value">{{ number_format(($banner->ctr ?? 0) * 100, 1) }}%</div>
                        <div class="stat-item-label">CTR</div>
                    </div>
                </div>

                <div class="banner-actions">
                    <a href="{{ route('agent.banner.edit', $banner->id) }}" class="btn btn-edit" title="Edit Campaign">
                        <i class="fas fa-edit"></i>
                    </a>

                    @if($banner->status == 'active')
                        <form method="POST" action="{{ route('agent.banner.pause', $banner->id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-pause" title="Pause Campaign">
                                <i class="fas fa-pause"></i>
                            </button>
                        </form>
                    @elseif($banner->status == 'paused')
                        <form method="POST" action="{{ route('agent.banner.resume', $banner->id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-resume" title="Go Live">
                                <i class="fas fa-play"></i>
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('agent.banner.analytics', $banner->id) }}" class="btn btn-analytics" title="View Analytics">
                        <i class="fas fa-chart-bar"></i>
                    </a>

                    <form method="POST" action="{{ route('agent.banner.delete', $banner->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this banner permanently?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 100px 20px; background: white; border-radius: 16px; border: 2px dashed #e5e7eb;">
            <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-ad" style="font-size: 32px; color: #94a3b8;"></i>
            </div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1a202c; margin-bottom: 8px;">No Banners Found</h2>
            <p style="color: #64748b; margin-bottom: 24px;">Start promoting your properties by creating your first banner ad campaign.</p>
            <a href="{{ route('agent.banner.add') }}" class="btn-primary">
                <i class="fas fa-plus"></i> Create Your First Banner
            </a>
        </div>
    @endforelse
</div>

{{-- Pagination Support --}}
@if(method_exists($banners, 'hasPages') && $banners->hasPages())
<div style="margin-top: 40px; display: flex; justify-content: center;">
    {{ $banners->appends(request()->query())->links() }}
</div>
@endif

@endsection
