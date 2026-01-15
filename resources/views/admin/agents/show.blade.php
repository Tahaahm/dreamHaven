@extends('layouts.admin-layout')

@section('title', $agent->agent_name . ' - Profile')

@section('content')

<div class="max-w-7xl mx-auto animate-fade-in-up">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex text-sm text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                <span class="mx-2 text-slate-300">/</span>
                <a href="{{ route('admin.agents.index') }}" class="hover:text-slate-800 transition">Agents</a>
                <span class="mx-2 text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">Profile</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                {{ $agent->agent_name }}
                @if($agent->is_verified)
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
            <a href="{{ route('admin.agents.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-lg hover:bg-slate-50 transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>

            <a href="{{ route('admin.agents.edit', $agent->id) }}" class="px-4 py-2 bg-slate-900 text-white text-sm font-bold rounded-lg shadow-lg hover:bg-slate-800 transition flex items-center gap-2">
                <i class="fas fa-pen-to-square"></i> Edit Profile
            </a>

            <div class="relative group">
                <button class="px-3 py-2 bg-white border border-slate-200 text-slate-500 rounded-lg hover:text-slate-700 hover:bg-slate-50 transition shadow-sm">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden">
                    @if(!$agent->is_verified)
                    <form action="{{ route('admin.agents.verify', $agent->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-xs font-bold text-emerald-600 hover:bg-emerald-50 transition">
                            <i class="fas fa-check-circle mr-2"></i> Approve Agent
                        </button>
                    </form>
                    <div class="border-t border-slate-100"></div>
                    @endif

                    <form action="{{ route('admin.agents.delete', $agent->id) }}" method="POST" onsubmit="return confirm('Delete this agent permanently?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full text-left px-4 py-3 text-xs font-bold text-rose-600 hover:bg-rose-50 transition">
                            <i class="fas fa-trash-alt mr-2"></i> Delete Agent
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT COLUMN: Identity & Contact --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Profile Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="h-32 bg-slate-100 relative">
                    @if($agent->bio_image)
                        <img src="{{ asset($agent->bio_image) }}" class="w-full h-full object-cover opacity-80">
                    @else
                        <div class="w-full h-full bg-gradient-to-r from-slate-800 to-slate-900"></div>
                    @endif
                </div>
                <div class="px-6 pb-6 text-center relative">
                    <div class="relative -mt-16 mb-4 inline-block">
                        <div class="w-32 h-32 rounded-2xl border-4 border-white bg-white shadow-md overflow-hidden">
                            @if($agent->profile_image)
                                <img src="{{ asset($agent->profile_image) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400 text-4xl font-bold">
                                    {{ strtoupper(substr($agent->agent_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="absolute bottom-2 right-2 bg-white rounded-full p-1.5 shadow-sm border border-slate-100" title="{{ ucfirst($agent->type) }}">
                            @if($agent->type === 'company')
                                <i class="fas fa-building text-indigo-600 text-sm"></i>
                            @else
                                <i class="fas fa-user-tie text-blue-600 text-sm"></i>
                            @endif
                        </div>
                    </div>

                    <h2 class="text-xl font-bold text-slate-900">{{ $agent->agent_name }}</h2>
                    <p class="text-sm text-slate-500 font-medium mb-4">
                        {{ $agent->city ?? 'Unknown City' }}
                        @if($agent->district) • {{ $agent->district }} @endif
                    </p>

                    <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4">
                        <div>
                            <span class="block text-xl font-bold text-slate-900">{{ $agent->years_experience ?? 0 }}</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Years Exp.</span>
                        </div>
                        <div class="border-l border-slate-100">
                            <span class="block text-xl font-bold text-slate-900 flex justify-center items-center gap-1">
                                {{ number_format((float)$agent->overall_rating, 1) }} <i class="fas fa-star text-amber-400 text-xs"></i>
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Rating</span>
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
                            <a href="mailto:{{ $agent->primary_email }}" class="text-sm font-bold text-slate-900 hover:text-blue-600 truncate block">{{ $agent->primary_email }}</a>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Phone Number</p>
                            <a href="tel:{{ $agent->primary_phone }}" class="text-sm font-bold text-slate-900 hover:text-emerald-600">{{ $agent->primary_phone ?? 'N/A' }}</a>
                        </div>
                    </div>

                    @if($agent->whatsapp_number)
                    <div class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-green-50 group-hover:text-green-600 transition">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">WhatsApp</p>
                            <a href="https://wa.me/{{ $agent->whatsapp_number }}" target="_blank" class="text-sm font-bold text-slate-900 hover:text-green-600">{{ $agent->whatsapp_number }}</a>
                        </div>
                    </div>
                    @endif

                    @if($agent->office_address)
                    <div class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-purple-50 group-hover:text-purple-600 transition">
                            <i class="fas fa-map-pin"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Office Address</p>
                            <p class="text-sm font-bold text-slate-900 leading-tight">{{ $agent->office_address }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Subscription Card --}}
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-5 rounded-full blur-xl"></div>

                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-[10px] text-slate-300 font-bold uppercase tracking-wider">Current Plan</p>
                        <h3 class="text-2xl font-black text-white mt-1">
                            {{ ucfirst($agent->current_plan ?? ($agent->subscription->currentPlan->name ?? 'Free Plan')) }}
                        </h3>
                    </div>
                    @if($agent->subscription && $agent->subscription->status === 'active')
                        <span class="px-2 py-1 bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 rounded text-[10px] font-bold uppercase">Active</span>
                    @else
                        <span class="px-2 py-1 bg-slate-700 border border-slate-600 text-slate-300 rounded text-[10px] font-bold uppercase">Inactive</span>
                    @endif
                </div>

                @if($agent->subscription)
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm border-b border-white/10 pb-2">
                        <span class="text-slate-400 font-medium">Properties Limit</span>
                        <span class="font-bold">{{ $agent->subscription->property_activation_limit > 0 ? $agent->subscription->property_activation_limit : 'Unlimited' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-white/10 pb-2">
                        <span class="text-slate-400 font-medium">Used Slots</span>
                        <span class="font-bold">{{ $agent->subscription->properties_activated_this_month ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm pt-1">
                        <span class="text-slate-400 font-medium">Expires On</span>
                        <span class="font-bold text-amber-300">{{ $agent->subscription->end_date ? $agent->subscription->end_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <p class="text-sm text-slate-400 mb-3">No active subscription found.</p>
                    <a href="{{ route('admin.agents.edit', $agent->id) }}" class="inline-block px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg text-xs font-bold transition">Assign Plan</a>
                </div>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: Stats & Details --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Key Statistics --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-home text-xs"></i></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Listings</span>
                    </div>
                    <p class="text-2xl font-black text-slate-900">{{ $agent->properties->count() }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fas fa-handshake text-xs"></i></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Sold</span>
                    </div>
                    <p class="text-2xl font-black text-slate-900">{{ $agent->properties_sold ?? 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center"><i class="fas fa-eye text-xs"></i></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Total Views</span>
                    </div>
                    {{-- Note: Ensure total_views is a number, not array, in your controller --}}
                    <p class="text-2xl font-black text-slate-900">{{ number_format((float)($agent->properties->sum('views') ?? 0)) }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fas fa-wallet text-xs"></i></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Commission</span>
                    </div>
                    <p class="text-2xl font-black text-slate-900">{{ (float)($agent->commission_rate) }}%</p>
                </div>
            </div>

            {{-- Professional Details --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Professional Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Company / Agency</p>
                            <p class="text-sm font-bold text-slate-900">{{ $agent->company_name ?? 'Independent Agent' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">License Number</p>
                            <p class="text-sm font-bold text-slate-900 font-mono">{{ $agent->license_number ?? 'Not Provided' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Consultation Fee</p>
                            <p class="text-sm font-bold text-slate-900">
                                @if($agent->consultation_fee > 0)
                                    {{ number_format((float)$agent->consultation_fee) }} {{ $agent->currency }}
                                @else
                                    Free
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($agent->agent_bio)
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">About Agent</p>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-sm text-slate-600 leading-relaxed">
                            {{ $agent->agent_bio }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Properties Table --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Recent Properties</h3>
                    <a href="{{ route('admin.properties.index', ['owner_type' => 'Agent', 'search' => $agent->agent_name]) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">View All Listings →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Property</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Price</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($agent->properties->take(5) as $property)
                            {{-- SAFE DATA EXTRACTION LOGIC --}}
                            @php
                                // Ensure we decode JSON only if it's a string, otherwise use it directly (if casted) or as default
                                $nameData = is_string($property->name) ? json_decode($property->name, true) : $property->name;
                                $propName = is_array($nameData) ? ($nameData['en'] ?? $nameData['ar'] ?? 'Property') : $nameData;

                                $imageData = is_string($property->images) ? json_decode($property->images, true) : $property->images;
                                $firstImage = is_array($imageData) ? ($imageData[0] ?? null) : $imageData;

                                $typeData = is_string($property->type) ? json_decode($property->type, true) : $property->type;
                                $category = is_array($typeData) ? ($typeData['category'] ?? 'N/A') : $typeData;

                                $priceData = is_string($property->price) ? json_decode($property->price, true) : $property->price;
                                $priceVal = is_array($priceData) ? ($priceData['usd'] ?? 0) : $priceData;
                            @endphp

                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-slate-200 overflow-hidden shrink-0 border border-slate-100">
                                            @if($firstImage)
                                                <img src="{{ $firstImage }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-400"><i class="fas fa-home"></i></div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-900 truncate max-w-[150px]">{{ $propName }}</p>
                                            <p class="text-[10px] text-slate-500">{{ $property->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-xs font-bold text-slate-600">
                                    {{ ucfirst($category) }}
                                </td>
                                <td class="px-6 py-3 text-xs font-bold text-slate-900">
                                    ${{ number_format((float)$priceVal) }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @php
                                        $statusConfig = match($property->status) {
                                            'available' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
                                            'sold' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
                                            'rented' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600'],
                                            default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-500'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                        {{ $property->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('admin.properties.show', $property->id) }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 transition">
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
