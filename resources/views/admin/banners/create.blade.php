@extends('layouts.admin-layout')

@section('title', 'Create Banner')

@section('content')

<div class="max-w-4xl mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <nav class="flex text-sm text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.banners.index') }}" class="hover:text-slate-900 transition">Banners</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-bold">Create</span>
            </nav>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">New Banner Ad</h1>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.banners.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition">Cancel</a>
            <button type="submit" form="createBannerForm" class="px-6 py-2.5 bg-black text-white font-bold rounded-xl shadow-lg hover:bg-slate-800 transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Create Banner
            </button>
        </div>
    </div>

    <form id="createBannerForm" method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- Main Column --}}
            <div class="md:col-span-2 space-y-6">

                {{-- Banner Info --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-6">Banner Details</h3>

                    {{-- Titles --}}
                    <div class="space-y-5 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Title (English) <span class="text-red-500">*</span></label>
                            <input type="text" name="title[en]" value="{{ old('title.en') }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none focus:border-indigo-500 transition">
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Title (Arabic)</label>
                                <input type="text" name="title[ar]" value="{{ old('title.ar') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none text-right focus:border-indigo-500 transition" dir="rtl">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Title (Kurdish)</label>
                                <input type="text" name="title[ku]" value="{{ old('title.ku') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none text-right focus:border-indigo-500 transition" dir="rtl">
                            </div>
                        </div>
                    </div>

                    {{-- Link & Owner --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Destination URL</label>
                            <input type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none focus:border-indigo-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Owner / Client Name</label>
                            <input type="text" name="owner_name" value="{{ old('owner_name') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none focus:border-indigo-500 transition">
                        </div>
                    </div>
                </div>

                {{-- Image Upload --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-2">Banner Image <span class="text-red-500">*</span></h3>
                    <p class="text-xs text-slate-500 mb-6">Recommended size: 1200x600px (2:1 aspect ratio). Max 4MB.</p>

                    <input type="file" name="image" required accept="image/png, image/jpeg, image/jpg, image/webp" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition cursor-pointer">
                     @error('image') <p class="text-xs text-red-500 mt-2 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Configuration --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Configuration</h3>

                    <div class="space-y-4">
                         {{-- Status Select --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Initial Status <span class="text-red-500">*</span></label>
                            <select name="status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none cursor-pointer focus:border-indigo-500">
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active Immediately</option>
                                <option value="paused" {{ old('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                            </select>
                        </div>

                        {{-- Dates --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Start Date <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">End Date (Optional)</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none focus:border-indigo-500">
                            <p class="text-[10px] text-slate-400 mt-1">Leave blank to run indefinitely.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
