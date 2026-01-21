@extends('layouts.admin-layout')

@section('content')
<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 bg-gray-50/50">
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">New Category</h1>
                <p class="text-sm text-gray-500 mt-2">Create a classification for your service providers.</p>
            </div>
            <a href="{{ route('admin.categories.index') }}"
               class="group flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to List
            </a>
        </div>

        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

                <div class="p-8 space-y-8">

                    {{-- Section 1: Visuals --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Category Visuals</h3>
                        <p class="text-sm text-gray-500 mb-4">Upload a representative image for this category.</p>

                        <div class="relative group">
                            <input type="file" name="image" id="category_image" accept="image/*" class="hidden" onchange="previewImage(event)">
                            <label for="category_image"
                                   class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed border-gray-300 rounded-2xl cursor-pointer hover:border-indigo-500 hover:bg-indigo-50/30 transition-all duration-300 relative overflow-hidden bg-gray-50">

                                {{-- Upload State --}}
                                <div id="upload-placeholder" class="text-center transition-all duration-300 group-hover:scale-105">
                                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                        <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900">Click to upload image</p>
                                    <p class="text-sm text-gray-500 mt-1">SVG, PNG, JPG or GIF (Max. 400x400px)</p>
                                </div>

                                {{-- Preview State --}}
                                <div id="image-preview-wrapper" class="absolute inset-0 hidden">
                                    <img id="image-preview" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <p class="text-white font-medium bg-black/50 px-4 py-2 rounded-lg backdrop-blur-sm">
                                            <i class="fas fa-pen mr-2"></i> Change Image
                                        </p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    {{-- Section 2: Details --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-6">General Information</h3>

                        <div class="space-y-6">
                            {{-- Name Input --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                        <i class="fas fa-tag"></i>
                                    </span>
                                    <input type="text" name="name" required placeholder="e.g. Plumbing Services"
                                           class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:border-transparent transition-all placeholder-gray-400">
                                </div>
                            </div>

                            {{-- Subtitle Input --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Subtitle / Short Description</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                        <i class="fas fa-align-left"></i>
                                    </span>
                                    <input type="text" name="subtitle" placeholder="e.g. Pipes, leaks, and installations"
                                           class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:border-transparent transition-all placeholder-gray-400">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Sort Order --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sort Order</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                            <i class="fas fa-sort-numeric-down"></i>
                                        </span>
                                        <input type="number" name="sort_order" value="0"
                                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white focus:border-transparent transition-all">
                                    </div>
                                </div>

                                {{-- Active Toggle --}}
                                <div class="bg-gray-50 rounded-xl p-3 border border-gray-200 flex items-center justify-between">
                                    <div class="ml-2">
                                        <span class="block text-sm font-semibold text-gray-900">Active Status</span>
                                        <span class="block text-xs text-gray-500">Visible to users</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer mr-2">
                                        <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.categories.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-200 hover:text-gray-800 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all duration-200">
                        Create Category
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){
            const preview = document.getElementById('image-preview');
            const wrapper = document.getElementById('image-preview-wrapper');
            const placeholder = document.getElementById('upload-placeholder');

            preview.src = e.target.result;
            wrapper.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
