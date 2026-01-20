@extends('layouts.admin-layout')

@section('title', 'Edit Property')

@section('content')

{{--
    =========================================================
    CRITICAL: DATA SAFETY LAYER
    Converts Arrays/JSON to Strings before HTML rendering
    =========================================================
--}}
@php
    // Helper function to safely convert values to string
    $safeString = function($val) {
        return is_array($val) ? '' : (string)($val ?? '');
    };

    // 1. Name
    $rawName = $property->name;
    $nameEn = is_array($rawName) ? ($rawName['en'] ?? '') : (is_string($rawName) ? $rawName : '');
    $nameAr = is_array($rawName) ? ($rawName['ar'] ?? '') : '';
    $nameKu = is_array($rawName) ? ($rawName['ku'] ?? '') : '';

    // 2. Description
    $rawDesc = $property->description;
    $descEn = is_array($rawDesc) ? ($rawDesc['en'] ?? '') : (is_string($rawDesc) ? $rawDesc : '');
    $descAr = is_array($rawDesc) ? ($rawDesc['ar'] ?? '') : '';
    $descKu = is_array($rawDesc) ? ($rawDesc['ku'] ?? '') : '';

    // 3. Price - UPDATED: Extract BOTH USD and IQD
    $rawPrice = $property->price;
    $priceIQD = 0;
    $priceUSD = 0;

    // Parse price if it's a string
    $priceData = is_string($rawPrice) ? json_decode($rawPrice, true) : $rawPrice;

    if (is_array($priceData)) {
        // Direct extraction
        $priceIQD = $priceData['iqd'] ?? 0;
        $priceUSD = $priceData['usd'] ?? 0;

        // Fallback for older data formats (single amount)
        if ($priceIQD == 0 && $priceUSD == 0 && isset($priceData['amount'])) {
            $currency = $priceData['currency'] ?? 'USD';
            if ($currency === 'USD') {
                $priceUSD = $priceData['amount'];
            } else {
                $priceIQD = $priceData['amount'];
            }
        }
    } elseif (is_numeric($priceData)) {
        // If it's just a number, assume IQD (or you can change logic)
        $priceIQD = $priceData;
    }

    // 4. Type
    $rawType = $property->type;
    $typeCategory = is_array($rawType) ? ($rawType['category'] ?? '') : (is_string($rawType) ? $rawType : '');

    // 5. Amenities & Features
    $rawAmenities = $property->amenities;
    $amenitiesString = is_array($rawAmenities) ? implode(', ', $rawAmenities) : (is_string($rawAmenities) ? $rawAmenities : '');

    $rawFeatures = $property->features;
    $featuresString = is_array($rawFeatures) ? implode(', ', $rawFeatures) : (is_string($rawFeatures) ? $rawFeatures : '');

    // 6. Rooms
    $rawRooms = is_array($property->rooms) ? $property->rooms : [];
    $roomBed = $rawRooms['bedroom'] ?? 0;
    if(is_array($roomBed)) $roomBed = $roomBed['count'] ?? 0;

    $roomBath = $rawRooms['bathroom'] ?? 0;
    if(is_array($roomBath)) $roomBath = $roomBath['count'] ?? 0;

    $roomLiving = $rawRooms['living_room'] ?? 0;
    if(is_array($roomLiving)) $roomLiving = $roomLiving['count'] ?? 0;

    // 7. Location
    $rawLocs = $property->locations;
    $firstLoc = [];
    if (is_array($rawLocs)) {
        if (isset($rawLocs['lat'])) {
            $firstLoc = $rawLocs;
        } elseif (isset($rawLocs[0])) {
            $firstLoc = $rawLocs[0];
        }
    }
    $lat = $firstLoc['lat'] ?? 0;
    $lng = $firstLoc['lng'] ?? 0;

    // 8. Address
    $rawAddr = is_array($property->address_details) ? $property->address_details : [];
    $cityVal = '';
    if (isset($rawAddr['city']) && is_array($rawAddr['city'])) {
        $cityVal = $rawAddr['city']['en'] ?? '';
    } elseif (isset($rawAddr['city']) && is_string($rawAddr['city'])) {
        $cityVal = $rawAddr['city'];
    }

    $distVal = '';
    if (isset($rawAddr['district']) && is_array($rawAddr['district'])) {
        $distVal = $rawAddr['district']['en'] ?? '';
    } elseif (isset($rawAddr['district']) && is_string($rawAddr['district'])) {
        $distVal = $rawAddr['district'];
    }

    $fullAddr = is_string($property->address) ? $property->address : '';

    // 9. Availability
    $rawAvailability = is_array($property->availability) ? $property->availability : [];
    $availableFrom = $rawAvailability['from'] ?? '';
    $availableTo = $rawAvailability['to'] ?? '';

    // 10. Floor Details
    $rawFloorDetails = is_array($property->floor_details) ? $property->floor_details : [];
    $totalFloors = $rawFloorDetails['total_floors'] ?? '';
    $floorPosition = $rawFloorDetails['position'] ?? '';

    // 11. Construction
    $rawConstruction = is_array($property->construction_details) ? $property->construction_details : [];
    $constructionType = $rawConstruction['type'] ?? '';
    $constructionQuality = $rawConstruction['quality'] ?? '';

    // 12. Energy
    $rawEnergy = is_array($property->energy_details) ? $property->energy_details : [];
    $energyCertificate = $rawEnergy['certificate'] ?? '';
    $energyConsumption = $rawEnergy['consumption'] ?? '';

    // 13. Furnishing
    $rawFurnishing = is_array($property->furnishing_details) ? $property->furnishing_details : [];
    $furnishingLevel = $rawFurnishing['level'] ?? '';
    $furnishingItems = '';
    if (isset($rawFurnishing['items']) && is_array($rawFurnishing['items'])) {
        $furnishingItems = implode(', ', $rawFurnishing['items']);
    } elseif (isset($rawFurnishing['items']) && is_string($rawFurnishing['items'])) {
        $furnishingItems = $rawFurnishing['items'];
    }

    // 14. SEO
    $rawSeo = is_array($property->seo_metadata) ? $property->seo_metadata : [];
    $seoTitle = $rawSeo['title'] ?? '';
    $seoDescription = $rawSeo['description'] ?? '';
    $seoKeywords = '';
    if (isset($rawSeo['keywords']) && is_array($rawSeo['keywords'])) {
        $seoKeywords = implode(', ', $rawSeo['keywords']);
    } elseif (isset($rawSeo['keywords']) && is_string($rawSeo['keywords'])) {
        $seoKeywords = $rawSeo['keywords'];
    }

    // 15. Nearby
    $rawNearby = is_array($property->nearby_amenities) ? $property->nearby_amenities : [];
    $nearbyString = '';
    if (is_array($rawNearby) && !empty($rawNearby)) {
        $nearbyArray = array_map(function($item) {
            if (is_array($item)) return $item['name'] ?? '';
            return is_string($item) ? $item : '';
        }, $rawNearby);
        $nearbyString = implode(', ', array_filter($nearbyArray));
    } elseif (is_string($rawNearby)) {
        $nearbyString = $rawNearby;
    }

    // Links
    $vTour = is_string($property->virtual_tour_url) ? $property->virtual_tour_url : '';
    $fPlan = is_string($property->floor_plan_url) ? $property->floor_plan_url : '';

    // Owner Information
    $owner = $property->owner;
    $ownerName = '';
    $ownerEmail = '';
    $ownerPhone = '';
    $ownerBadge = '';
    $ownerBadgeColor = '';

    if ($owner) {
        if ($property->owner_type === 'App\Models\User') {
            $ownerName = $owner->username ?? 'N/A';
            $ownerEmail = $owner->email ?? 'N/A';
            $ownerPhone = $owner->phone ?? 'N/A';
            $ownerBadge = 'User';
            $ownerBadgeColor = 'bg-blue-100 text-blue-700';
        } elseif ($property->owner_type === 'App\Models\Agent') {
            $ownerName = $owner->agent_name ?? 'N/A';
            $ownerEmail = $owner->primary_email ?? 'N/A';
            $ownerPhone = $owner->primary_phone ?? 'N/A';
            $ownerBadge = 'Agent';
            $ownerBadgeColor = 'bg-emerald-100 text-emerald-700';
        } elseif ($property->owner_type === 'App\Models\RealEstateOffice') {
            $ownerName = $owner->company_name ?? 'N/A';
            $ownerEmail = $owner->email_address ?? 'N/A';
            $ownerPhone = $owner->phone_number ?? 'N/A';
            $ownerBadge = 'Office';
            $ownerBadgeColor = 'bg-purple-100 text-purple-700';
        }
    }
@endphp

<div class="max-w-[1600px] mx-auto pb-20 animate-in fade-in zoom-in-95 duration-500" x-data="{ activeTab: 'basic' }">

    {{-- Elegant Header --}}
    <div class="mb-8">
        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm mb-4">
            <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-indigo-600 transition font-medium">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
            <a href="{{ route('admin.properties.index') }}" class="text-slate-400 hover:text-indigo-600 transition font-medium">
                Properties
            </a>
            <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
            <span class="text-slate-700 font-semibold">Edit Property</span>
        </div>

        {{-- Title & Actions --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">
                    Edit Property
                </h1>
                <p class="text-slate-500 font-medium">
                    Property ID: <span class="font-mono font-bold text-slate-700">{{ $property->id }}</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.properties.index') }}" class="px-6 py-3 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-2xl hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    <span>Cancel</span>
                </a>
                <button type="submit" form="editPropertyForm" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 hover:shadow-xl hover:shadow-indigo-300 hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Owner Information Card --}}
    @if($owner)
    <div class="bg-gradient-to-br from-slate-50 to-white border-2 border-slate-200 rounded-3xl p-8 mb-8 shadow-sm">
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-xl font-black shadow-lg">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900 mb-1">Property Owner</h3>
                    <span class="inline-block px-3 py-1 {{ $ownerBadgeColor }} rounded-full text-xs font-bold">
                        {{ $ownerBadge }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-200">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Owner Name</p>
                <p class="text-lg font-black text-slate-900 truncate">{{ $ownerName }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Owner ID</p>
                <p class="text-lg font-black text-slate-700 font-mono truncate">{{ $property->owner_id }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</p>
                <p class="text-sm font-bold text-indigo-600 truncate">
                    <i class="fas fa-envelope mr-1"></i>{{ $ownerEmail }}
                </p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Phone</p>
                <p class="text-sm font-bold text-emerald-600 truncate">
                    <i class="fas fa-phone mr-1"></i>{{ $ownerPhone }}
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Modern Tab Navigation --}}
    <div class="flex flex-wrap items-center gap-2 mb-8 select-none bg-white p-2 rounded-2xl border-2 border-slate-200 shadow-sm w-fit max-w-full overflow-x-auto">
        @foreach([
            'basic' => ['icon' => 'fa-home', 'label' => 'Basic Info'],
            'details' => ['icon' => 'fa-layer-group', 'label' => 'Details'],
            'location' => ['icon' => 'fa-map-marked-alt', 'label' => 'Location'],
            'construction' => ['icon' => 'fa-hard-hat', 'label' => 'Construction'],
            'media' => ['icon' => 'fa-images', 'label' => 'Media'],
            'availability' => ['icon' => 'fa-calendar-alt', 'label' => 'Status'],
            'seo' => ['icon' => 'fa-search', 'label' => 'SEO'],
            'analytics' => ['icon' => 'fa-chart-pie', 'label' => 'Analytics']
        ] as $key => $tab)
            <button
                type="button"
                @click="activeTab = '{{ $key }}'"
                :class="activeTab === '{{ $key }}' ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-lg shadow-indigo-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-bold transition-all duration-300 whitespace-nowrap">
                <i class="fas {{ $tab['icon'] }}"></i>
                <span>{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </div>

    <form id="editPropertyForm" method="POST" action="{{ route('admin.properties.update', $property->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ================= TAB 1: BASIC INFO ================= --}}
        <div x-show="activeTab === 'basic'" x-transition:enter="transition ease-out duration-300 opacity-0 translate-y-2">

            {{-- Property Titles Section --}}
            <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm p-8 mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-heading text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900">Property Titles</h2>
                        <p class="text-sm text-slate-500 font-medium">Multi-language property names</p>
                    </div>
                </div>

                <div class="space-y-6">
                    {{-- English Title --}}
                    <div class="bg-gradient-to-br from-slate-50 to-white rounded-2xl p-6 border border-slate-200">
                        <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-flag text-blue-500"></i>
                            <span>Title (English)</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name[en]" value="{{ $safeString($nameEn) }}" required
                            class="w-full px-5 py-4 bg-white border-2 border-slate-200 rounded-xl text-lg font-semibold text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 outline-none"
                            placeholder="Enter property title in English">
                    </div>

                    {{-- Arabic & Kurdish Titles --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-slate-50 to-white rounded-2xl p-6 border border-slate-200">
                            <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-flag text-emerald-500"></i>
                                <span>Title (Arabic)</span>
                            </label>
                            <input type="text" name="name[ar]" value="{{ $safeString($nameAr) }}"
                                class="w-full px-5 py-4 bg-white border-2 border-slate-200 rounded-xl text-lg font-semibold text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 outline-none text-right"
                                dir="rtl" placeholder="أدخل عنوان العقار بالعربية">
                        </div>
                        <div class="bg-gradient-to-br from-slate-50 to-white rounded-2xl p-6 border border-slate-200">
                            <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-flag text-amber-500"></i>
                                <span>Title (Kurdish)</span>
                            </label>
                            <input type="text" name="name[ku]" value="{{ $safeString($nameKu) }}"
                                class="w-full px-5 py-4 bg-white border-2 border-slate-200 rounded-xl text-lg font-semibold text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 outline-none text-right"
                                dir="rtl" placeholder="ناونیشانی موڵک بە کوردی بنووسە">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Descriptions Section --}}
            <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm p-8 mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-align-left text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900">Property Descriptions</h2>
                        <p class="text-sm text-slate-500 font-medium">Detailed property information in multiple languages</p>
                    </div>
                </div>

                <div class="space-y-6">
                    {{-- English Description --}}
                    <div class="bg-gradient-to-br from-slate-50 to-white rounded-2xl p-6 border border-slate-200">
                        <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-flag text-blue-500"></i>
                            <span>Description (English)</span>
                        </label>
                        <textarea name="description[en]" rows="5"
                            class="w-full px-5 py-4 bg-white border-2 border-slate-200 rounded-xl text-base font-medium text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 outline-none resize-none leading-relaxed"
                            placeholder="Describe the property in detail...">{{ $safeString($descEn) }}</textarea>
                    </div>

                    {{-- Arabic & Kurdish Descriptions --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-slate-50 to-white rounded-2xl p-6 border border-slate-200">
                            <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-flag text-emerald-500"></i>
                                <span>Description (Arabic)</span>
                            </label>
                            <textarea name="description[ar]" rows="5"
                                class="w-full px-5 py-4 bg-white border-2 border-slate-200 rounded-xl text-base font-medium text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 outline-none resize-none leading-relaxed text-right"
                                dir="rtl" placeholder="وصف العقار بالتفصيل...">{{ $safeString($descAr) }}</textarea>
                        </div>
                        <div class="bg-gradient-to-br from-slate-50 to-white rounded-2xl p-6 border border-slate-200">
                            <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-flag text-amber-500"></i>
                                <span>Description (Kurdish)</span>
                            </label>
                            <textarea name="description[ku]" rows="5"
                                class="w-full px-5 py-4 bg-white border-2 border-slate-200 rounded-xl text-base font-medium text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 outline-none resize-none leading-relaxed text-right"
                                dir="rtl" placeholder="وردەکاری موڵکەکە بە کوردی بنووسە...">{{ $safeString($descKu) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 2: DETAILS (Pricing & Class) ================= --}}
        <div x-show="activeTab === 'details'" x-transition:enter="transition ease-out duration-300 opacity-0 translate-y-2">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                {{-- Pricing Card --}}
                <div class="bg-gradient-to-br from-emerald-50 to-white border-2 border-emerald-200 rounded-3xl p-8 shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-dollar-sign text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-emerald-900">Pricing</h2>
                            <p class="text-sm text-emerald-700 font-medium">Set property price</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border-2 border-emerald-200">

                        {{-- UPDATED: Dual Inputs for USD and IQD --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-3">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-3">
                                    <i class="fas fa-money-bill text-emerald-500 mr-1"></i>
                                    Price (USD)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-600 font-bold">$</span>
                                    <input
                                        type="number"
                                        name="price_usd"
                                        value="{{ $safeString($priceUSD) }}"
                                        step="0.01"
                                        required
                                        class="w-full pl-8 pr-5 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-xl font-bold text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all duration-200 outline-none"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-3">
                                    <i class="fas fa-coins text-emerald-500 mr-1"></i>
                                    Price (IQD)
                                </label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        name="price"
                                        value="{{ $safeString($priceIQD) }}"
                                        step="0.01"
                                        required
                                        class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-xl font-bold text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all duration-200 outline-none"
                                        placeholder="0">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">IQD</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Property Classification Card --}}
                <div class="bg-gradient-to-br from-indigo-50 to-white border-2 border-indigo-200 rounded-3xl p-8 shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-tags text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-indigo-900">Classification</h2>
                            <p class="text-sm text-indigo-700 font-medium">Property type and listing details</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="bg-white rounded-2xl p-6 border-2 border-indigo-200">
                            <label class="block text-sm font-bold text-slate-700 mb-3">
                                <i class="fas fa-building text-indigo-500 mr-1"></i>
                                Property Category
                            </label>
                            <select
                                name="type[category]"
                                class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-base font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 outline-none cursor-pointer">
                                @foreach(['apartment', 'house', 'villa', 'office', 'land', 'commercial', 'industrial', 'warehouse'] as $opt)
                                    <option value="{{ $opt }}" {{ $typeCategory == $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div class="bg-white rounded-2xl p-6 border-2 border-indigo-200">
                                <label class="block text-sm font-bold text-slate-700 mb-3">
                                    <i class="fas fa-handshake text-indigo-500 mr-1"></i>
                                    Listing Type
                                </label>
                                <select
                                    name="listing_type"
                                    class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-base font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 outline-none cursor-pointer">
                                    <option value="sale" {{ $property->listing_type == 'sale' ? 'selected' : '' }}>For Sale</option>
                                    <option value="rent" {{ $property->listing_type == 'rent' ? 'selected' : '' }}>For Rent</option>
                                </select>
                            </div>

                            <div class="bg-white rounded-2xl p-6 border-2 border-indigo-200">
                                <label class="block text-sm font-bold text-slate-700 mb-3">
                                    <i class="fas fa-ruler-combined text-indigo-500 mr-1"></i>
                                    Area (m²)
                                </label>
                                <input
                                    type="number"
                                    name="area"
                                    value="{{ $safeString((float)$property->area) }}"
                                    step="0.01"
                                    required
                                    class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-base font-bold text-slate-700 font-mono focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 outline-none"
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 border-2 border-indigo-200">
                            <label class="block text-sm font-bold text-slate-700 mb-3">
                                <i class="fas fa-calendar-alt text-indigo-500 mr-1"></i>
                                Rental Period
                            </label>
                            <select
                                name="rental_period"
                                class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-base font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 outline-none cursor-pointer">
                                <option value="">Not Applicable</option>
                                <option value="daily" {{ $property->rental_period == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ $property->rental_period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ $property->rental_period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $property->rental_period == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Room Counts & Features --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                    @foreach([
                        ['label' => 'Bedrooms', 'name' => 'rooms[bedroom][count]', 'val' => $roomBed, 'icon' => 'fa-bed'],
                        ['label' => 'Bathrooms', 'name' => 'rooms[bathroom][count]', 'val' => $roomBath, 'icon' => 'fa-bath'],
                        ['label' => 'Living Rooms', 'name' => 'rooms[living_room][count]', 'val' => $roomLiving, 'icon' => 'fa-couch'],
                        ['label' => 'Floor No.', 'name' => 'floor_number', 'val' => $property->floor_number, 'icon' => 'fa-layer-group'],
                    ] as $field)
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:border-indigo-200 transition group">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-wider"><i class="fas {{ $field['icon'] }} mr-1"></i> {{ $field['label'] }}</label>
                        <input type="number" name="{{ $field['name'] }}" value="{{ $safeString($field['val']) }}" class="bg-transparent text-3xl font-black text-slate-800 w-full outline-none group-hover:text-indigo-600 transition">
                    </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 border-t border-slate-100 pt-8">
                    <div class="space-y-6">
                        <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-4">Features & Amenities</h4>
                        <div>
                            <label class="input-label">Amenities (Comma Separated)</label>
                            <input type="text" name="amenities" value="{{ $safeString($amenitiesString) }}" class="input-modern" placeholder="Pool, Gym, WiFi...">
                        </div>
                        <div>
                            <label class="input-label">Features (Comma Separated)</label>
                            <input type="text" name="features" value="{{ $safeString($featuresString) }}" class="input-modern" placeholder="Balcony, View, Corner unit...">
                        </div>
                        <div>
                            <label class="input-label">Nearby Amenities</label>
                            <input type="text" name="nearby_amenities" value="{{ $safeString($nearbyString) }}" class="input-modern" placeholder="School, Mall, Park...">
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 h-full">
                        <h4 class="text-sm font-bold text-slate-400 uppercase mb-6 tracking-wide">Furnishing & Utilities</h4>
                        <div class="grid grid-cols-1 gap-6 mb-6">
                             <div>
                                <label class="input-label">Furnishing Level</label>
                                <select name="furnishing_details[level]" class="input-modern">
                                    <option value="">Select Level</option>
                                    <option value="unfurnished" {{ $furnishingLevel == 'unfurnished' ? 'selected' : '' }}>Unfurnished</option>
                                    <option value="semi-furnished" {{ $furnishingLevel == 'semi-furnished' ? 'selected' : '' }}>Semi-Furnished</option>
                                    <option value="fully-furnished" {{ $furnishingLevel == 'fully-furnished' ? 'selected' : '' }}>Fully Furnished</option>
                                    <option value="luxury-furnished" {{ $furnishingLevel == 'luxury-furnished' ? 'selected' : '' }}>Luxury</option>
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Furnished Items</label>
                                <input type="text" name="furnishing_details[items]" value="{{ $safeString($furnishingItems) }}" class="input-modern" placeholder="Sofa, Bed, TV...">
                            </div>
                        </div>
                        <div class="space-y-4">
                            @foreach(['furnished' => 'Furnished', 'electricity' => 'Electricity', 'water' => 'Water Connection', 'internet' => 'Internet Available'] as $key => $label)
                            <label class="flex items-center justify-between cursor-pointer group">
                                <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition">{{ $label }}</span>
                                <div class="relative inline-block w-11 h-6 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="{{ $key }}" value="1" {{ $property->$key ? 'checked' : '' }} class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-2 appearance-none cursor-pointer border-slate-300 checked:right-0 checked:border-emerald-500 transition-all duration-300"/>
                                    <span class="toggle-label block overflow-hidden h-6 rounded-full bg-slate-200 cursor-pointer peer-checked:bg-emerald-500"></span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 3: LOCATION ================= --}}
        <div x-show="activeTab === 'location'" x-transition:enter="transition ease-out duration-300 opacity-0 translate-y-2">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2"><i class="fas fa-map-pin text-indigo-500"></i> Address Details</h3>
                    <div class="space-y-5">
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="input-label">City</label>
                                <input type="text" name="address_details[city][en]" value="{{ $safeString($cityVal) }}" class="input-modern">
                            </div>
                            <div>
                                <label class="input-label">District</label>
                                <input type="text" name="address_details[district][en]" value="{{ $safeString($distVal) }}" class="input-modern">
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Detailed Address</label>
                            <textarea name="address" rows="4" class="input-modern resize-none">{{ $safeString($fullAddr) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100">
                    <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2"><i class="fas fa-globe-americas text-indigo-500"></i> GPS Coordinates</h3>
                    <div class="space-y-5">
                        <div>
                            <label class="input-label">Latitude</label>
                            <input type="number" step="any" name="locations[0][lat]" value="{{ $safeString($lat) }}" class="input-modern font-mono">
                        </div>
                        <div>
                            <label class="input-label">Longitude</label>
                            <input type="number" step="any" name="locations[0][lng]" value="{{ $safeString($lng) }}" class="input-modern font-mono">
                        </div>
                        <div class="pt-4">
                             @if($lat && $lng)
                             <a target="_blank" href="https://www.google.com/maps/search/?api=1&query={{ $lat }},{{ $lng }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm">
                                <i class="fas fa-external-link-alt"></i> Verify on Google Maps
                             </a>
                             @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 4: CONSTRUCTION ================= --}}
        <div x-show="activeTab === 'construction'" x-transition:enter="transition ease-out duration-300 opacity-0 translate-y-2">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2"><i class="fas fa-hard-hat text-amber-500"></i> Build Info</h3>
                    <div class="grid grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="input-label">Year Built</label>
                            <input type="number" name="year_built" value="{{ $safeString($property->year_built) }}" class="input-modern">
                        </div>
                         <div>
                            <label class="input-label">Build Type</label>
                            <select name="construction_details[type]" class="input-modern">
                                <option value="">Select</option>
                                @foreach(['concrete', 'brick', 'steel', 'wood', 'mixed'] as $opt)
                                <option value="{{ $opt }}" {{ $constructionType == $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="input-label">Build Quality</label>
                        <select name="construction_details[quality]" class="input-modern">
                            <option value="">Select</option>
                            @foreach(['standard', 'premium', 'luxury', 'ultra-luxury'] as $opt)
                            <option value="{{ $opt }}" {{ $constructionQuality == $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="bg-emerald-50/50 rounded-2xl p-8 border border-emerald-100">
                    <h3 class="text-lg font-black text-emerald-800 mb-6 flex items-center gap-2"><i class="fas fa-leaf text-emerald-500"></i> Energy Efficiency</h3>
                    <div class="space-y-5">
                        <div>
                            <label class="input-label text-emerald-700">Energy Rating</label>
                            <select name="energy_rating" class="input-modern bg-white border-emerald-200">
                                <option value="">Not Rated</option>
                                @foreach(['A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G'] as $opt)
                                <option value="{{ $opt }}" {{ $property->energy_rating == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="input-label text-emerald-700">Certificate No.</label>
                                <input type="text" name="energy_details[certificate]" value="{{ $safeString($energyCertificate) }}" class="input-modern bg-white border-emerald-200">
                            </div>
                            <div>
                                <label class="input-label text-emerald-700">kWh Consumption</label>
                                <input type="number" name="energy_details[consumption]" value="{{ $safeString($energyConsumption) }}" class="input-modern bg-white border-emerald-200">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 5: MEDIA ================= --}}
        <div x-show="activeTab === 'media'" x-transition:enter="transition ease-out duration-300 opacity-0 translate-y-2">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-8">
                <div>
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wide mb-4">Gallery</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-6 gap-4">
                        @if(is_array($property->images) && count($property->images) > 0)
                            @foreach($property->images as $img)
                                @if(is_string($img))
                                <div class="relative group rounded-2xl overflow-hidden aspect-square border border-slate-200 shadow-sm bg-slate-50">
                                    <img src="{{ asset($img) }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center backdrop-blur-sm">
                                        <button type="button" class="text-white bg-rose-500/80 p-3 rounded-xl hover:bg-rose-600 transition shadow-lg">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @else
                            <div class="col-span-full py-12 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50">
                                <i class="fas fa-images text-slate-300 text-4xl mb-3"></i>
                                <p class="text-slate-400 text-sm font-medium">No images uploaded yet.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-8 text-center">
                    <label class="block text-xs font-bold text-indigo-900 uppercase mb-3">Upload New Photos</label>
                    <input type="file" name="images[]" multiple class="block w-full max-w-lg mx-auto text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition cursor-pointer bg-white rounded-xl border border-indigo-200 shadow-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="input-label">360° Virtual Tour URL</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fas fa-cube"></i></span>
                            <input type="url" name="virtual_tour_url" value="{{ $safeString($vTour) }}" class="input-modern pl-10">
                        </div>
                    </div>
                    <div>
                        <label class="input-label">Floor Plan Image URL</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fas fa-ruler-combined"></i></span>
                            <input type="url" name="floor_plan_url" value="{{ $safeString($fPlan) }}" class="input-modern pl-10">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 6: AVAILABILITY & STATUS ================= --}}
        <div x-show="activeTab === 'availability'" x-transition:enter="transition ease-out duration-300 opacity-0 translate-y-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 h-fit">
                    <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2"><i class="fas fa-toggle-on text-emerald-500"></i> Availability</h3>

                    <div class="mb-6">
                        <label class="input-label">Global Status</label>
                        <select name="status" class="input-modern cursor-pointer">
                            @foreach(['available', 'pending', 'sold', 'rented', 'suspended'] as $st)
                                <option value="{{ $st }}" {{ old('status', $property->status) == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                             <label class="input-label">Available From</label>
                             <input type="date" name="availability[from]" value="{{ $safeString($availableFrom) }}" class="input-modern">
                        </div>
                         <div>
                             <label class="input-label">Available To</label>
                             <input type="date" name="availability[to]" value="{{ $safeString($availableTo) }}" class="input-modern">
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        @foreach(['is_active' => 'Active Listing', 'published' => 'Publicly Visible', 'verified' => 'Verified Badge'] as $key => $label)
                        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-slate-50 transition">
                            <span class="text-sm font-bold text-slate-700">{{ $label }}</span>
                            <div class="relative inline-block w-11 h-6 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="{{ $key }}" value="1" {{ old($key, $property->$key) ? 'checked' : '' }} class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-2 appearance-none cursor-pointer border-slate-300 checked:right-0 checked:border-emerald-500 transition-all duration-300"/>
                                <span class="toggle-label block overflow-hidden h-6 rounded-full bg-slate-200 cursor-pointer peer-checked:bg-emerald-500"></span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-3xl shadow-lg p-8 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-indigo-500 rounded-full blur-3xl opacity-20"></div>

                    <h3 class="text-lg font-black mb-6 flex items-center gap-2 relative z-10"><i class="fas fa-rocket text-amber-400"></i> Boost Promotion</h3>

                    <label class="flex items-center justify-between cursor-pointer mb-8 relative z-10">
                        <div>
                            <span class="block text-sm font-bold text-white">Enable Boosting</span>
                            <span class="text-xs text-indigo-300">Feature this property on top</span>
                        </div>
                        <div class="relative inline-block w-12 h-6 align-middle select-none">
                            <input type="checkbox" name="is_boosted" value="1" {{ old('is_boosted', $property->is_boosted) ? 'checked' : '' }} class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-none appearance-none cursor-pointer checked:right-0 transition-all duration-300"/>
                            <span class="block overflow-hidden h-6 rounded-full bg-indigo-800/50 border border-indigo-700 cursor-pointer"></span>
                        </div>
                    </label>

                    <div class="grid grid-cols-1 gap-5 relative z-10">
                        <div>
                            <label class="text-xs font-bold text-indigo-300 uppercase mb-1.5 block">Start Date</label>
                            <input type="date" name="boost_start_date" value="{{ $safeString($property->boost_start_date) }}" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-sm font-bold text-white placeholder-indigo-300 focus:ring-2 focus:ring-amber-400 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-indigo-300 uppercase mb-1.5 block">End Date</label>
                            <input type="date" name="boost_end_date" value="{{ $safeString($property->boost_end_date) }}" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-sm font-bold text-white placeholder-indigo-300 focus:ring-2 focus:ring-amber-400 focus:border-transparent transition outline-none">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 7: SEO ================= --}}
         <div x-show="activeTab === 'seo'" x-transition:enter="transition ease-out duration-300 opacity-0 translate-y-2">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 max-w-4xl">
                 <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2"><i class="fas fa-search text-indigo-500"></i> SEO & Metadata</h3>

                 <div class="space-y-6">
                    <div>
                        <label class="input-label">Meta Title (Max 60 chars)</label>
                        <input type="text" name="seo_metadata[title]" value="{{ $safeString($seoTitle) }}" maxlength="60" class="input-modern">
                    </div>
                    <div>
                        <label class="input-label">Meta Description (Max 160 chars)</label>
                        <textarea name="seo_metadata[description]" rows="3" maxlength="160" class="input-modern resize-none">{{ $safeString($seoDescription) }}</textarea>
                    </div>
                    <div>
                        <label class="input-label">Keywords (Comma Separated)</label>
                        <input type="text" name="seo_metadata[keywords]" value="{{ $safeString($seoKeywords) }}" class="input-modern">
                    </div>
                 </div>
            </div>
         </div>

        {{-- ================= TAB 8: ANALYTICS ================= --}}
        <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300 opacity-0 translate-y-2">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach([
                    ['label' => 'Total Views', 'val' => $property->views, 'color' => 'text-slate-900', 'bg' => 'bg-white'],
                    ['label' => 'Favorites', 'val' => $property->favorites_count, 'color' => 'text-rose-500', 'bg' => 'bg-white'],
                    ['label' => 'Rating', 'val' => $property->rating, 'color' => 'text-amber-500', 'bg' => 'bg-white']
                ] as $stat)
                <div class="{{ $stat['bg'] }} rounded-3xl border border-slate-200 shadow-sm p-6 text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ $stat['label'] }}</p>
                    <p class="text-4xl font-black {{ $stat['color'] }}">{{ number_format((float)$stat['val']) }}</p>
                </div>
                @endforeach
            </div>

            <div class="bg-slate-900 rounded-3xl p-8 shadow-lg">
                <label class="block text-xs font-bold text-slate-400 uppercase mb-3 flex items-center gap-2">
                    <i class="fas fa-code"></i> Investment Analysis (Raw Data)
                </label>
                <div class="relative">
                    <textarea readonly class="w-full h-48 bg-slate-800/50 border border-slate-700 rounded-xl text-xs font-mono text-emerald-400 p-4 resize-none focus:outline-none">{{ is_array($property->investment_analysis) ? json_encode($property->investment_analysis, JSON_PRETTY_PRINT) : '{}' }}</textarea>
                </div>
            </div>
        </div>

    </form>
</div>

{{-- Custom CSS for Toggles and Inputs --}}
<style>
    .input-label {
        @apply block text-[11px] font-bold text-slate-400 uppercase mb-1.5 tracking-wide;
    }
    .input-modern {
        @apply w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 outline-none;
    }
    .toggle-checkbox:checked + .toggle-label {
        @apply bg-indigo-600;
    }
</style>

@endsection
