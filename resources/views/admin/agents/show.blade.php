@extends('layouts.admin-layout')

@section('title', $agent->agent_name . ' — Agent Profile')

@section('content')

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- DREAMMULK · AGENT SHOW · Redesigned UI                    --}}
{{-- ══════════════════════════════════════════════════════════ --}}

<style>
    :root {
        --dm-ink:       #0f172a;
        --dm-ink-2:     #475569;
        --dm-ink-3:     #94a3b8;
        --dm-border:    #e2e8f0;
        --dm-surface:   #f8fafc;
        --dm-white:     #ffffff;
        --dm-accent:    #6366f1;
        --dm-accent-lt: #eef2ff;
        --dm-green:     #10b981;
        --dm-green-lt:  #ecfdf5;
        --dm-amber:     #f59e0b;
        --dm-amber-lt:  #fffbeb;
        --dm-red:       #ef4444;
        --dm-blue:      #3b82f6;
        --dm-blue-lt:   #eff6ff;
        --dm-radius-sm: 10px;
        --dm-radius:    14px;
        --dm-radius-lg: 20px;
        --dm-shadow-sm: 0 1px 3px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.04);
        --dm-shadow:    0 4px 14px rgba(15,23,42,.09);
    }

    .dm-show-wrap { max-width: 1120px; margin: 0 auto; padding-bottom: 3rem; }

    /* ── Topbar ─────────────────────────────────────────── */
    .dm-topbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.5rem 0 1.75rem;
        flex-wrap: wrap;
    }
    .dm-breadcrumb {
        display: flex; align-items: center; gap: .35rem;
        font-size: .7rem; font-weight: 600; color: var(--dm-ink-3);
        letter-spacing: .04em; text-transform: uppercase; margin-bottom: .35rem;
    }
    .dm-breadcrumb a { color: var(--dm-ink-3); text-decoration: none; }
    .dm-breadcrumb a:hover { color: var(--dm-ink); }
    .dm-breadcrumb span { color: var(--dm-border); }
    .dm-page-title {
        font-size: 1.35rem; font-weight: 800; color: var(--dm-ink);
        letter-spacing: -.02em; display: flex; align-items: center;
        gap: .6rem; line-height: 1.2;
    }
    .dm-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .2rem .6rem; border-radius: 99px;
        font-size: .63rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
    }
    .dm-badge--verified { background: var(--dm-green-lt); color: #065f46; border: 1px solid #a7f3d0; }
    .dm-badge--pending  { background: var(--dm-amber-lt); color: #92400e; border: 1px solid #fcd34d; }

    .dm-topbar-actions { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }

    /* ── Buttons ─────────────────────────────────────────── */
    .dm-btn {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .55rem 1.1rem; border-radius: var(--dm-radius-sm);
        font-size: .78rem; font-weight: 800; cursor: pointer;
        border: none; transition: all .15s; text-decoration: none; white-space: nowrap;
    }
    .dm-btn--primary { background: var(--dm-ink); color: #fff; box-shadow: 0 2px 6px rgba(15,23,42,.2); }
    .dm-btn--primary:hover { background: #1e293b; }
    .dm-btn--ghost { background: var(--dm-white); border: 1.5px solid var(--dm-border); color: var(--dm-ink-2); }
    .dm-btn--ghost:hover { background: var(--dm-surface); color: var(--dm-ink); }
    .dm-btn--red { background: #fef2f2; border: 1.5px solid #fecaca; color: var(--dm-red); }
    .dm-btn--red:hover { background: #fee2e2; }
    .dm-btn--green-solid { background: var(--dm-green); color: #fff; }
    .dm-btn--green-solid:hover { background: #059669; }

    /* Dropdown */
    .dm-dropdown { position: relative; }
    .dm-dropdown-menu {
        display: none; position: absolute; right: 0; top: calc(100% + .4rem);
        background: var(--dm-white); border: 1px solid var(--dm-border);
        border-radius: var(--dm-radius); box-shadow: var(--dm-shadow);
        z-index: 60; min-width: 170px; overflow: hidden;
    }
    .dm-dropdown:hover .dm-dropdown-menu { display: block; }
    .dm-dropdown-item {
        display: flex; align-items: center; gap: .6rem;
        padding: .7rem 1rem; font-size: .78rem; font-weight: 700;
        cursor: pointer; border: none; background: transparent; width: 100%;
        text-align: left; text-decoration: none; color: var(--dm-ink-2);
        transition: background .13s;
    }
    .dm-dropdown-item:hover { background: var(--dm-surface); color: var(--dm-ink); }
    .dm-dropdown-item--danger { color: var(--dm-red); }
    .dm-dropdown-item--danger:hover { background: #fef2f2; }
    .dm-dropdown-divider { height: 1px; background: var(--dm-border); margin: .25rem 0; }

    /* ── Card ───────────────────────────────────────────── */
    .dm-card {
        background: var(--dm-white);
        border: 1px solid var(--dm-border);
        border-radius: var(--dm-radius-lg);
        box-shadow: var(--dm-shadow-sm);
        overflow: hidden;
    }
    .dm-card-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: .9rem 1.25rem; border-bottom: 1px solid var(--dm-border);
        background: var(--dm-surface);
    }
    .dm-card-title {
        font-size: .67rem; font-weight: 800; letter-spacing: .08em;
        text-transform: uppercase; color: var(--dm-ink-2);
        display: flex; align-items: center; gap: .45rem;
    }
    .dm-card-body { padding: 1.25rem; }

    /* ── Hero banner ────────────────────────────────────── */
    .dm-hero {
        position: relative;
        height: 160px;
        border-radius: var(--dm-radius-lg) var(--dm-radius-lg) 0 0;
        overflow: hidden;
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);
    }
    .dm-hero img { width:100%; height:100%; object-fit:cover; }
    .dm-hero-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(to bottom, transparent 40%, rgba(15,23,42,.55));
    }

    /* ── Profile card ───────────────────────────────────── */
    .dm-profile-card {
        border-top: none;
        border-radius: 0 0 var(--dm-radius-lg) var(--dm-radius-lg);
    }
    .dm-profile-inner { padding: 0 1.5rem 1.5rem; }
    .dm-avatar-wrap {
        position: relative;
        margin-top: -44px;
        display: inline-block;
        margin-bottom: .75rem;
    }
    .dm-avatar {
        width: 88px; height: 88px;
        border-radius: var(--dm-radius);
        border: 3px solid var(--dm-white);
        box-shadow: var(--dm-shadow);
        object-fit: cover;
        display: block;
        background: var(--dm-surface);
    }
    .dm-avatar-init {
        width: 88px; height: 88px;
        border-radius: var(--dm-radius);
        border: 3px solid var(--dm-white);
        box-shadow: var(--dm-shadow);
        background: linear-gradient(135deg, var(--dm-accent), #818cf8);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 900; color: #fff; letter-spacing: -.02em;
    }
    .dm-type-badge {
        position: absolute; bottom: -4px; right: -4px;
        width: 24px; height: 24px; border-radius: 50%;
        background: var(--dm-white); border: 2px solid var(--dm-border);
        display: flex; align-items: center; justify-content: center;
        font-size: .65rem; box-shadow: var(--dm-shadow-sm);
    }
    .dm-agent-name { font-size: 1.1rem; font-weight: 800; color: var(--dm-ink); line-height: 1.2; }
    .dm-agent-sub  { font-size: .78rem; font-weight: 500; color: var(--dm-ink-3); margin-top: .2rem; }

    /* stat bar */
    .dm-stat-bar {
        display: grid; grid-template-columns: 1fr 1fr;
        border-top: 1px solid var(--dm-border); margin-top: 1rem;
    }
    .dm-stat-item {
        padding: .9rem .5rem;
        text-align: center;
    }
    .dm-stat-item + .dm-stat-item { border-left: 1px solid var(--dm-border); }
    .dm-stat-val { font-size: 1.3rem; font-weight: 900; color: var(--dm-ink); line-height: 1; }
    .dm-stat-lbl { font-size: .63rem; font-weight: 700; color: var(--dm-ink-3); text-transform: uppercase; letter-spacing: .05em; margin-top: .2rem; }

    /* ── Contact card ───────────────────────────────────── */
    .dm-contact-list { display: flex; flex-direction: column; gap: .2rem; }
    .dm-contact-row {
        display: flex; align-items: center; gap: .75rem;
        padding: .7rem .75rem; border-radius: var(--dm-radius-sm);
        transition: background .13s; text-decoration: none;
    }
    .dm-contact-row:hover { background: var(--dm-surface); }
    .dm-contact-icon {
        width: 34px; height: 34px; border-radius: 9px;
        background: var(--dm-surface); border: 1px solid var(--dm-border);
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem; color: var(--dm-ink-3); flex-shrink: 0;
        transition: background .13s, color .13s;
    }
    .dm-contact-row:hover .dm-contact-icon { background: var(--dm-accent-lt); color: var(--dm-accent); }
    .dm-contact-lbl { font-size: .63rem; font-weight: 700; color: var(--dm-ink-3); text-transform: uppercase; letter-spacing: .04em; }
    .dm-contact-val { font-size: .82rem; font-weight: 700; color: var(--dm-ink); }

    /* ── Subscription card ──────────────────────────────── */
    .dm-sub-card {
        background: linear-gradient(145deg, #1e1b4b 0%, #2e1065 100%);
        border-radius: var(--dm-radius-lg);
        padding: 1.25rem;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .dm-sub-card::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 130px; height: 130px;
        border-radius: 50%;
        background: rgba(255,255,255,.05);
    }
    .dm-sub-card::after {
        content: '';
        position: absolute;
        bottom: -30px; left: 20px;
        width: 90px; height: 90px;
        border-radius: 50%;
        background: rgba(255,255,255,.04);
    }
    .dm-sub-plan { font-size: 1.4rem; font-weight: 900; letter-spacing: -.02em; }
    .dm-sub-label { font-size: .65rem; font-weight: 700; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .07em; margin-bottom: .25rem; }
    .dm-sub-status {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .22rem .7rem; border-radius: 99px; font-size: .63rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em;
    }
    .dm-sub-status--active { background: rgba(16,185,129,.2); color: #6ee7b7; border: 1px solid rgba(16,185,129,.3); }
    .dm-sub-status--inactive { background: rgba(255,255,255,.1); color: rgba(255,255,255,.5); border: 1px solid rgba(255,255,255,.15); }
    .dm-sub-rows { margin-top: 1rem; display: flex; flex-direction: column; gap: .5rem; }
    .dm-sub-row { display: flex; justify-content: space-between; align-items: center; font-size: .78rem; }
    .dm-sub-row-lbl { color: rgba(255,255,255,.45); font-weight: 500; }
    .dm-sub-row-val { font-weight: 700; color: #fff; }
    .dm-sub-row--expire .dm-sub-row-val { color: #fbbf24; }

    /* Progress bar */
    .dm-progress { height: 4px; background: rgba(255,255,255,.12); border-radius: 99px; margin-top: .65rem; overflow: hidden; }
    .dm-progress-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #818cf8, #a78bfa); transition: width .4s; }

    /* ── KPI row ────────────────────────────────────────── */
    .dm-kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
    @media (max-width: 700px) { .dm-kpi-row { grid-template-columns: repeat(2, 1fr); } }

    .dm-kpi {
        background: var(--dm-white);
        border: 1px solid var(--dm-border);
        border-radius: var(--dm-radius);
        padding: 1rem 1.1rem;
        box-shadow: var(--dm-shadow-sm);
    }
    .dm-kpi-top { display: flex; align-items: center; gap: .5rem; margin-bottom: .6rem; }
    .dm-kpi-icon {
        width: 32px; height: 32px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: .78rem;
    }
    .dm-kpi-lbl { font-size: .63rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--dm-ink-3); }
    .dm-kpi-val { font-size: 1.5rem; font-weight: 900; color: var(--dm-ink); letter-spacing: -.02em; line-height: 1; }

    /* ── Professional info ──────────────────────────────── */
    .dm-info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
    @media (max-width: 620px) { .dm-info-grid { grid-template-columns: 1fr 1fr; } }

    .dm-info-item {}
    .dm-info-lbl { font-size: .63rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--dm-ink-3); margin-bottom: .3rem; }
    .dm-info-val { font-size: .88rem; font-weight: 700; color: var(--dm-ink); }
    .dm-info-val--mono { font-family: monospace; font-size: .82rem; }

    /* Bio block */
    .dm-bio {
        background: var(--dm-surface);
        border: 1px solid var(--dm-border);
        border-radius: var(--dm-radius-sm);
        padding: 1rem 1.1rem;
        font-size: .84rem;
        color: var(--dm-ink-2);
        line-height: 1.65;
        font-weight: 500;
    }

    /* ── Properties table ───────────────────────────────── */
    .dm-table { width: 100%; border-collapse: collapse; }
    .dm-table thead th {
        padding: .6rem 1.1rem;
        font-size: .63rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .07em; color: var(--dm-ink-3);
        background: var(--dm-surface); text-align: left;
        border-bottom: 1px solid var(--dm-border);
    }
    .dm-table tbody tr { transition: background .12s; border-bottom: 1px solid var(--dm-border); }
    .dm-table tbody tr:last-child { border-bottom: none; }
    .dm-table tbody tr:hover { background: var(--dm-surface); }
    .dm-table td { padding: .75rem 1.1rem; font-size: .82rem; color: var(--dm-ink-2); vertical-align: middle; }

    .dm-prop-thumb {
        width: 40px; height: 40px; border-radius: 9px;
        object-fit: cover; border: 1px solid var(--dm-border);
        background: var(--dm-surface); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
    }
    .dm-prop-name { font-weight: 800; color: var(--dm-ink); font-size: .84rem; max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dm-prop-date { font-size: .68rem; color: var(--dm-ink-3); font-weight: 500; }

    .dm-status-pill {
        display: inline-flex; align-items: center;
        padding: .2rem .6rem; border-radius: 99px;
        font-size: .63rem; font-weight: 800; text-transform: uppercase; letter-spacing: .03em;
    }
    .dm-status--available { background: var(--dm-green-lt); color: #065f46; }
    .dm-status--sold      { background: var(--dm-blue-lt);  color: #1e40af; }
    .dm-status--rented    { background: #f5f3ff;            color: #5b21b6; }
    .dm-status--pending   { background: var(--dm-amber-lt); color: #92400e; }
    .dm-status--default   { background: var(--dm-surface);  color: var(--dm-ink-3); }

    .dm-icon-btn {
        width: 32px; height: 32px; border-radius: 8px;
        border: 1px solid var(--dm-border); background: var(--dm-white);
        display: inline-flex; align-items: center; justify-content: center;
        color: var(--dm-ink-3); font-size: .78rem; cursor: pointer;
        transition: all .13s; text-decoration: none;
    }
    .dm-icon-btn:hover { color: var(--dm-accent); background: var(--dm-accent-lt); border-color: #c7d2fe; }

    .dm-empty {
        padding: 2.5rem; text-align: center; color: var(--dm-ink-3);
        font-size: .84rem; font-weight: 500;
    }
    .dm-empty i { font-size: 1.8rem; display: block; margin-bottom: .5rem; opacity: .4; }

    /* ── Grid layout ─────────────────────────────────────── */
    .dm-show-grid { display: grid; grid-template-columns: 300px 1fr; gap: 1.5rem; }
    @media (max-width: 900px) { .dm-show-grid { grid-template-columns: 1fr; } }

    .dm-left  { display: flex; flex-direction: column; gap: 1.25rem; }
    .dm-right { display: flex; flex-direction: column; gap: 1.25rem; }

    .dm-divider { border: none; border-top: 1px solid var(--dm-border); margin: .9rem 0; }
</style>

<div class="dm-show-wrap">

    {{-- ── Top Bar ──────────────────────────────────────────── --}}
    <div class="dm-topbar">
        <div>
            <div class="dm-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.agents.index') }}">Agents</a>
                <span>/</span>
                Profile
            </div>
            <h1 class="dm-page-title">
                {{ $agent->agent_name }}
                @if($agent->is_verified)
                    <span class="dm-badge dm-badge--verified"><i class="fas fa-check-circle" style="font-size:.55rem"></i> Verified</span>
                @else
                    <span class="dm-badge dm-badge--pending"><i class="fas fa-clock" style="font-size:.55rem"></i> Pending Review</span>
                @endif
            </h1>
        </div>

        <div class="dm-topbar-actions">
            <a href="{{ route('admin.agents.index') }}" class="dm-btn dm-btn--ghost">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('admin.agents.edit', $agent->id) }}" class="dm-btn dm-btn--primary">
                <i class="fas fa-pen"></i> Edit Profile
            </a>

            {{-- Actions dropdown --}}
            <div class="dm-dropdown">
                <button class="dm-btn dm-btn--ghost" style="padding:.55rem .75rem">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dm-dropdown-menu">
                    @if(!$agent->is_verified)
                    <form action="{{ route('admin.agents.verify', $agent->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="dm-dropdown-item" style="color:var(--dm-green)">
                            <i class="fas fa-check-circle"></i> Approve Agent
                        </button>
                    </form>
                    <div class="dm-dropdown-divider"></div>
                    @endif
                    <a href="{{ route('admin.properties.create') }}?agent_id={{ $agent->id }}" class="dm-dropdown-item">
                        <i class="fas fa-plus-circle"></i> Add Property
                    </a>
                    <div class="dm-dropdown-divider"></div>
                    <form action="{{ route('admin.agents.delete', $agent->id) }}" method="POST"
                          onsubmit="return confirm('Delete {{ $agent->agent_name }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="dm-dropdown-item dm-dropdown-item--danger">
                            <i class="fas fa-trash-alt"></i> Delete Agent
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Grid ─────────────────────────────────────────── --}}
    <div class="dm-show-grid">

        {{-- ══ LEFT ════════════════════════════════════════════ --}}
        <div class="dm-left">

            {{-- Profile identity card --}}
            <div class="dm-card">
                {{-- Hero banner --}}
                <div class="dm-hero">
                    @if($agent->bio_image)
                        <img src="{{ asset($agent->bio_image) }}" alt="">
                    @endif
                    <div class="dm-hero-overlay"></div>
                </div>

                {{-- Identity --}}
                <div class="dm-profile-card">
                    <div class="dm-profile-inner">
                        <div class="dm-avatar-wrap">
                            @if($agent->profile_image)
                                <img class="dm-avatar" src="{{ asset($agent->profile_image) }}" alt="{{ $agent->agent_name }}">
                            @else
                                <div class="dm-avatar-init">{{ strtoupper(substr($agent->agent_name, 0, 1)) }}</div>
                            @endif
                            <div class="dm-type-badge">
                                @if($agent->type === 'company')
                                    <i class="fas fa-building" style="color:var(--dm-accent)"></i>
                                @else
                                    <i class="fas fa-user-tie" style="color:var(--dm-blue)"></i>
                                @endif
                            </div>
                        </div>

                        <div class="dm-agent-name">{{ $agent->agent_name }}</div>
                        <div class="dm-agent-sub">
                            @if($agent->company_name) {{ $agent->company_name }} · @endif
                            {{ $agent->city ?? 'Location unknown' }}
                            @if($agent->district) · {{ $agent->district }} @endif
                        </div>

                        <div class="dm-stat-bar">
                            <div class="dm-stat-item">
                                <div class="dm-stat-val">{{ $agent->years_experience ?? 0 }}</div>
                                <div class="dm-stat-lbl">Yrs Exp.</div>
                            </div>
                            <div class="dm-stat-item">
                                <div class="dm-stat-val" style="display:flex;align-items:center;justify-content:center;gap:.3rem">
                                    {{ number_format((float)($agent->overall_rating ?? 0), 1) }}
                                    <i class="fas fa-star" style="color:var(--dm-amber);font-size:.75rem"></i>
                                </div>
                                <div class="dm-stat-lbl">Rating</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Details --}}
            <div class="dm-card">
                <div class="dm-card-head">
                    <span class="dm-card-title"><i class="fas fa-address-card"></i> Contact</span>
                </div>
                <div class="dm-card-body" style="padding:.5rem">
                    <div class="dm-contact-list">
                        <a href="mailto:{{ $agent->primary_email }}" class="dm-contact-row">
                            <div class="dm-contact-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <div class="dm-contact-lbl">Email</div>
                                <div class="dm-contact-val">{{ $agent->primary_email }}</div>
                            </div>
                        </a>
                        @if($agent->primary_phone)
                        <a href="tel:{{ $agent->primary_phone }}" class="dm-contact-row">
                            <div class="dm-contact-icon"><i class="fas fa-phone"></i></div>
                            <div>
                                <div class="dm-contact-lbl">Phone</div>
                                <div class="dm-contact-val">{{ $agent->primary_phone }}</div>
                            </div>
                        </a>
                        @endif
                        @if($agent->whatsapp_number)
                        <a href="https://wa.me/{{ $agent->whatsapp_number }}" target="_blank" class="dm-contact-row">
                            <div class="dm-contact-icon" style="color:#25d366"><i class="fab fa-whatsapp"></i></div>
                            <div>
                                <div class="dm-contact-lbl">WhatsApp</div>
                                <div class="dm-contact-val">{{ $agent->whatsapp_number }}</div>
                            </div>
                        </a>
                        @endif
                        @if($agent->office_address)
                        <div class="dm-contact-row">
                            <div class="dm-contact-icon"><i class="fas fa-map-pin"></i></div>
                            <div>
                                <div class="dm-contact-lbl">Office</div>
                                <div class="dm-contact-val">{{ $agent->office_address }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Subscription Card --}}
            <div class="dm-sub-card">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.75rem">
                    <div>
                        <div class="dm-sub-label">Current Plan</div>
                        <div class="dm-sub-plan">
                            {{ ucfirst($agent->current_plan ?? ($agent->subscription->currentPlan->name ?? 'Free')) }}
                        </div>
                    </div>
                    @if($agent->subscription && $agent->subscription->status === 'active')
                        <span class="dm-sub-status dm-sub-status--active"><i class="fas fa-circle" style="font-size:.45rem"></i> Active</span>
                    @else
                        <span class="dm-sub-status dm-sub-status--inactive">Inactive</span>
                    @endif
                </div>

                @if($agent->subscription)
                <div class="dm-sub-rows">
                    <div class="dm-sub-row">
                        <span class="dm-sub-row-lbl">Property limit</span>
                        <span class="dm-sub-row-val">
                            {{ $agent->subscription->property_activation_limit > 0 ? $agent->subscription->property_activation_limit : '∞ Unlimited' }}
                        </span>
                    </div>
                    <div class="dm-sub-row">
                        <span class="dm-sub-row-lbl">Used this month</span>
                        <span class="dm-sub-row-val">{{ $agent->subscription->properties_activated_this_month ?? 0 }}</span>
                    </div>
                    <div class="dm-sub-row dm-sub-row--expire">
                        <span class="dm-sub-row-lbl">Expires</span>
                        <span class="dm-sub-row-val">{{ $agent->subscription->end_date ? $agent->subscription->end_date->format('d M Y') : 'N/A' }}</span>
                    </div>
                </div>

                @php
                    $limit = $agent->subscription->property_activation_limit;
                    $used  = $agent->subscription->properties_activated_this_month ?? 0;
                    $pct   = ($limit > 0) ? min(100, round(($used / $limit) * 100)) : 0;
                @endphp
                @if($limit > 0)
                <div class="dm-progress" style="margin-top:.9rem">
                    <div class="dm-progress-fill" style="width:{{ $pct }}%"></div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:.3rem;font-size:.65rem;color:rgba(255,255,255,.35);font-weight:600">
                    <span>{{ $used }} used</span>
                    <span>{{ $limit }} total</span>
                </div>
                @endif

                @else
                <div style="text-align:center;padding:1.25rem 0">
                    <p style="font-size:.8rem;color:rgba(255,255,255,.4);margin-bottom:.75rem">No active subscription.</p>
                    <a href="{{ route('admin.agents.edit', $agent->id) }}" class="dm-btn dm-btn--ghost"
                       style="color:rgba(255,255,255,.6);border-color:rgba(255,255,255,.15);background:rgba(255,255,255,.06)">
                        Assign Plan
                    </a>
                </div>
                @endif
            </div>

        </div>{{-- /.dm-left --}}

        {{-- ══ RIGHT ═══════════════════════════════════════════ --}}
        <div class="dm-right">

            {{-- KPI Row --}}
            <div class="dm-kpi-row">
                <div class="dm-kpi">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-icon" style="background:var(--dm-blue-lt);color:var(--dm-blue)"><i class="fas fa-home"></i></div>
                        <span class="dm-kpi-lbl">Listings</span>
                    </div>
                    <div class="dm-kpi-val">{{ $agent->properties->count() }}</div>
                </div>
                <div class="dm-kpi">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-icon" style="background:var(--dm-green-lt);color:var(--dm-green)"><i class="fas fa-handshake"></i></div>
                        <span class="dm-kpi-lbl">Sold</span>
                    </div>
                    <div class="dm-kpi-val">{{ $agent->properties_sold ?? 0 }}</div>
                </div>
                <div class="dm-kpi">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-eye"></i></div>
                        <span class="dm-kpi-lbl">Views</span>
                    </div>
                    <div class="dm-kpi-val">{{ number_format((float)($agent->properties->sum('views') ?? 0)) }}</div>
                </div>
                <div class="dm-kpi">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-icon" style="background:var(--dm-amber-lt);color:var(--dm-amber)"><i class="fas fa-percent"></i></div>
                        <span class="dm-kpi-lbl">Commission</span>
                    </div>
                    <div class="dm-kpi-val" style="font-size:1.25rem">{{ (float)($agent->commission_rate ?? 0) }}<span style="font-size:.8rem;font-weight:700;color:var(--dm-ink-3)">%</span></div>
                </div>
            </div>

            {{-- Professional Information --}}
            <div class="dm-card">
                <div class="dm-card-head">
                    <span class="dm-card-title"><i class="fas fa-briefcase"></i> Professional Information</span>
                </div>
                <div class="dm-card-body">
                    <div class="dm-info-grid">
                        <div class="dm-info-item">
                            <div class="dm-info-lbl">Company / Agency</div>
                            <div class="dm-info-val">{{ $agent->company_name ?? 'Independent' }}</div>
                        </div>
                        <div class="dm-info-item">
                            <div class="dm-info-lbl">License Number</div>
                            <div class="dm-info-val dm-info-val--mono">{{ $agent->license_number ?? 'Not provided' }}</div>
                        </div>
                        <div class="dm-info-item">
                            <div class="dm-info-lbl">Consultation Fee</div>
                            <div class="dm-info-val">
                                @if($agent->consultation_fee > 0)
                                    {{ number_format((float)$agent->consultation_fee) }} {{ $agent->currency }}
                                @else
                                    <span style="color:var(--dm-green);font-weight:800">Free</span>
                                @endif
                            </div>
                        </div>
                        <div class="dm-info-item">
                            <div class="dm-info-lbl">Employment</div>
                            <div class="dm-info-val">{{ $agent->employment_status ?? '—' }}</div>
                        </div>
                        <div class="dm-info-item">
                            <div class="dm-info-lbl">Currency</div>
                            <div class="dm-info-val">{{ $agent->currency ?? 'USD' }}</div>
                        </div>
                        <div class="dm-info-item">
                            <div class="dm-info-lbl">Agent Type</div>
                            <div class="dm-info-val" style="text-transform:capitalize">{{ $agent->type ?? '—' }}</div>
                        </div>
                    </div>

                    @if($agent->agent_bio)
                    <hr class="dm-divider">
                    <div class="dm-info-lbl" style="margin-bottom:.5rem">About</div>
                    <div class="dm-bio">{{ $agent->agent_bio }}</div>
                    @endif
                </div>
            </div>

            {{-- Recent Properties --}}
            <div class="dm-card">
                <div class="dm-card-head">
                    <span class="dm-card-title"><i class="fas fa-home"></i> Recent Properties</span>
                    <a href="{{ route('admin.properties.index', ['owner_type' => 'Agent', 'search' => $agent->agent_name]) }}"
                       style="font-size:.72rem;font-weight:800;color:var(--dm-accent);text-decoration:none">
                        View all →
                    </a>
                </div>

                @if($agent->properties->count())
                <div style="overflow-x:auto">
                    <table class="dm-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th style="text-align:center">Status</th>
                                <th style="text-align:right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($agent->properties->take(6) as $property)
                            @php
                                $nameData  = is_string($property->name)   ? json_decode($property->name, true)   : $property->name;
                                $propName  = is_array($nameData) ? ($nameData['en'] ?? $nameData['ar'] ?? 'Property') : ($nameData ?? 'Property');

                                $imageData = is_string($property->images) ? json_decode($property->images, true) : $property->images;
                                $firstImg  = is_array($imageData) ? ($imageData[0] ?? null) : null;

                                $typeData  = is_string($property->type)   ? json_decode($property->type, true)   : $property->type;
                                $category  = is_array($typeData)  ? ($typeData['category'] ?? 'N/A') : ($typeData ?? 'N/A');

                                $priceData = is_string($property->price)  ? json_decode($property->price, true)  : $property->price;
                                $priceVal  = is_array($priceData) ? ($priceData['usd'] ?? 0) : ($priceData ?? 0);

                                $statusCls = match($property->status ?? '') {
                                    'available' => 'dm-status--available',
                                    'sold'      => 'dm-status--sold',
                                    'rented'    => 'dm-status--rented',
                                    'pending'   => 'dm-status--pending',
                                    default     => 'dm-status--default',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:.75rem">
                                        <div class="dm-prop-thumb">
                                            @if($firstImg)
                                                <img src="{{ $firstImg }}" alt="" style="width:100%;height:100%;object-fit:cover">
                                            @else
                                                <i class="fas fa-home" style="color:var(--dm-ink-3);font-size:.8rem"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="dm-prop-name">{{ $propName }}</div>
                                            <div class="dm-prop-date">{{ $property->created_at->format('d M Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-weight:700;text-transform:capitalize">{{ $category }}</td>
                                <td style="font-weight:800;color:var(--dm-ink)">${{ number_format((float)$priceVal) }}</td>
                                <td style="text-align:center">
                                    <span class="dm-status-pill {{ $statusCls }}">{{ $property->status ?? 'N/A' }}</span>
                                </td>
                                <td style="text-align:right">
                                    <a href="{{ route('admin.properties.show', $property->id) }}" class="dm-icon-btn" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="dm-empty">
                    <i class="fas fa-home"></i>
                    No properties listed yet.
                </div>
                @endif
            </div>

        </div>{{-- /.dm-right --}}
    </div>
</div>

@endsection
