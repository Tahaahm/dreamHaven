@extends('layouts.office-layout')

@section('title', 'Agreements - Dream Mulk')
@section('search-placeholder', 'Search agreements...')

@section('styles')
<style>
    .coming-soon-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
        text-align: center;
        padding: 40px;
    }
    .coming-soon-icon {
        font-size: 120px;
        color: #6366f1;
        margin-bottom: 30px;
        opacity: 0.3;
    }
    .coming-soon-title {
        font-size: 48px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 16px;
    }
    .coming-soon-subtitle {
        font-size: 24px;
        color: var(--text-secondary);
        margin-bottom: 12px;
    }
    .coming-soon-description {
        font-size: 16px;
        color: var(--text-muted);
        max-width: 600px;
        line-height: 1.6;
        margin-bottom: 40px;
    }
    .feature-list {
        text-align: left;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 30px 40px;
        max-width: 500px;
        margin-top: 20px;
    }
    .feature-list h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 20px;
        text-align: center;
    }
    .feature-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        color: var(--text-secondary);
        font-size: 15px;
    }
    .feature-item i {
        color: #6366f1;
        font-size: 18px;
        width: 24px;
    }
</style>
@endsection

@section('content')
<div class="coming-soon-container">
    <div class="coming-soon-icon">
        <i class="fas fa-file-contract"></i>
    </div>

    <h1 class="coming-soon-title">Coming Soon</h1>
    <h2 class="coming-soon-subtitle">Agreements & Contracts</h2>

    <p class="coming-soon-description">
        We're building a powerful contract management system to help you manage all your property agreements, track contract status, and organize your legal documents in one place.
    </p>

    <div class="feature-list">
        <h3>What's Coming:</h3>

        <div class="feature-item">
            <i class="fas fa-check-circle"></i>
            <span>Create and manage sale agreements</span>
        </div>

        <div class="feature-item">
            <i class="fas fa-check-circle"></i>
            <span>Rental contract tracking</span>
        </div>

        <div class="feature-item">
            <i class="fas fa-check-circle"></i>
            <span>Contract status monitoring</span>
        </div>

        <div class="feature-item">
            <i class="fas fa-check-circle"></i>
            <span>Digital signatures</span>
        </div>

        <div class="feature-item">
            <i class="fas fa-check-circle"></i>
            <span>PDF generation & download</span>
        </div>

        <div class="feature-item">
            <i class="fas fa-check-circle"></i>
            <span>Expiry date alerts</span>
        </div>
    </div>
</div>
@endsection
