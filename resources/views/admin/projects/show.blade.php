@extends('layouts.admin-layout')

@section('title', 'Project Details')

@section('content')
<div class="min-h-screen bg-gray-50">

    @php
        $name        = is_array($project->name)        ? ($project->name['en']        ?? 'Untitled') : $project->name;
        $description = is_array($project->description) ? ($project->description['en'] ?? '')         : $project->description;
        $priceRange  = is_array($project->price_range) ? $project->price_range                       : [];
        $addr        = is_array($project->address_details) ? $project->address_details               : [];
        $statusColors = [
            'planning'           => ['bg' => '#fef9c3', 'text' => '#854d0e', 'dot' => '#ca8a04'],
            'under_construction' => ['bg' => '#ffedd5', 'text' => '#9a3412', 'dot' => '#ea580c'],
            'completed'          => ['bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#16a34a'],
            'delivered'          => ['bg' => '#dbeafe', 'text' => '#1e40af', 'dot' => '#2563eb'],
            'on_hold'            => ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#6b7280'],
            'cancelled'          => ['bg' => '#fee2e2', 'text' => '#991b1b', 'dot' => '#dc2626'],
        ];
        $salesColors = [
            'pre_launch' => ['bg' => '#f3e8ff', 'text' => '#6b21a8', 'dot' => '#9333ea'],
            'launched'   => ['bg' => '#dbeafe', 'text' => '#1e40af', 'dot' => '#2563eb'],
            'selling'    => ['bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#16a34a'],
            'sold_out'   => ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#6b7280'],
            'suspended'  => ['bg' => '#fee2e2', 'text' => '#991b1b', 'dot' => '#dc2626'],
        ];
        $sc = $statusColors[$project->status] ?? $statusColors['on_hold'];
        $ss = $salesColors[$project->sales_status]  ?? $salesColors['sold_out'];
    @endphp

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
                <div class="flex items-center gap-3">
                    {{-- Cover Thumb --}}
                    <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 bg-gradient-to-br from-indigo-100 to-blue-100 flex items-center justify-center">
                        @if($project->cover_image_url)
                            <img src="{{ $project->cover_image_url }}" class="w-full h-full object-cover" alt="{{ $name }}">
                        @else
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-gray-900">{{ $name }}</h1>
                            @if($project->is_featured)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Featured
                                </span>
                            @endif
                            @if($project->is_hot_project)
                                <span class="text-xs font-medium bg-red-50 text-red-600 px-2 py-0.5 rounded-full">🔥 Hot</span>
                            @endif
                            @if($project->is_premium)
                                <span class="text-xs font-medium bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full">✦ Premium</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-400 mt-0.5">
                            {{ ucwords(str_replace('_', ' ', $project->project_type)) }}
                            &middot; Created {{ $project->created_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.projects.toggle.active', $project->id) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors
                                   {{ $project->is_active ? 'text-amber-700 bg-amber-50 hover:bg-amber-100' : 'text-green-700 bg-green-50 hover:bg-green-100' }}">
                        @if($project->is_active)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            Deactivate
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Activate
                        @endif
                    </button>
                </form>

                <a href="{{ route('admin.projects.edit', $project->id) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                   style="background-color: #434eaa"
                   onmouseover="this.style.backgroundColor='#3a44a0'"
                   onmouseout="this.style.backgroundColor='#434eaa'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Project
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

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mx-6 mt-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
            <svg class="w-4 h-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="px-6 py-6 flex gap-6">

        {{-- Left Column - Main Content --}}
        <div class="flex-1 min-w-0 space-y-5">

            {{-- Cover Image + Gallery --}}
            @if($project->cover_image_url || ($project->images && count($project->images) > 0))
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    @if($project->cover_image_url)
                        <div class="relative">
                            <img src="{{ $project->cover_image_url }}" alt="{{ $name }}"
                                 class="w-full h-64 object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                            <div class="absolute bottom-4 left-4 text-white">
                                <p class="text-xs font-medium opacity-80">Cover Image</p>
                                <p class="font-semibold">{{ $name }}</p>
                            </div>
                        </div>
                    @endif

                    @if($project->images && count($project->images) > 0)
                        <div class="p-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                                Gallery — {{ count($project->images) }} Images
                            </p>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($project->images as $i => $image)
                                    <div class="relative group cursor-pointer" onclick="openLightbox('{{ $image }}')">
                                        <img src="{{ $image }}" alt="Gallery {{ $i + 1 }}"
                                             class="w-full h-20 object-cover rounded-lg border border-gray-200 group-hover:opacity-90 transition-opacity">
                                        @if($i === 3 && count($project->images) > 4)
                                            <div class="absolute inset-0 bg-black/50 rounded-lg flex items-center justify-center">
                                                <span class="text-white font-semibold text-sm">+{{ count($project->images) - 4 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    @if($i === 3) @break @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Overview Card --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-700">Project Overview</h2>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                              style="background-color: {{ $sc['bg'] }}; color: {{ $sc['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $sc['dot'] }}"></span>
                            {{ ucwords(str_replace('_', ' ', $project->status)) }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                              style="background-color: {{ $ss['bg'] }}; color: {{ $ss['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $ss['dot'] }}"></span>
                            {{ ucwords(str_replace('_', ' ', $project->sales_status)) }}
                        </span>
                    </div>
                </div>
                <div class="p-5">
                    {{-- Description --}}
                    @if($description)
                        <p class="text-sm text-gray-600 leading-relaxed mb-5">{{ $description }}</p>
                    @endif

                    {{-- Progress Bar --}}
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-medium text-gray-500">Completion Progress</span>
                            <span class="text-sm font-bold" style="color: #434eaa">{{ $project->completion_percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full transition-all"
                                 style="width: {{ $project->completion_percentage }}%; background-color: #434eaa"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $project->getCompletionStatusAttribute() ?? '' }}</p>
                    </div>

                    {{-- Key Info Grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                            $infoItems = [
                                ['label' => 'Project Type',   'value' => ucwords(str_replace('_', ' ', $project->project_type))],
                                ['label' => 'Total Units',    'value' => $project->total_units ?? '—'],
                                ['label' => 'Available',      'value' => $project->available_units ?? 0],
                                ['label' => 'Total Floors',   'value' => $project->total_floors ?? '—'],
                                ['label' => 'Total Area',     'value' => $project->total_area ? number_format($project->total_area) . ' m²' : '—'],
                                ['label' => 'Built Area',     'value' => $project->built_area  ? number_format($project->built_area)  . ' m²' : '—'],
                                ['label' => 'Year Built',     'value' => $project->year_built ?? '—'],
                                ['label' => 'Buildings',      'value' => $project->buildings_count ?? 1],
                            ];
                        @endphp
                        @foreach($infoItems as $item)
                            <div class="bg-gray-50 rounded-lg px-3 py-2.5">
                                <p class="text-xs text-gray-400 mb-0.5">{{ $item['label'] }}</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $item['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Pricing Card --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700">Pricing & Payment</h2>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="col-span-2 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-4 border border-indigo-100">
                            <p class="text-xs text-indigo-500 font-medium mb-1">Price Range</p>
                            @if(!empty($priceRange['min']) || !empty($priceRange['max']))
                                <p class="text-lg font-bold text-gray-900">
                                    {{ $project->pricing_currency }} {{ number_format($priceRange['min'] ?? 0) }}
                                    <span class="text-gray-400 font-normal text-sm mx-1">to</span>
                                    {{ $project->pricing_currency }} {{ number_format($priceRange['max'] ?? 0) }}
                                </p>
                            @else
                                <p class="text-sm text-gray-400">Not set</p>
                            @endif
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 mb-1">Down Payment</p>
                            <p class="text-lg font-bold text-gray-800">{{ $project->down_payment_percentage ?? '—' }}{{ $project->down_payment_percentage ? '%' : '' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 mb-1">Installment</p>
                            <p class="text-lg font-bold text-gray-800">
                                @if($project->installment_available)
                                    {{ $project->installment_months ?? '—' }} mo.
                                @else
                                    <span class="text-gray-400 text-sm font-normal">Not available</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location Card --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700">Location</h2>
                </div>
                <div class="p-5 space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">City</p>
                            <p class="text-sm font-medium text-gray-800">{{ $addr['city']['en'] ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">District</p>
                            <p class="text-sm font-medium text-gray-800">{{ $addr['district']['en'] ?? '—' }}</p>
                        </div>
                    </div>
                    @if($project->full_address)
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Full Address</p>
                            <p class="text-sm text-gray-700">{{ $project->full_address }}</p>
                        </div>
                    @endif
                    @if($project->latitude && $project->longitude)
                        <div class="flex items-center gap-2 pt-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            <a href="https://maps.google.com/?q={{ $project->latitude }},{{ $project->longitude }}"
                               target="_blank"
                               class="text-sm font-mono text-gray-500 hover:underline"
                               style="color: #434eaa">
                                {{ $project->latitude }}, {{ $project->longitude }}
                                <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Features & Amenities --}}
            @if($project->project_features || $project->nearby_amenities || $project->marketing_highlights)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-700">Features & Amenities</h2>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-6">

                        @if($project->project_features && count($project->project_features) > 0)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Project Features</p>
                                <ul class="space-y-1.5">
                                    @foreach($project->project_features as $feature)
                                        <li class="flex items-center gap-2 text-sm text-gray-600">
                                            <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: #434eaa"></span>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($project->nearby_amenities && count($project->nearby_amenities) > 0)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Nearby Amenities</p>
                                <ul class="space-y-1.5">
                                    @foreach($project->nearby_amenities as $amenity)
                                        <li class="flex items-center gap-2 text-sm text-gray-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 shrink-0"></span>
                                            {{ $amenity }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($project->marketing_highlights && count($project->marketing_highlights) > 0)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Highlights</p>
                                <ul class="space-y-1.5">
                                    @foreach($project->marketing_highlights as $highlight)
                                        <li class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg class="w-3.5 h-3.5 shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            {{ $highlight }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    </div>
                </div>
            @endif

            {{-- Dates Timeline --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700">Project Timeline</h2>
                </div>
                <div class="p-5">
                    <div class="flex items-start gap-0 overflow-x-auto">
                        @php
                            $timelineItems = [
                                ['label' => 'Launch',       'date' => $project->launch_date,              'color' => '#434eaa'],
                                ['label' => 'Construction', 'date' => $project->construction_start_date,  'color' => '#f97316'],
                                ['label' => 'Completion',   'date' => $project->expected_completion_date, 'color' => '#16a34a'],
                                ['label' => 'Handover',     'date' => $project->handover_date,            'color' => '#2563eb'],
                            ];
                        @endphp
                        @foreach($timelineItems as $i => $item)
                            <div class="flex items-center flex-1 min-w-32">
                                <div class="flex flex-col items-center flex-1">
                                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center bg-white shrink-0"
                                         style="border-color: {{ $item['date'] ? $item['color'] : '#e5e7eb' }}">
                                        @if($item['date'])
                                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $item['color'] }}"></div>
                                        @else
                                            <div class="w-3 h-3 rounded-full bg-gray-200"></div>
                                        @endif
                                    </div>
                                    <p class="text-xs font-semibold text-gray-600 mt-2">{{ $item['label'] }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('M Y') : '—' }}
                                    </p>
                                </div>
                                @if($i < count($timelineItems) - 1)
                                    <div class="h-0.5 flex-1 bg-gray-200 mb-6"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Developer / Additional Info --}}
            @if($project->architect || $project->contractor || $project->rera_registration || $project->virtual_tour_url)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-700">Additional Details</h2>
                    </div>
                    <div class="p-5 grid grid-cols-2 gap-4">
                        @if($project->architect)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Architect</p>
                                <p class="text-sm font-medium text-gray-800">{{ $project->architect }}</p>
                            </div>
                        @endif
                        @if($project->contractor)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Contractor</p>
                                <p class="text-sm font-medium text-gray-800">{{ $project->contractor }}</p>
                            </div>
                        @endif
                        @if($project->rera_registration)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">RERA Registration</p>
                                <p class="text-sm font-medium text-gray-800">{{ $project->rera_registration }}</p>
                            </div>
                        @endif
                        @if($project->virtual_tour_url)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Virtual Tour</p>
                                <a href="{{ $project->virtual_tour_url }}" target="_blank"
                                   class="text-sm font-medium hover:underline flex items-center gap-1"
                                   style="color: #434eaa">
                                    Open Tour
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- Right Sidebar --}}
        <div class="w-72 shrink-0 space-y-4">

            {{-- Visibility Card --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Visibility</h3>
                </div>
                <div class="p-4 space-y-2.5">
                    @php
                        $badges = [
                            ['label' => 'Active',      'value' => $project->is_active,      'on' => 'green'],
                            ['label' => 'Published',   'value' => $project->published,      'on' => 'blue'],
                            ['label' => 'Featured',    'value' => $project->is_featured,    'on' => 'amber'],
                            ['label' => 'Premium',     'value' => $project->is_premium,     'on' => 'purple'],
                            ['label' => 'Hot Project', 'value' => $project->is_hot_project, 'on' => 'red'],
                            ['label' => 'Boosted',     'value' => $project->is_boosted,     'on' => 'indigo'],
                        ];
                        $onColors = [
                            'green'  => ['dot' => 'bg-green-500',  'text' => 'text-green-700',  'bg' => 'bg-green-50'],
                            'blue'   => ['dot' => 'bg-blue-500',   'text' => 'text-blue-700',   'bg' => 'bg-blue-50'],
                            'amber'  => ['dot' => 'bg-amber-500',  'text' => 'text-amber-700',  'bg' => 'bg-amber-50'],
                            'purple' => ['dot' => 'bg-purple-500', 'text' => 'text-purple-700', 'bg' => 'bg-purple-50'],
                            'red'    => ['dot' => 'bg-red-500',    'text' => 'text-red-700',    'bg' => 'bg-red-50'],
                            'indigo' => ['dot' => 'bg-indigo-500', 'text' => 'text-indigo-700', 'bg' => 'bg-indigo-50'],
                        ];
                    @endphp
                    @foreach($badges as $badge)
                        @php $c = $onColors[$badge['on']]; @endphp
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ $badge['label'] }}</span>
                            @if($badge['value'])
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full {{ $c['bg'] }} {{ $c['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></span>
                                    Yes
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                    No
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Analytics Card --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Analytics</h3>
                </div>
                <div class="p-4 space-y-3">
                    @php
                        $analyticsItems = [
                            ['label' => 'Total Views',    'value' => number_format($project->views ?? 0),            'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                            ['label' => 'Favorites',      'value' => number_format($project->favorites_count ?? 0),  'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                            ['label' => 'Inquiries',      'value' => number_format($project->inquiries_count ?? 0),  'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                            ['label' => 'Site Visits',    'value' => number_format($project->site_visits_count ?? 0),'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'],
                            ['label' => 'Bookings',       'value' => number_format($project->bookings_count ?? 0),   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                            ['label' => 'Units Sold',     'value' => number_format($project->units_sold ?? 0),       'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ];
                    @endphp
                    @foreach($analyticsItems as $item)
                        <div class="flex items-center justify-between py-0.5">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                                </svg>
                                {{ $item['label'] }}
                            </div>
                            <span class="text-sm font-bold text-gray-800">{{ $item['value'] }}</span>
                        </div>
                    @endforeach

                    {{-- Rating --}}
                    <div class="flex items-center justify-between py-0.5 border-t border-gray-100 pt-2 mt-1">
                        <span class="text-xs text-gray-500">Rating</span>
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= round($project->rating ?? 0) ? 'text-amber-400' : 'text-gray-200' }}"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                            <span class="text-xs font-semibold text-gray-700 ml-1">{{ number_format($project->rating ?? 0, 1) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Boost Info --}}
            @if($project->is_boosted)
                <div class="rounded-xl border overflow-hidden" style="background: linear-gradient(135deg, #eef0fc, #e8eaf8); border-color: #c7cbec">
                    <div class="px-4 py-3 border-b" style="border-color: #c7cbec">
                        <h3 class="text-sm font-semibold" style="color: #434eaa">⚡ Boost Active</h3>
                    </div>
                    <div class="p-4 space-y-2 text-xs" style="color: #434eaa">
                        @if($project->boost_start_date)
                            <div class="flex justify-between">
                                <span>Start</span>
                                <span class="font-semibold">{{ \Carbon\Carbon::parse($project->boost_start_date)->format('M d, Y') }}</span>
                            </div>
                        @endif
                        @if($project->boost_end_date)
                            <div class="flex justify-between">
                                <span>End</span>
                                <span class="font-semibold">{{ \Carbon\Carbon::parse($project->boost_end_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2" style="border-color: #c7cbec">
                                <span>Remaining</span>
                                <span class="font-bold">
                                    {{ max(0, now()->diffInDays(\Carbon\Carbon::parse($project->boost_end_date), false)) }} days
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Quick Actions</h3>
                </div>
                <div class="p-3 space-y-1.5">
                    <a href="{{ route('admin.projects.edit', $project->id) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Project
                    </a>
                    <form method="POST" action="{{ route('admin.projects.toggle.active', $project->id) }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                            </svg>
                            {{ $project->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.projects.delete', $project->id) }}"
                          onsubmit="return confirm('Delete this project permanently?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-red-600 hover:bg-red-50 transition-colors text-left">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Project
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4"
     onclick="closeLightbox()">
    <img id="lightboxImg" src="" alt="" class="max-w-full max-h-full rounded-xl object-contain">
    <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white bg-black/50 rounded-full p-2 hover:bg-black/70 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

@push('scripts')
<script>
    function openLightbox(src) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightbox').classList.remove('hidden');
        document.getElementById('lightbox').classList.add('flex');
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.add('hidden');
        document.getElementById('lightbox').classList.remove('flex');
    }
    document.addEventListener('keydown', e => { if(e.key === 'Escape') closeLightbox(); });
</script>
@endpush
@endsection