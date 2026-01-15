@extends('layouts.admin-layout')

@section('title', 'Subscription Plans')

@section('content')

<div class="max-w-7xl mx-auto animate-in fade-in zoom-in-95 duration-500">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Subscription Plans</h1>
            <p class="text-slate-500 font-medium">Manage pricing tiers for Agents and Offices.</p>
        </div>
        <div>
            <a href="{{ route('admin.subscription-plans.create') }}" class="bg-black hover:bg-slate-800 text-white px-6 py-3 text-sm font-bold rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Create New Plan
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Plan Name</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Target Audience</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Price (USD / IQD)</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-center">Duration</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($plans as $plan)
                    @php
                        $name = is_array($plan->name) ? ($plan->name['en'] ?? 'Plan') : $plan->name;
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors group">

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 font-black text-lg">
                                    {{ substr($name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $name }}</p>
                                    @if($plan->is_featured)
                                        <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100">Featured</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            @if($plan->type == 'agent')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    <i class="fas fa-user-tie"></i> Agent
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                    <i class="fas fa-building"></i> Office
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-900">${{ number_format($plan->final_price_usd) }}</span>
                                <span class="text-xs font-bold text-slate-400">{{ number_format($plan->final_price_iqd) }} IQD</span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span class="font-mono text-sm font-bold text-slate-600">{{ $plan->duration_months }} Months</span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <form action="{{ route('admin.subscription-plans.toggle-active', $plan->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $plan->active ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $plan->active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.subscription-plans.edit', $plan->id) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('admin.subscription-plans.delete', $plan->id) }}" method="POST" onsubmit="return confirm('Delete this plan? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">No subscription plans found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $plans->links() }}
        </div>
    </div>
</div>
@endsection
