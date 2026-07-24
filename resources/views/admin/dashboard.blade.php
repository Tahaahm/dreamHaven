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
        content: ""; position: absolute; inset: 0; pointer-events: none; opacity: .35;
        background-image: radial-gradient(rgba(255,255,255,.10) 1px, transparent 1px);
        background-size: 22px 22px;
    }

    .card { background:#fff; border:1px solid #e8eaf0; border-radius:20px; }
    .card-hd { padding:18px 22px; border-bottom:1px solid #f1f2f7; display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .card-ttl { font-weight:800; color:#0f172a; font-size:15px; letter-spacing:-.01em; }

    .eyebrow { font-size:10px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; color:#94a3b8; }

    .kpi { position:relative; overflow:hidden; transition:.22s ease; }
    .kpi:hover { transform:translateY(-3px); box-shadow:0 18px 40px -22px rgba(48,59,151,.55); border-color:#c9cee8; }
    .kpi-spark { position:absolute; left:0; right:0; bottom:-6px; height:64px; opacity:.85; pointer-events:none; }

    .chip { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:800; padding:3px 9px; border-radius:999px; }
    .chip-up   { background:#ecfdf5; color:#047857; }
    .chip-down { background:#fef2f2; color:#b91c1c; }
    .chip-flat { background:#f1f5f9; color:#475569; }

    .task-row { display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:14px; transition:.16s; }
    .task-row:hover { background:rgba(255,255,255,.07); }
    .task-count { min-width:34px; text-align:center; font-weight:900; font-size:15px; }

    .queue-item { display:flex; gap:14px; padding:14px 22px; border-bottom:1px solid #f4f5f9; transition:.16s; }
    .queue-item:hover { background:#fafbff; }
    .queue-thumb { width:76px; height:64px; border-radius:14px; object-fit:cover; background:#eef0f7; flex-shrink:0; }

    .radar-item { display:flex; align-items:center; gap:12px; padding:12px 22px; border-bottom:1px solid #f4f5f9; }
    .radar-item:last-child { border-bottom:0; }
    .dot { width:8px; height:8px; border-radius:999px; flex-shrink:0; }
    .st-expired  { background:#ef4444; } .tx-expired  { color:#b91c1c; background:#fef2f2; }
    .st-critical { background:#f97316; } .tx-critical { color:#c2410c; background:#fff7ed; }
    .st-soon     { background:#eab308; } .tx-soon     { color:#a16207; background:#fefce8; }
    .st-ok       { background:#22c55e; } .tx-ok       { color:#15803d; background:#f0fdf4; }

    .lb-row { display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:14px; transition:.16s; }
    .lb-row:hover { background:#f7f8fc; }
    .rank { width:22px; text-align:center; font-weight:900; font-size:13px; color:#cbd5e1; }
    .rank-1 { color:#303b97; }

    .tl { position:relative; padding-left:30px; }
    .tl::before { content:""; position:absolute; left:11px; top:6px; bottom:6px; width:2px; background:#eef0f7; }
    .tl-item { position:relative; padding:9px 0; }
    .tl-item i { position:absolute; left:-30px; top:8px; width:23px; height:23px; border-radius:999px; display:grid; place-items:center; font-size:9px; border:2px solid #fff; }

    .seg { display:inline-flex; background:#f1f2f7; padding:3px; border-radius:11px; }
    .seg button { border:0; background:transparent; font-size:11px; font-weight:800; color:#64748b; padding:5px 12px; border-radius:8px; cursor:pointer; transition:.16s; }
    .seg button.on { background:#fff; color:#303b97; box-shadow:0 1px 3px rgba(15,23,42,.12); }

    .tabs button { border:0; background:transparent; font-size:12px; font-weight:800; color:#94a3b8; padding:6px 2px; margin-right:16px; cursor:pointer; border-bottom:2px solid transparent; }
    .tabs button.on { color:#303b97; border-color:#303b97; }

    .ghost-btn { display:inline-flex; align-items:center; gap:7px; font-size:12px; font-weight:800; padding:8px 13px; border-radius:11px; border:1px solid #e4e6ef; color:#475569; background:#fff; transition:.16s; }
    .ghost-btn:hover { border-color:#303b97; color:#303b97; background:#f5f6fd; }

    .solid-btn { display:inline-flex; align-items:center; gap:8px; font-size:12.5px; font-weight:800; padding:10px 16px; border-radius:12px; color:#fff;
                 background:linear-gradient(135deg,#303b97,#4b56b2); box-shadow:0 10px 22px -12px rgba(48,59,151,.9); transition:.16s; }
    .solid-btn:hover { filter:brightness(1.08); transform:translateY(-1px); }

    .empty { padding:34px 22px; text-align:center; color:#94a3b8; font-size:13px; font-weight:600; }
    .empty i { display:block; font-size:26px; margin-bottom:10px; color:#dbe0ee; }

    @media (prefers-reduced-motion: reduce) { * { animation:none !important; transition:none !important; } }
</style>
@endpush

@section('content')
<div class="max-w-[1640px] mx-auto pb-10">

    {{-- ══════════════════════════════════════════════════════════════
         1. HERO — the shift briefing
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="console-hero grain relative overflow-hidden rounded-[26px] text-white p-7 md:p-9 mb-7">
        <div class="relative z-10 flex flex-col xl:flex-row xl:items-end justify-between gap-7">

            <div class="min-w-0">
                <p class="eyebrow text-white/45 mb-2">{{ now()->format('l, d F Y') }} · Erbil</p>
                <h1 class="text-[30px] md:text-[38px] font-black tracking-tight leading-none mb-3">
                    {{ $greeting }}, {{ \Illuminate\Support\Str::before($adminName, ' ') }}
                </h1>

                @if($totalTasks > 0)
                    <p class="text-white/70 text-sm font-medium max-w-xl">
                        <span class="text-white font-black">{{ $totalTasks }}</span> item{{ $totalTasks === 1 ? '' : 's' }}
                        need your decision today@if($pulse['revenue_at_risk'] > 0), and
                        <span class="text-white font-black num">{{ number_format($pulse['revenue_at_risk']) }} IQD</span>
                        of subscriptions expire within 7 days@endif.
                    </p>
                @else
                    <p class="text-white/70 text-sm font-medium">Queue is clear. Nothing is waiting on you.</p>
                @endif

                <div class="flex flex-wrap items-center gap-2.5 mt-6">
                    @if($link('admin.properties.create'))
                        <a href="{{ $link('admin.properties.create') }}" class="solid-btn"><i class="fas fa-plus"></i> Add property</a>
                    @endif
                    @if($link('admin.projects.create'))
                        <a href="{{ $link('admin.projects.create') }}" class="ghost-btn !bg-white/10 !border-white/15 !text-white hover:!bg-white/20 hover:!text-white"><i class="fas fa-city"></i> Add project</a>
                    @endif
                    <a href="{{ url('admin/notifications/broadcast') }}" class="ghost-btn !bg-white/10 !border-white/15 !text-white hover:!bg-white/20 hover:!text-white"><i class="fas fa-bullhorn"></i> Send broadcast</a>
                    <button type="button" onclick="openPalette()" class="ghost-btn !bg-white/10 !border-white/15 !text-white hover:!bg-white/20 hover:!text-white">
                        <i class="fas fa-bolt"></i> Jump to… <kbd class="ml-1 text-[10px] bg-white/15 px-1.5 py-0.5 rounded">Ctrl K</kbd>
                    </button>
                    <a href="{{ route('admin.dashboard') }}?refresh=1" class="ghost-btn !bg-transparent !border-white/15 !text-white/60 hover:!text-white" title="Recalculate cached figures">
                        <i class="fas fa-rotate"></i>
                    </a>
                </div>
            </div>

            {{-- Pulse strip --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 shrink-0">
                @php
                    $pulseCards = [
                        ['New this week', number_format($pulse['listings_week']), 'listings'],
                        ['Boosted now',   number_format($pulse['boosted']),       'listings'],
                        ['Agent trust',   $pulse['verify_rate'] . '%',            'verified'],
                        ['Avg. asking',   '$' . number_format($pulse['avg_price']), 'per listing'],
                    ];
                @endphp
                @foreach($pulseCards as [$label, $value, $sub])
                    <div class="bg-white/[.07] border border-white/10 rounded-2xl px-4 py-3.5 backdrop-blur-sm min-w-[132px]">
                        <p class="eyebrow text-white/40 mb-1.5">{{ $label }}</p>
                        <p class="text-2xl font-black num leading-none">{{ $value }}</p>
                        <p class="text-[10px] text-white/45 font-bold mt-1.5 uppercase tracking-wide">{{ $sub }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         2. KPI ROW
    ═══════════════════════════════════════════════════════════════ --}}
    @php
        $kpis = [
            [
                'key'   => 'revenue',
                'label' => 'Subscription revenue',
                'value' => number_format($stats['subscription_revenue_iqd']),
                'unit'  => 'IQD active',
                'icon'  => 'fa-wallet',
                'delta' => $deltas['revenue'],
                'foot'  => $stats['active_subscriptions'] . ' active plans · ' . number_format($stats['this_month_revenue']) . ' IQD this month',
                'href'  => $link('admin.subscriptions.index'),
            ],
            [
                'key'   => 'users',
                'label' => 'Registered users',
                'value' => number_format($stats['total_users']),
                'unit'  => 'accounts',
                'icon'  => 'fa-users',
                'delta' => $deltas['users'],
                'foot'  => '+' . $stats['new_users_today'] . ' today · +' . $stats['new_users_week'] . ' this week',
                'href'  => $link('admin.users.index'),
            ],
            [
                'key'   => 'properties',
                'label' => 'Listings',
                'value' => number_format($stats['total_properties']),
                'unit'  => 'total',
                'icon'  => 'fa-house',
                'delta' => $deltas['properties'],
                'foot'  => $stats['properties_for_sale'] . ' for sale · ' . $stats['properties_for_rent'] . ' for rent',
                'href'  => $link('admin.properties.index'),
            ],
            [
                'key'   => 'network',
                'label' => 'Partner network',
                'value' => number_format($stats['total_agents'] + $stats['total_offices']),
                'unit'  => 'agents & offices',
                'icon'  => 'fa-handshake',
                'delta' => null,
                'foot'  => $stats['total_agents'] . ' agents · ' . $stats['total_offices'] . ' offices · ' . $stats['total_projects'] . ' projects',
                'href'  => $link('admin.agents.index'),
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-7">
        @foreach($kpis as $k)
            <a href="{{ $k['href'] ?? '#' }}" class="kpi card p-6 block">
                <div class="flex items-start justify-between mb-5">
                    <div class="w-11 h-11 rounded-2xl grid place-items-center" style="background:#f2f3fb;color:#303b97;">
                        <i class="fas {{ $k['icon'] }}"></i>
                    </div>
                    @if($k['delta'] !== null)
                        <span class="chip {{ $k['delta'] > 0 ? 'chip-up' : ($k['delta'] < 0 ? 'chip-down' : 'chip-flat') }}">
                            <i class="fas {{ $k['delta'] > 0 ? 'fa-arrow-trend-up' : ($k['delta'] < 0 ? 'fa-arrow-trend-down' : 'fa-minus') }} text-[9px]"></i>
                            {{ $k['delta'] > 0 ? '+' : '' }}{{ $k['delta'] }}%
                        </span>
                    @endif
                </div>

                <p class="eyebrow mb-2">{{ $k['label'] }}</p>
                <h3 class="text-[32px] font-black text-slate-900 num leading-none mb-1">{{ $k['value'] }}</h3>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">{{ $k['unit'] }}</p>

                <div class="relative mt-5 pt-4 border-t border-slate-100">
                    <p class="text-[11.5px] text-slate-500 font-semibold relative z-10">{{ $k['foot'] }}</p>
                </div>
                <div class="kpi-spark" id="spark-{{ $k['key'] }}"></div>
            </a>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         3. GROWTH CHART + ACTION CENTER
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-7">

        {{-- Growth --}}
        <div class="xl:col-span-2 card overflow-hidden">
            <div class="card-hd flex-wrap">
                <div>
                    <p class="eyebrow mb-1">Trend</p>
                    <h3 class="card-ttl">Platform growth</h3>
                </div>
                <div class="flex items-center gap-3">
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
            <div class="p-4"><div id="growthChart" style="height:322px"></div></div>
        </div>

        {{-- Action center --}}
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
        <div class="rounded-[20px] p-6 text-white relative overflow-hidden" style="background:linear-gradient(165deg,#1a1d2e,#12141f);">
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
                <h3 class="text-2xl font-black mb-5">Action center</h3>

                <div class="space-y-0.5 -mx-2">
                    @foreach($tasks as [$label, $count, $icon, $url])
                        <a href="{{ $url ?? '#' }}" class="task-row {{ $count > 0 ? '' : 'opacity-40' }}">
                            <span class="w-8 h-8 rounded-xl grid place-items-center text-[12px] shrink-0"
                                  style="background:{{ $count > 0 ? 'rgba(48,59,151,.55)' : 'rgba(255,255,255,.05)' }}">
                                <i class="fas {{ $icon }}"></i>
                            </span>
                            <span class="flex-1 text-[13px] font-bold text-white/85">{{ $label }}</span>
                            <span class="task-count num {{ $count > 0 ? 'text-white' : 'text-white/30' }}">{{ $count }}</span>
                            <i class="fas fa-chevron-right text-[10px] text-white/25"></i>
                        </a>
                    @endforeach
                </div>

                @if($totalTasks === 0)
                    <div class="mt-5 rounded-2xl bg-emerald-500/10 border border-emerald-400/20 px-4 py-3 text-[12.5px] font-bold text-emerald-300">
                        <i class="fas fa-check-circle mr-1.5"></i> Everything is reviewed.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         4. REVENUE SPLIT
    ═══════════════════════════════════════════════════════════════ --}}
    @php
        $totalRev = max($stats['subscription_revenue_iqd'], 1);
        $agentPct = (int) round(($stats['agent_subscription_revenue'] / $totalRev) * 100);
        $officePct = (int) round(($stats['office_subscription_revenue'] / $totalRev) * 100);
    @endphp
    <div class="card mb-7">
        <div class="card-hd">
            <div>
                <p class="eyebrow mb-1">Money</p>
                <h3 class="card-ttl">Where subscription revenue comes from</h3>
            </div>
            @if($link('admin.subscriptions.index'))
                <a href="{{ $link('admin.subscriptions.index') }}" class="ghost-btn">Manage subscriptions <i class="fas fa-arrow-right text-[10px]"></i></a>
            @endif
        </div>

        <div class="p-6">
            <div class="flex h-3 rounded-full overflow-hidden bg-slate-100 mb-6">
                <div style="width:{{ $agentPct }}%;background:#303b97" title="Agents"></div>
                <div style="width:{{ $officePct }}%;background:#8b5cf6" title="Offices"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $rev = [
                        ['Agents', $stats['agent_subscription_revenue'], $stats['agent_subscriptions_count'] . ' active agent plans', '#303b97', $agentPct . '% of revenue'],
                        ['Offices', $stats['office_subscription_revenue'], $stats['office_subscriptions_count'] . ' active office plans', '#8b5cf6', $officePct . '% of revenue'],
                        ['New this month', $stats['this_month_revenue'], $stats['new_subscriptions_this_month'] . ' subscriptions started', '#10b981', ($deltas['revenue'] >= 0 ? '+' : '') . $deltas['revenue'] . '% vs last month'],
                    ];
                @endphp
                @foreach($rev as [$label, $amount, $sub, $color, $note])
                    <div class="border border-slate-100 rounded-2xl p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $color }}"></span>
                            <span class="eyebrow !text-slate-500">{{ $label }}</span>
                        </div>
                        <p class="text-[26px] font-black text-slate-900 num leading-none mb-1">{{ number_format($amount) }}
                            <span class="text-xs font-bold text-slate-400">IQD</span>
                        </p>
                        <p class="text-[12px] text-slate-500 font-semibold">{{ $sub }}</p>
                        <p class="text-[11px] font-bold mt-3 pt-3 border-t border-slate-100" style="color:{{ $color }}">{{ $note }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         5. APPROVAL QUEUE + RENEWAL RADAR   (the working surface)
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-7">

        {{-- Approval queue --}}
        <div class="xl:col-span-2 card overflow-hidden">
            <div class="card-hd">
                <div>
                    <p class="eyebrow mb-1">Oldest first</p>
                    <h3 class="card-ttl">Approval queue
                        <span class="ml-1.5 text-[11px] font-black px-2 py-0.5 rounded-full" style="background:#f2f3fb;color:#303b97">{{ $pendingApprovals['properties'] }}</span>
                    </h3>
                </div>
                @if($link('admin.properties.index'))
                    <a href="{{ $link('admin.properties.index', ['status' => 'pending']) }}" class="ghost-btn">Open full queue</a>
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
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 text-[13.5px] truncate">{{ $p['name'] }}</h4>
                                <p class="text-[11.5px] text-slate-500 font-semibold mt-0.5 truncate">
                                    {{ $p['owner'] }} · {{ $p['city'] }} ·
                                    <span class="uppercase">{{ $p['type'] === 'sell' ? 'sale' : $p['type'] }}</span>
                                </p>
                            </div>
                            <span class="font-black text-slate-900 text-[13.5px] num whitespace-nowrap">${{ number_format($p['price']) }}</span>
                        </div>

                        <div class="flex items-center flex-wrap gap-2 mt-2.5">
                            <span class="text-[10.5px] font-black px-2 py-1 rounded-lg {{ $p['stale'] ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-500' }}">
                                <i class="far fa-clock mr-1"></i>waiting {{ $p['waiting'] }}
                            </span>

                            @if($approveUrl)
                                <form method="POST" action="{{ $approveUrl }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-black px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                </form>
                            @endif

                            @if($rejectUrl)
                                <form method="POST" action="{{ $rejectUrl }}" class="inline" onsubmit="return confirm('Reject this listing?')">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-black px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition">
                                        <i class="fas fa-xmark mr-1"></i>Reject
                                    </button>
                                </form>
                            @endif

                            @if($showUrl)
                                <a href="{{ $showUrl }}" class="text-[11px] font-black px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
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
                    @if($link('admin.properties.create'))
                        <br><a href="{{ $link('admin.properties.create') }}" class="text-[12px] font-black" style="color:#303b97">Add a listing yourself →</a>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Renewal radar --}}
        <div class="card overflow-hidden">
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
                <div class="px-6 py-3 bg-amber-50 border-b border-amber-100 flex items-center gap-2.5">
                    <i class="fas fa-triangle-exclamation text-amber-500"></i>
                    <p class="text-[12px] font-bold text-amber-800">
                        <span class="num">{{ number_format($pulse['revenue_at_risk']) }} IQD</span> renews within 7 days
                    </p>
                </div>
            @endif

            <div id="radar-expiring" class="max-h-[430px] overflow-y-auto custom-scrollbar">
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
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wide">
                                {{ $r['type'] }} · {{ $r['plan'] }} · {{ $r['end'] }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-black px-2 py-1 rounded-lg tx-{{ $r['state'] }}">{{ $r['label'] }}</span>
                            @if($editUrl)
                                <a href="{{ $editUrl }}" class="block text-[10px] font-black mt-1.5" style="color:#303b97">Renew →</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty"><i class="fas fa-shield-heart"></i>No subscription ends in the next 14 days.</div>
                @endforelse
            </div>

            <div id="radar-expired" class="hidden max-h-[430px] overflow-y-auto custom-scrollbar">
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
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wide">{{ $r['type'] }} · ended {{ $r['end'] }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-black px-2 py-1 rounded-lg tx-expired">{{ $r['label'] }}</span>
                            @if($editUrl)
                                <a href="{{ $editUrl }}" class="block text-[10px] font-black mt-1.5" style="color:#303b97">Reactivate →</a>
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
         6. INVENTORY MIX
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-7">

        <div class="card overflow-hidden">
            <div class="card-hd">
                <div><p class="eyebrow mb-1">Inventory</p><h3 class="card-ttl">Listing status</h3></div>
            </div>
            <div class="p-4"><div id="statusChart" style="height:290px"></div></div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-hd">
                <div><p class="eyebrow mb-1">Coverage</p><h3 class="card-ttl">Listings by city</h3></div>
            </div>
            @if(count($topCities))
                <div class="p-4"><div id="cityChart" style="height:290px"></div></div>
            @else
                <div class="empty" style="padding-top:90px"><i class="fas fa-map-location-dot"></i>No city data recorded on listings yet.</div>
            @endif
        </div>

        {{-- Viewings booked for today --}}
        <div class="card overflow-hidden">
            <div class="card-hd">
                <div><p class="eyebrow mb-1">{{ now()->format('D d M') }}</p><h3 class="card-ttl">Viewings today</h3></div>
                @if($link('admin.appointments.index'))
                    <a href="{{ $link('admin.appointments.index') }}" class="ghost-btn">All</a>
                @endif
            </div>
            <div class="p-3 max-h-[300px] overflow-y-auto custom-scrollbar">
                @forelse($todayAppointments as $a)
                    @php $apptUrl = $link('admin.appointments.show', $a['id']); @endphp
                    <a href="{{ $apptUrl ?? '#' }}" class="lb-row">
                        <span class="text-[12px] font-black num w-11" style="color:#303b97">{{ $a['time'] }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-bold text-slate-900 truncate">{{ $a['property'] }}</p>
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wide truncate">with {{ $a['user'] }}</p>
                        </div>
                        <span class="text-[9.5px] font-black px-2 py-1 rounded-lg
                            {{ $a['status'] === 'confirmed' ? 'bg-emerald-50 text-emerald-700' : ($a['status'] === 'cancelled' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                            {{ strtoupper($a['status']) }}
                        </span>
                    </a>
                @empty
                    <div class="empty"><i class="far fa-calendar"></i>No viewings booked for today.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         7. LEADERBOARD + ACTIVITY + NEW USERS
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Leaderboard --}}
        <div class="card overflow-hidden">
            <div class="card-hd">
                <div><p class="eyebrow mb-1">Most listings</p><h3 class="card-ttl">Leaderboard</h3></div>
                <div class="tabs" id="lbTabs">
                    <button class="on" data-lb="agents">Agents</button>
                    <button data-lb="offices">Offices</button>
                </div>
            </div>

            <div class="p-3" id="lb-agents">
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
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wide truncate">{{ $a['sub'] }}</p>
                        </div>
                        <div class="text-right">
                            <span class="block text-[14px] font-black text-slate-900 num">{{ $a['total'] }}</span>
                            @if($a['weekly'] > 0)
                                <span class="text-[9.5px] font-black text-emerald-600">+{{ $a['weekly'] }} this week</span>
                            @else
                                <span class="text-[9.5px] font-bold text-slate-300 uppercase">listings</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="empty"><i class="fas fa-user-tie"></i>No agents yet.</div>
                @endforelse
            </div>

            <div class="p-3 hidden" id="lb-offices">
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
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wide truncate">{{ $o['sub'] }}</p>
                        </div>
                        <div class="text-right">
                            <span class="block text-[14px] font-black text-slate-900 num">{{ $o['total'] }}</span>
                            @if($o['weekly'] > 0)
                                <span class="text-[9.5px] font-black text-emerald-600">+{{ $o['weekly'] }} this week</span>
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

        {{-- Activity --}}
        <div class="card overflow-hidden">
            <div class="card-hd">
                <div><p class="eyebrow mb-1">Live</p><h3 class="card-ttl">Recent activity</h3></div>
            </div>
            <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar">
                @php
                    $tones = [
                        'blue'   => ['#eff6ff', '#2563eb'],
                        'indigo' => ['#f2f3fb', '#303b97'],
                        'amber'  => ['#fffbeb', '#d97706'],
                        'violet' => ['#f5f3ff', '#7c3aed'],
                    ];
                @endphp
                @forelse($activity as $ev)
                    @php [$bg, $fg] = $tones[$ev['tone']] ?? $tones['indigo']; @endphp
                    <div class="tl">
                        <div class="tl-item">
                            <i class="fas {{ $ev['icon'] }}" style="background:{{ $bg }};color:{{ $fg }}"></i>
                            <p class="text-[12.5px] text-slate-700 leading-snug">
                                <span class="font-bold text-slate-900">{{ \Illuminate\Support\Str::limit($ev['title'], 34) }}</span>
                                {{ $ev['text'] }}
                            </p>
                            <p class="text-[10.5px] font-bold text-slate-400 mt-0.5">{{ $ev['ago'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="empty"><i class="fas fa-wave-square"></i>No activity recorded yet.</div>
                @endforelse
            </div>
        </div>

        {{-- New users --}}
        <div class="card overflow-hidden">
            <div class="card-hd">
                <div><p class="eyebrow mb-1">Signups</p><h3 class="card-ttl">Newest users</h3></div>
                @if($link('admin.users.index'))
                    <a href="{{ $link('admin.users.index') }}" class="ghost-btn">All</a>
                @endif
            </div>
            <div class="p-3 max-h-[400px] overflow-y-auto custom-scrollbar">
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
    const series = @json($charts);
    const BRAND = '#303b97';

    const money = v => new Intl.NumberFormat('en-US').format(Math.round(v));

    /* ---------- KPI sparklines ---------- */
    function spark(el, data, color) {
        const node = document.querySelector(el);
        if (!node || typeof ApexCharts === 'undefined') return;
        new ApexCharts(node, {
            chart: { type: 'area', height: 64, sparkline: { enabled: true }, animations: { enabled: false } },
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

    /* ---------- Main growth chart ---------- */
    let metric = 'users';
    let range  = 12;

    const META = {
        users:      { name: 'New users',    color: BRAND,     fmt: v => money(v) },
        properties: { name: 'New listings', color: '#8b5cf6', fmt: v => money(v) },
        revenue:    { name: 'Revenue',      color: '#10b981', fmt: v => money(v) + ' IQD' }
    };

    let growth = null;

    function growthOptions() {
        const cut  = -range;
        const data = series[metric].slice(cut);
        const cats = series.labels.slice(cut);
        const meta = META[metric];

        return {
            series: [{ name: meta.name, data: data }],
            chart: { type: 'area', height: 322, fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false } },
            colors: [meta.color],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .28, opacityTo: 0, stops: [0, 100] } },
            markers: { size: 0, hover: { size: 6 } },
            xaxis: {
                categories: cats,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 700 } }
            },
            yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 700 }, formatter: v => money(v) } },
            grid: { borderColor: '#f1f2f7', strokeDashArray: 5, padding: { left: 12, right: 12 } },
            tooltip: { y: { formatter: meta.fmt } }
        };
    }

    const growthEl = document.querySelector('#growthChart');
    if (growthEl && typeof ApexCharts !== 'undefined') {
        growth = new ApexCharts(growthEl, growthOptions());
        growth.render();
    }

    function redrawGrowth() { if (growth) growth.updateOptions(growthOptions(), true, true); }

    document.querySelectorAll('#metricSeg button').forEach(function (b) {
        b.addEventListener('click', function () {
            document.querySelectorAll('#metricSeg button').forEach(x => x.classList.remove('on'));
            b.classList.add('on');
            metric = b.dataset.metric;
            redrawGrowth();
        });
    });

    document.querySelectorAll('#rangeSeg button').forEach(function (b) {
        b.addEventListener('click', function () {
            document.querySelectorAll('#rangeSeg button').forEach(x => x.classList.remove('on'));
            b.classList.add('on');
            range = parseInt(b.dataset.range, 10);
            redrawGrowth();
        });
    });

    /* ---------- Listing status donut ---------- */
    const statusData = @json($statusCounts);
    const statusEl   = document.querySelector('#statusChart');

    if (statusEl && typeof ApexCharts !== 'undefined') {
        const labels = Object.keys(statusData);
        const values = Object.values(statusData).map(Number);
        const palette = { available: '#22c55e', pending: '#f59e0b', sold: BRAND, rented: '#8b5cf6', suspended: '#94a3b8', rejected: '#ef4444' };

        new ApexCharts(statusEl, {
            series: values.length ? values : [1],
            labels: labels.length ? labels.map(l => l.charAt(0).toUpperCase() + l.slice(1)) : ['No data'],
            chart: { type: 'donut', height: 290, fontFamily: 'inherit' },
            colors: labels.length ? labels.map(l => palette[l] || '#cbd5e1') : ['#e2e8f0'],
            stroke: { width: 3, colors: ['#fff'] },
            legend: { position: 'bottom', fontSize: '12px', fontWeight: 700, labels: { colors: '#64748b' }, markers: { radius: 12 } },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '73%',
                        labels: {
                            show: true,
                            name:  { fontSize: '11px', fontWeight: 800, color: '#94a3b8' },
                            value: { fontSize: '26px', fontWeight: 900, color: '#0f172a', formatter: v => money(v) },
                            total: { show: true, label: 'Total listings', fontSize: '11px', fontWeight: 800, color: '#94a3b8',
                                     formatter: w => money(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) }
                        }
                    }
                }
            }
        }).render();
    }

    /* ---------- Cities bar ---------- */
    const cities   = @json($topCities);
    const cityEl   = document.querySelector('#cityChart');

    if (cityEl && cities.length && typeof ApexCharts !== 'undefined') {
        new ApexCharts(cityEl, {
            series: [{ name: 'Listings', data: cities.map(c => c.total) }],
            chart: { type: 'bar', height: 290, fontFamily: 'inherit', toolbar: { show: false } },
            colors: [BRAND],
            plotOptions: { bar: { horizontal: true, borderRadius: 7, barHeight: '62%', distributed: false } },
            dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 800, colors: ['#fff'] }, offsetX: -6 },
            xaxis: {
                categories: cities.map(c => c.city),
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 700 } }
            },
            yaxis: { labels: { style: { colors: '#475569', fontSize: '11.5px', fontWeight: 800 } } },
            grid: { borderColor: '#f1f2f7', strokeDashArray: 5 }
        }).render();
    }

    /* ---------- Tab switchers ---------- */
    function wireTabs(container, attr, prefix) {
        document.querySelectorAll(container + ' button').forEach(function (b) {
            b.addEventListener('click', function () {
                document.querySelectorAll(container + ' button').forEach(x => x.classList.remove('on'));
                b.classList.add('on');
                const key = b.dataset[attr];
                document.querySelectorAll('[id^="' + prefix + '"]').forEach(p => p.classList.add('hidden'));
                const panel = document.getElementById(prefix + key);
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
