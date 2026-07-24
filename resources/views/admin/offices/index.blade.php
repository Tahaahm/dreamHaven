@extends('layouts.admin-layout')

@section('title', 'Offices')

@push('styles') @include('admin.partials.ui-kit') @endpush

@section('content')

@php
    use Illuminate\Support\Facades\Route as Rt;

    $link = fn($n, $p = []) => Rt::has($n) ? route($n, $p) : null;

    $expiry = function ($model) {
        $sub   = $model->subscription;
        $end   = $sub && $sub->end_date ? \Carbon\Carbon::parse($sub->end_date) : null;
        $start = $sub && $sub->start_date ? \Carbon\Carbon::parse($sub->start_date) : null;
        $days  = $end ? (int) now()->startOfDay()->diffInDays($end->startOfDay(), false) : null;

        $active   = $sub && $sub->status === 'active' && $days !== null && $days >= 0;
        $expired  = $sub && $days !== null && $days < 0;
        $expiring = $active && $days <= 7;

        $pct = 0;
        if ($start && $end && $end->gt($start)) {
            $total = max($start->diffInDays($end), 1);
            $pct   = (int) round((max(0, $days) / $total) * 100);
        }

        $ring = $expired || $expiring ? '#e11d48' : ($pct < 25 ? '#f59e0b' : '#22c55e');

        return compact('sub', 'end', 'days', 'active', 'expired', 'expiring', 'pct', 'ring');
    };

    $radar = $offices->filter(function ($o) {
        $sub = $o->subscription;
        if (!$sub || !$sub->end_date || $sub->status !== 'active') return false;
        $d = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($sub->end_date)->startOfDay(), false);
        return $d >= 0 && $d <= 14;
    });

    $hasFilters = request()->hasAny(['search', 'status', 'expiry', 'city']);
@endphp

<div class="max-w-[1500px] mx-auto">

    {{-- HEADER --}}
    <div class="page-head">
        <div>
            <p class="eyebrow mb-1.5">Dream Mulk network</p>
            <h1 class="page-ttl">Offices</h1>
            <p class="text-[13px] text-slate-500 font-semibold mt-1.5">
                {{ number_format($offices->total()) }} office{{ $offices->total() === 1 ? '' : 's' }} in this view
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="dmExportTable('#officesTable', 'offices')" class="btn-ghost"><i class="fas fa-download"></i> Export CSV</button>
            @if(($pendingCount ?? 0) > 0)
                <a href="{{ route('admin.offices.index', ['status' => 'pending']) }}" class="btn-solid" style="background:linear-gradient(135deg,#f59e0b,#f97316);box-shadow:0 10px 22px -12px rgba(245,158,11,.9)">
                    <i class="fas fa-hourglass-half"></i> {{ $pendingCount }} to review
                </a>
            @endif
        </div>
    </div>

    {{-- WEEKLY BRIEFING --}}
    @if(isset($briefing) && view()->exists('admin.partials.weekly-briefing'))
        @include('admin.partials.weekly-briefing', [
            'briefing'   => $briefing,
            'editRoute'  => 'admin.offices.edit',
            'showRoute'  => 'admin.offices.show',
            'entityIcon' => 'fa-building',
        ])
    @endif

    {{-- RENEWAL RADAR --}}
    @if($radar->isNotEmpty())
        <div class="radar">
            <div class="absolute -top-14 -right-10 w-40 h-40 rounded-full bg-white/5 pointer-events-none"></div>
            <div class="relative flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full animate-pulse" style="background:var(--dm-gold)"></span>
                    <h2 class="text-white text-[12px] font-black uppercase tracking-[.18em]">Renewal radar</h2>
                </div>
                <span class="text-[10px] font-bold text-white/45 uppercase">{{ $radar->count() }} within 14 days</span>
            </div>

            <div class="radar-scroll relative">
                @foreach($radar as $ro)
                    @php
                        $rEnd  = \Carbon\Carbon::parse($ro->subscription->end_date);
                        $rDays = (int) now()->startOfDay()->diffInDays($rEnd->startOfDay(), false);
                        $rLogo = $ro->logo ?? $ro->profile_image;
                    @endphp
                    <a href="{{ $link('admin.offices.edit', $ro->id) ?? '#' }}" class="radar-card">
                        <div class="flex items-center gap-2.5 mb-2.5">
                            @if($rLogo)
                                <img src="{{ asset($rLogo) }}" class="w-9 h-9 rounded-xl object-cover bg-white/10" alt="">
                            @else
                                <div class="w-9 h-9 rounded-xl bg-white/15 grid place-items-center text-white/70"><i class="fas fa-building text-[12px]"></i></div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-white text-[12px] font-bold truncate">{{ $ro->company_name }}</p>
                                <p class="text-white/40 text-[9.5px] font-bold uppercase tracking-wide">{{ $ro->current_plan ?? 'plan' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black {{ $rDays <= 3 ? 'text-rose-300' : 'text-amber-300' }}">
                                {{ $rDays === 0 ? 'Ends today' : ($rDays === 1 ? 'Ends tomorrow' : $rDays . ' days left') }}
                            </span>
                            <span class="text-[10px] font-black" style="color:var(--dm-gold)">RENEW →</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- STATS --}}
    <div class="stat-row">
        @php
            $cards = [
                ['Total offices', $stats['total'] ?? 0, 'fa-building', '#94a3b8'],
                ['Verified', $stats['verified'] ?? 0, 'fa-shield-halved', '#10b981'],
                ['Pending', $stats['pending'] ?? 0, 'fa-hourglass-half', '#f59e0b'],
                ['Active plans', $stats['active_subs'] ?? 0, 'fa-crown', '#C9A961'],
                ['Expiring ≤7d', $stats['expiring_soon'] ?? 0, 'fa-hourglass-end', ($stats['expiring_soon'] ?? 0) > 0 ? '#e11d48' : '#94a3b8'],
            ];
        @endphp
        @foreach($cards as [$label, $value, $icon, $color])
            <div class="stat">
                <div class="flex items-center justify-between mb-2">
                    <span class="eyebrow">{{ $label }}</span>
                    <i class="fas {{ $icon }} text-[11px]" style="color:{{ $color }}"></i>
                </div>
                <b class="num" style="{{ $color === '#e11d48' ? 'color:#e11d48' : '' }}">{{ number_format($value) }}</b>
            </div>
        @endforeach
    </div>

    {{-- SEARCH + FILTERS --}}
    <form method="GET" action="{{ route('admin.offices.index') }}" class="search-wrap">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search offices by name or email…">
    </form>

    <div class="pill-row mb-5">
        <a href="{{ route('admin.offices.index') }}" class="pill {{ !$hasFilters ? 'on' : '' }}">All</a>
        <a href="{{ route('admin.offices.index', ['status' => 'verified']) }}" class="pill {{ request('status') === 'verified' ? 'on' : '' }}"><i class="fas fa-shield-halved text-[10px]"></i> Verified</a>
        <a href="{{ route('admin.offices.index', ['status' => 'pending']) }}" class="pill {{ request('status') === 'pending' ? 'on' : '' }}"><i class="fas fa-hourglass-half text-[10px]"></i> Pending</a>
        <a href="{{ route('admin.offices.index', ['expiry' => 'expiring']) }}" class="pill {{ request('expiry') === 'expiring' ? 'on-alert' : '' }}"><i class="fas fa-hourglass-end text-[10px]"></i> Expiring ≤7d</a>
        <a href="{{ route('admin.offices.index', ['expiry' => 'expired']) }}" class="pill {{ request('expiry') === 'expired' ? 'on-alert' : '' }}"><i class="fas fa-circle-xmark text-[10px]"></i> Expired</a>
        <a href="{{ route('admin.offices.index', ['expiry' => 'active']) }}" class="pill {{ request('expiry') === 'active' ? 'on' : '' }}"><i class="fas fa-crown text-[10px]"></i> Active plan</a>
        @if($hasFilters)
            <a href="{{ route('admin.offices.index') }}" class="pill" style="background:#fef2f2;color:#b91c1c;border-color:#fecdd3"><i class="fas fa-xmark text-[10px]"></i> Clear</a>
        @endif
    </div>

    {{-- ══════════ DESKTOP TABLE ══════════ --}}
    <div class="card overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="tbl" id="officesTable">
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Contact</th>
                        <th class="text-center">Listings</th>
                        <th class="text-center">Status</th>
                        <th>Subscription</th>
                        <th class="w-44 text-right" data-noexport>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offices as $office)
                        @php
                            extract($expiry($office));
                            $logo = $office->logo ?? $office->profile_image;
                        @endphp
                        <tr style="{{ $expiring ? 'background:#fff5f6' : ($expired ? 'background:#fafbfd' : '') }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($logo)
                                        <img src="{{ asset($logo) }}" class="avatar" alt="">
                                    @else
                                        <div class="avatar grid place-items-center" style="color:var(--dm)"><i class="fas fa-building"></i></div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ $link('admin.offices.show', $office->id) ?? '#' }}" class="block text-[13px] font-bold text-slate-900 hover:text-[#303b97] truncate max-w-[190px]">
                                            {{ $office->company_name }}
                                        </a>
                                        <span class="text-[10.5px] font-bold text-slate-400 uppercase">
                                            <i class="fas fa-location-dot text-slate-300 mr-1"></i>{{ $office->city ?? 'Location N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <p class="text-[12px] font-semibold text-slate-700 truncate max-w-[190px]">{{ $office->email_address }}</p>
                                @if($office->phone_number)
                                    <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $office->phone_number }}</p>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="block text-[15px] font-black text-slate-900 num">{{ $office->owned_properties_count ?? 0 }}</span>
                                <span class="text-[9.5px] font-black text-slate-300 uppercase">listings</span>
                            </td>

                            <td class="text-center">
                                @if($office->is_verified)
                                    <span class="badge b-ok"><span class="dotlet"></span> Verified</span>
                                @else
                                    <span class="badge b-warn"><span class="dotlet"></span> Pending</span>
                                @endif
                            </td>

                            <td>
                                <div class="flex items-center gap-2.5">
                                    <div class="ring" style="--pct:{{ $expired ? 0 : $pct }};--rc:{{ $ring }}">
                                        <span>
                                            @if($expired)
                                                <i class="fas fa-xmark text-rose-500"></i>
                                            @elseif($days !== null)
                                                <span class="{{ $expiring ? 'text-rose-600' : 'text-slate-700' }}">{{ $days }}d</span>
                                            @else
                                                <i class="fas fa-minus text-slate-300"></i>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="badge {{ $active ? 'b-plan' : 'b-mute' }}">{{ strtoupper($office->current_plan ?? 'FREE') }}</span>
                                        <p class="text-[10.5px] font-bold mt-1 {{ $expired || $expiring ? 'text-rose-500' : 'text-slate-400' }}">
                                            {{ $end ? ($expired ? 'Ended ' : 'Ends ') . $end->format('d M Y') : 'No subscription' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td data-noexport>
                                <div class="flex items-center justify-end gap-1.5">
                                    @if(($expiring || $expired) && $link('admin.offices.edit', $office->id))
                                        <a href="{{ $link('admin.offices.edit', $office->id) }}" class="iact" style="background:#fef2f2;color:#e11d48;border-color:#fecdd3" title="Renew plan">
                                            <i class="fas fa-rotate"></i>
                                        </a>
                                    @endif
                                    @if($link('admin.offices.show', $office->id))
                                        <a href="{{ $link('admin.offices.show', $office->id) }}" class="iact" title="View"><i class="fas fa-eye"></i></a>
                                    @endif
                                    @if($link('admin.offices.edit', $office->id))
                                        <a href="{{ $link('admin.offices.edit', $office->id) }}" class="iact" title="Edit"><i class="fas fa-pen"></i></a>
                                    @endif
                                    @if(!$office->is_verified && $link('admin.offices.verify', $office->id))
                                        <form method="POST" action="{{ $link('admin.offices.verify', $office->id) }}" class="inline">
                                            @csrf
                                            <button class="iact good" title="Verify"><i class="fas fa-check"></i></button>
                                        </form>
                                    @endif
                                    @if($link('admin.offices.delete', $office->id))
                                        <form method="POST" action="{{ $link('admin.offices.delete', $office->id) }}" class="inline"
                                              onsubmit="return confirm('Delete {{ addslashes($office->company_name ?? '') }}?')">
                                            @csrf @method('DELETE')
                                            <button class="iact danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-building"></i>
                                    <h3>No offices match this view</h3>
                                    <p>Try a different search or clear the filters.</p>
                                    <a href="{{ route('admin.offices.index') }}" class="btn-ghost inline-flex"><i class="fas fa-rotate-left"></i> Clear filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($offices->hasPages())
            <div class="pager">{{ $offices->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- ══════════ MOBILE CARDS ══════════ --}}
    <div class="md:hidden">
        @forelse($offices as $office)
            @php
                extract($expiry($office));
                $logo = $office->logo ?? $office->profile_image;
            @endphp
            <div class="mcard" style="{{ $expiring ? 'border-color:#fecdd3' : '' }}">
                @if($expiring)
                    <div class="mcard-strip" style="background:#e11d48;color:#fff">
                        <span><i class="fas fa-hourglass-end mr-1"></i>{{ $days === 0 ? 'Expires today' : 'Expires in ' . $days . ' days' }}</span>
                        <a href="{{ $link('admin.offices.edit', $office->id) ?? '#' }}" class="underline underline-offset-2">RENEW</a>
                    </div>
                @elseif($expired)
                    <div class="mcard-strip" style="background:#1a1d2e;color:#fff">
                        <span><i class="fas fa-circle-xmark mr-1"></i> Subscription expired</span>
                        <a href="{{ $link('admin.offices.edit', $office->id) ?? '#' }}" class="underline underline-offset-2" style="color:var(--dm-gold)">REACTIVATE</a>
                    </div>
                @endif

                <div class="mcard-body">
                    <div class="flex items-start gap-3 mb-3">
                        @if($logo)
                            <img src="{{ asset($logo) }}" class="avatar !w-12 !h-12" alt="">
                        @else
                            <div class="avatar !w-12 !h-12 grid place-items-center" style="color:var(--dm)"><i class="fas fa-building text-lg"></i></div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ $link('admin.offices.show', $office->id) ?? '#' }}" class="text-[14px] font-black text-slate-900 truncate">{{ $office->company_name }}</a>
                                <span class="badge {{ $office->is_verified ? 'b-ok' : 'b-warn' }} shrink-0">{{ $office->is_verified ? 'Verified' : 'Pending' }}</span>
                            </div>
                            <p class="text-[11.5px] font-semibold text-slate-400 truncate mt-0.5">{{ $office->email_address }}</p>
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase mt-1">
                                {{ $office->city ?? 'N/A' }}
                                <span class="mx-1.5 text-slate-200">·</span>{{ $office->owned_properties_count ?? 0 }} listings
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3 mb-3">
                        <div class="ring !w-11 !h-11" style="--pct:{{ $expired ? 0 : $pct }};--rc:{{ $ring }}">
                            <span class="!w-8 !h-8 text-[10px]">
                                @if($expired)
                                    <i class="fas fa-xmark text-rose-500"></i>
                                @elseif($days !== null)
                                    <span class="{{ $expiring ? 'text-rose-600' : 'text-slate-700' }}">{{ $days }}d</span>
                                @else
                                    <i class="fas fa-minus text-slate-300"></i>
                                @endif
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="badge {{ $active ? 'b-plan' : 'b-mute' }}">{{ strtoupper($office->current_plan ?? 'FREE') }}</span>
                            <p class="text-[11px] font-bold mt-1 {{ $expired || $expiring ? 'text-rose-500' : 'text-slate-500' }}">
                                {{ $end ? ($expired ? 'Ended ' : 'Ends ') . $end->format('d M Y') : 'No active subscription' }}
                            </p>
                        </div>
                    </div>

                    <div class="mgrid" style="grid-template-columns:repeat({{ !$office->is_verified ? 4 : 3 }},1fr)">
                        <a href="{{ $link('admin.offices.show', $office->id) ?? '#' }}" class="mbtn mbtn-p"><i class="fas fa-eye text-[10px]"></i> View</a>
                        <a href="{{ $link('admin.offices.edit', $office->id) ?? '#' }}" class="mbtn mbtn-s"><i class="fas fa-pen text-[10px]"></i> Edit</a>
                        @if(!$office->is_verified && $link('admin.offices.verify', $office->id))
                            <form method="POST" action="{{ $link('admin.offices.verify', $office->id) }}">
                                @csrf
                                <button class="mbtn mbtn-g"><i class="fas fa-check text-[10px]"></i> Verify</button>
                            </form>
                        @endif
                        @if($link('admin.offices.delete', $office->id))
                            <form method="POST" action="{{ $link('admin.offices.delete', $office->id) }}" onsubmit="return confirm('Delete this office?')">
                                @csrf @method('DELETE')
                                <button class="mbtn mbtn-d"><i class="fas fa-trash text-[10px]"></i> Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card empty-state">
                <i class="fas fa-building"></i>
                <h3>No offices match this view</h3>
                <p>Try a different search or clear the filters.</p>
                <a href="{{ route('admin.offices.index') }}" class="btn-ghost inline-flex"><i class="fas fa-rotate-left"></i> Clear filters</a>
            </div>
        @endforelse

        @if($offices->hasPages())
            <div class="pt-2">{{ $offices->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

@endsection
