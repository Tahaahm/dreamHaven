@extends('layouts.admin-layout')

@section('title', 'Edit Banner')

@section('content')

@php
    // --- Data Parsing ---
    $titles = is_array($banner->title) ? $banner->title : json_decode($banner->title, true) ?? [];
    $desc = is_array($banner->description) ? $banner->description : json_decode($banner->description, true) ?? [];

    // Convert arrays to comma-separated strings for inputs
    $locations = is_array($banner->target_locations) ? implode(', ', $banner->target_locations) : '';
    $propertyTypes = is_array($banner->target_property_types) ? implode(', ', $banner->target_property_types) : '';
    $pages = is_array($banner->target_pages) ? implode(', ', $banner->target_pages) : '';

    // Price Range
    $priceRange = is_array($banner->target_price_range) ? $banner->target_price_range : json_decode($banner->target_price_range, true) ?? [];
    $minPrice = $priceRange['min'] ?? '';
    $maxPrice = $priceRange['max'] ?? '';
@endphp

<div class="max-w-[1600px] mx-auto animate-in fade-in zoom-in-95 duration-500 pb-20">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 border-b border-slate-200 pb-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                <a href="{{ route('admin.banners.index') }}" class="hover:text-slate-900 transition">Banners</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <a href="{{ route('admin.banners.show', $banner->id) }}" class="hover:text-slate-900 transition">Details</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span>Edit</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Edit Campaign</h1>
            <p class="text-sm text-slate-500 font-medium">Modifying banner: <span class="font-bold text-slate-900">{{ $titles['en'] ?? 'Untitled' }}</span></p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.banners.show', $banner->id) }}" class="px-6 py-3 bg-white border-2 border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 hover:border-slate-300 transition flex items-center gap-2">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" form="editBannerForm" class="px-8 py-3 bg-black text-white font-bold rounded-xl shadow-lg hover:bg-slate-800 transition flex items-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    <form id="editBannerForm" method="POST" action="{{ route('admin.banners.update', $banner->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

            {{-- LEFT COLUMN (Main Content) --}}
            <div class="xl:col-span-2 space-y-8">

                {{-- 1. VISUAL ASSET --}}
                <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                            <i class="fas fa-image text-indigo-500"></i> Visual Asset
                        </h3>
                        <span class="text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-full">Required</span>
                    </div>

                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        {{-- Current Image Preview --}}
                        <div class="w-full md:w-1/2">
                            <label class="input-label mb-2">Current Preview</label>
                            <div class="rounded-2xl overflow-hidden border-2 border-slate-100 bg-slate-50 relative group">
                                @if($banner->image_url)
                                    <img src="{{ $banner->image_url }}" class="w-full h-48 object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <p class="text-white text-xs font-bold uppercase tracking-wider">Current Image</p>
                                    </div>
                                @else
                                    <div class="h-48 flex flex-col items-center justify-center text-slate-300">
                                        <i class="fas fa-image text-3xl mb-2"></i>
                                        <span class="text-xs font-bold">No Image Set</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Upload New --}}
                        <div class="w-full md:w-1/2">
                            <label class="input-label mb-2">Upload Replacement</label>
                            <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:bg-slate-50 transition cursor-pointer relative">
                                <input type="file" name="image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="space-y-2">
                                    <div class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mx-auto">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-900">Click to upload new image</p>
                                    <p class="text-xs text-slate-500">Max 4MB. Recommended 1200x600px.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. TEXT & CONTENT --}}
                <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2">
                        <i class="fas fa-pen-nib text-emerald-500"></i> Text & Content
                    </h3>

                    <div class="space-y-6">
                        {{-- English --}}
                        <div>
                            <label class="input-label">Title (English) <span class="text-red-500">*</span></label>
                            <input type="text" name="title[en]" value="{{ old('title.en', $titles['en'] ?? '') }}" required class="input-modern font-bold text-lg">
                        </div>
                        <div>
                            <label class="input-label">Description (English)</label>
                            <textarea name="description[en]" rows="2" class="input-modern resize-none">{{ old('description.en', $desc['en'] ?? '') }}</textarea>
                        </div>

                        <div class="h-px bg-slate-100 my-4"></div>

                        {{-- Arabic & Kurdish --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div>
                                    <label class="input-label text-right w-full block">Title (Arabic)</label>
                                    <input type="text" name="title[ar]" value="{{ old('title.ar', $titles['ar'] ?? '') }}" class="input-modern text-right" dir="rtl">
                                </div>
                                <div>
                                    <label class="input-label text-right w-full block">Description (Arabic)</label>
                                    <textarea name="description[ar]" rows="2" class="input-modern resize-none text-right" dir="rtl">{{ old('description.ar', $desc['ar'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="input-label text-right w-full block">Title (Kurdish)</label>
                                    <input type="text" name="title[ku]" value="{{ old('title.ku', $titles['ku'] ?? '') }}" class="input-modern text-right" dir="rtl">
                                </div>
                                <div>
                                    <label class="input-label text-right w-full block">Description (Kurdish)</label>
                                    <textarea name="description[ku]" rows="2" class="input-modern resize-none text-right" dir="rtl">{{ old('description.ku', $desc['ku'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-slate-100 my-4"></div>

                        {{-- Links --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="input-label">Destination URL</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fas fa-link"></i></span>
                                    <input type="url" name="link_url" value="{{ old('link_url', $banner->link_url) }}" class="input-modern pl-10" placeholder="https://example.com">
                                </div>
                            </div>
                            <div class="flex items-end pb-3">
                                <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-slate-200 hover:border-indigo-200 hover:bg-indigo-50 transition w-full">
                                    <input type="checkbox" name="link_opens_new_tab" value="1" {{ $banner->link_opens_new_tab ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                                    <span class="text-sm font-bold text-slate-700">Open link in new tab</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. TARGETING --}}
                <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2">
                        <i class="fas fa-bullseye text-rose-500"></i> Audience Targeting
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                        <div>
                            <label class="input-label">Target Locations (Comma Separated)</label>
                            <input type="text" name="target_locations" value="{{ $locations }}" placeholder="Erbil, Baghdad, Duhok" class="input-modern">
                        </div>
                         <div>
                            <label class="input-label">Property Types (Comma Separated)</label>
                            <input type="text" name="target_property_types" value="{{ $propertyTypes }}" placeholder="Villa, Apartment, Office" class="input-modern">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                         <div>
                            <label class="input-label">Specific Pages (Routes)</label>
                            <input type="text" name="target_pages" value="{{ $pages }}" placeholder="home, properties.index" class="input-modern">
                        </div>
                         <div>
                            <label class="input-label">Price Range Targeting</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="target_price_range[min]" value="{{ $minPrice }}" placeholder="Min" class="input-modern w-1/2">
                                <span class="text-slate-400 font-bold">-</span>
                                <input type="number" name="target_price_range[max]" value="{{ $maxPrice }}" placeholder="Max" class="input-modern w-1/2">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN (Sidebar) --}}
            <div class="space-y-6">

                {{-- CONFIGURATION --}}
                <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Display Settings</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="input-label">Status</label>
                            <select name="status" class="input-modern cursor-pointer">
                                <option value="active" {{ $banner->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ $banner->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paused" {{ $banner->status == 'paused' ? 'selected' : '' }}>Paused</option>
                                <option value="rejected" {{ $banner->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>

                        <div>
                            <label class="input-label">Placement</label>
                            <select name="position" class="input-modern cursor-pointer">
                                <option value="home_top" {{ $banner->position == 'home_top' ? 'selected' : '' }}>Home: Top Hero</option>
                                <option value="home_middle" {{ $banner->position == 'home_middle' ? 'selected' : '' }}>Home: Middle Section</option>
                                <option value="sidebar" {{ $banner->position == 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                                <option value="listing_page" {{ $banner->position == 'listing_page' ? 'selected' : '' }}>Listing Detail Page</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label class="input-label">Start Date</label>
                                <input type="date" name="start_date" value="{{ $banner->start_date ? $banner->start_date->format('Y-m-d') : '' }}" class="input-modern">
                            </div>
                            <div>
                                <label class="input-label">End Date</label>
                                <input type="date" name="end_date" value="{{ $banner->end_date ? $banner->end_date->format('Y-m-d') : '' }}" class="input-modern">
                            </div>
                        </div>

                        <div>
                            <label class="input-label">Priority Order (Higher = First)</label>
                            <input type="number" name="display_priority" value="{{ $banner->display_priority }}" class="input-modern text-center font-bold">
                        </div>

                        <div class="pt-2">
                             <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl bg-amber-50 border border-amber-100 hover:border-amber-300 transition">
                                <input type="checkbox" name="is_featured" value="1" {{ $banner->is_featured ? 'checked' : '' }} class="w-5 h-5 text-amber-500 rounded border-amber-300 focus:ring-amber-500">
                                <span class="text-sm font-bold text-amber-900">Mark as Featured</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- OWNER INFO --}}
                <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Advertiser Info</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="input-label">Owner Name</label>
                            <input type="text" name="owner_name" value="{{ old('owner_name', $banner->owner_name) }}" class="input-modern">
                        </div>
                        <div>
                            <label class="input-label">Owner Email</label>
                            <input type="email" name="owner_email" value="{{ old('owner_email', $banner->owner_email) }}" class="input-modern">
                        </div>
                         <div>
                            <label class="input-label">Owner Phone</label>
                            <input type="text" name="owner_phone" value="{{ old('owner_phone', $banner->owner_phone) }}" class="input-modern">
                        </div>
                    </div>
                </div>

                 {{-- BUDGET --}}
                <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Financials</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="input-label">Total Budget ($)</label>
                            <input type="number" step="0.01" name="budget_total" value="{{ old('budget_total', $banner->budget_total) }}" class="input-modern font-mono font-bold text-emerald-600">
                        </div>
                        <div>
                            <label class="input-label">Cost Per Click ($)</label>
                            <input type="number" step="0.01" name="cost_per_click" value="{{ old('cost_per_click', $banner->cost_per_click) }}" class="input-modern font-mono">
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<style>
    .input-label {
        @apply block text-[11px] font-bold text-slate-400 uppercase mb-1.5 tracking-wide;
    }
    .input-modern {
        @apply w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:border-black focus:ring-0 transition-all duration-200 outline-none;
    }
</style>

@endsection
