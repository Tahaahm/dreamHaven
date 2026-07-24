@extends('layouts.admin-layout')

@section('title', 'Properties')

@push('styles') @include('admin.partials.ui-kit') @endpush

@section('content')

@php
    use Illuminate\Support\Facades\Route as Rt;

    $link = fn($n, $p = []) => Rt::has($n) ? route($n, $p) : null;

    /* Unpack a property row into plain values */
    $read = function ($property) {
        $json = function ($value) {
            if (is_string($value)) { $decoded = json_decode($value, true); return is_array($decoded) ? $decoded : null; }
            return is_array($value) ? $value : null;
        };

        $name  = $json($property->name);
        $price = $json($property->price);
        $imgs  = $json($property->images);
        $type  = $json($property->type);
        $addr  = $json($property->address_details);
        $city  = $addr['city'] ?? null;

        $owner = 'Unassigned';
        if ($property->owner) {
            $owner = $property->owner->agent_name
                ?? $property->owner->company_name
                ?? $property->owner->username
                ?? 'Owner';
        }

        return [
            'title' => $name['en'] ?? $name['ar'] ?? $name['ku'] ?? (is_string($property->name) ? $property->name : 'Untitled listing'),
            'price' => (float) ($price['usd'] ?? $price['amount'] ?? (is_numeric($property->price) ? $property->price : 0)),
            'iqd'   => (float) ($price['iqd'] ?? 0),
            'image' => is_array($imgs) && count($imgs) ? reset($imgs) : null,
            'kind'  => $type['category'] ?? 'property',
            'city'  => is_array($city) ? ($city['en'] ?? 'Unknown') : ($city ?? 'Unknown'),
            'owner' => $owner,
            'role'  => class_basename($property->owner_type ?? ''),
        ];
    };

    $tone = fn($status) => match ($status) {
        'available' => ['b-ok', 'fa-circle-dot'],
        'pending'   => ['b-warn', 'fa-clock'],
        'sold'      => ['b-plan', 'fa-handshake'],
        'rented'    => ['b-plan', 'fa-key'],
        'rejected'  => ['b-bad', 'fa-ban'],
        default     => ['b-mute', 'fa-circle'],
    };

    $q          = request()->except(['page']);
    $hasFilters = request()->anyFilled(['search', 'status', 'listing_type', 'owner_type', 'sort']);
@endphp

<div class="max-w-[1500px] mx-auto">

    {{-- HEADER --}}
    <div class="page-head">
        <div>
            <p class="eyebrow mb-1.5">Inventory</p>
            <h1 class="page-ttl">Properties</h1>
            <p class="text-[13px] text-slate-500 font-semibold mt-1.5">
                Showing {{ $properties->firstItem() ?? 0 }}–{{ $properties->lastItem() ?? 0 }} of {{ number_format($properties->total()) }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="dmExportTable('#propsTable', 'properties')" class="btn-ghost"><i class="fas fa-download"></i> Export CSV</button>
            @if(($pendingCount ?? 0) > 0)
                <a href="{{ route('admin.properties.index', ['status' => 'pending']) }}" class="btn-solid" style="background:linear-gradient(135deg,#f59e0b,#f97316);box-shadow:0 10px 22px -12px rgba(245,158,11,.9)">
                    <i class="fas fa-hourglass-half"></i> {{ $pendingCount }} to approve
                </a>
            @endif
            @if($link('admin.properties.create'))
                <a href="{{ $link('admin.properties.create') }}" class="btn-solid"><i class="fas fa-plus"></i> Add listing</a>
            @endif
        </div>
    </div>

    {{-- STATS --}}
    <div class="stat-row">
        @php
            $cards = [
                ['Total listings', $stats['total'] ?? 0, 'fa-layer-group', '#94a3b8'],
                ['Live', $stats['active'] ?? 0, 'fa-circle-check', '#10b981'],
                ['Pending', $stats['pending'] ?? 0, 'fa-hourglass-half', '#f59e0b'],
                ['For sale', $stats['for_sale'] ?? 0, 'fa-tag', '#303b97'],
                ['For rent', $stats['for_rent'] ?? 0, 'fa-key', '#8b5cf6'],
            ];
        @endphp
        @foreach($cards as [$label, $value, $icon, $color])
            <div class="stat">
                <div class="flex items-center justify-between mb-2">
                    <span class="eyebrow">{{ $label }}</span>
                    <i class="fas {{ $icon }} text-[11px]" style="color:{{ $color }}"></i>
                </div>
                <b class="num">{{ number_format($value) }}</b>
            </div>
        @endforeach
    </div>

    {{-- SEARCH --}}
    <form method="GET" action="{{ route('admin.properties.index') }}" class="search-wrap" id="searchForm">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search listings by title…">
        @foreach(['status', 'listing_type', 'owner_type', 'sort'] as $keep)
            @if(request($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
        @endforeach
    </form>

    {{-- FILTER PILLS --}}
    <div class="pill-row mb-2">
        <a href="{{ route('admin.properties.index') }}" class="pill {{ !$hasFilters ? 'on' : '' }}">All</a>
        @php
            $statusPills = [
                ['available', 'Available', 'fa-circle-dot'],
                ['pending', 'Pending', 'fa-clock'],
                ['sold', 'Sold', 'fa-handshake'],
                ['rented', 'Rented', 'fa-key'],
                ['rejected', 'Rejected', 'fa-ban'],
            ];
        @endphp
        @foreach($statusPills as [$key, $label, $icon])
            <a href="{{ route('admin.properties.index', array_merge($q, ['status' => $key])) }}"
               class="pill {{ request('status') === $key ? ($key === 'rejected' ? 'on-alert' : 'on') : '' }}">
                <i class="fas {{ $icon }} text-[10px]"></i> {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="pill-row mb-5">
        <a href="{{ route('admin.properties.index', array_merge($q, ['listing_type' => 'sale'])) }}"
           class="pill {{ request('listing_type') === 'sale' ? 'on' : '' }}"><i class="fas fa-tag text-[10px]"></i> For sale</a>
        <a href="{{ route('admin.properties.index', array_merge($q, ['listing_type' => 'rent'])) }}"
           class="pill {{ request('listing_type') === 'rent' ? 'on' : '' }}"><i class="fas fa-key text-[10px]"></i> For rent</a>
        <a href="{{ route('admin.properties.index', array_merge($q, ['owner_type' => 'Agent'])) }}"
           class="pill {{ request('owner_type') === 'Agent' ? 'on' : '' }}"><i class="fas fa-user-tie text-[10px]"></i> By agents</a>
        <a href="{{ route('admin.properties.index', array_merge($q, ['owner_type' => 'RealEstateOffice'])) }}"
           class="pill {{ request('owner_type') === 'RealEstateOffice' ? 'on' : '' }}"><i class="fas fa-building text-[10px]"></i> By offices</a>

        <span class="w-px bg-slate-200 mx-1 shrink-0"></span>

        @php
            $sorts = [
                ['newest', 'Newest', 'fa-arrow-down-wide-short'],
                ['oldest', 'Oldest', 'fa-arrow-up-wide-short'],
                ['price_high', 'Price high', 'fa-dollar-sign'],
                ['views', 'Most viewed', 'fa-eye'],
            ];
        @endphp
        @foreach($sorts as [$key, $label, $icon])
            <a href="{{ route('admin.properties.index', array_merge($q, ['sort' => $key])) }}"
               class="pill {{ request('sort') === $key ? 'on' : '' }}"><i class="fas {{ $icon }} text-[10px]"></i> {{ $label }}</a>
        @endforeach

        @if($hasFilters)
            <a href="{{ route('admin.properties.index') }}" class="pill" style="background:#fef2f2;color:#b91c1c;border-color:#fecdd3"><i class="fas fa-xmark text-[10px]"></i> Clear</a>
        @endif
    </div>

    {{-- ══════════ DESKTOP TABLE ══════════ --}}
    <div class="card overflow-hidden hidden md:block" id="propsAnchor">
        <div class="overflow-x-auto">
            <table class="tbl" id="propsTable">
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Owner</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Price</th>
                        <th class="text-center">Views</th>
                        <th class="w-40 text-right" data-noexport>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($properties as $property)
                        @php
                            $p = $read($property);
                            [$badge, $icon] = $tone($property->status);
                            $isNew   = $property->created_at && $property->created_at->gt(now()->subDays(7));
                            $uniques = $property->unique_viewers_count ?? 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="relative shrink-0">
                                        @if($p['image'])
                                            <img src="{{ asset($p['image']) }}" class="avatar !w-14 !h-12 !rounded-xl" loading="lazy" alt="">
                                        @else
                                            <div class="avatar !w-14 !h-12 !rounded-xl grid place-items-center text-slate-300"><i class="fas fa-image"></i></div>
                                        @endif
                                        @if($property->is_boosted)
                                            <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-amber-500 grid place-items-center border-2 border-white" title="Boosted">
                                                <i class="fas fa-bolt text-white text-[8px]"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ $link('admin.properties.show', $property->id) ?? '#' }}" class="text-[13px] font-bold text-slate-900 hover:text-[#303b97] truncate max-w-[230px]">
                                                {{ $p['title'] }}
                                            </a>
                                            @if($isNew)
                                                <span class="badge b-ok !text-[9px] shrink-0">NEW</span>
                                            @endif
                                        </div>
                                        <p class="text-[10.5px] font-bold text-slate-400 uppercase mt-0.5 truncate">
                                            <i class="fas fa-location-dot text-slate-300 mr-1"></i>{{ $p['city'] }}
                                            <span class="mx-1 text-slate-200">·</span>{{ optional($property->created_at)->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($property->owner)
                                    <p class="text-[12px] font-bold text-slate-700 truncate max-w-[150px]">{{ $p['owner'] }}</p>
                                    <p class="text-[10px] font-black text-slate-400 uppercase mt-0.5">{{ $p['role'] }}</p>
                                @else
                                    <span class="badge b-bad">Owner removed</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge b-mute">{{ strtoupper($property->listing_type === 'sell' ? 'SALE' : $property->listing_type) }}</span>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">{{ $p['kind'] }}</p>
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $badge }}"><i class="fas {{ $icon }} text-[8px]"></i> {{ ucfirst($property->status) }}</span>
                            </td>

                            <td class="text-right">
                                <p class="text-[14px] font-black text-slate-900 num">${{ number_format($p['price']) }}</p>
                                @if($p['iqd'] > 0)
                                    <p class="text-[10.5px] font-bold text-slate-400 num">{{ number_format($p['iqd']) }} IQD</p>
                                @endif
                            </td>

                            <td class="text-center">
                                <p class="text-[13px] font-black text-slate-900 num">{{ number_format($property->views ?? 0) }}</p>
                                @if($uniques > 0)
                                    <p class="text-[10px] font-black text-emerald-600">{{ $uniques }} people</p>
                                @else
                                    <p class="text-[10px] font-bold text-slate-300 uppercase">hits</p>
                                @endif
                            </td>

                            <td data-noexport>
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($property->status === 'pending' && $link('admin.properties.approve', $property->id))
                                        <form method="POST" action="{{ $link('admin.properties.approve', $property->id) }}" class="inline">
                                            @csrf
                                            <button class="iact good" title="Approve"><i class="fas fa-check"></i></button>
                                        </form>
                                    @endif
                                    @if($property->status === 'pending' && $link('admin.properties.reject', $property->id))
                                        <form method="POST" action="{{ $link('admin.properties.reject', $property->id) }}" class="inline" onsubmit="return confirm('Reject this listing?')">
                                            @csrf
                                            <button class="iact danger" title="Reject"><i class="fas fa-xmark"></i></button>
                                        </form>
                                    @endif
                                    @if($link('admin.properties.show', $property->id))
                                        <a href="{{ $link('admin.properties.show', $property->id) }}" class="iact" title="View"><i class="fas fa-eye"></i></a>
                                    @endif
                                    @if($link('admin.properties.edit', $property->id))
                                        <a href="{{ $link('admin.properties.edit', $property->id) }}" class="iact" title="Edit"><i class="fas fa-pen"></i></a>
                                    @endif
                                    @if($link('admin.properties.delete', $property->id))
                                        <form method="POST" action="{{ $link('admin.properties.delete', $property->id) }}" class="inline" onsubmit="return confirm('Delete this listing permanently?')">
                                            @csrf @method('DELETE')
                                            <button class="iact danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-house-circle-xmark"></i>
                                    <h3>No listings match this view</h3>
                                    <p>Try a different search or clear the filters.</p>
                                    <a href="{{ route('admin.properties.index') }}" class="btn-ghost inline-flex"><i class="fas fa-rotate-left"></i> Clear filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($properties->hasPages())
            <div class="pager">{{ $properties->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- ══════════ MOBILE CARDS ══════════ --}}
    <div class="md:hidden">
        @forelse($properties as $property)
            @php
                $p = $read($property);
                [$badge, $icon] = $tone($property->status);
                $uniques = $property->unique_viewers_count ?? 0;
            @endphp
            <div class="mcard">
                @if($property->status === 'pending')
                    <div class="mcard-strip" style="background:#fffbeb;color:#b45309">
                        <span><i class="fas fa-clock mr-1"></i> Waiting for approval</span>
                        @if($link('admin.properties.approve', $property->id))
                            <form method="POST" action="{{ $link('admin.properties.approve', $property->id) }}">
                                @csrf
                                <button class="underline underline-offset-2">APPROVE</button>
                            </form>
                        @endif
                    </div>
                @endif

                <div class="relative">
                    @if($p['image'])
                        <img src="{{ asset($p['image']) }}" class="w-full h-40 object-cover" loading="lazy" alt="">
                    @else
                        <div class="w-full h-40 bg-slate-100 grid place-items-center text-slate-300"><i class="fas fa-image text-2xl"></i></div>
                    @endif

                    <div class="absolute top-3 left-3 flex gap-1.5">
                        <span class="badge {{ $badge }} shadow-sm"><i class="fas {{ $icon }} text-[8px]"></i> {{ ucfirst($property->status) }}</span>
                        @if($property->is_boosted)
                            <span class="badge shadow-sm" style="background:#f59e0b;color:#fff"><i class="fas fa-bolt text-[8px]"></i> Boosted</span>
                        @endif
                    </div>

                    <div class="absolute bottom-0 left-0 right-0 px-4 py-3" style="background:linear-gradient(transparent,rgba(15,19,40,.86))">
                        <p class="text-white text-[17px] font-black num leading-none">${{ number_format($p['price']) }}</p>
                        @if($p['iqd'] > 0)
                            <p class="text-white/60 text-[10.5px] font-bold num mt-0.5">{{ number_format($p['iqd']) }} IQD</p>
                        @endif
                    </div>
                </div>

                <div class="mcard-body">
                    <a href="{{ $link('admin.properties.show', $property->id) ?? '#' }}" class="block text-[14px] font-black text-slate-900 truncate">{{ $p['title'] }}</a>
                    <p class="text-[10.5px] font-bold text-slate-400 uppercase mt-1 mb-3 truncate">
                        <i class="fas fa-location-dot text-slate-300 mr-1"></i>{{ $p['city'] }}
                        <span class="mx-1.5 text-slate-200">·</span>{{ $property->listing_type === 'sell' ? 'sale' : $property->listing_type }}
                        <span class="mx-1.5 text-slate-200">·</span>{{ $p['owner'] }}
                    </p>

                    <div class="flex items-center gap-2 bg-slate-50 rounded-xl px-3 py-2.5 mb-3">
                        <i class="fas fa-eye text-[11px] text-slate-400"></i>
                        <p class="text-[11.5px] font-bold text-slate-600">
                            {{ number_format($property->views ?? 0) }} views
                            @if($uniques > 0)<span class="text-emerald-600"> · {{ $uniques }} signed-in people</span>@endif
                        </p>
                        <span class="ml-auto text-[10.5px] font-bold text-slate-400">{{ optional($property->created_at)->format('d M') }}</span>
                    </div>

                    <div class="mgrid" style="grid-template-columns:repeat(3,1fr)">
                        <a href="{{ $link('admin.properties.show', $property->id) ?? '#' }}" class="mbtn mbtn-p"><i class="fas fa-eye text-[10px]"></i> View</a>
                        <a href="{{ $link('admin.properties.edit', $property->id) ?? '#' }}" class="mbtn mbtn-s"><i class="fas fa-pen text-[10px]"></i> Edit</a>
                        @if($link('admin.properties.delete', $property->id))
                            <form method="POST" action="{{ $link('admin.properties.delete', $property->id) }}" onsubmit="return confirm('Delete this listing?')">
                                @csrf @method('DELETE')
                                <button class="mbtn mbtn-d"><i class="fas fa-trash text-[10px]"></i> Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card empty-state">
                <i class="fas fa-house-circle-xmark"></i>
                <h3>No listings match this view</h3>
                <p>Try a different search or clear the filters.</p>
                <a href="{{ route('admin.properties.index') }}" class="btn-ghost inline-flex"><i class="fas fa-rotate-left"></i> Clear filters</a>
            </div>
        @endforelse

        @if($properties->hasPages())
            <div class="pt-2">{{ $properties->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
/* Keep the reading position when paging or filtering */
document.addEventListener('DOMContentLoaded', function () {
    const KEY = 'dm_props_scroll';
    const anchor = document.getElementById('propsAnchor');

    if (sessionStorage.getItem(KEY) && anchor) {
        setTimeout(function () { anchor.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 80);
        sessionStorage.removeItem(KEY);
    }

    document.querySelectorAll('.pill, .pagination a, .pager a').forEach(function (el) {
        el.addEventListener('click', function () { sessionStorage.setItem(KEY, '1'); });
    });

    const form = document.getElementById('searchForm');
    if (form) form.addEventListener('submit', function () { sessionStorage.setItem(KEY, '1'); });
});
</script>
@endpush

@endsection
