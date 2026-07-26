@extends('layouts.admin-layout')

@section('title', 'Edit Office — ' . $office->company_name)

@section('content')

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
        --dm-shadow-sm: 0 1px 3px rgba(15,23,42,.06);
        --dm-shadow:    0 4px 14px rgba(15,23,42,.09);
    }

    .dm-wrap { max-width: 1100px; margin: 0 auto; padding-bottom: 4rem; }

    /* ── Topbar ─────────────────────────────────────────────── */
    .dm-topbar {
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 1rem; padding: 1.5rem 0 1.75rem; flex-wrap: wrap;
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
        letter-spacing: -.02em; display: flex; align-items: center; gap: .6rem;
    }
    .dm-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .2rem .6rem; border-radius: 99px;
        font-size: .63rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
    }
    .dm-badge--verified { background: var(--dm-green-lt); color: #065f46; border: 1px solid #a7f3d0; }
    .dm-badge--pending  { background: var(--dm-amber-lt); color: #92400e; border: 1px solid #fcd34d; }
    .dm-topbar-actions  { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }

    /* ── Buttons ─────────────────────────────────────────────── */
    .dm-btn {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .6rem 1.15rem; border-radius: var(--dm-radius-sm);
        font-size: .79rem; font-weight: 800; cursor: pointer;
        border: none; transition: all .15s; text-decoration: none; white-space: nowrap;
    }
    .dm-btn--primary { background: var(--dm-ink); color: #fff; box-shadow: 0 2px 6px rgba(15,23,42,.2); }
    .dm-btn--primary:hover { background: #1e293b; }
    .dm-btn--accent  { background: var(--dm-accent); color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,.3); }
    .dm-btn--accent:hover  { background: #4f46e5; }
    .dm-btn--ghost   { background: var(--dm-white); border: 1.5px solid var(--dm-border); color: var(--dm-ink-2); }
    .dm-btn--ghost:hover   { background: var(--dm-surface); color: var(--dm-ink); }
    .dm-btn--green   { background: var(--dm-green); color: #fff; }
    .dm-btn--green:hover   { background: #059669; }

    /* ── KPI Strip ───────────────────────────────────────────── */
    .dm-kpi-strip { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.5rem; }
    @media(max-width:700px){ .dm-kpi-strip { grid-template-columns: repeat(2,1fr); } }
    .dm-kpi {
        background: var(--dm-white); border: 1px solid var(--dm-border);
        border-radius: var(--dm-radius); padding: 1rem 1.1rem; box-shadow: var(--dm-shadow-sm);
        display: flex; align-items: center; gap: .85rem;
    }
    .dm-kpi-icon {
        width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: .9rem;
    }
    .dm-kpi-val  { font-size: 1.4rem; font-weight: 900; color: var(--dm-ink); line-height: 1; }
    .dm-kpi-lbl  { font-size: .63rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--dm-ink-3); margin-top: .15rem; }

    /* ── Grid layout ─────────────────────────────────────────── */
    .dm-edit-grid { display: grid; grid-template-columns: 290px 1fr; gap: 1.5rem; }
    @media(max-width:900px){ .dm-edit-grid { grid-template-columns: 1fr; } }
    .dm-sidebar { display: flex; flex-direction: column; gap: 1.25rem; }
    .dm-main    { display: flex; flex-direction: column; gap: 1.25rem; }

    /* ── Card ────────────────────────────────────────────────── */
    .dm-card {
        background: var(--dm-white); border: 1px solid var(--dm-border);
        border-radius: var(--dm-radius-lg); box-shadow: var(--dm-shadow-sm); overflow: hidden;
    }
    .dm-card-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: .9rem 1.3rem; border-bottom: 1px solid var(--dm-border); background: var(--dm-surface);
    }
    .dm-card-title {
        font-size: .67rem; font-weight: 800; letter-spacing: .08em;
        text-transform: uppercase; color: var(--dm-ink-2);
        display: flex; align-items: center; gap: .45rem;
    }
    .dm-card-body { padding: 1.3rem; }

    /* ── Image Uploads ───────────────────────────────────────── */
    .dm-logo-wrap {
        display: flex; flex-direction: column; align-items: center; padding: 1.5rem; gap: .9rem;
    }
    .dm-logo-upload {
        position: relative; width: 120px; height: 120px; cursor: pointer;
    }
    .dm-logo-img, .dm-logo-placeholder {
        width: 120px; height: 120px; border-radius: var(--dm-radius);
        border: 2px dashed var(--dm-border); object-fit: cover; display: block;
        background: var(--dm-surface);
    }
    .dm-logo-placeholder {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .35rem; color: var(--dm-ink-3); font-size: .68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .04em;
    }
    .dm-logo-placeholder i { font-size: 1.6rem; }
    .dm-logo-upload input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .dm-logo-overlay {
        position: absolute; inset: 0; background: rgba(15,23,42,.4); border-radius: var(--dm-radius);
        display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .16s;
    }
    .dm-logo-upload:hover .dm-logo-overlay { opacity: 1; }
    .dm-logo-overlay i { color: #fff; font-size: 1.1rem; }

    .dm-cover-upload {
        position: relative; width: 100%; height: 90px; cursor: pointer;
        border-radius: var(--dm-radius-sm); overflow: hidden;
        border: 2px dashed var(--dm-border); background: var(--dm-surface); transition: border-color .15s;
    }
    .dm-cover-upload:hover { border-color: var(--dm-accent); }
    .dm-cover-upload input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .dm-cover-upload img { width: 100%; height: 100%; object-fit: cover; }
    .dm-cover-placeholder {
        width: 100%; height: 100%; display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: .25rem;
        color: var(--dm-ink-3); font-size: .68rem; font-weight: 700; text-transform: uppercase;
    }

    /* ── Section Nav ─────────────────────────────────────────── */
    .dm-nav { display: flex; flex-direction: column; gap: .15rem; padding: .5rem; }
    .dm-nav-item {
        display: flex; align-items: center; gap: .7rem;
        padding: .65rem .9rem; border-radius: var(--dm-radius-sm);
        font-size: .8rem; font-weight: 700; color: var(--dm-ink-2);
        cursor: pointer; border: none; background: transparent; width: 100%; text-align: left;
        transition: background .12s, color .12s;
    }
    .dm-nav-item i { width: 16px; text-align: center; font-size: .82rem; }
    .dm-nav-item:hover { background: var(--dm-surface); color: var(--dm-ink); }
    .dm-nav-item.active { background: var(--dm-accent-lt); color: var(--dm-accent); }
    .dm-nav-badge {
        margin-left: auto; padding: .1rem .45rem; border-radius: 99px;
        font-size: .6rem; font-weight: 800; background: var(--dm-red); color: #fff;
    }

    /* ── Form fields ─────────────────────────────────────────── */
    .dm-section { animation: dm-in .2s ease; }
    @keyframes dm-in { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:none; } }

    .dm-fields   { display: grid; gap: .9rem; }
    .dm-fields-2 { grid-template-columns: 1fr 1fr; }
    .dm-fields-3 { grid-template-columns: 1fr 1fr 1fr; }
    @media(max-width:640px){ .dm-fields-2,.dm-fields-3 { grid-template-columns: 1fr; } }

    .dm-field label {
        display: block; font-size: .67rem; font-weight: 800; letter-spacing: .06em;
        text-transform: uppercase; color: var(--dm-ink-2); margin-bottom: .4rem;
    }
    .dm-field label .req { color: var(--dm-red); margin-left: 2px; }
    .dm-field-hint { font-size: .67rem; font-weight: 500; color: var(--dm-ink-3); margin-top: .3rem; line-height: 1.4; }

    .dm-input, .dm-select, .dm-textarea {
        width: 100%; padding: .65rem 1rem;
        background: var(--dm-surface); border: 1.5px solid var(--dm-border);
        border-radius: var(--dm-radius-sm); font-size: .84rem; font-weight: 600;
        color: var(--dm-ink); outline: none; appearance: none; transition: border-color .14s, box-shadow .14s;
    }
    .dm-input:focus,.dm-select:focus,.dm-textarea:focus {
        border-color: var(--dm-accent); box-shadow: 0 0 0 3px rgba(99,102,241,.12); background: var(--dm-white);
    }
    .dm-input--mono { font-family: monospace; font-size: .78rem; }
    .dm-textarea { resize: vertical; min-height: 90px; }

    .dm-input-icon { position: relative; }
    .dm-input-icon .ico { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: var(--dm-ink-3); pointer-events: none; }
    .dm-input-icon .dm-input { padding-left: 2.3rem; }

    .dm-select-wrap { position: relative; }
    .dm-select-wrap .chev { position: absolute; right: .9rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--dm-ink-3); font-size: .72rem; }

    /* ── Schedule builder ────────────────────────────────────── */
    .dm-schedule { border-radius: var(--dm-radius-sm); border: 1.5px solid var(--dm-border); overflow: hidden; }
    .dm-schedule-row {
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        padding: .7rem 1rem; border-bottom: 1px solid var(--dm-border); transition: background .12s;
    }
    .dm-schedule-row:last-child { border-bottom: none; }
    .dm-schedule-row.active { background: var(--dm-accent-lt); }
    .dm-schedule-day { font-size: .8rem; font-weight: 700; min-width: 90px; display: flex; align-items: center; gap: .6rem; }
    .dm-schedule-times { display: flex; align-items: center; gap: .5rem; }
    .dm-time-input {
        padding: .35rem .65rem; border: 1.5px solid var(--dm-border); border-radius: 8px;
        font-size: .78rem; font-family: monospace; font-weight: 700; color: var(--dm-ink);
        background: var(--dm-white); outline: none;
    }
    .dm-time-input:focus { border-color: var(--dm-accent); box-shadow: 0 0 0 2px rgba(99,102,241,.12); }
    .dm-time-sep { font-size: .75rem; font-weight: 800; color: var(--dm-ink-3); }

    /* ── Toggle ──────────────────────────────────────────────── */
    .dm-toggle-slider {
        position: relative; width: 38px; height: 21px;
        background: var(--dm-border); border-radius: 99px; flex-shrink: 0; cursor: pointer;
        transition: background .16s;
    }
    .dm-toggle-slider::after {
        content: ''; position: absolute; width: 15px; height: 15px;
        background: var(--dm-white); border-radius: 50%; top: 3px; left: 3px;
        transition: left .16s; box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    input:checked + .dm-toggle-slider { background: var(--dm-green); }
    input:checked + .dm-toggle-slider::after { left: 20px; }

    .dm-toggle-row {
        display: flex; align-items: center; gap: .85rem;
        padding: .8rem 1rem; background: var(--dm-surface);
        border: 1.5px solid var(--dm-border); border-radius: var(--dm-radius-sm);
    }
    .dm-toggle-lbl { font-size: .82rem; font-weight: 700; color: var(--dm-ink); }
    .dm-toggle-sub { font-size: .7rem; font-weight: 500; color: var(--dm-ink-3); }

    /* ── Plan cards ──────────────────────────────────────────── */
    .dm-plan-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(155px,1fr)); gap: .75rem; }
    .dm-plan-card {
        position: relative; border: 2px solid var(--dm-border); border-radius: var(--dm-radius);
        padding: 1rem; cursor: pointer; background: var(--dm-white); transition: border-color .14s, box-shadow .14s;
    }
    .dm-plan-card:hover { border-color: var(--dm-accent); }
    .dm-plan-card input[type="radio"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .dm-plan-card.selected { border-color: var(--dm-accent); background: var(--dm-accent-lt); box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
    .dm-plan-name  { font-size: .82rem; font-weight: 800; color: var(--dm-ink); margin-bottom: .2rem; }
    .dm-plan-price { font-size: 1.1rem; font-weight: 900; color: var(--dm-accent); }
    .dm-plan-price span { font-size: .65rem; font-weight: 600; color: var(--dm-ink-3); }
    .dm-plan-dur   { font-size: .67rem; font-weight: 600; color: var(--dm-ink-3); margin-top: .15rem; }
    .dm-plan-check {
        position: absolute; top: .55rem; right: .55rem; width: 18px; height: 18px;
        border-radius: 50%; background: var(--dm-accent); color: #fff;
        display: none; align-items: center; justify-content: center; font-size: .55rem;
    }
    .dm-plan-card.selected .dm-plan-check { display: flex; }

    /* Active sub banner */
    .dm-sub-banner {
        display: flex; align-items: center; gap: .85rem;
        padding: .9rem 1.1rem; border-radius: var(--dm-radius-sm);
        background: linear-gradient(135deg,#1e1b4b,#312e81); color: #fff; margin-bottom: 1rem;
    }
    .dm-sub-banner-icon { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .dm-sub-banner-name { font-size: .9rem; font-weight: 800; }
    .dm-sub-banner-meta { font-size: .7rem; color: rgba(255,255,255,.55); }
    .dm-sub-banner-badge { margin-left: auto; padding: .2rem .65rem; border-radius: 99px; font-size: .62rem; font-weight: 800; text-transform: uppercase; background: rgba(16,185,129,.2); color: #6ee7b7; border: 1px solid rgba(16,185,129,.3); }

    /* ── Divider ─────────────────────────────────────────────── */
    .dm-divider { border: none; border-top: 1px solid var(--dm-border); margin: 1rem 0; }

    /* ── Errors ──────────────────────────────────────────────── */
    .dm-errors { background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--dm-radius-sm); padding: .9rem 1.1rem; margin-bottom: 1.25rem; }
    .dm-errors li { font-size: .8rem; color: #991b1b; font-weight: 600; }

    /* ── Sticky save bar ─────────────────────────────────────── */
    .dm-save-bar {
        position: sticky; bottom: 1.5rem; z-index: 50;
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        padding: .85rem 1.25rem; background: var(--dm-ink);
        border-radius: var(--dm-radius); box-shadow: 0 8px 30px rgba(15,23,42,.28);
        margin-top: 1.5rem; flex-wrap: wrap;
    }
    .dm-save-bar-text { font-size: .78rem; font-weight: 600; color: rgba(255,255,255,.55); }
    .dm-save-bar-text strong { color: #fff; font-weight: 800; }
</style>

<div class="dm-wrap">

    @if($errors->any())
    <div class="dm-errors">
        <p style="font-size:.8rem;font-weight:800;color:#b91c1c;margin:0 0 .35rem"><i class="fas fa-exclamation-circle mr-1"></i> Please fix these errors:</p>
        <ul style="padding-left:1.2rem;margin:0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- ── Topbar ──────────────────────────────────────────────── --}}
    <div class="dm-topbar">
        <div>
            <div class="dm-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a><span>/</span>
                <a href="{{ route('admin.offices.index') }}">Offices</a><span>/</span>
                Edit
            </div>
            <h1 class="dm-page-title">
                {{ $office->company_name }}
                @if($office->is_verified)
                    <span class="dm-badge dm-badge--verified"><i class="fas fa-check-circle" style="font-size:.55rem"></i> Verified</span>
                @else
                    <span class="dm-badge dm-badge--pending"><i class="fas fa-clock" style="font-size:.55rem"></i> Pending</span>
                @endif
            </h1>
        </div>
        <div class="dm-topbar-actions">
            <a href="{{ route('admin.properties.create') }}?office_id={{ $office->id }}" class="dm-btn dm-btn--green">
                <i class="fas fa-plus"></i> Add Property
            </a>
            <a href="{{ route('admin.offices.show', $office->id) }}" class="dm-btn dm-btn--ghost">
                <i class="fas fa-eye"></i> View Profile
            </a>
            <a href="{{ route('admin.offices.index') }}" class="dm-btn dm-btn--ghost">Cancel</a>
        </div>
    </div>

    {{-- ── KPI Strip ───────────────────────────────────────────── --}}
    <div class="dm-kpi-strip">
        <div class="dm-kpi">
            <div class="dm-kpi-icon" style="background:var(--dm-blue-lt);color:var(--dm-blue)"><i class="fas fa-building"></i></div>
            <div>
                <div class="dm-kpi-val">{{ $office->ownedProperties()->count() }}</div>
                <div class="dm-kpi-lbl">Properties</div>
            </div>
        </div>
        <div class="dm-kpi">
            <div class="dm-kpi-icon" style="background:var(--dm-green-lt);color:var(--dm-green)"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="dm-kpi-val">{{ $office->ownedProperties()->where('status','available')->count() }}</div>
                <div class="dm-kpi-lbl">Available</div>
            </div>
        </div>
        <div class="dm-kpi">
            <div class="dm-kpi-icon" style="background:var(--dm-amber-lt);color:var(--dm-amber)"><i class="fas fa-clock"></i></div>
            <div>
                <div class="dm-kpi-val">{{ $office->ownedProperties()->where('status','pending')->count() }}</div>
                <div class="dm-kpi-lbl">Pending</div>
            </div>
        </div>
        <div class="dm-kpi">
            <div class="dm-kpi-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-users"></i></div>
            <div>
                <div class="dm-kpi-val">{{ $office->agents()->count() }}</div>
                <div class="dm-kpi-lbl">Agents</div>
            </div>
        </div>
    </div>

    {{-- ── Form ────────────────────────────────────────────────── --}}
    <form id="editOfficeForm" method="POST" action="{{ route('admin.offices.update', $office->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="availability_schedule" id="availability_json">

        <div class="dm-edit-grid">

            {{-- ══ SIDEBAR ═══════════════════════════════════════ --}}
            <div class="dm-sidebar">

                {{-- Logo + Cover --}}
                <div class="dm-card">
                    <div class="dm-logo-wrap">
                        <div style="font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--dm-ink-3);margin-bottom:.1rem">Office Logo</div>
                        <div class="dm-logo-upload">
                            @if($office->profile_image)
                                <img id="logoPreview" class="dm-logo-img" src="{{ asset($office->profile_image) }}" alt="">
                            @else
                                <div class="dm-logo-placeholder" id="logoPlaceholder">
                                    <i class="fas fa-building"></i><span>Upload Logo</span>
                                </div>
                                <img id="logoPreview" class="dm-logo-img hidden" alt="">
                            @endif
                            <div class="dm-logo-overlay"><i class="fas fa-camera"></i></div>
                            <input type="file" name="logo" accept="image/*" onchange="previewImage(this,'logoPreview','logoPlaceholder')">
                        </div>
                        <p style="font-size:.67rem;color:var(--dm-ink-3);font-weight:500;text-align:center">JPG / PNG · Max 2 MB</p>
                        <hr class="dm-divider" style="width:100%;margin:.5rem 0">
                        <div style="width:100%">
                            <div style="font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--dm-ink-3);margin-bottom:.5rem">Cover Image</div>
                            <div class="dm-cover-upload">
                                @if($office->company_bio_image)
                                    <img id="bioPreview" src="{{ asset($office->company_bio_image) }}" alt="">
                                @else
                                    <div class="dm-cover-placeholder" id="bioPlaceholder">
                                        <i class="fas fa-image" style="font-size:1.1rem"></i><span>Click to upload</span>
                                    </div>
                                    <img id="bioPreview" class="hidden" alt="">
                                @endif
                                <input type="file" name="company_bio_image" accept="image/*" onchange="previewImage(this,'bioPreview','bioPlaceholder')">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Nav --}}
                <div class="dm-card">
                    <nav class="dm-nav">
                        <button type="button" class="dm-nav-item active" onclick="showSection('identity',this)">
                            <i class="fas fa-id-card"></i> Identity & Branding
                        </button>
                        <button type="button" class="dm-nav-item" onclick="showSection('contact',this)">
                            <i class="fas fa-phone"></i> Contact & Location
                        </button>
                        <button type="button" class="dm-nav-item" onclick="showSection('schedule',this)">
                            <i class="fas fa-clock"></i> Working Hours
                        </button>
                        <button type="button" class="dm-nav-item" onclick="showSection('performance',this)">
                            <i class="fas fa-chart-bar"></i> Performance
                        </button>
                        <button type="button" class="dm-nav-item" onclick="showSection('billing',this)">
                            <i class="fas fa-credit-card"></i> Plan & Billing
                        </button>
                        <button type="button" class="dm-nav-item" onclick="showSection('system',this)">
                            <i class="fas fa-sliders"></i> System
                        </button>
                    </nav>
                </div>

                {{-- Current subscription summary --}}
                <div class="dm-card">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-crown"></i> Active Plan</span>
                    </div>
                    <div class="dm-card-body" style="padding:1rem">
                        @if($office->subscription && $office->subscription->status === 'active')
                            <div style="background:linear-gradient(135deg,#1e1b4b,#312e81);border-radius:var(--dm-radius-sm);padding:.85rem 1rem;color:#fff">
                                <div style="font-size:.65rem;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em">Current Plan</div>
                                <div style="font-size:1rem;font-weight:900;margin:.2rem 0">{{ ucfirst($office->current_plan ?? $office->subscription->currentPlan->name ?? 'Active') }}</div>
                                <div style="font-size:.7rem;color:rgba(255,255,255,.5)">
                                    Expires {{ $office->subscription->end_date ? \Carbon\Carbon::parse($office->subscription->end_date)->format('d M Y') : '—' }}
                                </div>
                            </div>
                        @else
                            <div style="text-align:center;padding:.75rem 0;color:var(--dm-ink-3);font-size:.8rem;font-weight:500">
                                <i class="fas fa-exclamation-circle" style="font-size:1.3rem;display:block;margin-bottom:.4rem;color:var(--dm-amber)"></i>
                                No active subscription
                            </div>
                        @endif
                    </div>
                </div>

            </div>{{-- /.dm-sidebar --}}

            {{-- ══ MAIN ════════════════════════════════════════════ --}}
            <div class="dm-main">

                {{-- ── IDENTITY ─────────────────────────────────── --}}
                <div id="section-identity" class="dm-section dm-card">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-id-card"></i> Identity & Branding</span>
                    </div>
                    <div class="dm-card-body">
                        <div class="dm-fields">
                            <div class="dm-field">
                                <label>Company Name <span class="req">*</span></label>
                                <input type="text" name="company_name" class="dm-input"
                                       value="{{ old('company_name', $office->company_name) }}" required placeholder="e.g. Kurdistan Premier Realty">
                            </div>
                            <div class="dm-field">
                                <label>Tagline / Short Bio</label>
                                <input type="text" name="company_bio" class="dm-input"
                                       value="{{ old('company_bio', $office->company_bio) }}"
                                       placeholder="e.g. Your trusted real estate partner in Kurdistan">
                            </div>
                            <div class="dm-field">
                                <label>Detailed Description</label>
                                <textarea name="about_company" class="dm-textarea"
                                          placeholder="Tell potential clients about the office's history, services, and values…">{{ old('about_company', $office->about_company) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── CONTACT & LOCATION ───────────────────────── --}}
                <div id="section-contact" class="dm-section dm-card" style="display:none">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-phone"></i> Contact & Location</span>
                    </div>
                    <div class="dm-card-body">
                        <div class="dm-fields dm-fields-2">
                            <div class="dm-field">
                                <label>Email Address <span class="req">*</span></label>
                                <div class="dm-input-icon">
                                    <i class="fas fa-envelope ico"></i>
                                    <input type="email" name="email_address" class="dm-input"
                                           value="{{ old('email_address', $office->email_address) }}" required>
                                </div>
                            </div>
                            <div class="dm-field">
                                <label>Phone Number</label>
                                <div class="dm-input-icon">
                                    <i class="fas fa-phone ico"></i>
                                    <input type="text" name="phone_number" class="dm-input"
                                           value="{{ old('phone_number', $office->phone_number) }}" placeholder="+964 ...">
                                </div>
                            </div>
                        </div>

                        <hr class="dm-divider">

                        <div class="dm-fields">
                            <div class="dm-field">
                                <label>Full Office Address</label>
                                <div class="dm-input-icon">
                                    <i class="fas fa-building ico"></i>
                                    <input type="text" name="office_address" class="dm-input"
                                           value="{{ old('office_address', $office->office_address) }}" placeholder="Street, Building, Floor…">
                                </div>
                            </div>
                        </div>
                        <div class="dm-fields dm-fields-2" style="margin-top:.9rem">
                            <div class="dm-field">
                                <label>City</label>
                                <input type="text" name="city" class="dm-input"
                                       value="{{ old('city', $office->city) }}" placeholder="Erbil">
                            </div>
                            <div class="dm-field">
                                <label>District</label>
                                <input type="text" name="district" class="dm-input"
                                       value="{{ old('district', $office->district) }}" placeholder="Azadi">
                            </div>
                        </div>

                        <hr class="dm-divider">

                        <p style="font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--dm-ink-2);margin-bottom:.7rem">
                            <i class="fas fa-map-pin" style="margin-right:.35rem;color:var(--dm-ink-3)"></i> GPS Coordinates
                        </p>
                        <div class="dm-fields dm-fields-2">
                            <div class="dm-field">
                                <label>Latitude</label>
                                <input type="number" step="any" name="latitude" class="dm-input dm-input--mono"
                                       value="{{ old('latitude', $office->latitude) }}" placeholder="36.1234">
                            </div>
                            <div class="dm-field">
                                <label>Longitude</label>
                                <input type="number" step="any" name="longitude" class="dm-input dm-input--mono"
                                       value="{{ old('longitude', $office->longitude) }}" placeholder="44.0098">
                            </div>
                        </div>
                        @if($office->latitude && $office->longitude)
                        <div style="margin-top:.9rem;border-radius:var(--dm-radius-sm);overflow:hidden;border:1px solid var(--dm-border)">
                            <iframe width="100%" height="160" frameborder="0" style="border:0;display:block"
                                src="https://maps.google.com/maps?q={{ $office->latitude }},{{ $office->longitude }}&hl=en&z=14&output=embed"></iframe>
                        </div>
                        <p class="dm-field-hint" style="margin-top:.4rem">Map preview based on saved coordinates. Update lat/lng and save to refresh.</p>
                        @endif
                    </div>
                </div>

                {{-- ── WORKING HOURS ────────────────────────────── --}}
                <div id="section-schedule" class="dm-section dm-card" style="display:none">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-clock"></i> Weekly Working Hours</span>
                        <div style="display:flex;gap:.5rem">
                            <button type="button" onclick="setAllDays(true)" class="dm-btn dm-btn--ghost" style="padding:.3rem .7rem;font-size:.7rem">All On</button>
                            <button type="button" onclick="setAllDays(false)" class="dm-btn dm-btn--ghost" style="padding:.3rem .7rem;font-size:.7rem">All Off</button>
                        </div>
                    </div>
                    <div class="dm-card-body" style="padding:.75rem">
                        <div class="dm-schedule" id="schedule_container"></div>
                        <p class="dm-field-hint" style="margin-top:.65rem">Toggle each day and set open/close times. Closed days are hidden from the public profile.</p>
                    </div>
                </div>

                {{-- ── PERFORMANCE ──────────────────────────────── --}}
                <div id="section-performance" class="dm-section dm-card" style="display:none">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-chart-bar"></i> Performance Details</span>
                    </div>
                    <div class="dm-card-body">
                        <div class="dm-fields dm-fields-3">
                            <div class="dm-field">
                                <label>License Number</label>
                                <input type="text" name="license_number" class="dm-input"
                                       value="{{ old('license_number', $office->license_number) }}" placeholder="LIC-00000">
                            </div>
                            <div class="dm-field">
                                <label>Years in Business</label>
                                <input type="number" name="years_experience" class="dm-input" min="0"
                                       value="{{ old('years_experience', $office->years_experience) }}">
                            </div>
                            <div class="dm-field">
                                <label>Properties Sold</label>
                                <input type="number" name="properties_sold" class="dm-input" min="0"
                                       value="{{ old('properties_sold', $office->properties_sold) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── BILLING ──────────────────────────────────── --}}
                <div id="section-billing" class="dm-section dm-card" style="display:none">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-credit-card"></i> Plan & Billing</span>
                        @if(isset($plans))
                            <span style="font-size:.63rem;font-weight:800;color:var(--dm-accent);background:var(--dm-accent-lt);padding:.2rem .6rem;border-radius:99px;border:1px solid #c7d2fe">
                                {{ $plans->count() }} plans
                            </span>
                        @endif
                    </div>
                    <div class="dm-card-body">

                        {{-- Active sub banner --}}
                        @if($office->subscription && $office->subscription->currentPlan)
                        <div class="dm-sub-banner">
                            <div class="dm-sub-banner-icon"><i class="fas fa-crown"></i></div>
                            <div>
                                <div class="dm-sub-banner-name">{{ $office->subscription->currentPlan->name }}</div>
                                <div class="dm-sub-banner-meta">
                                    Expires {{ $office->subscription->end_date ? \Carbon\Carbon::parse($office->subscription->end_date)->format('d M Y') : '—' }}
                                </div>
                            </div>
                            <span class="dm-sub-banner-badge">Active</span>
                        </div>
                        @endif

                        @if(isset($plans) && $plans->count())
                        <p style="font-size:.7rem;font-weight:800;color:var(--dm-ink-2);letter-spacing:.05em;text-transform:uppercase;margin-bottom:.75rem">
                            Assign a New Plan
                        </p>
                        <div class="dm-plan-grid" style="margin-bottom:.75rem">
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
                                <div class="dm-plan-price">${{ number_format($plan->final_price_usd ?? 0) }}<span>/plan</span></div>
                                <div class="dm-plan-dur">{{ $plan->duration_label ?? ($plan->duration_months . ' mo') }}</div>
                                <span class="dm-plan-check"><i class="fas fa-check"></i></span>
                            </label>
                            @endforeach
                        </div>
                        <p class="dm-field-hint">Selecting a new plan cancels the current one and starts a fresh subscription immediately.</p>
                        @endif
                    </div>
                </div>

                {{-- ── SYSTEM ───────────────────────────────────── --}}
                <div id="section-system" class="dm-section dm-card" style="display:none">
                    <div class="dm-card-head">
                        <span class="dm-card-title"><i class="fas fa-sliders"></i> System Settings</span>
                    </div>
                    <div class="dm-card-body">
                        <div class="dm-fields dm-fields-2">
                            <div class="dm-field">
                                <label>New Password</label>
                                <div class="dm-input-icon">
                                    <i class="fas fa-lock ico"></i>
                                    <input type="password" name="password" class="dm-input"
                                           placeholder="Leave blank to keep current" autocomplete="new-password">
                                </div>
                                <p class="dm-field-hint">Minimum 8 characters. Leave blank to keep existing password.</p>
                            </div>
                            <div class="dm-field" style="display:flex;flex-direction:column;justify-content:flex-end">
                                <label style="margin-bottom:.5rem">Verified Status</label>
                                <label class="dm-toggle-row" style="cursor:pointer">
                                    <input type="hidden" name="is_verified" value="0">
                                    <input type="checkbox" name="is_verified" value="1" id="verifiedToggle" {{ $office->is_verified ? 'checked' : '' }} style="display:none" onchange="this.previousElementSibling.value = this.checked ? 1 : 0">
                                    <div class="dm-toggle-slider" onclick="toggleVerified()"></div>
                                    <div>
                                        <div class="dm-toggle-lbl" id="verifiedLabel">{{ $office->is_verified ? 'Verified Office' : 'Not Verified' }}</div>
                                        <div class="dm-toggle-sub">Verified offices appear in public search results.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <hr class="dm-divider">

                        {{-- Danger zone --}}
                        <div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:var(--dm-radius-sm);padding:1rem 1.1rem">
                            <p style="font-size:.75rem;font-weight:800;color:#b91c1c;margin:0 0 .5rem">
                                <i class="fas fa-triangle-exclamation mr-1"></i> Danger Zone
                            </p>
                            <p style="font-size:.78rem;color:#991b1b;margin:0 0 .75rem;font-weight:500">
                                Deleting an office permanently removes all its data including properties and subscriptions.
                            </p>
                            <form action="{{ route('admin.offices.delete', $office->id) }}" method="POST"
                                  onsubmit="return confirm('Delete {{ addslashes($office->company_name) }} permanently? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dm-btn dm-btn--ghost" style="border-color:#fca5a5;color:var(--dm-red);background:#fff">
                                    <i class="fas fa-trash-alt"></i> Delete This Office
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ── Sticky Save Bar ──────────────────────────── --}}
                <div class="dm-save-bar">
                    <div class="dm-save-bar-text">Editing <strong>{{ $office->company_name }}</strong></div>
                    <div style="display:flex;gap:.6rem">
                        <a href="{{ route('admin.offices.index') }}" class="dm-btn dm-btn--ghost"
                           style="color:rgba(255,255,255,.5);border-color:rgba(255,255,255,.15);background:transparent">
                            Discard
                        </a>
                        <button type="button" onclick="submitForm()" class="dm-btn dm-btn--accent">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>

            </div>{{-- /.dm-main --}}
        </div>{{-- /.dm-edit-grid --}}
    </form>
</div>

<script>
// ── Image Preview ──────────────────────────────────────────────
function previewImage(input, previewId, placeholderId) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById(previewId);
        const hold = document.getElementById(placeholderId);
        if (prev) { prev.src = e.target.result; prev.classList.remove('hidden'); }
        if (hold) hold.classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

// ── Section Switcher ───────────────────────────────────────────
const SECTIONS = ['identity','contact','schedule','performance','billing','system'];
function showSection(name, btn) {
    SECTIONS.forEach(id => {
        document.getElementById('section-' + id).style.display = id === name ? 'block' : 'none';
    });
    document.querySelectorAll('.dm-nav-item').forEach(el => el.classList.remove('active'));
    if (btn) btn.classList.add('active');
}

// ── Plan Card Selection ────────────────────────────────────────
function selectPlan(radio) {
    document.querySelectorAll('.dm-plan-card').forEach(c => c.classList.remove('selected'));
    radio.closest('.dm-plan-card').classList.add('selected');
}

// ── Verified Toggle ────────────────────────────────────────────
function toggleVerified() {
    const cb = document.getElementById('verifiedToggle');
    cb.checked = !cb.checked;
    cb.previousElementSibling.value = cb.checked ? 1 : 0;
    document.getElementById('verifiedLabel').textContent = cb.checked ? 'Verified Office' : 'Not Verified';
}

// ── Schedule Builder ───────────────────────────────────────────
let scheduleData = @json($office->availability_schedule
    ? (is_array($office->availability_schedule)
        ? $office->availability_schedule
        : json_decode($office->availability_schedule, true))
    : []);

const DAYS = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
const container = document.getElementById('schedule_container');
const hiddenInput = document.getElementById('availability_json');

function renderSchedule() {
    container.innerHTML = '';
    DAYS.forEach(day => {
        const d = scheduleData[day] || { active: false, start: '09:00', end: '18:00' };
        const isActive = d.active === true || d.active === 'true' || d.active === 1;

        const row = document.createElement('div');
        row.className = 'dm-schedule-row' + (isActive ? ' active' : '');
        row.id = 'row-' + day;

        row.innerHTML = `
            <div class="dm-schedule-day">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                    <input type="checkbox" ${isActive ? 'checked' : ''} style="display:none" id="chk-${day}"
                        onchange="toggleDay('${day}', this.checked)">
                    <div class="dm-toggle-slider" onclick="toggleDay('${day}', !document.getElementById('chk-${day}').checked); document.getElementById('chk-${day}').checked = !document.getElementById('chk-${day}').checked" style="cursor:pointer"></div>
                    <span style="font-weight:700;font-size:.82rem;color:${isActive ? 'var(--dm-accent)' : 'var(--dm-ink-3)'};text-transform:capitalize">${day}</span>
                </label>
            </div>
            <div class="dm-schedule-times" style="opacity:${isActive ? '1' : '.35'};pointer-events:${isActive ? 'all' : 'none'};transition:opacity .15s" id="times-${day}">
                <input type="time" class="dm-time-input" value="${d.start || '09:00'}"
                    onchange="updateDay('${day}','start',this.value)" title="Open time">
                <span class="dm-time-sep">→</span>
                <input type="time" class="dm-time-input" value="${d.end || '18:00'}"
                    onchange="updateDay('${day}','end',this.value)" title="Close time">
                <span style="font-size:.7rem;font-weight:700;color:${isActive ? 'var(--dm-green)' : 'var(--dm-ink-3)'};min-width:38px">
                    ${isActive ? '<i class="fas fa-check" style="color:var(--dm-green)"></i> Open' : 'Closed'}
                </span>
            </div>
        `;
        container.appendChild(row);
    });

    // Sync toggle sliders visual state
    DAYS.forEach(day => {
        const d = scheduleData[day] || { active: false };
        const isActive = d.active === true || d.active === 'true' || d.active === 1;
        const chk = document.getElementById('chk-' + day);
        if (chk) chk.checked = isActive;
    });

    hiddenInput.value = JSON.stringify(scheduleData);
}

function toggleDay(day, value) {
    if (!scheduleData[day]) scheduleData[day] = { active: false, start: '09:00', end: '18:00' };
    scheduleData[day].active = value;
    renderSchedule();
}

function updateDay(day, field, value) {
    if (!scheduleData[day]) scheduleData[day] = { active: false, start: '09:00', end: '18:00' };
    scheduleData[day][field] = value;
    hiddenInput.value = JSON.stringify(scheduleData);
}

function setAllDays(active) {
    DAYS.forEach(day => {
        if (!scheduleData[day]) scheduleData[day] = { start: '09:00', end: '18:00' };
        scheduleData[day].active = active;
    });
    renderSchedule();
}

// ── Form Submit ────────────────────────────────────────────────
function submitForm() {
    hiddenInput.value = JSON.stringify(scheduleData);
    document.getElementById('editOfficeForm').submit();
}

// ── Init ───────────────────────────────────────────────────────
renderSchedule();
</script>

@endsection
