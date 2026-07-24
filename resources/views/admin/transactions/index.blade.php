@extends('layouts.admin-layout')

@section('title', 'Transactions')

@push('styles') @include('admin.partials.ui-kit') @endpush

@section('content')

@php
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Route as Rt;

    $link = fn($n, $p = []) => Rt::has($n) ? route($n, $p) : null;

    $t = Cache::remember('admin.tx.stats', 120, function () {
        $safe = function ($cb, $fb = 0) { try { return $cb(); } catch (\Throwable $e) { return $fb; } };
        $M = \App\Models\Transaction::class;

        $months = [];
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonthsNoOverflow($i);
            $months[] = $m->format('M');
            $series[] = (float) $safe(fn() => $M::where('status', 'completed')
                ->whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->sum('amount_usd'));
        }

        return [
            'usd'       => (float) $safe(fn() => $M::where('status', 'completed')->sum('amount_usd')),
            'iqd'       => (float) $safe(fn() => $M::where('status', 'completed')->sum('amount_iqd')),
            'done'      => (int) $safe(fn() => $M::where('status', 'completed')->count()),
            'pending'   => (int) $safe(fn() => $M::where('status', 'pending')->count()),
            'cancelled' => (int) $safe(fn() => $M::where('status', 'cancelled')->count()),
            'month_usd' => (float) $safe(fn() => $M::where('status', 'completed')->where('created_at', '>=', now()->startOfMonth())->sum('amount_usd')),
            'months'    => $months,
            'series'    => $series,
        ];
    });

    $avgDeal = $t['done'] > 0 ? $t['usd'] / $t['done'] : 0;
    $status  = request('status');
@endphp

<div class="max-w-[1500px] mx-auto">

    {{-- HEADER --}}
    <div class="page-head">
        <div>
            <p class="eyebrow mb-1.5">Finance</p>
            <h1 class="page-ttl">Transactions</h1>
            <p class="text-[13px] text-slate-500 font-semibold mt-1.5">Sales, commissions and payment status across the platform.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="dmExportTable('#txTable', 'transactions')" class="btn-ghost"><i class="fas fa-download"></i> Export CSV</button>
        </div>
    </div>

    {{-- STATS --}}
    <div class="stat-row cols-4">
        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="eyebrow">Revenue (USD)</span>
                <i class="fas fa-dollar-sign text-emerald-500 text-[11px]"></i>
            </div>
            <b class="num">${{ number_format($t['usd']) }}</b>
            <p class="text-[10.5px] font-bold text-slate-400">${{ number_format($t['month_usd']) }} this month</p>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="eyebrow">Revenue (IQD)</span>
                <i class="fas fa-coins text-[11px]" style="color:var(--dm-gold)"></i>
            </div>
            <b class="num">{{ number_format($t['iqd']) }}</b>
            <p class="text-[10.5px] font-bold text-slate-400">completed deals only</p>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="eyebrow">Closed deals</span>
                <i class="fas fa-handshake text-[11px]" style="color:var(--dm)"></i>
            </div>
            <b class="num">{{ number_format($t['done']) }}</b>
            <p class="text-[10.5px] font-bold text-slate-400">avg ${{ number_format($avgDeal) }} each</p>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="eyebrow">Needs review</span>
                @if($t['pending'] > 0)
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                @endif
            </div>
            <b class="num {{ $t['pending'] > 0 ? 'text-amber-600' : '' }}">{{ number_format($t['pending']) }}</b>
            <p class="text-[10.5px] font-bold text-slate-400">{{ $t['cancelled'] }} cancelled</p>
        </div>
    </div>

    {{-- TREND --}}
    <div class="card mb-5 overflow-hidden">
        <div class="card-hd">
            <div><p class="eyebrow mb-1">Last 6 months</p><h3 class="card-ttl">Completed revenue</h3></div>
            <span class="text-[10.5px] font-bold text-slate-400">USD</span>
        </div>
        <div class="p-2 md:p-4"><div id="txChart"></div></div>
    </div>

    {{-- SEARCH + FILTERS --}}
    <form method="GET" action="{{ route('admin.transactions.index') }}" class="search-wrap">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by reference or party…">
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
    </form>

    <div class="pill-row mb-5">
        @php
            $txFilters = [
                ['', 'All', 'fa-layer-group'],
                ['completed', 'Completed', 'fa-circle-check'],
                ['pending', 'Pending', 'fa-hourglass-half'],
                ['in_progress', 'In progress', 'fa-spinner'],
                ['cancelled', 'Cancelled', 'fa-circle-xmark'],
            ];
        @endphp
        @foreach($txFilters as [$key, $label, $icon])
            <a href="{{ $key === '' ? route('admin.transactions.index') : route('admin.transactions.index', ['status' => $key]) }}"
               class="pill {{ $status === $key || ($key === '' && !$status) ? ($key === 'cancelled' ? 'on-alert' : 'on') : '' }}">
                <i class="fas {{ $icon }} text-[10px]"></i> {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- ══════════ DESKTOP TABLE ══════════ --}}
    <div class="card overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="tbl" id="txTable">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Property</th>
                        <th>Buyer / Seller</th>
                        <th>Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Date</th>
                        <th class="w-32" data-noexport></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        @php
                            $prop = $tx->property;
                            $pName = $prop ? (is_array($prop->name) ? ($prop->name['en'] ?? 'Property') : (json_decode($prop->name, true)['en'] ?? 'Property')) : null;
                            $imgs  = $prop ? (is_array($prop->images) ? $prop->images : json_decode($prop->images ?? '[]', true)) : [];
                            $img   = is_array($imgs) && count($imgs) ? $imgs[0] : null;
                            $tone  = match($tx->status) {
                                'completed'   => 'b-ok',
                                'pending'     => 'b-warn',
                                'cancelled'   => 'b-bad',
                                'in_progress' => 'b-plan',
                                default       => 'b-mute',
                            };
                        @endphp
                        <tr>
                            <td>
                                <span class="text-[11px] font-mono font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-lg">
                                    {{ $tx->transaction_reference ?? \Illuminate\Support\Str::limit($tx->id, 8, '') }}
                                </span>
                            </td>

                            <td>
                                @if($prop)
                                    <div class="flex items-center gap-3">
                                        @if($img)
                                            <img src="{{ asset($img) }}" class="avatar !w-10 !h-10" alt="">
                                        @else
                                            <div class="avatar !w-10 !h-10 grid place-items-center text-slate-300"><i class="fas fa-home text-[11px]"></i></div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-[12.5px] font-bold text-slate-900 truncate max-w-[170px]">{{ $pName }}</p>
                                            <p class="text-[10px] font-black text-slate-400 uppercase">{{ $prop->listing_type === 'sell' ? 'sale' : $prop->listing_type }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[11.5px] font-semibold text-slate-300 italic">Listing removed</span>
                                @endif
                            </td>

                            <td>
                                <p class="text-[12px] font-bold text-slate-700"><i class="fas fa-arrow-down text-emerald-500 text-[9px] mr-1.5"></i>{{ optional($tx->buyer)->username ?? 'Unknown' }}</p>
                                <p class="text-[12px] font-bold text-slate-400 mt-0.5"><i class="fas fa-arrow-up text-rose-500 text-[9px] mr-1.5"></i>{{ optional($tx->seller)->username ?? 'Unknown' }}</p>
                            </td>

                            <td>
                                @if($tx->amount_usd > 0)
                                    <p class="text-[13.5px] font-black text-slate-900 num">${{ number_format($tx->amount_usd) }}</p>
                                @endif
                                @if($tx->amount_iqd > 0)
                                    <p class="text-[11px] font-bold text-slate-500 num">{{ number_format($tx->amount_iqd) }} IQD</p>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $tone }}">{{ strtoupper(str_replace('_', ' ', $tx->status)) }}</span>
                            </td>

                            <td class="text-right">
                                <p class="text-[12px] font-bold text-slate-600">{{ optional($tx->created_at)->format('d M Y') }}</p>
                                <p class="text-[10.5px] font-semibold text-slate-400">{{ optional($tx->created_at)->diffForHumans(null, true) }} ago</p>
                            </td>

                            <td data-noexport>
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($tx->status === 'pending' && $link('admin.transactions.approve', $tx->id))
                                        <form method="POST" action="{{ $link('admin.transactions.approve', $tx->id) }}" class="inline">
                                            @csrf
                                            <button class="iact good" title="Approve"><i class="fas fa-check"></i></button>
                                        </form>
                                    @endif
                                    @if($tx->status === 'pending' && $link('admin.transactions.reject', $tx->id))
                                        <form method="POST" action="{{ $link('admin.transactions.reject', $tx->id) }}" class="inline" onsubmit="return confirm('Reject this transaction?')">
                                            @csrf
                                            <button class="iact danger" title="Reject"><i class="fas fa-xmark"></i></button>
                                        </form>
                                    @endif
                                    @if($link('admin.transactions.show', $tx->id))
                                        <a href="{{ $link('admin.transactions.show', $tx->id) }}" class="iact" title="Open"><i class="fas fa-chevron-right"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-receipt"></i>
                                    <h3>No transactions here</h3>
                                    <p>Deals appear once a sale or rental is recorded.</p>
                                    <a href="{{ route('admin.transactions.index') }}" class="btn-ghost inline-flex"><i class="fas fa-rotate-left"></i> Clear filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="pager">{{ $transactions->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- ══════════ MOBILE CARDS ══════════ --}}
    <div class="md:hidden">
        @forelse($transactions as $tx)
            @php
                $prop  = $tx->property;
                $pName = $prop ? (is_array($prop->name) ? ($prop->name['en'] ?? 'Property') : (json_decode($prop->name, true)['en'] ?? 'Property')) : 'Listing removed';
                $tone  = match($tx->status) {
                    'completed'   => ['#ecfdf5', '#047857'],
                    'pending'     => ['#fffbeb', '#b45309'],
                    'cancelled'   => ['#fef2f2', '#b91c1c'],
                    default       => ['#f1f5f9', '#475569'],
                };
            @endphp
            <div class="mcard">
                <div class="mcard-strip" style="background:{{ $tone[0] }};color:{{ $tone[1] }}">
                    <span>{{ str_replace('_', ' ', $tx->status) }}</span>
                    <span class="font-mono">{{ $tx->transaction_reference ?? \Illuminate\Support\Str::limit($tx->id, 8, '') }}</span>
                </div>

                <div class="mcard-body">
                    <p class="text-[14px] font-black text-slate-900 truncate mb-1">{{ $pName }}</p>
                    <p class="text-[11px] font-bold text-slate-400 uppercase mb-3">{{ optional($tx->created_at)->format('d M Y') }}</p>

                    <div class="flex items-center justify-between bg-slate-50 rounded-xl px-3 py-3 mb-3">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-wide mb-0.5">Amount</p>
                            @if($tx->amount_usd > 0)
                                <p class="text-[17px] font-black text-slate-900 num">${{ number_format($tx->amount_usd) }}</p>
                            @endif
                            @if($tx->amount_iqd > 0)
                                <p class="text-[11px] font-bold text-slate-500 num">{{ number_format($tx->amount_iqd) }} IQD</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] font-bold text-slate-700"><i class="fas fa-arrow-down text-emerald-500 text-[9px] mr-1"></i>{{ optional($tx->buyer)->username ?? 'Unknown' }}</p>
                            <p class="text-[11px] font-bold text-slate-400 mt-0.5"><i class="fas fa-arrow-up text-rose-500 text-[9px] mr-1"></i>{{ optional($tx->seller)->username ?? 'Unknown' }}</p>
                        </div>
                    </div>

                    <div class="mgrid" style="grid-template-columns:repeat({{ $tx->status === 'pending' && $link('admin.transactions.approve', $tx->id) ? 3 : 1 }},1fr)">
                        <a href="{{ $link('admin.transactions.show', $tx->id) ?? '#' }}" class="mbtn mbtn-p"><i class="fas fa-eye text-[10px]"></i> Open</a>
                        @if($tx->status === 'pending' && $link('admin.transactions.approve', $tx->id))
                            <form method="POST" action="{{ $link('admin.transactions.approve', $tx->id) }}">
                                @csrf
                                <button class="mbtn mbtn-g"><i class="fas fa-check text-[10px]"></i> Approve</button>
                            </form>
                            @if($link('admin.transactions.reject', $tx->id))
                                <form method="POST" action="{{ $link('admin.transactions.reject', $tx->id) }}" onsubmit="return confirm('Reject this transaction?')">
                                    @csrf
                                    <button class="mbtn mbtn-d"><i class="fas fa-xmark text-[10px]"></i> Reject</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card empty-state">
                <i class="fas fa-receipt"></i>
                <h3>No transactions here</h3>
                <p>Deals appear once a sale or rental is recorded.</p>
            </div>
        @endforelse

        @if($transactions->hasPages())
            <div class="pt-2">{{ $transactions->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    if (typeof ApexCharts === 'undefined') return;
    const el = document.querySelector('#txChart');
    if (!el) return;

    const mobile = window.matchMedia('(max-width: 767px)').matches;

    new ApexCharts(el, {
        series: [{ name: 'Completed', data: @json($t['series']) }],
        chart: { type: 'bar', height: mobile ? 190 : 240, fontFamily: 'inherit', toolbar: { show: false } },
        colors: ['#303b97'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '48%' } },
        dataLabels: { enabled: false },
        xaxis: {
            categories: @json($t['months']),
            axisBorder: { show: false }, axisTicks: { show: false },
            labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 700 } }
        },
        yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10.5px', fontWeight: 700 },
                 formatter: v => '$' + new Intl.NumberFormat('en-US', { notation: 'compact' }).format(v) } },
        grid: { borderColor: '#f1f2f7', strokeDashArray: 5 },
        tooltip: { y: { formatter: v => '$' + new Intl.NumberFormat('en-US').format(v) } }
    }).render();
})();
</script>
@endpush

@endsection
