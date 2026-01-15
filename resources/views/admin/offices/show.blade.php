@extends('layouts.admin-layout')

@section('title', $office->company_name . ' - Office Profile')

@section('content')

<div class="max-w-7xl mx-auto animate-fade-in-up">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex text-sm text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                <span class="mx-2 text-slate-300">/</span>
                <a href="{{ route('admin.offices.index') }}" class="hover:text-slate-800 transition">Offices</a>
                <span class="mx-2 text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">Profile</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                {{ $office->company_name }}
                @if($office->is_verified)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                        <i class="fas fa-check-circle text-[10px]"></i> Verified
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200 flex items-center gap-1">
                        <i class="fas fa-clock text-[10px]"></i> Pending Review
                    </span>
                @endif
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.offices.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-lg hover:bg-slate-50 transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>

            <a href="{{ route('admin.offices.edit', $office->id) }}" class="px-4 py-2 bg-slate-900 text-white text-sm font-bold rounded-lg shadow-lg hover:bg-slate-800 transition flex items-center gap-2">
                <i class="fas fa-pen-to-square"></i> Edit Office
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT COLUMN: Identity & Contact --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Profile Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="h-32 bg-slate-100 relative">
                    @if($office->company_bio_image)
                        <img src="{{ asset($office->company_bio_image) }}" class="w-full h-full object-cover opacity-90">
                    @else
                        <div class="w-full h-full bg-gradient-to-r from-slate-800 to-slate-900 flex items-center justify-center">
                            <i class="fas fa-building text-white/10 text-6xl"></i>
                        </div>
                    @endif
                </div>

                <div class="px-6 pb-6 text-center relative">
                    <div class="relative -mt-16 mb-4 inline-block">
                        <div class="w-32 h-32 rounded-2xl border-4 border-white bg-white shadow-md overflow-hidden flex items-center justify-center">
                            @if($office->profile_image)
                                <img src="{{ asset($office->profile_image) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-4xl font-bold text-slate-300 uppercase">{{ substr($office->company_name, 0, 2) }}</span>
                            @endif
                        </div>
                    </div>

                    <h2 class="text-xl font-bold text-slate-900">{{ $office->company_name }}</h2>
                    <p class="text-sm text-slate-500 font-medium mb-1">{{ $office->company_bio ?? 'Real Estate Agency' }}</p>
                    <p class="text-xs text-slate-400 mb-6">
                        {{ $office->city ?? 'Unknown City' }}
                        @if($office->district) • {{ $office->district }} @endif
                    </p>

                    <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4 text-left">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">License</p>
                            <p class="text-sm font-bold text-slate-900 font-mono">{{ $office->license_number ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Since</p>
                            <p class="text-sm font-bold text-slate-900">{{ $office->created_at->format('Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <i class="fas fa-address-card text-slate-400"></i> Contact Details
                </h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Email Address</p>
                            <a href="mailto:{{ $office->email_address }}" class="text-sm font-bold text-slate-900 hover:text-blue-600 truncate block">{{ $office->email_address }}</a>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Phone Number</p>
                            <a href="tel:{{ $office->phone_number }}" class="text-sm font-bold text-slate-900 hover:text-emerald-600">{{ $office->phone_number ?? 'N/A' }}</a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 group">
                        <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-purple-50 group-hover:text-purple-600 transition shrink-0">
                            <i class="fas fa-map-pin"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Headquarters</p>
                            <p class="text-sm font-bold text-slate-900 leading-tight">{{ $office->office_address ?? 'No physical address' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Map Location --}}
            @if($office->latitude && $office->longitude)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-1">
                <iframe
                    width="100%"
                    height="200"
                    frameborder="0"
                    style="border:0; border-radius: 12px;"
                    src="https://maps.google.com/maps?q={{ $office->latitude }},{{ $office->longitude }}&hl=en&z=14&output=embed">
                </iframe>
            </div>
            @endif

            {{-- Availability Schedule (New) --}}
            @if($office->availability_schedule)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-slate-400"></i> Opening Hours
                </h3>
                <div class="space-y-2 text-sm">
                    @php
                        $schedule = is_string($office->availability_schedule) ? json_decode($office->availability_schedule, true) : $office->availability_schedule;
                    @endphp
                    @if(is_array($schedule))
                        @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                            @if(isset($schedule[$day]) && ($schedule[$day]['active'] ?? false))
                                <div class="flex justify-between items-center">
                                    <span class="capitalize text-slate-500 font-medium">{{ $day }}</span>
                                    <span class="font-bold text-slate-900">{{ $schedule[$day]['start'] }} - {{ $schedule[$day]['end'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <p class="text-slate-400 italic">No schedule set.</p>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- RIGHT COLUMN: Stats & Listings --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Key Statistics --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-home text-xs"></i></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Listings</span>
                    </div>
                    <p class="text-2xl font-black text-slate-900">{{ $office->ownedProperties->count() }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fas fa-users text-xs"></i></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Agents</span>
                    </div>
                    {{-- Assuming relationship agents() exists --}}
                    <p class="text-2xl font-black text-slate-900">{{ $office->agents->count() ?? 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fas fa-handshake text-xs"></i></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Sold</span>
                    </div>
                    <p class="text-2xl font-black text-slate-900">{{ $office->properties_sold ?? 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fas fa-star text-xs"></i></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Rating</span>
                    </div>
                    <p class="text-2xl font-black text-slate-900">{{ number_format($office->average_rating ?? 0, 1) }}</p>
                </div>
            </div>

            {{-- Subscription Information --}}
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-5 rounded-full blur-xl"></div>
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Current Plan</p>
                        <h3 class="text-2xl font-black text-white">
                            {{ ucfirst($office->current_plan ?? ($office->subscription->currentPlan->name ?? 'Standard Plan')) }}
                        </h3>
                        @if($office->subscription)
                            <p class="text-xs text-slate-300 mt-1">
                                Expires: <span class="text-white font-bold">{{ $office->subscription->end_date ? $office->subscription->end_date->format('M d, Y') : 'Never' }}</span>
                            </p>
                        @else
                            <p class="text-xs text-rose-300 mt-1 font-bold">No active subscription</p>
                        @endif
                    </div>
                    <div>
                         @if($office->subscription && $office->subscription->status === 'active')
                            <span class="px-3 py-1.5 bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 rounded-lg text-xs font-bold uppercase flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        @else
                            <a href="{{ route('admin.offices.edit', $office->id) }}" class="px-4 py-2 bg-white text-slate-900 rounded-lg text-xs font-bold hover:bg-slate-100 transition">
                                Assign Plan
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Description --}}
            @if($office->about_company)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-3">About Office</h3>
                <div class="prose prose-sm text-slate-600 max-w-none">
                    <p>{{ $office->about_company }}</p>
                </div>
            </div>
            @endif

            {{-- Properties Table --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Recent Listings</h3>
                    <a href="{{ route('admin.properties.index', ['owner_type' => 'RealEstateOffice', 'search' => $office->company_name]) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">View All Listings →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Property</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Price</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($office->ownedProperties->take(5) as $property)
                            {{-- Safe Data Extraction --}}
                            @php
                                $nameData = is_string($property->name) ? json_decode($property->name, true) : $property->name;
                                $propName = is_array($nameData) ? ($nameData['en'] ?? 'Property') : $nameData;

                                $priceData = is_string($property->price) ? json_decode($property->price, true) : $property->price;
                                $priceVal = is_array($priceData) ? ($priceData['usd'] ?? 0) : $priceData;

                                $imageData = is_string($property->images) ? json_decode($property->images, true) : $property->images;
                                $firstImage = is_array($imageData) ? ($imageData[0] ?? null) : $imageData;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-slate-200 overflow-hidden shrink-0 border border-slate-100 flex items-center justify-center">
                                           @if($firstImage)
                                                <img src="{{ $firstImage }}" class="w-full h-full object-cover">
                                           @else
                                                <i class="fas fa-home text-slate-400"></i>
                                           @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-900 truncate max-w-[150px]">{{ $propName }}</p>
                                            <p class="text-[10px] text-slate-500">{{ $property->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-xs font-bold text-slate-600">
                                    {{ ucfirst($property->listing_type) }}
                                </td>
                                <td class="px-6 py-3 text-xs font-bold text-slate-900">
                                    ${{ number_format((float)$priceVal) }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                                        {{ $property->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('admin.properties.show', $property->id) }}" class="text-slate-400 hover:text-indigo-600">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500 italic">No properties listed yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
