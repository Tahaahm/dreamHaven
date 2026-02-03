@extends('layouts.admin-layout')

@section('title', 'Properties Directory')

@section('content')

<div class="max-w-[1600px] mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Properties</h1>
            <p class="text-slate-500 font-medium mt-1">Manage listings, approve submissions, and monitor inventory.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(($pendingCount ?? 0) > 0)
            <a href="{{ route('admin.properties.index', ['status' => 'pending']) }}" class="group relative px-5 py-2.5 bg-amber-50 text-amber-700 border border-amber-200/60 rounded-xl text-xs font-bold uppercase tracking-wide hover:bg-amber-100 transition">
                <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
                <i class="fas fa-clock mr-2"></i> {{ $pendingCount }} Pending Approval
            </a>
            @endif
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        @php
            $statCards = [
                ['label' => 'Total Inventory', 'value' => $stats['total'] ?? 0, 'icon' => 'fa-city', 'color' => 'text-slate-700'],
                ['label' => 'Active Listings', 'value' => $stats['active'] ?? 0, 'icon' => 'fa-check-circle', 'color' => 'text-emerald-600'],
                ['label' => 'Pending Review', 'value' => $stats['pending'] ?? 0, 'icon' => 'fa-hourglass-half', 'color' => 'text-amber-600'],
                ['label' => 'For Sale', 'value' => $stats['for_sale'] ?? 0, 'icon' => 'fa-tag', 'color' => 'text-blue-600'],
                ['label' => 'For Rent', 'value' => $stats['for_rent'] ?? 0, 'icon' => 'fa-key', 'color' => 'text-indigo-600'],
            ];
        @endphp

        @foreach($statCards as $stat)
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center {{ $stat['color'] }} group-hover:scale-110 transition-transform">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ number_format($stat['value']) }}</p>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $stat['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Unified Filter Bar --}}
    <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col lg:flex-row items-center gap-3">
        <div class="relative flex-1 w-full">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <form method="GET" action="{{ route('admin.properties.index') }}">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-11 pr-4 py-3 bg-transparent border-none focus:ring-0 text-sm font-semibold placeholder-slate-400"
                       placeholder="Search by property title, ID, or reference...">
            </form>
        </div>

        <div class="flex items-center gap-2 w-full lg:w-auto px-2 overflow-x-auto no-scrollbar">
            <div class="h-8 w-px bg-slate-200 mx-2 hidden lg:block"></div>

            <select onchange="window.location.href=this.value" class="bg-slate-50 border-none text-xs font-bold text-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer min-w-[140px]">
                <option value="{{ route('admin.properties.index') }}">Status: All</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('status'), ['status' => 'available'])) }}" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('status'), ['status' => 'sold'])) }}" {{ request('status') == 'sold' ? 'selected' : '' }}>Sold</option>
            </select>

            <select onchange="window.location.href=this.value" class="bg-slate-50 border-none text-xs font-bold text-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer min-w-[140px]">
                <option value="{{ route('admin.properties.index') }}">Type: All</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('listing_type'), ['listing_type' => 'sale'])) }}" {{ request('listing_type') == 'sale' ? 'selected' : '' }}>For Sale</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('listing_type'), ['listing_type' => 'rent'])) }}" {{ request('listing_type') == 'rent' ? 'selected' : '' }}>For Rent</option>
            </select>

            <select onchange="window.location.href=this.value" class="bg-slate-50 border-none text-xs font-bold text-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer min-w-[140px]">
                <option value="{{ route('admin.properties.index') }}">Owner: All</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('owner_type'), ['owner_type' => 'Agent'])) }}" {{ request('owner_type') == 'Agent' ? 'selected' : '' }}>Agent</option>
                <option value="{{ route('admin.properties.index', array_merge(request()->except('owner_type'), ['owner_type' => 'RealEstateOffice'])) }}" {{ request('owner_type') == 'RealEstateOffice' ? 'selected' : '' }}>Office</option>
            </select>

            @if(request()->anyFilled(['search', 'status', 'listing_type', 'owner_type']))
                <a href="{{ route('admin.properties.index') }}" class="p-2.5 text-rose-500 hover:bg-rose-50 rounded-xl transition text-sm" title="Clear Filters">
                    <i class="fas fa-times-circle"></i>
                </a>
            @endif
        </div>
    </div>

    {{-- Advanced Table --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em]">Listing Details</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em]">Owner / Source</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em]">Category</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em]">Price (USD)</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] text-center">Stats</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($properties as $property)
    @php
        // --- SAFE DATA EXTRACTION ---
        $nameData = is_string($property->name) ? json_decode($property->name, true) : $property->name;
        $propName = is_array($nameData) ? ($nameData['en'] ?? 'Property') : $nameData;

        // --- PRICE EXTRACTION (USD PRIORITY) ---
        $priceData = is_string($property->price) ? json_decode($property->price, true) : $property->price;
        $priceVal = 0;

        if (is_array($priceData)) {
            // Prioritize 'usd', fall back to 'amount' (which assumes base currency), then 'iqd' if desperate
            $priceVal = $priceData['usd'] ?? $priceData['amount'] ?? 0;
        } elseif (is_numeric($priceData)) {
            $priceVal = $priceData;
        }

        $imageData = is_string($property->images) ? json_decode($property->images, true) : $property->images;
        $firstImage = is_array($imageData) ? ($imageData[0] ?? null) : null;

        $typeData = is_string($property->type) ? json_decode($property->type, true) : $property->type;
        $typeCategory = is_array($typeData) ? ($typeData['category'] ?? 'N/A') : ($typeData ?? 'N/A');

        $addressData = is_string($property->address_details) ? json_decode($property->address_details, true) : $property->address_details;
        $cityData = is_array($addressData) && isset($addressData['city']) ? $addressData['city'] : null;
        $cityName = is_array($cityData) ? ($cityData['en'] ?? 'Unknown Location') : ($cityData ?? 'Unknown Location');
    @endphp

    <tr class="hover:bg-slate-50/50 transition-colors group">
        {{-- Listing Details --}}
        <td class="px-6 py-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 relative">
                    @if($firstImage)
                        <img src="{{ $firstImage }}" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                            <i class="fas fa-image text-xl"></i>
                        </div>
                    @endif
                </div>
                <div class="max-w-[220px]">
                    <a href="{{ route('admin.properties.show', $property->id) }}" class="text-sm font-bold text-slate-900 hover:text-indigo-600 line-clamp-1 mb-1">
                        {{ $propName }}
                    </a>
                    <p class="text-[10px] font-medium text-slate-500 line-clamp-1 mb-1">
                        <i class="fas fa-map-marker-alt mr-1 text-slate-300"></i> {{ $cityName }}
                    </p>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $property->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </td>

        {{-- Owner --}}
        <td class="px-6 py-4">
            <div class="flex flex-col">
                <span class="text-xs font-bold text-slate-700">
                    @if($property->owner)
                        {{ $property->owner->agent_name ?? $property->owner->company_name ?? $property->owner->username ?? 'Unknown' }}
                    @else
                        <span class="text-rose-500 italic">Deleted User</span>
                    @endif
                </span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">
                    {{ class_basename($property->owner_type) }}
                </span>
            </div>
        </td>

        {{-- Category --}}
        <td class="px-6 py-4">
            <div class="flex flex-col gap-1">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide bg-slate-100 text-slate-600 border border-slate-200 w-fit">
                    {{ $property->listing_type }}
                </span>
                <span class="text-[10px] text-slate-400 font-medium">{{ ucfirst($typeCategory) }}</span>
            </div>
        </td>

        {{-- Price (USD) --}}
        <td class="px-6 py-4">
            <p class="text-sm font-black text-slate-900">
                ${{ number_format((float)$priceVal) }}
                <span class="text-[10px] text-slate-400 font-bold ml-1">USD</span>
            </p>
        </td>

        {{-- Stats (UPDATED with Auth View Tracking) --}}
        <td class="px-6 py-4 text-center">
            <div class="flex flex-col gap-1 items-center">
                {{-- Total Hits --}}
                <div class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200">
                    <i class="fas fa-eye text-indigo-300"></i> {{ number_format($property->views ?? 0) }}
                </div>

                {{-- Auth Viewers (Logic: Check interactions) --}}
                @php
                    $uniqueAuthViews = $property->interactions->where('interaction_type', 'view')->unique('user_id')->count();
                @endphp

                @if($uniqueAuthViews > 0)
                <div class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100" title="{{ $uniqueAuthViews }} Authenticated Users">
                    <i class="fas fa-user-check"></i> {{ $uniqueAuthViews }} Auth
                </div>
                @endif
            </div>
        </td>

        {{-- Status --}}
        <td class="px-6 py-4 text-center">
            @php
                $statusStyles = match($property->status) {
                    'available' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                    'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                    'sold' => 'bg-blue-50 text-blue-700 border-blue-100',
                    'rented' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                    'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
                    'suspended' => 'bg-slate-100 text-slate-500 border-slate-200',
                    default => 'bg-slate-50 text-slate-600 border-slate-200'
                };
                $statusIcon = match($property->status) {
                    'available' => 'fa-check-circle',
                    'pending' => 'fa-clock',
                    'sold' => 'fa-handshake',
                    'rented' => 'fa-key',
                    'rejected' => 'fa-ban',
                    'suspended' => 'fa-pause-circle',
                    default => 'fa-circle'
                };
            @endphp
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $statusStyles }}">
                <i class="fas {{ $statusIcon }}"></i> {{ ucfirst($property->status) }}
            </span>
        </td>

        {{-- Actions --}}
        <td class="px-6 py-4 text-right">
            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.properties.show', $property->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm" title="View Details">
                    <i class="fas fa-eye text-xs"></i>
                </a>

                <a href="{{ route('admin.properties.edit', $property->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 transition shadow-sm" title="Edit">
                    <i class="fas fa-pen text-xs"></i>
                </a>

                @if($property->status == 'pending')
                <form action="{{ route('admin.properties.approve', $property->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-600 hover:bg-emerald-100 transition shadow-sm" title="Quick Approve">
                        <i class="fas fa-check text-xs"></i>
                    </button>
                </form>
                @endif

                <div class="relative group/delete">
                    <form action="{{ route('admin.properties.delete', $property->id) }}" method="POST" onsubmit="return confirm('Delete this property permanently?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition shadow-sm" title="Delete">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
        </td>
    </tr>
    @empty
        <tr>
            <td colspan="7" class="px-6 py-16 text-center">
                <div class="max-w-xs mx-auto">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i class="fas fa-search text-2xl"></i>
                    </div>
                    <h3 class="text-slate-900 font-bold">No properties found</h3>
                    <p class="text-slate-500 text-sm mt-1 mb-4">No listings match your current filters.</p>
                    <a href="{{ route('admin.properties.index') }}" class="text-indigo-600 font-bold text-sm hover:underline">Clear Filters</a>
                </div>
            </td>
        </tr>
    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100">
            {{ $properties->withQueryString()->links() }}
        </div>
    </div>
</div>

<style>
    /* Custom Scrollbar */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

@endsection
