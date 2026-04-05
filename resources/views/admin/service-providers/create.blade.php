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

    {{-- Language Tab Switcher (Global) --}}
    <div class="mb-6 flex items-center gap-2 bg-white border-2 border-gray-200 rounded-2xl p-1.5 w-fit shadow-sm">
        <button type="button" onclick="switchLang('en')" id="tab-en"
            class="lang-tab active-lang px-5 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
            🇬🇧 English
        </button>
        <button type="button" onclick="switchLang('ar')" id="tab-ar"
            class="lang-tab px-5 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
            🇮🇶 العربية
        </button>
        <button type="button" onclick="switchLang('ku')" id="tab-ku"
            class="lang-tab px-5 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
            🏔️ کوردی
        </button>
    </div>

    {{-- Completion indicator --}}
    <div class="mb-6 p-4 bg-white border-2 border-gray-200 rounded-2xl shadow-sm flex items-center gap-4">
        <span class="text-sm font-bold text-gray-700">Translation Progress:</span>
        <div class="flex gap-3">
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-blue-500" id="dot-en"></div>
                <span class="text-xs font-semibold text-gray-600">EN <span id="pct-en" class="text-blue-600">0%</span></span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-green-500" id="dot-ar"></div>
                <span class="text-xs font-semibold text-gray-600">AR <span id="pct-ar" class="text-green-600">0%</span></span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-orange-500" id="dot-ku"></div>
                <span class="text-xs font-semibold text-gray-600">KU <span id="pct-ku" class="text-orange-600">0%</span></span>
            </div>
        </div>
    </div>

    {{-- ERROR ALERT --}}
    @if ($errors->any())
    <div class="mb-8 rounded-2xl bg-red-50 p-4 border border-red-200 shadow-sm">
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

    <form action="{{ route('admin.service-providers.store') }}" method="POST" enctype="multipart/form-data" id="main-form">
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
                            <div class="relative">
                                <input type="file" name="profile_image" id="profile_image" accept="image/*" class="hidden" onchange="previewImage(event)">
                                <label for="profile_image"
                                    class="flex flex-col items-center justify-center w-full h-52 border-3 border-dashed border-gray-300 rounded-2xl hover:border-gray-900 transition-all cursor-pointer group bg-gray-50 hover:bg-white">
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

                        {{-- Verified --}}
                        <div class="pt-4 border-t-2 border-gray-100">
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-gray-200 hover:border-gray-900 transition-all cursor-pointer group bg-white">
                                <input type="checkbox" name="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }}
                                    class="mt-0.5 w-5 h-5 rounded-md border-2 border-gray-300 text-gray-900 focus:ring-2 focus:ring-gray-900">
                                <div class="flex-1">
                                    <span class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-check-circle text-blue-600"></i> Verified Provider
                                    </span>
                                    <p class="text-xs text-gray-600 mt-1">Show verified badge on profile</p>
                                </div>
                            </label>
                        </div>

                        {{-- Plan --}}
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
                        <button type="submit"
                            class="w-full bg-gray-900 hover:bg-black text-white py-3.5 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                            <i class="fas fa-check mr-2"></i>Create Provider
                        </button>
                    </div>
                </div>
            </div>

            {{-- ===== RIGHT CONTENT ===== --}}
            <div class="xl:col-span-3 space-y-6">

                {{-- 1. Company Details --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-building"></i> Company Details
                        </h3>
                        <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-3 py-1 rounded-full">
                            <i class="fas fa-language mr-1"></i> Trilingual
                        </span>
                    </div>
                    <div class="p-6 space-y-6">

                        {{-- Company Name - Trilingual --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">
                                Company Name <span class="text-red-600">*</span>
                            </label>
                            <div class="space-y-3">
                                <div class="lang-field lang-en">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">🇬🇧 EN</span>
                                    </div>
                                    <input type="text" name="company_name_en" value="{{ old('company_name_en') }}"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium"
                                        placeholder="Company Name in English" dir="ltr">
                                    @error('company_name_en') <p class="text-red-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>
                                <div class="lang-field lang-ar hidden">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-md">🇮🇶 AR</span>
                                    </div>
                                    <input type="text" name="company_name_ar" value="{{ old('company_name_ar') }}"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all font-medium"
                                        placeholder="اسم الشركة بالعربية" dir="rtl">
                                </div>
                                <div class="lang-field lang-ku hidden">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md">🏔️ KU</span>
                                    </div>
                                    <input type="text" name="company_name_ku" value="{{ old('company_name_ku') }}"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all font-medium"
                                        placeholder="ناوی کۆمپانیا بە کوردی" dir="rtl">
                                </div>
                            </div>
                        </div>

                        {{-- Category + Business Type (not multilingual) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">
                                    Category <span class="text-red-600">*</span>
                                </label>
                                <select name="category_id" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-red-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Business Type</label>
                                <select name="business_type"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium">
                                    <option value="">Select Type</option>
                                    @php
                                        $businessTypes = [
                                            'Construction & Building' => [
                                                'General Contractor',
                                                'Construction Company',
                                                'Civil Engineering',
                                                'Structural Engineering',
                                            ],
                                            'Design & Architecture' => [
                                                'Architecture Studio',
                                                'Interior Design',
                                                'Kitchen & Interiors Design',
                                                'Landscape Design',
                                                'Urban Planning',
                                            ],
                                            'Home Services' => [
                                                'Cleaning Services',
                                                'Maintenance & Repair',
                                                'Plumbing',
                                                'Electrical Services',
                                                'HVAC & Air Conditioning',
                                                'Pest Control',
                                            ],
                                            'Finishing & Decoration' => [
                                                'Interior Decoration',
                                                'Flooring & Tiling',
                                                'Painting & Wallpaper',
                                                'Curtain & Blinds',
                                                'Furniture & Carpentry',
                                                'Lighting & Fixtures',
                                                'Kitchen Cabinets',
                                            ],
                                            'Outdoor & Landscaping' => [
                                                'Landscaping',
                                                'Garden Design',
                                                'Irrigation Systems',
                                                'Swimming Pool',
                                            ],
                                            'Security & Technology' => [
                                                'Security Systems',
                                                'Smart Home',
                                                'CCTV & Surveillance',
                                                'Access Control',
                                                'IT & Networking',
                                            ],
                                            'Real Estate' => [
                                                'Real Estate Agency',
                                                'Property Management',
                                                'Real Estate Developer',
                                                'Property Valuation',
                                            ],
                                            'Events & Exhibition' => [
                                                'Event Management',
                                                'Exhibition Organizer',
                                                'Conference Services',
                                            ],
                                            'Consulting & Professional' => [
                                                'Consultant',
                                                'Legal Services',
                                                'Financial Services',
                                                'Project Management',
                                            ],
                                            'Supply & Manufacturing' => [
                                                'Building Materials Supplier',
                                                'Furniture Manufacturer',
                                                'Equipment Supplier',
                                                'Wholesaler',
                                            ],
                                            'Other' => [
                                                'Service Provider',
                                                'Other',
                                            ],
                                        ];
                                    @endphp
                                    @foreach($businessTypes as $group => $types)
                                        <optgroup label="{{ $group }}">
                                            @foreach($types as $type)
                                                <option value="{{ $type }}" {{ old('business_type') == $type ? 'selected' : '' }}>
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Contact (not multilingual) --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Email <span class="text-red-600">*</span></label>
                                <input type="email" name="email_address" value="{{ old('email_address') }}" required
                                    class="w-full px-4 py-3 rounded-xl border-2 {{ $errors->has('email_address') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium"
                                    placeholder="contact@company.com">
                                @error('email_address') <p class="text-red-600 text-xs mt-1 font-bold flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Phone <span class="text-red-600">*</span></label>
                                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium"
                                    placeholder="+964 XXX XXX XXXX">
                                @error('phone_number') <p class="text-red-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Years in Business</label>
                                <input type="number" name="years_in_business" value="{{ old('years_in_business') }}" min="0"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium"
                                    placeholder="5">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Website URL</label>
                            <input type="url" name="website_url" value="{{ old('website_url') }}"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium"
                                placeholder="https://company.com">
                        </div>

                        {{-- Divider with label --}}
                        <div class="flex items-center gap-3 pt-2">
                            <div class="flex-1 h-px bg-gray-200"></div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-1">
                                <i class="fas fa-language"></i> Multilingual Content
                            </span>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>

                        {{-- Company Bio - Trilingual --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">Company Bio
                                <span class="text-xs font-normal text-gray-500 ml-1">(Short tagline shown on cards)</span>
                            </label>
                            <div class="space-y-3">
                                <div class="lang-field lang-en">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">🇬🇧 EN</span>
                                    </div>
                                    <textarea name="company_bio_en" rows="2"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium resize-none"
                                        placeholder="Short description visible to users..." dir="ltr">{{ old('company_bio_en') }}</textarea>
                                </div>
                                <div class="lang-field lang-ar hidden">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-md">🇮🇶 AR</span>
                                    </div>
                                    <textarea name="company_bio_ar" rows="2"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all font-medium resize-none"
                                        placeholder="وصف قصير للشركة..." dir="rtl">{{ old('company_bio_ar') }}</textarea>
                                </div>
                                <div class="lang-field lang-ku hidden">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md">🏔️ KU</span>
                                    </div>
                                    <textarea name="company_bio_ku" rows="2"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all font-medium resize-none"
                                        placeholder="کورتەی کۆمپانیاکە..." dir="rtl">{{ old('company_bio_ku') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Business Description - Trilingual --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">Business Description
                                <span class="text-xs font-normal text-gray-500 ml-1">(Detailed services description)</span>
                            </label>
                            <div class="space-y-3">
                                <div class="lang-field lang-en">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">🇬🇧 EN</span>
                                    </div>
                                    <textarea name="business_description_en" rows="3"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium resize-none"
                                        placeholder="Detailed description of services offered..." dir="ltr">{{ old('business_description_en') }}</textarea>
                                </div>
                                <div class="lang-field lang-ar hidden">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-md">🇮🇶 AR</span>
                                    </div>
                                    <textarea name="business_description_ar" rows="3"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all font-medium resize-none"
                                        placeholder="وصف تفصيلي للخدمات المقدمة..." dir="rtl">{{ old('business_description_ar') }}</textarea>
                                </div>
                                <div class="lang-field lang-ku hidden">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md">🏔️ KU</span>
                                    </div>
                                    <textarea name="business_description_ku" rows="3"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all font-medium resize-none"
                                        placeholder="وەسفی تفصیلی خزمەتگوزارییەکان..." dir="rtl">{{ old('business_description_ku') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Company Overview - Trilingual --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">Company Overview
                                <span class="text-xs font-normal text-gray-500 ml-1">(Full story shown on detail page)</span>
                            </label>
                            <div class="space-y-3">
                                <div class="lang-field lang-en">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">🇬🇧 EN</span>
                                    </div>
                                    <textarea name="company_overview_en" rows="5"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium resize-none"
                                        placeholder="Full company overview and story..." dir="ltr">{{ old('company_overview_en') }}</textarea>
                                </div>
                                <div class="lang-field lang-ar hidden">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-md">🇮🇶 AR</span>
                                    </div>
                                    <textarea name="company_overview_ar" rows="5"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all font-medium resize-none"
                                        placeholder="نظرة عامة كاملة عن الشركة..." dir="rtl">{{ old('company_overview_ar') }}</textarea>
                                </div>
                                <div class="lang-field lang-ku hidden">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md">🏔️ KU</span>
                                    </div>
                                    <textarea name="company_overview_ku" rows="5"
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all font-medium resize-none"
                                        placeholder="پوختەی تەواوی کۆمپانیاکە..." dir="rtl">{{ old('company_overview_ku') }}</textarea>
                                </div>
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
                    <div class="p-6">
                        <div class="space-y-3">
                            @php
                                $days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                            @endphp
                            @foreach($days as $day)
                            <div class="flex items-center gap-4 p-3 rounded-xl border-2 border-gray-100 hover:border-gray-200 transition-all" id="hours-row-{{ $day }}">
                                <div class="w-28">
                                    <span class="text-sm font-bold text-gray-700 capitalize">{{ $day }}</span>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="hours_closed[{{ $day }}]" value="1"
                                        onchange="toggleDayHours('{{ $day }}', this.checked)"
                                        class="w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400">
                                    <span class="text-xs font-semibold text-gray-500">Closed</span>
                                </label>
                                <div class="flex items-center gap-2 flex-1 hours-time-fields-{{ $day }}">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 font-semibold">Open</span>
                                        <input type="time" name="hours_open[{{ $day }}]" value="08:00"
                                            class="px-3 py-2 rounded-lg border-2 border-gray-200 text-sm font-medium focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                                    </div>
                                    <span class="text-gray-400 font-bold">—</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 font-semibold">Close</span>
                                        <input type="time" name="hours_close[{{ $day }}]" value="17:00"
                                            class="px-3 py-2 rounded-lg border-2 border-gray-200 text-sm font-medium focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                                    </div>
                                </div>
                                <div class="hidden hours-closed-badge-{{ $day }}">
                                    <span class="text-xs font-bold text-red-500 bg-red-50 px-3 py-1 rounded-full">Closed</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 3. Location --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt"></i> Location Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">City</label>
                                <input type="text" name="city" value="{{ old('city') }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium"
                                    placeholder="Erbil">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">District</label>
                                <input type="text" name="district" value="{{ old('district') }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-all font-medium"
                                    placeholder="Downtown">
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

                {{-- 4. Gallery --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-images"></i> Gallery
                        </h3>
                    </div>
                    <div class="p-6">
                        <div id="gallery-container" class="space-y-4"></div>
                        <button type="button" onclick="addGalleryItem()"
                            class="mt-4 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all">
                            <i class="fas fa-plus mr-2"></i>Add Image
                        </button>
                    </div>
                </div>

                {{-- 5. Services --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-briefcase"></i> Services
                            <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full ml-1">
                                <i class="fas fa-language mr-1"></i>Trilingual
                            </span>
                        </h3>
                    </div>
                    <div class="p-6">
                        <div id="offerings-container" class="space-y-4"></div>
                        <button type="button" onclick="addOfferingItem()"
                            class="mt-4 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all">
                            <i class="fas fa-plus mr-2"></i>Add Service
                        </button>
                    </div>
                </div>

                {{-- 6. Reviews --}}
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b-2 border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-star"></i> Reviews
                        </h3>
                    </div>
                    <div class="p-6">
                        <div id="reviews-container" class="space-y-4"></div>
                        <button type="button" onclick="addReviewItem()"
                            class="mt-4 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl font-bold transition-all">
                            <i class="fas fa-plus mr-2"></i>Add Review
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<style>
    .lang-tab {
        color: #6b7280;
        background: transparent;
    }
    .active-lang {
        background: #111827;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    [dir="rtl"] {
        font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
    }
</style>

<script>
    // ==============================
    // LANGUAGE SWITCHER
    // ==============================
    let currentLang = 'en';

    function switchLang(lang) {
        currentLang = lang;

        // Update tabs
        document.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active-lang'));
        document.getElementById('tab-' + lang).classList.add('active-lang');

        // Show/hide all lang fields
        ['en','ar','ku'].forEach(l => {
            document.querySelectorAll('.lang-' + l).forEach(el => {
                el.classList.toggle('hidden', l !== lang);
            });
        });

        updateProgress();
    }

    // ==============================
    // PROGRESS TRACKER
    // ==============================
    function updateProgress() {
        const langs = ['en', 'ar', 'ku'];
        const textFields = {
            en: ['company_name_en','company_bio_en','business_description_en','company_overview_en'],
            ar: ['company_name_ar','company_bio_ar','business_description_ar','company_overview_ar'],
            ku: ['company_name_ku','company_bio_ku','business_description_ku','company_overview_ku'],
        };

        langs.forEach(lang => {
            const fields = textFields[lang];
            const filled = fields.filter(name => {
                const el = document.querySelector(`[name="${name}"]`);
                return el && el.value.trim().length > 0;
            }).length;
            const pct = Math.round((filled / fields.length) * 100);
            document.getElementById('pct-' + lang).textContent = pct + '%';
        });
    }

    // Listen to input changes for progress
    document.addEventListener('input', function(e) {
        if (e.target.name && (e.target.name.endsWith('_en') || e.target.name.endsWith('_ar') || e.target.name.endsWith('_ku'))) {
            updateProgress();
        }
    });

    // ==============================
    // BUSINESS HOURS
    // ==============================
    function toggleDayHours(day, isClosed) {
        const timeFields = document.querySelector('.hours-time-fields-' + day);
        const badge = document.querySelector('.hours-closed-badge-' + day);
        timeFields.classList.toggle('hidden', isClosed);
        badge.classList.toggle('hidden', !isClosed);
    }

    // ==============================
    // IMAGE PREVIEW
    // ==============================
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('preview-container').classList.remove('hidden');
                document.getElementById('upload-placeholder').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    // ==============================
    // GALLERY
    // ==============================
    let galleryCount = 0;
    function addGalleryItem() {
        const container = document.getElementById('gallery-container');
        const idx = galleryCount++;
        const item = document.createElement('div');
        item.className = 'gallery-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative';
        item.innerHTML = `
            <button type="button" onclick="this.closest('.gallery-item').remove()"
                class="absolute top-3 right-3 text-red-500 hover:text-red-700">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Image</label>
                    <input type="file" name="gallery_images[]" accept="image/*"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Title (EN)</label>
                    <input type="text" name="gallery_titles_en[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="Project Title" dir="ltr">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Title (AR)</label>
                    <input type="text" name="gallery_titles_ar[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="عنوان المشروع" dir="rtl">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Title (KU)</label>
                    <input type="text" name="gallery_titles_ku[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="ناونیشانی پرۆژە" dir="rtl">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Description (EN)</label>
                    <input type="text" name="gallery_descriptions_en[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="Project description" dir="ltr">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Description (AR)</label>
                    <input type="text" name="gallery_descriptions_ar[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="وصف المشروع" dir="rtl">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Description (KU)</label>
                    <input type="text" name="gallery_descriptions_ku[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="وەسفی پرۆژەکە" dir="rtl">
                </div>
            </div>
        `;
        container.appendChild(item);
    }

    // ==============================
    // OFFERINGS (Trilingual)
    // ==============================
    function addOfferingItem() {
        const container = document.getElementById('offerings-container');
        const item = document.createElement('div');
        item.className = 'offering-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative';
        item.innerHTML = `
            <button type="button" onclick="this.closest('.offering-item').remove()"
                class="absolute top-3 right-3 text-red-500 hover:text-red-700">
                <i class="fas fa-times-circle text-xl"></i>
            </button>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">
                        <span class="text-blue-600">EN</span> Title
                    </label>
                    <input type="text" name="offering_titles_en[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="Service Name" dir="ltr">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">
                        <span class="text-green-600">AR</span> Title
                    </label>
                    <input type="text" name="offering_titles_ar[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="اسم الخدمة" dir="rtl">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">
                        <span class="text-orange-600">KU</span> Title
                    </label>
                    <input type="text" name="offering_titles_ku[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="ناوی خزمەتگوزاری" dir="rtl">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">
                        <span class="text-blue-600">EN</span> Description
                    </label>
                    <textarea name="offering_descriptions_en[]" rows="2"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 resize-none" placeholder="Details..." dir="ltr"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">
                        <span class="text-green-600">AR</span> Description
                    </label>
                    <textarea name="offering_descriptions_ar[]" rows="2"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 resize-none" placeholder="التفاصيل..." dir="rtl"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">
                        <span class="text-orange-600">KU</span> Description
                    </label>
                    <textarea name="offering_descriptions_ku[]" rows="2"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 resize-none" placeholder="وردەکارییەکان..." dir="rtl"></textarea>
                </div>
            </div>

            <div class="flex gap-4 items-center">
                <div class="flex-1">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Price Range</label>
                    <input type="text" name="offering_prices[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="$5k - $50k">
                </div>
                <div class="pt-7">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="offering_active[]" value="1" checked class="w-5 h-5 rounded-md border-gray-300">
                        <span class="font-bold text-sm">Active</span>
                    </label>
                </div>
            </div>
        `;
        container.appendChild(item);
    }

    // ==============================
    // REVIEWS
    // ==============================
    function addReviewItem() {
        const container = document.getElementById('reviews-container');
        const item = document.createElement('div');
        item.className = 'review-item border-2 border-gray-200 rounded-xl p-5 bg-gray-50 relative';
        item.innerHTML = `
            <button type="button" onclick="this.closest('.review-item').remove()"
                class="absolute top-3 right-3 text-red-500 hover:text-red-700">
                <i class="fas fa-times-circle text-xl"></i>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Reviewer Name</label>
                    <input type="text" name="reviewer_names[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="Client Name">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Rating</label>
                    <select name="reviewer_ratings[]" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200">
                        <option value="5">⭐⭐⭐⭐⭐ 5 Stars</option>
                        <option value="4">⭐⭐⭐⭐ 4 Stars</option>
                        <option value="3">⭐⭐⭐ 3 Stars</option>
                        <option value="2">⭐⭐ 2 Stars</option>
                        <option value="1">⭐ 1 Star</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Review Content</label>
                    <textarea name="reviewer_contents[]" rows="2"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 resize-none" placeholder="Review text..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Service Type</label>
                    <input type="text" name="reviewer_service_types[]"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200" placeholder="Kitchen Renovation">
                </div>
                <div class="flex gap-4 pt-7">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="reviewer_verified[]" value="1" class="w-4 h-4 rounded">
                        <span class="text-sm font-bold text-gray-700">Verified</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="reviewer_featured[]" value="1" class="w-4 h-4 rounded">
                        <span class="text-sm font-bold text-gray-700">Featured</span>
                    </label>
                </div>
            </div>
        `;
        container.appendChild(item);
    }

    // ==============================
    // INIT
    // ==============================
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('gallery-container').children.length === 0) addGalleryItem();
        if (document.getElementById('offerings-container').children.length === 0) addOfferingItem();
        if (document.getElementById('reviews-container').children.length === 0) addReviewItem();
        updateProgress();
    });
</script>
@endsection
