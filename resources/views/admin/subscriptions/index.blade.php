@extends('layouts.admin-layout')

@section('title', 'Subscriptions Management')

@section('content')

<div class="max-w-[1600px] mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Subscriptions</h1>
            <p class="text-slate-500 font-medium">Manage recurring revenue, plan limits, and subscriber status.</p>
        </div>
        <div class="flex items-center gap-3">
             <div class="bg-white border border-slate-200 text-slate-500 px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm flex items-center gap-2">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search subscriber..." class="bg-transparent border-none outline-none text-slate-900 placeholder-slate-400 w-32 focus:w-48 transition-all">
            </div>
            <button class="bg-black hover:bg-slate-800 text-white px-5 py-2.5 text-xs font-bold rounded-xl shadow-lg shadow-slate-200 transition-all flex items-center gap-2 uppercase tracking-wide">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        {{-- Total Active --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-lg">
                    <i class="fas fa-check-circle"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Current</span>
            </div>
            <h3 class="text-3xl font-black text-slate-900">{{ \App\Models\Subscription\Subscription::where('status', 'active')->count() }}</h3>
            <p class="text-xs text-slate-400 mt-1 font-medium">Active Subscribers</p>
        </div>

        {{-- Monthly Revenue --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-lg">
                    <i class="fas fa-wallet"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">MRR</span>
            </div>
            <h3 class="text-3xl font-black text-slate-900">
                ${{ number_format(\App\Models\Subscription\Subscription::where('status', 'active')->sum('monthly_amount')) }}
            </h3>
            <p class="text-xs text-slate-400 mt-1 font-medium">Est. Monthly Revenue</p>
        </div>

        {{-- Expiring Soon --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-lg">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alerts</span>
            </div>
            <h3 class="text-3xl font-black text-slate-900">
                {{ \App\Models\Subscription\Subscription::where('status', 'active')->whereBetween('end_date', [now(), now()->addDays(7)])->count() }}
            </h3>
            <p class="text-xs text-slate-400 mt-1 font-medium">Expiring this week</p>
        </div>

        {{-- Churned --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center text-lg">
                    <i class="fas fa-ban"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Churn</span>
            </div>
            <h3 class="text-3xl font-black text-slate-900">
                {{ \App\Models\Subscription\Subscription::where('status', 'cancelled')->count() }}
            </h3>
            <p class="text-xs text-slate-400 mt-1 font-medium">Cancelled Subscriptions</p>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Filters --}}
        <div class="px-6 py-4 border-b border-slate-100 flex gap-4 overflow-x-auto no-scrollbar">
            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' }}">
                All
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'active']) }}" class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide {{ request('status') == 'active' ? 'bg-emerald-600 text-white' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' }}">
                Active
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'cancelled']) }}" class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide {{ request('status') == 'cancelled' ? 'bg-rose-600 text-white' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' }}">
                Cancelled
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'expired']) }}" class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide {{ request('status') == 'expired' ? 'bg-amber-500 text-white' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' }}">
                Expired
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Subscriber</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Pricing</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Usage</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-right">Next Billing</th>
                        <th class="px-6 py-5 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subscriptions as $sub)
                    @php
                        // Determine Subscriber Name & Type
                        $subscriberName = 'Unknown User';
                        $subscriberType = 'N/A';
                        $subscriberRoute = '#';
                        $icon = 'fa-user';
                        $iconColor = 'bg-slate-100 text-slate-500';

                        // Check relationships (Assuming polymorphic or manual check based on plan type)
                        if ($sub->currentPlan && $sub->currentPlan->type == 'agent') {
                            $agent = \App\Models\Agent::find($sub->user_id);
                            if ($agent) {
                                $subscriberName = $agent->agent_name;
                                $subscriberType = 'Agent';
                                $subscriberRoute = route('admin.agents.show', $agent->id);
                                $icon = 'fa-user-tie';
                                $iconColor = 'bg-blue-50 text-blue-600';
                            }
                        } elseif ($sub->currentPlan && $sub->currentPlan->type == 'real_estate_office') {
                            $office = \App\Models\RealEstateOffice::find($sub->user_id);
                            if ($office) {
                                $subscriberName = $office->company_name;
                                $subscriberType = 'Office';
                                $subscriberRoute = route('admin.offices.show', $office->id);
                                $icon = 'fa-building';
                                $iconColor = 'bg-purple-50 text-purple-600';
                            }
                        }

                        // Calculate Usage Percentage
                        $limit = $sub->property_activation_limit ?? 0;
                        $used = ($limit > 0) ? ($limit - $sub->remaining_activations) : 0; // Assuming remaining decrements
                        // OR if you track 'properties_activated_this_month'
                        $used = $sub->properties_activated_this_month ?? 0;
                        $percent = ($limit > 0) ? ($used / $limit) * 100 : 0;
                        $isUnlimited = ($limit === 0 || $limit === null);
                    @endphp

                    <tr class="hover:bg-slate-50 transition-colors group">

                        {{-- Subscriber --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg {{ $iconColor }} flex items-center justify-center text-lg shrink-0">
                                    <i class="fas {{ $icon }}"></i>
                                </div>
                                <div>
                                    <a href="{{ $subscriberRoute }}" class="text-sm font-bold text-slate-900 hover:text-indigo-600 transition block">
                                        {{ Str::limit($subscriberName, 20) }}
                                    </a>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $subscriberType }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Plan --}}
                        <td class="px-6 py-4">
                            <span class="block text-sm font-black text-slate-900">
                                {{ $sub->currentPlan->name['en'] ?? 'Custom Plan' }}
                            </span>
                            <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wide">
                                {{ ucfirst($sub->billing_cycle) }}
                            </span>
                        </td>

                        {{-- Pricing --}}
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-bold text-emerald-600">
                                ${{ number_format($sub->monthly_amount ?? 0, 2) }}
                            </span>
                        </td>

                        {{-- Usage (Progress Bar) --}}
                        <td class="px-6 py-4 w-48">
                            <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1">
                                <span>Uploads</span>
                                <span>{{ $isUnlimited ? '∞' : $used . ' / ' . $limit }}</span>
                            </div>
                            @if($isUnlimited)
                                <div class="w-full bg-emerald-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full w-full"></div>
                                </div>
                            @else
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $percent > 90 ? 'bg-rose-500' : 'bg-indigo-500' }}" style="width: {{ min($percent, 100) }}%"></div>
                                </div>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusStyle = match($sub->status) {
                                    'active' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'cancelled' => 'bg-rose-100 text-rose-700 border-rose-200',
                                    'expired' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200'
                                };
                                $statusIcon = match($sub->status) {
                                    'active' => 'fa-check-circle',
                                    'cancelled' => 'fa-ban',
                                    'expired' => 'fa-clock',
                                    default => 'fa-circle'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wide border {{ $statusStyle }}">
                                <i class="fas {{ $statusIcon }}"></i> {{ ucfirst($sub->status) }}
                            </span>
                        </td>

                        {{-- Date --}}
                        <td class="px-6 py-4 text-right">
                            <span class="block text-xs font-bold text-slate-700">
                                {{ $sub->end_date ? $sub->end_date->format('M d, Y') : 'Never' }}
                            </span>
                            @if($sub->status == 'active' && $sub->end_date && $sub->end_date->isPast())
                                <span class="text-[10px] font-bold text-rose-500">Overdue</span>
                            @elseif($sub->status == 'active')
                                <span class="text-[10px] text-slate-400">Renews in {{ now()->diffInDays($sub->end_date) }} days</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right">
                            <div class="relative group/menu inline-block">
                                <button class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>

                                <div class="hidden group-hover/menu:block absolute right-0 top-6 mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-xl z-50 animate-in fade-in zoom-in-95 duration-100 p-1">
                                    <a href="{{ route('admin.subscriptions.show', $sub->id) }}" class="flex items-center gap-3 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600 rounded-lg transition">
                                        <i class="fas fa-eye w-4 text-center"></i> View Details
                                    </a>

                                    @if($sub->status == 'active')
                                    <form action="{{ route('admin.subscriptions.cancel', $sub->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Cancel this subscription immediately?')" class="w-full flex items-center gap-3 px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-lg transition text-left">
                                            <i class="fas fa-times-circle w-4 text-center"></i> Cancel Plan
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">No subscriptions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $subscriptions->withQueryString()->links() }}
        </div>
    </div>
</div>

<style>
    /* Clean Scrollbar */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

@endsection
