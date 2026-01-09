@extends('layouts.office-layout')

@section('title', 'Banner Ads - Dream Haven')

@section('styles')
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; }
    .stat-value { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
    .stat-label { font-size: 14px; color: var(--text-muted); }

    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title { font-size: 28px; font-weight: 700; color: var(--text-primary); }
    .btn-primary { background: #6366f1; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; }
    .btn-primary:hover { background: #5558e3; }

    .filters { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; gap: 16px; flex-wrap: wrap; }
    .filter-group { flex: 1; min-width: 200px; }
    .filter-label { font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; font-weight: 600; }
    .filter-select { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-main); color: var(--text-primary); }

    .banners-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px; }
    .banner-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; transition: transform 0.2s; }
    .banner-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }

    .banner-image { width: 100%; height: 180px; object-fit: cover; background: #f3f4f6; }
    .banner-content { padding: 20px; }
    .banner-title { font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; }
    .banner-meta { display: flex; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
    .banner-badge { padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; }
    .badge-active { background: #dcfce7; color: #16a34a; }
    .badge-draft { background: #fef3c7; color: #d97706; }
    .badge-paused { background: #e0e7ff; color: #6366f1; }
    .badge-rejected { background: #fee2e2; color: #dc2626; }

    .banner-stats { display: flex; gap: 20px; margin: 16px 0; padding: 16px; background: var(--bg-main); border-radius: 8px; }
    .stat-item { flex: 1; text-align: center; }
    .stat-item-value { font-size: 20px; font-weight: 700; color: var(--text-primary); }
    .stat-item-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

    .banner-actions { display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap; }
    .btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; }
    .btn-edit { background: #e0e7ff; color: #6366f1; }
    .btn-pause { background: #fef3c7; color: #d97706; }
    .btn-resume { background: #dcfce7; color: #16a34a; }
    .btn-delete { background: #fee2e2; color: #dc2626; }
    .btn-analytics { background: #f3f4f6; color: #374151; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Banner Ads</h1>
    <a href="{{ route('office.banner.add') }}" class="btn-primary">
        <i class="fas fa-plus"></i> Create Banner
    </a>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Banners</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['active'] }}</div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['draft'] }}</div>
        <div class="stat-label">Pending Approval</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['paused'] }}</div>
        <div class="stat-label">Paused</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="filters">
    <div class="filter-group">
        <div class="filter-label">Status</div>
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
    </div>

    <div class="filter-group">
        <div class="filter-label">Search</div>
        <input type="text" name="search" class="filter-select" placeholder="Search banners..." value="{{ request('search') }}">
    </div>
</form>

{{-- Banners Grid --}}
<div class="banners-grid">
    @forelse($banners as $banner)
        <div class="banner-card">
            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="banner-image">

            <div class="banner-content">
                <h3 class="banner-title">{{ $banner->title }}</h3>

                <div class="banner-meta">
                    <span class="banner-badge badge-{{ $banner->status }}">
                        {{ ucfirst($banner->status) }}
                    </span>
                    <span class="banner-badge" style="background: #f3f4f6; color: #374151;">
                        {{ ucfirst(str_replace('_', ' ', $banner->banner_type)) }}
                    </span>
                </div>

                <div class="banner-stats">
                    <div class="stat-item">
                        <div class="stat-item-value">{{ number_format($banner->views ?? 0) }}</div>
                        <div class="stat-item-label">Views</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-value">{{ number_format($banner->clicks ?? 0) }}</div>
                        <div class="stat-item-label">Clicks</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item-value">{{ number_format($banner->ctr * 100, 1) }}%</div>
                        <div class="stat-item-label">CTR</div>
                    </div>
                </div>

                <div class="banner-actions">
                    <a href="{{ route('office.banner.edit', $banner->id) }}" class="btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>

                    @if($banner->status == 'active')
                        <form method="POST" action="{{ route('office.banner.pause', $banner->id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-pause">
                                <i class="fas fa-pause"></i> Pause
                            </button>
                        </form>
                    @elseif($banner->status == 'paused')
                        <form method="POST" action="{{ route('office.banner.resume', $banner->id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-resume">
                                <i class="fas fa-play"></i> Resume
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('office.banner.analytics', $banner->id) }}" class="btn btn-analytics">
                        <i class="fas fa-chart-line"></i> Analytics
                    </a>

                    <form method="POST" action="{{ route('office.banner.delete', $banner->id) }}" style="display: inline;" onsubmit="return confirm('Delete this banner?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: var(--text-muted);">
            <i class="fas fa-bullhorn" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
            <p>No banners found. Create your first banner to get started!</p>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
<div style="margin-top: 32px;">
    {{ $banners->links() }}
</div>
@endsection
