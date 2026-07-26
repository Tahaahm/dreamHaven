@extends('layouts.admin-layout')

@section('title', 'Edit Agent — ' . $agent->agent_name)

@section('content')

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- DREAMMULK · AGENT EDIT · Redesigned UI                    --}}
{{-- ══════════════════════════════════════════════════════════ --}}

<style>
    /* ── Token System ─────────────────────────────────────── */
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
        --dm-radius-sm: 10px;
        --dm-radius:    14px;
        --dm-radius-lg: 20px;
        --dm-shadow-sm: 0 1px 3px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.04);
        --dm-shadow:    0 4px 12px rgba(15,23,42,.08), 0 1px 3px rgba(15,23,42,.04);
    }

    /* ── Layout ───────────────────────────────────────────── */
    .dm-edit-wrap   { max-width: 1080px; margin: 0 auto; padding: 0 0 3rem; }
    .dm-edit-grid   { display: grid; grid-template-columns: 300px 1fr; gap: 1.5rem; }

    @media (max-width: 900px) {
        .dm-edit-grid { grid-template-columns: 1fr; }
    }

    /* ── Topbar ───────────────────────────────────────────── */
    .dm-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.5rem 0 1.75rem;
        flex-wrap: wrap;
    }
    .dm-breadcrumb {
        display: flex;
        align-items: center;
        gap: .35rem;
        font-size: .72rem;
        font-weight: 600;
        color: var(--dm-ink-3);
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: .35rem;
    }
    .dm-breadcrumb a { color: var(--dm-ink-3); text-decoration: none; }
    .dm-breadcrumb a:hover { color: var(--dm-ink); }
    .dm-breadcrumb span { color: var(--dm-border); }
    .dm-page-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--dm-ink);
        letter-spacing: -.02em;
        display: flex;
        align-items: center;
        gap: .6rem;
        line-height: 1.2;
    }
    .dm-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .2rem .6rem;
        border-radius: 99px;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .dm-badge--verified { background: var(--dm-green-lt); color: #065f46; border: 1px solid #a7f3d0; }
    .dm-badge--pending  { background: var(--dm-amber-lt); color: #92400e; border: 1px solid #fcd34d; }

    .dm-topbar-actions { display: flex; gap: .6rem; flex-wrap: wrap; }

    /* ── Shared card ─────────────────────────────────────── */
    .dm-card {
        background: var(--dm-white);
        border: 1px solid var(--dm-border);
        border-radius: var(--dm-radius-lg);
        box-shadow: var(--dm-shadow-sm);
        overflow: hidden;
    }
    .dm-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.4rem;
        border-bottom: 1px solid var(--dm-border);
        background: var(--dm-surface);
    }
    .dm-card-title {
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--dm-ink-2);
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .dm-card-title i { color: var(--dm-ink-3); }
    .dm-card-body { padding: 1.4rem; }

    /* ── Left Sidebar ─────────────────────────────────────── */
    .dm-sidebar { display: flex; flex-direction: column; gap: 1.25rem; }

    /* Photo upload */
    .dm-photo-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.75rem 1.4rem;
        gap: 1rem;
    }
    .dm-avatar-upload {
        position: relative;
        width: 110px;
        height: 110px;
        cursor: pointer;
    }
    .dm-avatar-upload img,
    .dm-avatar-upload .dm-avatar-placeholder {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--dm-white);
        box-shadow: var(--dm-shadow);
        display: block;
    }
    .dm-avatar-placeholder {
        background: var(--dm-surface);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--dm-ink-3);
        font-size: 2rem;
    }
    .dm-avatar-upload input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        border-radius: 50%;
    }
    .dm-avatar-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15,23,42,.45);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .18s;
    }
    .dm-avatar-upload:hover .dm-avatar-overlay { opacity: 1; }
    .dm-avatar-overlay i { color: #fff; font-size: 1.1rem; }

    .dm-photo-hint {
        text-align: center;
        font-size: .7rem;
        font-weight: 600;
        color: var(--dm-ink-3);
        line-height: 1.5;
    }

    /* Cover image */
    .dm-cover-upload {
        position: relative;
        width: 100%;
        height: 100px;
        border-radius: var(--dm-radius-sm);
        overflow: hidden;
        border: 2px dashed var(--dm-border);
        background: var(--dm-surface);
        cursor: pointer;
        transition: border-color .15s;
    }
    .dm-cover-upload:hover { border-color: var(--dm-accent); }
    .dm-cover-upload input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }
    .dm-cover-upload img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .dm-cover-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        color: var(--dm-ink-3);
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .dm-cover-placeholder i { font-size: 1.1rem; }

    /* Nav tabs (sidebar sections) */
    .dm-section-nav {
        display: flex;
        flex-direction: column;
        gap: .2rem;
        padding: .5rem;
    }
    .dm-nav-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .65rem .9rem;
        border-radius: var(--dm-radius-sm);
        font-size: .8rem;
        font-weight: 700;
        color: var(--dm-ink-2);
        text-decoration: none;
        cursor: pointer;
        border: none;
        background: transparent;
        width: 100%;
        text-align: left;
        transition: background .13s, color .13s;
    }
    .dm-nav-item:hover { background: var(--dm-surface); color: var(--dm-ink); }
    .dm-nav-item.active { background: var(--dm-accent-lt); color: var(--dm-accent); }
    .dm-nav-item i { width: 16px; text-align: center; font-size: .85rem; }
    .dm-nav-dot {
        margin-left: auto;
        width: 6px;
        height: 6px;
        border-radius: 99px;
        background: var(--dm-red);
    }

    /* ── Main Content ─────────────────────────────────────── */
    .dm-main { display: flex; flex-direction: column; gap: 1.25rem; }

    /* Section panel */
    .dm-section {
        animation: dm-fadein .22s ease;
    }
    @keyframes dm-fadein {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Form fields */
    .dm-fields { display: grid; gap: .9rem; }
    .dm-fields-2 { grid-template-columns: 1fr 1fr; }
    .dm-fields-3 { grid-template-columns: 1fr 1fr 1fr; }

    @media (max-width: 660px) {
        .dm-fields-2, .dm-fields-3 { grid-template-columns: 1fr; }
    }

    .dm-field label {
        display: block;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--dm-ink-2);
        margin-bottom: .4rem;
    }
    .dm-field label .req { color: var(--dm-red); margin-left: 2px; }

    .dm-input,
    .dm-select,
    .dm-textarea {
        width: 100%;
        padding: .65rem 1rem;
        background: var(--dm-surface);
        border: 1.5px solid var(--dm-border);
        border-radius: var(--dm-radius-sm);
        font-size: .84rem;
        font-weight: 600;
        color: var(--dm-ink);
        transition: border-color .15s, box-shadow .15s;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
    }
    .dm-input:focus,
    .dm-select:focus,
    .dm-textarea:focus {
        border-color: var(--dm-accent);
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
        background: var(--dm-white);
    }
    .dm-textarea { resize: vertical; min-height: 100px; }

    .dm-input-icon {
        position: relative;
    }
    .dm-input-icon .icon {
        position: absolute;
        left: .9rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--dm-ink-3);
        pointer-events: none;
    }
    .dm-input-icon .dm-input { padding-left: 2.3rem; }
    .dm-input-icon .icon-right {
        left: auto;
        right: .9rem;
        color: var(--dm-ink-3);
        font-size: .85rem;
    }
    .dm-input-icon .dm-input.has-right { padding-right: 2.3rem; }

    .dm-select-wrap { position: relative; }
    .dm-select-wrap .chevron {
        position: absolute;
        right: .9rem;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: var(--dm-ink-3);
        font-size: .75rem;
    }

    .dm-field-hint {
        font-size: .68rem;
        font-weight: 500;
        color: var(--dm-ink-3);
        margin-top: .3rem;
        line-height: 1.4;
    }

    /* Toggle switch */
    .dm-toggle-wrap {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .75rem 1rem;
        background: var(--dm-surface);
        border: 1.5px solid var(--dm-border);
        border-radius: var(--dm-radius-sm);
        cursor: pointer;
        user-select: none;
    }
    .dm-toggle-wrap input { display: none; }
    .dm-toggle-slider {
        position: relative;
        width: 40px;
        height: 22px;
        background: var(--dm-border);
        border-radius: 99px;
        transition: background .18s;
        flex-shrink: 0;
    }
    .dm-toggle-slider::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        background: var(--dm-white);
        border-radius: 50%;
        top: 3px;
        left: 3px;
        transition: left .18s;
        box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .dm-toggle-wrap input:checked ~ .dm-toggle-slider { background: var(--dm-green); }
    .dm-toggle-wrap input:checked ~ .dm-toggle-slider::after { left: 21px; }
    .dm-toggle-label { font-size: .82rem; font-weight: 700; color: var(--dm-ink); }
    .dm-toggle-sub   { font-size: .7rem; font-weight: 500; color: var(--dm-ink-3); }

    /* Plan cards */
    .dm-plan-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: .75rem;
    }
    .dm-plan-card {
        position: relative;
        border: 2px solid var(--dm-border);
        border-radius: var(--dm-radius);
        padding: 1rem;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s;
        background: var(--dm-white);
    }
    .dm-plan-card:hover { border-color: var(--dm-accent); }
    .dm-plan-card input[type="radio"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }
    .dm-plan-card.selected {
        border-color: var(--dm-accent);
        background: var(--dm-accent-lt);
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
    }
    .dm-plan-name { font-size: .82rem; font-weight: 800; color: var(--dm-ink); margin-bottom: .2rem; }
    .dm-plan-price { font-size: 1.15rem; font-weight: 900; color: var(--dm-accent); }
    .dm-plan-price span { font-size: .68rem; font-weight: 600; color: var(--dm-ink-3); }
    .dm-plan-dur { font-size: .68rem; font-weight: 600; color: var(--dm-ink-3); margin-top: .15rem; }
    .dm-plan-check {
        position: absolute;
        top: .6rem;
        right: .6rem;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--dm-accent);
        color: #fff;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: .55rem;
    }
    .dm-plan-card.selected .dm-plan-check { display: flex; }

    /* Active sub banner */
    .dm-sub-active {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .9rem 1.1rem;
        border-radius: var(--dm-radius-sm);
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        color: #fff;
        margin-bottom: 1rem;
    }
    .dm-sub-active-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: .9rem;
    }
    .dm-sub-active-name { font-size: .9rem; font-weight: 800; }
    .dm-sub-active-meta { font-size: .7rem; color: rgba(255,255,255,.6); }
    .dm-sub-active-badge {
        margin-left: auto;
        padding: .2rem .6rem;
        border-radius: 99px;
        font-size: .62rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        background: rgba(16,185,129,.2);
        color: #6ee7b7;
        border: 1px solid rgba(16,185,129,.3);
    }

    /* Inline stat row */
    .dm-stat-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .75rem;
    }
    .dm-stat-mini {
        padding: .8rem;
        background: var(--dm-surface);
        border: 1px solid var(--dm-border);
        border-radius: var(--dm-radius-sm);
        text-align: center;
    }
    .dm-stat-mini-val { font-size: 1.1rem; font-weight: 900; color: var(--dm-ink); }
    .dm-stat-mini-lbl { font-size: .64rem; font-weight: 700; color: var(--dm-ink-3); text-transform: uppercase; letter-spacing: .04em; }

    /* Divider */
    .dm-divider {
        border: none;
        border-top: 1px solid var(--dm-border);
        margin: 1.1rem 0;
    }

    /* Buttons */
    .dm-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .6rem 1.2rem;
        border-radius: var(--dm-radius-sm);
        font-size: .8rem;
        font-weight: 800;
        cursor: pointer;
        border: none;
        transition: all .15s;
        text-decoration: none;
        white-space: nowrap;
    }
    .dm-btn--primary {
        background: var(--dm-ink);
        color: #fff;
        box-shadow: 0 2px 6px rgba(15,23,42,.2);
    }
    .dm-btn--primary:hover { background: #1e293b; box-shadow: 0 4px 12px rgba(15,23,42,.25); }
    .dm-btn--accent {
        background: var(--dm-accent);
        color: #fff;
        box-shadow: 0 2px 6px rgba(99,102,241,.25);
    }
    .dm-btn--accent:hover { background: #4f46e5; }
    .dm-btn--ghost {
        background: var(--dm-white);
        border: 1.5px solid var(--dm-border);
        color: var(--dm-ink-2);
    }
    .dm-btn--ghost:hover { background: var(--dm-surface); color: var(--dm-ink); }
    .dm-btn--green {
        background: var(--dm-green);
        color: #fff;
        box-shadow: 0 2px 6px rgba(16,185,129,.25);
    }
    .dm-btn--green:hover { background: #059669; }

    /* Sticky save bar */
    .dm-save-bar {
        position: sticky;
        bottom: 1.5rem;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .9rem 1.25rem;
        background: var(--dm-ink);
        border-radius: var(--dm-radius);
        box-shadow: 0 8px 30px rgba(15,23,42,.25);
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }
    .dm-save-bar-text { font-size: .78rem; font-weight: 600; color: rgba(255,255,255,.6); }
    .dm-save-bar-text strong { color: #fff; font-weight: 800; }

    /* Error alert */
    .dm-errors {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: var(--dm-radius-sm);
        padding: .9rem 1.1rem;
        margin-bottom: 1.25rem;
    }
    .dm-errors ul { margin: .4rem 0 0; padding-left: 1.2rem; }
    .dm-errors li { font-size: .8rem; color: #991b1b; font-weight: 600; }
</style>

<div class="dm-edit-wrap">

    {{-- ── Validation Errors ──────────────────────────────── --}}
    @if($errors->any())
    <div class="dm-errors">
        <p style="font-size:.8rem;font-weight:800;color:#b91c1c;margin:0;">
            <i class="fas fa-exclamation-circle mr-1"></i> Please fix the following errors:
        </p>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── Top Bar ─────────────────────────────────────────── --}}
    <div class="dm-topbar">
        <div>
            <div class="dm-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.agents.index') }}">Agents</a>
                <span>/</span>
                Edit Profile
            </div>
            <h1 class="dm-page-title">
                {{ $agent->agent_name }}
                @if($agent->is_verified)
                    <span class="dm-badge dm-badge--verified"><i class="fas fa-check-circle" style="font-size:.55rem"></i> Verified</span>
                @else
                    <span class="dm-badge dm-badge--pending"><i class="fas fa-clock" style="font-size:.55rem"></i> Pending</span>
                @endif
            </h1>
        </div>
        <div class="dm-topbar-actions">
            <a href="{{ route('admin.properties.create') }}?agent_id={{ $agent->id }}" class="dm-btn dm-btn--green">
                <i class="fas fa-plus"></i> Add Property
            </a>
            <a href="{{ route('admin.agents.show', $agent->id) }}" class="dm-btn dm-btn--ghost">
                <i class="fas fa-eye"></i> View Profile
            </a>
            <a href="{{ route('admin.agents.index') }}" class="dm-btn dm-btn--ghost">
                Cancel
            </a>
        </div>
    </div>

    {{-- ── Two-Column Layout ────────────────────────────────── --}}
    <form id="editAgentForm" method="POST" action="{{ route('admin.agents.update', $agent->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="dm-edit-grid">

            {{-- ────────────────────────────────────────────── --}}
            {{-- LEFT: Sidebar                                  --}}
            {{-- ────────────────────────────────────────────── --}}
            <div class="dm-sidebar">

                {{-- Avatar + Cover --}}
                <div class="dm-card">
                    <div class="dm-photo-wrap">
                        {{-- Profile Photo --}}
                        <div class="dm-avatar-upload">
                            @if($agent->profile_image)
                                <img id="profilePreview" src="{{ asset($agent->profile_image) }}" alt="{{ $agent->agent_name }}">
                            @else
                                <div class="dm-avatar-placeholder" id="profilePlaceholder">
                                    <i class="fas fa-user"></i>
                                </div>
                                <img id="profilePreview" class="hidden" alt="">
                            @endif
                            <div class="dm-avatar-overlay"><i class="fas fa-camera"></i></div>
                            <input type="file" name="profile_image" accept="image/*"
                                   onchange="previewImage(this,'profilePreview','profilePlaceholder')">
                        </div>
                        <div class="dm-photo-hint">
                            Profile photo<br>
                            <span style="font-weight:500">JPG / PNG · Max 2 MB</span>
                        </div>

                        <hr class="dm-divider" style="width:100%">

                        {{-- Cover Image --}}
                        <div style="width:100%">
                            <div class="dm-card-title" style="margin-bottom:.55rem">
                                <i class="fas fa-image"></i> Cover Image
                            </div>
                            <div class="dm-cover-upload">
                                @if($agent->bio_image)
                                    <img id="bioPreview" src="{{ asset($agent->bio_image) }}" alt="">
                                @else
                                    <div class="dm-cover-placeholder" id="bioPlaceholder">
                                        <i class="fas fa-image"></i>
                                        <span>Click to upload</span>
                                    </div>
                                    <img id="bioPreview" class="hidden" alt="">
                                @endif
                                <input type="file" name="bio_image" accept="image/*"
                                       onchange="previewImage(this,'bioPreview','bioPlaceholder')">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Nav --}}
                <div class="dm-card">
                    <nav class="dm-section-nav" aria-label="Form sections">
                        <button type="button" class="dm-nav-item active" onclick="showSection('identity',this)">
                            <i class="fas fa-id-card"></i> Identity
                        </button>
                        <button type="button" class="dm-nav-item" onclick="showSection('professional',this)">
                            <i class="fas fa-briefcase"></i> Professional
                        </button>
                        <button type="button" class="dm-nav-item" onclick="showSection('location',this)">
                            <i class="fas fa-map-pin"></i> Location
                        </button>
                        <button type="button" class="dm-nav-item" onclick="showSection('billing',this)">
                            <i class="fas fa-credit-card"></i> Plan & Billing
                        </button>
                        <button type="button" class="dm-nav-item" onclick="showSection('system',this)">
                            <i class="fas fa-sliders"></i> System
                        </button>
                    </nav>
                </div>

                {{-- Quick Stats --}}
                <div class="dm-card">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-chart-bar"></i> Quick Stats</span>
                    </div>
                    <div class="dm-card-body" style="padding:1rem">
                        <div class="dm-stat-row">
                            <div class="dm-stat-mini">
                                <div class="dm-stat-mini-val">{{ $agent->properties->count() ?? 0 }}</div>
                                <div class="dm-stat-mini-lbl">Listings</div>
                            </div>
                            <div class="dm-stat-mini">
                                <div class="dm-stat-mini-val">{{ $agent->properties_sold ?? 0 }}</div>
                                <div class="dm-stat-mini-lbl">Sold</div>
                            </div>
                            <div class="dm-stat-mini">
                                <div class="dm-stat-mini-val">{{ number_format((float)$agent->overall_rating ?? 0, 1) }}</div>
                                <div class="dm-stat-mini-lbl">Rating</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ────────────────────────────────────────────── --}}
            {{-- RIGHT: Tabbed Form Sections                    --}}
            {{-- ────────────────────────────────────────────── --}}
            <div class="dm-main">

                {{-- ── SECTION: Identity ─────────────────────── --}}
                <div id="section-identity" class="dm-section dm-card">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-id-card"></i> Identity & Contact</span>
                    </div>
                    <div class="dm-card-body">
                        <div class="dm-fields">
                            <div class="dm-field">
                                <label>Full Name <span class="req">*</span></label>
                                <input type="text" name="agent_name" class="dm-input"
                                       value="{{ old('agent_name', $agent->agent_name) }}" required placeholder="e.g. Ahmad Al-Rashid">
                            </div>
                        </div>
                        <div class="dm-fields dm-fields-2" style="margin-top:.9rem">
                            <div class="dm-field">
                                <label>Primary Email <span class="req">*</span></label>
                                <div class="dm-input-icon">
                                    <i class="fas fa-envelope icon"></i>
                                    <input type="email" name="primary_email" class="dm-input"
                                           value="{{ old('primary_email', $agent->primary_email) }}" required>
                                </div>
                            </div>
                            <div class="dm-field">
                                <label>Primary Phone</label>
                                <div class="dm-input-icon">
                                    <i class="fas fa-phone icon"></i>
                                    <input type="text" name="primary_phone" class="dm-input"
                                           value="{{ old('primary_phone', $agent->primary_phone) }}" placeholder="+964 ...">
                                </div>
                            </div>
                            <div class="dm-field">
                                <label>WhatsApp Number</label>
                                <div class="dm-input-icon">
                                    <i class="fab fa-whatsapp icon" style="color:#25d366"></i>
                                    <input type="text" name="whatsapp_number" class="dm-input"
                                           value="{{ old('whatsapp_number', $agent->whatsapp_number) }}" placeholder="+964 ...">
                                </div>
                            </div>
                            <div class="dm-field">
                                <label>Agent Type</label>
                                <div class="dm-select-wrap">
                                    <select name="type" class="dm-select">
                                        <option value="independent" {{ $agent->type == 'independent' ? 'selected' : '' }}>Independent</option>
                                        <option value="company" {{ $agent->type == 'company' ? 'selected' : '' }}>Company</option>
                                    </select>
                                    <i class="fas fa-chevron-down chevron"></i>
                                </div>
                            </div>
                        </div>

                        <hr class="dm-divider">

                        <div class="dm-field">
                            <label>Professional Bio</label>
                            <textarea name="agent_bio" class="dm-textarea"
                                      placeholder="Brief professional background…">{{ old('agent_bio', $agent->agent_bio) }}</textarea>
                            <p class="dm-field-hint">Shown on the agent's public profile page.</p>
                        </div>
                    </div>
                </div>

                {{-- ── SECTION: Professional ─────────────────── --}}
                <div id="section-professional" class="dm-section dm-card" style="display:none">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-briefcase"></i> Professional Details</span>
                    </div>
                    <div class="dm-card-body">
                        <div class="dm-fields dm-fields-2">
                            <div class="dm-field">
                                <label>Company / Agency</label>
                                <input type="text" name="company_name" class="dm-input"
                                       value="{{ old('company_name', $agent->company_name) }}" placeholder="Acme Realty">
                            </div>
                            <div class="dm-field">
                                <label>Employment Status</label>
                                <input type="text" name="employment_status" class="dm-input"
                                       value="{{ old('employment_status', $agent->employment_status) }}" placeholder="Full-time">
                            </div>
                            <div class="dm-field">
                                <label>License Number</label>
                                <input type="text" name="license_number" class="dm-input"
                                       value="{{ old('license_number', $agent->license_number) }}" placeholder="LIC-00000">
                            </div>
                            <div class="dm-field">
                                <label>Years of Experience</label>
                                <input type="number" name="years_experience" class="dm-input" min="0"
                                       value="{{ old('years_experience', $agent->years_experience) }}">
                            </div>
                            <div class="dm-field">
                                <label>Properties Sold</label>
                                <input type="number" name="properties_sold" class="dm-input" min="0"
                                       value="{{ old('properties_sold', $agent->properties_sold) }}">
                            </div>
                        </div>

                        <hr class="dm-divider">

                        <div class="dm-field">
                            <label>Working Hours <span style="text-transform:none;font-weight:500;color:var(--dm-ink-3)">(JSON)</span></label>
                            <input type="text" name="working_hours" class="dm-input"
                                   style="font-family:monospace;font-size:.78rem"
                                   value="{{ is_array($agent->working_hours) ? json_encode($agent->working_hours) : old('working_hours', $agent->working_hours) }}"
                                   placeholder='{"mon":"9am-6pm","fri":"Closed"}'>
                            <p class="dm-field-hint">JSON format. Leave blank to skip.</p>
                        </div>
                    </div>
                </div>

                {{-- ── SECTION: Location ─────────────────────── --}}
                <div id="section-location" class="dm-section dm-card" style="display:none">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-map-pin"></i> Location</span>
                    </div>
                    <div class="dm-card-body">
                        <div class="dm-fields">
                            <div class="dm-field">
                                <label>Office Address</label>
                                <div class="dm-input-icon">
                                    <i class="fas fa-building icon"></i>
                                    <input type="text" name="office_address" class="dm-input"
                                           value="{{ old('office_address', $agent->office_address) }}"
                                           placeholder="Street, Building…">
                                </div>
                            </div>
                        </div>
                        <div class="dm-fields dm-fields-2" style="margin-top:.9rem">
                            <div class="dm-field">
                                <label>City</label>
                                <input type="text" name="city" class="dm-input"
                                       value="{{ old('city', $agent->city) }}" placeholder="Erbil">
                            </div>
                            <div class="dm-field">
                                <label>District</label>
                                <input type="text" name="district" class="dm-input"
                                       value="{{ old('district', $agent->district) }}" placeholder="Azadi">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── SECTION: Plan & Billing ───────────────── --}}
                <div id="section-billing" class="dm-section dm-card" style="display:none">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-credit-card"></i> Plan & Billing</span>
                        @if(isset($plans))
                            <span style="font-size:.65rem;font-weight:800;color:var(--dm-accent);background:var(--dm-accent-lt);padding:.2rem .6rem;border-radius:99px;border:1px solid #c7d2fe">
                                {{ $plans->count() }} plans
                            </span>
                        @endif
                    </div>
                    <div class="dm-card-body">

                        {{-- Active sub banner --}}
                        @if($agent->subscription && $agent->subscription->currentPlan)
                        <div class="dm-sub-active">
                            <div class="dm-sub-active-icon"><i class="fas fa-crown"></i></div>
                            <div>
                                <div class="dm-sub-active-name">{{ $agent->subscription->currentPlan->name }}</div>
                                <div class="dm-sub-active-meta">
                                    Expires {{ $agent->subscription->end_date ? $agent->subscription->end_date->format('d M Y') : '—' }}
                                </div>
                            </div>
                            <span class="dm-sub-active-badge">Active</span>
                        </div>
                        @endif

                        {{-- Plan picker --}}
                        @if(isset($plans) && $plans->count())
                        <p style="font-size:.72rem;font-weight:800;color:var(--dm-ink-2);letter-spacing:.05em;text-transform:uppercase;margin-bottom:.75rem">
                            Assign a New Plan
                        </p>
                        <div class="dm-plan-grid" style="margin-bottom:1rem">
                            <label class="dm-plan-card selected" id="plan-none">
                                <input type="radio" name="plan_id" value="" checked onchange="selectPlan(this)">
                                <div class="dm-plan-name">Keep Current</div>
                                <div class="dm-plan-dur" style="color:var(--dm-ink-3)">No change</div>
                                <span class="dm-plan-check"><i class="fas fa-check"></i></span>
                            </label>
                            @foreach($plans as $plan)
                            <label class="dm-plan-card" id="plan-{{ $plan->id }}">
                                <input type="radio" name="plan_id" value="{{ $plan->id }}" onchange="selectPlan(this)">
                                <div class="dm-plan-name">{{ $plan->name }}</div>
                                <div class="dm-plan-price">${{ number_format($plan->final_price_usd) }}<span>/plan</span></div>
                                <div class="dm-plan-dur">{{ $plan->duration_label ?? $plan->duration_months . ' mo' }}</div>
                                <span class="dm-plan-check"><i class="fas fa-check"></i></span>
                            </label>
                            @endforeach
                        </div>
                        <p class="dm-field-hint">Selecting a new plan cancels the current one and starts a fresh subscription immediately.</p>
                        @endif

                        <hr class="dm-divider">

                        <div class="dm-fields dm-fields-3">
                            <div class="dm-field">
                                <label>Remaining Uploads</label>
                                <input type="number" name="remaining_property_uploads" class="dm-input"
                                       value="{{ old('remaining_property_uploads', $agent->remaining_property_uploads) }}">
                            </div>
                            <div class="dm-field">
                                <label>Commission (%)</label>
                                <div class="dm-input-icon">
                                    <input type="number" step="0.01" name="commission_rate" class="dm-input has-right"
                                           value="{{ old('commission_rate', $agent->commission_rate) }}">
                                    <span class="icon-right" style="position:absolute;right:.9rem;top:50%;transform:translateY(-50%);font-weight:800;font-size:.75rem;color:var(--dm-ink-3)">%</span>
                                </div>
                            </div>
                            <div class="dm-field">
                                <label>Consultation Fee</label>
                                <input type="number" step="0.01" name="consultation_fee" class="dm-input"
                                       value="{{ old('consultation_fee', $agent->consultation_fee) }}">
                            </div>
                            <div class="dm-field">
                                <label>Currency</label>
                                <div class="dm-select-wrap">
                                    <select name="currency" class="dm-select">
                                        <option value="USD" {{ $agent->currency == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                        <option value="IQD" {{ $agent->currency == 'IQD' ? 'selected' : '' }}>IQD (د.ع)</option>
                                        <option value="EUR" {{ $agent->currency == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                    </select>
                                    <i class="fas fa-chevron-down chevron"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── SECTION: System ───────────────────────── --}}
                <div id="section-system" class="dm-section dm-card" style="display:none">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-sliders"></i> System Settings</span>
                    </div>
                    <div class="dm-card-body">
                        <div class="dm-fields dm-fields-2">
                            <div class="dm-field">
                                <label>Overall Rating</label>
                                <div class="dm-input-icon">
                                    <i class="fas fa-star icon" style="color:var(--dm-amber)"></i>
                                    <input type="number" step="0.1" min="0" max="5" name="overall_rating" class="dm-input"
                                           value="{{ old('overall_rating', $agent->overall_rating) }}">
                                </div>
                                <p class="dm-field-hint">0–5 scale.</p>
                            </div>
                            <div class="dm-field">
                                <label>New Password</label>
                                <div class="dm-input-icon">
                                    <i class="fas fa-lock icon"></i>
                                    <input type="password" name="password" class="dm-input"
                                           placeholder="Leave blank to keep current" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <hr class="dm-divider">

                        <label class="dm-toggle-wrap">
                            <input type="checkbox" name="is_verified" value="1" {{ $agent->is_verified ? 'checked' : '' }}>
                            <div class="dm-toggle-slider"></div>
                            <div>
                                <div class="dm-toggle-label">Verified Agent</div>
                                <div class="dm-toggle-sub">Verified agents are publicly searchable on DreamMulk.</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- ── Sticky Save Bar ───────────────────────── --}}
                <div class="dm-save-bar">
                    <div class="dm-save-bar-text">
                        Editing <strong>{{ $agent->agent_name }}</strong>
                    </div>
                    <div style="display:flex;gap:.6rem">
                        <a href="{{ route('admin.agents.index') }}" class="dm-btn dm-btn--ghost" style="color:rgba(255,255,255,.6);border-color:rgba(255,255,255,.15);background:transparent">
                            Discard
                        </a>
                        <button type="submit" class="dm-btn dm-btn--accent">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>

            </div>{{-- /.dm-main --}}
        </div>{{-- /.dm-edit-grid --}}
    </form>
</div>

<script>
// ── Image Preview ──────────────────────────────────────────
function previewImage(input, previewId, placeholderId) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById(previewId);
        const holder  = document.getElementById(placeholderId);
        if (preview) { preview.src = e.target.result; preview.classList.remove('hidden'); }
        if (holder)  { holder.classList.add('hidden'); }
    };
    reader.readAsDataURL(input.files[0]);
}

// ── Section Switcher ──────────────────────────────────────
const sectionIds = ['identity','professional','location','billing','system'];

function showSection(name, btn) {
    sectionIds.forEach(id => {
        document.getElementById('section-' + id).style.display = (id === name) ? 'block' : 'none';
    });
    document.querySelectorAll('.dm-nav-item').forEach(el => el.classList.remove('active'));
    if (btn) btn.classList.add('active');
}

// ── Plan Card Selection ───────────────────────────────────
function selectPlan(radio) {
    document.querySelectorAll('.dm-plan-card').forEach(c => c.classList.remove('selected'));
    const card = radio.closest('.dm-plan-card');
    if (card) card.classList.add('selected');
}
</script>

@endsection
