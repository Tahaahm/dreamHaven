@extends('layouts.admin-layout')

@section('title', 'Executive Dashboard')

@section('content')

<div class="max-w-[1600px] mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-1">Executive Overview</h1>
            <p class="text-slate-500 font-medium">
                {{ now()->format('l, F j, Y') }} • <span class="text-slate-900 font-bold">Real Estate Admin Portal</span>
            </p>
        </div>
        <div class="flex items-center gap-3">
             {{-- Action Center --}}
             <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 rounded-xl border border-slate-200">
                <div class="flex -space-x-2">
                    @if($pendingApprovals['properties'] > 0)
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse border-2 border-white"></span>
                    @endif
                    @if($pendingApprovals['agents'] > 0)
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 border-2 border-white"></span>
                    @endif
                </div>
                <span class="text-xs font-bold text-slate-600">
                    {{ array_sum($pendingApprovals) }} Pending Actions
                </span>
             </div>
            <a href="{{ route('admin.properties.index', ['status' => 'pending']) }}" class="bg-black hover:bg-slate-800 text-white px-5 py-2.5 text-sm font-bold rounded-xl shadow-lg shadow-slate-200 transition-all flex items-center gap-2">
                <i class="fas fa-check-double"></i> Review Pending
            </a>
        </div>
    </div>

    {{-- 2. KEY METRICS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">

        {{-- ✅ FIXED: Subscription Revenue in IQD --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-slate-50 rounded-xl group-hover:bg-emerald-50 transition-colors">
                    <i class="fas fa-wallet text-xl text-slate-900 group-hover:text-emerald-600"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">Subscriptions</span>
            </div>
            <h3 class="text-3xl font-black text-slate-900 mb-1">{{ number_format($stats['subscription_revenue_iqd']) }}</h3>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Total Revenue (IQD)</p>

            {{-- ✅ Show Active Subscriptions Count --}}
            <div class="mt-3 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Active Subscriptions</span>
                    <span class="font-bold text-slate-900">{{ $stats['active_subscriptions'] }}</span>
                </div>
            </div>
        </div>

        {{-- Users --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-slate-50 rounded-xl group-hover:bg-blue-50 transition-colors">
                    <i class="fas fa-users text-xl text-slate-900 group-hover:text-blue-600"></i>
                </div>
                <div class="text-right">
                    <span class="block text-lg font-black text-slate-900">+{{ $stats['new_users_today'] }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Today</span>
                </div>
            </div>
            <h3 class="text-3xl font-black text-slate-900 mb-1">{{ number_format($stats['total_users']) }}</h3>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Registered Users</p>
        </div>

        {{-- Properties --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-slate-50 rounded-xl group-hover:bg-indigo-50 transition-colors">
                    <i class="fas fa-city text-xl text-slate-900 group-hover:text-indigo-600"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 bg-slate-100 px-2 py-1 rounded-lg">Inventory</span>
            </div>
            <h3 class="text-3xl font-black text-slate-900 mb-1">{{ number_format($stats['total_properties']) }}</h3>
            <div class="flex gap-3 text-xs font-medium text-slate-500 mt-1">
                <span><b class="text-slate-900">{{ $stats['properties_for_sale'] }}</b> Sale</span>
                <span class="text-slate-300">|</span>
                <span><b class="text-slate-900">{{ $stats['properties_for_rent'] }}</b> Rent</span>
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

    {{-- ✅ NEW: Subscription Revenue Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        {{-- Agent Subscriptions --}}
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-200">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white rounded-lg shadow-sm">
                    <i class="fas fa-user-tie text-blue-600"></i>
                </div>
                <span class="text-xs font-bold text-blue-600 uppercase">Agents</span>
            </div>
            <h4 class="text-2xl font-black text-slate-900 mb-1">{{ number_format($stats['agent_subscription_revenue']) }}</h4>
            <p class="text-xs text-slate-600 font-medium mb-3">IQD from {{ $stats['agent_subscriptions_count'] }} subscriptions</p>
            <div class="h-2 bg-blue-200 rounded-full overflow-hidden">
                <div class="h-full bg-blue-600 rounded-full"
                     style="width: {{ $stats['subscription_revenue_iqd'] > 0 ? round(($stats['agent_subscription_revenue'] / $stats['subscription_revenue_iqd']) * 100) : 0 }}%"></div>
            </div>
        </div>

        {{-- Office Subscriptions --}}
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-2xl border border-purple-200">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white rounded-lg shadow-sm">
                    <i class="fas fa-building text-purple-600"></i>
                </div>
                <span class="text-xs font-bold text-purple-600 uppercase">Offices</span>
            </div>
            <h4 class="text-2xl font-black text-slate-900 mb-1">{{ number_format($stats['office_subscription_revenue']) }}</h4>
            <p class="text-xs text-slate-600 font-medium mb-3">IQD from {{ $stats['office_subscriptions_count'] }} subscriptions</p>
            <div class="h-2 bg-purple-200 rounded-full overflow-hidden">
                <div class="h-full bg-purple-600 rounded-full"
                     style="width: {{ $stats['subscription_revenue_iqd'] > 0 ? round(($stats['office_subscription_revenue'] / $stats['subscription_revenue_iqd']) * 100) : 0 }}%"></div>
            </div>
        </div>

        {{-- This Month Revenue --}}
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 p-6 rounded-2xl border border-emerald-200">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white rounded-lg shadow-sm">
                    <i class="fas fa-calendar-check text-emerald-600"></i>
                </div>
                <span class="text-xs font-bold text-emerald-600 uppercase">This Month</span>
            </div>
            <h4 class="text-2xl font-black text-slate-900 mb-1">{{ number_format($stats['this_month_revenue']) }}</h4>
            <p class="text-xs text-slate-600 font-medium mb-3">IQD from new subscriptions</p>
            <div class="flex items-center gap-2 text-xs">
                <span class="flex items-center gap-1 text-emerald-600 font-bold">
                    <i class="fas fa-arrow-up text-[10px]"></i>
                    {{ $stats['new_subscriptions_this_month'] }} new
                </span>
            </div>
        </div>
    </div>

    {{-- 3. CONTENT GRID --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        {{-- LEFT COLUMN: Charts & Recent Properties (2/3 Width) --}}
        <div class="xl:col-span-2 space-y-8">

            {{-- Chart Section --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-black text-slate-900">Growth Analytics</h3>
                    <div class="text-xs font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-lg">Last 12 Months</div>
                </div>
                <div id="mainChart" class="w-full h-[300px]"></div>
            </div>

            {{-- Recent Properties List --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-lg font-black text-slate-900">Newest Properties</h3>
                    <a href="{{ route('admin.properties.index') }}" class="text-xs font-bold text-slate-500 hover:text-black uppercase tracking-wide transition">View All</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($recent_properties as $property)
                        @php
                            $rawName = $property->name;
                            $pName = is_array($rawName) ? ($rawName['en'] ?? 'Property') : (json_decode($rawName)->en ?? $property->name);

                            // Price (Logic to find USD)
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

                            // Image
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

                        <div class="p-4 hover:bg-slate-50 transition group flex items-center gap-4 cursor-pointer" onclick="window.location='{{ route('admin.properties.show', $property->id) }}'">
                            {{-- Image --}}
                            <div class="w-16 h-16 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 relative">
                                @if($pThumb)
                                    <img src="{{ asset($pThumb) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <i class="fas fa-image text-lg"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-1">
                                    <h4 class="font-bold text-slate-900 truncate pr-4 text-sm group-hover:text-indigo-600 transition">{{ $pName }}</h4>

                                    <span class="font-black text-slate-900 text-sm whitespace-nowrap">
                                        ${{ number_format($pPrice) }} <span class="text-[10px] text-slate-400 font-bold">USD</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase border {{ ($property->listing_type == 'sale' || $property->listing_type == 'sell') ? 'bg-black text-white border-black' : 'bg-white text-slate-600 border-slate-200' }}">
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

        {{-- RIGHT COLUMN: Top Agents & Users (1/3 Width) --}}
        <div class="space-y-8">

            {{-- Top Agents --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-black text-slate-900 text-lg">Top Agents</h3>
                    <a href="{{ route('admin.agents.index') }}" class="text-xs font-bold text-slate-500 hover:text-black transition uppercase tracking-wide">Manage</a>
                </div>
                <div class="p-2">
                    @forelse($top_agents as $index => $agent)
                    <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 transition">
                        <div class="font-black text-slate-300 text-lg w-5 text-center">{{ $index + 1 }}</div>
                        <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm shadow-md">
                            {{ substr($agent->agent_name ?? 'A', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-slate-900 text-sm truncate">{{ $agent->agent_name }}</h4>
                            <p class="text-[11px] text-slate-400 font-bold uppercase truncate">{{ $agent->company_name ?? 'Independent' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="block font-black text-slate-900">{{ $agent->properties_count }}</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Listings</span>
                        </div>
                    </div>
                    @empty
                        <p class="text-center text-slate-400 py-4 text-sm">No agent data available.</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent Users --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-black text-slate-900 text-lg">New Users</h3>
                </div>
                <div>
                    @forelse($recent_users as $user)
                    <div class="flex items-center gap-3 p-4 hover:bg-slate-50 transition border-b border-slate-50 last:border-0">
                        <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-500">
                            {{ substr($user->username ?? 'U', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $user->username }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ $user->created_at->format('M d') }}</p>
                        </div>
                        <div>
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ ($user->is_active || $user->is_verified) ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
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
    // 1. Monochrome User Growth Chart
    const userOptions = {
        series: [{ name: 'Users', data: @json($monthlyData) }],
        chart: {
            type: 'area',
            height: 300,
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        colors: ['#0f172a'], // Slate-900 (Black)
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.1,
                opacityTo: 0.0,
                stops: [0, 100]
            }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 } }
        },
        yaxis: {
            show: true,
            labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 } }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4,
            padding: { top: 0, right: 0, bottom: 0, left: 10 }
        }
    };
    new ApexCharts(document.querySelector("#mainChart"), userOptions).render();
</script>
@endpush

@endsection
