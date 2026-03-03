@extends('layouts.admin-layout')

@section('title', 'Create Project')

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- Page Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.projects.index') }}"
               class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Project</h1>
                <p class="text-sm text-gray-500 mt-0.5">Add a new real estate development project</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data" id="projectForm">
        @csrf

        <div class="px-6 py-6 flex gap-6">

            {{-- Left Column - Main Form --}}
            <div class="flex-1 min-w-0 space-y-5">

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9v4a1 1 0 102 0V9a1 1 0 10-2 0zm1-4a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-red-700">Please fix the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="text-sm text-red-600">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Tab Navigation --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="flex border-b border-gray-200 overflow-x-auto">
                        @php
                            $tabs = [
                                ['id' => 'basic',        'label' => 'Basic Info',     'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['id' => 'location',     'label' => 'Location',       'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'],
                                ['id' => 'pricing',      'label' => 'Pricing',        'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['id' => 'details',      'label' => 'Details',        'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                ['id' => 'media',        'label' => 'Media',          'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                ['id' => 'features',     'label' => 'Features',       'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
                            ];
                        @endphp
                        @foreach($tabs as $i => $tab)
                            <button type="button"
                                    onclick="switchTab('{{ $tab['id'] }}')"
                                    id="tab-btn-{{ $tab['id'] }}"
                                    class="tab-btn flex items-center gap-2 px-5 py-3.5 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                                           {{ $i === 0 ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                                </svg>
                                {{ $tab['label'] }}
                            </button>
                        @endforeach
                    </div>

                    {{-- ==================== TAB: BASIC INFO ==================== --}}
                    <div id="tab-basic" class="tab-panel p-6 space-y-6">

                        {{-- Project Name (Multilingual) --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xs font-bold">1</span>
                                Project Name
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">English <span class="text-red-500">*</span></label>
                                    <input type="text" name="name[en]" value="{{ old('name.en') }}"
                                           placeholder="e.g. Dream Tower Residences"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('name.en') border-red-400 @enderror">
                                    @error('name.en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Arabic</label>
                                    <input type="text" name="name[ar]" value="{{ old('name.ar') }}"
                                           placeholder="الاسم بالعربية" dir="rtl"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Kurdish</label>
                                    <input type="text" name="name[ku]" value="{{ old('name.ku') }}"
                                           placeholder="ناوی کوردی" dir="rtl"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        {{-- Description (Multilingual) --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xs font-bold">2</span>
                                Description
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">English</label>
                                    <textarea name="description[en]" rows="3"
                                              placeholder="Describe the project in English..."
                                              class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('description.en') }}</textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Arabic</label>
                                        <textarea name="description[ar]" rows="2" dir="rtl"
                                                  placeholder="وصف المشروع..."
                                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('description.ar') }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Kurdish</label>
                                        <textarea name="description[ku]" rows="2" dir="rtl"
                                                  placeholder="پیناسەی پڕۆژە..."
                                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('description.ku') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Type & Category --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xs font-bold">3</span>
                                Classification
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Project Type <span class="text-red-500">*</span></label>
                                    <select name="project_type"
                                            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 @error('project_type') border-red-400 @enderror">
                                        <option value="">Select type...</option>
                                        <option value="residential"  {{ old('project_type') == 'residential'  ? 'selected' : '' }}>Residential</option>
                                        <option value="commercial"   {{ old('project_type') == 'commercial'   ? 'selected' : '' }}>Commercial</option>
                                        <option value="mixed_use"    {{ old('project_type') == 'mixed_use'    ? 'selected' : '' }}>Mixed Use</option>
                                        <option value="industrial"   {{ old('project_type') == 'industrial'   ? 'selected' : '' }}>Industrial</option>
                                        <option value="retail"       {{ old('project_type') == 'retail'       ? 'selected' : '' }}>Retail</option>
                                        <option value="office"       {{ old('project_type') == 'office'       ? 'selected' : '' }}>Office</option>
                                        <option value="hospitality"  {{ old('project_type') == 'hospitality'  ? 'selected' : '' }}>Hospitality</option>
                                    </select>
                                    @error('project_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Slug</label>
                                    <input type="text" name="slug" value="{{ old('slug') }}"
                                           placeholder="auto-generated-from-name"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <p class="text-xs text-gray-400 mt-1">Leave empty to auto-generate from English name</p>
                                </div>
                            </div>
                        </div>

                        {{-- Developer --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xs font-bold">4</span>
                                Developer / Owner
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Developer Type <span class="text-red-500">*</span></label>
                                    <select name="developer_type" id="developerType"
                                            onchange="loadDeveloperOptions()"
                                            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">Select type...</option>
                                        <option value="App\Models\RealEstateOffice" {{ old('developer_type') == 'App\Models\RealEstateOffice' ? 'selected' : '' }}>Real Estate Office</option>
                                        <option value="App\Models\Agent"            {{ old('developer_type') == 'App\Models\Agent'            ? 'selected' : '' }}>Agent</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Select Developer <span class="text-red-500">*</span></label>
                                    <select name="developer_id" id="developerSelect"
                                            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="">— Select developer type first —</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}" data-type="App\Models\RealEstateOffice"
                                                    {{ old('developer_id') == $office->id ? 'selected' : '' }}>
                                                {{ $office->company_name }}
                                            </option>
                                        @endforeach
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}" data-type="App\Models\Agent"
                                                    {{ old('developer_id') == $agent->id ? 'selected' : '' }}>
                                                {{ $agent->agent_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Dates --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xs font-bold">5</span>
                                Key Dates
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Launch Date</label>
                                    <input type="date" name="launch_date" value="{{ old('launch_date') }}"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Construction Start</label>
                                    <input type="date" name="construction_start_date" value="{{ old('construction_start_date') }}"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Expected Completion</label>
                                    <input type="date" name="expected_completion_date" value="{{ old('expected_completion_date') }}"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Handover Date</label>
                                    <input type="date" name="handover_date" value="{{ old('handover_date') }}"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ==================== TAB: LOCATION ==================== --}}
                    <div id="tab-location" class="tab-panel p-6 space-y-6 hidden">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">City (EN)</label>
                                <input type="text" name="address_details[city][en]" value="{{ old('address_details.city.en') }}"
                                       placeholder="e.g. Erbil"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">District (EN)</label>
                                <input type="text" name="address_details[district][en]" value="{{ old('address_details.district.en') }}"
                                       placeholder="e.g. Ankawa"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">City (Arabic)</label>
                                <input type="text" name="address_details[city][ar]" value="{{ old('address_details.city.ar') }}"
                                       dir="rtl" placeholder="مثلاً: أربيل"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">District (Arabic)</label>
                                <input type="text" name="address_details[district][ar]" value="{{ old('address_details.district.ar') }}"
                                       dir="rtl"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Full Address</label>
                            <input type="text" name="full_address" value="{{ old('full_address') }}"
                                   placeholder="Complete address..."
                                   class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Latitude</label>
                                <input type="number" step="any" name="latitude" value="{{ old('latitude') }}"
                                       placeholder="36.1901"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Longitude</label>
                                <input type="number" step="any" name="longitude" value="{{ old('longitude') }}"
                                       placeholder="44.0091"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>

                    {{-- ==================== TAB: PRICING ==================== --}}
                    <div id="tab-pricing" class="tab-panel p-6 space-y-6 hidden">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Min Price (USD) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-sm text-gray-400 font-medium">$</span>
                                    <input type="number" name="price_range[min]" value="{{ old('price_range.min') }}"
                                           placeholder="0"
                                           class="w-full pl-7 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Max Price (USD) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-sm text-gray-400 font-medium">$</span>
                                    <input type="number" name="price_range[max]" value="{{ old('price_range.max') }}"
                                           placeholder="0"
                                           class="w-full pl-7 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Currency</label>
                                <select name="pricing_currency"
                                        class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="USD" {{ old('pricing_currency', 'USD') == 'USD' ? 'selected' : '' }}>USD — US Dollar</option>
                                    <option value="IQD" {{ old('pricing_currency') == 'IQD' ? 'selected' : '' }}>IQD — Iraqi Dinar</option>
                                    <option value="EUR" {{ old('pricing_currency') == 'EUR' ? 'selected' : '' }}>EUR — Euro</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Down Payment %</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="down_payment_percentage" value="{{ old('down_payment_percentage') }}"
                                           placeholder="20"
                                           class="w-full pl-3 pr-8 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <span class="absolute right-3 top-2.5 text-sm text-gray-400">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Installment Months</label>
                                <input type="number" name="installment_months" value="{{ old('installment_months') }}"
                                       placeholder="e.g. 36"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <input type="hidden" name="installment_available" value="0">
                            <input type="checkbox" name="installment_available" id="installment_available" value="1"
                                   {{ old('installment_available') ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-primary-500 focus:ring-primary-500 border-gray-300">
                            <label for="installment_available" class="text-sm text-gray-700 font-medium">Installment plan available</label>
                        </div>
                    </div>

                    {{-- ==================== TAB: DETAILS ==================== --}}
                    <div id="tab-details" class="tab-panel p-6 space-y-6 hidden">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Total Units</label>
                                <input type="number" name="total_units" value="{{ old('total_units') }}"
                                       placeholder="e.g. 120"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Available Units</label>
                                <input type="number" name="available_units" value="{{ old('available_units', 0) }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Total Floors</label>
                                <input type="number" name="total_floors" value="{{ old('total_floors') }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Buildings Count</label>
                                <input type="number" name="buildings_count" value="{{ old('buildings_count', 1) }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Total Area (m²)</label>
                                <input type="number" step="0.01" name="total_area" value="{{ old('total_area') }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Built Area (m²)</label>
                                <input type="number" step="0.01" name="built_area" value="{{ old('built_area') }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Completion %</label>
                                <input type="number" min="0" max="100" name="completion_percentage" value="{{ old('completion_percentage', 0) }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Year Built</label>
                                <input type="number" name="year_built" value="{{ old('year_built') }}"
                                       placeholder="{{ date('Y') }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Architect</label>
                                <input type="text" name="architect" value="{{ old('architect') }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Contractor</label>
                                <input type="text" name="contractor" value="{{ old('contractor') }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Virtual Tour URL</label>
                            <input type="url" name="virtual_tour_url" value="{{ old('virtual_tour_url') }}"
                                   placeholder="https://..."
                                   class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">RERA Registration</label>
                            <input type="text" name="rera_registration" value="{{ old('rera_registration') }}"
                                   class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    {{-- ==================== TAB: MEDIA ==================== --}}
                    <div id="tab-media" class="tab-panel p-6 space-y-6 hidden">
                        {{-- Cover Image --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Cover Image</label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-primary-400 transition-colors cursor-pointer"
                                 onclick="document.getElementById('cover_image').click()">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm text-gray-500">Click to upload cover image</p>
                                <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP up to 4MB</p>
                                <input type="file" id="cover_image" name="cover_image" accept="image/*" class="hidden"
                                       onchange="previewImage(this, 'coverPreview')">
                            </div>
                            <img id="coverPreview" src="" alt="" class="hidden mt-3 w-full max-h-48 object-cover rounded-lg border border-gray-200">
                        </div>

                        {{-- Project Gallery --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Project Gallery</label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-primary-400 transition-colors cursor-pointer"
                                 onclick="document.getElementById('gallery_images').click()">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <p class="text-sm text-gray-500">Click to upload gallery images</p>
                                <p class="text-xs text-gray-400 mt-1">Multiple files supported</p>
                                <input type="file" id="gallery_images" name="images[]" accept="image/*" multiple class="hidden"
                                       onchange="previewGallery(this)">
                            </div>
                            <div id="galleryPreview" class="grid grid-cols-4 gap-2 mt-3"></div>
                        </div>

                        {{-- Logo --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Project Logo</label>
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center bg-gray-50 cursor-pointer"
                                     onclick="document.getElementById('logo_input').click()">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <input type="file" id="logo_input" name="logo" accept="image/*" class="hidden"
                                           onchange="previewImage(this, 'logoPreview')">
                                </div>
                                <img id="logoPreview" src="" alt="" class="hidden w-16 h-16 object-cover rounded-xl border border-gray-200">
                                <p class="text-xs text-gray-400">PNG/SVG recommended, square format</p>
                            </div>
                        </div>
                    </div>

                    {{-- ==================== TAB: FEATURES ==================== --}}
                    <div id="tab-features" class="tab-panel p-6 space-y-6 hidden">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Project Features</label>
                            <p class="text-xs text-gray-400 mb-3">Enter one feature per line (e.g. Swimming Pool, Gym, Parking)</p>
                            <textarea name="project_features_text" rows="5"
                                      placeholder="Swimming Pool&#10;Gym&#10;Underground Parking&#10;24/7 Security&#10;Children's Playground"
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('project_features_text') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nearby Amenities</label>
                            <textarea name="nearby_amenities_text" rows="4"
                                      placeholder="Schools&#10;Hospitals&#10;Shopping Malls&#10;Mosques"
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('nearby_amenities_text') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Marketing Highlights</label>
                            <textarea name="marketing_highlights_text" rows="3"
                                      placeholder="Prime location&#10;Modern architecture&#10;Smart home features"
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('marketing_highlights_text') }}</textarea>
                        </div>

                    </div>
                </div>

            </div>

            {{-- Right Sidebar --}}
            <div class="w-72 shrink-0 space-y-4">

                {{-- Publish Card --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Publish Settings</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Project Status</label>
                            <select name="status"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="planning"           {{ old('status', 'planning') == 'planning'           ? 'selected' : '' }}>Planning</option>
                                <option value="under_construction" {{ old('status') == 'under_construction'             ? 'selected' : '' }}>Under Construction</option>
                                <option value="completed"          {{ old('status') == 'completed'                      ? 'selected' : '' }}>Completed</option>
                                <option value="delivered"          {{ old('status') == 'delivered'                      ? 'selected' : '' }}>Delivered</option>
                                <option value="on_hold"            {{ old('status') == 'on_hold'                        ? 'selected' : '' }}>On Hold</option>
                                <option value="cancelled"          {{ old('status') == 'cancelled'                      ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Sales Status</label>
                            <select name="sales_status"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="pre_launch" {{ old('sales_status', 'pre_launch') == 'pre_launch' ? 'selected' : '' }}>Pre-Launch</option>
                                <option value="launched"   {{ old('sales_status') == 'launched'                 ? 'selected' : '' }}>Launched</option>
                                <option value="selling"    {{ old('sales_status') == 'selling'                  ? 'selected' : '' }}>Selling</option>
                                <option value="sold_out"   {{ old('sales_status') == 'sold_out'                 ? 'selected' : '' }}>Sold Out</option>
                                <option value="suspended"  {{ old('sales_status') == 'suspended'                ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>

                        <div class="space-y-2 pt-1">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-primary-500 focus:ring-primary-500 border-gray-300">
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="published" value="0">
                                <input type="checkbox" name="published" value="1" {{ old('published') ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-primary-500 focus:ring-primary-500 border-gray-300">
                                <span class="text-sm text-gray-700">Published</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-primary-500 focus:ring-primary-500 border-gray-300">
                                <span class="text-sm text-gray-700">Featured</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="is_premium" value="0">
                                <input type="checkbox" name="is_premium" value="1" {{ old('is_premium') ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-primary-500 focus:ring-primary-500 border-gray-300">
                                <span class="text-sm text-gray-700">Premium</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="is_hot_project" value="0">
                                <input type="checkbox" name="is_hot_project" value="1" {{ old('is_hot_project') ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-primary-500 focus:ring-primary-500 border-gray-300">
                                <span class="text-sm text-gray-700">Hot Project 🔥</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Boost Card --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Boost Settings</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="is_boosted" value="0">
                            <input type="checkbox" name="is_boosted" id="is_boosted" value="1" {{ old('is_boosted') ? 'checked' : '' }}
                                   onchange="toggleBoostDates()"
                                   class="w-4 h-4 rounded text-primary-500 focus:ring-primary-500 border-gray-300">
                            <span class="text-sm text-gray-700 font-medium">Enable Boost</span>
                        </label>
                        <div id="boostDates" class="{{ old('is_boosted') ? '' : 'hidden' }} space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Boost Start</label>
                                <input type="datetime-local" name="boost_start_date" value="{{ old('boost_start_date') }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Boost End</label>
                                <input type="datetime-local" name="boost_end_date" value="{{ old('boost_end_date') }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-2">
                    <button type="submit"
                            class="w-full py-2.5 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm"
                            style="background-color: #434eaa">
                        Create Project
                    </button>
                    <a href="{{ route('admin.projects.index') }}"
                       class="block w-full py-2.5 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Tab switching
    function switchTab(tabId) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-primary-500', 'text-primary-600');
            b.classList.add('border-transparent', 'text-gray-500');
        });
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        document.getElementById('tab-btn-' + tabId).classList.add('border-primary-500', 'text-primary-600');
        document.getElementById('tab-btn-' + tabId).classList.remove('border-transparent', 'text-gray-500');
    }

    // Image preview
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Gallery preview
    function previewGallery(input) {
        const container = document.getElementById('galleryPreview');
        container.innerHTML = '';
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-full h-20 object-cover rounded-lg border border-gray-200';
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }

    // Boost dates toggle
    function toggleBoostDates() {
        const checkbox = document.getElementById('is_boosted');
        const dates    = document.getElementById('boostDates');
        dates.classList.toggle('hidden', !checkbox.checked);
    }

    // Developer select filter
    function loadDeveloperOptions() {
        const type   = document.getElementById('developerType').value;
        const select = document.getElementById('developerSelect');
        Array.from(select.options).forEach(opt => {
            if (!opt.value) { opt.style.display = ''; return; }
            opt.style.display = (!type || opt.dataset.type === type) ? '' : 'none';
        });
        select.value = '';
    }

    // Auto-generate slug from name
    document.querySelector('input[name="name[en]"]').addEventListener('input', function() {
        const slugField = document.querySelector('input[name="slug"]');
        if (!slugField.dataset.manual) {
            slugField.value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
        }
    });
    document.querySelector('input[name="slug"]').addEventListener('input', function() {
        this.dataset.manual = this.value ? '1' : '';
    });
</script>
@endpush
@endsection