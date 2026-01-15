@extends('layouts.admin-layout')

@section('title', 'Banner Details')

@section('content')

@php
    // Safe Title Parsing
    $rawTitle = $banner->title;
    $title = is_array($rawTitle) ? ($rawTitle['en'] ?? 'Untitled') : $rawTitle;
    if(is_string($rawTitle) && str_starts_with($rawTitle, '{')) {
         $decoded = json_decode($rawTitle, true);
         $title = $decoded['en'] ?? $title;
    }

    // Status Colors
    $statusColor = match($banner->status) {
        'active' => 'text-emerald-500 bg-emerald-50 border-emerald-100',
        'pending' => 'text-amber-500 bg-amber-50 border-amber-100',
        'paused' => 'text-slate-500 bg-slate-50 border-slate-100',
        'rejected' => 'text-rose-500 bg-rose-50 border-rose-100',
        default => 'text-slate-500 bg-slate-50 border-slate-100'
    };
@endphp

<div class="max-w-[1600px] mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-400 mb-1">
                <a href="{{ route('admin.banners.index') }}" class="hover:text-indigo-600 transition">Banners</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-600">Details</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                {{ $title }}
                <span class="px-3 py-1 rounded-full text-xs font-bold border uppercase tracking-wide {{ $statusColor }}">
                    {{ ucfirst($banner->status) }}
                </span>
            </h1>
        </div>
        <div class="flex gap-3">
             <form action="{{ route('admin.banners.delete', $banner->id) }}" method="POST" onsubmit="return confirm('Delete this banner?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-5 py-2.5 bg-white border border-slate-200 text-rose-600 font-bold rounded-xl hover:bg-rose-50 hover:border-rose-100 transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </form>
            <a href="{{ route('admin.banners.edit', $banner->id) }}" class="px-6 py-2.5 bg-black text-white font-bold rounded-xl shadow-lg hover:bg-slate-800 transition flex items-center gap-2">
                <i class="fas fa-pen"></i> Edit Banner
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        {{-- Left Column: Preview & Stats --}}
        <div class="xl:col-span-2 space-y-8">

            {{-- Banner Preview --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 overflow-hidden">
                <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2"><i class="fas fa-eye text-indigo-500"></i> Visual Preview</h3>

                <div class="relative w-full rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 group">
                    @if($banner->image_url)
                        <img src="{{ $banner->image_url }}" alt="{{ $title }}" class="w-full h-auto object-cover max-h-[400px]">
                        @if($banner->link_url)
                            <a href="{{ $banner->link_url }}" target="_blank" class="absolute bottom-4 right-4 bg-white/90 backdrop-blur text-slate-900 px-4 py-2 rounded-lg text-xs font-bold shadow-lg opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-2">
                                Visit Link <i class="fas fa-external-link-alt"></i>
                            </a>
                        @endif
                    @else
                        <div class="h-64 flex flex-col items-center justify-center text-slate-400">
                            <i class="fas fa-image text-4xl mb-3"></i>
                            <span class="text-sm font-medium">No Image Uploaded</span>
                        </div>
                    @endif
                </div>

                {{-- Quick Info Row --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-8 pt-8 border-t border-slate-100">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Position</p>
                        <p class="text-sm font-bold text-slate-900 capitalize">{{ str_replace('_', ' ', $banner->position ?? 'Default') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Banner Size</p>
                        <p class="text-sm font-bold text-slate-900">{{ $banner->banner_size ?? 'Responsive' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Link Opens In</p>
                        <p class="text-sm font-bold text-slate-900">{{ $banner->link_opens_new_tab ? 'New Tab' : 'Same Tab' }}</p>
                    </div>
                     <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Priority</p>
                        <p class="text-sm font-bold text-slate-900">{{ $banner->display_priority ?? 0 }}</p>
                    </div>
                </div>
            </div>

            {{-- Performance Analytics --}}
            <div class="bg-slate-900 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600 rounded-full blur-3xl opacity-20 -mr-20 -mt-20"></div>

                <div class="flex items-center justify-between mb-8 relative z-10">
                    <h3 class="text-lg font-black flex items-center gap-2"><i class="fas fa-chart-line text-emerald-400"></i> Performance Analytics</h3>
                    <div class="text-xs font-bold bg-white/10 px-3 py-1 rounded-lg">Last Updated: {{ $banner->updated_at->diffForHumans() }}</div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 relative z-10">
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Views</p>
                        <p class="text-3xl font-black text-white">{{ number_format($banner->views) }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Clicks</p>
                        <p class="text-3xl font-black text-emerald-400">{{ number_format($banner->clicks) }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">CTR</p>
                        <p class="text-3xl font-black text-indigo-400">{{ number_format($banner->ctr, 2) }}%</p>
                    </div>
                    <div class="space-y-1">
                         <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Budget Spent</p>
                         <p class="text-3xl font-black text-white">${{ number_format($banner->budget_spent, 2) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8 mt-8 pt-8 border-t border-white/10 relative z-10">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Last Viewed</p>
                        <p class="text-sm font-medium text-slate-200">{{ $banner->last_viewed_at ? $banner->last_viewed_at->format('M d, Y h:i A') : 'Never' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Last Clicked</p>
                        <p class="text-sm font-medium text-slate-200">{{ $banner->last_clicked_at ? $banner->last_clicked_at->format('M d, Y h:i A') : 'Never' }}</p>
                    </div>
                </div>
            </div>

            {{-- Targeting Information --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                 <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2"><i class="fas fa-bullseye text-rose-500"></i> Targeting Rules</h3>

                 <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                     <div>
                         <p class="text-xs font-bold text-slate-400 uppercase mb-3">Locations</p>
                         <div class="flex flex-wrap gap-2">
                             @forelse($banner->target_locations ?? [] as $loc)
                                <span class="px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700">{{ $loc }}</span>
                             @empty
                                <span class="text-sm text-slate-500 italic">All Locations</span>
                             @endforelse
                         </div>
                     </div>
                     <div>
                         <p class="text-xs font-bold text-slate-400 uppercase mb-3">Property Types</p>
                         <div class="flex flex-wrap gap-2">
                             @forelse($banner->target_property_types ?? [] as $type)
                                <span class="px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 capitalize">{{ $type }}</span>
                             @empty
                                <span class="text-sm text-slate-500 italic">All Types</span>
                             @endforelse
                         </div>
                     </div>
                 </div>

                 <div class="mt-8 pt-6 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-8">
                     <div>
                         <p class="text-xs font-bold text-slate-400 uppercase mb-2">Price Range Targeting</p>
                         <p class="text-sm font-bold text-slate-900 font-mono">
                             @if($banner->target_price_range)
                                ${{ number_format($banner->target_price_range['min'] ?? 0) }} - ${{ number_format($banner->target_price_range['max'] ?? 0) }}
                             @else
                                <span class="text-slate-500 font-sans italic">Any Price</span>
                             @endif
                         </p>
                     </div>
                     <div>
                         <p class="text-xs font-bold text-slate-400 uppercase mb-2">Specific Pages</p>
                         <div class="flex flex-wrap gap-2">
                             @forelse($banner->target_pages ?? [] as $page)
                                <span class="px-2 py-1 bg-slate-100 rounded text-[10px] font-mono text-slate-600">{{ $page }}</span>
                             @empty
                                <span class="text-sm text-slate-500 italic">Run of Network</span>
                             @endforelse
                         </div>
                     </div>
                 </div>
            </div>

        </div>

        {{-- Right Column: Sidebar --}}
        <div class="space-y-6">

            {{-- Owner Information --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Advertiser</h3>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                         @if($banner->owner_logo)
                            <img src="{{ $banner->owner_logo }}" class="w-full h-full object-cover">
                         @else
                            <i class="fas fa-briefcase text-slate-400 text-lg"></i>
                         @endif
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-bold text-slate-900 truncate">{{ $banner->owner_name ?? 'Unknown Owner' }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $banner->owner_email ?? 'No email provided' }}</p>
                    </div>
                </div>

                @if($banner->owner_phone)
                <div class="bg-slate-50 p-3 rounded-lg flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500">Phone</span>
                    <a href="tel:{{ $banner->owner_phone }}" class="text-xs font-bold text-indigo-600 hover:underline">{{ $banner->owner_phone }}</a>
                </div>
                @endif
            </div>

            {{-- Schedule --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Schedule & Budget</h3>

                <div class="space-y-4 relative pl-4 border-l-2 border-slate-100 ml-1">
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white shadow"></div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Starts</p>
                        <p class="text-sm font-bold text-slate-900">{{ $banner->start_date ? $banner->start_date->format('M d, Y') : 'Immediately' }}</p>
                    </div>
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 w-3 h-3 bg-rose-500 rounded-full border-2 border-white shadow"></div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Ends</p>
                        <p class="text-sm font-bold text-slate-900">{{ $banner->end_date ? $banner->end_date->format('M d, Y') : 'Never (Ongoing)' }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500">Total Budget</span>
                        <span class="text-sm font-mono font-bold text-slate-900">${{ number_format($banner->budget_total, 2) }}</span>
                    </div>
                     <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500">Billing Type</span>
                        <span class="text-xs font-bold bg-slate-100 px-2 py-0.5 rounded uppercase">{{ $banner->billing_type ?? 'Flat Rate' }}</span>
                    </div>
                </div>
            </div>

            {{-- Admin Meta --}}
            <div class="bg-slate-50 rounded-3xl border border-slate-200 p-6">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">System Info</h3>
                <div class="space-y-3 text-xs">
                     <div class="flex justify-between">
                        <span class="text-slate-500 font-bold">Banner ID</span>
                        <span class="font-mono text-slate-700">{{ substr($banner->id, 0, 8) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-bold">Created</span>
                        <span class="text-slate-700">{{ $banner->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-bold">Approved By</span>
                        <span class="text-slate-700">{{ $banner->approver->name ?? 'Auto/System' }}</span>
                    </div>
                     <div class="flex justify-between">
                        <span class="text-slate-500 font-bold">Created IP</span>
                        <span class="font-mono text-slate-700">{{ $banner->created_by_ip ?? 'N/A' }}</span>
                    </div>
                </div>

                @if($banner->admin_notes)
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <p class="text-xs font-bold text-slate-500 uppercase mb-2">Admin Notes</p>
                    <p class="text-xs text-slate-600 bg-white p-3 rounded-lg border border-slate-200 italic">
                        "{{ $banner->admin_notes }}"
                    </p>
                </div>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection
