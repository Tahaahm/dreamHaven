@extends('layouts.agent-layout')

@section('title', 'Subscriptions - Dream Mulk')

@section('styles')
<style>
    .page-title { font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 12px; }
    .subtitle { color: #64748b; font-size: 16px; margin-bottom: 32px; }

    .current-plan {
        background: linear-gradient(135deg, #303b97, #1e2875);
        border-radius: 16px;
        padding: 32px;
        color: white;
        margin-bottom: 32px;
    }

    .current-plan h3 {
        font-size: 20px;
        margin-bottom: 8px;
        opacity: 0.9;
    }

    .current-plan h2 {
        font-size: 36px;
        margin-bottom: 20px;
    }

    .current-plan-features {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
    }

    .plan-feature {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
    }

    .plans-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin-top: 32px;
    }

    .plan-card {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 32px;
        transition: all 0.3s;
        position: relative;
    }

    .plan-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(48,59,151,0.15);
        border-color: #303b97;
    }

    .plan-card.featured {
        border-color: #303b97;
        box-shadow: 0 10px 30px rgba(48,59,151,0.2);
    }

    .featured-badge {
        position: absolute;
        top: -12px;
        right: 32px;
        background: #303b97;
        color: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .plan-name {
        font-size: 24px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .plan-price {
        font-size: 40px;
        font-weight: 700;
        color: #303b97;
        margin-bottom: 8px;
    }

    .plan-price span {
        font-size: 18px;
        color: #64748b;
        font-weight: 500;
    }

    .plan-description {
        color: #64748b;
        margin-bottom: 24px;
        font-size: 15px;
    }

    .plan-features {
        list-style: none;
        margin-bottom: 24px;
    }

    .plan-features li {
        padding: 10px 0;
        color: #64748b;
        display: flex;
        align-items: start;
        gap: 10px;
        font-size: 14px;
    }

    .plan-features i {
        color: #22c55e;
        margin-top: 2px;
    }

    .plan-features .disabled {
        color: #cbd5e1;
        opacity: 0.5;
    }

    .plan-features .disabled i {
        color: #cbd5e1;
    }

    .btn-subscribe {
        width: 100%;
        padding: 14px;
        background: #303b97;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-subscribe:hover {
        background: #1e2875;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(48,59,151,0.3);
    }

    .btn-subscribe.current {
        background: #22c55e;
        cursor: default;
    }

    .btn-subscribe.current:hover {
        transform: none;
    }

    .alert {
        padding: 16px;
        border-radius: 10px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-error {
        background: rgba(239,68,68,0.1);
        color: #ef4444;
        border: 1px solid rgba(239,68,68,0.2);
    }

    .alert-success {
        background: rgba(34,197,94,0.1);
        color: #22c55e;
        border: 1px solid rgba(34,197,94,0.2);
    }
</style>
@endsection

@section('content')
<h1 class="page-title">Subscription Plans</h1>
<p class="subtitle">Choose the perfect plan to grow your real estate business</p>

@if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($currentSubscription)
<div class="current-plan">
    <h3>Your Current Plan</h3>
    <h2>{{ $currentSubscription->currentPlan->name ?? 'Free Plan' }}</h2>
    <div class="current-plan-features">
        <div class="plan-feature">
            <i class="fas fa-check-circle"></i>
            <span>{{ $currentSubscription->property_activation_limit ?? 'Unlimited' }} Properties</span>
        </div>
        <div class="plan-feature">
            <i class="fas fa-calendar-alt"></i>
            <span>Expires: {{ $currentSubscription->end_date ? $currentSubscription->end_date->format('M d, Y') : 'Never' }}</span>
        </div>
        <div class="plan-feature">
            <i class="fas fa-trophy"></i>
            <span>Active since {{ $currentSubscription->created_at->format('M d, Y') }}</span>
        </div>
    </div>
</div>
@endif

<h2 style="font-size: 24px; margin-bottom: 20px;">Available Plans</h2>

<div class="plans-grid">
    <div class="plan-card">
        <div class="plan-name">Free</div>
        <div class="plan-price">
            $0 <span>/ month</span>
        </div>
        <div class="plan-description">Perfect for getting started</div>
        <ul class="plan-features">
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Up to 5 property listings</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Basic property features</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Standard support</span>
            </li>
            <li class="disabled">
                <i class="fas fa-times-circle"></i>
                <span>Featured listings</span>
            </li>
            <li class="disabled">
                <i class="fas fa-times-circle"></i>
                <span>Priority support</span>
            </li>
        </ul>
        <button class="btn-subscribe">
            Get Started
        </button>
    </div>

    <div class="plan-card">
        <div class="plan-name">Basic</div>
        <div class="plan-price">
            $29 <span>/ month</span>
        </div>
        <div class="plan-description">For growing professionals</div>
        <ul class="plan-features">
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Up to 20 property listings</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>All property features</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Priority support</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>2 featured listings</span>
            </li>
            <li class="disabled">
                <i class="fas fa-times-circle"></i>
                <span>Analytics dashboard</span>
            </li>
        </ul>
        <button class="btn-subscribe">
            Subscribe Now
        </button>
    </div>

    <div class="plan-card featured">
        <div class="featured-badge">Most Popular</div>
        <div class="plan-name">Professional</div>
        <div class="plan-price">
            $79 <span>/ month</span>
        </div>
        <div class="plan-description">For established agents</div>
        <ul class="plan-features">
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Up to 50 property listings</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>All property features</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Priority support</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>10 featured listings</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Advanced analytics</span>
            </li>
        </ul>
        <button class="btn-subscribe">
            Subscribe Now
        </button>
    </div>

    <div class="plan-card">
        <div class="plan-name">Enterprise</div>
        <div class="plan-price">
            $199 <span>/ month</span>
        </div>
        <div class="plan-description">For top-tier agencies</div>
        <ul class="plan-features">
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Unlimited property listings</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>All property features</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>24/7 Premium support</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Unlimited featured listings</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Custom branding</span>
            </li>
        </ul>
        <button class="btn-subscribe">
            Contact Sales
        </button>
    </div>
</div>
@endsection
