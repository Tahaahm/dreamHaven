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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                         <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Destination URL</label>
                            <input type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none focus:border-indigo-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Owner / Client Name</label>
                            <input type="text" name="owner_name" value="{{ old('owner_name') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none focus:border-indigo-500 transition">
                        </div>
                    </div>

                    {{-- Property Link (Missing data from before) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Link to Specific Property (Optional)</label>
                        <select name="property_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none focus:border-indigo-500 transition">
                            <option value="">None (General Ad)</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                    {{ is_array($property->name) ? $property->name['en'] : $property->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Image Upload with Preview --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-2">Banner Image <span class="text-red-500">*</span></h3>
                    <p class="text-xs text-slate-500 mb-6">Recommended size: 1200x600px (2:1 aspect ratio). Max 4MB.</p>

                    <div class="relative group mb-4">
                        <div id="imagePreviewContainer" class="hidden w-full h-64 mb-4 rounded-2xl overflow-hidden border-2 border-dashed border-slate-200 relative">
                            <img id="imagePreview" src="#" alt="Preview" class="w-full h-full object-contain bg-slate-50">
                            <button type="button" onclick="removeImage()" class="absolute top-4 right-4 bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-600 transition shadow-lg">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <label id="uploadPlaceholder" class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed border-slate-300 rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fas fa-cloud-upload-alt text-3xl text-slate-400 mb-3"></i>
                                <p class="mb-2 text-sm text-slate-500 font-bold">Click to upload image</p>
                                <p class="text-xs text-slate-400">PNG, JPG or WEBP (MAX. 4MB)</p>
                            </div>
                            <input type="file" id="imageInput" name="image" required accept="image/*" class="hidden" onchange="previewFile()">
                        </label>
                    </div>
                    @error('image') <p class="text-xs text-red-500 mt-2 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Placement Configuration (Missing fields added) --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Placement</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Banner Size <span class="text-red-500">*</span></label>
                            <select name="banner_size" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none">
                                <option value="banner">Standard (728x90)</option>
                                <option value="leaderboard" selected>Large (1200x600)</option>
                                <option value="rectangle">Rectangle (300x250)</option>
                                <option value="mobile">Mobile (320x100)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Position <span class="text-red-500">*</span></label>
                            <select name="position" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none">
                                <option value="header">Home Header</option>
                                <option value="sidebar">Sidebar</option>
                                <option value="content_middle">Content Middle</option>
                                <option value="footer">Footer</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Schedule Configuration --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Configuration</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Initial Status <span class="text-red-500">*</span></label>
                            <select name="status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none cursor-pointer focus:border-indigo-500">
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active Immediately</option>
                                <option value="paused" {{ old('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                            </select>
                        </div>

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

{{-- JavaScript for Image Preview --}}
<script>
    function previewFile() {
        const preview = document.getElementById('imagePreview');
        const file = document.getElementById('imageInput').files[0];
        const reader = new FileReader();
        const container = document.getElementById('imagePreviewContainer');
        const placeholder = document.getElementById('uploadPlaceholder');

        reader.onloadend = function () {
            preview.src = reader.result;
            container.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "";
        }
    }

    function removeImage() {
        const preview = document.getElementById('imagePreview');
        const input = document.getElementById('imageInput');
        const container = document.getElementById('imagePreviewContainer');
        const placeholder = document.getElementById('uploadPlaceholder');

        input.value = "";
        preview.src = "";
        container.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
</script>

@endsection
