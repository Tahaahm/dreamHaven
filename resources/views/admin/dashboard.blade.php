@extends('layouts.admin-layout')

@section('title', 'Command Center')

@php
    /** Safe route helper — never crashes if a route name doesn't exist yet. */
    $link = function (string $name, $params = []) {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : null;
    };

    $admin      = optional(Auth::guard('admin')->user());
    $adminName  = $admin->username ?? $admin->name ?? 'Administrator';
    $hour       = now()->hour;
    $greeting   = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $totalTasks = array_sum($pendingApprovals);
@endphp

@push('styles')
<style>
    .num { font-variant-numeric: tabular-nums; letter-spacing: -0.02em; }

    .console-hero {
        background:
            radial-gradient(1200px 400px at 15% -40%, rgba(75,86,178,.55), transparent 60%),
            linear-gradient(135deg, #1a1d2e 0%, #262b4a 55%, #303b97 160%);
    }
    .grain::after {
        content: ""; position: absolute; inset: 0; pointer-events: none; opacity: .3;
        background-image: radial-gradient(rgba(255,255,255,.10) 1px, transparent 1px);
        background-size: 22px 22px;
    }

    .card { background:#fff; border:1px solid #e8eaf0; border-radius:18px; }
    .card-hd { padding:15px 17px; border-bottom:1px solid #f1f2f7; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .card-ttl { font-weight:800; color:#0f172a; font-size:14.5px; letter-spacing:-.01em; }
    .eyebrow { font-size:9.5px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; color:#94a3b8; }

    .kpi { position:relative; overflow:hidden; transition:.22s ease; }
    .kpi:active { transform:scale(.985); }
    .kpi-spark { position:absolute; left:0; right:0; bottom:-6px; height:56px; opacity:.8; pointer-events:none; }

    .chip { display:inline-flex; align-items:center; gap:4px; font-size:10.5px; font-weight:800; padding:3px 8px; border-radius:999px; }
    .chip-up   { background:#ecfdf5; color:#047857; }
    .chip-down { background:#fef2f2; color:#b91c1c; }
    .chip-flat { background:#f1f5f9; color:#475569; }

    .task-row { display:flex; align-items:center; gap:11px; padding:11px 13px; border-radius:13px; transition:.16s; min-height:46px; }
    .task-row:active { background:rgba(255,255,255,.1); }

    .queue-item { display:flex; gap:12px; padding:13px 16px; border-bottom:1px solid #f4f5f9; }
    .queue-thumb { width:62px; height:56px; border-radius:12px; object-fit:cover; background:#eef0f7; flex-shrink:0; }

    .radar-item, .lb-row { display:flex; align-items:center; gap:11px; padding:11px 16px; border-bottom:1px solid #f4f5f9; min-height:52px; }
    .lb-row { border-bottom:0; border-radius:13px; padding:9px 11px; }
    .lb-row:active { background:#f5f6fb; }
    .dot { width:8px; height:8px; border-radius:999px; flex-shrink:0; }
    .st-expired{background:#ef4444}  .tx-expired{color:#b91c1c;background:#fef2f2}
    .st-critical{background:#f97316} .tx-critical{color:#c2410c;background:#fff7ed}
    .st-soon{background:#eab308}     .tx-soon{color:#a16207;background:#fefce8}
    .st-ok{background:#22c55e}       .tx-ok{color:#15803d;background:#f0fdf4}

    .rank { width:20px; text-align:center; font-weight:900; font-size:12.5px; color:#cbd5e1; }
    .rank-1 { color:#303b97; }

    .tl { position:relative; padding-left:28px; }
    .tl::before { content:""; position:absolute; left:10px; top:4px; bottom:4px; width:2px; background:#eef0f7; }
    .tl-item { position:relative; padding:8px 0; }
    .tl-item i { position:absolute; left:-28px; top:8px; width:22px; height:22px; border-radius:999px; display:grid; place-items:center; font-size:9px; border:2px solid #fff; }

    .seg { display:inline-flex; background:#f1f2f7; padding:3px; border-radius:11px; }
    .seg button { border:0; background:transparent; font-size:11px; font-weight:800; color:#64748b; padding:6px 11px; border-radius:8px; cursor:pointer; transition:.16s; min-height:32px; }
    .seg button.on { background:#fff; color:#303b97; box-shadow:0 1px 3px rgba(15,23,42,.12); }

    .tabs { display:flex; gap:14px; overflow-x:auto; }
    .tabs::-webkit-scrollbar { display:none; }
    .tabs button { border:0; background:transparent; font-size:12px; font-weight:800; color:#94a3b8; padding:6px 0; cursor:pointer; border-bottom:2px solid transparent; white-space:nowrap; }
    .tabs button.on { color:#303b97; border-color:#303b97; }

    .ghost-btn { display:inline-flex; align-items:center; gap:6px; font-size:11.5px; font-weight:800; padding:8px 12px; border-radius:11px; border:1px solid #e4e6ef; color:#475569; background:#fff; transition:.16s; min-height:38px; }
    .ghost-btn:hover { border-color:#303b97; color:#303b97; background:#f5f6fd; }

    .solid-btn { display:inline-flex; align-items:center; gap:7px; font-size:12.5px; font-weight:800; padding:11px 16px; border-radius:12px; color:#fff; min-height:44px;
                 background:linear-gradient(135deg,#303b97,#4b56b2); box-shadow:0 10px 22px -12px rgba(48,59,151,.9); transition:.16s; }
    .solid-btn:active { transform:scale(.97); }

    .empty { padding:30px 18px; text-align:center; color:#94a3b8; font-size:12.5px; font-weight:600; }
    .empty i { display:block; font-size:24px; margin-bottom:9px; color:#dbe0ee; }

    /* ---- Retention ---- */
    .ret-tile { border:1px solid #eceef6; border-radius:16px; padding:15px; background:#fff; }
    .ret-tile b { display:block; font-size:25px; font-weight:900; color:#0f172a; line-height:1; margin-bottom:5px; }

    .gauge { position:relative; width:128px; height:128px; margin:0 auto; }
    .gauge svg { transform:rotate(-90deg); }
    .gauge-mid { position:absolute; inset:0; display:grid; place-items:center; text-align:center; }

    .cohort { width:100%; border-collapse:separate; border-spacing:4px; min-width:520px; }
    .cohort th { font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:#94a3b8; padding-bottom:3px; }
    .cohort td { text-align:center; font-size:11.5px; font-weight:800; border-radius:9px; padding:9px 4px; color:#475569; background:#f6f7fb; }
    .cohort td.head { text-align:left; background:transparent; font-weight:800; color:#0f172a; white-space:nowrap; padding-left:0; }
    .cohort td.void { background:transparent; }

    .hours { display:flex; gap:2.5px; align-items:flex-end; height:74px; }
    .hours span { flex:1; background:#e6e8f6; border-radius:3px 3px 0 0; min-height:3px; transition:.2s; }
    .hours span.peak { background:#303b97; }

    /* ---- Mobile jump chips ---- */
    .jump { display:flex; gap:8px; overflow-x:auto; padding:2px 0 4px; scrollbar-width:none; }
    .jump::-webkit-scrollbar { display:none; }
    .jump a { flex:0 0 auto; font-size:11.5px; font-weight:800; padding:8px 13px; border-radius:999px; background:#fff; border:1px solid #e6e8f2; color:#475569; }

    .scroll-row { display:flex; gap:10px; overflow-x:auto; scroll-snap-type:x mandatory; padding-bottom:4px; scrollbar-width:none; }
    .scroll-row::-webkit-scrollbar { display:none; }
    .scroll-row > * { scroll-snap-align:start; flex:0 0 auto; }

    /* ---- Small screens ---- */
    @media (max-width: 767px) {
        .card { border-radius:16px; }
        .card-hd { padding:13px 14px; }
        .queue-item { padding:12px 14px; }
        .radar-item { padding:11px 14px; }
        .tl { padding-left:26px; }
    }

    @media (prefers-reduced-motion: reduce) { * { animation:none !important; transition:none !important; } }
</style>
@endpush

@section('content')
<div class="max-w-[1640px] mx-auto">

    {{-- ══════════════════════════════════════════════════════════════
         1. HERO
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="console-hero grain relative overflow-hidden rounded-[22px] md:rounded-[26px] text-white p-5 md:p-8 mb-4 md:mb-6">
        <div class="relative z-10 flex flex-col xl:flex-row xl:items-end justify-between gap-6">

            <div class="min-w-0">
                <p class="eyebrow text-white/45 mb-1.5">{{ now()->format('l, d F Y') }} · Erbil</p>
                <h1 class="text-[25px] md:text-[36px] font-black tracking-tight leading-none mb-2.5">
                    {{ $greeting }}, {{ \Illuminate\Support\Str::before($adminName, ' ') }}
                </h1>

                @if($totalTasks > 0)
                    <p class="text-white/70 text-[13px] md:text-sm font-medium max-w-xl">
                        <span class="text-white font-black">{{ $totalTasks }}</span> item{{ $totalTasks === 1 ? '' : 's' }} need your decision@if($pulse['revenue_at_risk'] > 0), and
                        <span class="text-white font-black num">{{ number_format($pulse['revenue_at_risk']) }} IQD</span> of subscriptions expire within 7 days@endif.
                    </p>
                @else
                    <p class="text-white/70 text-[13px] md:text-sm font-medium">Queue is clear. Nothing is waiting on you.</p>
                @endif

                <div class="flex flex-wrap items-center gap-2 mt-5">
                    @if($link('admin.properties.create'))
                        <a href="{{ $link('admin.properties.create') }}" class="solid-btn"><i class="fas fa-plus"></i> Add property</a>
                    @endif
                    <button type="button" onclick="openPalette()" class="ghost-btn !bg-white/10 !border-white/15 !text-white">
                        <i class="fas fa-bolt"></i> Jump to…
                    </button>
                    @if($link('admin.projects.create'))
                        <a href="{{ $link('admin.projects.create') }}" class="ghost-btn !bg-white/10 !border-white/15 !text-white hidden sm:inline-flex"><i class="fas fa-city"></i> Add project</a>
                    @endif
                    <a href="{{ url('admin/notifications/broadcast') }}" class="ghost-btn !bg-white/10 !border-white/15 !text-white hidden sm:inline-flex"><i class="fas fa-bullhorn"></i> Broadcast</a>
                    <a href="{{ route('admin.dashboard') }}?refresh=1" class="ghost-btn !bg-transparent !border-white/15 !text-white/60" title="Recalculate cached figures">
                        <i class="fas fa-rotate"></i>
                    </a>
                </div>
            </div>

            {{-- Pulse strip: scrolls sideways on phones --}}
            @php
                $pulseCards = [
                    ['Coming back', number_format($retention['repeat_visitors']), 'in 30 days'],
                    ['New this week', number_format($pulse['listings_week']), 'listings'],
                    ['Agent trust', $pulse['verify_rate'] . '%', 'verified'],
                    ['Avg. asking', '$' . number_format($pulse['avg_price']), 'per listing'],
                ];
            @endphp
            <div class="scroll-row lg:grid lg:grid-cols-4 lg:gap-3 shrink-0 -mx-1 px-1 lg:mx-0 lg:px-0">
                @foreach($pulseCards as [$label, $value, $sub])
                    <div class="bg-white/[.07] border border-white/10 rounded-2xl px-4 py-3 backdrop-blur-sm min-w-[128px]">
                        <p class="eyebrow text-white/40 mb-1.5">{{ $label }}</p>
                        <p class="text-[21px] md:text-2xl font-black num leading-none">{{ $value }}</p>
                        <p class="text-[9.5px] text-white/45 font-bold mt-1.5 uppercase tracking-wide">{{ $sub }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Section jump chips (phones only) --}}
    <div class="jump lg:hidden mb-4">
        <a href="#sec-tasks">Tasks</a>
        <a href="#sec-growth">Growth</a>
        <a href="#sec-visitors">Visitors</a>
        <a href="#sec-retention">Returning</a>
        <a href="#sec-queue">Approvals</a>
        <a href="#sec-renewals">Renewals</a>
        <a href="#sec-inventory">Inventory</a>
        <a href="#sec-people">People</a>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         2. KPI ROW
    ═══════════════════════════════════════════════════════════════ --}}
    @php
        $kpis = [
            ['key' => 'revenue', 'label' => 'Subscription revenue', 'value' => number_format($stats['subscription_revenue_iqd']), 'unit' => 'IQD active', 'icon' => 'fa-wallet',
             'delta' => $deltas['revenue'], 'foot' => $stats['active_subscriptions'] . ' active plans', 'href' => $link('admin.subscriptions.index')],
            ['key' => 'users', 'label' => 'Registered users', 'value' => number_format($stats['total_users']), 'unit' => 'accounts', 'icon' => 'fa-users',
             'delta' => $deltas['users'], 'foot' => '+' . $stats['new_users_today'] . ' today · +' . $stats['new_users_week'] . ' this week', 'href' => $link('admin.users.index')],
            ['key' => 'properties', 'label' => 'Listings', 'value' => number_format($stats['total_properties']), 'unit' => 'total', 'icon' => 'fa-house',
             'delta' => $deltas['properties'], 'foot' => $stats['properties_for_sale'] . ' sale · ' . $stats['properties_for_rent'] . ' rent', 'href' => $link('admin.properties.index')],
            ['key' => 'network', 'label' => 'Partner network', 'value' => number_format($stats['total_agents'] + $stats['total_offices']), 'unit' => 'agents & offices', 'icon' => 'fa-handshake',
             'delta' => null, 'foot' => $stats['total_agents'] . ' agents · ' . $stats['total_offices'] . ' offices', 'href' => $link('admin.agents.index')],
        ];
    @endphp

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 md:gap-5 mb-4 md:mb-6">
        @foreach($kpis as $k)
            <a href="{{ $k['href'] ?? '#' }}" class="kpi card p-4 md:p-6 block">
                <div class="flex items-start justify-between mb-3.5 md:mb-5">
                    <div class="w-9 h-9 md:w-11 md:h-11 rounded-xl md:rounded-2xl grid place-items-center text-[13px]" style="background:#f2f3fb;color:#303b97;">
                        <i class="fas {{ $k['icon'] }}"></i>
                    </div>
                    @if($k['delta'] !== null)
                        <span class="chip {{ $k['delta'] > 0 ? 'chip-up' : ($k['delta'] < 0 ? 'chip-down' : 'chip-flat') }}">
                            <i class="fas {{ $k['delta'] > 0 ? 'fa-arrow-trend-up' : ($k['delta'] < 0 ? 'fa-arrow-trend-down' : 'fa-minus') }} text-[8px]"></i>
                            {{ $k['delta'] > 0 ? '+' : '' }}{{ $k['delta'] }}%
                        </span>
                    @endif
                </div>

                <p class="eyebrow mb-1.5 truncate">{{ $k['label'] }}</p>
                <h3 class="text-[23px] md:text-[32px] font-black text-slate-900 num leading-none mb-1">{{ $k['value'] }}</h3>
                <p class="text-[10px] md:text-[11px] font-bold text-slate-400 uppercase tracking-wide">{{ $k['unit'] }}</p>

                <div class="relative mt-3.5 md:mt-5 pt-3 md:pt-4 border-t border-slate-100">
                    <p class="text-[10.5px] md:text-[11.5px] text-slate-500 font-semibold relative z-10 leading-snug">{{ $k['foot'] }}</p>
                </div>
                <div class="kpi-spark" id="spark-{{ $k['key'] }}"></div>
            </a>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         3. ACTION CENTER + GROWTH
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="sec-tasks" class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6 mb-4 md:mb-6 scroll-mt-24">

        @php
            $tasks = [
                ['Properties',   $pendingApprovals['properties'],   'fa-house-circle-exclamation', $link('admin.properties.index', ['status' => 'pending'])],
                ['Agents',       $pendingApprovals['agents'],       'fa-user-tie',                 $link('admin.agents.index', ['status' => 'pending'])],
                ['Offices',      $pendingApprovals['offices'],      'fa-building',                 $link('admin.offices.index', ['status' => 'pending'])],
                ['Banners',      $pendingApprovals['banners'],      'fa-rectangle-ad',             $link('admin.banners.index', ['status' => 'pending'])],
                ['Providers',    $pendingApprovals['providers'],    'fa-tools',                    $link('admin.service-providers.index')],
                ['Appointments', $pendingApprovals['appointments'], 'fa-calendar-check',           $link('admin.appointments.index', ['status' => 'pending'])],
                ['Reports',      $pendingApprovals['reports'],      'fa-flag',                     $link('admin.reports.index')],
            ];
        @endphp
        <div class="rounded-[18px] p-5 md:p-6 text-white relative overflow-hidden xl:order-2" style="background:linear-gradient(165deg,#1a1d2e,#12141f);">
            <div class="absolute -top-16 -right-14 w-52 h-52 rounded-full blur-3xl opacity-40" style="background:#303b97"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-1">
                    <p class="eyebrow text-white/40">Waiting on you</p>
                    @if($totalTasks > 0)
                        <span class="flex h-2.5 w-2.5 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                        </span>
                    @endif
                </div>
                <h3 class="text-xl md:text-2xl font-black mb-4">Action center</h3>

                <div class="space-y-0.5 -mx-2">
                    @foreach($tasks as [$label, $count, $icon, $url])
                        <a href="{{ $url ?? '#' }}" class="task-row {{ $count > 0 ? '' : 'opacity-40' }}">
                            <span class="w-8 h-8 rounded-xl grid place-items-center text-[12px] shrink-0"
                                  style="background:{{ $count > 0 ? 'rgba(48,59,151,.55)' : 'rgba(255,255,255,.05)' }}">
                                <i class="fas {{ $icon }}"></i>
                            </span>
                            <span class="flex-1 text-[13px] font-bold text-white/85">{{ $label }}</span>
                            <span class="num font-black text-[15px] {{ $count > 0 ? 'text-white' : 'text-white/30' }}">{{ $count }}</span>
                            <i class="fas fa-chevron-right text-[10px] text-white/25"></i>
                        </a>
                    @endforeach
                </div>

                @if($totalTasks === 0)
                    <div class="mt-4 rounded-2xl bg-emerald-500/10 border border-emerald-400/20 px-4 py-3 text-[12.5px] font-bold text-emerald-300">
                        <i class="fas fa-check-circle mr-1.5"></i> Everything is reviewed.
                    </div>
                @endif
            </div>
        </div>

        <div id="sec-growth" class="xl:col-span-2 xl:order-1 card overflow-hidden scroll-mt-24">
            <div class="card-hd">
                <div>
                    <p class="eyebrow mb-1">Trend</p>
                    <h3 class="card-ttl">Platform growth</h3>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="seg" id="metricSeg">
                        <button class="on" data-metric="users">Users</button>
                        <button data-metric="properties">Listings</button>
                        <button data-metric="revenue">Revenue</button>
                    </div>
                    <div class="seg" id="rangeSeg">
                        <button data-range="6">6M</button>
                        <button class="on" data-range="12">12M</button>
                    </div>
                </div>
            </div>
            <div class="p-2 md:p-4"><div id="growthChart"></div></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         4. VISITORS — everyone, including people who never sign in
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="sec-visitors" class="card mb-4 md:mb-6 overflow-hidden scroll-mt-24">
        <div class="card-hd">
            <div>
                <p class="eyebrow mb-1">Traffic</p>
                <h3 class="card-ttl">Visitors and guests</h3>
            </div>
            @if(($visitors['available'] ?? false) && $visitors['live'] > 0)
                <span class="inline-flex items-center gap-1.5 text-[10.5px] font-black px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    {{ $visitors['live'] }} online now
                </span>
            @endif
        </div>

        @if(!($visitors['available'] ?? false))
            <div class="empty">
                <i class="fas fa-chart-line"></i>
                Visitor tracking isn't switched on yet.<br>
                <span class="text-[11.5px]">Run the visits migration and register the TrackVisitor middleware.</span>
            </div>
        @else
            <div class="p-4 md:p-6">

                {{-- ── Headline row ─────────────────────────────────── --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 md:gap-7 mb-6">

                    <div>
                        <div class="flex items-end gap-3 mb-1">
                            <p class="text-[42px] md:text-[54px] font-black text-slate-900 num leading-none">
                                {{ number_format($visitors['today']) }}
                            </p>
                            <span class="chip mb-2 {{ $visitors['change'] > 0 ? 'chip-up' : ($visitors['change'] < 0 ? 'chip-down' : 'chip-flat') }}">
                                <i class="fas {{ $visitors['change'] > 0 ? 'fa-arrow-trend-up' : ($visitors['change'] < 0 ? 'fa-arrow-trend-down' : 'fa-minus') }} text-[9px]"></i>
                                {{ $visitors['change'] > 0 ? '+' : '' }}{{ $visitors['change'] }}%
                            </span>
                        </div>
                        <p class="eyebrow mb-4">people here today</p>

                        {{-- New vs returning today — the headline for guest loyalty --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl p-4 text-white" style="background:linear-gradient(140deg,#303b97,#4b56b2)">
                                <p class="text-[26px] font-black num leading-none mb-1">{{ number_format($visitors['returning_today']) }}</p>
                                <p class="text-[10.5px] font-black uppercase tracking-wide text-white/70">Came back</p>
                                <p class="text-[10.5px] font-bold text-white/50 mt-1">{{ $visitors['return_rate'] }}% of today</p>
                            </div>
                            <div class="rounded-2xl p-4 border border-slate-200">
                                <p class="text-[26px] font-black num leading-none mb-1 text-slate-900">{{ number_format($visitors['new_today']) }}</p>
                                <p class="text-[10.5px] font-black uppercase tracking-wide text-slate-400">First time</p>
                                <p class="text-[10.5px] font-bold text-slate-400 mt-1">brand new today</p>
                            </div>
                        </div>

                        <p class="text-[12px] text-slate-500 font-semibold leading-relaxed mt-4">
                            <b class="text-slate-900 num">{{ number_format($visitors['yesterday']) }}</b> yesterday ·
                            <b class="text-slate-900 num">{{ number_format($visitors['sessions']) }}</b> sessions ·
                            <b class="text-slate-900 num">{{ number_format($visitors['month_unique']) }}</b> this month
                        </p>
                    </div>

                    {{-- Returning vs first-time, 14 days --}}
                    <div class="lg:col-span-2">
                        <div class="flex items-center justify-between mb-1 flex-wrap gap-2">
                            <h4 class="text-[13px] font-extrabold text-slate-900">Returning vs first-time visitors</h4>
                            <div class="flex items-center gap-3 text-[10.5px] font-bold text-slate-500">
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:#303b97"></span> Returning</span>
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:#c7cbe8"></span> First time</span>
                            </div>
                        </div>
                        <div id="visitorChart"></div>
                    </div>
                </div>

                {{-- ── Loyalty tiles ────────────────────────────────── --}}
                <div class="pt-5 border-t border-slate-100">
                    <h4 class="text-[13px] font-extrabold text-slate-900 mb-3">How loyal are they? <span class="font-bold text-slate-400">Last 30 days</span></h4>

                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                        @php
                            $loyalty = [
                                ['Came back',      number_format($visitors['repeat_30']),  'visited on 2+ days',  'fa-rotate-right'],
                                ['Regulars',       number_format($visitors['loyal_30']),   'visited on 4+ days',  'fa-heart'],
                                ['Guests who returned', number_format($visitors['guest_repeat_30']), 'never signed in', 'fa-user-secret'],
                                ['Visits each',    $visitors['avg_days'],                  'days per person',     'fa-calendar-check'],
                                ['Made an account',$visitors['converted_30'] . '%',        'of visitors',         'fa-user-plus'],
                                ['One and done',   $visitors['bounce_share'] . '%',        'came once only',      'fa-door-open'],
                            ];
                        @endphp
                        @foreach($loyalty as [$label, $value, $sub, $icon])
                            <div class="border border-slate-200 rounded-2xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="eyebrow truncate">{{ $label }}</span>
                                    <i class="fas {{ $icon }} text-[11px]" style="color:#303b97"></i>
                                </div>
                                <p class="text-[24px] font-black text-slate-900 num leading-none mb-1">{{ $value }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ $sub }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ── Signed in vs guests + device split ───────────── --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 md:gap-7 mt-5 pt-5 border-t border-slate-100">
                    <div>
                        <div class="flex items-center justify-between mb-2 flex-wrap gap-1">
                            <h4 class="text-[13px] font-extrabold text-slate-900">Signed in vs guests today</h4>
                            <span class="text-[11px] font-black" style="color:#303b97">{{ $visitors['guest_share'] }}% never sign in</span>
                        </div>
                        @php $signedShare = 100 - $visitors['guest_share']; @endphp
                        <div class="flex h-3 rounded-full overflow-hidden bg-slate-100 mb-2.5">
                            <div style="width:{{ $signedShare }}%;background:#303b97"></div>
                            <div style="width:{{ $visitors['guest_share'] }}%;background:#c7cbe8"></div>
                        </div>
                        <div class="flex items-center gap-4 text-[11px] font-bold text-slate-500 flex-wrap">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:#303b97"></span> {{ number_format($visitors['signed_in']) }} signed in</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:#c7cbe8"></span> {{ number_format($visitors['guests']) }} guests</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[13px] font-extrabold text-slate-900 mb-2">Where they come from</h4>
                        @php
                            $totalDevice = max($visitors['app'] + $visitors['web'], 1);
                            $appShare    = (int) round(($visitors['app'] / $totalDevice) * 100);
                        @endphp
                        <div class="flex h-3 rounded-full overflow-hidden bg-slate-100 mb-2.5">
                            <div style="width:{{ $appShare }}%;background:#8b5cf6"></div>
                            <div style="width:{{ 100 - $appShare }}%;background:#e2e6f3"></div>
                        </div>
                        <div class="flex items-center gap-4 text-[11px] font-bold text-slate-500 flex-wrap">
                            <span class="flex items-center gap-1.5"><i class="fas fa-mobile-screen" style="color:#8b5cf6"></i> {{ number_format($visitors['app']) }} in the app</span>
                            <span class="flex items-center gap-1.5"><i class="fas fa-globe text-slate-400"></i> {{ number_format($visitors['web']) }} on the website</span>
                        </div>
                    </div>
                </div>

                {{-- ── Landing pages ────────────────────────────────── --}}
                @if(count($visitors['top_pages']))
                    <div class="mt-5 pt-5 border-t border-slate-100">
                        <h4 class="text-[13px] font-extrabold text-slate-900 mb-3">Where guests land today</h4>
                        @php $topHit = max(array_column($visitors['top_pages'], 'total')) ?: 1; @endphp
                        <div class="space-y-2">
                            @foreach($visitors['top_pages'] as $page)
                                <div class="flex items-center gap-3">
                                    <span class="text-[11.5px] font-bold text-slate-600 truncate w-32 md:w-64">/{{ ltrim($page['path'], '/') }}</span>
                                    <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full" style="width:{{ round(($page['total'] / $topHit) * 100) }}%;background:#303b97"></div>
                                    </div>
                                    <span class="text-[11.5px] font-black text-slate-900 num w-10 text-right">{{ number_format($page['total']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         5. RETENTION — who comes back: members AND guests
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="sec-retention" class="card mb-4 md:mb-6 overflow-hidden scroll-mt-24">
        <div class="card-hd">
            <div>
                <p class="eyebrow mb-1">Loyalty</p>
                <h3 class="card-ttl">Who comes back</h3>
            </div>
            @if($retention['available'] ?? false)
                <div class="seg" id="retSeg">
                    <button class="on" data-group="everyone">Everyone</button>
                    <button data-group="members">Members</button>
                    <button data-group="guests" {{ ($retention['covers_guests'] ?? false) ? '' : 'disabled style=opacity:.4' }}>Guests</button>
                </div>
            @endif
        </div>

        @if(!($retention['available'] ?? false))
            <div class="empty">
                <i class="fas fa-chart-simple"></i>
                Nothing to measure yet.<br>
                <span class="text-[11.5px]">Switch on visitor tracking and return visits will appear here.</span>
            </div>
        @else
            @if(!($retention['covers_guests'] ?? false))
                <div class="px-4 md:px-6 py-2.5 bg-amber-50 border-b border-amber-100 flex items-center gap-2.5">
                    <i class="fas fa-circle-info text-amber-500 text-[12px]"></i>
                    <p class="text-[11.5px] font-bold text-amber-800">
                        Signed-in people only. Install the visits migration to include guests.
                    </p>
                </div>
            @endif

            <div class="p-4 md:p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 md:gap-7">

                    {{-- Gauge --}}
                    <div class="flex flex-col items-center justify-center">
                        @php
                            $g    = $retention['groups']['everyone'];
                            $circ = 2 * M_PI * 54;
                            $dash = round((max(0, min(100, $g['rate'])) / 100) * $circ, 2);
                        @endphp
                        <div class="gauge">
                            <svg width="128" height="128" viewBox="0 0 128 128">
                                <circle cx="64" cy="64" r="54" fill="none" stroke="#eef0f7" stroke-width="13"></circle>
                                <circle id="retGauge" cx="64" cy="64" r="54" fill="none" stroke="#303b97" stroke-width="13" stroke-linecap="round"
                                        stroke-dasharray="{{ $dash }} {{ $circ }}"></circle>
                            </svg>
                            <div class="gauge-mid">
                                <div>
                                    <p id="retRate" class="text-[30px] font-black text-slate-900 num leading-none">{{ $g['rate'] }}%</p>
                                    <p class="eyebrow mt-1">Come back</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-[12px] text-slate-500 font-semibold text-center mt-4 max-w-[240px] leading-snug">
                            <b id="retReturning" class="text-slate-900 num">{{ number_format($g['returning']) }}</b>
                            of <b id="retMonth" class="text-slate-900 num">{{ number_format($g['month']) }}</b>
                            <span id="retScopeWord">people</span> active this month visited on more than one day.
                        </p>
                    </div>

                    {{-- Tiles --}}
                    <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $tiles = [
                                ['retToday',     number_format($g['today']),     'Here today',        'people'],
                                ['retWeek',      number_format($g['week']),      'This week',         'people'],
                                ['retMonthTile', number_format($g['month']),     'This month',        'people'],
                                ['retBack',      number_format($g['returning']), 'Came back',         'visited 2+ days'],
                                ['retLoyal',     number_format($g['loyal']),     'Regulars',          'visited 4+ days'],
                                ['retAvg',       $g['avg_days'],                 'Visits each',       'days per person'],
                            ];
                        @endphp
                        @foreach($tiles as [$id, $value, $label, $sub])
                            <div class="ret-tile">
                                <b id="{{ $id }}" class="num">{{ $value }}</b>
                                <p class="text-[11.5px] font-extrabold text-slate-700 leading-tight">{{ $label }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">{{ $sub }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Weekly returning vs first-time --}}
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                        <h4 class="text-[13px] font-extrabold text-slate-900">Returning vs first-time, by week</h4>
                        <div class="flex items-center gap-3 text-[10.5px] font-bold text-slate-500">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:#303b97"></span> Returning</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:#c7cbe8"></span> First time</span>
                        </div>
                    </div>
                    <div id="returnChart"></div>
                </div>

                {{-- Peak hours --}}
                @if(array_sum($retention['hourly']) > 0)
                    <div class="mt-5 pt-5 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                            <h4 class="text-[13px] font-extrabold text-slate-900">When people are active</h4>
                            @if($retention['peak_hour'] !== null)
                                <span class="text-[10.5px] font-black px-2.5 py-1 rounded-lg" style="background:#f2f3fb;color:#303b97">
                                    Busiest at {{ str_pad($retention['peak_hour'], 2, '0', STR_PAD_LEFT) }}:00 — best time to broadcast
                                </span>
                            @endif
                        </div>
                        @php $maxHour = max($retention['hourly']) ?: 1; @endphp
                        <div class="hours">
                            @foreach($retention['hourly'] as $h => $count)
                                <span class="{{ $h === $retention['peak_hour'] ? 'peak' : '' }}"
                                      style="height:{{ max(3, round(($count / $maxHour) * 74)) }}px"
                                      title="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00 — {{ $count }} actions"></span>
                            @endforeach
                        </div>
                        <div class="flex justify-between text-[9.5px] font-bold text-slate-400 mt-1.5">
                            <span>00:00</span><span class="hidden sm:inline">06:00</span><span>12:00</span><span class="hidden sm:inline">18:00</span><span>23:00</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         6. COHORTS + WIN-BACK
    ═══════════════════════════════════════════════════════════════ --}}
    @if(count($cohorts) || count($winBack))
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6 mb-4 md:mb-6">

            <div class="xl:col-span-2 card overflow-hidden">
                <div class="card-hd">
                    <div>
                        <p class="eyebrow mb-1">Cohorts</p>
                        <h3 class="card-ttl">Do new signups stay?</h3>
                    </div>
                    <span class="text-[10.5px] font-bold text-slate-400">% still active</span>
                </div>

                @if(count($cohorts))
                    <div class="p-4 overflow-x-auto custom-scrollbar">
                        <table class="cohort">
                            <thead>
                                <tr>
                                    <th class="text-left">Signed up</th>
                                    <th>Users</th>
                                    @for($w = 0; $w <= 5; $w++)
                                        <th>Wk {{ $w }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cohorts as $row)
                                    <tr>
                                        <td class="head">{{ $row['label'] }}</td>
                                        <td class="head num">{{ $row['size'] }}</td>
                                        @for($w = 0; $w <= 5; $w++)
                                            @php $val = $row['cells'][$w] ?? null; @endphp
                                            @if($val === null)
                                                <td class="void"></td>
                                            @else
                                                <td class="num" style="background:rgba(48,59,151,{{ max(0.06, min(1, $val / 100)) }});color:{{ $val > 45 ? '#fff' : '#334155' }}">{{ $val }}%</td>
                                            @endif
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="text-[11px] text-slate-400 font-semibold mt-3">
                            Read each row left to right: of everyone who signed up that week, this is the share still opening listings weeks later.
                        </p>
                    </div>
                @else
                    <div class="empty"><i class="fas fa-table-cells"></i>Not enough history to build cohorts yet.</div>
                @endif
            </div>

            <div class="card overflow-hidden">
                <div class="card-hd">
                    <div>
                        <p class="eyebrow mb-1">Win back</p>
                        <h3 class="card-ttl">Gone quiet</h3>
                    </div>
                    <a href="{{ url('admin/notifications/broadcast') }}" class="ghost-btn"><i class="fas fa-bullhorn"></i> Message</a>
                </div>

                <div class="p-2 max-h-[330px] overflow-y-auto custom-scrollbar">
                    @forelse($winBack as $w)
                        @php $u = $link('admin.users.show', $w['id']); @endphp
                        <a href="{{ $u ?? '#' }}" class="lb-row">
                            @if($w['image'])
                                <img src="{{ $w['image'] }}" class="w-9 h-9 rounded-full object-cover bg-slate-100" alt="">
                            @else
                                <div class="w-9 h-9 rounded-full grid place-items-center bg-slate-100 text-slate-500 text-[11px] font-black">
                                    {{ strtoupper(substr($w['name'], 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-[12.5px] font-bold text-slate-900 truncate">{{ $w['name'] }}</p>
                                <p class="text-[10.5px] font-bold text-slate-400 truncate">{{ $w['hits'] }} visits before leaving</p>
                            </div>
                            <span class="text-[10px] font-black px-2 py-1 rounded-lg bg-amber-50 text-amber-700 shrink-0">{{ $w['gone'] }} ago</span>
                        </a>
                    @empty
                        <div class="empty"><i class="fas fa-heart"></i>Nobody has drifted away in the last 30 days.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════
         6. APPROVAL QUEUE + RENEWAL RADAR
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6 mb-4 md:mb-6">

        <div id="sec-queue" class="xl:col-span-2 card overflow-hidden scroll-mt-24">
            <div class="card-hd">
                <div>
                    <p class="eyebrow mb-1">Oldest first</p>
                    <h3 class="card-ttl">Approval queue
                        <span class="ml-1 text-[11px] font-black px-2 py-0.5 rounded-full" style="background:#f2f3fb;color:#303b97">{{ $pendingApprovals['properties'] }}</span>
                    </h3>
                </div>
                @if($link('admin.properties.index'))
                    <a href="{{ $link('admin.properties.index', ['status' => 'pending']) }}" class="ghost-btn">Full queue</a>
                @endif
            </div>

            @forelse($pendingProperties as $p)
                @php
                    $showUrl    = $link('admin.properties.show', $p['id']);
                    $approveUrl = $link('admin.properties.approve', $p['id']);
                    $rejectUrl  = $link('admin.properties.reject', $p['id']);
                @endphp
                <div class="queue-item">
                    @if($p['image'])
                        <img src="{{ asset($p['image']) }}" class="queue-thumb" alt="">
                    @else
                        <div class="queue-thumb grid place-items-center text-slate-300"><i class="fas fa-image"></i></div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 text-[13px] truncate">{{ $p['name'] }}</h4>
                                <p class="text-[11px] text-slate-500 font-semibold mt-0.5 truncate">
                                    {{ $p['owner'] }} · {{ $p['city'] }} · <span class="uppercase">{{ $p['type'] === 'sell' ? 'sale' : $p['type'] }}</span>
                                </p>
                            </div>
                            <span class="font-black text-slate-900 text-[13px] num whitespace-nowrap">${{ number_format($p['price']) }}</span>
                        </div>

                        <div class="flex items-center flex-wrap gap-1.5 mt-2.5">
                            <span class="text-[10px] font-black px-2 py-1.5 rounded-lg {{ $p['stale'] ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-500' }}">
                                <i class="far fa-clock mr-1"></i>{{ $p['waiting'] }}
                            </span>

                            @if($approveUrl)
                                <form method="POST" action="{{ $approveUrl }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-black px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 min-h-[32px]">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                </form>
                            @endif

                            @if($rejectUrl)
                                <form method="POST" action="{{ $rejectUrl }}" class="inline" onsubmit="return confirm('Reject this listing?')">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-black px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 min-h-[32px]">
                                        <i class="fas fa-xmark mr-1"></i>Reject
                                    </button>
                                </form>
                            @endif

                            @if($showUrl)
                                <a href="{{ $showUrl }}" class="text-[11px] font-black px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 min-h-[32px] inline-flex items-center">
                                    <i class="fas fa-eye mr-1"></i>Inspect
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty">
                    <i class="fas fa-mug-hot"></i>
                    Nothing is waiting for approval.
                </div>
            @endforelse
        </div>

        <div id="sec-renewals" class="card overflow-hidden scroll-mt-24">
            <div class="card-hd">
                <div>
                    <p class="eyebrow mb-1">Retention</p>
                    <h3 class="card-ttl">Renewal radar</h3>
                </div>
                <div class="tabs" id="radarTabs">
                    <button class="on" data-radar="expiring">Expiring</button>
                    <button data-radar="expired">Expired</button>
                </div>
            </div>

            @if($pulse['revenue_at_risk'] > 0)
                <div class="px-4 py-2.5 bg-amber-50 border-b border-amber-100 flex items-center gap-2.5">
                    <i class="fas fa-triangle-exclamation text-amber-500"></i>
                    <p class="text-[11.5px] font-bold text-amber-800"><span class="num">{{ number_format($pulse['revenue_at_risk']) }} IQD</span> renews within 7 days</p>
                </div>
            @endif

            <div id="radar-expiring" class="max-h-[400px] overflow-y-auto custom-scrollbar">
                @forelse($expiringNow as $r)
                    @php $editUrl = $link($r['type'] === 'agent' ? 'admin.agents.edit' : 'admin.offices.edit', $r['id']); @endphp
                    <div class="radar-item">
                        <span class="dot st-{{ $r['state'] }}"></span>
                        @if($r['image'])
                            <img src="{{ $r['image'] }}" class="w-9 h-9 rounded-xl object-cover bg-slate-100" alt="">
                        @else
                            <div class="w-9 h-9 rounded-xl grid place-items-center text-white text-[11px] font-black" style="background:#303b97">
                                {{ strtoupper(substr($r['name'], 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-bold text-slate-900 truncate">{{ $r['name'] }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide truncate">{{ $r['type'] }} · {{ $r['plan'] }} · {{ $r['end'] }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-black px-2 py-1 rounded-lg tx-{{ $r['state'] }}">{{ $r['label'] }}</span>
                            @if($editUrl)
                                <a href="{{ $editUrl }}" class="block text-[10px] font-black mt-1" style="color:#303b97">Renew →</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty"><i class="fas fa-shield-heart"></i>No subscription ends in the next 14 days.</div>
                @endforelse
            </div>

            <div id="radar-expired" class="hidden max-h-[400px] overflow-y-auto custom-scrollbar">
                @forelse($expiredNow as $r)
                    @php $editUrl = $link($r['type'] === 'agent' ? 'admin.agents.edit' : 'admin.offices.edit', $r['id']); @endphp
                    <div class="radar-item">
                        <span class="dot st-expired"></span>
                        @if($r['image'])
                            <img src="{{ $r['image'] }}" class="w-9 h-9 rounded-xl object-cover bg-slate-100" alt="">
                        @else
                            <div class="w-9 h-9 rounded-xl grid place-items-center text-white text-[11px] font-black bg-rose-500">
                                {{ strtoupper(substr($r['name'], 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-bold text-slate-900 truncate">{{ $r['name'] }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ $r['type'] }} · ended {{ $r['end'] }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-black px-2 py-1 rounded-lg tx-expired">{{ $r['label'] }}</span>
                            @if($editUrl)
                                <a href="{{ $editUrl }}" class="block text-[10px] font-black mt-1" style="color:#303b97">Reactivate →</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty"><i class="fas fa-face-smile"></i>No lapsed subscriptions in the last 30 days.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         7. INVENTORY + MOST VIEWED
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="sec-inventory" class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6 mb-4 md:mb-6 scroll-mt-24">

        <div class="card overflow-hidden">
            <div class="card-hd"><div><p class="eyebrow mb-1">Inventory</p><h3 class="card-ttl">Listing status</h3></div></div>
            <div class="p-2 md:p-4"><div id="statusChart"></div></div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-hd"><div><p class="eyebrow mb-1">Coverage</p><h3 class="card-ttl">Listings by city</h3></div></div>
            @if(count($topCities))
                <div class="p-2 md:p-4"><div id="cityChart"></div></div>
            @else
                <div class="empty" style="padding-top:70px"><i class="fas fa-map-location-dot"></i>No city data on listings yet.</div>
            @endif
        </div>

        <div class="card overflow-hidden">
            <div class="card-hd">
                <div><p class="eyebrow mb-1">Last 30 days</p><h3 class="card-ttl">Most viewed listings</h3></div>
            </div>
            <div class="p-2 max-h-[300px] overflow-y-auto custom-scrollbar">
                @forelse($topViewed as $i => $v)
                    @php $u = $link('admin.properties.show', $v['id']); @endphp
                    <a href="{{ $u ?? '#' }}" class="lb-row">
                        <span class="rank {{ $i === 0 ? 'rank-1' : '' }}">{{ $i + 1 }}</span>
                        @if($v['image'])
                            <img src="{{ asset($v['image']) }}" class="w-10 h-10 rounded-xl object-cover bg-slate-100" alt="">
                        @else
                            <div class="w-10 h-10 rounded-xl grid place-items-center bg-slate-100 text-slate-300"><i class="fas fa-image text-[11px]"></i></div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-bold text-slate-900 truncate">{{ $v['name'] }}</p>
                            <p class="text-[10.5px] font-bold text-slate-400 truncate">${{ number_format($v['price']) }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="block text-[13.5px] font-black text-slate-900 num">{{ number_format($v['hits']) }}</span>
                            <span class="text-[9.5px] font-black text-slate-400 uppercase">{{ $v['people'] }} people</span>
                        </div>
                    </a>
                @empty
                    <div class="empty"><i class="fas fa-eye"></i>No view data recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         8. REVENUE SPLIT
    ═══════════════════════════════════════════════════════════════ --}}
    @php
        $totalRev  = max($stats['subscription_revenue_iqd'], 1);
        $agentPct  = (int) round(($stats['agent_subscription_revenue'] / $totalRev) * 100);
        $officePct = (int) round(($stats['office_subscription_revenue'] / $totalRev) * 100);
    @endphp
    <div class="card mb-4 md:mb-6">
        <div class="card-hd">
            <div><p class="eyebrow mb-1">Money</p><h3 class="card-ttl">Where subscription revenue comes from</h3></div>
            @if($link('admin.subscriptions.index'))
                <a href="{{ $link('admin.subscriptions.index') }}" class="ghost-btn">Manage <i class="fas fa-arrow-right text-[10px]"></i></a>
            @endif
        </div>

        <div class="p-4 md:p-6">
            <div class="flex h-3 rounded-full overflow-hidden bg-slate-100 mb-5">
                <div style="width:{{ $agentPct }}%;background:#303b97"></div>
                <div style="width:{{ $officePct }}%;background:#8b5cf6"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $rev = [
                        ['Agents', $stats['agent_subscription_revenue'], $stats['agent_subscriptions_count'] . ' active agent plans', '#303b97', $agentPct . '% of revenue'],
                        ['Offices', $stats['office_subscription_revenue'], $stats['office_subscriptions_count'] . ' active office plans', '#8b5cf6', $officePct . '% of revenue'],
                        ['New this month', $stats['this_month_revenue'], $stats['new_subscriptions_this_month'] . ' subscriptions started', '#10b981', ($deltas['revenue'] >= 0 ? '+' : '') . $deltas['revenue'] . '% vs last month'],
                    ];
                @endphp
                @foreach($rev as [$label, $amount, $sub, $color, $note])
                    <div class="border border-slate-100 rounded-2xl p-4 md:p-5">
                        <div class="flex items-center gap-2 mb-2.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $color }}"></span>
                            <span class="eyebrow !text-slate-500">{{ $label }}</span>
                        </div>
                        <p class="text-[23px] md:text-[26px] font-black text-slate-900 num leading-none mb-1">{{ number_format($amount) }}
                            <span class="text-xs font-bold text-slate-400">IQD</span>
                        </p>
                        <p class="text-[11.5px] text-slate-500 font-semibold">{{ $sub }}</p>
                        <p class="text-[11px] font-bold mt-3 pt-3 border-t border-slate-100" style="color:{{ $color }}">{{ $note }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         9. PEOPLE — leaderboard, activity, new users, viewings
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="sec-people" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 scroll-mt-24">

        <div class="card overflow-hidden">
            <div class="card-hd">
                <div><p class="eyebrow mb-1">Most listings</p><h3 class="card-ttl">Leaderboard</h3></div>
                <div class="tabs" id="lbTabs">
                    <button class="on" data-lb="agents">Agents</button>
                    <button data-lb="offices">Offices</button>
                </div>
            </div>

            <div class="p-2 max-h-[360px] overflow-y-auto custom-scrollbar" id="lb-agents">
                @forelse($topAgents as $i => $a)
                    @php $u = $link('admin.agents.show', $a['id']); @endphp
                    <a href="{{ $u ?? '#' }}" class="lb-row">
                        <span class="rank {{ $i === 0 ? 'rank-1' : '' }}">{{ $i + 1 }}</span>
                        @if($a['image'])
                            <img src="{{ $a['image'] }}" class="w-9 h-9 rounded-full object-cover bg-slate-100" alt="">
                        @else
                            <div class="w-9 h-9 rounded-full grid place-items-center text-white text-[11px] font-black" style="background:#303b97">
                                {{ strtoupper(substr($a['name'], 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-bold text-slate-900 truncate">{{ $a['name'] }}</p>
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase truncate">{{ $a['sub'] }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="block text-[13.5px] font-black text-slate-900 num">{{ $a['total'] }}</span>
                            @if($a['weekly'] > 0)
                                <span class="text-[9.5px] font-black text-emerald-600">+{{ $a['weekly'] }} wk</span>
                            @else
                                <span class="text-[9.5px] font-bold text-slate-300 uppercase">listings</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="empty"><i class="fas fa-user-tie"></i>No agents yet.</div>
                @endforelse
            </div>

            <div class="p-2 hidden max-h-[360px] overflow-y-auto custom-scrollbar" id="lb-offices">
                @forelse($topOffices as $i => $o)
                    @php $u = $link('admin.offices.show', $o['id']); @endphp
                    <a href="{{ $u ?? '#' }}" class="lb-row">
                        <span class="rank {{ $i === 0 ? 'rank-1' : '' }}">{{ $i + 1 }}</span>
                        @if($o['image'])
                            <img src="{{ $o['image'] }}" class="w-9 h-9 rounded-xl object-cover bg-slate-100" alt="">
                        @else
                            <div class="w-9 h-9 rounded-xl grid place-items-center text-white text-[11px] font-black" style="background:#6C3FC5">
                                {{ strtoupper(substr($o['name'], 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-bold text-slate-900 truncate">{{ $o['name'] }}</p>
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase truncate">{{ $o['sub'] }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="block text-[13.5px] font-black text-slate-900 num">{{ $o['total'] }}</span>
                            @if($o['weekly'] > 0)
                                <span class="text-[9.5px] font-black text-emerald-600">+{{ $o['weekly'] }} wk</span>
                            @else
                                <span class="text-[9.5px] font-bold text-slate-300 uppercase">listings</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="empty"><i class="fas fa-building"></i>No offices yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-hd">
                <div><p class="eyebrow mb-1">{{ now()->format('D d M') }}</p><h3 class="card-ttl">Viewings today</h3></div>
                @if($link('admin.appointments.index'))
                    <a href="{{ $link('admin.appointments.index') }}" class="ghost-btn">All</a>
                @endif
            </div>
            <div class="p-2 max-h-[360px] overflow-y-auto custom-scrollbar">
                @forelse($todayAppointments as $a)
                    @php $apptUrl = $link('admin.appointments.show', $a['id']); @endphp
                    <a href="{{ $apptUrl ?? '#' }}" class="lb-row">
                        <span class="text-[12px] font-black num w-10 shrink-0" style="color:#303b97">{{ $a['time'] }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-bold text-slate-900 truncate">{{ $a['property'] }}</p>
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase truncate">with {{ $a['user'] }}</p>
                        </div>
                        <span class="text-[9.5px] font-black px-2 py-1 rounded-lg shrink-0
                            {{ $a['status'] === 'confirmed' ? 'bg-emerald-50 text-emerald-700' : ($a['status'] === 'cancelled' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                            {{ strtoupper($a['status']) }}
                        </span>
                    </a>
                @empty
                    <div class="empty"><i class="far fa-calendar"></i>No viewings booked today.</div>
                @endforelse
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-hd"><div><p class="eyebrow mb-1">Live</p><h3 class="card-ttl">Recent activity</h3></div></div>
            <div class="p-4 md:p-5 max-h-[360px] overflow-y-auto custom-scrollbar">
                @php
                    $tones = ['blue' => ['#eff6ff', '#2563eb'], 'indigo' => ['#f2f3fb', '#303b97'], 'amber' => ['#fffbeb', '#d97706'], 'violet' => ['#f5f3ff', '#7c3aed']];
                @endphp
                @forelse($activity as $ev)
                    @php [$bg, $fg] = $tones[$ev['tone']] ?? $tones['indigo']; @endphp
                    <div class="tl">
                        <div class="tl-item">
                            <i class="fas {{ $ev['icon'] }}" style="background:{{ $bg }};color:{{ $fg }}"></i>
                            <p class="text-[12px] text-slate-700 leading-snug">
                                <span class="font-bold text-slate-900">{{ \Illuminate\Support\Str::limit($ev['title'], 30) }}</span> {{ $ev['text'] }}
                            </p>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $ev['ago'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="empty"><i class="fas fa-wave-square"></i>No activity recorded yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-hd">
                <div><p class="eyebrow mb-1">Signups</p><h3 class="card-ttl">Newest users</h3></div>
                @if($link('admin.users.index'))
                    <a href="{{ $link('admin.users.index') }}" class="ghost-btn">All</a>
                @endif
            </div>
            <div class="p-2 max-h-[360px] overflow-y-auto custom-scrollbar">
                @forelse($recent_users as $user)
                    @php $u = $link('admin.users.show', $user->id); @endphp
                    <a href="{{ $u ?? '#' }}" class="lb-row">
                        @if($user->photo_image)
                            <img src="{{ $user->photo_image }}" class="w-9 h-9 rounded-full object-cover bg-slate-100" alt="">
                        @else
                            <div class="w-9 h-9 rounded-full grid place-items-center bg-slate-100 text-slate-500 text-[11px] font-black">
                                {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-bold text-slate-900 truncate">{{ $user->username }}</p>
                            <p class="text-[10.5px] font-bold text-slate-400 truncate">{{ $user->email }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="block text-[9.5px] font-black px-2 py-1 rounded-lg {{ $user->is_verified ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $user->is_verified ? 'VERIFIED' : 'PENDING' }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 block mt-1">{{ optional($user->created_at)->format('d M') }}</span>
                        </div>
                    </a>
                @empty
                    <div class="empty"><i class="fas fa-user-plus"></i>No users yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    if (typeof ApexCharts === 'undefined') return;

    const series    = @json($charts);
    const retention = @json($retention);
    const statusData = @json($statusCounts);
    const cities     = @json($topCities);

    const BRAND  = '#303b97';
    const mobile = window.matchMedia('(max-width: 767px)').matches;
    const money  = v => new Intl.NumberFormat('en-US').format(Math.round(v));

    /* ---------- KPI sparklines ---------- */
    function spark(sel, data, color) {
        const node = document.querySelector(sel);
        if (!node) return;
        new ApexCharts(node, {
            chart: { type: 'area', height: 56, sparkline: { enabled: true }, animations: { enabled: false } },
            series: [{ data: data }],
            stroke: { curve: 'smooth', width: 2 },
            colors: [color],
            fill: { type: 'gradient', gradient: { opacityFrom: .22, opacityTo: 0 } },
            tooltip: { enabled: false }
        }).render();
    }

    spark('#spark-revenue',    series.revenue,    '#10b981');
    spark('#spark-users',      series.users,      BRAND);
    spark('#spark-properties', series.properties, '#8b5cf6');
    spark('#spark-network',    series.properties.map((v, i) => v + series.users[i]), '#f59e0b');

    /* ---------- Growth ---------- */
    let metric = 'users';
    let range  = mobile ? 6 : 12;

    const META = {
        users:      { name: 'New users',    color: BRAND,     fmt: v => money(v) },
        properties: { name: 'New listings', color: '#8b5cf6', fmt: v => money(v) },
        revenue:    { name: 'Revenue',      color: '#10b981', fmt: v => money(v) + ' IQD' }
    };

    function growthOptions() {
        const meta = META[metric];
        return {
            series: [{ name: meta.name, data: series[metric].slice(-range) }],
            chart: { type: 'area', height: mobile ? 235 : 320, fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false } },
            colors: [meta.color],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .28, opacityTo: 0, stops: [0, 100] } },
            markers: { size: 0, hover: { size: 6 } },
            xaxis: {
                categories: series.labels.slice(-range),
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { rotate: 0, style: { colors: '#94a3b8', fontSize: mobile ? '10px' : '11px', fontWeight: 700 } }
            },
            yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10.5px', fontWeight: 700 }, formatter: v => money(v) } },
            grid: { borderColor: '#f1f2f7', strokeDashArray: 5, padding: { left: 6, right: 8 } },
            tooltip: { y: { formatter: meta.fmt } }
        };
    }

    let growth = null;
    const growthEl = document.querySelector('#growthChart');
    if (growthEl) { growth = new ApexCharts(growthEl, growthOptions()); growth.render(); }

    document.querySelectorAll('#rangeSeg button').forEach(function (b) {
        if (parseInt(b.dataset.range, 10) === range) {
            document.querySelectorAll('#rangeSeg button').forEach(x => x.classList.remove('on'));
            b.classList.add('on');
        }
    });

    function segment(sel, apply) {
        document.querySelectorAll(sel + ' button').forEach(function (b) {
            b.addEventListener('click', function () {
                document.querySelectorAll(sel + ' button').forEach(x => x.classList.remove('on'));
                b.classList.add('on');
                apply(b);
                if (growth) growth.updateOptions(growthOptions(), true, true);
            });
        });
    }

    segment('#metricSeg', b => { metric = b.dataset.metric; });
    segment('#rangeSeg',  b => { range = parseInt(b.dataset.range, 10); });

    /* ---------- Visitors: returning vs first-time ---------- */
    const visitors = @json($visitors);
    const visitorEl = document.querySelector('#visitorChart');

    if (visitorEl && visitors.available) {
        const days = mobile ? 7 : 14;
        new ApexCharts(visitorEl, {
            series: [
                { name: 'Returning',  data: (visitors.series_returning || []).slice(-days) },
                { name: 'First time', data: (visitors.series_new || []).slice(-days) }
            ],
            chart: { type: 'bar', stacked: true, height: mobile ? 210 : 262, fontFamily: 'inherit', toolbar: { show: false } },
            colors: [BRAND, '#c7cbe8'],
            plotOptions: { bar: { borderRadius: 5, columnWidth: '58%' } },
            dataLabels: { enabled: false },
            legend: { show: false },
            xaxis: {
                categories: (visitors.labels || []).slice(-days),
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { rotate: 0, hideOverlappingLabels: true, style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } }
            },
            yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10.5px', fontWeight: 700 } } },
            grid: { borderColor: '#f1f2f7', strokeDashArray: 5 },
            tooltip: { shared: true, intersect: false, y: { formatter: v => v + ' people' } }
        }).render();
    }

    /* ---------- Retention: weekly returning vs first-time ---------- */
    const returnEl = document.querySelector('#returnChart');
    if (returnEl && retention.available) {
        const weeks = mobile ? 6 : 12;
        new ApexCharts(returnEl, {
            series: [
                { name: 'Returning',  data: (retention.weekly_return || []).slice(-weeks) },
                { name: 'First time', data: (retention.weekly_new || []).slice(-weeks) }
            ],
            chart: { type: 'bar', stacked: true, height: mobile ? 195 : 245, fontFamily: 'inherit', toolbar: { show: false } },
            colors: [BRAND, '#c7cbe8'],
            plotOptions: { bar: { borderRadius: 5, columnWidth: '58%' } },
            dataLabels: { enabled: false },
            legend: { show: false },
            xaxis: {
                categories: (retention.weeks || []).slice(-weeks),
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { rotate: 0, hideOverlappingLabels: true, style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } }
            },
            yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10.5px', fontWeight: 700 } } },
            grid: { borderColor: '#f1f2f7', strokeDashArray: 5 },
            tooltip: { shared: true, intersect: false, y: { formatter: v => v + ' people' } }
        }).render();
    }

    /* ---------- Retention: Everyone / Members / Guests ---------- */
    const GROUPS = retention.groups || {};
    const CIRC   = 2 * Math.PI * 54;
    const fmt    = v => new Intl.NumberFormat('en-US').format(v);

    const WORD = { everyone: 'people', members: 'members', guests: 'guests' };

    function showGroup(name) {
        const g = GROUPS[name];
        if (!g) return;

        const set = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        };

        set('retRate', g.rate + '%');
        set('retReturning', fmt(g.returning));
        set('retMonth', fmt(g.month));
        set('retScopeWord', WORD[name] || 'people');
        set('retToday', fmt(g.today));
        set('retWeek', fmt(g.week));
        set('retMonthTile', fmt(g.month));
        set('retBack', fmt(g.returning));
        set('retLoyal', fmt(g.loyal));
        set('retAvg', g.avg_days);

        const gauge = document.getElementById('retGauge');
        if (gauge) {
            const pct = Math.max(0, Math.min(100, g.rate));
            gauge.setAttribute('stroke-dasharray', ((pct / 100) * CIRC).toFixed(2) + ' ' + CIRC.toFixed(2));
            gauge.setAttribute('stroke', name === 'guests' ? '#8b5cf6' : BRAND);
        }
    }

    document.querySelectorAll('#retSeg button').forEach(function (b) {
        b.addEventListener('click', function () {
            if (b.hasAttribute('disabled')) return;
            document.querySelectorAll('#retSeg button').forEach(x => x.classList.remove('on'));
            b.classList.add('on');
            showGroup(b.dataset.group);
        });
    });

    /* ---------- Listing status ---------- */
    const statusEl = document.querySelector('#statusChart');
    if (statusEl) {
        const labels  = Object.keys(statusData);
        const values  = Object.values(statusData).map(Number);
        const palette = { available: '#22c55e', pending: '#f59e0b', sold: BRAND, rented: '#8b5cf6', suspended: '#94a3b8', rejected: '#ef4444' };

        new ApexCharts(statusEl, {
            series: values.length ? values : [1],
            labels: labels.length ? labels.map(l => l.charAt(0).toUpperCase() + l.slice(1)) : ['No data'],
            chart: { type: 'donut', height: mobile ? 250 : 285, fontFamily: 'inherit' },
            colors: labels.length ? labels.map(l => palette[l] || '#cbd5e1') : ['#e2e8f0'],
            stroke: { width: 3, colors: ['#fff'] },
            legend: { position: 'bottom', fontSize: '11.5px', fontWeight: 700, labels: { colors: '#64748b' }, markers: { radius: 12 } },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: { donut: { size: '72%', labels: {
                    show: true,
                    name:  { fontSize: '11px', fontWeight: 800, color: '#94a3b8' },
                    value: { fontSize: '24px', fontWeight: 900, color: '#0f172a', formatter: v => money(v) },
                    total: { show: true, label: 'Total listings', fontSize: '10.5px', fontWeight: 800, color: '#94a3b8',
                             formatter: w => money(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) }
                } } }
            }
        }).render();
    }

    /* ---------- Cities ---------- */
    const cityEl = document.querySelector('#cityChart');
    if (cityEl && cities.length) {
        new ApexCharts(cityEl, {
            series: [{ name: 'Listings', data: cities.map(c => c.total) }],
            chart: { type: 'bar', height: mobile ? 250 : 285, fontFamily: 'inherit', toolbar: { show: false } },
            colors: [BRAND],
            plotOptions: { bar: { horizontal: true, borderRadius: 7, barHeight: '60%' } },
            dataLabels: { enabled: true, style: { fontSize: '10.5px', fontWeight: 800, colors: ['#fff'] }, offsetX: -6 },
            xaxis: {
                categories: cities.map(c => c.city),
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { colors: '#94a3b8', fontSize: '10.5px', fontWeight: 700 } }
            },
            yaxis: { labels: { style: { colors: '#475569', fontSize: '11px', fontWeight: 800 } } },
            grid: { borderColor: '#f1f2f7', strokeDashArray: 5 }
        }).render();
    }

    /* ---------- Tabs ---------- */
    function wireTabs(container, attr, prefix) {
        document.querySelectorAll(container + ' button').forEach(function (b) {
            b.addEventListener('click', function () {
                document.querySelectorAll(container + ' button').forEach(x => x.classList.remove('on'));
                b.classList.add('on');
                document.querySelectorAll('[id^="' + prefix + '"]').forEach(p => p.classList.add('hidden'));
                const panel = document.getElementById(prefix + b.dataset[attr]);
                if (panel) panel.classList.remove('hidden');
            });
        });
    }

    wireTabs('#radarTabs', 'radar', 'radar-');
    wireTabs('#lbTabs', 'lb', 'lb-');
})();
</script>
@endpush

@endsection
