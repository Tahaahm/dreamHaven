@extends('layouts.admin-layout')

@section('title', 'Agents')

@push('styles') @include('admin.partials.ui-kit') @endpush

@section('content')

@php
    use Illuminate\Support\Facades\Route as Rt;

    $link = fn($n, $p = []) => Rt::has($n) ? route($n, $p) : null;

    /* Expiry maths, shared by table and cards */
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

    $radar = $agents->filter(function ($a) {
        $sub = $a->subscription;
        if (!$sub || !$sub->end_date || $sub->status !== 'active') return false;
        $d = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($sub->end_date)->startOfDay(), false);
        return $d >= 0 && $d <= 14;
    });

    $hasFilters = request()->hasAny(['search', 'status', 'type', 'expiry']);
@endphp

<div class="max-w-[1500px] mx-auto">

    {{-- HEADER --}}
    <div class="page-head">
        <div>
            <p class="eyebrow mb-1.5">Dream Mulk network</p>
            <h1 class="page-ttl">Agents</h1>
            <p class="text-[13px] text-slate-500 font-semibold mt-1.5">
                {{ number_format($agents->total()) }} agent{{ $agents->total() === 1 ? '' : 's' }} in this view
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="dmExportTable('#agentsTable', 'agents')" class="btn-ghost"><i class="fas fa-download"></i> Export CSV</button>
            @if(($pendingCount ?? 0) > 0)
                <a href="{{ route('admin.agents.index', ['status' => 'pending']) }}" class="btn-solid" style="background:linear-gradient(135deg,#f59e0b,#f97316);box-shadow:0 10px 22px -12px rgba(245,158,11,.9)">
                    <i class="fas fa-hourglass-half"></i> {{ $pendingCount }} to review
                </a>
            @endif
        </div>
    </div>

    {{-- WEEKLY BRIEFING (kept as-is) --}}
    @if(isset($briefing) && view()->exists('admin.partials.weekly-briefing'))
        @include('admin.partials.weekly-briefing', [
            'briefing'   => $briefing,
            'editRoute'  => 'admin.agents.edit',
            'showRoute'  => 'admin.agents.show',
            'entityIcon' => 'fa-user',
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
                @foreach($radar as $ra)
                    @php
                        $rEnd  = \Carbon\Carbon::parse($ra->subscription->end_date);
                        $rDays = (int) now()->startOfDay()->diffInDays($rEnd->startOfDay(), false);
                    @endphp
                    <a href="{{ $link('admin.agents.edit', $ra->id) ?? '#' }}" class="radar-card">
                        <div class="flex items-center gap-2.5 mb-2.5">
                            @if($ra->profile_image)
                                <img src="{{ asset($ra->profile_image) }}" class="w-9 h-9 rounded-xl object-cover bg-white/10" alt="">
                            @else
                                <div class="w-9 h-9 rounded-xl bg-white/15 grid place-items-center text-[11px] font-black text-white/80">
                                    {{ strtoupper(substr($ra->agent_name ?? 'A', 0, 2)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-white text-[12px] font-bold truncate">{{ $ra->agent_name }}</p>
                                <p class="text-white/40 text-[9.5px] font-bold uppercase tracking-wide">{{ $ra->current_plan ?? 'plan' }}</p>
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
                ['Total agents', $stats['total'] ?? 0, 'fa-users', '#94a3b8'],
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
    <form method="GET" action="{{ route('admin.agents.index') }}" class="search-wrap">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search agents by name, email or phone…">
    </form>

    <div class="pill-row mb-5">
        <a href="{{ route('admin.agents.index') }}" class="pill {{ !$hasFilters ? 'on' : '' }}">All</a>
        <a href="{{ route('admin.agents.index', ['status' => 'verified']) }}" class="pill {{ request('status') === 'verified' ? 'on' : '' }}"><i class="fas fa-shield-halved text-[10px]"></i> Verified</a>
        <a href="{{ route('admin.agents.index', ['status' => 'pending']) }}" class="pill {{ request('status') === 'pending' ? 'on' : '' }}"><i class="fas fa-hourglass-half text-[10px]"></i> Pending</a>
        <a href="{{ route('admin.agents.index', ['expiry' => 'expiring']) }}" class="pill {{ request('expiry') === 'expiring' ? 'on-alert' : '' }}"><i class="fas fa-hourglass-end text-[10px]"></i> Expiring ≤7d</a>
        <a href="{{ route('admin.agents.index', ['expiry' => 'expired']) }}" class="pill {{ request('expiry') === 'expired' ? 'on-alert' : '' }}"><i class="fas fa-circle-xmark text-[10px]"></i> Expired</a>
        <a href="{{ route('admin.agents.index', ['expiry' => 'active']) }}" class="pill {{ request('expiry') === 'active' ? 'on' : '' }}"><i class="fas fa-crown text-[10px]"></i> Active plan</a>
        <a href="{{ route('admin.agents.index', ['type' => 'independent']) }}" class="pill {{ request('type') === 'independent' ? 'on' : '' }}">Independent</a>
        <a href="{{ route('admin.agents.index', ['type' => 'company']) }}" class="pill {{ request('type') === 'company' ? 'on' : '' }}"><i class="fas fa-building text-[10px]"></i> Company</a>
        @if($hasFilters)
            <a href="{{ route('admin.agents.index') }}" class="pill" style="background:#fef2f2;color:#b91c1c;border-color:#fecdd3"><i class="fas fa-xmark text-[10px]"></i> Clear</a>
        @endif
    </div>

    {{-- ══════════ DESKTOP TABLE ══════════ --}}
    <div class="card overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="tbl" id="agentsTable">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Contact</th>
                        <th class="text-center">Listings</th>
                        <th class="text-center">Status</th>
                        <th>Subscription</th>
                        <th class="w-44 text-right" data-noexport>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                        @php extract($expiry($agent)); @endphp
                        <tr style="{{ $expiring ? 'background:#fff5f6' : ($expired ? 'background:#fafbfd' : '') }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="relative shrink-0">
                                        @if($agent->profile_image)
                                            <img src="{{ asset($agent->profile_image) }}" class="avatar" alt="">
                                        @else
                                            <div class="avatar avatar-fb">{{ strtoupper(substr($agent->agent_name ?? 'A', 0, 2)) }}</div>
                                        @endif
                                        @if($agent->type === 'company')
                                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white grid place-items-center" style="background:var(--dm)" title="Company">
                                                <i class="fas fa-building text-[6px] text-white"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ $link('admin.agents.show', $agent->id) ?? '#' }}" class="block text-[13px] font-bold text-slate-900 hover:text-[#303b97] truncate max-w-[170px]">
                                            {{ $agent->agent_name }}
                                        </a>
                                        <span class="text-[10.5px] font-bold text-slate-400 uppercase">
                                            <i class="fas fa-location-dot text-slate-300 mr-1"></i>{{ $agent->city ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <p class="text-[12px] font-semibold text-slate-700 truncate max-w-[180px]">{{ $agent->primary_email }}</p>
                                @if($agent->primary_phone)
                                    <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $agent->primary_phone }}</p>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="block text-[15px] font-black text-slate-900 num">{{ $agent->properties_count ?? 0 }}</span>
                                <span class="text-[10px] font-black text-amber-500">{{ number_format($agent->overall_rating ?? 0, 1) }} ★</span>
                            </td>

                            <td class="text-center">
                                @if($agent->is_verified)
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
                                        <span class="badge {{ $active ? 'b-plan' : 'b-mute' }}">{{ strtoupper($agent->current_plan ?? 'FREE') }}</span>
                                        <p class="text-[10.5px] font-bold mt-1 {{ $expired || $expiring ? 'text-rose-500' : 'text-slate-400' }}">
                                            {{ $end ? ($expired ? 'Ended ' : 'Ends ') . $end->format('d M Y') : 'No subscription' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td data-noexport>
                                <div class="flex items-center justify-end gap-1.5">
                                    @if(($expiring || $expired) && $link('admin.agents.edit', $agent->id))
                                        <a href="{{ $link('admin.agents.edit', $agent->id) }}" class="iact" style="background:#fef2f2;color:#e11d48;border-color:#fecdd3" title="Renew plan">
                                            <i class="fas fa-rotate"></i>
                                        </a>
                                    @endif
                                    @if($link('admin.agents.show', $agent->id))
                                        <a href="{{ $link('admin.agents.show', $agent->id) }}" class="iact" title="View"><i class="fas fa-eye"></i></a>
                                    @endif
                                    @if($link('admin.agents.edit', $agent->id))
                                        <a href="{{ $link('admin.agents.edit', $agent->id) }}" class="iact" title="Edit"><i class="fas fa-pen"></i></a>
                                    @endif
                                    @if(!$agent->is_verified && $link('admin.agents.verify', $agent->id))
                                        <form method="POST" action="{{ $link('admin.agents.verify', $agent->id) }}" class="inline">
                                            @csrf
                                            <button class="iact good" title="Approve"><i class="fas fa-check"></i></button>
                                        </form>
                                    @endif
                                    @if($link('admin.agents.delete', $agent->id))
                                        <form method="POST" action="{{ $link('admin.agents.delete', $agent->id) }}" class="inline"
                                              onsubmit="return confirm('Delete {{ addslashes($agent->agent_name ?? '') }} and their listings?')">
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
                                    <i class="fas fa-user-slash"></i>
                                    <h3>No agents match this view</h3>
                                    <p>Try a different search or clear the filters.</p>
                                    <a href="{{ route('admin.agents.index') }}" class="btn-ghost inline-flex"><i class="fas fa-rotate-left"></i> Clear filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($agents->hasPages())
            <div class="pager">{{ $agents->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- ══════════ MOBILE CARDS ══════════ --}}
    <div class="md:hidden">
        @forelse($agents as $agent)
            @php extract($expiry($agent)); @endphp
            <div class="mcard" style="{{ $expiring ? 'border-color:#fecdd3' : '' }}">
                @if($expiring)
                    <div class="mcard-strip" style="background:#e11d48;color:#fff">
                        <span><i class="fas fa-hourglass-end mr-1"></i>{{ $days === 0 ? 'Expires today' : 'Expires in ' . $days . ' days' }}</span>
                        <a href="{{ $link('admin.agents.edit', $agent->id) ?? '#' }}" class="underline underline-offset-2">RENEW</a>
                    </div>
                @elseif($expired)
                    <div class="mcard-strip" style="background:#1a1d2e;color:#fff">
                        <span><i class="fas fa-circle-xmark mr-1"></i> Subscription expired</span>
                        <a href="{{ $link('admin.agents.edit', $agent->id) ?? '#' }}" class="underline underline-offset-2" style="color:var(--dm-gold)">REACTIVATE</a>
                    </div>
                @endif

                <div class="mcard-body">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="relative shrink-0">
                            @if($agent->profile_image)
                                <img src="{{ asset($agent->profile_image) }}" class="avatar !w-12 !h-12" alt="">
                            @else
                                <div class="avatar avatar-fb !w-12 !h-12 text-sm">{{ strtoupper(substr($agent->agent_name ?? 'A', 0, 2)) }}</div>
                            @endif
                            @if($agent->type === 'company')
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white grid place-items-center" style="background:var(--dm)">
                                    <i class="fas fa-building text-[6px] text-white"></i>
                                </span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ $link('admin.agents.show', $agent->id) ?? '#' }}" class="text-[14px] font-black text-slate-900 truncate">{{ $agent->agent_name }}</a>
                                <span class="badge {{ $agent->is_verified ? 'b-ok' : 'b-warn' }} shrink-0">{{ $agent->is_verified ? 'Verified' : 'Pending' }}</span>
                            </div>
                            <p class="text-[11.5px] font-semibold text-slate-400 truncate mt-0.5">{{ $agent->primary_email }}</p>
                            <p class="text-[10.5px] font-bold text-slate-400 uppercase mt-1">
                                {{ $agent->city ?? 'N/A' }}
                                <span class="mx-1.5 text-slate-200">·</span>{{ $agent->properties_count ?? 0 }} listings
                                <span class="mx-1.5 text-slate-200">·</span><span class="text-amber-500">{{ number_format($agent->overall_rating ?? 0, 1) }} ★</span>
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
                            <span class="badge {{ $active ? 'b-plan' : 'b-mute' }}">{{ strtoupper($agent->current_plan ?? 'FREE') }}</span>
                            <p class="text-[11px] font-bold mt-1 {{ $expired || $expiring ? 'text-rose-500' : 'text-slate-500' }}">
                                {{ $end ? ($expired ? 'Ended ' : 'Ends ') . $end->format('d M Y') : 'No active subscription' }}
                            </p>
                        </div>
                    </div>

                    <div class="mgrid" style="grid-template-columns:repeat({{ !$agent->is_verified ? 4 : 3 }},1fr)">
                        <a href="{{ $link('admin.agents.show', $agent->id) ?? '#' }}" class="mbtn mbtn-p"><i class="fas fa-eye text-[10px]"></i> View</a>
                        <a href="{{ $link('admin.agents.edit', $agent->id) ?? '#' }}" class="mbtn mbtn-s"><i class="fas fa-pen text-[10px]"></i> Edit</a>
                        @if(!$agent->is_verified && $link('admin.agents.verify', $agent->id))
                            <form method="POST" action="{{ $link('admin.agents.verify', $agent->id) }}">
                                @csrf
                                <button class="mbtn mbtn-g"><i class="fas fa-check text-[10px]"></i> Approve</button>
                            </form>
                        @endif
                        @if($link('admin.agents.delete', $agent->id))
                            <form method="POST" action="{{ $link('admin.agents.delete', $agent->id) }}" onsubmit="return confirm('Delete this agent?')">
                                @csrf @method('DELETE')
                                <button class="mbtn mbtn-d"><i class="fas fa-trash text-[10px]"></i> Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card empty-state">
                <i class="fas fa-user-slash"></i>
                <h3>No agents match this view</h3>
                <p>Try a different search or clear the filters.</p>
                <a href="{{ route('admin.agents.index') }}" class="btn-ghost inline-flex"><i class="fas fa-rotate-left"></i> Clear filters</a>
            </div>
        @endforelse

        @if($agents->hasPages())
            <div class="pt-2">{{ $agents->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

@endsection
