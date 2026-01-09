@extends('layouts.office-layout')

@section('title', 'Agents - Dream Mulk')
@section('search-placeholder', 'Search agents...')

@section('top-actions')
    <a href="{{ route('office.agents.add') }}" class="add-btn">
        <i class="fas fa-plus"></i> Add Agent
    </a>
@endsection

@section('styles')
<style>
    .page-title {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 32px;
    }

    .agents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }

    .agent-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 28px;
        text-align: center;
        transition: all 0.3s;
    }

    .agent-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px var(--shadow);
        border-color: rgba(99,102,241,0.4);
    }

    .agent-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 32px;
        margin: 0 auto 18px;
    }

    .agent-name {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .agent-email {
        font-size: 14px;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .agent-phone {
        font-size: 14px;
        color: var(--text-muted);
        margin-bottom: 18px;
    }

    .agent-stats {
        display: flex;
        justify-content: center;
        gap: 24px;
        padding-top: 18px;
        border-top: 1px solid var(--border-color);
        margin-bottom: 18px;
    }

    .stat {
        text-align: center;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .remove-btn {
        width: 100%;
        background: transparent;
        border: 1px solid #ef4444;
        color: #ef4444;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .remove-btn:hover {
        background: #ef4444;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239,68,68,0.3);
    }

    .empty {
        text-align: center;
        padding: 80px 20px;
        color: var(--text-muted);
    }

    .empty i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.4;
    }

    .empty h3 {
        font-size: 24px;
        color: var(--text-secondary);
        margin-bottom: 10px;
    }

    .empty p {
        font-size: 16px;
        margin-bottom: 20px;
    }

    .empty .add-btn {
        margin-top: 20px;
        display: inline-flex;
        background: #6366f1;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
    }

    .empty .add-btn:hover {
        background: #5558e3;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99,102,241,0.3);
    }

    .alert {
        padding: 16px;
        border-radius: 10px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: rgba(34,197,94,0.1);
        color: #22c55e;
        border: 1px solid rgba(34,197,94,0.2);
    }

    .alert-error {
        background: rgba(239,68,68,0.1);
        color: #ef4444;
        border: 1px solid rgba(239,68,68,0.2);
    }
</style>
@endsection

@section('content')
<h1 class="page-title">Agents</h1>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if($agents->count() > 0)
    <div class="agents-grid">
        @foreach($agents as $agent)
        <div class="agent-card">
            <div class="agent-avatar">{{ strtoupper(substr($agent->agent_name, 0, 1)) }}</div>
            <div class="agent-name">{{ $agent->agent_name }}</div>
            <div class="agent-email">{{ $agent->primary_email ?? 'No email' }}</div>
            <div class="agent-phone">{{ $agent->primary_phone ?? 'No phone' }}</div>
            <div class="agent-stats">
                <div class="stat">
                    <div class="stat-value">{{ $agent->owned_properties_count ?? 0 }}</div>
                    <div class="stat-label">Properties</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $agent->properties_sold ?? 0 }}</div>
                    <div class="stat-label">Sold</div>
                </div>
            </div>
            <form action="{{ route('office.agents.remove', $agent->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this agent from your office?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="remove-btn">
                    <i class="fas fa-trash"></i> Remove Agent
                </button>
            </form>
        </div>
        @endforeach
    </div>
@else
    <div class="empty">
        <i class="fas fa-users"></i>
        <h3>No Agents Yet</h3>
        <p>Start building your team by adding your first agent</p>
        <a href="{{ route('office.agents.add') }}" class="add-btn">
            <i class="fas fa-plus"></i> Add Your First Agent
        </a>
    </div>
@endif
@endsection

@section('scripts')
<script>
// Any additional JavaScript for the agents page can go here
</script>
@endsection
