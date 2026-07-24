<!DOCTYPE html>
@php
    /* ---------------------------------------------------------------
     | Sidebar badge counts — cached 60s so this costs almost nothing.
     | Falls back to 0 if a model/column is missing. Never throws.
     --------------------------------------------------------------- */
    $navBadges = \Illuminate\Support\Facades\Cache::remember('admin.sidebar.badges', 60, function () {
        $count = function ($callback) {
            try { return (int) $callback(); } catch (\Throwable $e) { return 0; }
        };
        return [
            'properties'   => $count(fn() => \App\Models\Property::where('status', 'pending')->count()),
            'agents'       => $count(fn() => \App\Models\Agent::where('is_verified', false)->count()),
            'offices'      => $count(fn() => \App\Models\RealEstateOffice::where('is_verified', false)->count()),
            'banners'      => $count(fn() => \App\Models\BannerAd::where('status', 'pending')->count()),
            'appointments' => $count(fn() => \App\Models\Appointment::where('status', 'pending')->count()),
            'providers'    => $count(fn() => \App\Models\ServiceProvider::where('is_verified', false)->count()),
        ];
    });

    $navTotal = array_sum($navBadges);

    /* Safe route helper */
    $navLink = function (string $name, $params = []) {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : null;
    };

    $adminUser = optional(Auth::guard('admin')->user());
    $adminName = $adminUser->username ?? $adminUser->name ?? 'Administrator';
    $adminMail = $adminUser->email ?? 'admin@dreammulk.com';

    /* Navigation model — also powers the ⌘K command palette */
    $navSections = [
        'Overview' => [
            ['label' => 'Dashboard', 'icon' => 'fa-gauge-high', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'badge' => null, 'keys' => 'home overview stats'],
        ],
        'Management' => [
            ['label' => 'Users',   'icon' => 'fa-users',    'route' => 'admin.users.index',   'active' => 'admin.users.*',   'badge' => null,                    'keys' => 'customers accounts members'],
            ['label' => 'Agents',  'icon' => 'fa-user-tie', 'route' => 'admin.agents.index',  'active' => 'admin.agents.*',  'badge' => $navBadges['agents'],    'keys' => 'brokers realtors'],
            ['label' => 'Offices', 'icon' => 'fa-building', 'route' => 'admin.offices.index', 'active' => 'admin.offices.*', 'badge' => $navBadges['offices'],   'keys' => 'companies agencies'],
        ],
        'Real estate' => [
            ['label' => 'Properties',   'icon' => 'fa-home',            'route' => 'admin.properties.index',   'active' => 'admin.properties.*',   'badge' => $navBadges['properties'],   'keys' => 'listings homes apartments'],
            ['label' => 'Projects',     'icon' => 'fa-city',            'route' => 'admin.projects.index',     'active' => 'admin.projects.*',     'badge' => null,                       'keys' => 'developments towers compounds'],
            ['label' => 'Appointments', 'icon' => 'fa-calendar-check',  'route' => 'admin.appointments.index', 'active' => 'admin.appointments.*',  'badge' => $navBadges['appointments'], 'keys' => 'viewings bookings visits'],
            ['label' => 'Banners',      'icon' => 'fa-rectangle-ad',    'route' => 'admin.banners.index',      'active' => 'admin.banners.*',      'badge' => $navBadges['banners'],      'keys' => 'ads advertising promos'],
        ],
        'Services' => [
            ['label' => 'Providers',     'icon' => 'fa-tools',           'route' => 'admin.service-providers.index',      'active' => 'admin.service-providers.*',      'badge' => $navBadges['providers'], 'keys' => 'contractors vendors services'],
            ['label' => 'Provider plans','icon' => 'fa-clipboard-list',  'route' => 'admin.service-provider-plans.index', 'active' => 'admin.service-provider-plans.*', 'badge' => null,                    'keys' => 'packages pricing vendors'],
            ['label' => 'Categories',    'icon' => 'fa-layer-group',     'route' => 'admin.categories.index',             'active' => 'admin.categories.*',             'badge' => null,                    'keys' => 'taxonomy groups types'],
        ],
        'Finance' => [
            ['label' => 'Agent plans',   'icon' => 'fa-tags',                'route' => 'admin.subscription-plans.index', 'active' => 'admin.subscription-plans.*', 'badge' => null, 'keys' => 'pricing packages tiers'],
            ['label' => 'Subscriptions', 'icon' => 'fa-credit-card',         'route' => 'admin.subscriptions.index',      'active' => 'admin.subscriptions.*',      'badge' => null, 'keys' => 'billing renewals plans'],
            ['label' => 'Transactions',  'icon' => 'fa-money-bill-transfer', 'route' => 'admin.transactions.index',       'active' => 'admin.transactions.*',       'badge' => null, 'keys' => 'payments deals sales'],
        ],
        'System' => [
            ['label' => 'Settings', 'icon' => 'fa-cog', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*', 'badge' => null, 'keys' => 'config preferences'],
        ],
    ];

    /* Extra destinations that only exist in the palette */
    $paletteExtras = [
        ['label' => 'Add a property',      'icon' => 'fa-plus',     'route' => 'admin.properties.create', 'keys' => 'new listing create'],
        ['label' => 'Add a project',       'icon' => 'fa-plus',     'route' => 'admin.projects.create',   'keys' => 'new development create'],
        ['label' => 'Add a user',          'icon' => 'fa-plus',     'route' => 'admin.users.create',      'keys' => 'new account create'],
        ['label' => 'Add a banner',        'icon' => 'fa-plus',     'route' => 'admin.banners.create',    'keys' => 'new ad create'],
        ['label' => 'Pending properties',  'icon' => 'fa-hourglass','route' => 'admin.properties.index',  'params' => ['status' => 'pending'], 'keys' => 'approve queue review'],
        ['label' => 'My profile',          'icon' => 'fa-user',     'route' => 'admin.profile',           'keys' => 'account me'],
    ];

    /* Flattened list handed to the command palette */
    $paletteItems = collect($navSections)
        ->flatMap(function ($items, $section) use ($navLink) {
            return collect($items)->map(function ($i) use ($section, $navLink) {
                return [
                    'label'   => $i['label'],
                    'section' => $section,
                    'icon'    => $i['icon'],
                    'keys'    => $i['keys'],
                    'url'     => $navLink($i['route']),
                ];
            });
        })
        ->concat(
            collect($paletteExtras)->map(function ($i) use ($navLink) {
                return [
                    'label'   => $i['label'],
                    'section' => 'Quick actions',
                    'icon'    => $i['icon'],
                    'keys'    => $i['keys'],
                    'url'     => $navLink($i['route'], $i['params'] ?? []),
                ];
            })
        )
        ->filter(function ($i) { return !empty($i['url']); })
        ->values();
@endphp
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Dream Mulk Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #303b97;
            --primary-light: #4b56b2;
            --sidebar-bg: #1a1d2e;
            --sidebar-darker: #12141f;
            --rail: 78px;
            --panel: 264px;
        }

        html, body { height: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
            background: #f6f7fb;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        .sidebar-dark { background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-darker) 100%); }

        .sidebar-link-active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            box-shadow: 0 8px 20px -8px rgba(48, 59, 151, .9);
            color: #fff !important;
        }
        .gradient-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); }

        /* ---- Sidebar ---- */
        #sidebar { width: var(--panel); transition: width .25s ease, transform .25s ease; }
        body.rail #sidebar { width: var(--rail); }
        body.rail .rail-hide { display: none !important; }
        body.rail .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
        body.rail #sidebar .nav-badge { position: absolute; top: 6px; right: 12px; }

        .nav-item {
            position: relative; display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: 13px; font-size: 13.5px; font-weight: 700;
            color: #9aa2bd; transition: .18s;
        }
        .nav-item:hover { color: #fff; background: rgba(255, 255, 255, .06); }
        .nav-item i.nav-ico { width: 20px; text-align: center; font-size: 14px; }

        .nav-badge {
            min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px;
            background: #ef4444; color: #fff; font-size: 10px; font-weight: 900;
            display: inline-grid; place-items: center;
        }
        .sidebar-link-active .nav-badge { background: rgba(255, 255, 255, .25); }

        .nav-group {
            padding: 0 16px; margin: 18px 0 7px; font-size: 9.5px; font-weight: 800;
            letter-spacing: .16em; text-transform: uppercase; color: #5b6280;
        }

        /* Tooltip in rail mode */
        body.rail .nav-item:hover::after {
            content: attr(data-label); position: absolute; left: calc(100% + 10px); top: 50%;
            transform: translateY(-50%); background: #0f172a; color: #fff; font-size: 11.5px;
            font-weight: 700; padding: 6px 10px; border-radius: 8px; white-space: nowrap; z-index: 80;
        }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd2e4; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #98a1bd; }
        #sidebar .custom-scrollbar::-webkit-scrollbar-thumb { background: #333850; }

        /* ---- Top bar ---- */
        .topbar { backdrop-filter: blur(10px); background: rgba(255, 255, 255, .86); }

        .crumb { font-size: 12px; font-weight: 700; color: #94a3b8; }
        .crumb b { color: #0f172a; font-weight: 800; }

        .icon-btn {
            width: 38px; height: 38px; border-radius: 12px; display: grid; place-items: center;
            color: #64748b; background: #f2f3f8; transition: .18s; border: 1px solid transparent;
        }
        .icon-btn:hover { color: var(--primary); background: #eceefb; border-color: #dfe2f6; }

        .fake-search {
            display: flex; align-items: center; gap: 10px; width: 100%; max-width: 380px;
            padding: 9px 13px; border-radius: 13px; background: #f2f3f8; border: 1px solid #e6e8f2;
            color: #94a3b8; font-size: 13px; font-weight: 600; transition: .18s; cursor: pointer;
        }
        .fake-search:hover { border-color: var(--primary); background: #fff; color: #475569; }
        kbd {
            font-family: inherit; font-size: 10px; font-weight: 800; padding: 2px 6px;
            border-radius: 6px; background: #e2e5f0; color: #64748b;
        }

        /* ---- Command palette ---- */
        #palette { position: fixed; inset: 0; z-index: 90; display: none; }
        #palette.open { display: block; }
        #palette .backdrop { position: absolute; inset: 0; background: rgba(15, 19, 40, .55); backdrop-filter: blur(4px); }
        #palette .panel {
            position: relative; max-width: 620px; margin: 11vh auto 0; background: #fff;
            border-radius: 20px; box-shadow: 0 40px 80px -20px rgba(15, 23, 42, .5); overflow: hidden;
            animation: pop .16s ease-out;
        }
        .pal-row {
            display: flex; align-items: center; gap: 13px; padding: 11px 20px; cursor: pointer;
            font-size: 13.5px; font-weight: 700; color: #334155;
        }
        .pal-row.sel { background: #f2f3fb; color: var(--primary); }
        .pal-row .pal-ico { width: 30px; height: 30px; border-radius: 9px; background: #f2f3f8; display: grid; place-items: center; font-size: 12px; color: #64748b; }
        .pal-row.sel .pal-ico { background: var(--primary); color: #fff; }
        .pal-sec { padding: 12px 20px 5px; font-size: 9.5px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #b0b7cc; }

        /* ---- Toasts ---- */
        #toasts { position: fixed; top: 18px; right: 18px; z-index: 95; display: flex; flex-direction: column; gap: 10px; }
        .toast {
            display: flex; gap: 12px; align-items: flex-start; min-width: 290px; max-width: 380px;
            padding: 14px 16px; border-radius: 15px; background: #fff; border: 1px solid #e8eaf2;
            box-shadow: 0 20px 40px -18px rgba(15, 23, 42, .45); animation: slide .28s ease-out;
        }
        .toast.out { animation: fade .3s forwards; }

        /* ---- Misc ---- */
        #progress { position: fixed; top: 0; left: 0; height: 3px; width: 0; z-index: 99; background: linear-gradient(90deg, var(--primary), var(--primary-light)); transition: width .2s ease; }
        #totop { position: fixed; right: 20px; bottom: 20px; z-index: 60; opacity: 0; pointer-events: none; transition: .25s; }
        #totop.show { opacity: 1; pointer-events: auto; }

        .fade-in { animation: fadeIn .28s ease-in-out; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
        @keyframes pop { from { opacity: 0; transform: translateY(-8px) scale(.98); } to { opacity: 1; transform: none; } }
        @keyframes slide { from { opacity: 0; transform: translateX(24px); } to { opacity: 1; transform: none; } }
        @keyframes fade { to { opacity: 0; transform: translateX(24px); } }

        @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
    </style>
    @stack('styles')
</head>
<body class="h-full antialiased">

<div id="progress"></div>

<div class="flex h-screen overflow-hidden">

    {{-- ══════════════ SIDEBAR ══════════════ --}}
    <aside id="sidebar" class="sidebar-dark flex flex-col shrink-0 fixed lg:static h-full z-50 -translate-x-full lg:translate-x-0 shadow-2xl">

        <div class="flex items-center gap-3 px-5 py-5 shrink-0">
            <div class="w-10 h-10 gradient-primary rounded-xl grid place-items-center shadow-lg ring-1 ring-white/10 shrink-0">
                <i class="fas fa-shield-halved text-white"></i>
            </div>
            <div class="rail-hide min-w-0">
                <h1 class="text-[15px] font-extrabold text-white leading-tight truncate">Dream Mulk</h1>
                <p class="text-[10.5px] text-gray-400 font-bold uppercase tracking-wider">Admin panel</p>
            </div>
        </div>

        <div class="px-3 mb-1 rail-hide">
            <button type="button" onclick="openPalette()"
                    class="w-full flex items-center gap-2.5 bg-white/[.06] hover:bg-white/[.11] border border-white/10 text-gray-400 hover:text-white text-[12.5px] font-bold rounded-xl px-3 py-2.5 transition">
                <i class="fas fa-magnifying-glass text-[11px]"></i>
                <span class="flex-1 text-left">Search…</span>
                <kbd class="!bg-white/10 !text-gray-400">⌘K</kbd>
            </button>
        </div>

        <nav class="flex-1 px-3 py-3 overflow-y-auto custom-scrollbar">
            @foreach($navSections as $section => $items)
                <p class="nav-group rail-hide">{{ $section }}</p>

                @foreach($items as $item)
                    @php $href = $navLink($item['route']); @endphp
                    @if($href)
                        <a href="{{ $href }}"
                           data-label="{{ $item['label'] }}"
                           class="nav-item mb-0.5 {{ request()->routeIs($item['active']) ? 'sidebar-link-active' : '' }}">
                            <i class="fas {{ $item['icon'] }} nav-ico"></i>
                            <span class="rail-hide flex-1 truncate">{{ $item['label'] }}</span>
                            @if(!empty($item['badge']) && $item['badge'] > 0)
                                <span class="nav-badge">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            @endforeach

            <p class="nav-group rail-hide">Communication</p>
            <a href="{{ url('admin/notifications/broadcast') }}" data-label="Broadcasts"
               class="nav-item mb-0.5 {{ request()->is('admin/notifications/broadcast') ? 'sidebar-link-active' : '' }}">
                <i class="fas fa-bullhorn nav-ico"></i>
                <span class="rail-hide flex-1 truncate">Broadcasts</span>
            </a>
        </nav>

        <div class="p-3 border-t border-white/5 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 gradient-primary rounded-full grid place-items-center text-white font-extrabold text-[13px] shrink-0 ring-2 ring-white/10">
                    {{ strtoupper(substr($adminName, 0, 1)) }}
                </div>
                <div class="rail-hide flex-1 min-w-0">
                    <p class="text-[13px] font-bold text-white truncate">{{ $adminName }}</p>
                    @if($navLink('admin.profile'))
                        <a href="{{ $navLink('admin.profile') }}" class="text-[10.5px] text-gray-400 hover:text-white font-bold transition">View profile</a>
                    @endif
                </div>
                <form method="POST" action="{{ route('admin.logout') }}" class="rail-hide">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-rose-400 transition p-2" title="Sign out">
                        <i class="fas fa-power-off"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div onclick="toggleSidebar()" id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden hidden"></div>

    {{-- ══════════════ MAIN ══════════════ --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <header class="topbar border-b border-slate-200/80 px-5 py-3 z-30 shrink-0">
            <div class="flex items-center justify-between gap-4">

                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <button onclick="toggleSidebar()" class="icon-btn lg:hidden"><i class="fas fa-bars"></i></button>
                    <button onclick="toggleRail()" class="icon-btn hidden lg:grid" title="Collapse menu"><i class="fas fa-bars-staggered"></i></button>

                    <div class="hidden md:block min-w-0">
                        <p class="crumb truncate">Admin <span class="mx-1.5 text-slate-300">/</span> <b>@yield('title', 'Dashboard')</b></p>
                    </div>

                    <div class="hidden xl:block flex-1 max-w-[380px] ml-4">
                        <div class="fake-search" onclick="openPalette()">
                            <i class="fas fa-magnifying-glass text-[12px]"></i>
                            <span class="flex-1">Search pages and actions…</span>
                            <kbd>Ctrl K</kbd>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ url('/') }}" target="_blank" class="icon-btn" title="Open the website"><i class="fas fa-arrow-up-right-from-square text-[13px]"></i></a>

                    {{-- Tasks bell --}}
                    <div class="relative">
                        <button onclick="toggleDropdown('tasks-menu')" class="icon-btn relative" title="Pending tasks">
                            <i class="far fa-bell"></i>
                            @if($navTotal > 0)
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[9.5px] font-black grid place-items-center border-2 border-white">
                                    {{ $navTotal > 99 ? '99+' : $navTotal }}
                                </span>
                            @endif
                        </button>

                        <div id="tasks-menu" class="hidden absolute right-0 mt-2 w-[290px] bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50">
                            <p class="px-4 pt-1 pb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Needs review</p>
                            @php
                                $bellRows = [
                                    ['Properties',   $navBadges['properties'],   'admin.properties.index',        ['status' => 'pending']],
                                    ['Agents',       $navBadges['agents'],       'admin.agents.index',            ['status' => 'pending']],
                                    ['Offices',      $navBadges['offices'],      'admin.offices.index',           ['status' => 'pending']],
                                    ['Banners',      $navBadges['banners'],      'admin.banners.index',           ['status' => 'pending']],
                                    ['Appointments', $navBadges['appointments'], 'admin.appointments.index',      ['status' => 'pending']],
                                    ['Providers',    $navBadges['providers'],    'admin.service-providers.index', []],
                                ];
                            @endphp
                            @foreach($bellRows as [$label, $count, $rname, $params])
                                @php $href = $navLink($rname, $params); @endphp
                                @if($href)
                                    <a href="{{ $href }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 transition">
                                        <span class="text-[13px] font-bold {{ $count > 0 ? 'text-slate-800' : 'text-slate-400' }}">{{ $label }}</span>
                                        <span class="text-[11px] font-black px-2 py-0.5 rounded-lg {{ $count > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-400' }}">{{ $count }}</span>
                                    </a>
                                @endif
                            @endforeach
                            @if($navTotal === 0)
                                <p class="px-4 py-4 text-[12.5px] font-semibold text-emerald-600"><i class="fas fa-check-circle mr-1.5"></i>Nothing is waiting.</p>
                            @endif
                        </div>
                    </div>

                    <button onclick="toggleShortcuts()" class="icon-btn hidden sm:grid" title="Keyboard shortcuts (press ?)">
                        <i class="fas fa-keyboard text-[13px]"></i>
                    </button>

                    {{-- Profile --}}
                    <div class="relative">
                        <button onclick="toggleDropdown('profile-menu')" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-xl hover:bg-slate-100 transition border border-transparent hover:border-slate-200">
                            <span class="w-9 h-9 gradient-primary rounded-xl grid place-items-center text-white text-[13px] font-black">
                                {{ strtoupper(substr($adminName, 0, 1)) }}
                            </span>
                            <i class="fas fa-chevron-down text-slate-400 text-[10px]"></i>
                        </button>

                        <div id="profile-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50">
                            <div class="px-4 py-2.5 border-b border-slate-100 mb-1">
                                <p class="text-[13px] font-extrabold text-slate-900 truncate">{{ $adminName }}</p>
                                <p class="text-[11px] text-slate-400 font-semibold truncate">{{ $adminMail }}</p>
                            </div>
                            @if($navLink('admin.profile'))
                                <a href="{{ $navLink('admin.profile') }}" class="block px-4 py-2.5 text-[13px] font-bold text-slate-600 hover:bg-slate-50 hover:text-[#303b97]">
                                    <i class="fas fa-user w-5"></i> Profile
                                </a>
                            @endif
                            @if($navLink('admin.settings.index'))
                                <a href="{{ $navLink('admin.settings.index') }}" class="block px-4 py-2.5 text-[13px] font-bold text-slate-600 hover:bg-slate-50 hover:text-[#303b97]">
                                    <i class="fas fa-cog w-5"></i> Settings
                                </a>
                            @endif
                            <div class="border-t border-slate-100 my-1"></div>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-[13px] font-bold text-rose-600 hover:bg-rose-50">
                                    <i class="fas fa-arrow-right-from-bracket w-5"></i> Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto custom-scrollbar p-5 md:p-7 fade-in">
            @yield('content')
        </main>
    </div>
</div>

{{-- ══════════════ TOASTS ══════════════ --}}
<div id="toasts">
    @if(session('success'))
        <div class="toast" data-life="5000">
            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center shrink-0"><i class="fas fa-check"></i></span>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-extrabold text-slate-900">Done</p>
                <p class="text-[12.5px] text-slate-500 font-semibold">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-300 hover:text-slate-500"><i class="fas fa-xmark"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="toast" data-life="9000">
            <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 grid place-items-center shrink-0"><i class="fas fa-exclamation"></i></span>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-extrabold text-slate-900">That didn't work</p>
                <p class="text-[12.5px] text-slate-500 font-semibold">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-300 hover:text-slate-500"><i class="fas fa-xmark"></i></button>
        </div>
    @endif

    @if($errors->any())
        <div class="toast" data-life="11000">
            <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 grid place-items-center shrink-0"><i class="fas fa-triangle-exclamation"></i></span>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-extrabold text-slate-900">Check {{ $errors->count() }} field{{ $errors->count() === 1 ? '' : 's' }}</p>
                <ul class="text-[12px] text-slate-500 font-semibold mt-1 space-y-0.5">
                    @foreach($errors->all() as $e)
                        <li>· {{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-300 hover:text-slate-500"><i class="fas fa-xmark"></i></button>
        </div>
    @endif
</div>

{{-- ══════════════ COMMAND PALETTE ══════════════ --}}
<div id="palette">
    <div class="backdrop" onclick="closePalette()"></div>
    <div class="panel">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100">
            <i class="fas fa-magnifying-glass text-slate-400"></i>
            <input id="pal-input" type="text" placeholder="Where do you want to go?" autocomplete="off"
                   class="flex-1 text-[15px] font-semibold outline-none placeholder-slate-300 bg-transparent">
            <kbd>Esc</kbd>
        </div>
        <div id="pal-list" class="max-h-[52vh] overflow-y-auto custom-scrollbar py-2"></div>
        <div class="px-5 py-2.5 border-t border-slate-100 flex items-center gap-4 text-[10.5px] font-bold text-slate-400">
            <span><kbd>↑</kbd><kbd class="ml-1">↓</kbd> move</span>
            <span><kbd>Enter</kbd> open</span>
            <span class="ml-auto">Dream Mulk Admin</span>
        </div>
    </div>
</div>

{{-- ══════════════ SHORTCUTS ══════════════ --}}
<div id="shortcuts" class="hidden fixed inset-0 z-[92]">
    <div class="absolute inset-0 bg-slate-900/55 backdrop-blur-sm" onclick="toggleShortcuts()"></div>
    <div class="relative max-w-md mx-auto mt-[16vh] bg-white rounded-2xl shadow-2xl p-6">
        <h3 class="text-lg font-extrabold text-slate-900 mb-4">Keyboard shortcuts</h3>
        <div class="space-y-2.5 text-[13px] font-semibold text-slate-600">
            @php
                $shortcuts = [
                    ['Ctrl K', 'Open the command palette'],
                    ['G then D', 'Go to dashboard'],
                    ['G then P', 'Go to properties'],
                    ['G then A', 'Go to agents'],
                    ['G then O', 'Go to offices'],
                    ['G then U', 'Go to users'],
                    ['[', 'Collapse or expand the menu'],
                    ['?', 'Show this list'],
                ];
            @endphp
            @foreach($shortcuts as [$key, $desc])
                <div class="flex items-center justify-between">
                    <span>{{ $desc }}</span>
                    <kbd class="!text-[11px] !px-2 !py-1">{{ $key }}</kbd>
                </div>
            @endforeach
        </div>
        <button onclick="toggleShortcuts()" class="mt-6 w-full py-2.5 rounded-xl gradient-primary text-white text-[13px] font-extrabold">Got it</button>
    </div>
</div>

<button id="totop" onclick="scrollTopMain()" class="icon-btn !w-11 !h-11 !bg-white shadow-lg border !border-slate-200" title="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
/* ============================================================
   NAVIGATION DESTINATIONS (used by the command palette)
   ============================================================ */
const PALETTE_ITEMS = @json($paletteItems);

/* ============================================================
   SIDEBAR
   ============================================================ */
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.toggle('hidden');
}

function toggleRail() {
    document.body.classList.toggle('rail');
    try { localStorage.setItem('dm_rail', document.body.classList.contains('rail') ? '1' : '0'); } catch (e) {}
}

try { if (localStorage.getItem('dm_rail') === '1') document.body.classList.add('rail'); } catch (e) {}

/* ============================================================
   DROPDOWNS
   ============================================================ */
function toggleDropdown(id) {
    const el = document.getElementById(id);
    if (!el) return;
    document.querySelectorAll('#tasks-menu, #profile-menu').forEach(m => { if (m.id !== id) m.classList.add('hidden'); });
    el.classList.toggle('hidden');
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('[onclick^="toggleDropdown"]') && !e.target.closest('#tasks-menu') && !e.target.closest('#profile-menu')) {
        document.querySelectorAll('#tasks-menu, #profile-menu').forEach(m => m.classList.add('hidden'));
    }
});

/* ============================================================
   COMMAND PALETTE
   ============================================================ */
let palIndex = 0;
let palMatches = [];

function renderPalette(query) {
    const list = document.getElementById('pal-list');
    const q = (query || '').toLowerCase().trim();

    palMatches = PALETTE_ITEMS.filter(function (item) {
        if (!q) return true;
        return (item.label + ' ' + item.section + ' ' + (item.keys || '')).toLowerCase().includes(q);
    });

    if (!palMatches.length) {
        list.innerHTML = '<p class="px-5 py-8 text-center text-[13px] font-semibold text-slate-400">Nothing matches that. Try a page name like “agents”.</p>';
        return;
    }

    palIndex = Math.min(palIndex, palMatches.length - 1);

    let html = '';
    let lastSection = null;

    palMatches.forEach(function (item, i) {
        if (item.section !== lastSection) {
            html += '<p class="pal-sec">' + item.section + '</p>';
            lastSection = item.section;
        }
        html += '<div class="pal-row ' + (i === palIndex ? 'sel' : '') + '" data-i="' + i + '">' +
                    '<span class="pal-ico"><i class="fas ' + item.icon + '"></i></span>' +
                    '<span class="flex-1">' + item.label + '</span>' +
                    (i === palIndex ? '<i class="fas fa-arrow-turn-down fa-rotate-90 text-[10px] opacity-50"></i>' : '') +
                '</div>';
    });

    list.innerHTML = html;

    list.querySelectorAll('.pal-row').forEach(function (row) {
        row.addEventListener('click', function () { go(parseInt(row.dataset.i, 10)); });
    });
}

function go(i) {
    const item = palMatches[i];
    if (item && item.url) { startProgress(); window.location = item.url; }
}

function openPalette() {
    const p = document.getElementById('palette');
    p.classList.add('open');
    palIndex = 0;
    renderPalette('');
    const input = document.getElementById('pal-input');
    input.value = '';
    setTimeout(() => input.focus(), 30);
}

function closePalette() { document.getElementById('palette').classList.remove('open'); }

document.getElementById('pal-input').addEventListener('input', function (e) {
    palIndex = 0;
    renderPalette(e.target.value);
});

/* ============================================================
   KEYBOARD
   ============================================================ */
function toggleShortcuts() { document.getElementById('shortcuts').classList.toggle('hidden'); }

const GO_TO = {
    d: @json($navLink('admin.dashboard')),
    p: @json($navLink('admin.properties.index')),
    a: @json($navLink('admin.agents.index')),
    o: @json($navLink('admin.offices.index')),
    u: @json($navLink('admin.users.index'))
};

let awaitingGo = false;

document.addEventListener('keydown', function (e) {
    const key = (e.key || '').toString();
    const active = document.activeElement || document.body;
    const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName) || active.isContentEditable;
    const paletteOpen = document.getElementById('palette').classList.contains('open');

    if ((e.ctrlKey || e.metaKey) && key.toLowerCase() === 'k') {
        e.preventDefault();
        paletteOpen ? closePalette() : openPalette();
        return;
    }

    if (paletteOpen) {
        if (key === 'Escape')    { closePalette(); return; }
        if (key === 'ArrowDown') { e.preventDefault(); palIndex = Math.min(palIndex + 1, palMatches.length - 1); renderPalette(document.getElementById('pal-input').value); return; }
        if (key === 'ArrowUp')   { e.preventDefault(); palIndex = Math.max(palIndex - 1, 0); renderPalette(document.getElementById('pal-input').value); return; }
        if (key === 'Enter')     { e.preventDefault(); go(palIndex); return; }
        return;
    }

    if (typing) return;

    if (key === '?') { e.preventDefault(); toggleShortcuts(); return; }
    if (key === '[') { e.preventDefault(); toggleRail(); return; }
    if (key === 'Escape') { document.getElementById('shortcuts').classList.add('hidden'); return; }

    if (key.toLowerCase() === 'g') { awaitingGo = true; setTimeout(() => { awaitingGo = false; }, 1200); return; }

    if (awaitingGo) {
        const target = GO_TO[key.toLowerCase()];
        awaitingGo = false;
        if (target) { startProgress(); window.location = target; }
    }
});

/* ============================================================
   TOASTS, PROGRESS, BACK TO TOP
   ============================================================ */
document.querySelectorAll('.toast').forEach(function (t) {
    const life = parseInt(t.dataset.life || '5000', 10);
    setTimeout(function () {
        t.classList.add('out');
        setTimeout(() => t.remove(), 320);
    }, life);
});

function startProgress() {
    const bar = document.getElementById('progress');
    bar.style.width = '35%';
    setTimeout(() => { bar.style.width = '72%'; }, 220);
}

document.querySelectorAll('a[href]').forEach(function (a) {
    a.addEventListener('click', function () {
        const href = a.getAttribute('href');
        if (href && !href.startsWith('#') && !a.target && !href.startsWith('javascript')) startProgress();
    });
});

window.addEventListener('load', function () {
    const bar = document.getElementById('progress');
    bar.style.width = '100%';
    setTimeout(() => { bar.style.width = '0'; }, 400);
});

const mainScroll = document.querySelector('main');
const toTop = document.getElementById('totop');

if (mainScroll) {
    mainScroll.addEventListener('scroll', function () {
        toTop.classList.toggle('show', mainScroll.scrollTop > 380);
    });
}

function scrollTopMain() {
    if (mainScroll) mainScroll.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

@stack('scripts')
</body>
</html>
