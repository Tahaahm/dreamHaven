@extends('layouts.admin-layout')

@section('title', 'Transactions')

@section('content')

<div class="max-w-[1600px] mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Transactions</h1>
            <p class="text-slate-500 font-medium">Monitor financial activity, commissions, and property sales.</p>
        </div>
        <div class="flex items-center gap-3">
             <div class="bg-white border border-slate-200 text-slate-500 px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm flex items-center gap-2">
                <i class="fas fa-filter"></i> Filter
            </div>
            <button class="bg-black hover:bg-slate-800 text-white px-5 py-2.5 text-xs font-bold rounded-xl shadow-lg shadow-slate-200 transition-all flex items-center gap-2 uppercase tracking-wide">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Revenue (USD)</p>
            <h3 class="text-3xl font-black text-slate-900">${{ number_format(\App\Models\Transaction::where('status', 'completed')->sum('amount_usd')) }}</h3>
        </div>
         <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Revenue (IQD)</p>
            <h3 class="text-3xl font-black text-slate-900">{{ number_format(\App\Models\Transaction::where('status', 'completed')->sum('amount_iqd')) }}</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Completed Deals</p>
            <h3 class="text-3xl font-black text-emerald-600">{{ \App\Models\Transaction::where('status', 'completed')->count() }}</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pending Review</p>
            <h3 class="text-3xl font-black text-amber-500">{{ \App\Models\Transaction::where('status', 'pending')->count() }}</h3>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Ref ID</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Property</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Buyer / Seller</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-right">Date</th>
                        <th class="px-6 py-5 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-slate-50/80 transition-colors group cursor-pointer" onclick="window.location='{{ route('admin.transactions.show', $transaction->id) }}'">

                        {{-- Ref ID --}}
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded">
                                {{ $transaction->transaction_reference ?? Str::limit($transaction->id, 8) }}
                            </span>
                        </td>

                        {{-- Property --}}
                        <td class="px-6 py-4">
                            @if($transaction->property)
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-slate-200 overflow-hidden shrink-0">
                                         {{-- Image Logic --}}
                                        @php
                                            $img = is_array($transaction->property->images) ? ($transaction->property->images[0] ?? null) : null;
                                        @endphp
                                        @if($img)
                                            <img src="{{ asset($img) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-400"><i class="fas fa-home"></i></div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 line-clamp-1 max-w-[150px]">
                                            {{ is_array($transaction->property->name) ? ($transaction->property->name['en'] ?? 'Property') : $transaction->property->name }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 uppercase font-bold">{{ $transaction->property->listing_type }}</p>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Property Deleted</span>
                            @endif
                        </td>

                        {{-- Parties --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col text-xs">
                                <span class="font-bold text-slate-700">
                                    <i class="fas fa-arrow-down text-emerald-500 mr-1"></i> {{ $transaction->buyer->username ?? 'Unknown Buyer' }}
                                </span>
                                <span class="font-bold text-slate-500 mt-1">
                                    <i class="fas fa-arrow-up text-rose-500 mr-1"></i> {{ $transaction->seller->username ?? 'Unknown Seller' }}
                                </span>
                            </div>
                        </td>

                        {{-- Amount --}}
                        <td class="px-6 py-4">
                            @if($transaction->amount_usd > 0)
                                <span class="block text-sm font-black text-slate-900">${{ number_format($transaction->amount_usd) }}</span>
                            @endif
                            @if($transaction->amount_iqd > 0)
                                <span class="block text-xs font-bold text-slate-500">{{ number_format($transaction->amount_iqd) }} IQD</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusColor = match($transaction->status) {
                                    'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'cancelled' => 'bg-rose-100 text-rose-700 border-rose-200',
                                    'in_progress' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200'
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wide border {{ $statusColor }}">
                                {{ str_replace('_', ' ', $transaction->status) }}
                            </span>
                        </td>

                        {{-- Date --}}
                        <td class="px-6 py-4 text-right">
                            <span class="text-xs font-bold text-slate-500">
                                {{ $transaction->created_at->format('M d, Y') }}
                            </span>
                        </td>

                        {{-- Action --}}
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="text-slate-400 hover:text-indigo-600 transition">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm font-medium">No transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $transactions->links() }}
        </div>
    </div>
</div>

@endsection
