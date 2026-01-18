@extends('layouts.office-layout')

@section('title', 'Team Management - Dream Mulk')

@section('styles')
<style>
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --danger: #ef4444;
        --surface: #ffffff;
        --background: #f8fafc;
        --border: #e2e8f0;
        --text-main: #0f172a;
        --text-sub: #64748b;
    }

    /* Page Header Layout */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        background: white;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        flex-wrap: wrap;
        gap: 20px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-main);
        margin: 0;
    }

    .count-badge {
        background: #e0e7ff;
        color: var(--primary);
        font-size: 13px;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 700;
    }

    /* Controls Area (Search + Add Button) */
    .header-controls {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    /* Search Bar */
    .search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding-left: 14px;
        transition: all 0.2s;
    }

    .search-wrapper:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .search-icon { color: var(--text-sub); font-size: 14px; }

    .search-input {
        border: none;
        padding: 12px 14px;
        font-size: 14px;
        width: 260px;
        outline: none;
        color: var(--text-main);
        background: transparent;
    }

    /* Add Button - High Visibility */
    .btn-add {
        background: var(--text-main);
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
        white-space: nowrap;
    }

    .btn-add:hover {
        background: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    }

    /* Grid Layout */
    .agents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }

    /* Agent Card */
    .agent-card {
        background: var(--surface);
        border-radius: 20px;
        border: 1px solid var(--border);
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .agent-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.08);
        border-color: var(--primary);
    }

    /* Card Banner */
    .card-banner {
        height: 90px;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        border-bottom: 1px solid var(--border);
    }

    /* Avatar */
    .avatar-wrapper {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        margin: -42px auto 0;
        position: relative;
        padding: 4px;
        background: var(--surface);
    }

    .agent-avatar {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .avatar-placeholder {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--text-main), #334155);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* Content */
    .card-content {
        padding: 12px 24px 20px;
        text-align: center;
        flex: 1;
    }

    .agent-name {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .agent-email {
        font-size: 13px;
        color: var(--text-sub);
        margin-bottom: 16px;
        display: block;
        font-weight: 500;
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    .quick-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    .action-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f1f5f9;
        color: var(--text-sub);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        text-decoration: none;
        border: 1px solid transparent;
    }

    .action-circle:hover {
        background: var(--primary);
        color: white;
        transform: scale(1.1);
    }

    /* Stats */
    .stats-row {
        display: flex;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }

    .stat-col {
        flex: 1;
        text-align: center;
        padding: 12px 8px;
    }

    .stat-val { font-size: 18px; font-weight: 800; color: var(--text-main); }
    .stat-lbl { font-size: 11px; color: var(--text-sub); font-weight: 600; text-transform: uppercase; }

    /* Footer */
    .card-footer { padding: 16px 24px; }

    .btn-remove {
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #fee2e2;
        background: #fff1f2;
        color: var(--danger);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-remove:hover {
        background: var(--danger);
        color: white;
        border-color: var(--danger);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 24px;
        border: 2px dashed var(--border);
        margin-top: 40px;
    }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: stretch; }
        .header-controls { flex-direction: column; width: 100%; }
        .search-wrapper, .search-input { width: 100%; }
        .btn-add { width: 100%; justify-content: center; }
    }
</style>
@endsection

@section('content')

<div class="page-header">
    <div class="header-left">
        <h1 class="page-title">
            Team Members
            <span class="count-badge">{{ $agents->count() }}</span>
        </h1>
    </div>

    <div class="header-controls">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="agentSearch" class="search-input" placeholder="Search name or email...">
        </div>

        <a href="{{ route('office.agents.add') }}" class="btn-add">
            <i class="fas fa-plus"></i> Add New Agent
        </a>
    </div>
</div>

@if(session('success'))
    <div style="padding: 16px; background: #dcfce7; color: #166534; border-radius: 12px; margin-bottom: 24px; display: flex; gap: 10px; align-items: center; border: 1px solid #bbf7d0;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="padding: 16px; background: #fee2e2; color: #991b1b; border-radius: 12px; margin-bottom: 24px; display: flex; gap: 10px; align-items: center; border: 1px solid #fecaca;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

@if($agents->count() > 0)
    <div class="agents-grid" id="agentsGrid">
        @foreach($agents as $agent)
        <div class="agent-card">
            <div class="card-banner"></div>

            <div class="avatar-wrapper">
                @if(!empty($agent->profile_image))
                    <img src="{{ asset($agent->profile_image) }}" alt="{{ $agent->agent_name }}" class="agent-avatar">
                @else
                    <div class="avatar-placeholder">
                        {{ strtoupper(substr($agent->agent_name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <div class="card-content">
                <h3 class="agent-name">{{ $agent->agent_name }}</h3>
                <span class="agent-email">{{ $agent->primary_email }}</span>

                <div class="quick-actions">
                    @if($agent->primary_phone)
                        <a href="tel:{{ $agent->primary_phone }}" class="action-circle" title="Call">
                            <i class="fas fa-phone"></i>
                        </a>
                    @endif
                    <a href="mailto:{{ $agent->primary_email }}" class="action-circle" title="Email">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-col">
                    <div class="stat-val">{{ $agent->owned_properties_count ?? 0 }}</div>
                    <div class="stat-lbl">Listed</div>
                </div>
                <div class="stat-col">
                    <div class="stat-val">{{ $agent->properties_sold ?? 0 }}</div>
                    <div class="stat-lbl">Sold</div>
                </div>
            </div>

            <div class="card-footer">
                <form action="{{ route('office.agents.remove', $agent->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-remove" onclick="return confirm('Are you sure you want to remove {{ $agent->agent_name }} from your team?')">
                        <i class="fas fa-trash-alt"></i> Remove Agent
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <div style="font-size: 48px; color: var(--text-sub); margin-bottom: 20px;">
            <i class="fas fa-users"></i>
        </div>
        <h2 style="font-size: 24px; color: var(--text-main); margin-bottom: 12px; font-weight: 800;">Build Your Team</h2>
        <p style="color: var(--text-sub); margin-bottom: 32px;">You haven't added any agents to your office yet.</p>
        <a href="{{ route('office.agents.add') }}" class="btn-add">
            <i class="fas fa-plus"></i> Add New Agent
        </a>
    </div>
@endif

@endsection

@section('scripts')
<script>
    // Search Functionality: Filters by Name OR Email
    document.getElementById('agentSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let cards = document.querySelectorAll('.agent-card');

        cards.forEach(card => {
            // Get Agent Name
            let name = card.querySelector('.agent-name').innerText.toLowerCase();
            // Get Agent Email
            let email = card.querySelector('.agent-email').innerText.toLowerCase();

            // Check if search text matches either name or email
            if(name.includes(value) || email.includes(value)) {
                card.style.display = "flex";
            } else {
                card.style.display = "none";
            }
        });
    });
</script>
@endsection
