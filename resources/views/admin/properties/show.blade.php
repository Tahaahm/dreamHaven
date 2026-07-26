@extends('layouts.admin-layout')

@section('title', 'Property Intelligence')

@php
    // ── Safe data extraction ──────────────────────────────────────────
    $nameData  = is_string($property->name)   ? json_decode($property->name, true)   : $property->name;
    $propName  = is_array($nameData) ? ($nameData['en'] ?? $nameData['ar'] ?? 'Untitled') : ($nameData ?? 'Untitled');
    $propNameAr = is_array($nameData) ? ($nameData['ar'] ?? '') : '';
    $propNameKu = is_array($nameData) ? ($nameData['ku'] ?? '') : '';

    $descData  = is_string($property->description) ? json_decode($property->description, true) : $property->description;
    $descEn    = is_array($descData) ? ($descData['en'] ?? '') : ($descData ?? '');
    $descAr    = is_array($descData) ? ($descData['ar'] ?? '') : '';

    $priceData = is_string($property->price) ? json_decode($property->price, true) : $property->price;
    $priceUSD  = is_array($priceData) ? (float)($priceData['usd'] ?? $priceData['amount'] ?? 0) : (float)($priceData ?? 0);
    $priceIQD  = is_array($priceData) ? (float)($priceData['iqd'] ?? 0) : 0;

    $typeData  = is_string($property->type) ? json_decode($property->type, true) : $property->type;
    $category  = is_array($typeData) ? ($typeData['category'] ?? 'N/A') : ($typeData ?? 'N/A');

    $roomsData = is_string($property->rooms) ? json_decode($property->rooms, true) : $property->rooms;
    $roomsData = is_array($roomsData) ? $roomsData : [];
    $bedrooms  = $roomsData['bedroom']['count']    ?? $roomsData['bedroom']    ?? 0;
    $bathrooms = $roomsData['bathroom']['count']   ?? $roomsData['bathroom']   ?? 0;
    $living    = $roomsData['living_room']['count'] ?? $roomsData['living_room'] ?? 0;

    $imagesData = is_string($property->images) ? json_decode($property->images, true) : $property->images;
    $images     = is_array($imagesData) ? array_filter($imagesData, fn($i) => is_string($i)) : [];
    $heroImage  = count($images) > 0 ? reset($images) : null;

    $addrData = is_string($property->address_details) ? json_decode($property->address_details, true) : $property->address_details;
    $addrData = is_array($addrData) ? $addrData : [];
    $city     = is_array($addrData['city'] ?? null) ? ($addrData['city']['en'] ?? '') : ($addrData['city'] ?? '');
    $district = is_array($addrData['district'] ?? null) ? ($addrData['district']['en'] ?? '') : ($addrData['district'] ?? '');

    $locsData = is_string($property->locations) ? json_decode($property->locations, true) : $property->locations;
    $locsData = is_array($locsData) ? $locsData : [];
    $firstLoc = $locsData[0] ?? $locsData;
    $lat = (float)($firstLoc['lat'] ?? 0);
    $lng = (float)($firstLoc['lng'] ?? 0);

    $furnData  = is_string($property->furnishing_details) ? json_decode($property->furnishing_details, true) : $property->furnishing_details;
    $furnLevel = is_array($furnData) ? ($furnData['level'] ?? '') : '';

    $constrData = is_string($property->construction_details) ? json_decode($property->construction_details, true) : $property->construction_details;
    $buildType  = is_array($constrData) ? ($constrData['type'] ?? '') : '';
    $buildQuality = is_array($constrData) ? ($constrData['quality'] ?? '') : '';

    // ── Owner info ──────────────────────────────────────────────────
    $owner      = $property->owner;
    $ownerName  = '—'; $ownerEmail = '—'; $ownerPhone = '—';
    $ownerType  = class_basename($property->owner_type ?? '');
    $ownerRoute = null;
    if ($owner) {
        if ($property->owner_type === 'App\Models\Agent') {
            $ownerName  = $owner->agent_name   ?? '—';
            $ownerEmail = $owner->primary_email ?? '—';
            $ownerPhone = $owner->primary_phone ?? '—';
            $ownerRoute = route('admin.agents.show', $owner->id);
        } elseif ($property->owner_type === 'App\Models\RealEstateOffice') {
            $ownerName  = $owner->company_name  ?? '—';
            $ownerEmail = $owner->email_address ?? '—';
            $ownerPhone = $owner->phone_number  ?? '—';
            $ownerRoute = route('admin.offices.show', $owner->id);
        } elseif ($property->owner_type === 'App\Models\User') {
            $ownerName  = $owner->username ?? '—';
            $ownerEmail = $owner->email    ?? '—';
            $ownerPhone = $owner->phone    ?? '—';
            $ownerRoute = route('admin.users.show', $owner->id);
        }
    }

    // ── Viewer stats ──────────────────────────────────────────────
    $allInteractions = $property->interactions ?? collect();
    $impressions     = $allInteractions->where('interaction_type', 'impression');
    $totalViews      = max((int)($property->views ?? 0), $impressions->count());
    $uniqueViewers   = $impressions->whereNotNull('user_id')->unique('user_id')->count();
    $todayViews      = $impressions->filter(fn($i) => $i->created_at && $i->created_at->isToday())->count();
    $weekViews       = $impressions->filter(fn($i) => $i->created_at && $i->created_at->gte(now()->startOfWeek()))->count();

    // Engagement score (simple heuristic 0-100)
    $engScore = min(100, (int)(
        ($totalViews * 2) +
        ($uniqueViewers * 5) +
        ((int)($property->favorites_count ?? 0) * 8) +
        ($todayViews * 3)
    ));

    // Top viewers (most repeat visits)
    $topViewers = $impressions->whereNotNull('user_id')
        ->groupBy('user_id')
        ->map(fn($g) => ['count' => $g->count(), 'last' => $g->max('created_at'), 'user' => $g->first()->user])
        ->sortByDesc('count')
        ->take(10);

    // Price per sqm
    $area = (float)($property->area ?? 0);
    $pricePerSqm = ($area > 0 && $priceUSD > 0) ? round($priceUSD / $area, 0) : 0;

    // Days on market
    $daysOnMarket = $property->created_at ? (int)$property->created_at->diffInDays(now()) : 0;
@endphp

@push('styles')
<style>
    :root {
        --brand:    #303b97;
        --brand-lt: #4b56b2;
        --ink:      #0f172a;
        --ink-2:    #475569;
        --ink-3:    #94a3b8;
        --border:   #e8eaf0;
        --surface:  #f8fafc;
        --green:    #10b981;
        --amber:    #f59e0b;
        --red:      #ef4444;
        --purple:   #8b5cf6;
        --radius:   16px;
        --radius-lg:22px;
        --shadow:   0 4px 14px rgba(15,23,42,.08);
    }

    /* ── Layout ─────────────────────────────────────────────── */
    .pm-wrap { max-width: 1640px; margin: 0 auto; padding-bottom: 3rem; }

    /* ── Topbar ──────────────────────────────────────────────── */
    .pm-topbar { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding:1.5rem 0 1.75rem; flex-wrap:wrap; }
    .pm-crumb  { display:flex; align-items:center; gap:.35rem; font-size:.7rem; font-weight:600; color:var(--ink-3); letter-spacing:.04em; text-transform:uppercase; margin-bottom:.35rem; }
    .pm-crumb a { color:var(--ink-3); text-decoration:none; }
    .pm-crumb a:hover { color:var(--ink); }
    .pm-crumb span { color:var(--border); }
    .pm-title { font-size:1.5rem; font-weight:900; color:var(--ink); letter-spacing:-.025em; }

    /* ── Buttons ──────────────────────────────────────────────── */
    .btn { display:inline-flex; align-items:center; gap:.4rem; padding:.6rem 1.15rem; border-radius:11px; font-size:.79rem; font-weight:800; cursor:pointer; border:none; transition:all .15s; text-decoration:none; white-space:nowrap; }
    .btn--primary { background:var(--brand); color:#fff; box-shadow:0 4px 14px rgba(48,59,151,.3); }
    .btn--primary:hover { background:#232d8a; transform:translateY(-1px); }
    .btn--ghost { background:#fff; border:1.5px solid var(--border); color:var(--ink-2); }
    .btn--ghost:hover { background:var(--surface); color:var(--ink); }
    .btn--green { background:var(--green); color:#fff; }
    .btn--green:hover { background:#059669; }
    .btn--amber { background:var(--amber); color:#fff; }
    .btn--amber:hover { background:#d97706; }
    .btn--red-ghost { background:#fef2f2; border:1.5px solid #fecaca; color:var(--red); }
    .btn--red-ghost:hover { background:#fee2e2; }

    /* ── Cards ────────────────────────────────────────────────── */
    .card { background:#fff; border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden; }
    .card-hd { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px 18px; border-bottom:1px solid var(--border); background:var(--surface); flex-wrap:wrap; }
    .card-ttl { font-size:13px; font-weight:800; color:var(--ink); letter-spacing:-.01em; display:flex; align-items:center; gap:.5rem; }
    .card-body { padding:1.25rem; }
    .eyebrow { font-size:9.5px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; color:var(--ink-3); }

    /* ── Hero gallery ─────────────────────────────────────────── */
    .gallery-hero { height:380px; border-radius:var(--radius-lg) var(--radius-lg) 0 0; overflow:hidden; position:relative; background:#1e2030; }
    .gallery-hero img { width:100%; height:100%; object-fit:cover; }
    .gallery-thumbs { display:flex; gap:6px; padding:6px; background:#111827; border-radius:0 0 var(--radius-lg) var(--radius-lg); overflow-x:auto; scrollbar-width:none; }
    .gallery-thumbs::-webkit-scrollbar { display:none; }
    .gallery-thumb { width:72px; height:56px; border-radius:9px; object-fit:cover; flex-shrink:0; cursor:pointer; opacity:.65; border:2px solid transparent; transition:.15s; }
    .gallery-thumb.active, .gallery-thumb:hover { opacity:1; border-color:#fff; }
    .gallery-overlay { position:absolute; inset:0; background:linear-gradient(to top,rgba(15,23,42,.7) 0%,transparent 40%); pointer-events:none; }
    .gallery-meta { position:absolute; bottom:0; left:0; right:0; padding:1.25rem 1.5rem; color:#fff; }

    /* ── KPI strip ────────────────────────────────────────────── */
    .kpi-strip { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
    @media(max-width:700px){ .kpi-strip { grid-template-columns:repeat(2,1fr); } }
    .kpi-tile { background:#fff; border:1px solid var(--border); border-radius:var(--radius); padding:1rem 1.15rem; transition:.15s; }
    .kpi-tile:hover { box-shadow:var(--shadow); }
    .kpi-val { font-size:1.65rem; font-weight:900; color:var(--ink); line-height:1; letter-spacing:-.03em; }
    .kpi-lbl { font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-3); margin-top:.25rem; }

    /* ── Info rows ────────────────────────────────────────────── */
    .info-row { display:flex; justify-content:space-between; align-items:center; padding:.55rem 0; border-bottom:1px solid var(--border); font-size:.82rem; }
    .info-row:last-child { border-bottom:none; }
    .info-lbl { font-weight:600; color:var(--ink-3); }
    .info-val { font-weight:800; color:var(--ink); }

    /* ── Status badge ─────────────────────────────────────────── */
    .status-pill { display:inline-flex; align-items:center; gap:.3rem; padding:.3rem .75rem; border-radius:99px; font-size:.65rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
    .status--available { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
    .status--pending   { background:#fffbeb; color:#92400e; border:1px solid #fcd34d; }
    .status--sold      { background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; }
    .status--rented    { background:#f5f3ff; color:#5b21b6; border:1px solid #ddd6fe; }
    .status--suspended { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }

    /* ── Engagement bar ───────────────────────────────────────── */
    .eng-bar { height:8px; border-radius:99px; overflow:hidden; background:#f1f2f7; }
    .eng-fill { height:100%; border-radius:99px; background:linear-gradient(90deg,var(--brand),#818cf8); transition:width .5s; }

    /* ── Viewer table ─────────────────────────────────────────── */
    .vt { width:100%; border-collapse:collapse; }
    .vt thead th { padding:.6rem 1rem; font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:var(--ink-3); background:var(--surface); text-align:left; border-bottom:1px solid var(--border); }
    .vt tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
    .vt tbody tr:last-child { border-bottom:none; }
    .vt tbody tr:hover { background:#f5f6fb; }
    .vt td { padding:.75rem 1rem; font-size:.82rem; color:var(--ink-2); vertical-align:middle; }

    /* Interest bar */
    .interest { height:5px; background:#f1f2f7; border-radius:99px; overflow:hidden; width:70px; }
    .interest-fill { height:100%; border-radius:99px; transition:width .3s; }
    .int-high { background:linear-gradient(90deg,#10b981,#6ee7b7); }
    .int-med  { background:linear-gradient(90deg,var(--brand),#818cf8); }
    .int-low  { background:#e2e8f0; }

    /* ── Feature tags ─────────────────────────────────────────── */
    .feat-tag { display:inline-flex; align-items:center; gap:.3rem; padding:.3rem .7rem; border-radius:99px; font-size:.72rem; font-weight:700; background:var(--surface); border:1px solid var(--border); color:var(--ink-2); }

    /* ── Map ──────────────────────────────────────────────────── */
    .map-wrap { border-radius:var(--radius); overflow:hidden; border:1px solid var(--border); }

    /* ── Timeline ─────────────────────────────────────────────── */
    .tl-item { display:flex; gap:.75rem; padding:.65rem 0; border-bottom:1px solid var(--border); }
    .tl-item:last-child { border-bottom:none; }
    .tl-dot { width:8px; height:8px; border-radius:50%; background:var(--brand); flex-shrink:0; margin-top:5px; }

    /* ── Donut gauge ──────────────────────────────────────────── */
    .gauge-wrap { position:relative; width:110px; height:110px; margin:0 auto; }
    .gauge-wrap svg { transform:rotate(-90deg); }
    .gauge-mid { position:absolute; inset:0; display:grid; place-items:center; text-align:center; }

    /* ── Tabs ─────────────────────────────────────────────────── */
    .tab-nav { display:flex; gap:.25rem; background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:.25rem; overflow-x:auto; scrollbar-width:none; }
    .tab-nav::-webkit-scrollbar { display:none; }
    .tab-btn { border:none; background:transparent; padding:.55rem 1.1rem; font-size:.78rem; font-weight:800; color:var(--ink-3); border-radius:9px; cursor:pointer; transition:.14s; white-space:nowrap; }
    .tab-btn.active { background:#fff; color:var(--brand); box-shadow:0 1px 4px rgba(15,23,42,.1); }

    /* ── Scrollbar ────────────────────────────────────────────── */
    .scr::-webkit-scrollbar { width:4px; }
    .scr::-webkit-scrollbar-track { background:transparent; }
    .scr::-webkit-scrollbar-thumb { background:#dde0ea; border-radius:99px; }

    @media(prefers-reduced-motion:reduce){ *,*::before,*::after { animation:none !important; transition:none !important; } }
</style>
@endpush

@section('content')
<div class="pm-wrap">

    {{-- ── Topbar ─────────────────────────────────────────────────── --}}
    <div class="pm-topbar">
        <div>
            <div class="pm-crumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a><span>/</span>
                <a href="{{ route('admin.properties.index') }}">Properties</a><span>/</span>
                Details
            </div>
            <h1 class="pm-title">{{ $propName }}</h1>
            @if($propNameAr)
                <p style="font-size:.8rem;color:var(--ink-3);font-weight:600;margin-top:.2rem">{{ $propNameAr }}</p>
            @endif
            <div style="display:flex;align-items:center;gap:.5rem;margin-top:.5rem;flex-wrap:wrap">
                <span class="status-pill status--{{ $property->status ?? 'default' }}">
                    <i class="fas fa-circle" style="font-size:.45rem"></i> {{ ucfirst($property->status ?? 'Unknown') }}
                </span>
                @if($property->is_boosted)
                    <span class="status-pill" style="background:#fffbeb;color:#92400e;border:1px solid #fcd34d">
                        <i class="fas fa-rocket text-[.55rem]"></i> Boosted
                    </span>
                @endif
                @if($property->verified)
                    <span class="status-pill" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0">
                        <i class="fas fa-shield-check text-[.55rem]"></i> Verified
                    </span>
                @endif
                <span style="font-size:.72rem;font-weight:700;color:var(--ink-3)">
                    ID: <span style="font-family:monospace;color:var(--ink)">{{ $property->id }}</span>
                </span>
            </div>
        </div>

        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
            @if($property->status === 'pending')
            <form action="{{ route('admin.properties.approve', $property->id) }}" method="POST" class="inline">@csrf
                <button type="submit" class="btn btn--green"><i class="fas fa-check"></i> Approve</button>
            </form>
            <form action="{{ route('admin.properties.reject', $property->id) }}" method="POST" class="inline" onsubmit="return confirm('Reject this listing?')">@csrf
                <button type="submit" class="btn btn--red-ghost"><i class="fas fa-xmark"></i> Reject</button>
            </form>
            @endif
            <a href="{{ route('admin.properties.viewers', $property->id) }}" class="btn btn--ghost">
                <i class="fas fa-users"></i> Viewers ({{ $totalViews }})
            </a>
            <a href="{{ route('admin.properties.edit', $property->id) }}" class="btn btn--primary">
                <i class="fas fa-pen"></i> Edit Property
            </a>
            <div style="position:relative" x-data="{ open: false }">
                <button @click="open=!open" class="btn btn--ghost" style="padding:.6rem .75rem"><i class="fas fa-ellipsis-v"></i></button>
                <div x-show="open" @click.outside="open=false" style="position:absolute;right:0;top:calc(100% + .4rem);background:#fff;border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);z-index:60;min-width:180px;overflow:hidden">
                    <a href="{{ route('admin.properties.index') }}" style="display:flex;align-items:center;gap:.6rem;padding:.7rem 1rem;font-size:.78rem;font-weight:700;color:var(--ink-2);text-decoration:none;transition:background .12s" onmouseover="this.style.background='var(--surface)'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-arrow-left"></i> Back to list
                    </a>
                    <form action="{{ route('admin.properties.delete', $property->id) }}" method="POST" onsubmit="return confirm('Permanently delete this property?')">@csrf @method('DELETE')
                        <button type="submit" style="display:flex;align-items:center;gap:.6rem;padding:.7rem 1rem;font-size:.78rem;font-weight:700;color:var(--red);background:transparent;border:none;width:100%;text-align:left;cursor:pointer" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                            <i class="fas fa-trash-alt"></i> Delete permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main layout: left wide, right sidebar ───────────────────── --}}
    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem" class="property-grid">

        {{-- ════════════════ LEFT COLUMN ════════════════ --}}
        <div style="display:flex;flex-direction:column;gap:1.5rem">

            {{-- ── Gallery ────────────────────────────────── --}}
            <div class="card" style="overflow:visible">
                <div class="gallery-hero" id="galleryHero">
                    @if($heroImage)
                        <img id="mainImg" src="{{ asset($heroImage) }}" alt="{{ $propName }}">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.2);font-size:4rem">
                            <i class="fas fa-image"></i>
                        </div>
                    @endif
                    <div class="gallery-overlay"></div>
                    <div class="gallery-meta">
                        <div style="display:flex;align-items:flex-end;justify-content:space-between">
                            <div>
                                <p style="font-size:1.5rem;font-weight:900;letter-spacing:-.02em">
                                    @if($priceUSD > 0) ${{ number_format($priceUSD) }} @endif
                                    @if($priceIQD > 0) <span style="font-size:1rem;opacity:.7">/ {{ number_format($priceIQD) }} IQD</span> @endif
                                </p>
                                @if($pricePerSqm > 0)
                                    <p style="font-size:.75rem;font-weight:700;opacity:.65">${{ number_format($pricePerSqm) }}/m² · {{ $area }}m²</p>
                                @endif
                            </div>
                            <div style="display:flex;gap:.5rem">
                                @if($lat && $lng)
                                <a href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" target="_blank"
                                   style="display:inline-flex;align-items:center;gap:.35rem;padding:.45rem .9rem;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border-radius:9px;font-size:.72rem;font-weight:800;color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.2)">
                                    <i class="fas fa-map-pin"></i> Map
                                </a>
                                @endif
                                @if($property->virtual_tour_url)
                                <a href="{{ $property->virtual_tour_url }}" target="_blank"
                                   style="display:inline-flex;align-items:center;gap:.35rem;padding:.45rem .9rem;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border-radius:9px;font-size:.72rem;font-weight:800;color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.2)">
                                    <i class="fas fa-cube"></i> 360°
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @if(count($images) > 1)
                <div class="gallery-thumbs">
                    @foreach($images as $i => $img)
                    <img src="{{ asset($img) }}" class="gallery-thumb {{ $i === 0 ? 'active' : '' }}"
                         onclick="document.getElementById('mainImg').src='{{ asset($img) }}';document.querySelectorAll('.gallery-thumb').forEach(t=>t.classList.remove('active'));this.classList.add('active')"
                         alt="">
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ── KPI Strip ──────────────────────────────── --}}
            <div class="kpi-strip">
                <div class="kpi-tile">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                        <div style="width:30px;height:30px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;color:#3b82f6;font-size:.78rem"><i class="fas fa-eye"></i></div>
                        <span class="eyebrow">Total Views</span>
                    </div>
                    <div class="kpi-val">{{ number_format($totalViews) }}</div>
                    <p style="font-size:.68rem;color:var(--ink-3);font-weight:600;margin-top:.25rem">{{ $todayViews }} today · {{ $weekViews }} this week</p>
                </div>
                <div class="kpi-tile">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                        <div style="width:30px;height:30px;border-radius:8px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;color:#10b981;font-size:.78rem"><i class="fas fa-users"></i></div>
                        <span class="eyebrow">Unique Viewers</span>
                    </div>
                    <div class="kpi-val">{{ number_format($uniqueViewers) }}</div>
                    <p style="font-size:.68rem;color:var(--ink-3);font-weight:600;margin-top:.25rem">Registered accounts</p>
                </div>
                <div class="kpi-tile">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                        <div style="width:30px;height:30px;border-radius:8px;background:#fef2f2;display:flex;align-items:center;justify-content:center;color:#ef4444;font-size:.78rem"><i class="fas fa-heart"></i></div>
                        <span class="eyebrow">Favorites</span>
                    </div>
                    <div class="kpi-val">{{ number_format($property->favorites_count ?? 0) }}</div>
                    <p style="font-size:.68rem;color:var(--ink-3);font-weight:600;margin-top:.25rem">Saved by users</p>
                </div>
                <div class="kpi-tile">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                        <div style="width:30px;height:30px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center;color:#f59e0b;font-size:.78rem"><i class="fas fa-calendar-days"></i></div>
                        <span class="eyebrow">Days Listed</span>
                    </div>
                    <div class="kpi-val">{{ $daysOnMarket }}</div>
                    <p style="font-size:.68rem;color:var(--ink-3);font-weight:600;margin-top:.25rem">Since {{ $property->created_at?->format('d M Y') }}</p>
                </div>
            </div>

            {{-- ── Tab navigation ──────────────────────────── --}}
            <div class="tab-nav" id="propTabs">
                <button class="tab-btn active" data-tab="overview">Overview</button>
                <button class="tab-btn" data-tab="viewers">Viewers & Engagement</button>
                <button class="tab-btn" data-tab="details">Full Details</button>
                <button class="tab-btn" data-tab="location">Location</button>
                <button class="tab-btn" data-tab="description">Description</button>
            </div>

            {{-- ── TAB: OVERVIEW ───────────────────────────── --}}
            <div id="tab-overview" class="tab-panel">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">

                    {{-- Property specs --}}
                    <div class="card">
                        <div class="card-hd"><span class="card-ttl"><i class="fas fa-house" style="color:var(--brand)"></i> Property Specs</span></div>
                        <div class="card-body">
                            <div class="info-row"><span class="info-lbl">Category</span><span class="info-val" style="text-transform:capitalize">{{ $category }}</span></div>
                            <div class="info-row"><span class="info-lbl">Listing Type</span><span class="info-val">{{ ucfirst($property->listing_type === 'sell' ? 'For Sale' : ($property->listing_type ?? '—')) }}</span></div>
                            <div class="info-row"><span class="info-lbl">Total Area</span><span class="info-val">{{ $area > 0 ? number_format($area) . ' m²' : '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Floor</span><span class="info-val">{{ $property->floor_number ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Year Built</span><span class="info-val">{{ $property->year_built ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Rental Period</span><span class="info-val" style="text-transform:capitalize">{{ $property->rental_period ?? 'N/A' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Furnishing</span><span class="info-val" style="text-transform:capitalize">{{ $furnLevel ?: '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Build Type</span><span class="info-val" style="text-transform:capitalize">{{ $buildType ?: '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Build Quality</span><span class="info-val" style="text-transform:capitalize">{{ $buildQuality ?: '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Energy Rating</span><span class="info-val">{{ $property->energy_rating ?? '—' }}</span></div>
                        </div>
                    </div>

                    {{-- Rooms & utilities --}}
                    <div style="display:flex;flex-direction:column;gap:1.25rem">
                        <div class="card">
                            <div class="card-hd"><span class="card-ttl"><i class="fas fa-bed" style="color:var(--purple)"></i> Rooms</span></div>
                            <div class="card-body" style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem">
                                @foreach([['Bedrooms',$bedrooms,'fa-bed','#3b82f6'],['Bathrooms',$bathrooms,'fa-bath','#8b5cf6'],['Living Rooms',$living,'fa-couch','#10b981']] as [$lbl,$val,$ico,$clr])
                                <div style="text-align:center;padding:.75rem;background:var(--surface);border-radius:12px;border:1px solid var(--border)">
                                    <i class="fas {{ $ico }}" style="color:{{ $clr }};font-size:1rem;display:block;margin-bottom:.35rem"></i>
                                    <div style="font-size:1.5rem;font-weight:900;color:var(--ink)">{{ $val }}</div>
                                    <div style="font-size:.62rem;font-weight:700;color:var(--ink-3);text-transform:uppercase;letter-spacing:.05em">{{ $lbl }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-hd"><span class="card-ttl"><i class="fas fa-plug" style="color:var(--amber)"></i> Utilities</span></div>
                            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
                                @foreach([['Furnished','furnished','fa-couch'],['Electricity','electricity','fa-bolt'],['Water','water','fa-droplet'],['Internet','internet','fa-wifi']] as [$lbl,$key,$ico])
                                <div style="display:flex;align-items:center;gap:.5rem;padding:.6rem .75rem;border-radius:10px;background:{{ $property->$key ? '#ecfdf5' : 'var(--surface)' }};border:1px solid {{ $property->$key ? '#a7f3d0' : 'var(--border)' }}">
                                    <i class="fas {{ $ico }}" style="color:{{ $property->$key ? '#10b981' : 'var(--ink-3)' }};font-size:.8rem"></i>
                                    <span style="font-size:.78rem;font-weight:700;color:{{ $property->$key ? '#065f46' : 'var(--ink-3)' }}">{{ $lbl }}</span>
                                    <i class="fas {{ $property->$key ? 'fa-check' : 'fa-xmark' }}" style="margin-left:auto;font-size:.65rem;color:{{ $property->$key ? '#10b981' : '#cbd5e1' }}"></i>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Engagement gauge --}}
                        <div class="card">
                            <div class="card-hd"><span class="card-ttl"><i class="fas fa-chart-pie" style="color:var(--brand)"></i> Engagement Score</span></div>
                            <div class="card-body" style="display:flex;align-items:center;gap:1.25rem">
                                <div class="gauge-wrap" style="flex-shrink:0">
                                    @php $circ = 2*M_PI*46; $dash = round(($engScore/100)*$circ,2); @endphp
                                    <svg width="110" height="110" viewBox="0 0 110 110">
                                        <circle cx="55" cy="55" r="46" fill="none" stroke="#f1f2f7" stroke-width="11"></circle>
                                        <circle cx="55" cy="55" r="46" fill="none" stroke="url(#engGrad)" stroke-width="11" stroke-linecap="round"
                                                stroke-dasharray="{{ $dash }} {{ $circ }}" style="transform:rotate(-90deg);transform-origin:center"></circle>
                                        <defs>
                                            <linearGradient id="engGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                                <stop offset="0%" stop-color="#303b97"/>
                                                <stop offset="100%" stop-color="#818cf8"/>
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                    <div class="gauge-mid">
                                        <div>
                                            <p style="font-size:1.35rem;font-weight:900;color:var(--ink);line-height:1">{{ $engScore }}</p>
                                            <p style="font-size:.55rem;font-weight:800;color:var(--ink-3);text-transform:uppercase;letter-spacing:.06em">Score</p>
                                        </div>
                                    </div>
                                </div>
                                <div style="flex:1">
                                    <p style="font-size:.82rem;font-weight:800;color:var(--ink);margin-bottom:.75rem">
                                        @if($engScore >= 70) 🔥 High Demand
                                        @elseif($engScore >= 40) ⚡ Moderate Interest
                                        @else 💤 Low Activity @endif
                                    </p>
                                    @foreach([['Views','fa-eye',$totalViews,100],['Unique','fa-users',$uniqueViewers,50],['Saved','fa-heart',(int)($property->favorites_count??0),30]] as [$lbl,$ico,$n,$mx])
                                    <div style="margin-bottom:.4rem">
                                        <div style="display:flex;justify-content:space-between;font-size:.68rem;font-weight:700;color:var(--ink-3);margin-bottom:.2rem">
                                            <span><i class="fas {{ $ico }} mr-1"></i>{{ $lbl }}</span><span>{{ $n }}</span>
                                        </div>
                                        <div class="eng-bar"><div class="eng-fill" style="width:{{ min(100,round(($n/$mx)*100)) }}%"></div></div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Features & amenities --}}
                @php
                    $feats = is_string($property->features) ? json_decode($property->features, true) : $property->features;
                    $amens = is_string($property->amenities) ? json_decode($property->amenities, true) : $property->amenities;
                    $nearby = is_string($property->nearby_amenities) ? json_decode($property->nearby_amenities, true) : $property->nearby_amenities;
                @endphp
                @if((is_array($feats) && count($feats)) || (is_array($amens) && count($amens)))
                <div class="card">
                    <div class="card-hd"><span class="card-ttl"><i class="fas fa-list-check" style="color:var(--green)"></i> Features & Amenities</span></div>
                    <div class="card-body">
                        @if(is_array($feats) && count($feats))
                        <p style="font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-3);margin-bottom:.65rem">Features</p>
                        <div style="display:flex;flex-wrap:wrap;gap:.45rem;margin-bottom:1rem">
                            @foreach($feats as $f)
                                @if(is_string($f) && trim($f))
                                <span class="feat-tag"><i class="fas fa-check" style="color:var(--green);font-size:.6rem"></i> {{ $f }}</span>
                                @endif
                            @endforeach
                        </div>
                        @endif
                        @if(is_array($amens) && count($amens))
                        <p style="font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-3);margin-bottom:.65rem">Amenities</p>
                        <div style="display:flex;flex-wrap:wrap;gap:.45rem">
                            @foreach($amens as $a)
                                @if(is_string($a) && trim($a))
                                <span class="feat-tag" style="background:#eff6ff;border-color:#bfdbfe;color:#1e40af"><i class="fas fa-star" style="color:#3b82f6;font-size:.6rem"></i> {{ $a }}</span>
                                @endif
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- ── TAB: VIEWERS & ENGAGEMENT ───────────────── --}}
            <div id="tab-viewers" class="tab-panel hidden">
                {{-- Summary row --}}
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.25rem">
                    @foreach([['Total Impressions',$totalViews,'fa-eye','#3b82f6'],['Unique Registered',$uniqueViewers,'fa-user-check','#8b5cf6'],['Today\'s Views',$todayViews,'fa-sun','#10b981'],['This Week',$weekViews,'fa-calendar','#f59e0b']] as [$lbl,$val,$ico,$clr])
                    <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:.9rem 1rem">
                        <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.4rem">
                            <i class="fas {{ $ico }}" style="color:{{ $clr }};font-size:.78rem"></i>
                            <span class="eyebrow">{{ $lbl }}</span>
                        </div>
                        <div style="font-size:1.5rem;font-weight:900;color:var(--ink);line-height:1">{{ number_format($val) }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Top viewer table --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-ttl"><i class="fas fa-trophy" style="color:var(--amber)"></i> Most Engaged Viewers</span>
                        <a href="{{ route('admin.properties.viewers', $property->id) }}" class="btn btn--ghost" style="font-size:.72rem;padding:.4rem .9rem">Full Viewer Log</a>
                    </div>
                    <div style="overflow-x:auto">
                        <table class="vt">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Contact</th>
                                    <th>Views</th>
                                    <th>Interest</th>
                                    <th>Last Visit</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topViewers as $rank => $data)
                                @php $u = $data['user']; @endphp
                                @if($u)
                                <tr>
                                    <td>
                                        <span style="width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:900;background:{{ $rank === 0 ? 'var(--brand)' : 'var(--surface)' }};color:{{ $rank === 0 ? '#fff' : 'var(--ink-3)' }}">
                                            {{ $rank + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:.65rem">
                                            @if($u->photo_image)
                                                <img src="{{ $u->photo_image }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid var(--border)" alt="">
                                            @else
                                                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--brand),#818cf8);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:900;color:#fff;flex-shrink:0">
                                                    {{ strtoupper(substr($u->username ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div style="font-weight:800;color:var(--ink);font-size:.82rem">{{ $u->username ?? '—' }}</div>
                                                <div style="font-size:.67rem;color:{{ $u->is_verified ? '#10b981' : 'var(--ink-3)' }};font-weight:700">
                                                    {{ $u->is_verified ? '✓ Verified' : 'Unverified' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size:.78rem;font-weight:600;color:var(--ink-2)">{{ $u->email ?? '—' }}</div>
                                        @if($u->phone)<div style="font-size:.72rem;font-weight:600;color:var(--ink-3)">{{ $u->phone }}</div>@endif
                                    </td>
                                    <td>
                                        <span style="font-size:1rem;font-weight:900;color:var(--ink)">{{ $data['count'] }}</span>
                                        <span style="font-size:.65rem;font-weight:700;color:var(--ink-3)"> visits</span>
                                    </td>
                                    <td>
                                        @php $pct = min(100, (int)round(($data['count']/max(1,$topViewers->max('count')))*100)); @endphp
                                        <div style="display:flex;align-items:center;gap:.5rem">
                                            <div class="interest">
                                                <div class="interest-fill {{ $data['count'] > 5 ? 'int-high' : ($data['count'] > 2 ? 'int-med' : 'int-low') }}" style="width:{{ $pct }}%"></div>
                                            </div>
                                            <span style="font-size:.65rem;font-weight:800;color:{{ $data['count'] > 5 ? '#10b981' : ($data['count'] > 2 ? 'var(--brand)' : 'var(--ink-3)') }}">
                                                @if($data['count'] > 5) 🔥 High @elseif($data['count'] > 2) ⚡ Med @else 💤 Low @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td style="font-size:.75rem;font-weight:700;color:var(--ink-3)">{{ \Carbon\Carbon::parse($data['last'])->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $u->id) }}" class="btn btn--ghost" style="font-size:.68rem;padding:.3rem .65rem">Profile →</a>
                                    </td>
                                </tr>
                                @endif
                                @empty
                                <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:var(--ink-3);font-size:.84rem">No registered viewers yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- View activity chart placeholder --}}
                <div class="card" style="margin-top:1.25rem">
                    <div class="card-hd"><span class="card-ttl"><i class="fas fa-chart-area" style="color:var(--brand)"></i> View Activity (Last 30 Days)</span></div>
                    <div style="padding:1rem"><div id="viewsChart"></div></div>
                </div>
            </div>

            {{-- ── TAB: FULL DETAILS ──────────────────────── --}}
            <div id="tab-details" class="tab-panel hidden">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
                    <div class="card">
                        <div class="card-hd"><span class="card-ttl"><i class="fas fa-info-circle" style="color:var(--brand)"></i> Pricing Details</span></div>
                        <div class="card-body">
                            <div class="info-row"><span class="info-lbl">Price (USD)</span><span class="info-val">${{ number_format($priceUSD) }}</span></div>
                            <div class="info-row"><span class="info-lbl">Price (IQD)</span><span class="info-val">{{ number_format($priceIQD) }} IQD</span></div>
                            <div class="info-row"><span class="info-lbl">Per m²</span><span class="info-val">${{ number_format($pricePerSqm) }}</span></div>
                            <div class="info-row"><span class="info-lbl">Rental Period</span><span class="info-val" style="text-transform:capitalize">{{ $property->rental_period ?? 'N/A' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Consultation Fee</span><span class="info-val">{{ $property->consultation_fee ? '$'.number_format($property->consultation_fee) : 'Free' }}</span></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-hd"><span class="card-ttl"><i class="fas fa-hard-hat" style="color:var(--amber)"></i> Construction</span></div>
                        <div class="card-body">
                            <div class="info-row"><span class="info-lbl">Build Type</span><span class="info-val" style="text-transform:capitalize">{{ $buildType ?: '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Quality</span><span class="info-val" style="text-transform:capitalize">{{ $buildQuality ?: '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Year Built</span><span class="info-val">{{ $property->year_built ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Energy Rating</span><span class="info-val">{{ $property->energy_rating ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Floor Number</span><span class="info-val">{{ $property->floor_number ?? '—' }}</span></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-hd"><span class="card-ttl"><i class="fas fa-toggle-on" style="color:var(--green)"></i> System Status</span></div>
                        <div class="card-body">
                            @foreach(['is_active'=>'Active Listing','published'=>'Publicly Visible','verified'=>'Verified Badge','is_boosted'=>'Boosted Listing'] as $k=>$lbl)
                            <div class="info-row">
                                <span class="info-lbl">{{ $lbl }}</span>
                                <span style="display:inline-flex;align-items:center;gap:.35rem;font-size:.72rem;font-weight:800;color:{{ $property->$k ? '#10b981' : '#ef4444' }}">
                                    <i class="fas {{ $property->$k ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    {{ $property->$k ? 'Yes' : 'No' }}
                                </span>
                            </div>
                            @endforeach
                            @if($property->boost_start_date)
                            <div class="info-row"><span class="info-lbl">Boost Start</span><span class="info-val" style="font-size:.78rem">{{ $property->boost_start_date }}</span></div>
                            <div class="info-row"><span class="info-lbl">Boost End</span><span class="info-val" style="font-size:.78rem">{{ $property->boost_end_date ?? '—' }}</span></div>
                            @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-hd"><span class="card-ttl"><i class="fas fa-calendar" style="color:var(--purple)"></i> Availability</span></div>
                        <div class="card-body">
                            @php $avail = is_string($property->availability) ? json_decode($property->availability, true) : $property->availability; $avail = is_array($avail) ? $avail : []; @endphp
                            <div class="info-row"><span class="info-lbl">Status</span><span class="info-val" style="text-transform:capitalize">{{ $property->status ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">From</span><span class="info-val">{{ $avail['from'] ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">To</span><span class="info-val">{{ $avail['to'] ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Created</span><span class="info-val">{{ $property->created_at?->format('d M Y') }}</span></div>
                            <div class="info-row"><span class="info-lbl">Last Updated</span><span class="info-val">{{ $property->updated_at?->format('d M Y') }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- SEO --}}
                @php $seo = is_string($property->seo_metadata) ? json_decode($property->seo_metadata,true) : $property->seo_metadata; $seo = is_array($seo) ? $seo : []; @endphp
                @if(!empty($seo['title']) || !empty($seo['description']))
                <div class="card" style="margin-top:1.25rem">
                    <div class="card-hd"><span class="card-ttl"><i class="fas fa-search" style="color:var(--brand)"></i> SEO Metadata</span></div>
                    <div class="card-body">
                        @if(!empty($seo['title']))<div class="info-row"><span class="info-lbl">Meta Title</span><span class="info-val" style="font-size:.78rem">{{ $seo['title'] }}</span></div>@endif
                        @if(!empty($seo['description']))<div class="info-row"><span class="info-lbl">Meta Desc</span><span class="info-val" style="font-size:.78rem">{{ $seo['description'] }}</span></div>@endif
                        @if(!empty($seo['keywords']))<div class="info-row"><span class="info-lbl">Keywords</span><span class="info-val" style="font-size:.78rem">{{ is_array($seo['keywords']) ? implode(', ',$seo['keywords']) : $seo['keywords'] }}</span></div>@endif
                    </div>
                </div>
                @endif
            </div>

            {{-- ── TAB: LOCATION ───────────────────────────── --}}
            <div id="tab-location" class="tab-panel hidden">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
                    <div class="card">
                        <div class="card-hd"><span class="card-ttl"><i class="fas fa-map-pin" style="color:var(--red)"></i> Address</span></div>
                        <div class="card-body">
                            <div class="info-row"><span class="info-lbl">City</span><span class="info-val">{{ $city ?: '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">District</span><span class="info-val">{{ $district ?: '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Full Address</span><span class="info-val" style="font-size:.78rem;max-width:200px;text-align:right">{{ $property->address ?? '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Latitude</span><span class="info-val" style="font-family:monospace">{{ $lat ?: '—' }}</span></div>
                            <div class="info-row"><span class="info-lbl">Longitude</span><span class="info-val" style="font-family:monospace">{{ $lng ?: '—' }}</span></div>
                        </div>
                        @if($lat && $lng)
                        <div style="padding:.75rem 1.25rem;border-top:1px solid var(--border)">
                            <a href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" target="_blank" class="btn btn--ghost" style="width:100%;justify-content:center">
                                <i class="fas fa-external-link-alt"></i> Open in Google Maps
                            </a>
                        </div>
                        @endif
                    </div>
                    @if($lat && $lng)
                    <div class="map-wrap" style="height:340px">
                        <iframe width="100%" height="340" frameborder="0" style="border:0;display:block"
                            src="https://maps.google.com/maps?q={{ $lat }},{{ $lng }}&hl=en&z=15&output=embed"></iframe>
                    </div>
                    @else
                    <div style="display:flex;align-items:center;justify-content:center;background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);height:340px;color:var(--ink-3);font-size:.84rem;font-weight:600">
                        <div style="text-align:center"><i class="fas fa-map-location-dot" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3"></i>No coordinates set</div>
                    </div>
                    @endif
                </div>

                @if(is_array($nearby) && count($nearby))
                <div class="card" style="margin-top:1.25rem">
                    <div class="card-hd"><span class="card-ttl"><i class="fas fa-location-dot" style="color:var(--green)"></i> Nearby Amenities</span></div>
                    <div class="card-body" style="display:flex;flex-wrap:wrap;gap:.45rem">
                        @foreach($nearby as $n)
                            @php $nLabel = is_array($n) ? ($n['name'] ?? '') : $n; @endphp
                            @if(is_string($nLabel) && trim($nLabel))
                            <span class="feat-tag" style="background:#f0fdf4;border-color:#a7f3d0;color:#065f46"><i class="fas fa-location-dot" style="color:#10b981;font-size:.6rem"></i> {{ $nLabel }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- ── TAB: DESCRIPTION ────────────────────────── --}}
            <div id="tab-description" class="tab-panel hidden">
                <div class="card">
                    <div class="card-hd"><span class="card-ttl"><i class="fas fa-align-left" style="color:var(--brand)"></i> Property Descriptions</span></div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem">
                        @if($descEn)
                        <div>
                            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.65rem">
                                <span style="width:20px;height:14px;border-radius:2px;background:#1e40af;display:inline-block"></span>
                                <span style="font-size:.7rem;font-weight:800;color:var(--ink-3);text-transform:uppercase;letter-spacing:.06em">English</span>
                            </div>
                            <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:1rem 1.15rem;font-size:.88rem;color:var(--ink-2);line-height:1.7;font-weight:500">{{ $descEn }}</div>
                        </div>
                        @endif
                        @if($descAr)
                        <div>
                            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.65rem">
                                <span style="width:20px;height:14px;border-radius:2px;background:#15803d;display:inline-block"></span>
                                <span style="font-size:.7rem;font-weight:800;color:var(--ink-3);text-transform:uppercase;letter-spacing:.06em">Arabic</span>
                            </div>
                            <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:1rem 1.15rem;font-size:.88rem;color:var(--ink-2);line-height:1.8;font-weight:500;direction:rtl;text-align:right">{{ $descAr }}</div>
                        </div>
                        @endif
                        @if(!$descEn && !$descAr)
                        <div style="text-align:center;padding:2rem;color:var(--ink-3)"><i class="fas fa-align-left" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3"></i>No description provided.</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>{{-- /.left --}}

        {{-- ════════════════ RIGHT SIDEBAR ════════════════ --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem">

            {{-- Owner card --}}
            <div class="card">
                <div class="card-hd"><span class="card-ttl"><i class="fas fa-user-tie" style="color:var(--brand)"></i> Owner</span></div>
                <div class="card-body">
                    <div style="display:flex;align-items:center;gap:.85rem;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--border)">
                        <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--brand),#818cf8);display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:900;color:#fff;flex-shrink:0">
                            {{ strtoupper(substr($ownerName, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight:800;color:var(--ink);font-size:.9rem">{{ $ownerName }}</div>
                            <span style="font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;padding:.15rem .5rem;border-radius:99px;background:{{ $ownerType === 'Agent' ? '#ecfdf5' : ($ownerType === 'RealEstateOffice' ? '#f5f3ff' : '#eff6ff') }};color:{{ $ownerType === 'Agent' ? '#065f46' : ($ownerType === 'RealEstateOffice' ? '#5b21b6' : '#1e40af') }}">{{ $ownerType }}</span>
                        </div>
                    </div>
                    <div class="info-row"><span class="info-lbl">Email</span><a href="mailto:{{ $ownerEmail }}" style="font-size:.78rem;font-weight:700;color:var(--brand);text-decoration:none;word-break:break-all">{{ $ownerEmail }}</a></div>
                    <div class="info-row"><span class="info-lbl">Phone</span><a href="tel:{{ $ownerPhone }}" style="font-size:.78rem;font-weight:700;color:var(--green);text-decoration:none">{{ $ownerPhone }}</a></div>
                    <div class="info-row"><span class="info-lbl">Owner ID</span><span style="font-family:monospace;font-size:.72rem;color:var(--ink-2);font-weight:700">{{ $property->owner_id }}</span></div>
                    @if($ownerRoute)
                    <div style="margin-top:.9rem">
                        <a href="{{ $ownerRoute }}" class="btn btn--ghost" style="width:100%;justify-content:center;font-size:.75rem">
                            <i class="fas fa-external-link-alt"></i> View {{ $ownerType }} Profile
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="card">
                <div class="card-hd"><span class="card-ttl"><i class="fas fa-bolt" style="color:var(--amber)"></i> Quick Actions</span></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.5rem">
                    <a href="{{ route('admin.properties.edit', $property->id) }}" class="btn btn--primary" style="justify-content:center">
                        <i class="fas fa-pen"></i> Edit Property
                    </a>
                    @if($property->status === 'pending')
                    <form action="{{ route('admin.properties.approve', $property->id) }}" method="POST">@csrf
                        <button type="submit" class="btn btn--green" style="width:100%;justify-content:center">
                            <i class="fas fa-check"></i> Approve Listing
                        </button>
                    </form>
                    <form action="{{ route('admin.properties.reject', $property->id) }}" method="POST" onsubmit="return confirm('Reject?')">@csrf
                        <button type="submit" class="btn btn--red-ghost" style="width:100%;justify-content:center">
                            <i class="fas fa-xmark"></i> Reject Listing
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('admin.properties.toggleActive', $property->id) }}" method="POST">@csrf
                        <button type="submit" class="btn btn--ghost" style="width:100%;justify-content:center;font-size:.75rem">
                            <i class="fas {{ $property->is_active ? 'fa-toggle-on text-green-500' : 'fa-toggle-off' }}"></i>
                            {{ $property->is_active ? 'Deactivate' : 'Activate' }} Listing
                        </button>
                    </form>
                    <a href="{{ route('admin.properties.viewers', $property->id) }}" class="btn btn--ghost" style="justify-content:center;font-size:.75rem">
                        <i class="fas fa-users"></i> Full Viewer Log ({{ $totalViews }})
                    </a>
                    <a href="{{ route('admin.properties.index') }}" class="btn btn--ghost" style="justify-content:center;font-size:.75rem">
                        <i class="fas fa-arrow-left"></i> Back to Properties
                    </a>
                </div>
            </div>

            {{-- Stats summary --}}
            <div class="card">
                <div class="card-hd"><span class="card-ttl"><i class="fas fa-chart-bar" style="color:var(--purple)"></i> Listing Stats</span></div>
                <div class="card-body">
                    <div class="info-row"><span class="info-lbl">Total Views</span><span class="info-val num">{{ number_format($totalViews) }}</span></div>
                    <div class="info-row"><span class="info-lbl">Today</span><span class="info-val num">{{ $todayViews }}</span></div>
                    <div class="info-row"><span class="info-lbl">This Week</span><span class="info-val num">{{ $weekViews }}</span></div>
                    <div class="info-row"><span class="info-lbl">Unique Viewers</span><span class="info-val num">{{ $uniqueViewers }}</span></div>
                    <div class="info-row"><span class="info-lbl">Favorites</span><span class="info-val num">{{ number_format($property->favorites_count ?? 0) }}</span></div>
                    <div class="info-row"><span class="info-lbl">Rating</span><span class="info-val">{{ number_format((float)($property->rating ?? 0), 1) }} / 5</span></div>
                    <div class="info-row"><span class="info-lbl">Days Listed</span><span class="info-val">{{ $daysOnMarket }} days</span></div>
                    <div class="info-row"><span class="info-lbl">Engagement</span>
                        <span style="font-weight:800;color:{{ $engScore >= 70 ? 'var(--green)' : ($engScore >= 40 ? 'var(--brand)' : 'var(--ink-3)') }}">
                            {{ $engScore }}/100
                        </span>
                    </div>
                </div>
            </div>

            {{-- Recent activity timeline --}}
            <div class="card">
                <div class="card-hd"><span class="card-ttl"><i class="fas fa-clock-rotate-left" style="color:var(--green)"></i> Recent Viewers</span></div>
                <div class="card-body scr" style="max-height:320px;overflow-y:auto;padding:.75rem">
                    @forelse($impressions->whereNotNull('user_id')->sortByDesc('created_at')->take(12) as $iv)
                    @if($iv->user)
                    <div class="tl-item">
                        <div class="tl-dot" style="margin-top:6px"></div>
                        <div>
                            <div style="font-size:.8rem;font-weight:700;color:var(--ink)">{{ $iv->user->username ?? '—' }}</div>
                            <div style="font-size:.68rem;font-weight:600;color:var(--ink-3)">{{ $iv->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <div style="text-align:center;padding:1.5rem;color:var(--ink-3);font-size:.82rem">No viewer history yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Floor plan / Virtual tour --}}
            @if($property->virtual_tour_url || $property->floor_plan_url)
            <div class="card">
                <div class="card-hd"><span class="card-ttl"><i class="fas fa-cube" style="color:var(--purple)"></i> Media Links</span></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.5rem">
                    @if($property->virtual_tour_url)
                    <a href="{{ $property->virtual_tour_url }}" target="_blank" class="btn btn--ghost" style="justify-content:center;font-size:.75rem">
                        <i class="fas fa-cube"></i> 360° Virtual Tour
                    </a>
                    @endif
                    @if($property->floor_plan_url)
                    <a href="{{ $property->floor_plan_url }}" target="_blank" class="btn btn--ghost" style="justify-content:center;font-size:.75rem">
                        <i class="fas fa-ruler-combined"></i> Floor Plan
                    </a>
                    @endif
                </div>
            </div>
            @endif

        </div>{{-- /.sidebar --}}

    </div>{{-- /.grid --}}
</div>

<style>
.property-grid { display:grid; grid-template-columns:1fr 340px; gap:1.5rem; }
@media(max-width:1100px){ .property-grid { grid-template-columns:1fr; } }
.num { font-variant-numeric: tabular-nums; }
</style>
@endsection

@push('scripts')
<script>
// ── Tab switcher ───────────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        btn.classList.add('active');
        const panel = document.getElementById('tab-' + btn.dataset.tab);
        if (panel) panel.classList.remove('hidden');

        // Render views chart when viewers tab opens
        if (btn.dataset.tab === 'viewers' && !window.viewsChartRendered) {
            renderViewsChart();
            window.viewsChartRendered = true;
        }
    });
});

// ── Views activity sparkline (ApexCharts) ─────────────────────────
function renderViewsChart() {
    if (typeof ApexCharts === 'undefined') return;
    const el = document.querySelector('#viewsChart');
    if (!el) return;

    // Build last-30-days labels
    const labels = [], data = [];
    for (let i = 29; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        data.push(0); // server-rendered count would fill this
    }

    // Fill with real data from PHP
    const rawCounts = @json(
        collect($impressions->toArray())
            ->groupBy(fn($i) => \Carbon\Carbon::parse($i['created_at'] ?? null)?->format('Y-m-d'))
            ->map->count()
            ->toArray()
    );

    const filledData = labels.map((lbl, idx) => {
        const d = new Date();
        d.setDate(d.getDate() - (29 - idx));
        const key = d.toISOString().slice(0, 10);
        return rawCounts[key] ?? 0;
    });

    new ApexCharts(el, {
        series: [{ name: 'Views', data: filledData }],
        chart: { type: 'area', height: 200, fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false } },
        colors: ['#303b97'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        fill: { type: 'gradient', gradient: { opacityFrom: .3, opacityTo: 0 } },
        markers: { size: 0, hover: { size: 5 } },
        xaxis: {
            categories: labels,
            axisBorder: { show: false }, axisTicks: { show: false },
            labels: { rotate: 0, hideOverlappingLabels: true, style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } },
        },
        yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } }, min: 0 },
        grid: { borderColor: '#f1f2f7', strokeDashArray: 5 },
        tooltip: { y: { formatter: v => v + ' views' } },
    }).render();
}
</script>
@endpush
