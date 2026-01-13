@extends('layouts.agent-layout')

@section('title', 'Subscriptions - Dream Mulk')

@section('styles')
<style>
    /* --- OFFICE STYLE CSS MATCH --- */
    .page-header { margin-bottom: 40px; }
    .page-title { font-size: 32px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
    .page-subtitle { color: #64748b; font-size: 16px; }

    /* Current Subscription Widget (Matches Office) */
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
        content: ''; position: absolute; top: -50%; right: -10%; width: 300px; height: 300px;
        background: rgba(255,255,255,0.1); border-radius: 50%;
    }
    .current-sub-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; position: relative; z-index: 1; }
    .current-sub-title { font-size: 14px; text-transform: uppercase; font-weight: 600; opacity: 0.9; margin-bottom: 8px; }
    .current-sub-plan { font-size: 28px; font-weight: 800; }
    .current-sub-badge { background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; }

    .current-sub-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; position: relative; z-index: 1; }
    .sub-detail-item { background: rgba(255,255,255,0.15); padding: 16px; border-radius: 12px; backdrop-filter: blur(10px); }
    .sub-detail-label { font-size: 12px; opacity: 0.8; margin-bottom: 6px; text-transform: uppercase; font-weight: 600; }
    .sub-detail-value { font-size: 20px; font-weight: 700; }

    /* Plans Grid (Matches Office) */
    .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 32px; margin-top: 40px; }

    .plan-card {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 36px;
        transition: all 0.3s;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .plan-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.1); border-color: #6366f1; }

    /* Featured & Active Styling */
    .plan-card.featured { border-color: #6366f1; background: linear-gradient(135deg, rgba(99,102,241,0.03) 0%, rgba(139,92,246,0.03) 100%); }
    .plan-card.current-plan { border-color: #22c55e; background: linear-gradient(135deg, rgba(34,197,94,0.03) 0%, rgba(16,185,129,0.03) 100%); }

    .plan-badge {
        position: absolute; top: -12px; right: 24px;
        background: #6366f1; color: white;
        padding: 6px 16px; border-radius: 20px;
        font-size: 12px; font-weight: 700; text-transform: uppercase;
    }
    .plan-badge.current { background: #22c55e; }

    .plan-type {
        display: inline-block; padding: 6px 14px; border-radius: 8px;
        font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;
        background: rgba(99,102,241,0.15); color: #6366f1;
    }

    .plan-name { font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 16px; }

    .plan-price { margin-bottom: 12px; }
    .plan-price-original { font-size: 18px; color: #94a3b8; text-decoration: line-through; font-weight: 600; margin-bottom: 4px; }
    .plan-price-final { font-size: 42px; font-weight: 800; color: #6366f1; line-height: 1; }
    .plan-price-currency { font-size: 18px; color: #64748b; font-weight: 600; margin-left: 4px; }

    .plan-duration {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        padding: 12px; background: #f8fafc; border-radius: 10px; margin-bottom: 24px;
    }
    .plan-duration i { color: #6366f1; font-size: 18px; }
    .plan-duration span { color: #1e293b; font-weight: 600; font-size: 15px; }

    .plan-features { list-style: none; margin-bottom: 32px; flex-grow: 1; }
    .plan-features li { padding: 12px 0; color: #64748b; font-size: 15px; display: flex; align-items: flex-start; gap: 12px; border-bottom: 1px dashed #f1f5f9; }
    .plan-features li:last-child { border-bottom: none; }
    .plan-features li i { color: #22c55e; font-size: 18px; margin-top: 2px; flex-shrink: 0; }

    .btn-subscribe {
        width: 100%; background: #6366f1; color: white; padding: 16px; border: none; border-radius: 10px;
        font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    /* Locked Button Style */
    .btn-subscribe:disabled { background: #94a3b8; cursor: not-allowed; transform: none; opacity: 0.8; }

    /* Active Button Style */
    .btn-subscribe.current { background: #22c55e; cursor: default; opacity: 1; }

    .empty { text-align: center; padding: 80px 20px; color: #94a3b8; background: white; border: 1px solid #e2e8f0; border-radius: 14px; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Subscription Plans</h1>
    <p class="page-subtitle">Choose the perfect plan for your business</p>
</div>

{{-- Current Subscription Widget --}}
@if($currentSubscription)
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
            <div class="sub-detail-label">Properties</div>
            {{-- This will now reflect the updated database value --}}
            <div class="sub-detail-value">{{ $currentSubscription->property_activation_limit }} Allowed</div>
        </div>
        <div class="sub-detail-item">
            <div class="sub-detail-label">Days Remaining</div>
            <div class="sub-detail-value">{{ $currentSubscription->daysRemaining() }} Days</div>
        </div>
        <div class="sub-detail-item">
            <div class="sub-detail-label">Expires On</div>
            <div class="sub-detail-value" style="font-size: 16px;">
                {{ $currentSubscription->end_date ? $currentSubscription->end_date->format('M d, Y') : 'Never' }}
            </div>
        </div>
    </div>
</div>
@endif

{{-- Dynamic Plans Grid --}}
@if(isset($plans) && $plans->count() > 0)
    <div class="plans-grid">
        @foreach($plans as $plan)
        @php
            $isCurrentPlan = $currentSubscription && $currentSubscription->current_plan_id == $plan->id;
        @endphp

        <div class="plan-card {{ $plan->is_featured ? 'featured' : '' }} {{ $isCurrentPlan ? 'current-plan' : '' }}">

            {{-- Badges --}}
            @if($isCurrentPlan)
                <div class="plan-badge current"><i class="fas fa-check"></i> Current Plan</div>
            @elseif($plan->is_featured)
                <div class="plan-badge">Recommended</div>
            @endif

            <div class="plan-type">Agent Plan</div>

            <h3 class="plan-name">{{ $plan->name }}</h3>

            <div class="plan-price">
                @if($plan->discount_iqd > 0)
                    <div class="plan-price-original">{{ number_format($plan->original_price_iqd) }} IQD</div>
                @endif
                <div>
                    <span class="plan-price-final">{{ number_format($plan->final_price_iqd) }}</span>
                    <span class="plan-price-currency">IQD</span>
                </div>
            </div>

            <div class="plan-duration">
                <i class="fas fa-clock"></i>
                <span>{{ $plan->duration_months }} Months Duration</span>
            </div>

            <ul class="plan-features">
                {{-- Property Limit Feature --}}
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>
                        @if($plan->max_properties > 0)
                            <strong>{{ $plan->max_properties }}</strong> Property Listings
                        @else
                            <strong>Unlimited</strong> Property Listings
                        @endif
                    </span>
                </li>

                {{-- Other Features --}}
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

            {{-- Action Button --}}
            @if($isCurrentPlan)
                <button type="button" class="btn-subscribe current" disabled>
                    <i class="fas fa-check-circle"></i> Active Plan
                </button>
            @else
                <button type="button" class="btn-subscribe" disabled>
                    <i class="fas fa-lock"></i> Contact Admin to Subscribe
                </button>
            @endif

        </div>
        @endforeach
    </div>
@else
    <div class="empty">
        <i class="fas fa-box-open" style="font-size: 64px; margin-bottom: 20px; opacity: 0.4;"></i>
        <h3 style="color: #64748b; margin-bottom: 8px;">No Plans Available</h3>
        <p>Subscription plans will be displayed here</p>
    </div>
@endif
@endsection
