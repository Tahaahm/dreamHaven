@extends('layouts.admin-layout')

@section('title', $office->company_name . ' — Office Profile')

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

    .dm-wrap { max-width: 1120px; margin: 0 auto; padding-bottom: 3rem; }

    /* ── Topbar ─────────────────────────────────────────────── */
    .dm-topbar { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding:1.5rem 0 1.75rem; flex-wrap:wrap; }
    .dm-breadcrumb { display:flex; align-items:center; gap:.35rem; font-size:.7rem; font-weight:600; color:var(--dm-ink-3); letter-spacing:.04em; text-transform:uppercase; margin-bottom:.35rem; }
    .dm-breadcrumb a { color:var(--dm-ink-3); text-decoration:none; }
    .dm-breadcrumb a:hover { color:var(--dm-ink); }
    .dm-breadcrumb span { color:var(--dm-border); }
    .dm-page-title { font-size:1.35rem; font-weight:800; color:var(--dm-ink); letter-spacing:-.02em; display:flex; align-items:center; gap:.6rem; line-height:1.2; }
    .dm-badge { display:inline-flex; align-items:center; gap:.25rem; padding:.2rem .6rem; border-radius:99px; font-size:.63rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .dm-badge--verified { background:var(--dm-green-lt); color:#065f46; border:1px solid #a7f3d0; }
    .dm-badge--pending  { background:var(--dm-amber-lt); color:#92400e; border:1px solid #fcd34d; }
    .dm-topbar-actions  { display:flex; gap:.6rem; flex-wrap:wrap; align-items:center; }

    /* ── Buttons ─────────────────────────────────────────────── */
    .dm-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; border-radius:var(--dm-radius-sm); font-size:.78rem; font-weight:800; cursor:pointer; border:none; transition:all .15s; text-decoration:none; white-space:nowrap; }
    .dm-btn--primary { background:var(--dm-ink); color:#fff; box-shadow:0 2px 6px rgba(15,23,42,.2); }
    .dm-btn--primary:hover { background:#1e293b; }
    .dm-btn--ghost { background:var(--dm-white); border:1.5px solid var(--dm-border); color:var(--dm-ink-2); }
    .dm-btn--ghost:hover { background:var(--dm-surface); color:var(--dm-ink); }
    .dm-btn--green { background:var(--dm-green); color:#fff; }
    .dm-btn--green:hover { background:#059669; }
    .dm-btn--accent { background:var(--dm-accent); color:#fff; }
    .dm-btn--accent:hover { background:#4f46e5; }
    .dm-btn--red-ghost { background:#fef2f2; border:1.5px solid #fecaca; color:var(--dm-red); }
    .dm-btn--red-ghost:hover { background:#fee2e2; }

    /* Dropdown */
    .dm-dropdown { position:relative; }
    .dm-dropdown-menu { display:none; position:absolute; right:0; top:calc(100% + .4rem); background:var(--dm-white); border:1px solid var(--dm-border); border-radius:var(--dm-radius); box-shadow:var(--dm-shadow); z-index:60; min-width:180px; overflow:hidden; }
    .dm-dropdown:hover .dm-dropdown-menu { display:block; }
    .dm-dropdown-item { display:flex; align-items:center; gap:.6rem; padding:.7rem 1rem; font-size:.78rem; font-weight:700; cursor:pointer; border:none; background:transparent; width:100%; text-align:left; text-decoration:none; color:var(--dm-ink-2); transition:background .12s; }
    .dm-dropdown-item:hover { background:var(--dm-surface); color:var(--dm-ink); }
    .dm-dropdown-item--danger { color:var(--dm-red); }
    .dm-dropdown-item--danger:hover { background:#fef2f2; }
    .dm-dropdown-item--green { color:var(--dm-green); }
    .dm-dropdown-item--green:hover { background:var(--dm-green-lt); }
    .dm-dropdown-divider { height:1px; background:var(--dm-border); margin:.25rem 0; }

    /* ── Card ────────────────────────────────────────────────── */
    .dm-card { background:var(--dm-white); border:1px solid var(--dm-border); border-radius:var(--dm-radius-lg); box-shadow:var(--dm-shadow-sm); overflow:hidden; }
    .dm-card-head { display:flex; align-items:center; justify-content:space-between; padding:.9rem 1.25rem; border-bottom:1px solid var(--dm-border); background:var(--dm-surface); }
    .dm-card-title { font-size:.67rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--dm-ink-2); display:flex; align-items:center; gap:.45rem; }
    .dm-card-body { padding:1.25rem; }

    /* ── Hero ────────────────────────────────────────────────── */
    .dm-hero { position:relative; height:170px; border-radius:var(--dm-radius-lg) var(--dm-radius-lg) 0 0; overflow:hidden; background:linear-gradient(135deg,#1e1b4b 0%,#312e81 60%,#4338ca 100%); }
    .dm-hero img { width:100%; height:100%; object-fit:cover; }
    .dm-hero-overlay { position:absolute; inset:0; background:linear-gradient(to bottom,transparent 30%,rgba(15,23,42,.6)); }
    .dm-hero-icon { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:5rem; color:rgba(255,255,255,.07); }

    /* ── Profile card ────────────────────────────────────────── */
    .dm-profile-inner { padding:0 1.5rem 1.5rem; }
    .dm-logo-wrap { position:relative; margin-top:-50px; display:inline-block; margin-bottom:.75rem; }
    .dm-logo { width:96px; height:96px; border-radius:var(--dm-radius); border:3px solid var(--dm-white); box-shadow:var(--dm-shadow); object-fit:cover; display:block; }
    .dm-logo-init { width:96px; height:96px; border-radius:var(--dm-radius); border:3px solid var(--dm-white); box-shadow:var(--dm-shadow); background:linear-gradient(135deg,var(--dm-accent),#818cf8); display:flex; align-items:center; justify-content:center; font-size:1.75rem; font-weight:900; color:#fff; letter-spacing:-.02em; }
    .dm-co-name { font-size:1.15rem; font-weight:800; color:var(--dm-ink); line-height:1.2; }
    .dm-co-bio  { font-size:.8rem; font-weight:500; color:var(--dm-ink-3); margin-top:.2rem; }
    .dm-co-loc  { font-size:.75rem; font-weight:600; color:var(--dm-ink-3); margin-top:.15rem; display:flex; align-items:center; gap:.3rem; }

    /* stat bar */
    .dm-stat-bar { display:grid; grid-template-columns:repeat(3,1fr); border-top:1px solid var(--dm-border); margin-top:1rem; }
    .dm-stat-item { padding:.85rem .5rem; text-align:center; }
    .dm-stat-item+.dm-stat-item { border-left:1px solid var(--dm-border); }
    .dm-stat-val { font-size:1.25rem; font-weight:900; color:var(--dm-ink); line-height:1; }
    .dm-stat-lbl { font-size:.62rem; font-weight:700; color:var(--dm-ink-3); text-transform:uppercase; letter-spacing:.05em; margin-top:.2rem; }

    /* ── Contact rows ────────────────────────────────────────── */
    .dm-contact-list { display:flex; flex-direction:column; gap:.15rem; }
    .dm-contact-row { display:flex; align-items:center; gap:.75rem; padding:.65rem .75rem; border-radius:var(--dm-radius-sm); transition:background .12s; text-decoration:none; }
    .dm-contact-row:hover { background:var(--dm-surface); }
    .dm-contact-icon { width:34px; height:34px; border-radius:9px; background:var(--dm-surface); border:1px solid var(--dm-border); display:flex; align-items:center; justify-content:center; font-size:.8rem; color:var(--dm-ink-3); flex-shrink:0; transition:background .12s,color .12s; }
    .dm-contact-row:hover .dm-contact-icon { background:var(--dm-accent-lt); color:var(--dm-accent); }
    .dm-contact-lbl { font-size:.62rem; font-weight:700; color:var(--dm-ink-3); text-transform:uppercase; letter-spacing:.04em; }
    .dm-contact-val { font-size:.82rem; font-weight:700; color:var(--dm-ink); }

    /* ── Subscription card ───────────────────────────────────── */
    .dm-sub-card { background:linear-gradient(145deg,#1e1b4b,#2e1065); border-radius:var(--dm-radius-lg); padding:1.25rem; color:#fff; position:relative; overflow:hidden; }
    .dm-sub-card::before { content:''; position:absolute; top:-40px; right:-40px; width:130px; height:130px; border-radius:50%; background:rgba(255,255,255,.05); }
    .dm-sub-card::after  { content:''; position:absolute; bottom:-30px; left:20px; width:90px; height:90px; border-radius:50%; background:rgba(255,255,255,.04); }
    .dm-sub-plan  { font-size:1.4rem; font-weight:900; letter-spacing:-.02em; }
    .dm-sub-label { font-size:.63rem; font-weight:700; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.07em; margin-bottom:.2rem; }
    .dm-sub-status { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .7rem; border-radius:99px; font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
    .dm-sub-status--active { background:rgba(16,185,129,.2); color:#6ee7b7; border:1px solid rgba(16,185,129,.3); }
    .dm-sub-status--inactive { background:rgba(255,255,255,.1); color:rgba(255,255,255,.45); border:1px solid rgba(255,255,255,.15); }
    .dm-sub-rows { margin-top:1rem; display:flex; flex-direction:column; gap:.5rem; }
    .dm-sub-row { display:flex; justify-content:space-between; align-items:center; font-size:.78rem; }
    .dm-sub-row-lbl { color:rgba(255,255,255,.4); font-weight:500; }
    .dm-sub-row-val { font-weight:700; color:#fff; }
    .dm-sub-row--expire .dm-sub-row-val { color:#fbbf24; }
    .dm-progress { height:4px; background:rgba(255,255,255,.12); border-radius:99px; margin-top:.65rem; overflow:hidden; }
    .dm-progress-fill { height:100%; border-radius:99px; background:linear-gradient(90deg,#818cf8,#a78bfa); transition:width .4s; }

    /* ── KPI row ─────────────────────────────────────────────── */
    .dm-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
    @media(max-width:700px){ .dm-kpi-row { grid-template-columns:repeat(2,1fr); } }
    .dm-kpi { background:var(--dm-white); border:1px solid var(--dm-border); border-radius:var(--dm-radius); padding:1rem 1.1rem; box-shadow:var(--dm-shadow-sm); }
    .dm-kpi-top { display:flex; align-items:center; gap:.5rem; margin-bottom:.6rem; }
    .dm-kpi-icon { width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:.78rem; }
    .dm-kpi-lbl  { font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:var(--dm-ink-3); }
    .dm-kpi-val  { font-size:1.5rem; font-weight:900; color:var(--dm-ink); letter-spacing:-.02em; line-height:1; }

    /* ── Schedule display ────────────────────────────────────── */
    .dm-sched-row { display:flex; justify-content:space-between; align-items:center; padding:.55rem .75rem; border-radius:var(--dm-radius-sm); font-size:.82rem; }
    .dm-sched-row:nth-child(even) { background:var(--dm-surface); }
    .dm-sched-day { font-weight:700; color:var(--dm-ink); text-transform:capitalize; }
    .dm-sched-time { font-weight:800; color:var(--dm-ink-2); font-family:monospace; font-size:.79rem; }
    .dm-sched-closed { font-weight:700; color:var(--dm-ink-3); font-size:.75rem; }

    /* ── Agents mini table ───────────────────────────────────── */
    .dm-agent-row { display:flex; align-items:center; gap:.75rem; padding:.6rem .75rem; border-radius:var(--dm-radius-sm); text-decoration:none; transition:background .12s; }
    .dm-agent-row:hover { background:var(--dm-surface); }
    .dm-agent-avatar { width:36px; height:36px; border-radius:50%; object-fit:cover; background:var(--dm-surface); border:2px solid var(--dm-border); display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:800; color:var(--dm-ink-2); flex-shrink:0; overflow:hidden; }
    .dm-agent-name { font-size:.82rem; font-weight:700; color:var(--dm-ink); }
    .dm-agent-sub  { font-size:.68rem; font-weight:500; color:var(--dm-ink-3); }
    .dm-agent-count { margin-left:auto; font-size:.72rem; font-weight:800; color:var(--dm-accent); background:var(--dm-accent-lt); padding:.15rem .5rem; border-radius:99px; }

    /* ── Properties table ────────────────────────────────────── */
    .dm-table { width:100%; border-collapse:collapse; }
    .dm-table thead th { padding:.6rem 1.1rem; font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:var(--dm-ink-3); background:var(--dm-surface); text-align:left; border-bottom:1px solid var(--dm-border); }
    .dm-table tbody tr { transition:background .12s; border-bottom:1px solid var(--dm-border); }
    .dm-table tbody tr:last-child { border-bottom:none; }
    .dm-table tbody tr:hover { background:var(--dm-surface); }
    .dm-table td { padding:.75rem 1.1rem; font-size:.82rem; color:var(--dm-ink-2); vertical-align:middle; }

    .dm-prop-thumb { width:40px; height:40px; border-radius:9px; object-fit:cover; border:1px solid var(--dm-border); background:var(--dm-surface); flex-shrink:0; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .dm-prop-name { font-weight:800; color:var(--dm-ink); font-size:.84rem; max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .dm-prop-date { font-size:.67rem; color:var(--dm-ink-3); font-weight:500; }

    .dm-status-pill { display:inline-flex; align-items:center; padding:.2rem .6rem; border-radius:99px; font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.03em; }
    .dm-status--available { background:var(--dm-green-lt); color:#065f46; }
    .dm-status--sold      { background:var(--dm-blue-lt);  color:#1e40af; }
    .dm-status--rented    { background:#f5f3ff;            color:#5b21b6; }
    .dm-status--pending   { background:var(--dm-amber-lt); color:#92400e; }
    .dm-status--suspended { background:#fef2f2;            color:#991b1b; }
    .dm-status--default   { background:var(--dm-surface);  color:var(--dm-ink-3); }

    .dm-icon-btn { width:32px; height:32px; border-radius:8px; border:1px solid var(--dm-border); background:var(--dm-white); display:inline-flex; align-items:center; justify-content:center; color:var(--dm-ink-3); font-size:.78rem; cursor:pointer; transition:all .12s; text-decoration:none; }
    .dm-icon-btn:hover { color:var(--dm-accent); background:var(--dm-accent-lt); border-color:#c7d2fe; }

    .dm-empty { padding:2.5rem; text-align:center; color:var(--dm-ink-3); font-size:.84rem; font-weight:500; }
    .dm-empty i { font-size:1.8rem; display:block; margin-bottom:.5rem; opacity:.35; }

    /* ── Info grid ───────────────────────────────────────────── */
    .dm-info-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.1rem; }
    @media(max-width:580px){ .dm-info-grid { grid-template-columns:1fr 1fr; } }
    .dm-info-lbl { font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:var(--dm-ink-3); margin-bottom:.25rem; }
    .dm-info-val { font-size:.87rem; font-weight:700; color:var(--dm-ink); }
    .dm-info-val--mono { font-family:monospace; font-size:.8rem; }

    .dm-bio { background:var(--dm-surface); border:1px solid var(--dm-border); border-radius:var(--dm-radius-sm); padding:1rem 1.1rem; font-size:.84rem; color:var(--dm-ink-2); line-height:1.65; font-weight:500; }

    /* ── Layout ──────────────────────────────────────────────── */
    .dm-show-grid { display:grid; grid-template-columns:305px 1fr; gap:1.5rem; }
    @media(max-width:900px){ .dm-show-grid { grid-template-columns:1fr; } }
    .dm-left  { display:flex; flex-direction:column; gap:1.25rem; }
    .dm-right { display:flex; flex-direction:column; gap:1.25rem; }
    .dm-divider { border:none; border-top:1px solid var(--dm-border); margin:.9rem 0; }

    /* ── Quick action bar ────────────────────────────────────── */
    .dm-action-bar { display:flex; gap:.6rem; flex-wrap:wrap; padding:.9rem 1.25rem; border-top:1px solid var(--dm-border); background:var(--dm-surface); }
</style>

<div class="dm-wrap">

    {{-- ── Topbar ────────────────────────────────────────────── --}}
    <div class="dm-topbar">
        <div>
            <div class="dm-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a><span>/</span>
                <a href="{{ route('admin.offices.index') }}">Offices</a><span>/</span>
                Profile
            </div>
            <h1 class="dm-page-title">
                {{ $office->company_name }}
                @if($office->is_verified)
                    <span class="dm-badge dm-badge--verified"><i class="fas fa-check-circle" style="font-size:.55rem"></i> Verified</span>
                @else
                    <span class="dm-badge dm-badge--pending"><i class="fas fa-clock" style="font-size:.55rem"></i> Pending Review</span>
                @endif
            </h1>
        </div>

        <div class="dm-topbar-actions">
            <a href="{{ route('admin.offices.index') }}" class="dm-btn dm-btn--ghost">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('admin.offices.edit', $office->id) }}" class="dm-btn dm-btn--primary">
                <i class="fas fa-pen"></i> Edit Office
            </a>

            {{-- Actions dropdown --}}
            <div class="dm-dropdown">
                <button class="dm-btn dm-btn--ghost" style="padding:.55rem .75rem"><i class="fas fa-ellipsis-v"></i></button>
                <div class="dm-dropdown-menu">
                    <a href="{{ route('admin.properties.create') }}?office_id={{ $office->id }}" class="dm-dropdown-item">
                        <i class="fas fa-plus-circle"></i> Add Property
                    </a>
                    @if(!$office->is_verified)
                    <div class="dm-dropdown-divider"></div>
                    <form action="{{ route('admin.offices.verify', $office->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="dm-dropdown-item dm-dropdown-item--green">
                            <i class="fas fa-check-circle"></i> Approve Office
                        </button>
                    </form>
                    @else
                    <div class="dm-dropdown-divider"></div>
                    <form action="{{ route('admin.offices.suspend', $office->id) }}" method="POST"
                          onsubmit="return confirm('Suspend this office? They will no longer appear in public listings.')">
                        @csrf
                        <button type="submit" class="dm-dropdown-item" style="color:var(--dm-amber)">
                            <i class="fas fa-pause-circle"></i> Suspend Office
                        </button>
                    </form>
                    @endif
                    <div class="dm-dropdown-divider"></div>
                    <form action="{{ route('admin.offices.delete', $office->id) }}" method="POST"
                          onsubmit="return confirm('Delete {{ addslashes($office->company_name) }} permanently?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="dm-dropdown-item dm-dropdown-item--danger">
                            <i class="fas fa-trash-alt"></i> Delete Office
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Grid ───────────────────────────────────────────── --}}
    <div class="dm-show-grid">

        {{-- ══ LEFT ════════════════════════════════════════════════ --}}
        <div class="dm-left">

            {{-- Profile identity card --}}
            <div class="dm-card">
                <div class="dm-hero">
                    @if($office->company_bio_image)
                        <img src="{{ asset($office->company_bio_image) }}" alt="">
                    @else
                        <div class="dm-hero-icon"><i class="fas fa-building"></i></div>
                    @endif
                    <div class="dm-hero-overlay"></div>
                </div>
                <div style="border-top:none;border-radius:0 0 var(--dm-radius-lg) var(--dm-radius-lg)">
                    <div class="dm-profile-inner">
                        <div class="dm-logo-wrap">
                            @if($office->profile_image)
                                <img class="dm-logo" src="{{ asset($office->profile_image) }}" alt="{{ $office->company_name }}">
                            @else
                                <div class="dm-logo-init">{{ strtoupper(substr($office->company_name, 0, 2)) }}</div>
                            @endif
                        </div>
                        <div class="dm-co-name">{{ $office->company_name }}</div>
                        @if($office->company_bio)
                            <div class="dm-co-bio">{{ $office->company_bio }}</div>
                        @endif
                        <div class="dm-co-loc">
                            <i class="fas fa-map-pin" style="font-size:.7rem;color:var(--dm-accent)"></i>
                            {{ $office->city ?? 'Unknown City' }}
                            @if($office->district) · {{ $office->district }} @endif
                        </div>

                        <div class="dm-stat-bar">
                            <div class="dm-stat-item">
                                <div class="dm-stat-val">{{ $office->years_experience ?? 0 }}</div>
                                <div class="dm-stat-lbl">Yrs Exp.</div>
                            </div>
                            <div class="dm-stat-item">
                                <div class="dm-stat-val">{{ $office->properties_sold ?? 0 }}</div>
                                <div class="dm-stat-lbl">Sold</div>
                            </div>
                            <div class="dm-stat-item">
                                <div class="dm-stat-val">{{ $office->ownedProperties->count() }}</div>
                                <div class="dm-stat-lbl">Listed</div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Action Bar --}}
                    <div class="dm-action-bar">
                        <a href="{{ route('admin.offices.edit', $office->id) }}" class="dm-btn dm-btn--ghost" style="flex:1;justify-content:center;font-size:.75rem">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                        <a href="{{ route('admin.properties.create') }}?office_id={{ $office->id }}" class="dm-btn dm-btn--green" style="flex:1;justify-content:center;font-size:.75rem">
                            <i class="fas fa-plus"></i> Add Property
                        </a>
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
                        <a href="mailto:{{ $office->email_address }}" class="dm-contact-row">
                            <div class="dm-contact-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <div class="dm-contact-lbl">Email</div>
                                <div class="dm-contact-val" style="word-break:break-all">{{ $office->email_address }}</div>
                            </div>
                        </a>
                        @if($office->phone_number)
                        <a href="tel:{{ $office->phone_number }}" class="dm-contact-row">
                            <div class="dm-contact-icon"><i class="fas fa-phone"></i></div>
                            <div>
                                <div class="dm-contact-lbl">Phone</div>
                                <div class="dm-contact-val">{{ $office->phone_number }}</div>
                            </div>
                        </a>
                        @endif
                        @if($office->office_address)
                        <div class="dm-contact-row">
                            <div class="dm-contact-icon"><i class="fas fa-map-pin"></i></div>
                            <div>
                                <div class="dm-contact-lbl">Address</div>
                                <div class="dm-contact-val" style="line-height:1.4">{{ $office->office_address }}</div>
                            </div>
                        </div>
                        @endif
                        <div class="dm-contact-row">
                            <div class="dm-contact-icon"><i class="fas fa-calendar-plus"></i></div>
                            <div>
                                <div class="dm-contact-lbl">Member Since</div>
                                <div class="dm-contact-val">{{ $office->created_at->format('d M Y') }}</div>
                            </div>
                        </div>
                        @if($office->license_number)
                        <div class="dm-contact-row">
                            <div class="dm-contact-icon"><i class="fas fa-id-badge"></i></div>
                            <div>
                                <div class="dm-contact-lbl">License</div>
                                <div class="dm-contact-val" style="font-family:monospace;font-size:.8rem">{{ $office->license_number }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Map --}}
            @if($office->latitude && $office->longitude)
            <div class="dm-card">
                <div class="dm-card-head">
                    <span class="dm-card-title"><i class="fas fa-map"></i> Location Map</span>
                    <a href="https://maps.google.com/?q={{ $office->latitude }},{{ $office->longitude }}" target="_blank"
                       style="font-size:.7rem;font-weight:800;color:var(--dm-accent);text-decoration:none">
                        Open in Maps →
                    </a>
                </div>
                <div style="padding:.5rem">
                    <div style="border-radius:var(--dm-radius-sm);overflow:hidden">
                        <iframe width="100%" height="180" frameborder="0" style="border:0;display:block"
                            src="https://maps.google.com/maps?q={{ $office->latitude }},{{ $office->longitude }}&hl=en&z=15&output=embed"></iframe>
                    </div>
                </div>
            </div>
            @endif

            {{-- Working Hours --}}
            @if($office->availability_schedule)
            @php
                $schedule = is_string($office->availability_schedule)
                    ? json_decode($office->availability_schedule, true)
                    : $office->availability_schedule;
                $allDays = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
            @endphp
            <div class="dm-card">
                <div class="dm-card-head">
                    <span class="dm-card-title"><i class="fas fa-clock"></i> Working Hours</span>
                </div>
                <div class="dm-card-body" style="padding:.75rem">
                    @if(is_array($schedule))
                        @foreach($allDays as $day)
                        <div class="dm-sched-row">
                            <span class="dm-sched-day">{{ ucfirst($day) }}</span>
                            @if(isset($schedule[$day]) && ($schedule[$day]['active'] ?? false))
                                <span class="dm-sched-time">{{ $schedule[$day]['start'] ?? '—' }} – {{ $schedule[$day]['end'] ?? '—' }}</span>
                            @else
                                <span class="dm-sched-closed">Closed</span>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <p style="font-size:.8rem;color:var(--dm-ink-3);text-align:center;padding:.5rem">No schedule set.</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Subscription --}}
            <div class="dm-sub-card">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.75rem;position:relative;z-index:1">
                    <div>
                        <div class="dm-sub-label">Current Plan</div>
                        <div class="dm-sub-plan">
                            {{ ucfirst($office->current_plan ?? ($office->subscription->currentPlan->name ?? 'Free')) }}
                        </div>
                    </div>
                    @if($office->subscription && $office->subscription->status === 'active')
                        <span class="dm-sub-status dm-sub-status--active"><i class="fas fa-circle" style="font-size:.4rem"></i> Active</span>
                    @else
                        <span class="dm-sub-status dm-sub-status--inactive">Inactive</span>
                    @endif
                </div>

                @if($office->subscription)
                <div class="dm-sub-rows" style="position:relative;z-index:1">
                    <div class="dm-sub-row">
                        <span class="dm-sub-row-lbl">Properties allowed</span>
                        <span class="dm-sub-row-val">
                            {{ ($office->subscription->property_activation_limit ?? 0) > 0 ? $office->subscription->property_activation_limit : '∞ Unlimited' }}
                        </span>
                    </div>
                    <div class="dm-sub-row">
                        <span class="dm-sub-row-lbl">Used this month</span>
                        <span class="dm-sub-row-val">{{ $office->subscription->properties_activated_this_month ?? 0 }}</span>
                    </div>
                    <div class="dm-sub-row dm-sub-row--expire">
                        <span class="dm-sub-row-lbl">Expires on</span>
                        <span class="dm-sub-row-val">{{ $office->subscription->end_date ? \Carbon\Carbon::parse($office->subscription->end_date)->format('d M Y') : 'N/A' }}</span>
                    </div>
                </div>
                @php
                    $subLimit = $office->subscription->property_activation_limit ?? 0;
                    $subUsed  = $office->subscription->properties_activated_this_month ?? 0;
                    $subPct   = ($subLimit > 0) ? min(100, round(($subUsed / $subLimit) * 100)) : 0;
                @endphp
                @if($subLimit > 0)
                <div class="dm-progress" style="position:relative;z-index:1">
                    <div class="dm-progress-fill" style="width:{{ $subPct }}%"></div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:.3rem;font-size:.64rem;color:rgba(255,255,255,.3);font-weight:600;position:relative;z-index:1">
                    <span>{{ $subUsed }} used</span><span>{{ $subLimit }} limit</span>
                </div>
                @endif
                @else
                <div style="text-align:center;padding:1rem 0;position:relative;z-index:1">
                    <p style="font-size:.8rem;color:rgba(255,255,255,.35);margin-bottom:.75rem">No active subscription.</p>
                    <a href="{{ route('admin.offices.edit', $office->id) }}" class="dm-btn dm-btn--ghost"
                       style="color:rgba(255,255,255,.6);border-color:rgba(255,255,255,.15);background:rgba(255,255,255,.07)">
                        Assign Plan
                    </a>
                </div>
                @endif
            </div>

        </div>{{-- /.dm-left --}}

        {{-- ══ RIGHT ════════════════════════════════════════════════ --}}
        <div class="dm-right">

            {{-- KPI Row --}}
            <div class="dm-kpi-row">
                <div class="dm-kpi">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-icon" style="background:var(--dm-blue-lt);color:var(--dm-blue)"><i class="fas fa-home"></i></div>
                        <span class="dm-kpi-lbl">Listings</span>
                    </div>
                    <div class="dm-kpi-val">{{ $office->ownedProperties->count() }}</div>
                </div>
                <div class="dm-kpi">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-users"></i></div>
                        <span class="dm-kpi-lbl">Agents</span>
                    </div>
                    <div class="dm-kpi-val">{{ $office->agents->count() ?? 0 }}</div>
                </div>
                <div class="dm-kpi">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-icon" style="background:var(--dm-green-lt);color:var(--dm-green)"><i class="fas fa-handshake"></i></div>
                        <span class="dm-kpi-lbl">Sold</span>
                    </div>
                    <div class="dm-kpi-val">{{ $office->properties_sold ?? 0 }}</div>
                </div>
                <div class="dm-kpi">
                    <div class="dm-kpi-top">
                        <div class="dm-kpi-icon" style="background:var(--dm-amber-lt);color:var(--dm-amber)"><i class="fas fa-star"></i></div>
                        <span class="dm-kpi-lbl">Rating</span>
                    </div>
                    <div class="dm-kpi-val" style="font-size:1.3rem">{{ number_format($office->average_rating ?? 0, 1) }}</div>
                </div>
            </div>

            {{-- Professional Info --}}
            <div class="dm-card">
                <div class="dm-card-head">
                    <span class="dm-card-title"><i class="fas fa-briefcase"></i> Office Details</span>
                </div>
                <div class="dm-card-body">
                    <div class="dm-info-grid">
                        <div>
                            <div class="dm-info-lbl">License Number</div>
                            <div class="dm-info-val dm-info-val--mono">{{ $office->license_number ?? 'Not provided' }}</div>
                        </div>
                        <div>
                            <div class="dm-info-lbl">Years in Business</div>
                            <div class="dm-info-val">{{ $office->years_experience ?? 0 }} years</div>
                        </div>
                        <div>
                            <div class="dm-info-lbl">Registered</div>
                            <div class="dm-info-val">{{ $office->created_at->format('d M Y') }}</div>
                        </div>
                    </div>

                    @if($office->about_company)
                    <hr class="dm-divider">
                    <div class="dm-info-lbl" style="margin-bottom:.5rem">About</div>
                    <div class="dm-bio">{{ $office->about_company }}</div>
                    @endif
                </div>
            </div>

            {{-- Agents under this office --}}
            @if(isset($office->agents) && $office->agents->count())
            <div class="dm-card">
                <div class="dm-card-head">
                    <span class="dm-card-title"><i class="fas fa-users"></i> Agents ({{ $office->agents->count() }})</span>
                    <a href="{{ route('admin.agents.index') }}" style="font-size:.72rem;font-weight:800;color:var(--dm-accent);text-decoration:none">View all →</a>
                </div>
                <div class="dm-card-body" style="padding:.5rem">
                    @foreach($office->agents->take(6) as $agent)
                    <a href="{{ route('admin.agents.show', $agent->id) }}" class="dm-agent-row">
                        <div class="dm-agent-avatar">
                            @if($agent->profile_image)
                                <img src="{{ asset($agent->profile_image) }}" alt="" style="width:100%;height:100%;object-fit:cover">
                            @else
                                {{ strtoupper(substr($agent->agent_name, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div class="dm-agent-name">{{ $agent->agent_name }}</div>
                            <div class="dm-agent-sub">{{ $agent->is_verified ? 'Verified' : 'Pending' }} · {{ $agent->properties_count ?? 0 }} listings</div>
                        </div>
                        @if($agent->is_verified)
                            <span style="margin-left:auto;font-size:.62rem;font-weight:800;color:#065f46;background:var(--dm-green-lt);padding:.15rem .5rem;border-radius:99px;border:1px solid #a7f3d0">Verified</span>
                        @else
                            <span style="margin-left:auto;font-size:.62rem;font-weight:800;color:#92400e;background:var(--dm-amber-lt);padding:.15rem .5rem;border-radius:99px;border:1px solid #fcd34d">Pending</span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Properties Table --}}
            <div class="dm-card">
                <div class="dm-card-head">
                    <span class="dm-card-title"><i class="fas fa-home"></i> Recent Listings</span>
                    <a href="{{ route('admin.properties.index', ['owner_type' => 'RealEstateOffice', 'search' => $office->company_name]) }}"
                       style="font-size:.72rem;font-weight:800;color:var(--dm-accent);text-decoration:none">View all →</a>
                </div>

                @if($office->ownedProperties->count())
                <div style="overflow-x:auto">
                    <table class="dm-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th style="text-align:center">Status</th>
                                <th>Views</th>
                                <th style="text-align:right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($office->ownedProperties->take(8) as $property)
                            @php
                                $nameData  = is_string($property->name)   ? json_decode($property->name, true)   : $property->name;
                                $propName  = is_array($nameData)  ? ($nameData['en'] ?? $nameData['ar'] ?? 'Property') : ($nameData ?? 'Property');

                                $imageData = is_string($property->images) ? json_decode($property->images, true) : $property->images;
                                $firstImg  = is_array($imageData) ? ($imageData[0] ?? null) : null;

                                $priceData = is_string($property->price)  ? json_decode($property->price, true)  : $property->price;
                                $priceVal  = is_array($priceData) ? ($priceData['usd'] ?? 0) : ($priceData ?? 0);

                                $statusCls = match($property->status ?? '') {
                                    'available' => 'dm-status--available',
                                    'sold'      => 'dm-status--sold',
                                    'rented'    => 'dm-status--rented',
                                    'pending'   => 'dm-status--pending',
                                    'suspended' => 'dm-status--suspended',
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
                                <td style="font-weight:700;color:var(--dm-ink-2);text-transform:capitalize">
                                    {{ ucfirst($property->listing_type ?? '—') }}
                                </td>
                                <td style="font-weight:800;color:var(--dm-ink)">${{ number_format((float)$priceVal) }}</td>
                                <td style="text-align:center">
                                    <span class="dm-status-pill {{ $statusCls }}">{{ $property->status ?? 'N/A' }}</span>
                                </td>
                                <td style="font-weight:700;color:var(--dm-ink-2)">
                                    {{ number_format($property->views ?? 0) }}
                                </td>
                                <td style="text-align:right">
                                    <div style="display:flex;gap:.35rem;justify-content:flex-end">
                                        <a href="{{ route('admin.properties.show', $property->id) }}" class="dm-icon-btn" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.properties.edit', $property->id) }}" class="dm-icon-btn" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Property Status Summary Bar --}}
                @php
                    $propTotal     = $office->ownedProperties->count();
                    $propAvailable = $office->ownedProperties->where('status','available')->count();
                    $propPending   = $office->ownedProperties->where('status','pending')->count();
                    $propSold      = $office->ownedProperties->where('status','sold')->count();
                    $propRented    = $office->ownedProperties->where('status','rented')->count();
                @endphp
                <div style="padding:.75rem 1.1rem;border-top:1px solid var(--dm-border);background:var(--dm-surface);display:flex;gap:1.25rem;flex-wrap:wrap">
                    <span style="font-size:.72rem;font-weight:700;color:var(--dm-ink-3)">
                        Total: <strong style="color:var(--dm-ink)">{{ $propTotal }}</strong>
                    </span>
                    <span style="font-size:.72rem;font-weight:700;color:#065f46">
                        Available: <strong>{{ $propAvailable }}</strong>
                    </span>
                    <span style="font-size:.72rem;font-weight:700;color:#92400e">
                        Pending: <strong>{{ $propPending }}</strong>
                    </span>
                    <span style="font-size:.72rem;font-weight:700;color:#1e40af">
                        Sold: <strong>{{ $propSold }}</strong>
                    </span>
                    <span style="font-size:.72rem;font-weight:700;color:#5b21b6">
                        Rented: <strong>{{ $propRented }}</strong>
                    </span>
                </div>

                @else
                <div class="dm-empty">
                    <i class="fas fa-home"></i>
                    No properties listed yet.
                    <div style="margin-top:.75rem">
                        <a href="{{ route('admin.properties.create') }}?office_id={{ $office->id }}" class="dm-btn dm-btn--accent">
                            <i class="fas fa-plus"></i> Add First Property
                        </a>
                    </div>
                </div>
                @endif
            </div>

        </div>{{-- /.dm-right --}}
    </div>
</div>

@endsection
