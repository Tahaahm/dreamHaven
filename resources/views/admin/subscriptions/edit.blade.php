@extends('layouts.admin-layout')

@section('title', 'Edit Subscription')

@section('content')

{{-- Resolve Subscriber Logic (Agent or Office) --}}
@php
    $subscriberName = 'Unknown User';
    $subscriberType = 'N/A';

    // Check if the user is an Agent
    $agent = \App\Models\Agent::find($subscription->user_id);
    if ($agent) {
        $subscriberName = $agent->agent_name;
        $subscriberType = 'Agent';
    } else {
        // Check if the user is an Office
        $office = \App\Models\RealEstateOffice::find($subscription->user_id);
        if ($office) {
            $subscriberName = $office->company_name;
            $subscriberType = 'Real Estate Office';
        }
    }

    // Get Plan Name Safely
    $planName = 'Unknown Plan';
    if ($subscription->currentPlan) {
        $rawName = $subscription->currentPlan->name;
        // Check if it's an array or JSON string
        if (is_array($rawName)) {
            $planName = $rawName['en'] ?? $rawName['ar'] ?? 'Plan';
        } elseif (is_string($rawName)) {
            // Try to decode if it's a JSON string
            $decoded = json_decode($rawName, true);
            $planName = is_array($decoded) ? ($decoded['en'] ?? 'Plan') : $rawName;
        }
    }
@endphp

<div class="max-w-4xl mx-auto animate-in fade-in zoom-in-95 duration-500">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Edit Subscription</h1>
            <p class="text-sm text-slate-500">Managing subscription for <span class="font-bold text-slate-800">{{ $subscriberName }}</span></p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.subscriptions.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition">Cancel</a>
            <button type="submit" form="editSubscriptionForm" class="px-6 py-2.5 bg-black text-white font-bold rounded-xl shadow-lg hover:bg-slate-800 transition flex items-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    <form id="editSubscriptionForm" method="POST" action="{{ route('admin.subscriptions.update', $subscription->id) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="md:col-span-2 space-y-6">

                {{-- Plan Details (Read Only as requested) --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-6">Plan Configuration</h3>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Assigned Plan</label>
                            {{-- Hidden input to keep the ID --}}
                            <input type="hidden" name="current_plan_id" value="{{ $subscription->current_plan_id }}">
                            <div class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-bold text-slate-500 cursor-not-allowed">
                                {{ $planName }} ({{ $subscriberType }})
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Plan cannot be changed directly. Cancel and create new if needed.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Recurring Price</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                    <input type="number" step="0.01" name="monthly_amount" value="{{ old('monthly_amount', $subscription->monthly_amount) }}" class="w-full pl-8 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-lg font-black text-emerald-600 outline-none focus:border-emerald-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Billing Cycle</label>
                                <select name="billing_cycle" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none cursor-pointer">
                                    <option value="monthly" {{ $subscription->billing_cycle == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="annual" {{ $subscription->billing_cycle == 'annual' ? 'selected' : '' }}>Annual</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Limits --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-6">Resource Limits</h3>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Total Upload Limit</label>
                            <input type="number" name="property_activation_limit" value="{{ old('property_activation_limit', $subscription->property_activation_limit) }}" placeholder="0 for Unlimited" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Remaining Activations</label>
                            <input type="number" name="remaining_activations" value="{{ old('remaining_activations', $subscription->remaining_activations) }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                {{-- Subscriber Info --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Subscriber</h3>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold">
                            {{ substr($subscriberName, 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $subscriberName }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $subscriberType }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                        <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">System ID</p>
                        <p class="text-xs font-mono text-slate-600 break-all">{{ $subscription->user_id }}</p>
                    </div>
                </div>

                {{-- Status & Dates --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Status & Timeline</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Subscription Status</label>
                            <select name="status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none cursor-pointer">
                                <option value="active" {{ $subscription->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="cancelled" {{ $subscription->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="expired" {{ $subscription->status == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="suspended" {{ $subscription->status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Start Date</label>
                            <input type="date" name="start_date" value="{{ $subscription->start_date ? $subscription->start_date->format('Y-m-d') : '' }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">End / Renew Date</label>
                            <input type="date" name="end_date" value="{{ $subscription->end_date ? $subscription->end_date->format('Y-m-d') : '' }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none">
                        </div>
                        <div class="pt-4 border-t border-slate-100">
                            <label class="flex items-center gap-3 cursor-pointer p-2 hover:bg-slate-50 rounded-lg transition">
                                <input type="checkbox" name="auto_renewal" value="1" {{ $subscription->auto_renewal ? 'checked' : '' }} class="w-5 h-5 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700">Auto Renewal</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
