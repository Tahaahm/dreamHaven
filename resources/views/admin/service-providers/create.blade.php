@extends('layouts.admin-layout')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-3">
            <a href="{{ route('admin.service-providers.index') }}"
               class="w-11 h-11 flex items-center justify-center rounded-xl bg-white border-2 border-gray-200 hover:border-gray-900 text-gray-600 hover:text-gray-900 transition-all shadow-sm hover:shadow-md">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add Service Provider</h1>
                <p class="text-sm text-gray-600 mt-1">Create a trilingual service provider profile (English · Arabic · Kurdish)</p>
            </div>
        </div>
    </div>

    {{-- ERROR ALERT --}}
    @if ($errors->any())
    <div class="mb-6 rounded-2xl bg-red-50 p-4 border border-red-200 shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0"><i class="fas fa-times-circle text-red-400 text-xl"></i></div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-red-800">Unable to create provider</h3>
                <ul class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.service-providers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

            {{-- ===== LEFT SIDEBAR ===== --}}
            <div class="xl:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-cog"></i> Settings
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">

                        {{-- Profile Image --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">Profile Image</label>
                            <input type="file" name="profile_image" id="profile_image" accept="image/*" class="hidden" onchange="previewImage(event)">
                            <label for="profile_image"
                                class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-2xl hover:border-gray-900 transition-all cursor-pointer group bg-gray-50 hover:bg-white">
                                <div id="preview-container" class="hidden w-full h-full">
                                    <img id="image-preview" class="w-full h-full object-cover rounded-2xl">
                                </div>
                                <div id="upload-placeholder" class="text-center px-4">
                                    <div class="w-14 h-14 bg-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-camera text-xl text-white"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900">Upload Image</p>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG up to 2MB</p>
                                </div>
                            </label>
                            @error('profile_image') <p class="text-red-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Verified --}}
                        <div class="pt-4 border-t-2 border-gray-100">
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-gray-200 hover:border-gray-900 transition-all cursor-pointer bg-white">
                                <input type="checkbox" name="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }}
                                    class="mt-0.5 w-5 h-5 rounded border-gray-300 text-gray-900 focus:ring-2 focus:ring-gray-900">
                                <div>
                                    <span class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-check-circle text-blue-600"></i> Verified Provider
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">Show verified badge on profile</p>
                                </div>
                            </label>
                        </div>

                        {{-- Plan --}}
                        <div class="pt-4 border-t-2 border-gray-100">
                            <label class="block text-sm font-bold text-gray-900 mb-2">
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
                            <p class="text-xs text-gray-500 mt-2"><i class="fas fa-info-circle mr-1"></i> Activates immediately</p>
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                            class="w-full bg-gray-900 hover:bg-black text-white py-3.5 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                            <i class="fas fa-check mr-2"></i>Create Provider
                        </button>
                    </div>
                </div>
            </div>

            {{-- ===== RIGHT CONTENT ===== --}}
            <div class="xl:col-span-3 space-y-6">

                {{-- ===== 1. Company Details ===== --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-building"></i> Company Details
                        </h3>
                    </div>
                    <div class="p-6 space-y-5">

                        {{-- Company Name - single field --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">
                                Company Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" required
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm"
                                placeholder="Lenya Kitchen & Interiors">
                            @error('company_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Category + Business Type --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Category <span class="text-red-500">*</span></label>
                                <select name="category_id" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Business Type</label>
                                <select name="business_type"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
                                    <option value="">Select Type</option>
                                    @php
                                        $businessTypes = [
                                            'Construction & Building' => ['General Contractor','Construction Company','Civil Engineering','Structural Engineering'],
                                            'Design & Architecture'   => ['Architecture Studio','Interior Design','Kitchen & Interiors Design','Landscape Design','Urban Planning'],
                                            'Home Services'           => ['Cleaning Services','Maintenance & Repair','Plumbing','Electrical Services','HVAC & Air Conditioning','Pest Control'],
                                            'Finishing & Decoration'  => ['Interior Decoration','Flooring & Tiling','Painting & Wallpaper','Curtain & Blinds','Furniture & Carpentry','Lighting & Fixtures','Kitchen Cabinets'],
                                            'Outdoor & Landscaping'   => ['Landscaping','Garden Design','Irrigation Systems','Swimming Pool'],
                                            'Security & Technology'   => ['Security Systems','Smart Home','CCTV & Surveillance','Access Control','IT & Networking'],
                                            'Real Estate'             => ['Real Estate Agency','Property Management','Real Estate Developer','Property Valuation'],
                                            'Events & Exhibition'     => ['Event Management','Exhibition Organizer','Conference Services'],
                                            'Consulting & Professional'=> ['Consultant','Legal Services','Financial Services','Project Management'],
                                            'Supply & Manufacturing'  => ['Building Materials Supplier','Furniture Manufacturer','Equipment Supplier','Wholesaler'],
                                            'Other'                   => ['Service Provider','Other'],
                                        ];
                                    @endphp
                                    @foreach($businessTypes as $group => $types)
                                        <optgroup label="{{ $group }}">
                                            @foreach($types as $type)
                                                <option value="{{ $type }}" {{ old('business_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Contact --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email_address" value="{{ old('email_address') }}" required
                                    class="w-full px-4 py-3 rounded-xl border-2 {{ $errors->has('email_address') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm"
                                    placeholder="contact@company.com">
                                @error('email_address') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Phone <span class="text-red-500">*</span></label>
                                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm"
                                    placeholder="+964 XXX XXX XXXX">
                                @error('phone_number') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Years in Business</label>
                                <input type="number" name="years_in_business" value="{{ old('years_in_business') }}" min="0"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm"
                                    placeholder="10">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Website URL</label>
                            <input type="url" name="website_url" value="{{ old('website_url') }}"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm"
                                placeholder="https://company.com">
                        </div>

                        {{-- Company Bio --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">
                                Company Bio
                                <span class="text-xs font-normal text-gray-400 ml-1">(Short tagline shown on cards)</span>
                            </label>
                            <textarea name="company_bio" rows="3"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all text-sm resize-none"
                                placeholder="Short tagline about the company...">{{ old('company_bio') }}</textarea>
                        </div>

                        {{-- Business Description --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">
                                Business Description
                                <span class="text-xs font-normal text-gray-400 ml-1">(Detailed services)</span>
                            </label>
                            <textarea name="business_description" rows="4"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all text-sm resize-none"
                                placeholder="Describe the services offered...">{{ old('business_description') }}</textarea>
                        </div>

                        {{-- Company Overview --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">
                                Company Overview
                                <span class="text-xs font-normal text-gray-400 ml-1">(Full story on detail page)</span>
                            </label>
                            <textarea name="company_overview" rows="5"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all text-sm resize-none"
                                placeholder="Full company story and history...">{{ old('company_overview') }}</textarea>
                        </div>

                    </div>
                </div>

                {{-- ===== 2. Business Hours ===== --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-clock"></i> Business Hours
                        </h3>
                    </div>
                    <div class="p-6 space-y-2">
                        @foreach(['sunday','monday','tuesday','wednesday','thursday','friday','saturday'] as $day)
                        <div class="flex items-center gap-4 p-3 rounded-xl border-2 border-gray-100 hover:border-gray-200 transition-all">
                            <div class="w-24 shrink-0">
                                <span class="text-sm font-bold text-gray-700 capitalize">{{ $day }}</span>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer shrink-0">
                                <input type="checkbox" name="hours_closed[{{ $day }}]" value="1"
                                    onchange="toggleDay('{{ $day }}', this.checked)"
                                    class="w-4 h-4 rounded border-gray-300 text-red-500">
                                <span class="text-xs font-semibold text-gray-500">Closed</span>
                            </label>
                            <div class="flex items-center gap-2 flex-1 day-times-{{ $day }}">
                                <span class="text-xs text-gray-400">Open</span>
                                <input type="time" name="hours_open[{{ $day }}]" value="08:00"
                                    class="px-3 py-2 rounded-lg border-2 border-gray-200 text-sm font-medium focus:ring-2 focus:ring-gray-900">
                                <span class="text-gray-300">—</span>
                                <span class="text-xs text-gray-400">Close</span>
                                <input type="time" name="hours_close[{{ $day }}]" value="17:00"
                                    class="px-3 py-2 rounded-lg border-2 border-gray-200 text-sm font-medium focus:ring-2 focus:ring-gray-900">
                            </div>
                            <span class="hidden text-xs font-bold text-red-500 bg-red-50 px-3 py-1 rounded-full day-closed-{{ $day }}">Closed</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- ===== 3. Location ===== --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt"></i> Location
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">City</label>
                                <input type="text" name="city" value="{{ old('city') }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm"
                                    placeholder="Erbil">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">District</label>
                                <input type="text" name="district" value="{{ old('district') }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm"
                                    placeholder="60 Meter Road">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Latitude</label>
                                <input type="text" name="latitude" value="{{ old('latitude') }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-mono text-sm"
                                    placeholder="36.191113">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Longitude</label>
                                <input type="text" name="longitude" value="{{ old('longitude') }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-mono text-sm"
                                    placeholder="44.009167">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== 4. Gallery ===== --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-images"></i> Gallery
                        </h3>
                    </div>
                    <div class="p-6">
                        <div id="gallery-container" class="space-y-4"></div>
                        <button type="button" onclick="addGalleryItem()"
                            class="mt-4 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Image
                        </button>
                    </div>
                </div>

                {{-- ===== 5. Services ===== --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-briefcase"></i> Services
                        </h3>
                    </div>
                    <div class="p-6">
                        <div id="offerings-container" class="space-y-4"></div>
                        <button type="button" onclick="addOfferingItem()"
                            class="mt-4 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Service
                        </button>
                    </div>
                </div>

                {{-- ===== 6. Reviews ===== --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-star"></i> Reviews
                        </h3>
                    </div>
                    <div class="p-6">
                        <div id="reviews-container" class="space-y-4"></div>
                        <button type="button" onclick="addReviewItem()"
                            class="mt-4 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Review
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    // ===== Image Preview =====
    function previewImage(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('image-preview').src = e.target.result;
            document.getElementById('preview-container').classList.remove('hidden');
            document.getElementById('upload-placeholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    // ===== Business Hours =====
    function toggleDay(day, closed) {
        document.querySelector('.day-times-' + day).classList.toggle('hidden', closed);
        document.querySelector('.day-closed-' + day).classList.toggle('hidden', !closed);
    }

    // ===== Gallery =====
    function addGalleryItem() {
        const container = document.getElementById('gallery-container');
        const item = document.createElement('div');
        item.className = 'gallery-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative';
        item.innerHTML = `
            <button type="button" onclick="this.closest('.gallery-item').remove()"
                class="absolute top-3 right-3 text-red-400 hover:text-red-600">
                <i class="fas fa-times-circle text-lg"></i>
            </button>
            <div class="mb-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Image File</label>
                <input type="file" name="gallery_images[]" accept="image/*"
                    class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Title</label>
                <input type="text" name="gallery_titles[]" class="w-full px-3 py-2.5 rounded-xl border-2 border-gray-200 text-sm" placeholder="Project title">
            </div>
            <div class="mt-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Description</label>
                <input type="text" name="gallery_descriptions[]" class="w-full px-3 py-2.5 rounded-xl border-2 border-gray-200 text-sm" placeholder="Project description">
            </div>`;
        container.appendChild(item);
    }

    // ===== Services =====
    function addOfferingItem() {
        const container = document.getElementById('offerings-container');
        const item = document.createElement('div');
        item.className = 'offering-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative';
        item.innerHTML = `
            <button type="button" onclick="this.closest('.offering-item').remove()"
                class="absolute top-3 right-3 text-red-400 hover:text-red-600">
                <i class="fas fa-times-circle text-lg"></i>
            </button>
            <div class="mb-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Title</label>
                <input type="text" name="offering_titles[]" class="w-full px-3 py-2.5 rounded-xl border-2 border-gray-200 text-sm" placeholder="Service Name">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Description</label>
                <textarea name="offering_descriptions[]" rows="3" class="w-full px-3 py-2.5 rounded-xl border-2 border-gray-200 text-sm resize-none" placeholder="Details..."></textarea>
            </div>
            <div class="flex items-center gap-4 mt-1">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Price Range</label>
                    <input type="text" name="offering_prices[]" class="w-full px-3 py-2.5 rounded-xl border-2 border-gray-200 text-sm" placeholder="e.g. $5k - $50k">
                </div>
                <div class="pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="offering_active[]" value="1" checked class="w-4 h-4 rounded border-gray-300">
                        <span class="text-sm font-bold text-gray-700">Active</span>
                    </label>
                </div>
            </div>`;
        container.appendChild(item);
    }

    // ===== Reviews =====
    function addReviewItem() {
        const container = document.getElementById('reviews-container');
        const item = document.createElement('div');
        item.className = 'review-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative';
        item.innerHTML = `
            <button type="button" onclick="this.closest('.review-item').remove()"
                class="absolute top-3 right-3 text-red-400 hover:text-red-600">
                <i class="fas fa-times-circle text-lg"></i>
            </button>
            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Reviewer Name</label>
                    <input type="text" name="reviewer_names[]" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 text-sm" placeholder="Client Name">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Rating</label>
                    <select name="reviewer_ratings[]" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 text-sm">
                        <option value="5">⭐⭐⭐⭐⭐ 5 Stars</option>
                        <option value="4">⭐⭐⭐⭐ 4 Stars</option>
                        <option value="3">⭐⭐⭐ 3 Stars</option>
                        <option value="2">⭐⭐ 2 Stars</option>
                        <option value="1">⭐ 1 Star</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Review Content</label>
                <textarea name="reviewer_contents[]" rows="2" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 text-sm resize-none" placeholder="Review text..."></textarea>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Service Type</label>
                    <input type="text" name="reviewer_service_types[]" class="w-full px-3 py-2.5 rounded-xl border-2 border-gray-200 text-sm" placeholder="Kitchen Renovation">
                </div>
                <div class="pt-5 flex gap-4">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="reviewer_verified[]" value="1" class="w-4 h-4 rounded border-gray-300">
                        <span class="text-sm font-bold text-gray-700">Verified</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="reviewer_featured[]" value="1" class="w-4 h-4 rounded border-gray-300">
                        <span class="text-sm font-bold text-gray-700">Featured</span>
                    </label>
                </div>
            </div>`;
        container.appendChild(item);
    }

    // ===== Init =====
    document.addEventListener('DOMContentLoaded', function () {
        addGalleryItem();
        addOfferingItem();
        addReviewItem();
    });
</script>
@endsection
