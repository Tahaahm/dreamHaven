@extends('layouts.admin-layout')

@section('title', 'Offices Directory')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     DREAM MULK — OFFICES DIRECTORY v2
     Same design system as Agents: navy + gold, Renewal Radar,
     desktop table + mobile cards, expiry donut rings.
════════════════════════════════════════════════════════════════ --}}

<style>
    :root {
        --dm-navy: #233264;
        --dm-navy-soft: #ECF0F8;
        --dm-gold: #C9A961;
        --dm-gold-soft: #FBF6EA;
    }
    .dm-navy { color: var(--dm-navy); }
    .bg-dm-navy { background-color: var(--dm-navy); }
    .bg-dm-navy-soft { background-color: var(--dm-navy-soft); }
    .dm-gold { color: var(--dm-gold); }
    .bg-dm-gold-soft { background-color: var(--dm-gold-soft); }

    .radar-scroll::-webkit-scrollbar { display: none; }
    .radar-scroll { -ms-overflow-style: none; scrollbar-width: none; }

    .expiry-ring {
        --pct: 0;
        --ring-color: #10B981;
        background: conic-gradient(var(--ring-color) calc(var(--pct) * 1%), #E2E8F0 0);
    }

    @media (prefers-reduced-motion: reduce) {
        .animate-pulse, .animate-ping { animation: none; }
    }
</style>

<div class="max-w-7xl mx-auto animate-fade-in-up pb-24 md:pb-8">

    {{-- ── PAGE HEADER ──────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-8 h-[3px] rounded-full" style="background: var(--dm-gold)"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Dream Mulk Network</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black dm-navy tracking-tight">Offices Directory</h1>
        </div>

        @if(($pendingCount ?? 0) > 0)
        <a href="{{ route('admin.offices.index', ['status' => 'pending']) }}"
           class="self-start sm:self-auto flex items-center gap-2 px-4 py-2.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl text-sm font-bold hover:bg-amber-100 active:scale-[0.98] transition shadow-sm">
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
            </span>
            {{ $pendingCount }} Pending
        </a>
        @endif
    </div>

    {{-- ── WEEKLY BRIEFING — top posters + expirations ──────────── --}}
    @if(isset($briefing))
        @include('admin.partials.weekly-briefing', [
            'briefing'   => $briefing,
            'editRoute'  => 'admin.offices.edit',
            'showRoute'  => 'admin.offices.show',
            'entityIcon' => 'fa-building',
        ])
    @endif

    {{-- ── RENEWAL RADAR ─────────────────────────────────────────── --}}
    @php
        $radarOffices = $offices->filter(function ($o) {
            $end = $o->subscription?->end_date;
            if (!$end || $o->subscription->status !== 'active') return false;
            $days = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($end)->startOfDay(), false);
            return $days >= 0 && $days <= 14;
        });
    @endphp
    @if($radarOffices->isNotEmpty())
    <div class="mb-6 bg-dm-navy rounded-2xl p-4 md:p-5 shadow-lg shadow-slate-300/50 overflow-hidden relative">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full animate-pulse" style="background: var(--dm-gold)"></span>
                <h2 class="text-white text-sm font-black uppercase tracking-widest">Renewal Radar</h2>
            </div>
            <span class="text-[10px] font-bold text-white/50 uppercase">{{ $radarOffices->count() }} at risk · 14 days</span>
        </div>
        <div class="radar-scroll flex gap-3 overflow-x-auto pb-1">
            @foreach($radarOffices as $ro)
                @php
                    $rEnd = \Carbon\Carbon::parse($ro->subscription->end_date);
                    $rDays = now()->startOfDay()->diffInDays($rEnd->startOfDay(), false);
                @endphp
                <a href="{{ route('admin.offices.edit', $ro->id) }}"
                   class="shrink-0 w-52 bg-white/10 border border-white/10 rounded-xl p-3 hover:bg-white/20 active:scale-[0.97] transition group">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center overflow-hidden shrink-0">
                            @if($ro->logo)
                                <img src="{{ asset($ro->logo) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-building text-white/60 text-sm"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-white text-xs font-bold truncate">{{ $ro->company_name }}</p>
                            <p class="text-white/40 text-[10px] font-semibold uppercase">{{ $ro->current_plan ?? 'plan' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold {{ $rDays <= 3 ? 'text-rose-300' : 'text-amber-300' }}">
                            <i class="fas fa-hourglass-end mr-1"></i>{{ $rDays == 0 ? 'Today' : $rDays . ' days left' }}
                        </span>
                        <span class="text-[10px] font-black group-hover:translate-x-0.5 transition-transform" style="color: var(--dm-gold)">RENEW →</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── STATS STRIP ───────────────────────────────────────────── --}}
    <div class="radar-scroll flex md:grid md:grid-cols-5 gap-3 overflow-x-auto mb-6 pb-1">
        @php
            $statCards = [
                ['label' => 'Total Offices', 'value' => $stats['total'] ?? 0, 'icon' => 'fa-building', 'tone' => 'slate'],
                ['label' => 'Verified', 'value' => $stats['verified'] ?? 0, 'icon' => 'fa-check-circle', 'tone' => 'emerald'],
                ['label' => 'Pending', 'value' => $stats['pending'] ?? 0, 'icon' => 'fa-clock', 'tone' => 'amber'],
                ['label' => 'Active Plans', 'value' => $stats['active_subs'] ?? 0, 'icon' => 'fa-crown', 'tone' => 'gold'],
                ['label' => 'Expiring ≤7d', 'value' => $stats['expiring_soon'] ?? 0, 'icon' => 'fa-hourglass-end', 'tone' => ($stats['expiring_soon'] ?? 0) > 0 ? 'rose' : 'slate'],
            ];
        @endphp
        @foreach($statCards as $card)
        <div class="shrink-0 w-36 md:w-auto bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">{{ $card['label'] }}</span>
                <i class="fas {{ $card['icon'] }} text-xs
                    {{ $card['tone'] == 'emerald' ? 'text-emerald-500' : '' }}
                    {{ $card['tone'] == 'amber' ? 'text-amber-500' : '' }}
                    {{ $card['tone'] == 'rose' ? 'text-rose-500' : '' }}
                    {{ $card['tone'] == 'gold' ? 'dm-gold' : '' }}
                    {{ $card['tone'] == 'slate' ? 'text-slate-300' : '' }}"></i>
            </div>
            <p class="text-2xl font-black {{ $card['tone'] == 'rose' ? 'text-rose-600' : 'dm-navy' }}">{{ number_format($card['value']) }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── SEARCH + FILTER PILLS ─────────────────────────────────── --}}
    <div class="mb-5 space-y-3">
        <form method="GET" action="{{ route('admin.offices.index') }}" class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400 text-sm"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="block w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#233264]/20 focus:border-[#233264] transition shadow-sm"
                   placeholder="Search offices by name or email…">
        </form>

        <div class="radar-scroll flex gap-2 overflow-x-auto pb-1">
            @php
                $pill = 'shrink-0 px-4 py-2 rounded-full text-xs font-bold border transition active:scale-95 whitespace-nowrap';
                $on   = 'bg-dm-navy text-white border-transparent shadow';
                $off  = 'bg-white text-slate-600 border-slate-200 hover:border-slate-300';
                $onRose = 'bg-rose-600 text-white border-transparent shadow';
            @endphp
            <a href="{{ route('admin.offices.index') }}"
               class="{{ $pill }} {{ !request()->hasAny(['status','expiry']) ? $on : $off }}">All</a>
            <a href="{{ route('admin.offices.index', ['status' => 'verified']) }}"
               class="{{ $pill }} {{ request('status') == 'verified' ? $on : $off }}"><i class="fas fa-check-circle mr-1.5 text-[10px]"></i>Verified</a>
            <a href="{{ route('admin.offices.index', ['status' => 'pending']) }}"
               class="{{ $pill }} {{ request('status') == 'pending' ? $on : $off }}"><i class="fas fa-clock mr-1.5 text-[10px]"></i>Pending</a>
            <a href="{{ route('admin.offices.index', ['expiry' => 'expiring']) }}"
               class="{{ $pill }} {{ request('expiry') == 'expiring' ? $onRose : $off }}"><i class="fas fa-hourglass-end mr-1.5 text-[10px]"></i>Expiring ≤7d</a>
            <a href="{{ route('admin.offices.index', ['expiry' => 'expired']) }}"
               class="{{ $pill }} {{ request('expiry') == 'expired' ? $onRose : $off }}"><i class="fas fa-circle-xmark mr-1.5 text-[10px]"></i>Expired</a>
            <a href="{{ route('admin.offices.index', ['expiry' => 'active']) }}"
               class="{{ $pill }} {{ request('expiry') == 'active' ? $on : $off }}"><i class="fas fa-crown mr-1.5 text-[10px]"></i>Active plan</a>
        </div>
    </div>

    @php
        $computeExpiry = function ($office) {
            $sub = $office->subscription;
            $end = $sub?->end_date ? \Carbon\Carbon::parse($sub->end_date) : null;
            $start = $sub?->start_date ? \Carbon\Carbon::parse($sub->start_date) : null;
            $days = $end ? now()->startOfDay()->diffInDays($end->startOfDay(), false) : null;
            $active = $sub && $sub->status === 'active' && $days !== null && $days >= 0;
            $expired = $sub && $days !== null && $days < 0;
            $expiring = $active && $days <= 7;
            $pct = 0;
            if ($start && $end && $end->gt($start)) {
                $total = $start->diffInDays($end);
                $left = max(0, $days ?? 0);
                $pct = $total > 0 ? round(($left / $total) * 100) : 0;
            }
            return compact('sub', 'end', 'days', 'active', 'expired', 'expiring', 'pct');
        };
    @endphp

    {{-- ── DESKTOP TABLE (md+) ───────────────────────────────────── --}}
    <div class="hidden md:block bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200">
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Office</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Contact</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Listings</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Subscription</th>
                    <th class="px-6 py-4 w-40 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($offices as $office)
                @php extract($computeExpiry($office)); @endphp
                <tr class="hover:bg-slate-50/70 transition-colors {{ $expiring ? 'bg-rose-50/40' : ($expired ? 'bg-slate-50/60' : '') }}">

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-dm-navy-soft border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                                @if($office->logo)
                                    <img src="{{ asset($office->logo) }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-building dm-navy opacity-40"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('admin.offices.show', $office->id) }}" class="text-sm font-bold text-slate-900 hover:text-[#233264] transition block truncate max-w-[180px]">{{ $office->company_name }}</a>
                                <span class="text-[10px] font-bold uppercase text-slate-400">
                                    <i class="fas fa-location-dot text-slate-300 mr-1"></i>{{ $office->city ?? 'Location N/A' }}
                                </span>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <p class="text-xs font-bold text-slate-700 truncate max-w-[180px]">{{ $office->email_address }}</p>
                        @if($office->phone_number)
                            <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $office->phone_number }}</p>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-center">
                        <span class="text-base font-black dm-navy">{{ $office->owned_properties_count ?? 0 }}</span>
                    </td>

                    <td class="px-6 py-4 text-center">
                        @if($office->is_verified)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Verified
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Pending
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="expiry-ring w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                                 style="--pct: {{ $expired ? 0 : $pct }}; --ring-color: {{ $expired || $expiring ? '#F43F5E' : ($pct < 25 ? '#F59E0B' : '#10B981') }};">
                                <div class="w-7 h-7 rounded-full bg-white flex items-center justify-center">
                                    @if($expired)
                                        <i class="fas fa-xmark text-rose-500 text-[10px]"></i>
                                    @elseif($days !== null)
                                        <span class="text-[9px] font-black {{ $expiring ? 'text-rose-600' : 'dm-navy' }}">{{ $days }}d</span>
                                    @else
                                        <i class="fas fa-minus text-slate-300 text-[10px]"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="min-w-0">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider
                                    {{ $active ? 'bg-dm-gold-soft dm-gold' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $office->current_plan ?? 'FREE' }}
                                </span>
                                <p class="text-[10px] font-semibold mt-0.5 {{ $expired || $expiring ? 'text-rose-500' : 'text-slate-400' }}">
                                    @if($end)
                                        {{ $expired ? 'Ended' : 'Ends' }} {{ $end->format('d M Y') }}
                                    @else
                                        No subscription
                                    @endif
                                </p>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($expiring || $expired)
                            <a href="{{ route('admin.offices.edit', $office->id) }}" title="Renew plan"
                               class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center hover:bg-rose-100 active:scale-95 transition">
                                <i class="fas fa-rotate text-xs"></i>
                            </a>
                            @endif
                            <a href="{{ route('admin.offices.show', $office->id) }}" title="View"
                               class="w-8 h-8 rounded-lg bg-slate-50 text-slate-500 border border-slate-200 flex items-center justify-center hover:bg-[#233264] hover:text-white hover:border-transparent active:scale-95 transition">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('admin.offices.edit', $office->id) }}" title="Edit"
                               class="w-8 h-8 rounded-lg bg-slate-50 text-slate-500 border border-slate-200 flex items-center justify-center hover:bg-[#233264] hover:text-white hover:border-transparent active:scale-95 transition">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            @if(!$office->is_verified)
                            <form action="{{ route('admin.offices.verify', $office->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Verify"
                                        class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center hover:bg-emerald-500 hover:text-white hover:border-transparent active:scale-95 transition">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.offices.delete', $office->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete"
                                        onclick="return confirm('Permanently delete {{ addslashes($office->company_name) }}?')"
                                        class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 border border-slate-200 flex items-center justify-center hover:bg-rose-500 hover:text-white hover:border-transparent active:scale-95 transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="w-16 h-16 bg-dm-navy-soft rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-building dm-navy text-xl opacity-40"></i>
                        </div>
                        <h3 class="text-slate-900 font-bold mb-1">No offices found</h3>
                        <p class="text-slate-500 text-sm mb-4">Try adjusting your filters or search.</p>
                        <a href="{{ route('admin.offices.index') }}" class="inline-flex items-center gap-2 text-sm font-bold dm-navy hover:underline">
                            <i class="fas fa-rotate-left"></i> Clear filters
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $offices->withQueryString()->links() }}
        </div>
    </div>

    {{-- ── MOBILE CARDS (below md) ───────────────────────────────── --}}
    <div class="md:hidden space-y-3">
        @forelse($offices as $office)
        @php extract($computeExpiry($office)); @endphp
        <div class="bg-white rounded-2xl border {{ $expiring ? 'border-rose-200' : 'border-slate-200' }} shadow-sm overflow-hidden">

            @if($expiring)
            <div class="bg-rose-500 px-4 py-1.5 flex items-center justify-between">
                <span class="text-[10px] font-black text-white uppercase tracking-wider">
                    <i class="fas fa-hourglass-end mr-1"></i>{{ $days == 0 ? 'Expires today' : 'Expires in ' . $days . ' days' }}
                </span>
                <a href="{{ route('admin.offices.edit', $office->id) }}" class="text-[10px] font-black text-white underline underline-offset-2">RENEW NOW</a>
            </div>
            @elseif($expired)
            <div class="bg-slate-700 px-4 py-1.5 flex items-center justify-between">
                <span class="text-[10px] font-black text-white/90 uppercase tracking-wider">
                    <i class="fas fa-circle-xmark mr-1"></i>Subscription expired
                </span>
                <a href="{{ route('admin.offices.edit', $office->id) }}" class="text-[10px] font-black underline underline-offset-2" style="color: var(--dm-gold)">REACTIVATE</a>
            </div>
            @endif

            <div class="p-4">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-dm-navy-soft border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                        @if($office->logo)
                            <img src="{{ asset($office->logo) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-building dm-navy opacity-40 text-lg"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('admin.offices.show', $office->id) }}" class="text-sm font-black text-slate-900 truncate">{{ $office->company_name }}</a>
                            @if($office->is_verified)
                                <span class="shrink-0 w-5 h-5 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center" title="Verified">
                                    <i class="fas fa-check text-emerald-500 text-[9px]"></i>
                                </span>
                            @else
                                <span class="shrink-0 px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-50 text-amber-600 border border-amber-200 uppercase">Pending</span>
                            @endif
                        </div>
                        <p class="text-[11px] font-semibold text-slate-400 truncate mt-0.5">{{ $office->email_address }}</p>
                        <p class="text-[10px] font-bold uppercase text-slate-400 mt-0.5">
                            <i class="fas fa-location-dot text-slate-300 mr-1"></i>{{ $office->city ?? 'N/A' }}
                            <span class="mx-1.5 text-slate-200">·</span>
                            {{ $office->owned_properties_count ?? 0 }} listings
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3 mb-3">
                    <div class="expiry-ring w-11 h-11 rounded-full flex items-center justify-center shrink-0"
                         style="--pct: {{ $expired ? 0 : $pct }}; --ring-color: {{ $expired || $expiring ? '#F43F5E' : ($pct < 25 ? '#F59E0B' : '#10B981') }};">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center">
                            @if($expired)
                                <i class="fas fa-xmark text-rose-500 text-xs"></i>
                            @elseif($days !== null)
                                <span class="text-[10px] font-black {{ $expiring ? 'text-rose-600' : 'dm-navy' }}">{{ $days }}d</span>
                            @else
                                <i class="fas fa-minus text-slate-300 text-xs"></i>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider
                            {{ $active ? 'bg-dm-gold-soft dm-gold' : 'bg-slate-200 text-slate-500' }}">
                            {{ $office->current_plan ?? 'FREE' }}
                        </span>
                        <p class="text-[11px] font-bold mt-1 {{ $expired || $expiring ? 'text-rose-500' : 'text-slate-500' }}">
                            @if($end)
                                <i class="far fa-calendar mr-1"></i>{{ $expired ? 'Ended' : 'Ends' }} {{ $end->format('d M Y') }}
                            @else
                                No active subscription
                            @endif
                        </p>
                    </div>
                </div>

                <div class="grid {{ !$office->is_verified ? 'grid-cols-4' : 'grid-cols-3' }} gap-2">
                    <a href="{{ route('admin.offices.show', $office->id) }}"
                       class="flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-dm-navy text-white text-[11px] font-bold active:scale-95 transition">
                        <i class="fas fa-eye text-[10px]"></i> View
                    </a>
                    <a href="{{ route('admin.offices.edit', $office->id) }}"
                       class="flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-[11px] font-bold active:scale-95 transition">
                        <i class="fas fa-pen text-[10px]"></i> Edit
                    </a>
                    @if(!$office->is_verified)
                    <form action="{{ route('admin.offices.verify', $office->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-emerald-500 text-white text-[11px] font-bold active:scale-95 transition">
                            <i class="fas fa-check text-[10px]"></i> Verify
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('admin.offices.delete', $office->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit"
                                onclick="return confirm('Permanently delete {{ addslashes($office->company_name) }}?')"
                                class="w-full flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-rose-50 text-rose-600 text-[11px] font-bold active:scale-95 transition">
                            <i class="fas fa-trash text-[10px]"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center">
            <div class="w-16 h-16 bg-dm-navy-soft rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-building dm-navy text-xl opacity-40"></i>
            </div>
            <h3 class="text-slate-900 font-bold mb-1">No offices found</h3>
            <p class="text-slate-500 text-sm mb-4">Try adjusting your filters or search.</p>
            <a href="{{ route('admin.offices.index') }}" class="inline-flex items-center gap-2 text-sm font-bold dm-navy">
                <i class="fas fa-rotate-left"></i> Clear filters
            </a>
        </div>
        @endforelse

        <div class="pt-2">
            {{ $offices->withQueryString()->links() }}
        </div>
    </div>
</div>

@endsection
