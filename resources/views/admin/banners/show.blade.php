@extends('layouts.admin-layout')

@section('title', 'Banner Details')

@section('content')

@php
    // --- 1. Data Parsing ---
    $titles = is_array($banner->title) ? $banner->title : json_decode($banner->title, true) ?? [];
    $desc = is_array($banner->description) ? $banner->description : json_decode($banner->description, true) ?? [];
    $cta = is_array($banner->call_to_action) ? $banner->call_to_action : json_decode($banner->call_to_action, true) ?? [];

    // Targeting Arrays
    $locations = is_array($banner->target_locations) ? $banner->target_locations : json_decode($banner->target_locations, true) ?? [];
    $propertyTypes = is_array($banner->target_property_types) ? $banner->target_property_types : json_decode($banner->target_property_types, true) ?? [];
    $pages = is_array($banner->target_pages) ? $banner->target_pages : json_decode($banner->target_pages, true) ?? [];
    $priceRange = is_array($banner->target_price_range) ? $banner->target_price_range : json_decode($banner->target_price_range, true);

    // Owner Resolution
    $ownerType = 'External/System';
    $ownerBadgeColor = 'bg-slate-100 text-slate-600';
    if ($banner->owner_type == 'App\Models\Agent') {
        $ownerType = 'Real Estate Agent';
        $ownerBadgeColor = 'bg-blue-50 text-blue-700 border-blue-100';
    } elseif ($banner->owner_type == 'App\Models\RealEstateOffice') {
        $ownerType = 'Real Estate Office';
        $ownerBadgeColor = 'bg-purple-50 text-purple-700 border-purple-100';
    } elseif ($banner->owner_type == 'App\Models\User') {
        $ownerType = 'Individual User';
        $ownerBadgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-100';
    }

    // Status Color
    $statusColor = match($banner->status) {
        'active' => 'bg-emerald-500 text-white',
        'pending' => 'bg-amber-400 text-white',
        'paused' => 'bg-slate-500 text-white',
        'rejected' => 'bg-rose-500 text-white',
        default => 'bg-slate-400 text-white'
    };
@endphp

<div class="max-w-[1600px] mx-auto animate-in fade-in zoom-in-95 duration-500 pb-20">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 border-b border-slate-200 pb-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                <a href="{{ route('admin.banners.index') }}" class="hover:text-slate-900 transition">Banners</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span>Details</span>
            </div>
            <div class="flex items-center gap-4">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ $titles['en'] ?? 'Untitled Banner' }}</h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $statusColor }}">
                    {{ ucfirst($banner->status) }}
                </span>
            </div>
            <p class="text-sm text-slate-500 font-mono mt-1">ID: {{ $banner->id }}</p>
        </div>

        <div class="flex gap-3">
             <form action="{{ route('admin.banners.delete', $banner->id) }}" method="POST" onsubmit="return confirm('Permanently delete this banner?');">
                @csrf @method('DELETE')
                <button class="px-5 py-2.5 bg-white border border-slate-200 text-rose-600 font-bold rounded-xl hover:bg-rose-50 transition flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </form>
            <a href="{{ route('admin.banners.edit', $banner->id) }}" class="px-6 py-2.5 bg-black text-white font-bold rounded-xl shadow-lg hover:bg-slate-800 transition flex items-center gap-2">
                <i class="fas fa-pen"></i> Edit Banner
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        {{-- LEFT COLUMN --}}
        <div class="xl:col-span-2 space-y-8">

            {{-- 1. VISUAL PREVIEW --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-2 overflow-hidden">
                <div class="relative w-full rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 group">
                    @if($banner->image_url)
                        <img src="{{ $banner->image_url }}" class="w-full h-auto object-contain max-h-[500px]">
                        @if($banner->link_url)
                        <a href="{{ $banner->link_url }}" target="_blank" class="absolute bottom-4 right-4 bg-black/80 backdrop-blur text-white px-4 py-2 rounded-lg text-xs font-bold opacity-0 group-hover:opacity-100 transition flex items-center gap-2">
                            {{ $banner->link_url }} <i class="fas fa-external-link-alt"></i>
                        </a>
                        @endif
                    @else
                        <div class="h-64 flex flex-col items-center justify-center text-slate-400">
                            <i class="fas fa-image text-4xl mb-2"></i>
                            <span class="text-sm font-bold">No Image Asset</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. CAMPAIGN DETAILS (Full Data) --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2">
                    <i class="fas fa-info-circle text-slate-400"></i> Campaign Data
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Languages --}}
                    <div class="space-y-4">
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Title (English)</p>
                            <p class="text-sm font-bold text-slate-900">{{ $titles['en'] ?? '-' }}</p>
                            @if(!empty($desc['en']))
                                <p class="text-xs text-slate-600 mt-2">{{ $desc['en'] }}</p>
                            @endif
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-right" dir="rtl">
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Title (Arabic)</p>
                            <p class="text-sm font-bold text-slate-900">{{ $titles['ar'] ?? '-' }}</p>
                             @if(!empty($desc['ar']))
                                <p class="text-xs text-slate-600 mt-2">{{ $desc['ar'] }}</p>
                            @endif
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-right" dir="rtl">
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Title (Kurdish)</p>
                            <p class="text-sm font-bold text-slate-900">{{ $titles['ku'] ?? '-' }}</p>
                             @if(!empty($desc['ku']))
                                <p class="text-xs text-slate-600 mt-2">{{ $desc['ku'] }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Configuration --}}
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Banner Type</p>
                                <p class="text-sm font-bold text-slate-900 capitalize">{{ $banner->banner_type ?? 'Standard' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Position</p>
                                <p class="text-sm font-bold text-slate-900 capitalize">{{ $banner->position ?? 'General' }}</p>
                            </div>
                             <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Size</p>
                                <p class="text-sm font-bold text-slate-900">{{ $banner->banner_size ?? 'Responsive' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Display Priority</p>
                                <p class="text-sm font-bold text-slate-900">{{ $banner->display_priority ?? '0' }}</p>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100">
                             <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Linked Property</p>
                             @if($banner->property)
                                <a href="{{ route('admin.properties.show', $banner->property->id) }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-100 transition group">
                                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-slate-400">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-900 group-hover:text-indigo-700">Property #{{ $banner->property->id }}</p>
                                        <p class="text-[10px] text-slate-500">Click to view property</p>
                                    </div>
                                </a>
                             @else
                                <p class="text-sm text-slate-500 italic">No specific property linked.</p>
                             @endif
                        </div>
                    </div>
                </div>
            </div>

             {{-- 3. TARGETING METRICS --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2">
                    <i class="fas fa-crosshairs text-slate-400"></i> Targeting Logic
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                     <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-3">Target Locations</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse($locations as $loc)
                                <span class="px-2 py-1 bg-slate-100 border border-slate-200 rounded text-xs font-bold text-slate-700">{{ $loc }}</span>
                            @empty
                                <span class="text-xs text-slate-400 italic">Global (No restriction)</span>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-3">Target Property Types</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse($propertyTypes as $type)
                                <span class="px-2 py-1 bg-slate-100 border border-slate-200 rounded text-xs font-bold text-slate-700 capitalize">{{ $type }}</span>
                            @empty
                                <span class="text-xs text-slate-400 italic">All Types</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                 <div class="mt-6 pt-6 border-t border-slate-100">
                     <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Price Range Targeting</p>
                     @if($priceRange)
                        <p class="font-mono text-sm font-bold text-slate-800">
                            ${{ number_format($priceRange['min'] ?? 0) }} - ${{ number_format($priceRange['max'] ?? 0) }}
                        </p>
                     @else
                        <p class="text-xs text-slate-400 italic">Any Price</p>
                     @endif
                 </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-8">

            {{-- 1. OWNER DETAILS (Detailed) --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-slate-50 rounded-full -mr-6 -mt-6"></div>

                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-6 relative z-10">Owner Details</h3>

                <div class="text-center mb-6">
                    <div class="w-20 h-20 mx-auto bg-slate-100 rounded-full border-4 border-white shadow-sm flex items-center justify-center overflow-hidden mb-3">
                         @if($banner->owner_logo)
                            <img src="{{ $banner->owner_logo }}" class="w-full h-full object-cover">
                         @elseif($banner->owner && method_exists($banner->owner, 'profile_image') && $banner->owner->profile_image)
                             <img src="{{ $banner->owner->profile_image }}" class="w-full h-full object-cover">
                         @else
                            <i class="fas fa-user-tie text-3xl text-slate-300"></i>
                         @endif
                    </div>
                    <h2 class="text-lg font-black text-slate-900">{{ $banner->owner_name ?? 'Unknown' }}</h2>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase {{ $ownerBadgeColor }}">
                        {{ $ownerType }}
                    </span>
                </div>

                <div class="space-y-4 text-sm border-t border-slate-100 pt-6">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Email</span>
                        <a href="mailto:{{ $banner->owner_email }}" class="font-bold text-indigo-600 hover:underline truncate max-w-[150px]">{{ $banner->owner_email ?? 'N/A' }}</a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Phone</span>
                        <a href="tel:{{ $banner->owner_phone }}" class="font-bold text-slate-700 hover:underline">{{ $banner->owner_phone ?? 'N/A' }}</a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Owner ID</span>
                        <span class="font-mono text-xs text-slate-400">{{ $banner->owner_id ?? '-' }}</span>
                    </div>
                </div>

                @if($banner->owner_id && $banner->owner_type)
                    <div class="mt-6">
                        @php
                            $route = '#';
                            if(str_contains($banner->owner_type, 'Agent')) $route = route('admin.agents.show', $banner->owner_id);
                            elseif(str_contains($banner->owner_type, 'RealEstateOffice')) $route = route('admin.offices.show', $banner->owner_id);
                            elseif(str_contains($banner->owner_type, 'User')) $route = route('admin.users.show', $banner->owner_id);
                        @endphp
                        <a href="{{ $route }}" class="block w-full py-2 bg-slate-50 border border-slate-200 text-center rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            View Full Profile
                        </a>
                    </div>
                @endif
            </div>

            {{-- 2. ANALYTICS CARD --}}
            <div class="bg-slate-900 rounded-3xl p-6 text-white shadow-xl">
                 <h3 class="text-sm font-black uppercase tracking-wide mb-6 text-slate-400">Performance</h3>

                 <div class="grid grid-cols-2 gap-6 mb-6">
                     <div>
                         <p class="text-2xl font-black text-white">{{ number_format($banner->views) }}</p>
                         <p class="text-[10px] font-bold text-slate-500 uppercase">Impressions</p>
                     </div>
                     <div>
                         <p class="text-2xl font-black text-emerald-400">{{ number_format($banner->clicks) }}</p>
                         <p class="text-[10px] font-bold text-slate-500 uppercase">Clicks</p>
                     </div>
                 </div>

                 <div class="mb-6">
                     <div class="flex justify-between text-xs font-bold text-slate-400 mb-1">
                         <span>CTR</span>
                         <span>{{ $banner->ctr }}%</span>
                     </div>
                     <div class="w-full bg-slate-800 rounded-full h-1.5">
                         <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ min($banner->ctr * 10, 100) }}%"></div>
                     </div>
                 </div>

                 <div class="pt-6 border-t border-white/10 space-y-3 text-xs">
                     <div class="flex justify-between">
                         <span class="text-slate-400">Budget Spent</span>
                         <span class="font-bold text-white">${{ number_format($banner->budget_spent, 2) }}</span>
                     </div>
                     <div class="flex justify-between">
                         <span class="text-slate-400">Cost Per Click</span>
                         <span class="font-bold text-white">${{ number_format($banner->cost_per_click, 2) }}</span>
                     </div>
                 </div>
            </div>

            {{-- 3. SYSTEM META --}}
            <div class="bg-slate-50 rounded-3xl border border-slate-200 p-6 text-xs">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wide mb-4">System Metadata</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Created At</span>
                        <span class="font-mono text-slate-700">{{ $banner->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                     <div class="flex justify-between">
                        <span class="text-slate-500">Updated At</span>
                        <span class="font-mono text-slate-700">{{ $banner->updated_at->format('Y-m-d H:i') }}</span>
                    </div>
                     <div class="flex justify-between">
                        <span class="text-slate-500">Created IP</span>
                        <span class="font-mono text-slate-700">{{ $banner->created_by_ip ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
