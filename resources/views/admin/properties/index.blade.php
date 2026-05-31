@extends('layouts.admin-layout')

@section('title', 'Properties Directory')

@section('content')

<div class="min-h-screen bg-slate-50">
<div class="max-w-[1500px] mx-auto px-6 py-8">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-8 h-8 rounded-lg bg-violet-600 flex items-center justify-center">
                    <i class="fas fa-city text-white text-sm"></i>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Properties</h1>
            </div>
            <p class="text-slate-400 text-sm ml-11">Manage listings, approvals &amp; inventory</p>
        </div>
        <div class="flex items-center gap-3">
            @if(($pendingCount ?? 0) > 0)
            <a href="{{ route('admin.properties.index', ['status' => 'pending']) }}"
               class="relative flex items-center gap-2 px-4 py-2 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-xs font-bold hover:bg-amber-100 transition">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-400"></span>
                </span>
                {{ $pendingCount }} pending approval
            </a>
            @endif
        </div>
    </div>

    {{-- STAT CARDS --}}
    @php
        $statCards = [
            ['label' => 'Total',    'value' => $stats['total']    ?? 0, 'icon' => 'fa-layer-group',    'iconBg' => 'bg-slate-100',    'iconColor' => 'text-slate-600'],
            ['label' => 'Active',   'value' => $stats['active']   ?? 0, 'icon' => 'fa-check-circle',   'iconBg' => 'bg-emerald-50',   'iconColor' => 'text-emerald-600'],
            ['label' => 'Pending',  'value' => $stats['pending']  ?? 0, 'icon' => 'fa-hourglass-half', 'iconBg' => 'bg-amber-50',     'iconColor' => 'text-amber-600'],
            ['label' => 'For Sale', 'value' => $stats['for_sale'] ?? 0, 'icon' => 'fa-tag',            'iconBg' => 'bg-blue-50',      'iconColor' => 'text-blue-600'],
            ['label' => 'For Rent', 'value' => $stats['for_rent'] ?? 0, 'icon' => 'fa-key',            'iconBg' => 'bg-violet-50',    'iconColor' => 'text-violet-600'],
        ];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        @foreach($statCards as $s)
        <div class="bg-white border border-slate-200 rounded-2xl p-4 hover:border-slate-300 hover:shadow-sm transition-all">
            <div class="w-8 h-8 rounded-lg {{ $s['iconBg'] }} {{ $s['iconColor'] }} flex items-center justify-center mb-3">
                <i class="fas {{ $s['icon'] }} text-xs"></i>
            </div>
            <p class="text-xl font-black text-slate-900">{{ number_format($s['value']) }}</p>
            <p class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-2 mb-5 flex flex-col lg:flex-row items-stretch lg:items-center gap-2 shadow-sm">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <form id="search-form" method="GET" action="{{ route('admin.properties.index') }}">
                @if(request('status'))       <input type="hidden" name="status"       value="{{ request('status') }}">       @endif
                @if(request('listing_type')) <input type="hidden" name="listing_type" value="{{ request('listing_type') }}"> @endif
                @if(request('owner_type'))   <input type="hidden" name="owner_type"   value="{{ request('owner_type') }}">   @endif
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full bg-transparent pl-9 pr-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 border-none focus:ring-0 focus:outline-none"
                       placeholder="Search title, ID, reference…">
            </form>
        </div>

        <div class="h-px lg:h-6 w-full lg:w-px bg-slate-200"></div>

        <div class="flex items-center gap-2 flex-wrap lg:flex-nowrap px-1">
            @php $selBase = 'bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-600 rounded-xl px-3 py-2 focus:ring-0 focus:border-violet-400 cursor-pointer hover:border-slate-300 transition-colors'; @endphp

            <select id="filter-status" class="{{ $selBase }} min-w-[130px]">
                <option value="{{ route('admin.properties.index', array_merge(request()->except('status','page'), [])) }}">All statuses</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('status','page'), ['status'=>'available'])) }}" {{ request('status')=='available'?'selected':'' }}>Available</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('status','page'), ['status'=>'pending'])) }}"   {{ request('status')=='pending'  ?'selected':'' }}>Pending</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('status','page'), ['status'=>'sold'])) }}"      {{ request('status')=='sold'     ?'selected':'' }}>Sold</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('status','page'), ['status'=>'rented'])) }}"    {{ request('status')=='rented'   ?'selected':'' }}>Rented</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('status','page'), ['status'=>'rejected'])) }}"  {{ request('status')=='rejected' ?'selected':'' }}>Rejected</option>
            </select>

            <select id="filter-type" class="{{ $selBase }} min-w-[120px]">
                <option value="{{ route('admin.properties.index', array_merge(request()->except('listing_type','page'), [])) }}">All types</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('listing_type','page'), ['listing_type'=>'sale'])) }}" {{ request('listing_type')=='sale'?'selected':'' }}>For sale</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('listing_type','page'), ['listing_type'=>'rent'])) }}" {{ request('listing_type')=='rent'?'selected':'' }}>For rent</option>
            </select>

            <select id="filter-owner" class="{{ $selBase }} min-w-[120px]">
                <option value="{{ route('admin.properties.index', array_merge(request()->except('owner_type','page'), [])) }}">All owners</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('owner_type','page'), ['owner_type'=>'Agent'])) }}"            {{ request('owner_type')=='Agent'           ?'selected':'' }}>Agent</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('owner_type','page'), ['owner_type'=>'RealEstateOffice'])) }}" {{ request('owner_type')=='RealEstateOffice'?'selected':'' }}>Office</option>
            </select>

            @if(request()->anyFilled(['search','status','listing_type','owner_type']))
            <a href="{{ route('admin.properties.index') }}"
               class="flex items-center gap-1.5 px-3 py-2 bg-rose-50 border border-rose-200 text-rose-500 rounded-xl text-xs font-semibold hover:bg-rose-100 transition whitespace-nowrap">
                <i class="fas fa-times text-[10px]"></i> Clear
            </a>
            @endif
        </div>
    </div>

    {{-- RESULTS COUNT --}}
    <div class="flex items-center justify-between mb-3 px-1">
        <p class="text-xs text-slate-400 font-medium">
            <span class="text-slate-700 font-bold">{{ $properties->firstItem() ?? 0 }}–{{ $properties->lastItem() ?? 0 }}</span>
            of <span class="text-slate-700 font-bold">{{ $properties->total() }}</span> properties
        </p>
        <p class="text-xs text-slate-400">Page {{ $properties->currentPage() }} / {{ $properties->lastPage() }}</p>
    </div>

    {{-- PROPERTY ROWS --}}
    <div id="properties-table" class="space-y-2">

        @forelse($properties as $property)
        @php
            $nameData     = is_string($property->name)            ? json_decode($property->name, true)            : $property->name;
            $propName     = is_array($nameData)                   ? ($nameData['en'] ?? 'Property')                : $nameData;

            $priceData    = is_string($property->price)           ? json_decode($property->price, true)           : $property->price;
            $priceVal     = 0;
            if (is_array($priceData))       { $priceVal = $priceData['usd'] ?? $priceData['amount'] ?? 0; }
            elseif (is_numeric($priceData)) { $priceVal = $priceData; }

            $imageData    = is_string($property->images)          ? json_decode($property->images, true)          : $property->images;
            $firstImage   = is_array($imageData)                  ? ($imageData[0] ?? null)                       : null;

            $typeData     = is_string($property->type)            ? json_decode($property->type, true)            : $property->type;
            $typeCategory = is_array($typeData)                   ? ($typeData['category'] ?? 'N/A')               : ($typeData ?? 'N/A');

            $addressData  = is_string($property->address_details) ? json_decode($property->address_details, true) : $property->address_details;
            $cityData     = is_array($addressData) && isset($addressData['city']) ? $addressData['city'] : null;
            $cityName     = is_array($cityData)                   ? ($cityData['en'] ?? 'Unknown')                 : ($cityData ?? 'Unknown');

            $ownerName = 'Unknown';
            if ($property->owner) {
                if      ($property->owner_type === 'App\Models\Agent')            { $ownerName = $property->owner->name         ?? $property->owner->agent_name ?? $property->owner->username ?? 'Unknown'; }
                elseif  ($property->owner_type === 'App\Models\RealEstateOffice') { $ownerName = $property->owner->company_name ?? $property->owner->name       ?? 'Unknown'; }
                elseif  ($property->owner_type === 'App\Models\User')             { $ownerName = $property->owner->username     ?? $property->owner->name       ?? 'Unknown'; }
            }

            $statusConfig = match($property->status) {
                'available' => ['bg-emerald-50 border-emerald-200 text-emerald-700', 'fa-circle-dot'],
                'pending'   => ['bg-amber-50 border-amber-200 text-amber-700',       'fa-clock'],
                'sold'      => ['bg-blue-50 border-blue-200 text-blue-700',          'fa-handshake'],
                'rented'    => ['bg-violet-50 border-violet-200 text-violet-700',    'fa-key'],
                'rejected'  => ['bg-rose-50 border-rose-200 text-rose-600',          'fa-ban'],
                default     => ['bg-slate-100 border-slate-200 text-slate-600',      'fa-circle'],
            };

            $uniqueViewers = $property->interactions->where('interaction_type', 'impression')->unique('user_id')->count();
            $isNew = $property->created_at->diffInDays() < 7;
        @endphp

        <div class="group bg-white border border-slate-200 rounded-2xl hover:border-slate-300 hover:shadow-md transition-all duration-200">
            <div class="flex items-center gap-4 px-5 py-4">

                {{-- Thumbnail --}}
                <div class="shrink-0 w-[68px] h-[68px] rounded-xl overflow-hidden bg-slate-100 border border-slate-200 relative">
                    @if($firstImage)
                        <img src="{{ $firstImage }}" alt="{{ $propName }}" loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @if($property->is_boosted)
                            <div class="absolute top-1 right-1 w-4 h-4 rounded-full bg-amber-500 flex items-center justify-center">
                                <i class="fas fa-bolt text-white text-[7px]"></i>
                            </div>
                        @endif
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                            <i class="fas fa-image text-xl"></i>
                        </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0 flex items-center justify-between gap-4 flex-wrap">

                    {{-- Left: name + meta --}}
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1.5">
                            <a href="{{ route('admin.properties.show', $property->id) }}"
                               class="text-sm font-bold text-slate-900 hover:text-violet-600 transition-colors truncate max-w-[300px]">
                                {{ $propName }}
                            </a>
                            @if($isNew)
                            <span class="shrink-0 px-1.5 py-0.5 bg-teal-50 border border-teal-200 text-teal-700 rounded-md text-[10px] font-bold uppercase tracking-wide">New</span>
                            @endif
                            @if($property->is_boosted)
                            <span class="shrink-0 px-1.5 py-0.5 bg-amber-50 border border-amber-200 text-amber-700 rounded-md text-[10px] font-bold uppercase tracking-wide">
                                <i class="fas fa-bolt text-[8px]"></i> Boosted
                            </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2.5 flex-wrap text-[11px] text-slate-400 font-medium">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-map-marker-alt text-[9px] text-slate-300"></i> {{ $cityName }}
                            </span>
                            <span class="text-slate-200">·</span>
                            <span class="flex items-center gap-1">
                                <i class="fas fa-calendar text-[9px] text-slate-300"></i> {{ $property->created_at->format('M d, Y') }}
                            </span>
                            <span class="text-slate-200">·</span>
                            <span class="text-slate-300">#{{ $property->id }}</span>
                            <span class="text-slate-200">·</span>
                            @if($property->owner)
                                <span>{{ $ownerName }}</span>
                                <span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-400 text-[10px]">{{ class_basename($property->owner_type) }}</span>
                            @else
                                <span class="text-rose-400">Deleted user</span>
                            @endif
                        </div>
                    </div>

                    {{-- Right: tags + price + stats + actions --}}
                    <div class="flex items-center gap-2.5 shrink-0 flex-wrap justify-end">

                        {{-- Type badges --}}
                        <div class="flex items-center gap-1">
                            <span class="px-2 py-1 bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 uppercase tracking-wide">{{ $property->listing_type }}</span>
                            <span class="px-2 py-1 bg-slate-50 border border-slate-100 rounded-lg text-[10px] text-slate-400">{{ ucfirst($typeCategory) }}</span>
                        </div>

                        {{-- Status badge --}}
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $statusConfig[0] }} uppercase tracking-wide shrink-0">
                            <i class="fas {{ $statusConfig[1] }} text-[8px]"></i> {{ ucfirst($property->status) }}
                        </span>

                        {{-- Price --}}
                        <div class="text-right min-w-[80px]">
                            <p class="text-sm font-black text-slate-900 leading-none">${{ number_format((float)$priceVal) }}</p>
                            <p class="text-[10px] text-slate-400 font-medium mt-0.5">USD</p>
                        </div>

                        {{-- Stats --}}
                        <div class="flex items-center gap-1.5">
                            <div class="flex items-center gap-1 px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg" title="Total hits">
                                <i class="fas fa-eye text-slate-400 text-[9px]"></i>
                                <span class="text-[11px] font-bold text-slate-600">{{ number_format($property->views ?? 0) }}</span>
                            </div>
                            @if($uniqueViewers > 0)
                            <div class="flex items-center gap-1 px-2 py-1 bg-emerald-50 border border-emerald-100 rounded-lg" title="Authenticated viewers">
                                <i class="fas fa-user-check text-emerald-500 text-[9px]"></i>
                                <span class="text-[11px] font-bold text-emerald-600">{{ $uniqueViewers }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-1 pl-2.5 border-l border-slate-200">
                            <a href="{{ route('admin.properties.show', $property->id) }}"
                               class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-violet-600 hover:bg-violet-50 transition" title="View">
                                <i class="fas fa-eye text-[11px]"></i>
                            </a>
                            <a href="{{ route('admin.properties.edit', $property->id) }}"
                               class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                <i class="fas fa-pen text-[11px]"></i>
                            </a>
                            @if($property->status === 'pending')
                            <form action="{{ route('admin.properties.approve', $property->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Approve">
                                    <i class="fas fa-check text-[11px]"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.properties.delete', $property->id) }}" method="POST"
                                  onsubmit="return confirm('Delete permanently?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition" title="Delete">
                                    <i class="fas fa-trash-alt text-[11px]"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        @empty
        <div class="bg-white border border-slate-200 rounded-2xl py-24 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center mx-auto mb-4 text-slate-300">
                <i class="fas fa-search text-2xl"></i>
            </div>
            <h3 class="text-slate-900 font-bold text-sm mb-1">No properties found</h3>
            <p class="text-slate-400 text-xs mb-5">No listings match your current filters.</p>
            <a href="{{ route('admin.properties.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-violet-50 border border-violet-200 text-violet-600 rounded-xl text-xs font-bold hover:bg-violet-100 transition">
                <i class="fas fa-times text-[10px]"></i> Clear filters
            </a>
        </div>
        @endforelse

    </div>

    {{-- PAGINATION --}}
    @if($properties->hasPages())
    <div id="pagination-wrapper" class="mt-6 flex items-center justify-center gap-1.5 flex-wrap">

        {{-- Prev --}}
        @if($properties->onFirstPage())
            <span class="px-3.5 py-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-300 text-xs cursor-not-allowed select-none">
                <i class="fas fa-chevron-left text-[10px]"></i>
            </span>
        @else
            <a href="{{ $properties->previousPageUrl() }}" class="pagination-link px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-500 text-xs font-bold hover:text-slate-900 hover:border-slate-300 hover:shadow-sm transition">
                <i class="fas fa-chevron-left text-[10px]"></i>
            </a>
        @endif

        @php
            $current = $properties->currentPage();
            $last    = $properties->lastPage();
            $window  = 2;
            $pages   = collect();
            for ($i = max(1, $current - $window); $i <= min($last, $current + $window); $i++) {
                $pages->push($i);
            }
        @endphp

        @if(!$pages->contains(1))
            <a href="{{ $properties->url(1) }}" class="pagination-link w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 text-xs font-bold hover:text-slate-900 hover:border-slate-300 hover:shadow-sm transition">1</a>
        @endif
        @if($pages->first() > 2)
            <span class="w-6 text-center text-slate-300 text-xs">…</span>
        @endif

        @foreach($pages as $page)
            @if($page === $current)
                <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-violet-600 text-white text-xs font-black shadow-sm">{{ $page }}</span>
            @else
                <a href="{{ $properties->url($page) }}" class="pagination-link w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 text-xs font-bold hover:text-slate-900 hover:border-slate-300 hover:shadow-sm transition">{{ $page }}</a>
            @endif
        @endforeach

        @if($pages->last() < $last - 1)
            <span class="w-6 text-center text-slate-300 text-xs">…</span>
        @endif
        @if(!$pages->contains($last))
            <a href="{{ $properties->url($last) }}" class="pagination-link w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 text-xs font-bold hover:text-slate-900 hover:border-slate-300 hover:shadow-sm transition">{{ $last }}</a>
        @endif

        {{-- Next --}}
        @if($properties->hasMorePages())
            <a href="{{ $properties->nextPageUrl() }}" class="pagination-link px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-500 text-xs font-bold hover:text-slate-900 hover:border-slate-300 hover:shadow-sm transition">
                <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
        @else
            <span class="px-3.5 py-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-300 text-xs cursor-not-allowed select-none">
                <i class="fas fa-chevron-right text-[10px]"></i>
            </span>
        @endif

    </div>
    @endif

</div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var SCROLL_KEY = 'dm_props_scroll';
    var table = document.getElementById('properties-table');

    if (sessionStorage.getItem(SCROLL_KEY) && table) {
        setTimeout(function () {
            table.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 80);
        sessionStorage.removeItem(SCROLL_KEY);
    }

    function flagAndGo(url) {
        sessionStorage.setItem(SCROLL_KEY, '1');
        window.location.href = url;
    }

    document.querySelectorAll('.pagination-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            flagAndGo(this.href);
        });
    });

    ['filter-status', 'filter-type', 'filter-owner'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', function () { flagAndGo(this.value); });
    });

    var sf = document.getElementById('search-form');
    if (sf) sf.addEventListener('submit', function () { sessionStorage.setItem(SCROLL_KEY, '1'); });
});
</script>

@endsection
