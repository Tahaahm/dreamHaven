@extends('layouts.admin-layout')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-3">
            <a href="{{ route('admin.service-providers.index') }}" class="w-11 h-11 flex items-center justify-center rounded-xl bg-white border-2 border-gray-200 hover:border-gray-900 text-gray-600 hover:text-gray-900 transition-all shadow-sm hover:shadow-md">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add Service Provider</h1>
                <p class="text-sm text-gray-600 mt-1">Create a comprehensive service provider profile</p>
            </div>
        </div>
    </div>

    {{-- ERROR ALERT SECTION --}}
    @if ($errors->any())
    <div class="mb-8 rounded-2xl bg-red-50 p-4 border border-red-200 shadow-sm animate-pulse">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-times-circle text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-red-800">
                    Unable to create provider
                </h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul role="list" class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.service-providers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

            {{-- LEFT SIDEBAR --}}
            <div class="xl:col-span-1 space-y-6">

                {{-- Settings Card --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-cog"></i> Settings
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">

                        {{-- Image Upload --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">Profile Image</label>
                            <div class="relative">
                                <input type="file" name="profile_image" id="profile_image" accept="image/*" class="hidden" onchange="previewImage(event)">
                                <label for="profile_image" class="flex flex-col items-center justify-center w-full h-52 border-3 border-dashed border-gray-300 rounded-2xl hover:border-gray-900 transition-all cursor-pointer group bg-gray-50 hover:bg-white">
                                    <div id="preview-container" class="hidden w-full h-full">
                                        <img id="image-preview" class="w-full h-full object-cover rounded-2xl">
                                    </div>
                                    <div id="upload-placeholder" class="text-center px-4">
                                        <div class="w-16 h-16 bg-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                            <i class="fas fa-camera text-2xl text-white"></i>
                                        </div>
                                        <p class="text-sm font-bold text-gray-900">Upload Image</p>
                                        <p class="text-xs text-gray-600 mt-1">PNG, JPG up to 2MB</p>
                                    </div>
                                </label>
                            </div>
                            @error('profile_image') <p class="text-red-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Verified Badge --}}
                        <div class="pt-4 border-t-2 border-gray-100">
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-gray-200 hover:border-gray-900 transition-all cursor-pointer group bg-white">
                                <input type="checkbox" name="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }} class="mt-0.5 w-5 h-5 rounded-md border-2 border-gray-300 text-gray-900 focus:ring-2 focus:ring-gray-900">
                                <div class="flex-1">
                                    <span class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-check-circle text-blue-600"></i> Verified Provider
                                    </span>
                                    <p class="text-xs text-gray-600 mt-1">Show verified badge on profile</p>
                                </div>
                            </label>
                        </div>

                        {{-- Subscription Plan --}}
                        <div class="pt-4 border-t-2 border-gray-100">
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                <i class="fas fa-gem mr-1 text-purple-600"></i> Subscription Plan
                            </label>
                            <select name="plan_id" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-gray-900">
                                <option value="">No Plan (Free)</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} - ${{ number_format($plan->monthly_price, 0) }}/mo
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-600 mt-2"><i class="fas fa-info-circle mr-1"></i> Activates immediately</p>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="w-full bg-gray-900 hover:bg-black text-white py-3.5 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                            <i class="fas fa-check mr-2"></i>Create Provider
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT CONTENT --}}
            <div class="xl:col-span-3 space-y-6">

                {{-- 1. Company Details --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-building"></i> Company Details</h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-900 mb-2">Company Name <span class="text-red-600">*</span></label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium" placeholder="Elite Construction Services">
                                @error('company_name') <p class="text-red-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Category <span class="text-red-600">*</span></label>
                                <select name="category_id" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-red-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Business Type</label>
                                <select name="business_type" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium">
                                    <option value="">Select Type</option>
                                    @foreach(['contractor', 'consultant', 'supplier', 'service'] as $type)
                                        <option value="{{ $type }}" {{ old('business_type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Email Address <span class="text-red-600">*</span></label>
                                {{-- Highlights red if error --}}
                                <input type="email" name="email_address" value="{{ old('email_address') }}" required
                                       class="w-full px-4 py-3 rounded-xl border-2 {{ $errors->has('email_address') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium"
                                       placeholder="contact@company.com">
                                @error('email_address')
                                    <p class="text-red-600 text-sm mt-1 font-bold flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Phone Number <span class="text-red-600">*</span></label>
                                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium" placeholder="+964 XXX XXX XXXX">
                                @error('phone_number') <p class="text-red-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Website URL</label>
                                <input type="url" name="website_url" value="{{ old('website_url') }}" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium" placeholder="https://company.com">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Years in Business</label>
                                <input type="number" name="years_in_business" value="{{ old('years_in_business') }}" min="0" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium" placeholder="5">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-900 mb-2">Company Bio</label>
                                <textarea name="company_bio" rows="4" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium resize-none" placeholder="Brief description visible to users...">{{ old('company_bio') }}</textarea>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-900 mb-2">Detailed Overview</label>
                                <textarea name="company_overview" rows="6" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium resize-none" placeholder="Full company overview...">{{ old('company_overview') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Location --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-map-marker-alt"></i> Location Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">City</label>
                                <input type="text" name="city" value="{{ old('city') }}" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium" placeholder="Erbil">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">District</label>
                                <input type="text" name="district" value="{{ old('district') }}" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium" placeholder="Downtown">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Latitude</label>
                                <input type="text" name="latitude" value="{{ old('latitude') }}" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-mono text-sm" placeholder="36.191113">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Longitude</label>
                                <input type="text" name="longitude" value="{{ old('longitude') }}" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-mono text-sm" placeholder="44.009167">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Gallery Images --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-images"></i> Gallery</h3>
                    </div>
                    <div class="p-6">
                        <div id="gallery-container" class="space-y-4">
                            {{-- JS Injects here --}}
                        </div>
                        <button type="button" onclick="addGalleryItem()" class="mt-4 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all">
                            <i class="fas fa-plus mr-2"></i>Add Image
                        </button>
                    </div>
                </div>

                {{-- 4. Service Offerings --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-briefcase"></i> Services</h3>
                    </div>
                    <div class="p-6">
                        <div id="offerings-container" class="space-y-4">
                            {{-- JS Injects here --}}
                        </div>
                        <button type="button" onclick="addOfferingItem()" class="mt-4 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all">
                            <i class="fas fa-plus mr-2"></i>Add Service
                        </button>
                    </div>
                </div>

                {{-- 5. Reviews --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-star"></i> Reviews</h3>
                    </div>
                    <div class="p-6">
                        <div id="reviews-container" class="space-y-4">
                             {{-- JS Injects here --}}
                        </div>
                        <button type="button" onclick="addReviewItem()" class="mt-4 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all">
                            <i class="fas fa-plus mr-2"></i>Add Review
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('preview-container').classList.remove('hidden');
                document.getElementById('upload-placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    // --- Dynamic Gallery ---
    function addGalleryItem() {
        const container = document.getElementById('gallery-container');
        const newItem = document.createElement('div');
        newItem.className = 'gallery-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative mt-4';
        newItem.innerHTML = `
            <button type="button" onclick="this.closest('.gallery-item').remove()" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Image</label>
                    <input type="file" name="gallery_images[]" accept="image/*" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Title</label>
                    <input type="text" name="gallery_titles[]" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="Modern Villa">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Description</label>
                    <input type="text" name="gallery_descriptions[]" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="Project Description">
                </div>
            </div>
        `;
        container.appendChild(newItem);
    }

    // --- Dynamic Offerings ---
    function addOfferingItem() {
        const container = document.getElementById('offerings-container');
        const newItem = document.createElement('div');
        newItem.className = 'offering-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative mt-4';
        newItem.innerHTML = `
            <button type="button" onclick="this.closest('.offering-item').remove()" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Title</label>
                    <input type="text" name="offering_titles[]" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="Service Name">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Description</label>
                    <textarea name="offering_descriptions[]" rows="2" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 resize-none" placeholder="Details..."></textarea>
                </div>
                <div class="flex gap-4 items-center">
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-900 mb-2">Price Range</label>
                        <input type="text" name="offering_prices[]" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="$5k - $50k">
                    </div>
                    <div class="pt-7">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="offering_active[]" value="1" checked class="w-5 h-5 rounded-md border-gray-300">
                            <span class="font-bold text-sm">Active</span>
                        </label>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newItem);
    }

    // --- Dynamic Reviews ---
    function addReviewItem() {
        const container = document.getElementById('reviews-container');
        const newItem = document.createElement('div');
        newItem.className = 'review-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative mt-4';
        newItem.innerHTML = `
            <button type="button" onclick="this.closest('.review-item').remove()" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Reviewer Name</label>
                    <input type="text" name="reviewer_names[]" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="Client Name">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Rating</label>
                    <select name="reviewer_ratings[]" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200">
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Content</label>
                    <textarea name="reviewer_contents[]" rows="2" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 resize-none" placeholder="Review text..."></textarea>
                </div>
            </div>
        `;
        container.appendChild(newItem);
    }

    // On Load: Add one blank item if empty (optional)
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('gallery-container').children.length === 0) addGalleryItem();
        if(document.getElementById('offerings-container').children.length === 0) addOfferingItem();
        if(document.getElementById('reviews-container').children.length === 0) addReviewItem();
    });
</script>
@endsection
