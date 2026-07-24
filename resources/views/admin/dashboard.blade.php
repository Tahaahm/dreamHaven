@extends('layouts.admin-layout')

@section('title', 'Executive Dashboard')

@section('content')

<div class="max-w-[1600px] mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 border-b border-slate-200 dark:border-slate-700 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-1">Executive Overview</h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium">
                {{ now()->format('l, F j, Y') }} • <span class="text-slate-900 dark:text-slate-200 font-bold">Dream Mulk Admin Portal</span>
            </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Command palette trigger --}}
            <button onclick="openPalette()" class="hidden md:flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-500 dark:text-slate-400 hover:border-slate-300 transition">
                <i class="fas fa-search"></i> Quick jump
                <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-[10px] font-mono">⌘K</kbd>
            </button>

            {{-- Action Center --}}
            <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="flex -space-x-2">
                    @if($pendingApprovals['properties'] > 0)
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse border-2 border-white dark:border-slate-800"></span>
                    @endif
                    @if($pendingApprovals['agents'] > 0)
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 border-2 border-white dark:border-slate-800"></span>
                    @endif
                </div>
                <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                    {{ array_sum($pendingApprovals) }} Pending Actions
                </span>
            </div>

            <a href="{{ route('admin.properties.index', ['status' => 'pending']) }}" class="bg-black dark:bg-white hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-black px-5 py-2.5 text-sm font-bold rounded-xl shadow-lg shadow-slate-200 dark:shadow-none transition-all flex items-center gap-2">
                <i class="fas fa-check-double"></i> Review Pending
            </a>
        </div>
    </div>

    {{-- 2. KEY METRICS (with MoM delta badges) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">

        {{-- Subscription Revenue --}}
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl group-hover:bg-emerald-50 transition-colors">
                    <i class="fas fa-wallet text-xl text-slate-900 dark:text-white group-hover:text-emerald-600"></i>
                </div>
                {{-- @include('admin.partials.delta-badge', ['pct' => $stats['revenue_delta_pct'] ?? null]) --}}
            </div>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white mb-1">{{ number_format($stats['subscription_revenue_iqd']) }}</h3>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Total Revenue (IQD)</p>

            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Active Subscriptions</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $stats['active_subscriptions'] }}</span>
                </div>
            </div>
        </div>

        {{-- Users --}}
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl group-hover:bg-blue-50 transition-colors">
                    <i class="fas fa-users text-xl text-slate-900 dark:text-white group-hover:text-blue-600"></i>
                </div>
                @include('admin.partials.delta-badge', ['pct' => $stats['users_delta_pct'] ?? null])
            </div>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white mb-1">{{ number_format($stats['total_users']) }}</h3>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Registered Users</p>
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">+{{ $stats['new_users_today'] }} today</span>
            </div>
        </div>

        {{-- Properties --}}
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl group-hover:bg-indigo-50 transition-colors">
                    <i class="fas fa-city text-xl text-slate-900 dark:text-white group-hover:text-indigo-600"></i>
                </div>
                @include('admin.partials.delta-badge', ['pct' => $stats['properties_delta_pct'] ?? null])
            </div>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white mb-1">{{ number_format($stats['total_properties']) }}</h3>
            <div class="flex gap-3 text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                <span><b class="text-slate-900 dark:text-white">{{ $stats['properties_for_sale'] }}</b> Sale</span>
                <span class="text-slate-300 dark:text-slate-600">|</span>
                <span><b class="text-slate-900 dark:text-white">{{ $stats['properties_for_rent'] }}</b> Rent</span>
            </div>
        </div>

        {{-- Pending Actions --}}
        <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-slate-800 rounded-full blur-3xl -mr-10 -mt-10 opacity-50"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-4">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Needs Attention</p>
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                    </span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm text-slate-300 border-b border-slate-800 pb-2">
                        <span>Properties Pending</span>
                        <span class="font-bold text-white bg-slate-800 px-2 py-0.5 rounded">{{ $pendingApprovals['properties'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm text-slate-300">
                        <span>Agents Pending</span>
                        <span class="font-bold text-white bg-slate-800 px-2 py-0.5 rounded">{{ $pendingApprovals['agents'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. SUBSCRIPTION REVENUE BREAKDOWN --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950/40 dark:to-indigo-950/40 p-6 rounded-2xl border border-blue-200 dark:border-blue-900">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white dark:bg-slate-800 rounded-lg shadow-sm">
                    <i class="fas fa-user-tie text-blue-600"></i>
                </div>
                <span class="text-xs font-bold text-blue-600 uppercase">Agents</span>
            </div>
            <h4 class="text-2xl font-black text-slate-900 dark:text-white mb-1">{{ number_format($stats['agent_subscription_revenue']) }}</h4>
            <p class="text-xs text-slate-600 dark:text-slate-400 font-medium mb-3">IQD from {{ $stats['agent_subscriptions_count'] }} subscriptions</p>
            <div class="h-2 bg-blue-200 dark:bg-blue-900 rounded-full overflow-hidden">
                <div class="h-full bg-blue-600 rounded-full"
                     style="width: {{ $stats['subscription_revenue_iqd'] > 0 ? round(($stats['agent_subscription_revenue'] / $stats['subscription_revenue_iqd']) * 100) : 0 }}%"></div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-950/40 dark:to-pink-950/40 p-6 rounded-2xl border border-purple-200 dark:border-purple-900">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white dark:bg-slate-800 rounded-lg shadow-sm">
                    <i class="fas fa-building text-purple-600"></i>
                </div>
                <span class="text-xs font-bold text-purple-600 uppercase">Offices</span>
            </div>
            <h4 class="text-2xl font-black text-slate-900 dark:text-white mb-1">{{ number_format($stats['office_subscription_revenue']) }}</h4>
            <p class="text-xs text-slate-600 dark:text-slate-400 font-medium mb-3">IQD from {{ $stats['office_subscriptions_count'] }} subscriptions</p>
            <div class="h-2 bg-purple-200 dark:bg-purple-900 rounded-full overflow-hidden">
                <div class="h-full bg-purple-600 rounded-full"
                     style="width: {{ $stats['subscription_revenue_iqd'] > 0 ? round(($stats['office_subscription_revenue'] / $stats['subscription_revenue_iqd']) * 100) : 0 }}%"></div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/40 p-6 rounded-2xl border border-emerald-200 dark:border-emerald-900">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white dark:bg-slate-800 rounded-lg shadow-sm">
                    <i class="fas fa-calendar-check text-emerald-600"></i>
                </div>
                <span class="text-xs font-bold text-emerald-600 uppercase">This Month</span>
            </div>
            <h4 class="text-2xl font-black text-slate-900 dark:text-white mb-1">{{ number_format($stats['this_month_revenue']) }}</h4>
            <p class="text-xs text-slate-600 dark:text-slate-400 font-medium mb-3">IQD from new subscriptions</p>
            <div class="flex items-center gap-2 text-xs">
                <span class="flex items-center gap-1 text-emerald-600 font-bold">
                    <i class="fas fa-arrow-up text-[10px]"></i>
                    {{ $stats['new_subscriptions_this_month'] }} new
                </span>
            </div>
        </div>
    </div>

    {{-- 4. NEW: FUNNEL + CITY DISTRIBUTION + QUICK ACTIONS --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-10">

        {{-- Conversion Funnel --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
            <h3 class="text-sm font-black text-slate-900 dark:text-white mb-1">Conversion Funnel</h3>
            <p class="text-xs text-slate-400 mb-5">View → Contact → Appointment → Deal</p>
            @php
                $funnelSteps = [
                    ['label' => 'Property Views', 'value' => $funnel['views'], 'color' => 'bg-slate-900 dark:bg-white'],
                    ['label' => 'WhatsApp Contacts', 'value' => $funnel['whatsapp'], 'color' => 'bg-indigo-500'],
                    ['label' => 'Appointments', 'value' => $funnel['appointments'], 'color' => 'bg-amber-500'],
                    ['label' => 'Transactions', 'value' => $funnel['transactions'], 'color' => 'bg-emerald-500'],
                ];
                $funnelMax = max(1, $funnel['views']);
            @endphp
            <div class="space-y-4">
                @foreach($funnelSteps as $step)
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-500 dark:text-slate-400">{{ $step['label'] }}</span>
                            <span class="text-slate-900 dark:text-white">{{ number_format($step['value']) }}</span>
                        </div>
                        <div class="h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full {{ $step['color'] }} rounded-full transition-all duration-700"
                                 style="width: {{ $funnelMax > 0 ? max(3, round(($step['value'] / $funnelMax) * 100)) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- City Distribution --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
            <h3 class="text-sm font-black text-slate-900 dark:text-white mb-1">Listings by City</h3>
            <p class="text-xs text-slate-400 mb-5">Top {{ $cityDistribution->count() }} markets</p>
            @php $cityMax = $cityDistribution->max('total') ?: 1; @endphp
            <div class="space-y-3">
                @forelse($cityDistribution as $city)
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-600 dark:text-slate-300">{{ $city->city }}</span>
                            <span class="text-slate-900 dark:text-white">{{ $city->total }}</span>
                        </div>
                        <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ round(($city->total / $cityMax) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-slate-400 text-sm py-6">No city data yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-slate-900 rounded-3xl border border-slate-800 shadow-sm p-6">
            <h3 class="text-sm font-black text-white mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('admin.properties.create') }}" class="flex flex-col items-center justify-center gap-2 p-4 bg-slate-800 hover:bg-slate-700 rounded-2xl transition text-center">
                    <i class="fas fa-plus text-indigo-400"></i>
                    <span class="text-[11px] font-bold text-slate-300">New Property</span>
                </a>
                <a href="{{ route('admin.banners.create') }}" class="flex flex-col items-center justify-center gap-2 p-4 bg-slate-800 hover:bg-slate-700 rounded-2xl transition text-center">
                    <i class="fas fa-rectangle-ad text-amber-400"></i>
                    <span class="text-[11px] font-bold text-slate-300">New Banner</span>
                </a>
                <a href="{{ url('admin/notifications/broadcast') }}" class="flex flex-col items-center justify-center gap-2 p-4 bg-slate-800 hover:bg-slate-700 rounded-2xl transition text-center">
                    <i class="fas fa-bullhorn text-rose-400"></i>
                    <span class="text-[11px] font-bold text-slate-300">Broadcast</span>
                </a>
                <a href="{{ route('admin.subscription-plans.index') }}" class="flex flex-col items-center justify-center gap-2 p-4 bg-slate-800 hover:bg-slate-700 rounded-2xl transition text-center">
                    <i class="fas fa-tags text-emerald-400"></i>
                    <span class="text-[11px] font-bold text-slate-300">Manage Plans</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 5. CHARTS: USER GROWTH + REVENUE TREND --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-10">
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-black text-slate-900 dark:text-white">User Growth</h3>
                <div class="text-xs font-bold text-slate-400 bg-slate-50 dark:bg-slate-700 px-3 py-1 rounded-lg">Last 12 Months</div>
            </div>
            <div id="userChart" class="w-full h-[280px]"></div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Revenue Trend</h3>
                <div class="text-xs font-bold text-slate-400 bg-slate-50 dark:bg-slate-700 px-3 py-1 rounded-lg">Agent vs Office · 6 Months</div>
            </div>
            <div id="revenueChart" class="w-full h-[280px]"></div>
        </div>
    </div>

    {{-- 6. EXPIRING SOON + LIVE ACTIVITY --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-10">

        {{-- Expiring Subscriptions --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Expiring Soon</h3>
                <span class="text-xs font-bold text-rose-500 bg-rose-50 dark:bg-rose-950/40 px-2 py-1 rounded-lg">Next 7 days</span>
            </div>
            <div class="divide-y divide-slate-50 dark:divide-slate-700">
                @forelse($expiringSubscriptions as $sub)
                    <a href="{{ $sub['route'] }}" class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 overflow-hidden shrink-0 flex items-center justify-center text-xs font-bold text-slate-500">
                            @if($sub['image'])
                                <img src="{{ asset($sub['image']) }}" class="w-full h-full object-cover">
                            @else
                                {{ substr($sub['name'] ?? 'A', 0, 1) }}
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $sub['name'] }}</p>
                            <p class="text-[11px] text-slate-400 font-bold uppercase">{{ $sub['type'] }} · ends {{ $sub['ends'] }}</p>
                        </div>
                        <span class="text-xs font-black px-2 py-1 rounded-lg {{ $sub['urgent'] ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                            {{ $sub['days'] == 0 ? 'Today' : ($sub['days'] == 1 ? 'Tomorrow' : $sub['days'] . 'd') }}
                        </span>
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-400 text-sm font-medium">Nothing expiring this week.</div>
                @endforelse
            </div>
        </div>

        {{-- Live Activity Feed --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Live Activity</h3>
            </div>
            <div class="divide-y divide-slate-50 dark:divide-slate-700 max-h-[360px] overflow-y-auto">
                @forelse($activity as $event)
                    <a href="{{ $event['route'] }}" class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition flex items-center gap-4">
                        <div class="w-9 h-9 rounded-full bg-{{ $event['color'] }}-50 dark:bg-{{ $event['color'] }}-950/40 text-{{ $event['color'] }}-600 flex items-center justify-center shrink-0">
                            <i class="fas {{ $event['icon'] }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $event['title'] }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ $event['subtitle'] }}</p>
                        </div>
                        <span class="text-[10px] text-slate-400 font-bold whitespace-nowrap">{{ \Carbon\Carbon::parse($event['time'])->diffForHumans(null, true) }}</span>
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-400 text-sm font-medium">No recent activity.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 7. CONTENT GRID: RECENT PROPERTIES / TOP AGENTS / NEW USERS --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        {{-- LEFT COLUMN --}}
        <div class="xl:col-span-2 space-y-8">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Newest Properties</h3>
                    <a href="{{ route('admin.properties.index') }}" class="text-xs font-bold text-slate-500 hover:text-black dark:hover:text-white uppercase tracking-wide transition">View All</a>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-slate-700">
                    @forelse($recent_properties as $property)
                        @php
                            $rawName = $property->name;
                            $pName = is_array($rawName) ? ($rawName['en'] ?? 'Property') : (json_decode($rawName)->en ?? $property->name);

                            $rawPrice = $property->price;
                            $pPrice = 0;
                            if (is_array($rawPrice)) {
                                $pPrice = $rawPrice['usd'] ?? $rawPrice['amount'] ?? 0;
                            } elseif (is_string($rawPrice)) {
                                $decoded = json_decode($rawPrice, true);
                                if (is_array($decoded)) {
                                    $pPrice = $decoded['usd'] ?? $decoded['amount'] ?? 0;
                                } elseif (is_numeric($rawPrice)) {
                                    $pPrice = $rawPrice;
                                }
                            } elseif (is_numeric($rawPrice)) {
                                $pPrice = $rawPrice;
                            }

                            $rawImg = $property->images;
                            $pThumb = null;
                            if (is_array($rawImg) && !empty($rawImg)) {
                                $pThumb = $rawImg[0];
                            } elseif (is_string($rawImg)) {
                                $decodedImg = json_decode($rawImg, true);
                                if (is_array($decodedImg) && !empty($decodedImg)) {
                                    $pThumb = $decodedImg[0];
                                }
                            }
                        @endphp

                        <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition group flex items-center gap-4 cursor-pointer" onclick="window.location='{{ route('admin.properties.show', $property->id) }}'">
                            <div class="w-16 h-16 rounded-xl bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 overflow-hidden shrink-0 relative">
                                @if($pThumb)
                                    <img src="{{ asset($pThumb) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <i class="fas fa-image text-lg"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-1">
                                    <h4 class="font-bold text-slate-900 dark:text-white truncate pr-4 text-sm group-hover:text-indigo-600 transition">{{ $pName }}</h4>
                                    <span class="font-black text-slate-900 dark:text-white text-sm whitespace-nowrap">
                                        ${{ number_format($pPrice) }} <span class="text-[10px] text-slate-400 font-bold">USD</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase border {{ ($property->listing_type == 'sale' || $property->listing_type == 'sell') ? 'bg-black text-white border-black' : 'bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600' }}">
                                        {{ ucfirst($property->listing_type) }}
                                    </span>
                                    <span class="text-xs text-slate-400 flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt"></i> {{ $property->city ?? 'Location N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-sm font-medium">No recent properties found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-8">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="font-black text-slate-900 dark:text-white text-lg">Top Agents</h3>
                    <a href="{{ route('admin.agents.index') }}" class="text-xs font-bold text-slate-500 hover:text-black dark:hover:text-white transition uppercase tracking-wide">Manage</a>
                </div>
                <div class="p-2">
                    @forelse($top_agents as $index => $agent)
                    <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                        <div class="font-black text-slate-300 dark:text-slate-600 text-lg w-5 text-center">{{ $index + 1 }}</div>
                        <div class="w-10 h-10 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 flex items-center justify-center font-bold text-sm shadow-md">
                            {{ substr($agent->agent_name ?? 'A', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $agent->agent_name }}</h4>
                            <p class="text-[11px] text-slate-400 font-bold uppercase truncate">{{ $agent->company_name ?? 'Independent' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="block font-black text-slate-900 dark:text-white">{{ $agent->properties_count }}</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Listings</span>
                        </div>
                    </div>
                    @empty
                        <p class="text-center text-slate-400 py-4 text-sm">No agent data available.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="font-black text-slate-900 dark:text-white text-lg">New Users</h3>
                </div>
                <div>
                    @forelse($recent_users as $user)
                    <div class="flex items-center gap-3 p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition border-b border-slate-50 dark:border-slate-700 last:border-0">
                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center text-xs font-bold text-slate-500">
                            {{ substr($user->username ?? 'U', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $user->username }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ $user->created_at->format('M d') }}</p>
                        </div>
                        <div>
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ ($user->is_active || $user->is_verified) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300' }}">
                                {{ ($user->is_active || $user->is_verified) ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    @empty
                         <p class="text-center text-slate-400 py-6 text-sm">No new users.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const isDark = document.documentElement.classList.contains('dark');
    const labelColor = isDark ? '#94a3b8' : '#94a3b8';
    const gridColor = isDark ? '#1e293b' : '#f1f5f9';

    // User Growth Chart
    new ApexCharts(document.querySelector("#userChart"), {
        series: [{ name: 'Users', data: @json($monthlyData) }],
        chart: { type: 'area', height: 280, fontFamily: 'Inter, sans-serif', toolbar: { show: false }, zoom: { enabled: false } },
        colors: [isDark ? '#ffffff' : '#0f172a'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.15, opacityTo: 0.0, stops: [0, 100] } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: {
            categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
            axisBorder: { show: false }, axisTicks: { show: false },
            labels: { style: { colors: labelColor, fontSize: '11px', fontWeight: 600 } }
        },
        yaxis: { labels: { style: { colors: labelColor, fontSize: '11px', fontWeight: 600 } } },
        grid: { borderColor: gridColor, strokeDashArray: 4, padding: { top: 0, right: 0, bottom: 0, left: 10 } }
    }).render();

    // Revenue Trend Chart (Agent vs Office)
    const revenueTrend = @json($revenueTrend);
    new ApexCharts(document.querySelector("#revenueChart"), {
        series: [
            { name: 'Agents', data: revenueTrend.map(r => r.agent) },
            { name: 'Offices', data: revenueTrend.map(r => r.office) },
        ],
        chart: { type: 'bar', height: 280, fontFamily: 'Inter, sans-serif', toolbar: { show: false }, stacked: true },
        colors: ['#3b82f6', '#a855f7'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
        dataLabels: { enabled: false },
        xaxis: {
            categories: revenueTrend.map(r => r.label),
            axisBorder: { show: false }, axisTicks: { show: false },
            labels: { style: { colors: labelColor, fontSize: '11px', fontWeight: 600 } }
        },
        yaxis: { labels: { style: { colors: labelColor, fontSize: '11px', fontWeight: 600 } } },
        legend: { position: 'top', horizontalAlign: 'right', labels: { colors: labelColor } },
        grid: { borderColor: gridColor, strokeDashArray: 4 }
    }).render();
</script>
@endpush

@endsection
