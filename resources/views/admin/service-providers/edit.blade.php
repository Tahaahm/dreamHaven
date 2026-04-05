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
                <h1 class="text-3xl font-bold text-gray-900">Edit Service Provider</h1>
                <p class="text-sm text-gray-600 mt-1">Update profile for <span class="font-semibold text-indigo-600">{{ $provider->company_name }}</span></p>
            </div>
        </div>
    </div>

    @if ($errors->any())
    <div class="mb-6 rounded-2xl bg-red-50 p-4 border border-red-200 shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0"><i class="fas fa-times-circle text-red-400 text-xl"></i></div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-red-800">Unable to update provider</h3>
                <ul class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.service-providers.update', $provider->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

            {{-- LEFT SIDEBAR --}}
            <div class="xl:col-span-1 space-y-6">
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
                            <input type="file" name="profile_image" id="profile_image" accept="image/*" class="hidden" onchange="previewImage(event)">
                            <label for="profile_image" class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-2xl hover:border-gray-900 transition-all cursor-pointer group bg-gray-50 hover:bg-white overflow-hidden relative">
                                <div id="preview-container" class="{{ $provider->profile_image ? '' : 'hidden' }} w-full h-full absolute inset-0">
                                    <img id="image-preview" src="{{ $provider->profile_image }}" class="w-full h-full object-cover">
                                </div>
                                <div id="upload-placeholder" class="{{ $provider->profile_image ? 'hidden' : '' }} text-center px-4 relative z-10">
                                    <div class="w-14 h-14 bg-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-camera text-xl text-white"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900">Change Image</p>
                                </div>
                            </label>
                        </div>

                        {{-- Verified --}}
                        <div class="pt-4 border-t-2 border-gray-100">
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-gray-200 hover:border-gray-900 transition-all cursor-pointer bg-white">
                                <input type="checkbox" name="is_verified" value="1" {{ $provider->is_verified ? 'checked' : '' }}
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
                                    <option value="{{ $plan->id }}" {{ $provider->plan_id == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} - ${{ number_format($plan->monthly_price, 0) }}/mo
                                    </option>
                                @endforeach
                            </select>
                            @if($provider->plan_expires_at)
                                <p class="text-xs text-gray-500 mt-2">Expires: {{ $provider->plan_expires_at->format('M d, Y') }}</p>
                            @endif
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>Update Provider
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT CONTENT --}}
            <div class="xl:col-span-3 space-y-6">

                {{-- 1. Company Details --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-building"></i> Company Details
                        </h3>
                    </div>
                    <div class="p-6 space-y-5">

                        {{-- Company Name - single field --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" name="company_name" value="{{ old('company_name', $provider->company_name) }}" required
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
                            @error('company_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Category + Business Type --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Category <span class="text-red-500">*</span></label>
                                <select name="category_id" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $provider->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Business Type</label>
                                <select name="business_type" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
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
                                                <option value="{{ $type }}" {{ $provider->business_type == $type ? 'selected' : '' }}>{{ $type }}</option>
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
                                <input type="email" name="email_address" value="{{ old('email_address', $provider->email_address) }}" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Phone <span class="text-red-500">*</span></label>
                                <input type="text" name="phone_number" value="{{ old('phone_number', $provider->phone_number) }}" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Years in Business</label>
                                <input type="number" name="years_in_business" value="{{ old('years_in_business', $provider->years_in_business) }}" min="0"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Website URL</label>
                            <input type="url" name="website_url" value="{{ old('website_url', $provider->website_url) }}"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
                        </div>

                        {{-- Divider --}}
                        <div class="flex items-center gap-3 pt-1">
                            <div class="flex-1 h-px bg-gray-200"></div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">
                                <i class="fas fa-language mr-1"></i> Multilingual Content
                            </span>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>

                        {{-- Language column headers --}}
                        <div class="grid grid-cols-3 gap-3 -mb-2">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-blue-600">
                                <span class="w-5 h-5 rounded bg-blue-100 flex items-center justify-center">🇬🇧</span> English
                            </div>
                            <div class="flex items-center gap-1.5 text-xs font-bold text-green-600">
                                <span class="w-5 h-5 rounded bg-green-100 flex items-center justify-center">🇮🇶</span> العربية
                            </div>
                            <div class="flex items-center gap-1.5 text-xs font-bold text-orange-600">
                                <span class="w-5 h-5 rounded bg-orange-100 flex items-center justify-center">🏔️</span> کوردی
                            </div>
                        </div>

                        {{-- Company Bio --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Company Bio <span class="text-xs font-normal text-gray-400">(Short tagline)</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <textarea name="company_bio_en" rows="3" class="w-full px-4 py-3 rounded-xl border-2 border-blue-200 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-sm resize-none" placeholder="Short tagline..." dir="ltr">{{ old('company_bio_en', $provider->company_bio_en) }}</textarea>
                                <textarea name="company_bio_ar" rows="3" class="w-full px-4 py-3 rounded-xl border-2 border-green-200 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-all text-sm resize-none" placeholder="الوصف القصير..." dir="rtl">{{ old('company_bio_ar', $provider->company_bio_ar) }}</textarea>
                                <textarea name="company_bio_ku" rows="3" class="w-full px-4 py-3 rounded-xl border-2 border-orange-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all text-sm resize-none" placeholder="کورتەی کۆمپانیا..." dir="rtl">{{ old('company_bio_ku', $provider->company_bio_ku) }}</textarea>
                            </div>
                        </div>

                        {{-- Business Description --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Business Description <span class="text-xs font-normal text-gray-400">(Detailed services)</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <textarea name="business_description_en" rows="4" class="w-full px-4 py-3 rounded-xl border-2 border-blue-200 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-sm resize-none" placeholder="Describe your services..." dir="ltr">{{ old('business_description_en', $provider->business_description_en) }}</textarea>
                                <textarea name="business_description_ar" rows="4" class="w-full px-4 py-3 rounded-xl border-2 border-green-200 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-all text-sm resize-none" placeholder="وصف الخدمات..." dir="rtl">{{ old('business_description_ar', $provider->business_description_ar) }}</textarea>
                                <textarea name="business_description_ku" rows="4" class="w-full px-4 py-3 rounded-xl border-2 border-orange-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all text-sm resize-none" placeholder="وەسفی خزمەتگوزارییەکان..." dir="rtl">{{ old('business_description_ku', $provider->business_description_ku) }}</textarea>
                            </div>
                        </div>

                        {{-- Company Overview --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Company Overview <span class="text-xs font-normal text-gray-400">(Full story)</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <textarea name="company_overview_en" rows="5" class="w-full px-4 py-3 rounded-xl border-2 border-blue-200 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-sm resize-none" placeholder="Full company story..." dir="ltr">{{ old('company_overview_en', $provider->company_overview_en) }}</textarea>
                                <textarea name="company_overview_ar" rows="5" class="w-full px-4 py-3 rounded-xl border-2 border-green-200 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-all text-sm resize-none" placeholder="نظرة عامة عن الشركة..." dir="rtl">{{ old('company_overview_ar', $provider->company_overview_ar) }}</textarea>
                                <textarea name="company_overview_ku" rows="5" class="w-full px-4 py-3 rounded-xl border-2 border-orange-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all text-sm resize-none" placeholder="پوختەی کۆمپانیاکە..." dir="rtl">{{ old('company_overview_ku', $provider->company_overview_ku) }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 2. Business Hours --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-clock"></i> Business Hours
                        </h3>
                    </div>
                    <div class="p-6 space-y-2">
                        @php
                            $days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                            $hours = $provider->business_hours ?? [];
                        @endphp
                        @foreach($days as $day)
                        @php
                            $isClosed = isset($hours[$day]['closed']) && $hours[$day]['closed'];
                            $openVal  = $hours[$day]['open']  ?? '08:00';
                            $closeVal = $hours[$day]['close'] ?? '17:00';
                        @endphp
                        <div class="flex items-center gap-4 p-3 rounded-xl border-2 border-gray-100 hover:border-gray-200 transition-all">
                            <div class="w-24 shrink-0">
                                <span class="text-sm font-bold text-gray-700 capitalize">{{ $day }}</span>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer shrink-0">
                                <input type="checkbox" name="hours_closed[{{ $day }}]" value="1"
                                    {{ $isClosed ? 'checked' : '' }}
                                    onchange="toggleDay('{{ $day }}', this.checked)"
                                    class="w-4 h-4 rounded border-gray-300 text-red-500">
                                <span class="text-xs font-semibold text-gray-500">Closed</span>
                            </label>
                            <div class="flex items-center gap-2 flex-1 day-times-{{ $day }} {{ $isClosed ? 'hidden' : '' }}">
                                <span class="text-xs text-gray-400">Open</span>
                                <input type="time" name="hours_open[{{ $day }}]" value="{{ $isClosed ? '08:00' : $openVal }}"
                                    class="px-3 py-2 rounded-lg border-2 border-gray-200 text-sm font-medium focus:ring-2 focus:ring-gray-900">
                                <span class="text-gray-300">—</span>
                                <span class="text-xs text-gray-400">Close</span>
                                <input type="time" name="hours_close[{{ $day }}]" value="{{ $isClosed ? '17:00' : $closeVal }}"
                                    class="px-3 py-2 rounded-lg border-2 border-gray-200 text-sm font-medium focus:ring-2 focus:ring-gray-900">
                            </div>
                            <span class="{{ $isClosed ? '' : 'hidden' }} text-xs font-bold text-red-500 bg-red-50 px-3 py-1 rounded-full day-closed-{{ $day }}">Closed</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 3. Location --}}
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
                                <input type="text" name="city" value="{{ old('city', $provider->city) }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">District</label>
                                <input type="text" name="district" value="{{ old('district', $provider->district) }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Latitude</label>
                                <input type="text" name="latitude" value="{{ old('latitude', $provider->latitude) }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-mono text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Longitude</label>
                                <input type="text" name="longitude" value="{{ old('longitude', $provider->longitude) }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-mono text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Gallery --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-images"></i> Gallery</h3>
                    </div>
                    <div class="p-6">
                        <div id="gallery-container" class="space-y-4"></div>
                        <button type="button" onclick="addGalleryItem()" class="mt-4 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Image
                        </button>
                    </div>
                </div>

                {{-- 5. Services --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-briefcase"></i> Services</h3>
                    </div>
                    <div class="p-6">
                        <div id="offerings-container" class="space-y-4"></div>
                        <button type="button" onclick="addOfferingItem()" class="mt-4 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Service
                        </button>
                    </div>
                </div>

                {{-- 6. Reviews --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-star"></i> Reviews</h3>
                    </div>
                    <div class="p-6">
                        <div id="reviews-container" class="space-y-4"></div>
                        <button type="button" onclick="addReviewItem()" class="mt-4 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Review
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    const existingGalleries = @json($provider->galleries);
    const existingOfferings = @json($provider->offerings);
    const existingReviews   = @json($provider->reviews);
</script>

<script>
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

    function toggleDay(day, closed) {
        document.querySelector('.day-times-' + day).classList.toggle('hidden', closed);
        document.querySelector('.day-closed-' + day).classList.toggle('hidden', !closed);
    }

    function langHeaders() {
        return `<div class="grid grid-cols-3 gap-3 mb-1">
            <span class="text-xs font-bold text-blue-600">🇬🇧 English</span>
            <span class="text-xs font-bold text-green-600">🇮🇶 العربية</span>
            <span class="text-xs font-bold text-orange-600">🏔️ کوردی</span>
        </div>`;
    }

    function addGalleryItem(data = null) {
        const container = document.getElementById('gallery-container');
        const item = document.createElement('div');
        item.className = 'gallery-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative';
        const imageUrl  = data ? (data.image_url  || '') : '';
        const titleEn   = data ? (data.project_title_en || data.project_title || '') : '';
        const titleAr   = data ? (data.project_title_ar || '') : '';
        const titleKu   = data ? (data.project_title_ku || '') : '';
        const descEn    = data ? (data.description_en || data.description || '') : '';
        const descAr    = data ? (data.description_ar || '') : '';
        const descKu    = data ? (data.description_ku || '') : '';
        item.innerHTML = `
            <button type="button" onclick="this.closest('.gallery-item').remove()" class="absolute top-3 right-3 text-red-400 hover:text-red-600">
                <i class="fas fa-times-circle text-lg"></i>
            </button>
            <div class="mb-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Image</label>
                ${imageUrl ? `<img src="${imageUrl}" class="h-20 w-auto rounded-lg mb-2 border object-cover">` : ''}
                <input type="hidden" name="gallery_existing_images[]" value="${imageUrl}">
                <input type="file" name="gallery_images[]" accept="image/*" class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Title</label>
                ${langHeaders()}
                <div class="grid grid-cols-3 gap-3">
                    <input type="text" name="gallery_titles_en[]" value="${titleEn}" class="px-3 py-2.5 rounded-xl border-2 border-blue-200 text-sm w-full" placeholder="Project title" dir="ltr">
                    <input type="text" name="gallery_titles_ar[]" value="${titleAr}" class="px-3 py-2.5 rounded-xl border-2 border-green-200 text-sm w-full" placeholder="عنوان المشروع" dir="rtl">
                    <input type="text" name="gallery_titles_ku[]" value="${titleKu}" class="px-3 py-2.5 rounded-xl border-2 border-orange-200 text-sm w-full" placeholder="ناوی پرۆژە" dir="rtl">
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Description</label>
                ${langHeaders()}
                <div class="grid grid-cols-3 gap-3">
                    <input type="text" name="gallery_descriptions_en[]" value="${descEn}" class="px-3 py-2.5 rounded-xl border-2 border-blue-200 text-sm w-full" placeholder="Description" dir="ltr">
                    <input type="text" name="gallery_descriptions_ar[]" value="${descAr}" class="px-3 py-2.5 rounded-xl border-2 border-green-200 text-sm w-full" placeholder="الوصف" dir="rtl">
                    <input type="text" name="gallery_descriptions_ku[]" value="${descKu}" class="px-3 py-2.5 rounded-xl border-2 border-orange-200 text-sm w-full" placeholder="وەسف" dir="rtl">
                </div>
            </div>`;
        container.appendChild(item);
    }

    function addOfferingItem(data = null) {
        const container = document.getElementById('offerings-container');
        const item = document.createElement('div');
        item.className = 'offering-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative';
        const titleEn = data ? (data.service_title_en || data.service_title || '') : '';
        const titleAr = data ? (data.service_title_ar || '') : '';
        const titleKu = data ? (data.service_title_ku || '') : '';
        const descEn  = data ? (data.service_description_en || data.service_description || '') : '';
        const descAr  = data ? (data.service_description_ar || '') : '';
        const descKu  = data ? (data.service_description_ku || '') : '';
        const price   = data ? (data.price_range || '') : '';
        const checked = data ? (data.active ? 'checked' : '') : 'checked';
        item.innerHTML = `
            <button type="button" onclick="this.closest('.offering-item').remove()" class="absolute top-3 right-3 text-red-400 hover:text-red-600">
                <i class="fas fa-times-circle text-lg"></i>
            </button>
            <div class="mb-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Title</label>
                ${langHeaders()}
                <div class="grid grid-cols-3 gap-3">
                    <input type="text" name="offering_titles_en[]" value="${titleEn}" class="px-3 py-2.5 rounded-xl border-2 border-blue-200 text-sm w-full" placeholder="Service Name" dir="ltr">
                    <input type="text" name="offering_titles_ar[]" value="${titleAr}" class="px-3 py-2.5 rounded-xl border-2 border-green-200 text-sm w-full" placeholder="اسم الخدمة" dir="rtl">
                    <input type="text" name="offering_titles_ku[]" value="${titleKu}" class="px-3 py-2.5 rounded-xl border-2 border-orange-200 text-sm w-full" placeholder="ناوی خزمەتگوزاری" dir="rtl">
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Description</label>
                ${langHeaders()}
                <div class="grid grid-cols-3 gap-3">
                    <textarea name="offering_descriptions_en[]" rows="3" class="px-3 py-2.5 rounded-xl border-2 border-blue-200 text-sm w-full resize-none" placeholder="Details..." dir="ltr">${descEn}</textarea>
                    <textarea name="offering_descriptions_ar[]" rows="3" class="px-3 py-2.5 rounded-xl border-2 border-green-200 text-sm w-full resize-none" placeholder="التفاصيل..." dir="rtl">${descAr}</textarea>
                    <textarea name="offering_descriptions_ku[]" rows="3" class="px-3 py-2.5 rounded-xl border-2 border-orange-200 text-sm w-full resize-none" placeholder="وردەکارییەکان..." dir="rtl">${descKu}</textarea>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Price Range</label>
                    <input type="text" name="offering_prices[]" value="${price}" class="w-full px-3 py-2.5 rounded-xl border-2 border-gray-200 text-sm" placeholder="e.g. $5k - $50k">
                </div>
                <div class="pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="offering_active[]" value="1" ${checked} class="w-4 h-4 rounded border-gray-300">
                        <span class="text-sm font-bold text-gray-700">Active</span>
                    </label>
                </div>
            </div>`;
        container.appendChild(item);
    }

    function addReviewItem(data = null) {
        const container = document.getElementById('reviews-container');
        const item = document.createElement('div');
        item.className = 'review-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative';
        const name    = data ? (data.reviewer_name || '') : '';
        const content = data ? (data.review_content || '') : '';
        const rating  = data ? (data.star_rating || 5) : 5;
        const svcType = data ? (data.service_type || '') : '';
        const verified  = data && data.is_verified  ? 'checked' : '';
        const featured  = data && data.is_featured  ? 'checked' : '';
        item.innerHTML = `
            <button type="button" onclick="this.closest('.review-item').remove()" class="absolute top-3 right-3 text-red-400 hover:text-red-600">
                <i class="fas fa-times-circle text-lg"></i>
            </button>
            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Reviewer Name</label>
                    <input type="text" name="reviewer_names[]" value="${name}" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 text-sm" placeholder="Client Name">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Rating</label>
                    <select name="reviewer_ratings[]" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 text-sm">
                        <option value="5" ${rating==5?'selected':''}>⭐⭐⭐⭐⭐ 5 Stars</option>
                        <option value="4" ${rating==4?'selected':''}>⭐⭐⭐⭐ 4 Stars</option>
                        <option value="3" ${rating==3?'selected':''}>⭐⭐⭐ 3 Stars</option>
                        <option value="2" ${rating==2?'selected':''}>⭐⭐ 2 Stars</option>
                        <option value="1" ${rating==1?'selected':''}>⭐ 1 Star</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-bold text-gray-900 mb-2">Review Content</label>
                <textarea name="reviewer_contents[]" rows="2" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 text-sm resize-none" placeholder="Review text...">${content}</textarea>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Service Type</label>
                    <input type="text" name="reviewer_service_types[]" value="${svcType}" class="w-full px-3 py-2.5 rounded-xl border-2 border-gray-200 text-sm" placeholder="Kitchen Renovation">
                </div>
                <div class="pt-5 flex gap-4">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="reviewer_verified[]" value="1" ${verified} class="w-4 h-4 rounded border-gray-300">
                        <span class="text-sm font-bold text-gray-700">Verified</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="reviewer_featured[]" value="1" ${featured} class="w-4 h-4 rounded border-gray-300">
                        <span class="text-sm font-bold text-gray-700">Featured</span>
                    </label>
                </div>
            </div>`;
        container.appendChild(item);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (existingGalleries.length > 0) existingGalleries.forEach(i => addGalleryItem(i));
        else addGalleryItem();

        if (existingOfferings.length > 0) existingOfferings.forEach(i => addOfferingItem(i));
        else addOfferingItem();

        if (existingReviews.length > 0) existingReviews.forEach(i => addReviewItem(i));
        else addReviewItem();
    });
</script>
@endsection
