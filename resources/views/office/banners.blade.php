@extends('layouts.office-layout')

@section('title', 'Ad Management | Dream Haven')

@section('styles')
<style>
    :root {
        --primary: #6366f1;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --bg-subtle: #f8fafc;
        --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Page Header */
    .page-header { margin-bottom: 2rem; }
    .page-title { font-size: 1.875rem; font-weight: 800; color: #111827; letter-spacing: -0.025em; }

    .btn-create {
        background: var(--primary); color: white; padding: 0.75rem 1.5rem;
        border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center;
        gap: 0.5rem; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4);
    }
    .btn-create:hover { background: #4f46e5; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.5); }

    /* Stats Section */
    .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
    .stat-glass-card {
        background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem;
        position: relative; overflow: hidden; transition: transform 0.3s ease;
    }
    .stat-glass-card:hover { transform: translateY(-5px); }
    .stat-number { font-size: 2rem; font-weight: 800; color: #1e293b; line-height: 1; margin-bottom: 0.5rem; }
    .stat-label { font-size: 0.875rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
    .stat-icon { position: absolute; right: -10px; bottom: -10px; font-size: 4rem; opacity: 0.05; color: var(--primary); }

    /* Filter Bar */
    .filter-bar {
        background: white; padding: 1.25rem; border-radius: 16px; border: 1px solid #e2e8f0;
        display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem; box-shadow: var(--card-shadow);
    }
    .search-wrapper { position: relative; flex: 1; }
    .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .input-custom {
        width: 100%; padding: 0.625rem 1rem 0.625rem 2.5rem; border-radius: 10px;
        border: 1px solid #e2e8f0; background: #fcfcfd; transition: border 0.2s;
    }
    .input-custom:focus { border-color: var(--primary); outline: none; background: white; }

    /* Banner Grid & Cards */
    .banners-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem; }
    .premium-card {
        background: white; border-radius: 20px; overflow: hidden; border: 1px solid #f1f5f9;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative;
    }
    .premium-card:hover { transform: scale(1.02); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }

    .img-wrapper { position: relative; height: 200px; overflow: hidden; }
    .img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
    .premium-card:hover .img-wrapper img { transform: scale(1.1); }

    .status-floating {
        position: absolute; top: 1rem; left: 1rem; padding: 0.4rem 1rem;
        border-radius: 50px; font-size: 0.75rem; font-weight: 700; backdrop-filter: blur(8px);
    }
    .status-active { background: rgba(16, 185, 129, 0.9); color: white; }
    .status-paused { background: rgba(245, 158, 11, 0.9); color: white; }

    .card-body { padding: 1.5rem; }
    .card-title { font-size: 1.125rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem; }

    /* Micro Stats */
    .micro-stats { display: flex; justify-content: space-between; background: #f8fafc; border-radius: 12px; padding: 1rem; }
    .m-stat-val { display: block; font-weight: 800; color: #334155; font-size: 1rem; }
    .m-stat-lbl { font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; }

    /* Action Buttons */
    .action-row { display: flex; gap: 0.5rem; margin-top: 1.5rem; }
    .btn-icon {
        flex: 1; padding: 0.6rem; border-radius: 8px; border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: all 0.2s;
        font-size: 0.875rem; font-weight: 600;
    }
    .btn-edit { background: #eff6ff; color: #2563eb; }
    .btn-edit:hover { background: #2563eb; color: white; }
    .btn-pause { background: #fff7ed; color: #ea580c; }
    .btn-pause:hover { background: #ea580c; color: white; }
    .btn-delete { background: #fef2f2; color: #dc2626; }
    .btn-delete:hover { background: #dc2626; color: white; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 class="page-title">Advertising Console</h1>
            <p style="color: #64748b; margin-top: 0.5rem;">Manage and monitor your banner performance across the network.</p>
        </div>
        <a href="{{ route('office.banner.add') }}" class="btn-create">
            <i class="fas fa-plus"></i> New Campaign
        </a>
    </div>
</div>

{{-- Stats Section --}}
<div class="stats-container">
    <div class="stat-glass-card">
        <div class="stat-number">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Banners</div>
        <i class="fas fa-layer-group stat-icon"></i>
    </div>
    <div class="stat-glass-card" style="border-left: 4px solid var(--success);">
        <div class="stat-number" style="color: var(--success);">{{ $stats['active'] }}</div>
        <div class="stat-label">Live Now</div>
        <i class="fas fa-broadcast-tower stat-icon"></i>
    </div>
    <div class="stat-glass-card" style="border-left: 4px solid var(--warning);">
        <div class="stat-number" style="color: var(--warning);">{{ $stats['paused'] }}</div>
        <div class="stat-label">Paused</div>
        <i class="fas fa-pause-circle stat-icon"></i>
    </div>
    <div class="stat-glass-card">
        <div class="stat-number">{{ $stats['draft'] }}</div>
        <div class="stat-label">In Review</div>
        <i class="fas fa-clock stat-icon"></i>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('office.banners') }}" class="filter-bar">
    <div class="search-wrapper">
        <i class="fas fa-search search-icon"></i>
        <input type="text" name="search" class="input-custom" placeholder="Search campaigns..." value="{{ request('search') }}">
    </div>
    <select name="status" class="input-custom" style="width: 180px;" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
        <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
    </select>
</form>

{{-- Banner Grid --}}
<div class="banners-grid">
    @forelse($banners as $banner)
    <div class="premium-card">
        <div class="img-wrapper">
            <span class="status-floating status-{{ $banner->status }}">
                {{ strtoupper($banner->status) }}
            </span>
            <img src="{{ $banner->image_url }}" alt="Banner Title">
        </div>

        <div class="card-body">
            <h3 class="card-title">{{ $banner->title }}</h3>

            <div class="micro-stats">
                <div style="text-align: center;">
                    <span class="m-stat-lbl">Views</span>
                    <span class="m-stat-val">{{ number_format($banner->views) }}</span>
                </div>
                <div style="border-left: 1px solid #e2e8f0; padding-left: 1rem; text-align: center;">
                    <span class="m-stat-lbl">Clicks</span>
                    <span class="m-stat-val">{{ number_format($banner->clicks) }}</span>
                </div>
                <div style="border-left: 1px solid #e2e8f0; padding-left: 1rem; text-align: center;">
                    <span class="m-stat-lbl">CTR</span>
                    <span class="m-stat-val" style="color: var(--primary);">{{ number_format($banner->ctr * 100, 1) }}%</span>
                </div>
            </div>

            <div class="action-row">
                <a href="{{ route('office.banner.edit', $banner->id) }}" class="btn-icon btn-edit" title="Edit">
                    <i class="fas fa-pen"></i>
                </a>

                @if($banner->status == 'active')
                <form method="POST" action="{{ route('office.banner.pause', $banner->id) }}" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn-icon btn-pause" style="width: 100%;">
                        <i class="fas fa-pause"></i>
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('office.banner.resume', $banner->id) }}" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn-icon" style="width: 100%; background: #ecfdf5; color: #059669;">
                        <i class="fas fa-play"></i>
                    </button>
                </form>
                @endif

                <a href="{{ route('office.banner.analytics', $banner->id) }}" class="btn-icon" style="background: #f1f5f9; color: #475569;">
                    <i class="fas fa-chart-bar"></i>
                </a>

                <form method="POST" action="{{ route('office.banner.delete', $banner->id) }}" onsubmit="return confirm('Delete this banner permanently?')" style="flex: 0 0 45px;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-icon btn-delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column: 1/-1; text-align: center; padding: 5rem; background: white; border-radius: 20px; border: 2px dashed #e2e8f0;">
        <i class="fas fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
        <h3 style="color: #64748b;">No active campaigns found</h3>
        <p style="color: #94a3b8;">Start by creating your first banner advertisement.</p>
    </div>
    @endforelse
</div>

<div style="margin-top: 3rem;">
    {{ $banners->links() }}
</div>
@endsection
