@extends('layouts.admin-layout')

@section('title', 'Banner Ads')

@section('content')

<div class="max-w-[1600px] mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Banner Ads</h1>
            <p class="text-slate-500 font-medium">Manage promotional campaigns and tracking.</p>
        </div>
        <div class="flex items-center gap-3">
             <form method="GET" class="bg-white border border-slate-200 text-slate-500 px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm flex items-center gap-2 transition focus-within:border-black focus-within:ring-1 focus-within:ring-black">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search banners..." class="bg-transparent border-none outline-none text-slate-900 placeholder-slate-400 w-32 focus:w-48 transition-all">
            </form>
            <a href="{{ route('admin.banners.create') }}" class="bg-black hover:bg-slate-800 text-white px-5 py-2.5 text-xs font-bold rounded-xl shadow-lg transition-all flex items-center gap-2 uppercase tracking-wide">
                <i class="fas fa-plus"></i> New Banner
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Total Banners</p>
            <h3 class="text-3xl font-black text-slate-900 mb-1">{{ $stats['total'] ?? 0 }}</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Active Campaigns</p>
            <h3 class="text-3xl font-black text-emerald-600 mb-1">{{ $stats['active'] ?? 0 }}</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Pending Review</p>
            <h3 class="text-3xl font-black text-amber-500 mb-1">{{ $stats['pending'] ?? 0 }}</h3>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Banner Visual</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Campaign Details</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider">Owner (Agent/Office)</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-center">Stats</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($banners as $banner)
                    @php
                        // 1. Safe Title Parsing
                        $title = 'Untitled';
                        if(is_array($banner->title)) $title = $banner->title['en'] ?? 'Untitled';
                        elseif(is_string($banner->title)) {
                             $decoded = json_decode($banner->title, true);
                             $title = is_array($decoded) ? ($decoded['en'] ?? 'Untitled') : $banner->title;
                        }

                        // 2. Status Color
                        $statusClass = match($banner->status) {
                            'active' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                            'paused' => 'bg-slate-100 text-slate-600 border-slate-200',
                            'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                            default => 'bg-slate-100 text-slate-600 border-slate-200'
                        };

                        // 3. Resolve Owner (Agent or Office)
                        $ownerName = $banner->owner_name ?? 'System Admin';
                        $ownerImage = $banner->owner_logo;
                        $ownerType = 'Admin';

                        // Attempt to find linked owner if relations exist
                        if($banner->owner_type && $banner->owner_id) {
                            if(str_contains($banner->owner_type, 'Agent')) {
                                $ownerType = 'Agent';
                                // If owner_logo is empty, try to get from relation (optional)
                            } elseif(str_contains($banner->owner_type, 'RealEstateOffice')) {
                                $ownerType = 'Office';
                            }
                        }
                    @endphp

                    <tr class="hover:bg-slate-50 transition-colors group">

                        {{-- Visual --}}
                        <td class="px-6 py-4">
                            <div class="w-32 h-16 bg-slate-100 rounded-lg border border-slate-200 overflow-hidden relative">
                                @if($banner->image_url)
                                    <img src="{{ $banner->image_url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Details --}}
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-slate-900 line-clamp-1">{{ $title }}</p>
                            <div class="flex items-center gap-3 mt-1 text-[10px] font-medium text-slate-500">
                                <span class="flex items-center gap-1"><i class="far fa-calendar"></i> {{ $banner->start_date ? $banner->start_date->format('M d') : 'Now' }}</span>
                                <i class="fas fa-arrow-right text-[8px] text-slate-300"></i>
                                <span class="flex items-center gap-1"><i class="far fa-calendar-check"></i> {{ $banner->end_date ? $banner->end_date->format('M d') : 'Forever' }}</span>
                            </div>
                        </td>

                        {{-- Owner / Agent --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center overflow-hidden shrink-0">
                                    @if($ownerImage)
                                        <img src="{{ $ownerImage }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-[10px] font-bold text-slate-500">{{ substr($ownerName, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-900">{{ $ownerName }}</p>
                                    <span class="text-[9px] uppercase font-bold tracking-wider text-slate-400">{{ $ownerType }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Stats --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-4">
                                <div class="text-center">
                                    <span class="block text-xs font-bold text-slate-900">{{ number_format($banner->views) }}</span>
                                    <span class="block text-[9px] text-slate-400 uppercase">Views</span>
                                </div>
                                <div class="w-px h-6 bg-slate-200"></div>
                                <div class="text-center">
                                    <span class="block text-xs font-bold text-slate-900">{{ number_format($banner->clicks) }}</span>
                                    <span class="block text-[9px] text-slate-400 uppercase">Clicks</span>
                                </div>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wide border {{ $statusClass }}">
                                {{ ucfirst($banner->status) }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.banners.show', $banner->id) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.banners.edit', $banner->id) }}" class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">No banners found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $banners->links() }}
        </div>
    </div>
</div>
@endsection
