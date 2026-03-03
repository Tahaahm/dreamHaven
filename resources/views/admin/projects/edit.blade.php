@extends('layouts.admin-layout')

@section('title', 'Edit Project')

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- Page Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.projects.index') }}"
                   class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    @php $projectName = is_array($project->name) ? ($project->name['en'] ?? 'Untitled') : $project->name; @endphp
                    <h1 class="text-2xl font-bold text-gray-900">Edit Project</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $projectName }}</p>
                </div>
            </div>
            {{-- Quick Actions --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.projects.show', $project->id) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View
                </a>
                <form method="POST" action="{{ route('admin.projects.delete', $project->id) }}"
                      onsubmit="return confirm('Delete this project permanently?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.projects.update', $project->id) }}" enctype="multipart/form-data" id="projectForm">
        @csrf
        @method('PUT')

        <div class="px-6 py-6 flex gap-6">

            {{-- Left Column --}}
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

                {{-- Last Updated Notice --}}
                <div class="flex items-center gap-2 text-xs text-gray-400 bg-white border border-gray-200 rounded-lg px-4 py-2.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Last updated {{ $project->updated_at->diffForHumans() }} &mdash; Created {{ $project->created_at->format('M d, Y') }}
                </div>

                {{-- Tab Navigation --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="flex border-b border-gray-200 overflow-x-auto">
                        @php
                            $tabs = [
                                ['id' => 'basic',    'label' => 'Basic Info',  'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['id' => 'location', 'label' => 'Location',    'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'],
                                ['id' => 'pricing',  'label' => 'Pricing',     'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['id' => 'details',  'label' => 'Details',     'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                ['id' => 'media',    'label' => 'Media',       'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                ['id' => 'features', 'label' => 'Features',    'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
                            ];
                        @endphp
                        @foreach($tabs as $i => $tab)
                            <button type="button"
                                    onclick="switchTab('{{ $tab['id'] }}')"
                                    id="tab-btn-{{ $tab['id'] }}"
                                    class="tab-btn flex items-center gap-2 px-5 py-3.5 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                                           {{ $i === 0 ? 'border-b-2 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                    style="{{ $i === 0 ? 'border-color: #434eaa; color: #434eaa;' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                                </svg>
                                {{ $tab['label'] }}
                            </button>
                        @endforeach
                    </div>

                    {{-- ==================== TAB: BASIC INFO ==================== --}}
                    <div id="tab-basic" class="tab-panel p-6 space-y-6">

                        {{-- Project Name --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background-color:#434eaa">1</span>
                                Project Name
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @php
                                    $nameEn = old('name.en', is_array($project->name) ? ($project->name['en'] ?? '') : $project->name);
                                    $nameAr = old('name.ar', is_array($project->name) ? ($project->name['ar'] ?? '') : '');
                                    $nameKu = old('name.ku', is_array($project->name) ? ($project->name['ku'] ?? '') : '');
                                @endphp
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">English <span class="text-red-500">*</span></label>
                                    <input type="text" name="name[en]" value="{{ $nameEn }}"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent @error('name.en') border-red-400 @enderror"
                                           style="--tw-ring-color: #434eaa">
                                    @error('name.en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Arabic</label>
                                    <input type="text" name="name[ar]" value="{{ $nameAr }}" dir="rtl"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Kurdish</label>
                                    <input type="text" name="name[ku]" value="{{ $nameKu }}" dir="rtl"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background-color:#434eaa">2</span>
                                Description
                            </h3>
                            @php
                                $descEn = old('description.en', is_array($project->description) ? ($project->description['en'] ?? '') : $project->description);
                                $descAr = old('description.ar', is_array($project->description) ? ($project->description['ar'] ?? '') : '');
                                $descKu = old('description.ku', is_array($project->description) ? ($project->description['ku'] ?? '') : '');
                            @endphp
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">English</label>
                                    <textarea name="description[en]" rows="3"
                                              class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent resize-none">{{ $descEn }}</textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Arabic</label>
                                        <textarea name="description[ar]" rows="2" dir="rtl"
                                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent resize-none">{{ $descAr }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Kurdish</label>
                                        <textarea name="description[ku]" rows="2" dir="rtl"
                                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent resize-none">{{ $descKu }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Classification --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background-color:#434eaa">3</span>
                                Classification
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Project Type <span class="text-red-500">*</span></label>
                                    <select name="project_type"
                                            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                        @foreach(['residential','commercial','mixed_use','industrial','retail','office','hospitality'] as $type)
                                            <option value="{{ $type }}" {{ old('project_type', $project->project_type) == $type ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('_', ' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Slug</label>
                                    <input type="text" name="slug" value="{{ old('slug', $project->slug) }}"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        {{-- Key Dates --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background-color:#434eaa">4</span>
                                Key Dates
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @php
                                    $dateFields = [
                                        ['name' => 'launch_date',               'label' => 'Launch Date'],
                                        ['name' => 'construction_start_date',   'label' => 'Construction Start'],
                                        ['name' => 'expected_completion_date',  'label' => 'Expected Completion'],
                                        ['name' => 'handover_date',             'label' => 'Handover Date'],
                                    ];
                                @endphp
                                @foreach($dateFields as $field)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $field['label'] }}</label>
                                        <input type="date" name="{{ $field['name'] }}"
                                               value="{{ old($field['name'], $project->{$field['name']} ? \Carbon\Carbon::parse($project->{$field['name']})->format('Y-m-d') : '') }}"
                                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ==================== TAB: LOCATION ==================== --}}
                    <div id="tab-location" class="tab-panel p-6 space-y-5 hidden">
                        @php
                            $addr   = is_array($project->address_details) ? $project->address_details : [];
                            $cityEn = old('address_details.city.en',     $addr['city']['en']     ?? '');
                            $cityAr = old('address_details.city.ar',     $addr['city']['ar']     ?? '');
                            $distEn = old('address_details.district.en', $addr['district']['en'] ?? '');
                            $distAr = old('address_details.district.ar', $addr['district']['ar'] ?? '');
                        @endphp
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">City (English)</label>
                                <input type="text" name="address_details[city][en]" value="{{ $cityEn }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">District (English)</label>
                                <input type="text" name="address_details[district][en]" value="{{ $distEn }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">City (Arabic)</label>
                                <input type="text" name="address_details[city][ar]" value="{{ $cityAr }}" dir="rtl"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">District (Arabic)</label>
                                <input type="text" name="address_details[district][ar]" value="{{ $distAr }}" dir="rtl"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Full Address</label>
                            <input type="text" name="full_address" value="{{ old('full_address', $project->full_address) }}"
                                   class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Latitude</label>
                                <input type="number" step="any" name="latitude" value="{{ old('latitude', $project->latitude) }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Longitude</label>
                                <input type="number" step="any" name="longitude" value="{{ old('longitude', $project->longitude) }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                        </div>

                        {{-- Map Preview --}}
                        @if($project->latitude && $project->longitude)
                            <div class="rounded-xl overflow-hidden border border-gray-200 h-48 bg-gray-100 flex items-center justify-center">
                                <a href="https://maps.google.com/?q={{ $project->latitude }},{{ $project->longitude }}"
                                   target="_blank"
                                   class="flex flex-col items-center gap-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm">{{ $project->latitude }}, {{ $project->longitude }}</span>
                                    <span class="text-xs underline">Open in Google Maps</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- ==================== TAB: PRICING ==================== --}}
                    <div id="tab-pricing" class="tab-panel p-6 space-y-5 hidden">
                        @php
                            $priceRange = is_array($project->price_range) ? $project->price_range : [];
                        @endphp
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Min Price <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-sm text-gray-400 font-medium">$</span>
                                    <input type="number" name="price_range[min]"
                                           value="{{ old('price_range.min', $priceRange['min'] ?? '') }}"
                                           class="w-full pl-7 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Max Price <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-sm text-gray-400 font-medium">$</span>
                                    <input type="number" name="price_range[max]"
                                           value="{{ old('price_range.max', $priceRange['max'] ?? '') }}"
                                           class="w-full pl-7 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Currency</label>
                                <select name="pricing_currency"
                                        class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                    @foreach(['USD' => 'USD — US Dollar', 'IQD' => 'IQD — Iraqi Dinar', 'EUR' => 'EUR — Euro'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('pricing_currency', $project->pricing_currency) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Down Payment %</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="down_payment_percentage"
                                           value="{{ old('down_payment_percentage', $project->down_payment_percentage) }}"
                                           class="w-full pl-3 pr-8 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                    <span class="absolute right-3 top-2.5 text-sm text-gray-400">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Installment Months</label>
                                <input type="number" name="installment_months"
                                       value="{{ old('installment_months', $project->installment_months) }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                        </div>

                        <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer">
                            <input type="hidden" name="installment_available" value="0">
                            <input type="checkbox" name="installment_available" value="1"
                                   {{ old('installment_available', $project->installment_available) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300">
                            <span class="text-sm text-gray-700 font-medium">Installment plan available</span>
                        </label>
                    </div>

                    {{-- ==================== TAB: DETAILS ==================== --}}
                    <div id="tab-details" class="tab-panel p-6 space-y-5 hidden">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @php
                                $detailFields = [
                                    ['name' => 'total_units',           'label' => 'Total Units',       'type' => 'number'],
                                    ['name' => 'available_units',       'label' => 'Available Units',   'type' => 'number'],
                                    ['name' => 'total_floors',          'label' => 'Total Floors',      'type' => 'number'],
                                    ['name' => 'buildings_count',       'label' => 'Buildings Count',   'type' => 'number'],
                                    ['name' => 'total_area',            'label' => 'Total Area (m²)',   'type' => 'number'],
                                    ['name' => 'built_area',            'label' => 'Built Area (m²)',   'type' => 'number'],
                                    ['name' => 'completion_percentage', 'label' => 'Completion %',      'type' => 'number'],
                                    ['name' => 'year_built',            'label' => 'Year Built',        'type' => 'number'],
                                ];
                            @endphp
                            @foreach($detailFields as $field)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $field['label'] }}</label>
                                    <input type="{{ $field['type'] }}" name="{{ $field['name'] }}"
                                           value="{{ old($field['name'], $project->{$field['name']}) }}"
                                           {{ $field['name'] == 'completion_percentage' ? 'min=0 max=100' : '' }}
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                </div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Architect</label>
                                <input type="text" name="architect" value="{{ old('architect', $project->architect) }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Contractor</label>
                                <input type="text" name="contractor" value="{{ old('contractor', $project->contractor) }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Virtual Tour URL</label>
                            <input type="url" name="virtual_tour_url" value="{{ old('virtual_tour_url', $project->virtual_tour_url) }}"
                                   placeholder="https://..."
                                   class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">RERA Registration</label>
                            <input type="text" name="rera_registration" value="{{ old('rera_registration', $project->rera_registration) }}"
                                   class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                        </div>
                    </div>

                    {{-- ==================== TAB: MEDIA ==================== --}}
                    <div id="tab-media" class="tab-panel p-6 space-y-6 hidden">

                        {{-- Cover Image --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Cover Image</label>
                            @if($project->cover_image_url)
                                <div class="mb-3 relative inline-block">
                                    <img src="{{ $project->cover_image_url }}" alt="Cover"
                                         class="w-full max-h-48 object-cover rounded-xl border border-gray-200">
                                    <span class="absolute top-2 left-2 bg-black/50 text-white text-xs px-2 py-1 rounded-full">Current</span>
                                </div>
                            @endif
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-5 text-center hover:border-primary-400 transition-colors cursor-pointer"
                                 onclick="document.getElementById('cover_image').click()">
                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm text-gray-500">{{ $project->cover_image_url ? 'Replace cover image' : 'Upload cover image' }}</p>
                                <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP up to 4MB</p>
                                <input type="file" id="cover_image" name="cover_image" accept="image/*" class="hidden"
                                       onchange="previewImage(this, 'coverPreview')">
                            </div>
                            <img id="coverPreview" src="" alt="" class="hidden mt-3 w-full max-h-40 object-cover rounded-lg border border-gray-200">
                        </div>

                        {{-- Existing Gallery --}}
                        @if($project->images && count($project->images) > 0)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Current Gallery
                                    <span class="text-xs font-normal text-gray-400 ml-1">({{ count($project->images) }} images)</span>
                                </label>
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach($project->images as $index => $image)
                                        <div class="relative group">
                                            <img src="{{ $image }}" class="w-full h-20 object-cover rounded-lg border border-gray-200" alt="Gallery {{ $index + 1 }}">
                                            <label class="absolute inset-0 bg-black/0 group-hover:bg-black/30 rounded-lg transition-colors flex items-center justify-center cursor-pointer">
                                                <input type="checkbox" name="remove_images[]" value="{{ $image }}"
                                                       class="hidden peer">
                                                <span class="opacity-0 group-hover:opacity-100 text-white text-xs font-medium transition-opacity peer-checked:opacity-100">
                                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-400 mt-2">Hover and click to mark images for removal</p>
                            </div>
                        @endif

                        {{-- Add New Gallery Images --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Add New Gallery Images</label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-5 text-center hover:border-primary-400 transition-colors cursor-pointer"
                                 onclick="document.getElementById('gallery_images').click()">
                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                                </svg>
                                <p class="text-sm text-gray-500">Click to add more images</p>
                                <input type="file" id="gallery_images" name="new_images[]" accept="image/*" multiple class="hidden"
                                       onchange="previewGallery(this)">
                            </div>
                            <div id="galleryPreview" class="grid grid-cols-4 gap-2 mt-3"></div>
                        </div>

                        {{-- Logo --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Project Logo</label>
                            <div class="flex items-center gap-4">
                                @if($project->logo_url)
                                    <img src="{{ $project->logo_url }}" class="w-16 h-16 object-contain rounded-xl border border-gray-200 bg-gray-50">
                                @endif
                                <div class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center bg-gray-50 cursor-pointer"
                                     onclick="document.getElementById('logo_input').click()">
                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <input type="file" id="logo_input" name="logo" accept="image/*" class="hidden"
                                           onchange="previewImage(this, 'logoPreview')">
                                </div>
                                <img id="logoPreview" src="" alt="" class="hidden w-16 h-16 object-cover rounded-xl border border-gray-200">
                                <p class="text-xs text-gray-400">{{ $project->logo_url ? 'Upload to replace current logo' : 'PNG/SVG, square format' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- ==================== TAB: FEATURES ==================== --}}
                    <div id="tab-features" class="tab-panel p-6 space-y-6 hidden">
                        @php
                            $features   = is_array($project->project_features)   ? implode("\n", $project->project_features)   : '';
                            $amenities  = is_array($project->nearby_amenities)   ? implode("\n", $project->nearby_amenities)   : '';
                            $highlights = is_array($project->marketing_highlights) ? implode("\n", $project->marketing_highlights) : '';
                        @endphp
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Project Features</label>
                            <p class="text-xs text-gray-400 mb-2">One feature per line</p>
                            <textarea name="project_features_text" rows="5"
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent resize-none">{{ old('project_features_text', $features) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nearby Amenities</label>
                            <textarea name="nearby_amenities_text" rows="4"
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent resize-none">{{ old('nearby_amenities_text', $amenities) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Marketing Highlights</label>
                            <textarea name="marketing_highlights_text" rows="3"
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent resize-none">{{ old('marketing_highlights_text', $highlights) }}</textarea>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Right Sidebar --}}
            <div class="w-72 shrink-0 space-y-4">

                {{-- Status Card --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Publish Settings</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Project Status</label>
                            <select name="status"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                @foreach(['planning','under_construction','completed','delivered','on_hold','cancelled'] as $s)
                                    <option value="{{ $s }}" {{ old('status', $project->status) == $s ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Sales Status</label>
                            <select name="sales_status"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                                @foreach(['pre_launch','launched','selling','sold_out','suspended'] as $s)
                                    <option value="{{ $s }}" {{ old('sales_status', $project->sales_status) == $s ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2 pt-1">
                            @php
                                $toggles = [
                                    ['name' => 'is_active',     'label' => 'Active'],
                                    ['name' => 'published',     'label' => 'Published'],
                                    ['name' => 'is_featured',   'label' => 'Featured'],
                                    ['name' => 'is_premium',    'label' => 'Premium'],
                                    ['name' => 'is_hot_project','label' => 'Hot Project 🔥'],
                                ];
                            @endphp
                            @foreach($toggles as $toggle)
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="hidden" name="{{ $toggle['name'] }}" value="0">
                                    <input type="checkbox" name="{{ $toggle['name'] }}" value="1"
                                           {{ old($toggle['name'], $project->{$toggle['name']}) ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-gray-300">
                                    <span class="text-sm text-gray-700">{{ $toggle['label'] }}</span>
                                </label>
                            @endforeach
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
                            <input type="checkbox" name="is_boosted" id="is_boosted" value="1"
                                   {{ old('is_boosted', $project->is_boosted) ? 'checked' : '' }}
                                   onchange="toggleBoostDates()"
                                   class="w-4 h-4 rounded border-gray-300">
                            <span class="text-sm text-gray-700 font-medium">Enable Boost</span>
                        </label>
                        <div id="boostDates" class="{{ old('is_boosted', $project->is_boosted) ? '' : 'hidden' }} space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Boost Start</label>
                                <input type="datetime-local" name="boost_start_date"
                                       value="{{ old('boost_start_date', $project->boost_start_date ? \Carbon\Carbon::parse($project->boost_start_date)->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Boost End</label>
                                <input type="datetime-local" name="boost_end_date"
                                       value="{{ old('boost_end_date', $project->boost_end_date ? \Carbon\Carbon::parse($project->boost_end_date)->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Project Stats</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        @php
                            $quickStats = [
                                ['label' => 'Total Views',   'value' => number_format($project->views ?? 0),            'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                                ['label' => 'Favorites',     'value' => number_format($project->favorites_count ?? 0),  'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                                ['label' => 'Inquiries',     'value' => number_format($project->inquiries_count ?? 0),  'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                                ['label' => 'Rating',        'value' => number_format($project->rating ?? 0, 1) . ' / 5', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                            ];
                        @endphp
                        @foreach($quickStats as $stat)
                            <div class="flex items-center justify-between py-1.5">
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                                    </svg>
                                    {{ $stat['label'] }}
                                </div>
                                <span class="text-sm font-semibold text-gray-800">{{ $stat['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-2">
                    <button type="submit"
                            class="w-full py-2.5 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm"
                            style="background-color: #434eaa"
                            onmouseover="this.style.backgroundColor='#3a44a0'"
                            onmouseout="this.style.backgroundColor='#434eaa'">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.projects.show', $project->id) }}"
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
    function switchTab(tabId) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.style.borderColor = '';
            b.style.color = '';
            b.classList.remove('border-b-2');
            b.classList.add('border-transparent', 'text-gray-500');
        });
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        const btn = document.getElementById('tab-btn-' + tabId);
        btn.classList.add('border-b-2');
        btn.classList.remove('border-transparent', 'text-gray-500');
        btn.style.borderColor = '#434eaa';
        btn.style.color = '#434eaa';
    }

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

    function toggleBoostDates() {
        const checked = document.getElementById('is_boosted').checked;
        document.getElementById('boostDates').classList.toggle('hidden', !checked);
    }

    // Focus ring color via inline style
    document.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('focus', function() { this.style.outlineColor = '#434eaa'; });
    });
</script>
@endpush
@endsection