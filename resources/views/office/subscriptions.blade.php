@extends('layouts.office-layout')

@section('title', 'Subscription Plans - Dream Mulk')
@section('search-placeholder', 'Search plans...')

@section('styles')
<style>
    .page-header { margin-bottom: 40px; }
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
    .page-subtitle { color: var(--text-secondary); font-size: 16px; }

    /* Current Subscription Widget */
    .current-subscription {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 32px;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .current-subscription::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .current-sub-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
    }
    .current-sub-title {
        font-size: 14px;
        text-transform: uppercase;
        font-weight: 600;
        opacity: 0.9;
        margin-bottom: 8px;
    }
    .current-sub-plan {
        font-size: 28px;
        font-weight: 800;
    }
    .current-sub-badge {
        background: rgba(255,255,255,0.2);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    .current-sub-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        position: relative;
        z-index: 1;
    }
    .sub-detail-item {
        background: rgba(255,255,255,0.15);
        padding: 16px;
        border-radius: 12px;
        backdrop-filter: blur(10px);
    }
    .sub-detail-label {
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 6px;
        text-transform: uppercase;
        font-weight: 600;
    }
    .sub-detail-value {
        font-size: 24px;
        font-weight: 700;
    }
    .sub-progress-bar {
        margin-top: 24px;
        position: relative;
        z-index: 1;
    }
    .sub-progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
    }
    .sub-progress-track {
        background: rgba(255,255,255,0.2);
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
    }
    .sub-progress-fill {
        background: white;
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s;
    }

    .filter-tabs { display: flex; gap: 12px; margin-bottom: 32px; }
    .filter-tab { padding: 10px 20px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-secondary); font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; }
    .filter-tab:hover { border-color: #6366f1; color: #6366f1; }
    .filter-tab.active { background: #6366f1; color: white; border-color: #6366f1; }

    .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 32px; }
    .plan-card { background: var(--bg-card); border: 2px solid var(--border-color); border-radius: 16px; padding: 36px; transition: all 0.3s; position: relative; }
    .plan-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px var(--shadow); border-color: #6366f1; }
    .plan-card.featured { border-color: #6366f1; background: linear-gradient(135deg, rgba(99,102,241,0.08) 0%, rgba(139,92,246,0.08) 100%); }
    .plan-card.current-plan { border-color: #22c55e; background: linear-gradient(135deg, rgba(34,197,94,0.08) 0%, rgba(16,185,129,0.08) 100%); }
    .plan-badge { position: absolute; top: -12px; right: 24px; background: #6366f1; color: white; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .plan-badge.current { background: #22c55e; }
    .plan-type { display: inline-block; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px; }
    .plan-type.banner { background: rgba(99,102,241,0.15); color: #6366f1; }
    .plan-type.real_estate_office { background: rgba(34,197,94,0.15); color: #22c55e; }
    .plan-name { font-size: 28px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }
    .plan-price { margin-bottom: 12px; }
    .plan-price-original { font-size: 18px; color: var(--text-muted); text-decoration: line-through; font-weight: 600; margin-bottom: 4px; }
    .plan-price-final { font-size: 52px; font-weight: 800; color: #6366f1; line-height: 1; }
    .plan-price-currency { font-size: 22px; color: var(--text-muted); font-weight: 600; margin-left: 4px; }
    .plan-savings { display: inline-block; background: rgba(34,197,94,0.15); color: #22c55e; padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 700; margin-bottom: 20px; }
    .plan-duration { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px; background: var(--bg-main); border-radius: 10px; margin-bottom: 24px; }
    .plan-duration i { color: #6366f1; font-size: 18px; }
    .plan-duration span { color: var(--text-primary); font-weight: 600; font-size: 15px; }
    .plan-features { list-style: none; margin-bottom: 32px; }
    .plan-features li { padding: 12px 0; color: var(--text-secondary); font-size: 15px; display: flex; align-items: flex-start; gap: 12px; }
    .plan-features li i { color: #22c55e; font-size: 18px; margin-top: 2px; flex-shrink: 0; }

    /* Ensure disabled button looks correct */
    .btn-subscribe { width: 100%; background: #6366f1; color: white; padding: 16px; border: none; border-radius: 10px; font-size: 17px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-subscribe:disabled { background: #94a3b8; cursor: not-allowed; transform: none; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .empty { text-align: center; padding: 80px 20px; color: var(--text-muted); background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; }
    .empty i { font-size: 64px; margin-bottom: 20px; opacity: 0.4; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Subscription Plans</h1>
    <p class="page-subtitle">Choose the perfect plan for your business</p>
</div>

{{-- Current Subscription Widget --}}
@if($currentSubscription && $currentSubscription->isActive())
<div class="current-subscription">
    <div class="current-sub-header">
        <div>
            <div class="current-sub-title">Current Plan</div>
            <div class="current-sub-plan">{{ $currentSubscription->currentPlan->name ?? 'Active Subscription' }}</div>
        </div>
        <div class="current-sub-badge">
            <i class="fas fa-check-circle"></i> Active
        </div>
    </div>

    <div class="current-sub-details">
        <div class="sub-detail-item">
            <div class="sub-detail-label">Days Remaining</div>
            <div class="sub-detail-value">{{ $currentSubscription->daysRemaining() }}</div>
        </div>
        <div class="sub-detail-item">
            <div class="sub-detail-label">Expires On</div>
            <div class="sub-detail-value" style="font-size: 16px;">
                {{ $currentSubscription->end_date ? $currentSubscription->end_date->format('M d, Y') : 'Never' }}
            </div>
        </div>
        @if($propertyLimit['has_subscription'])
        <div class="sub-detail-item">
            <div class="sub-detail-label">Properties</div>
            <div class="sub-detail-value" style="font-size: 18px;">
                {{ $propertyLimit['current'] }} / {{ $propertyLimit['limit'] }}
            </div>
        </div>
        @endif
    </div>

    @if($currentSubscription->end_date)
    <div class="sub-progress-bar">
        <div class="sub-progress-label">
            <span>Subscription Progress</span>
            <span>{{ round($currentSubscription->remainingPercentage()) }}% remaining</span>
        </div>
        <div class="sub-progress-track">
            <div class="sub-progress-fill" style="width: {{ $currentSubscription->remainingPercentage() }}%"></div>
        </div>
    </div>
    @endif
</div>
@endif

<div class="filter-tabs">
    <a href="{{ route('office.subscriptions', ['type' => 'real_estate_office']) }}" class="filter-tab {{ !request('type') || request('type') == 'real_estate_office' ? 'active' : '' }}">
        <i class="fas fa-building"></i> Office Plans
    </a>
    <a href="{{ route('office.subscriptions', ['type' => 'banner']) }}" class="filter-tab {{ request('type') == 'banner' ? 'active' : '' }}">
        <i class="fas fa-ad"></i> Banner Advertising
    </a>
</div>

@if(isset($plans) && $plans->count() > 0)
    <div class="plans-grid">
        @foreach($plans as $plan)
        @php
            $isCurrentPlan = $currentSubscription && $currentSubscription->current_plan_id == $plan->id;
        @endphp
        <div class="plan-card {{ $plan->is_featured ? 'featured' : '' }} {{ $isCurrentPlan ? 'current-plan' : '' }}">
            @if($isCurrentPlan)
                <div class="plan-badge current">Current Plan</div>
            @elseif($plan->is_featured)
                <div class="plan-badge">Recommended</div>
            @endif

            <div class="plan-type {{ $plan->type }}">
                {{ $plan->type == 'banner' ? 'Banner Ad' : 'Office Plan' }}
            </div>

            <h3 class="plan-name">{{ $plan->name }}</h3>

            <div class="plan-price">
                @if($plan->discount_iqd > 0)
                    <div class="plan-price-original">{{ number_format($plan->original_price_iqd) }} IQD</div>
                @endif
                <div>
                    <span class="plan-price-final">{{ number_format($plan->final_price_iqd) }}</span>
                    <span class="plan-price-currency">IQD</span>
                </div>
                @if($plan->discount_percentage > 0)
                    <div class="plan-savings">
                        <i class="fas fa-tag"></i> Save {{ $plan->discount_percentage }}%
                    </div>
                @endif
            </div>

            <div class="plan-duration">
                <i class="fas fa-clock"></i>
                <span>{{ ucfirst(str_replace('_', ' ', $plan->duration_label)) }}</span>
            </div>

            @if($plan->features)
                <ul class="plan-features">
                    @php
                        $features = is_array($plan->features) ? $plan->features : json_decode($plan->features, true);
                    @endphp
                    @if($features && is_array($features))
                        @foreach($features as $feature)
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>{{ is_array($feature) ? ($feature['name'] ?? $feature['text'] ?? 'Feature') : $feature }}</span>
                            </li>
                        @endforeach
                    @endif
                </ul>
            @endif

            @if($plan->max_properties)
                <div style="padding: 16px; background: var(--bg-main); border-radius: 10px; margin-bottom: 24px; text-align: center;">
                    <div style="font-size: 32px; font-weight: 800; color: #6366f1;">{{ $plan->max_properties }}</div>
                    <div style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-top: 4px;">Properties Allowed</div>
                </div>
            @endif

            {{--
                LOCKED BUTTON SECTION
                We have removed the form and replaced it with a disabled button
                that instructs the user to contact the admin.
            --}}
            @if($isCurrentPlan)
                <button type="button" class="btn-subscribe" disabled>
                    <i class="fas fa-check-circle"></i> Active Plan
                </button>
            @else
                <button type="button" class="btn-subscribe" disabled>
                    <i class="fas fa-lock"></i> Contact Admin to Subscribe
                </button>
            @endif

            {{-- Optional small help text to reinforce the message --}}
            <div style="text-align: center; margin-top: 12px; font-size: 13px; color: var(--text-muted);">
                <i class="fas fa-info-circle"></i> Admin activation required
            </div>

        </div>
        @endforeach
    </div>
@else
    <div class="empty">
        <i class="fas fa-box-open"></i>
        <h3 style="color: var(--text-secondary); margin-bottom: 8px;">No Plans Available</h3>
        <p>Subscription plans will be displayed here</p>
    </div>
@endif
@endsection
