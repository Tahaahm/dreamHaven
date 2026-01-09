@extends('layouts.office-layout')

@section('title', 'Subscription Plans - Dream Mulk')
@section('search-placeholder', 'Search plans...')

@section('styles')
<style>
    .page-header { margin-bottom: 40px; }
    .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
    .page-subtitle { color: var(--text-secondary); font-size: 16px; }
    .filter-tabs { display: flex; gap: 12px; margin-bottom: 32px; }
    .filter-tab { padding: 10px 20px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-secondary); font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; }
    .filter-tab:hover { border-color: #6366f1; color: #6366f1; }
    .filter-tab.active { background: #6366f1; color: white; border-color: #6366f1; }
    .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 32px; }
    .plan-card { background: var(--bg-card); border: 2px solid var(--border-color); border-radius: 16px; padding: 36px; transition: all 0.3s; position: relative; }
    .plan-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px var(--shadow); border-color: #6366f1; }
    .plan-card.featured { border-color: #6366f1; background: linear-gradient(135deg, rgba(99,102,241,0.08) 0%, rgba(139,92,246,0.08) 100%); }
    .plan-badge { position: absolute; top: -12px; right: 24px; background: #6366f1; color: white; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
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
    .btn-subscribe { width: 100%; background: #6366f1; color: white; padding: 16px; border: none; border-radius: 10px; font-size: 17px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-subscribe:hover { background: #5558e3; transform: translateY(-2px); }
    .empty { text-align: center; padding: 80px 20px; color: var(--text-muted); background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; }
    .empty i { font-size: 64px; margin-bottom: 20px; opacity: 0.4; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Subscription Plans</h1>
    <p class="page-subtitle">Choose the perfect plan for your business</p>
</div>

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
        <div class="plan-card {{ $plan->is_featured ? 'featured' : '' }}">
            @if($plan->is_featured)
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

            <form action="{{ route('office.subscription.subscribe', $plan->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-subscribe">
                    <i class="fas fa-crown"></i> Subscribe Now
                </button>
            </form>
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
