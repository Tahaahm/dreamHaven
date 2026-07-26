@extends('layouts.admin-layout')

@section('title', 'Edit Property — ' . (is_array($property->name) ? ($property->name['en'] ?? 'Property') : $property->name))

@php
    // ── Safe data extraction ────────────────────────────────────────
    $safe = fn($v) => is_array($v) ? '' : (string)($v ?? '');

    $nameData = is_string($property->name) ? json_decode($property->name,true) : $property->name;
    $nameEn = is_array($nameData) ? ($nameData['en'] ?? '') : (is_string($nameData) ? $nameData : '');
    $nameAr = is_array($nameData) ? ($nameData['ar'] ?? '') : '';
    $nameKu = is_array($nameData) ? ($nameData['ku'] ?? '') : '';

    $descData = is_string($property->description) ? json_decode($property->description,true) : $property->description;
    $descEn = is_array($descData) ? ($descData['en'] ?? '') : (is_string($descData) ? $descData : '');
    $descAr = is_array($descData) ? ($descData['ar'] ?? '') : '';
    $descKu = is_array($descData) ? ($descData['ku'] ?? '') : '';

    $priceData = is_string($property->price) ? json_decode($property->price,true) : $property->price;
    $priceUSD = is_array($priceData) ? (float)($priceData['usd'] ?? 0) : 0;
    $priceIQD = is_array($priceData) ? (float)($priceData['iqd'] ?? 0) : 0;

    $typeData = is_string($property->type) ? json_decode($property->type,true) : $property->type;
    $typeCategory = is_array($typeData) ? ($typeData['category'] ?? '') : (is_string($typeData) ? $typeData : '');

    $roomsData = is_string($property->rooms) ? json_decode($property->rooms,true) : $property->rooms;
    $roomsData = is_array($roomsData) ? $roomsData : [];
    $roomBed   = $roomsData['bedroom']['count']    ?? $roomsData['bedroom']    ?? 0;
    $roomBath  = $roomsData['bathroom']['count']   ?? $roomsData['bathroom']   ?? 0;
    $roomLive  = $roomsData['living_room']['count'] ?? $roomsData['living_room'] ?? 0;

    $locsData = is_string($property->locations) ? json_decode($property->locations,true) : $property->locations;
    $locsData = is_array($locsData) ? $locsData : [];
    $firstLoc = $locsData[0] ?? $locsData;
    $lat = (float)($firstLoc['lat'] ?? 0);
    $lng = (float)($firstLoc['lng'] ?? 0);

    $addrData = is_string($property->address_details) ? json_decode($property->address_details,true) : $property->address_details;
    $addrData = is_array($addrData) ? $addrData : [];
    $cityVal = is_array($addrData['city'] ?? null) ? ($addrData['city']['en'] ?? '') : ($addrData['city'] ?? '');
    $distVal = is_array($addrData['district'] ?? null) ? ($addrData['district']['en'] ?? '') : ($addrData['district'] ?? '');

    $availData = is_array($property->availability) ? $property->availability : [];
    $availFrom = $availData['from'] ?? '';
    $availTo   = $availData['to']   ?? '';

    $constrData = is_array($property->construction_details) ? $property->construction_details : [];
    $buildType    = $constrData['type']    ?? '';
    $buildQuality = $constrData['quality'] ?? '';

    $energyData   = is_array($property->energy_details) ? $property->energy_details : [];
    $energyCert   = $energyData['certificate'] ?? '';
    $energyKwh    = $energyData['consumption']  ?? '';

    $furnData   = is_array($property->furnishing_details) ? $property->furnishing_details : [];
    $furnLevel  = $furnData['level'] ?? '';
    $furnItems  = is_array($furnData['items'] ?? null) ? implode(', ',$furnData['items']) : ($furnData['items'] ?? '');

    $seoData     = is_array($property->seo_metadata) ? $property->seo_metadata : [];
    $seoTitle    = $seoData['title']       ?? '';
    $seoDesc     = $seoData['description'] ?? '';
    $seoKeywords = is_array($seoData['keywords'] ?? null) ? implode(', ',$seoData['keywords']) : ($seoData['keywords'] ?? '');

    $amenData  = is_string($property->amenities)        ? json_decode($property->amenities,true)        : $property->amenities;
    $featData  = is_string($property->features)         ? json_decode($property->features,true)         : $property->features;
    $nearData  = is_string($property->nearby_amenities) ? json_decode($property->nearby_amenities,true) : $property->nearby_amenities;
    $amenStr   = is_array($amenData) ? implode(', ',$amenData) : (is_string($amenData) ? $amenData : '');
    $featStr   = is_array($featData) ? implode(', ',$featData) : (is_string($featData) ? $featData : '');
    $nearStr   = is_array($nearData) ? implode(', ',array_map(fn($n) => is_array($n)?($n['name']??''):$n, $nearData)) : (is_string($nearData) ? $nearData : '');

    $imagesData = is_string($property->images) ? json_decode($property->images,true) : $property->images;
    $images     = is_array($imagesData) ? array_filter($imagesData,fn($i)=>is_string($i)) : [];

    $ownerLabel = '';
    if ($property->owner) {
        $ownerLabel = $property->owner->agent_name ?? $property->owner->company_name ?? $property->owner->username ?? '';
    }
@endphp

@push('styles')
<style>
    :root {
        --brand:    #303b97;
        --brand-lt: #4b56b2;
        --ink:      #0f172a;
        --ink-2:    #475569;
        --ink-3:    #94a3b8;
        --border:   #e2e8f0;
        --surface:  #f8fafc;
        --green:    #10b981;
        --amber:    #f59e0b;
        --red:      #ef4444;
        --radius:   14px;
        --radius-lg:20px;
        --shadow-sm:0 1px 3px rgba(15,23,42,.06);
    }

    /* ── Layout ─────────────────────────────────────────────── */
    .pe-wrap { max-width:1200px; margin:0 auto; padding-bottom:5rem; }

    /* ── Topbar ──────────────────────────────────────────────── */
    .pe-topbar { display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.5rem 0 1.75rem;flex-wrap:wrap; }
    .pe-crumb  { display:flex;align-items:center;gap:.35rem;font-size:.7rem;font-weight:600;color:var(--ink-3);letter-spacing:.04em;text-transform:uppercase;margin-bottom:.35rem; }
    .pe-crumb a { color:var(--ink-3);text-decoration:none; }
    .pe-crumb a:hover { color:var(--ink); }

    /* ── Buttons ──────────────────────────────────────────────── */
    .btn { display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.15rem;border-radius:11px;font-size:.79rem;font-weight:800;cursor:pointer;border:none;transition:all .15s;text-decoration:none;white-space:nowrap; }
    .btn--primary { background:var(--brand);color:#fff;box-shadow:0 4px 14px rgba(48,59,151,.3); }
    .btn--primary:hover { background:#232d8a;transform:translateY(-1px); }
    .btn--ghost   { background:#fff;border:1.5px solid var(--border);color:var(--ink-2); }
    .btn--ghost:hover { background:var(--surface);color:var(--ink); }

    /* ── Tab nav ──────────────────────────────────────────────── */
    .tab-nav { display:flex;gap:.2rem;background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:.25rem;overflow-x:auto;scrollbar-width:none;margin-bottom:1.5rem; }
    .tab-nav::-webkit-scrollbar { display:none; }
    .tab-btn { border:none;background:transparent;padding:.6rem 1.1rem;font-size:.78rem;font-weight:800;color:var(--ink-3);border-radius:10px;cursor:pointer;transition:.14s;white-space:nowrap; }
    .tab-btn.active { background:#fff;color:var(--brand);box-shadow:0 1px 4px rgba(15,23,42,.1); }
    .tab-panel { display:none; }
    .tab-panel.active { display:block; }

    /* ── Cards ────────────────────────────────────────────────── */
    .card { background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1.25rem; }
    .card-hd { display:flex;align-items:center;gap:.75rem;padding:1rem 1.3rem;border-bottom:1px solid var(--border);background:var(--surface); }
    .card-icon { width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0; }
    .card-ttl { font-size:.9rem;font-weight:800;color:var(--ink); }
    .card-sub  { font-size:.72rem;font-weight:500;color:var(--ink-3); }
    .card-body { padding:1.3rem; }

    /* ── Form inputs ──────────────────────────────────────────── */
    .lbl { display:block;font-size:.67rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-2);margin-bottom:.4rem; }
    .lbl .req { color:var(--red);margin-left:2px; }
    .inp, .sel, .ta {
        width:100%;padding:.65rem 1rem;
        background:var(--surface);border:1.5px solid var(--border);
        border-radius:var(--radius);font-size:.84rem;font-weight:600;color:var(--ink);
        outline:none;appearance:none;transition:border-color .14s,box-shadow .14s;
    }
    .inp:focus,.sel:focus,.ta:focus { border-color:var(--brand);box-shadow:0 0 0 3px rgba(48,59,151,.1);background:#fff; }
    .inp--mono { font-family:monospace;font-size:.8rem; }
    .ta { resize:vertical;min-height:100px;line-height:1.65; }
    .inp-icon { position:relative; }
    .inp-icon .ic { position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--ink-3);pointer-events:none;font-size:.8rem; }
    .inp-icon .inp { padding-left:2.35rem; }

    /* ── Grid helpers ─────────────────────────────────────────── */
    .g2 { display:grid;grid-template-columns:1fr 1fr;gap:1rem; }
    .g3 { display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem; }
    .g4 { display:grid;grid-template-columns:repeat(4,1fr);gap:.85rem; }
    @media(max-width:640px){ .g2,.g3,.g4 { grid-template-columns:1fr; } }

    /* ── Room counter ─────────────────────────────────────────── */
    .room-box { text-align:center;padding:1rem .75rem;background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius);transition:.14s; }
    .room-box:focus-within { border-color:var(--brand);background:#fff; }
    .room-box .room-ico { font-size:.9rem;display:block;margin-bottom:.4rem; }
    .room-box input { width:100%;background:transparent;border:none;outline:none;font-size:1.75rem;font-weight:900;color:var(--ink);text-align:center; }
    .room-lbl { font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-3); }

    /* ── Toggle ───────────────────────────────────────────────── */
    .tgl-row { display:flex;align-items:center;justify-content:space-between;padding:.7rem .9rem;border-radius:var(--radius);border:1.5px solid var(--border);cursor:pointer;transition:.13s; }
    .tgl-row:hover { background:var(--surface); }
    .tgl-lbl { font-size:.82rem;font-weight:700;color:var(--ink); }
    .tgl-sub  { font-size:.68rem;font-weight:500;color:var(--ink-3); }
    .tgl-slider { position:relative;width:40px;height:22px;background:var(--border);border-radius:99px;flex-shrink:0;transition:.16s; }
    .tgl-slider::after { content:'';position:absolute;width:16px;height:16px;background:#fff;border-radius:50%;top:3px;left:3px;transition:.16s;box-shadow:0 1px 3px rgba(0,0,0,.2); }
    input:checked + .tgl-slider { background:var(--green); }
    input:checked + .tgl-slider::after { left:21px; }

    /* ── Image grid ───────────────────────────────────────────── */
    .img-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:.65rem; }
    .img-thumb { position:relative;aspect-ratio:1;border-radius:12px;overflow:hidden;border:1.5px solid var(--border);background:var(--surface); }
    .img-thumb img { width:100%;height:100%;object-fit:cover; }
    .img-thumb-overlay { position:absolute;inset:0;background:rgba(15,23,42,.5);display:flex;align-items:center;justify-content:center;opacity:0;transition:.15s;backdrop-filter:blur(4px); }
    .img-thumb:hover .img-thumb-overlay { opacity:1; }

    /* ── Sticky save bar ──────────────────────────────────────── */
    .save-bar { position:sticky;bottom:1.5rem;z-index:50;display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.85rem 1.25rem;background:var(--ink);border-radius:var(--radius-lg);box-shadow:0 8px 30px rgba(15,23,42,.3);margin-top:1.5rem;flex-wrap:wrap; }
    .save-bar-text { font-size:.78rem;font-weight:600;color:rgba(255,255,255,.5); }
    .save-bar-text strong { color:#fff;font-weight:800; }

    /* ── Highlight badge ──────────────────────────────────────── */
    .badge { display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .6rem;border-radius:99px;font-size:.63rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em; }
    .badge--blue { background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe; }
    .badge--green { background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0; }
    .badge--purple { background:#f5f3ff;color:#5b21b6;border:1px solid #ddd6fe; }

    /* ── Error box ────────────────────────────────────────────── */
    .err-box { background:#fef2f2;border:1px solid #fecaca;border-radius:var(--radius);padding:.9rem 1.1rem;margin-bottom:1.25rem; }
    .err-box li { font-size:.8rem;color:#991b1b;font-weight:600; }

    /* ── Section divider ──────────────────────────────────────── */
    .divider { border:none;border-top:1px solid var(--border);margin:1.1rem 0; }
</style>
@endpush

@section('content')
<div class="pe-wrap">

    @if($errors->any())
    <div class="err-box">
        <p style="font-size:.8rem;font-weight:800;color:#b91c1c;margin:0 0 .35rem"><i class="fas fa-exclamation-circle mr-1"></i> Fix the following:</p>
        <ul style="padding-left:1.2rem;margin:0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- ── Topbar ──────────────────────────────────────────────── --}}
    <div class="pe-topbar">
        <div>
            <div class="pe-crumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a><span>/</span>
                <a href="{{ route('admin.properties.index') }}">Properties</a><span>/</span>
                Edit
            </div>
            <h1 style="font-size:1.4rem;font-weight:900;color:var(--ink);letter-spacing:-.025em;margin-bottom:.3rem">
                {{ $nameEn ?: 'Edit Property' }}
            </h1>
            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                <span style="font-size:.7rem;font-weight:700;color:var(--ink-3)">ID:</span>
                <span style="font-family:monospace;font-size:.72rem;font-weight:700;color:var(--ink)">{{ $property->id }}</span>
                @if($ownerLabel)
                    <span style="color:var(--border)">·</span>
                    <span style="font-size:.72rem;font-weight:700;color:var(--ink-3)">Owner: <strong style="color:var(--ink)">{{ $ownerLabel }}</strong></span>
                @endif
            </div>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
            <a href="{{ route('admin.properties.show', $property->id) }}" class="btn btn--ghost">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ route('admin.properties.index') }}" class="btn btn--ghost">Cancel</a>
            <button type="submit" form="editPropertyForm" class="btn btn--primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    {{-- ── Owner summary strip ─────────────────────────────────── --}}
    @if($property->owner)
    @php $o = $property->owner; $ownerType = class_basename($property->owner_type ?? ''); @endphp
    <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);padding:1rem 1.3rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
        <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--brand),#818cf8);display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:900;color:#fff;flex-shrink:0">
            {{ strtoupper(substr($ownerLabel, 0, 1)) }}
        </div>
        <div>
            <div style="font-weight:800;color:var(--ink);font-size:.9rem">{{ $ownerLabel }}</div>
            <span class="badge badge--{{ $ownerType === 'Agent' ? 'green' : ($ownerType === 'RealEstateOffice' ? 'purple' : 'blue') }}">{{ $ownerType }}</span>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;flex-wrap:wrap">
            <span style="font-size:.78rem;font-weight:600;color:var(--ink-3)">
                <i class="fas fa-envelope mr-1"></i>{{ $o->primary_email ?? $o->email_address ?? $o->email ?? '—' }}
            </span>
            <span style="font-size:.78rem;font-weight:600;color:var(--ink-3)">
                <i class="fas fa-phone mr-1"></i>{{ $o->primary_phone ?? $o->phone_number ?? $o->phone ?? '—' }}
            </span>
        </div>
    </div>
    @endif

    {{-- ── Tab navigation ──────────────────────────────────────── --}}
    <div class="tab-nav" id="editTabs">
        <button class="tab-btn active" data-tab="titles"><i class="fas fa-heading mr-1.5"></i> Names</button>
        <button class="tab-btn" data-tab="pricing"><i class="fas fa-tag mr-1.5"></i> Pricing</button>
        <button class="tab-btn" data-tab="details"><i class="fas fa-layer-group mr-1.5"></i> Details</button>
        <button class="tab-btn" data-tab="location"><i class="fas fa-map-pin mr-1.5"></i> Location</button>
        <button class="tab-btn" data-tab="construction"><i class="fas fa-hard-hat mr-1.5"></i> Build</button>
        <button class="tab-btn" data-tab="media"><i class="fas fa-images mr-1.5"></i> Media</button>
        <button class="tab-btn" data-tab="status"><i class="fas fa-toggle-on mr-1.5"></i> Status</button>
        <button class="tab-btn" data-tab="seo"><i class="fas fa-search mr-1.5"></i> SEO</button>
        <button class="tab-btn" data-tab="analytics"><i class="fas fa-chart-pie mr-1.5"></i> Analytics</button>
    </div>

    <form id="editPropertyForm" method="POST" action="{{ route('admin.properties.update', $property->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ════ NAMES ═════════════════════════════════════════════ --}}
        <div id="panel-titles" class="tab-panel active">
            <div class="card">
                <div class="card-hd">
                    <div class="card-icon" style="background:#eff6ff;color:#3b82f6"><i class="fas fa-heading"></i></div>
                    <div><div class="card-ttl">Property Names</div><div class="card-sub">Multi-language titles</div></div>
                </div>
                <div class="card-body">
                    <div class="lbl">English Title <span class="req">*</span></div>
                    <input type="text" name="name[en]" value="{{ old('name.en', $nameEn) }}" required class="inp" style="margin-bottom:1rem;font-size:1rem;font-weight:700" placeholder="Property title in English">
                    <div class="g2">
                        <div>
                            <label class="lbl">Arabic Title</label>
                            <input type="text" name="name[ar]" value="{{ old('name.ar', $nameAr) }}" class="inp" dir="rtl" style="text-align:right" placeholder="اسم العقار بالعربية">
                        </div>
                        <div>
                            <label class="lbl">Kurdish Title</label>
                            <input type="text" name="name[ku]" value="{{ old('name.ku', $nameKu) }}" class="inp" dir="rtl" style="text-align:right" placeholder="ناوی موڵک بە کوردی">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-hd">
                    <div class="card-icon" style="background:#f5f3ff;color:#8b5cf6"><i class="fas fa-align-left"></i></div>
                    <div><div class="card-ttl">Descriptions</div><div class="card-sub">Detailed text in multiple languages</div></div>
                </div>
                <div class="card-body">
                    <label class="lbl">English Description</label>
                    <textarea name="description[en]" class="ta" style="margin-bottom:1rem" placeholder="Describe the property in detail…">{{ old('description.en', $descEn) }}</textarea>
                    <div class="g2">
                        <div>
                            <label class="lbl">Arabic Description</label>
                            <textarea name="description[ar]" class="ta" dir="rtl" style="text-align:right" placeholder="وصف العقار…">{{ old('description.ar', $descAr) }}</textarea>
                        </div>
                        <div>
                            <label class="lbl">Kurdish Description</label>
                            <textarea name="description[ku]" class="ta" dir="rtl" style="text-align:right" placeholder="وردەکاری موڵکەکە…">{{ old('description.ku', $descKu) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════ PRICING ════════════════════════════════════════════ --}}
        <div id="panel-pricing" class="tab-panel">
            <div class="card">
                <div class="card-hd">
                    <div class="card-icon" style="background:#ecfdf5;color:#10b981"><i class="fas fa-dollar-sign"></i></div>
                    <div><div class="card-ttl">Pricing</div><div class="card-sub">USD and IQD values</div></div>
                </div>
                <div class="card-body">
                    <div class="g2" style="margin-bottom:1rem">
                        <div>
                            <label class="lbl">Price (USD) <span class="req">*</span></label>
                            <div class="inp-icon">
                                <span class="ic" style="font-weight:900;color:#10b981">$</span>
                                <input type="number" name="price_usd" value="{{ old('price_usd', $priceUSD) }}" step="0.01" required class="inp" placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label class="lbl">Price (IQD) <span class="req">*</span></label>
                            <div class="inp-icon">
                                <input type="number" name="price" value="{{ old('price', $priceIQD) }}" step="0.01" required class="inp" placeholder="0" style="padding-right:2.8rem">
                                <span style="position:absolute;right:.9rem;top:50%;transform:translateY(-50%);font-size:.68rem;font-weight:800;color:var(--ink-3)">IQD</span>
                            </div>
                        </div>
                    </div>
                    @if($priceUSD > 0 && (float)($property->area ?? 0) > 0)
                    <div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:var(--radius);padding:.75rem 1rem;font-size:.8rem;font-weight:700;color:#065f46">
                        <i class="fas fa-calculator mr-1"></i> ${{ number_format($priceUSD / (float)$property->area, 0) }}/m² based on {{ number_format((float)$property->area) }}m²
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-hd">
                    <div class="card-icon" style="background:#eef2ff;color:var(--brand)"><i class="fas fa-tags"></i></div>
                    <div><div class="card-ttl">Classification</div><div class="card-sub">Type, listing, and area</div></div>
                </div>
                <div class="card-body">
                    <div class="g3" style="margin-bottom:1rem">
                        <div>
                            <label class="lbl">Category</label>
                            <select name="type[category]" class="sel">
                                @foreach(['apartment','house','villa','office','land','commercial','industrial','warehouse'] as $opt)
                                    <option value="{{ $opt }}" {{ $typeCategory == $opt ? 'selected':'' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="lbl">Listing Type</label>
                            <select name="listing_type" class="sel">
                                <option value="sale"  {{ in_array($property->listing_type,['sale','sell']) ? 'selected':'' }}>For Sale</option>
                                <option value="rent"  {{ $property->listing_type === 'rent'  ? 'selected':'' }}>For Rent</option>
                            </select>
                        </div>
                        <div>
                            <label class="lbl">Rental Period</label>
                            <select name="rental_period" class="sel">
                                <option value="">N/A</option>
                                @foreach(['daily','weekly','monthly','yearly'] as $opt)
                                    <option value="{{ $opt }}" {{ $property->rental_period == $opt ? 'selected':'' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="lbl">Total Area (m²) <span class="req">*</span></label>
                        <input type="number" step="0.01" name="area" value="{{ old('area', (float)$property->area) }}" required class="inp" placeholder="0.00">
                    </div>
                </div>
            </div>
        </div>

        {{-- ════ DETAILS ════════════════════════════════════════════ --}}
        <div id="panel-details" class="tab-panel">
            <div class="card">
                <div class="card-hd">
                    <div class="card-icon" style="background:#f5f3ff;color:#8b5cf6"><i class="fas fa-bed"></i></div>
                    <div><div class="card-ttl">Rooms & Floor</div></div>
                </div>
                <div class="card-body">
                    <div class="g4">
                        @foreach([['Bedrooms','rooms[bedroom][count]',$roomBed,'fa-bed','#3b82f6'],['Bathrooms','rooms[bathroom][count]',$roomBath,'fa-bath','#8b5cf6'],['Living Rooms','rooms[living_room][count]',$roomLive,'fa-couch','#10b981'],['Floor No.','floor_number',$property->floor_number,'fa-layer-group','#f59e0b']] as [$lbl,$name,$val,$ico,$clr])
                        <div class="room-box">
                            <i class="fas {{ $ico }} room-ico" style="color:{{ $clr }}"></i>
                            <input type="number" name="{{ $name }}" value="{{ old($name, (int)$val) }}" min="0">
                            <div class="room-lbl">{{ $lbl }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
                <div class="card">
                    <div class="card-hd">
                        <div class="card-icon" style="background:#fefce8;color:#ca8a04"><i class="fas fa-star"></i></div>
                        <div><div class="card-ttl">Features & Amenities</div></div>
                    </div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                        <div><label class="lbl">Features <span style="font-weight:500;text-transform:none;letter-spacing:0">(comma separated)</span></label>
                            <input type="text" name="features" value="{{ old('features', $featStr) }}" class="inp" placeholder="Balcony, View, Corner unit…"></div>
                        <div><label class="lbl">Amenities</label>
                            <input type="text" name="amenities" value="{{ old('amenities', $amenStr) }}" class="inp" placeholder="Pool, Gym, WiFi…"></div>
                        <div><label class="lbl">Nearby Amenities</label>
                            <input type="text" name="nearby_amenities" value="{{ old('nearby_amenities', $nearStr) }}" class="inp" placeholder="School, Mall, Park…"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-hd">
                        <div class="card-icon" style="background:#ecfdf5;color:#10b981"><i class="fas fa-couch"></i></div>
                        <div><div class="card-ttl">Furnishing & Utilities</div></div>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom:.85rem">
                            <label class="lbl">Furnishing Level</label>
                            <select name="furnishing_details[level]" class="sel">
                                <option value="">Select</option>
                                @foreach(['unfurnished','semi-furnished','fully-furnished','luxury-furnished'] as $opt)
                                    <option value="{{ $opt }}" {{ $furnLevel == $opt ? 'selected':'' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="margin-bottom:1rem">
                            <label class="lbl">Furnished Items</label>
                            <input type="text" name="furnishing_details[items]" value="{{ old('furnishing_details.items', $furnItems) }}" class="inp" placeholder="Sofa, Bed, TV…">
                        </div>
                        <hr class="divider">
                        @foreach(['furnished'=>['Furnished','fa-couch'],'electricity'=>['Electricity','fa-bolt'],'water'=>['Water','fa-droplet'],'internet'=>['Internet','fa-wifi']] as $key=>[$lbl,$ico])
                        <label class="tgl-row" style="margin-bottom:.5rem;cursor:pointer">
                            <div>
                                <div class="tgl-lbl"><i class="fas {{ $ico }} mr-1.5" style="color:var(--brand);font-size:.8rem"></i>{{ $lbl }}</div>
                            </div>
                            <input type="checkbox" name="{{ $key }}" value="1" {{ $property->$key ? 'checked':'' }} style="display:none">
                            <div class="tgl-slider"></div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ════ LOCATION ═══════════════════════════════════════════ --}}
        <div id="panel-location" class="tab-panel">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
                <div class="card">
                    <div class="card-hd">
                        <div class="card-icon" style="background:#fef2f2;color:#ef4444"><i class="fas fa-map-pin"></i></div>
                        <div><div class="card-ttl">Address Details</div></div>
                    </div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                        <div class="g2">
                            <div><label class="lbl">City</label><input type="text" name="address_details[city][en]" value="{{ old('address_details.city.en', $cityVal) }}" class="inp" placeholder="Erbil"></div>
                            <div><label class="lbl">District</label><input type="text" name="address_details[district][en]" value="{{ old('address_details.district.en', $distVal) }}" class="inp" placeholder="Azadi"></div>
                        </div>
                        <div><label class="lbl">Full Address</label><textarea name="address" class="ta" style="min-height:75px" placeholder="Street, Building…">{{ old('address', $property->address ?? '') }}</textarea></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-hd">
                        <div class="card-icon" style="background:#eff6ff;color:#3b82f6"><i class="fas fa-globe"></i></div>
                        <div><div class="card-ttl">GPS Coordinates</div></div>
                    </div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                        <div><label class="lbl">Latitude</label><input type="number" step="any" name="locations[0][lat]" value="{{ old('locations.0.lat', $lat ?: '') }}" class="inp inp--mono" placeholder="36.1900"></div>
                        <div><label class="lbl">Longitude</label><input type="number" step="any" name="locations[0][lng]" value="{{ old('locations.0.lng', $lng ?: '') }}" class="inp inp--mono" placeholder="44.0090"></div>
                        @if($lat && $lng)
                        <a href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" target="_blank" class="btn btn--ghost" style="justify-content:center;font-size:.75rem">
                            <i class="fas fa-external-link-alt"></i> Verify on Google Maps
                        </a>
                        <div style="border-radius:var(--radius);overflow:hidden;border:1px solid var(--border)">
                            <iframe width="100%" height="160" frameborder="0" style="display:block;border:0"
                                src="https://maps.google.com/maps?q={{ $lat }},{{ $lng }}&hl=en&z=15&output=embed"></iframe>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ════ CONSTRUCTION ═══════════════════════════════════════ --}}
        <div id="panel-construction" class="tab-panel">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
                <div class="card">
                    <div class="card-hd">
                        <div class="card-icon" style="background:#fffbeb;color:#d97706"><i class="fas fa-hard-hat"></i></div>
                        <div><div class="card-ttl">Build Information</div></div>
                    </div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                        <div><label class="lbl">Year Built</label>
                            <input type="number" name="year_built" value="{{ old('year_built', $property->year_built) }}" class="inp" placeholder="2020"></div>
                        <div><label class="lbl">Build Type</label>
                            <select name="construction_details[type]" class="sel">
                                <option value="">Select</option>
                                @foreach(['concrete','brick','steel','wood','mixed'] as $opt)
                                    <option value="{{ $opt }}" {{ $buildType == $opt ? 'selected':'' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="lbl">Build Quality</label>
                            <select name="construction_details[quality]" class="sel">
                                <option value="">Select</option>
                                @foreach(['standard','premium','luxury','ultra-luxury'] as $opt)
                                    <option value="{{ $opt }}" {{ $buildQuality == $opt ? 'selected':'' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-hd">
                        <div class="card-icon" style="background:#ecfdf5;color:#10b981"><i class="fas fa-leaf"></i></div>
                        <div><div class="card-ttl">Energy Efficiency</div></div>
                    </div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                        <div><label class="lbl">Energy Rating</label>
                            <select name="energy_rating" class="sel">
                                <option value="">Not Rated</option>
                                @foreach(['A++','A+','A','B','C','D','E','F','G'] as $opt)
                                    <option value="{{ $opt }}" {{ $property->energy_rating == $opt ? 'selected':'' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="g2">
                            <div><label class="lbl">Certificate No.</label>
                                <input type="text" name="energy_details[certificate]" value="{{ old('energy_details.certificate', $energyCert) }}" class="inp"></div>
                            <div><label class="lbl">kWh Consumption</label>
                                <input type="number" name="energy_details[consumption]" value="{{ old('energy_details.consumption', $energyKwh) }}" class="inp"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════ MEDIA ══════════════════════════════════════════════ --}}
        <div id="panel-media" class="tab-panel">
            <div class="card">
                <div class="card-hd">
                    <div class="card-icon" style="background:#f5f3ff;color:#8b5cf6"><i class="fas fa-images"></i></div>
                    <div><div class="card-ttl">Gallery</div><div class="card-sub">{{ count($images) }} images uploaded</div></div>
                </div>
                <div class="card-body">
                    @if(count($images))
                    <div class="img-grid" style="margin-bottom:1.25rem">
                        @foreach($images as $img)
                        <div class="img-thumb">
                            <img src="{{ asset($img) }}" alt="">
                            <div class="img-thumb-overlay">
                                <button type="button" style="background:rgba(239,68,68,.8);color:#fff;border:none;border-radius:9px;padding:.4rem .6rem;cursor:pointer;font-size:.75rem">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div style="text-align:center;padding:1.5rem;background:var(--surface);border-radius:var(--radius);border:2px dashed var(--border);margin-bottom:1.25rem">
                        <i class="fas fa-images" style="font-size:2rem;color:var(--ink-3);display:block;margin-bottom:.5rem;opacity:.4"></i>
                        <p style="font-size:.82rem;font-weight:600;color:var(--ink-3)">No images yet</p>
                    </div>
                    @endif

                    <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:var(--radius);padding:1rem 1.15rem;text-align:center">
                        <label class="lbl" style="margin-bottom:.6rem">Upload New Photos</label>
                        <input type="file" name="images[]" multiple accept="image/*"
                               class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition cursor-pointer">
                        <p style="font-size:.68rem;font-weight:500;color:var(--ink-3);margin-top:.5rem">JPG, PNG, WebP · Max 5MB each</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-hd">
                    <div class="card-icon" style="background:#eff6ff;color:#3b82f6"><i class="fas fa-link"></i></div>
                    <div><div class="card-ttl">Media Links</div></div>
                </div>
                <div class="card-body">
                    <div class="g2">
                        <div><label class="lbl">360° Virtual Tour URL</label>
                            <div class="inp-icon"><span class="ic"><i class="fas fa-cube"></i></span>
                                <input type="url" name="virtual_tour_url" value="{{ old('virtual_tour_url', $property->virtual_tour_url ?? '') }}" class="inp" placeholder="https://…">
                            </div>
                        </div>
                        <div><label class="lbl">Floor Plan URL</label>
                            <div class="inp-icon"><span class="ic"><i class="fas fa-ruler-combined"></i></span>
                                <input type="url" name="floor_plan_url" value="{{ old('floor_plan_url', $property->floor_plan_url ?? '') }}" class="inp" placeholder="https://…">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════ STATUS ══════════════════════════════════════════════ --}}
        <div id="panel-status" class="tab-panel">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
                <div class="card">
                    <div class="card-hd">
                        <div class="card-icon" style="background:#ecfdf5;color:#10b981"><i class="fas fa-toggle-on"></i></div>
                        <div><div class="card-ttl">Availability & Status</div></div>
                    </div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                        <div><label class="lbl">Global Status</label>
                            <select name="status" class="sel">
                                @foreach(['available','pending','sold','rented','suspended'] as $st)
                                    <option value="{{ $st }}" {{ old('status',$property->status) == $st ? 'selected':'' }}>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="g2">
                            <div><label class="lbl">Available From</label><input type="date" name="availability[from]" value="{{ old('availability.from', $availFrom) }}" class="inp"></div>
                            <div><label class="lbl">Available To</label><input type="date" name="availability[to]" value="{{ old('availability.to', $availTo) }}" class="inp"></div>
                        </div>
                        <hr class="divider">
                        @foreach(['is_active'=>['Active Listing','Make this listing visible'],'published'=>['Publicly Visible','Show on the website and app'],'verified'=>['Verified Badge','Show verification checkmark']] as $key=>[$lbl,$sub])
                        <label class="tgl-row" style="cursor:pointer">
                            <div><div class="tgl-lbl">{{ $lbl }}</div><div class="tgl-sub">{{ $sub }}</div></div>
                            <input type="checkbox" name="{{ $key }}" value="1" {{ old($key,$property->$key) ? 'checked':'' }} style="display:none">
                            <div class="tgl-slider"></div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div style="background:linear-gradient(145deg,#1e1b4b,#1a1d36);border-radius:var(--radius-lg);padding:1.3rem;color:#fff;position:relative;overflow:hidden">
                    <div style="position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.05)"></div>
                    <div style="position:relative;z-index:1">
                        <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:1.25rem">
                            <div style="width:36px;height:36px;border-radius:10px;background:rgba(245,158,11,.2);display:flex;align-items:center;justify-content:center;color:#fbbf24"><i class="fas fa-rocket"></i></div>
                            <div>
                                <div style="font-size:.9rem;font-weight:800">Boost Promotion</div>
                                <div style="font-size:.7rem;color:rgba(255,255,255,.4)">Feature this property on top</div>
                            </div>
                        </div>
                        <label style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;cursor:pointer">
                            <span style="font-size:.82rem;font-weight:700">Enable Boosting</span>
                            <input type="checkbox" name="is_boosted" value="1" {{ old('is_boosted',$property->is_boosted) ? 'checked':'' }} style="display:none" id="boostChk">
                            <div class="tgl-slider" onclick="document.getElementById('boostChk').click()"></div>
                        </label>
                        <div style="display:flex;flex-direction:column;gap:.7rem">
                            <div>
                                <label style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.4);display:block;margin-bottom:.3rem">Start Date</label>
                                <input type="date" name="boost_start_date" value="{{ old('boost_start_date', $property->boost_start_date ?? '') }}"
                                       style="width:100%;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:.55rem .9rem;font-size:.82rem;font-weight:700;color:#fff;outline:none">
                            </div>
                            <div>
                                <label style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.4);display:block;margin-bottom:.3rem">End Date</label>
                                <input type="date" name="boost_end_date" value="{{ old('boost_end_date', $property->boost_end_date ?? '') }}"
                                       style="width:100%;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:.55rem .9rem;font-size:.82rem;font-weight:700;color:#fff;outline:none">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════ SEO ════════════════════════════════════════════════ --}}
        <div id="panel-seo" class="tab-panel">
            <div class="card" style="max-width:780px">
                <div class="card-hd">
                    <div class="card-icon" style="background:#eef2ff;color:var(--brand)"><i class="fas fa-search"></i></div>
                    <div><div class="card-ttl">SEO & Metadata</div><div class="card-sub">Improve search engine visibility</div></div>
                </div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                    <div><label class="lbl">Meta Title <span style="font-weight:500;text-transform:none;letter-spacing:0">(max 60 chars)</span></label>
                        <input type="text" name="seo_metadata[title]" value="{{ old('seo_metadata.title', $seoTitle) }}" maxlength="60" class="inp" placeholder="Short, keyword-rich title"></div>
                    <div><label class="lbl">Meta Description <span style="font-weight:500;text-transform:none;letter-spacing:0">(max 160 chars)</span></label>
                        <textarea name="seo_metadata[description]" maxlength="160" class="ta" style="min-height:75px" placeholder="Brief description for search results…">{{ old('seo_metadata.description', $seoDesc) }}</textarea></div>
                    <div><label class="lbl">Keywords <span style="font-weight:500;text-transform:none;letter-spacing:0">(comma separated)</span></label>
                        <input type="text" name="seo_metadata[keywords]" value="{{ old('seo_metadata.keywords', $seoKeywords) }}" class="inp" placeholder="apartment, Erbil, for sale…"></div>
                </div>
            </div>
        </div>

        {{-- ════ ANALYTICS ══════════════════════════════════════════ --}}
        <div id="panel-analytics" class="tab-panel">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.25rem">
                @foreach([['Total Views',$property->views,'fa-eye','#3b82f6'],['Favorites',$property->favorites_count,'fa-heart','#ef4444'],['Rating',$property->rating,'fa-star','#f59e0b']] as [$lbl,$val,$ico,$clr])
                <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem;text-align:center">
                    <i class="fas {{ $ico }}" style="color:{{ $clr }};font-size:1.25rem;display:block;margin-bottom:.5rem"></i>
                    <p style="font-size:2rem;font-weight:900;color:var(--ink);line-height:1;margin-bottom:.25rem">{{ number_format((float)$val) }}</p>
                    <p style="font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-3)">{{ $lbl }}</p>
                </div>
                @endforeach
            </div>
            <div style="background:#0f172a;border-radius:var(--radius-lg);padding:1.25rem">
                <label class="lbl" style="color:#64748b;margin-bottom:.6rem"><i class="fas fa-code mr-1"></i> Raw JSON Data</label>
                <textarea readonly style="width:100%;height:200px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:12px;font-size:.72rem;font-family:monospace;color:#4ade80;padding:.85rem;resize:none;outline:none">{{ json_encode(['id'=>$property->id,'status'=>$property->status,'views'=>$property->views,'favorites'=>$property->favorites_count,'rating'=>$property->rating,'created_at'=>$property->created_at,'updated_at'=>$property->updated_at,'is_active'=>$property->is_active,'published'=>$property->published,'verified'=>$property->verified,'is_boosted'=>$property->is_boosted], JSON_PRETTY_PRINT) }}</textarea>
            </div>
        </div>

        {{-- Sticky save bar --}}
        <div class="save-bar">
            <div class="save-bar-text">Editing <strong>{{ $nameEn ?: $property->id }}</strong></div>
            <div style="display:flex;gap:.5rem">
                <a href="{{ route('admin.properties.show', $property->id) }}" class="btn btn--ghost"
                   style="color:rgba(255,255,255,.5);border-color:rgba(255,255,255,.15);background:transparent;font-size:.75rem">
                    Discard
                </a>
                <button type="submit" class="btn" style="background:linear-gradient(135deg,#303b97,#4b56b2);color:#fff;box-shadow:0 4px 14px rgba(48,59,151,.4)">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
// ── Tab switching ──────────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => { p.style.display='none'; p.classList.remove('active'); });
        btn.classList.add('active');
        const panel = document.getElementById('panel-' + btn.dataset.tab);
        if (panel) { panel.style.display='block'; panel.classList.add('active'); }
    });
});

// init: hide all non-active
document.querySelectorAll('.tab-panel:not(.active)').forEach(p => p.style.display='none');
</script>
@endpush
@endsection
