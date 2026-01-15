@extends('layouts.admin-layout')

@section('title', 'Subscription Details')

@section('content')

<div class="max-w-5xl mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- 1. Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <nav class="flex text-sm text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900 transition">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('admin.subscriptions.index') }}" class="hover:text-slate-900 transition">Subscriptions</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-bold">Details</span>
            </nav>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                Subscription #{{ substr($subscription->id, 0, 8) }}
            </h1>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.subscriptions.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition shadow-sm">
                Back
            </a>

            @if($subscription->status == 'active')
            <form action="{{ route('admin.subscriptions.cancel', $subscription->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this subscription? It will stop renewing.');">
                @csrf
                <button type="submit" class="px-5 py-2.5 bg-rose-50 text-rose-600 border border-rose-100 font-bold rounded-xl hover:bg-rose-100 transition flex items-center gap-2">
                    <i class="fas fa-ban"></i> Cancel Plan
                </button>
            </form>
            <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl shadow-lg hover:bg-slate-800 transition flex items-center gap-2">
                <i class="fas fa-pen"></i> Edit
            </a>
            @endif
        </div>
    </div>

    {{-- Resolve Subscriber Logic --}}
    @php
        $subscriberName = 'Unknown User';
        $subscriberEmail = 'N/A';
        $subscriberType = 'N/A';
        $subscriberLink = '#';
        $avatarChar = 'U';

        if ($subscription->currentPlan && $subscription->currentPlan->type == 'agent') {
            $agent = \App\Models\Agent::find($subscription->user_id);
            if ($agent) {
                $subscriberName = $agent->agent_name;
                $subscriberEmail = $agent->primary_email;
                $subscriberType = 'Agent';
                $subscriberLink = route('admin.agents.show', $agent->id);
                $avatarChar = substr($subscriberName, 0, 1);
            }
        } elseif ($subscription->currentPlan && $subscription->currentPlan->type == 'real_estate_office') {
            $office = \App\Models\RealEstateOffice::find($subscription->user_id);
            if ($office) {
                $subscriberName = $office->company_name;
                $subscriberEmail = $office->email_address;
                $subscriberType = 'Real Estate Office';
                $subscriberLink = route('admin.offices.show', $office->id);
                $avatarChar = substr($subscriberName, 0, 1);
            }
        }
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left Column: Main Info --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Subscriber Card --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-slate-800 to-black text-white flex items-center justify-center text-2xl font-black shadow-lg">
                            {{ $avatarChar }}
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900">{{ $subscriberName }}</h2>
                            <p class="text-sm text-slate-500 font-medium">{{ $subscriberEmail }}</p>
                            <span class="inline-flex mt-2 items-center px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-slate-100 text-slate-600">
                                {{ $subscriberType }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ $subscriberLink }}" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                        View Profile <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-6 border-t border-slate-100">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Plan Name</p>
                        <p class="text-lg font-bold text-slate-900">{{ $subscription->currentPlan->name['en'] ?? 'Custom Plan' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Billing Cycle</p>
                        <p class="text-lg font-bold text-slate-900 capitalize">{{ $subscription->billing_cycle }}</p>
                    </div>
                </div>
            </div>

            {{-- Usage Stats --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                <h3 class="text-lg font-black text-slate-900 mb-6">Resource Usage</h3>

                @php
                    $limit = $subscription->property_activation_limit;
                    $used = $subscription->properties_activated_this_month ?? 0;
                    $isUnlimited = ($limit === 0 || $limit === null);
                    $percent = $isUnlimited ? 0 : ($used / $limit) * 100;
                @endphp

                <div class="mb-2 flex justify-between items-end">
                    <span class="text-sm font-bold text-slate-700">Property Uploads</span>
                    <span class="text-xs font-bold text-slate-500">
                        @if($isUnlimited)
                            <span class="text-emerald-600">Unlimited Access</span>
                        @else
                            {{ $used }} / {{ $limit }} Used
                        @endif
                    </span>
                </div>

                <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden mb-6">
                    @if($isUnlimited)
                         <div class="h-full w-full bg-emerald-500 rounded-full"></div>
                    @else
                         <div class="h-full bg-slate-900 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Remaining</p>
                        <p class="text-2xl font-black text-slate-900">{{ $isUnlimited ? '∞' : ($limit - $used) }}</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">This Month</p>
                        <p class="text-2xl font-black text-slate-900">{{ $used }}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Status & Timeline --}}
        <div class="space-y-6">

            {{-- Status Card --}}
            <div class="bg-slate-900 text-white rounded-3xl p-8 shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full blur-2xl -mr-10 -mt-10"></div>

                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Current Status</p>

                <div class="flex items-center gap-3 mb-6">
                    @if($subscription->status == 'active')
                        <div class="w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></div>
                        <h2 class="text-3xl font-black text-white">Active</h2>
                    @elseif($subscription->status == 'cancelled')
                        <div class="w-3 h-3 bg-rose-500 rounded-full"></div>
                        <h2 class="text-3xl font-black text-white">Cancelled</h2>
                    @else
                        <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                        <h2 class="text-3xl font-black text-white capitalize">{{ $subscription->status }}</h2>
                    @endif
                </div>

                <div class="space-y-3 pt-6 border-t border-slate-700">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Recurring Price</span>
                        <span class="font-mono font-bold text-emerald-400">${{ number_format($subscription->monthly_amount, 2) }}</span>
                    </div>
                     <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Auto Renew</span>
                        <span class="font-bold {{ $subscription->auto_renewal ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $subscription->auto_renewal ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-6">Timeline</h3>

                <div class="space-y-6 relative before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-100">

                    {{-- Start --}}
                    <div class="relative pl-8">
                        <div class="absolute left-0 top-1.5 w-4 h-4 bg-emerald-500 rounded-full border-4 border-white shadow-sm"></div>
                        <p class="text-sm font-bold text-slate-900">Started</p>
                        <p class="text-xs text-slate-500">{{ $subscription->start_date ? $subscription->start_date->format('M d, Y') : 'N/A' }}</p>
                    </div>

                    {{-- Next Billing / End --}}
                    <div class="relative pl-8">
                        @if($subscription->status == 'active')
                            <div class="absolute left-0 top-1.5 w-4 h-4 bg-blue-500 rounded-full border-4 border-white shadow-sm"></div>
                            <p class="text-sm font-bold text-slate-900">Renews On</p>
                            <p class="text-xs text-slate-500">{{ $subscription->end_date ? $subscription->end_date->format('M d, Y') : 'Lifetime' }}</p>
                            <p class="text-[10px] text-indigo-600 font-bold mt-1">
                                {{ $subscription->end_date ? now()->diffInDays($subscription->end_date) . ' days remaining' : '' }}
                            </p>
                        @else
                            <div class="absolute left-0 top-1.5 w-4 h-4 bg-rose-500 rounded-full border-4 border-white shadow-sm"></div>
                            <p class="text-sm font-bold text-slate-900">Ends On</p>
                            <p class="text-xs text-slate-500">{{ $subscription->end_date ? $subscription->end_date->format('M d, Y') : 'N/A' }}</p>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection
