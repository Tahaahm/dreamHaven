@extends('layouts.admin-layout')

@section('title', 'Property Details')

@section('content')

<div class="max-w-[1600px] mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700">

    {{-- Header Section --}}
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Property Details</h1>
                <p class="text-gray-600 mt-1">Complete property information and analytics.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if($property->status == 'pending')
                <form action="{{ route('admin.properties.approve', $property->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-emerald-500 text-white px-6 py-2.5 rounded-xl font-bold shadow-sm hover:bg-emerald-600 hover:shadow-md transition flex items-center">
                        <i class="fas fa-check mr-2"></i> Approve
                    </button>
                </form>
                @endif

                <a href="{{ route('admin.properties.edit', $property->id) }}" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-sm hover:bg-indigo-700 hover:shadow-md transition flex items-center">
                    <i class="fas fa-pen mr-2"></i> Edit
                </a>

                <a href="{{ route('admin.properties.index') }}" class="bg-white text-gray-700 border border-gray-200 px-6 py-2.5 rounded-xl font-bold hover:bg-gray-50 transition flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Main Grid Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Left Column (Images & Details) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Property Images --}}
            @if(isset($property->images) && count($property->images) > 0)
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Property Images</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($property->images as $image)
                    <div class="relative group overflow-hidden rounded-xl h-48">
                        <img src="{{ asset($image) }}" alt="Property" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Property Information --}}
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Property Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Property Name</p>
                        <p class="text-base font-semibold text-gray-900">{{ $property->name['en'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Property Type</p>
                        <p class="text-base font-semibold text-gray-900">{{ $property->type['category'] ?? $property->type['en'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Listing Type</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700">
                            {{ ucfirst($property->listing_type) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Price</p>
                        <p class="text-xl font-black text-gray-900">
                            ${{ number_format($property->price['usd'] ?? $property->price['amount'] ?? 0) }}
                            <span class="text-xs font-bold text-gray-400">USD</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Area</p>
                        <p class="text-base font-semibold text-gray-900">{{ $property->area ?? 'N/A' }} m²</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Rooms</p>
                        <div class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                            <span><i class="fas fa-bed text-gray-400 mr-1"></i> {{ $property->rooms['bedroom']['count'] ?? 0 }} Bed</span>
                            <span><i class="fas fa-bath text-gray-400 mr-1"></i> {{ $property->rooms['bathroom']['count'] ?? 0 }} Bath</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Furnished</p>
                        <p class="text-base font-semibold text-gray-900">{{ $property->furnished ? 'Yes' : 'No' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Views</p>
                        <p class="text-base font-semibold text-gray-900">{{ number_format($property->views ?? 0) }}</p>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            @if(isset($property->description['en']))
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Description</h3>
                <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
                    {{ $property->description['en'] }}
                </div>
            </div>
            @endif

            {{-- Features --}}
            @if($property->features)
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Features</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($property->features as $feature)
                    <span class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold border border-indigo-100">
                        <i class="fas fa-check-circle mr-1.5 text-indigo-500"></i> {{ $feature }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column (Sidebar) --}}
        <div class="space-y-6">

            {{-- Status Card --}}
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Status</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-50">
                        <span class="text-sm font-medium text-gray-500">Current Status</span>
                        @php
                            $statusClass = match($property->status) {
                                'available' => 'bg-emerald-100 text-emerald-700',
                                'pending' => 'bg-amber-100 text-amber-700',
                                'sold' => 'bg-blue-100 text-blue-700',
                                'rejected' => 'bg-rose-100 text-rose-700',
                                default => 'bg-gray-100 text-gray-700'
                            };
                        @endphp
                        <span class="px-3 py-1 text-xs font-bold rounded-full {{ $statusClass }}">
                            {{ ucfirst($property->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Active</span>
                        @if($property->is_active)
                            <span class="text-xs font-bold text-emerald-600 flex items-center"><i class="fas fa-check mr-1"></i> Yes</span>
                        @else
                            <span class="text-xs font-bold text-rose-500 flex items-center"><i class="fas fa-times mr-1"></i> No</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Published</span>
                        @if($property->published)
                            <span class="text-xs font-bold text-emerald-600 flex items-center"><i class="fas fa-check mr-1"></i> Yes</span>
                        @else
                            <span class="text-xs font-bold text-rose-500 flex items-center"><i class="fas fa-times mr-1"></i> No</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Verified</span>
                        @if($property->verified)
                            <span class="text-xs font-bold text-emerald-600 flex items-center"><i class="fas fa-check mr-1"></i> Yes</span>
                        @else
                            <span class="text-xs font-bold text-gray-400 flex items-center"><i class="fas fa-minus mr-1"></i> No</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-sm font-medium text-gray-500">Boosted</span>
                        @if($property->is_boosted)
                            <span class="text-xs font-bold text-purple-600 flex items-center"><i class="fas fa-bolt mr-1"></i> Yes</span>
                        @else
                            <span class="text-xs font-bold text-gray-400 flex items-center"><i class="fas fa-minus mr-1"></i> No</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Owner Info --}}
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Owner Information</h3>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                        <i class="fas fa-user text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">
                            @if($property->owner)
                                @if(method_exists($property->owner, 'agent_name'))
                                    {{ $property->owner->agent_name }}
                                @elseif(method_exists($property->owner, 'company_name'))
                                    {{ $property->owner->company_name }}
                                @else
                                    {{ $property->owner->username ?? 'Unknown User' }}
                                @endif
                            @else
                                <span class="italic text-gray-400">Unknown Owner</span>
                            @endif
                        </p>
                        <p class="text-xs font-medium text-gray-500">{{ class_basename($property->owner_type) }}</p>
                    </div>
                </div>
                <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg border border-gray-100">
                    ID: <span class="font-mono text-gray-700">{{ $property->owner_id }}</span>
                </div>
            </div>

            {{-- Stats --}}
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Statistics</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600"><i class="fas fa-eye text-indigo-400 w-5"></i> Total Views</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($property->views ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600"><i class="fas fa-heart text-rose-400 w-5"></i> Favorites</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($property->favorites_count ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600"><i class="fas fa-star text-amber-400 w-5"></i> Rating</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($property->rating ?? 0, 1) }}</span>
                    </div>
                    <div class="pt-3 border-t border-gray-50 mt-2">
                        <span class="text-xs font-medium text-gray-400 block mb-1">Created Date</span>
                        <span class="text-sm font-bold text-gray-700">{{ $property->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ✅ NEW SECTION: Recent Authenticated Viewers --}}
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden mb-12">
        <div class="px-8 py-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900 tracking-tight">Recent Authenticated Viewers</h3>
                <p class="text-gray-500 font-medium text-sm mt-1">
                    List of registered users who have viewed this property details page.
                </p>
            </div>

            @php
                // Calculate unique authenticated viewers count
                $uniqueAuthViews = $property->interactions ? $property->interactions->where('interaction_type', 'view')->unique('user_id')->count() : 0;
            @endphp

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    <i class="fas fa-users mr-2"></i> {{ $uniqueAuthViews }} Unique Users
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">User Details</th>
                        <th class="px-8 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Contact Info</th>
                        <th class="px-8 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Account Status</th>
                        <th class="px-8 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-right">Last Viewed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @if($property->interactions)
                        @forelse($property->interactions->where('interaction_type', 'view')->whereNotNull('user') as $interaction)
                        <tr class="hover:bg-gray-50 transition-colors group">
                            {{-- User Profile --}}
                            <td class="px-8 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 overflow-hidden shrink-0">
                                        @if($interaction->user->photo_image)
                                            <img src="{{ $interaction->user->photo_image }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.users.show', $interaction->user->id) }}" class="text-sm font-bold text-gray-900 hover:text-indigo-600 transition">
                                            {{ $interaction->user->username }}
                                        </a>
                                        <p class="text-[10px] text-gray-400 font-mono mt-0.5">ID: {{ substr($interaction->user->id, 0, 8) }}...</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact Info --}}
                            <td class="px-8 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <a href="mailto:{{ $interaction->user->email }}" class="flex items-center text-xs font-medium text-gray-600 hover:text-indigo-600 transition w-fit">
                                        <i class="fas fa-envelope text-gray-300 w-5"></i> {{ $interaction->user->email }}
                                    </a>
                                    @if($interaction->user->phone)
                                    <a href="tel:{{ $interaction->user->phone }}" class="flex items-center text-xs font-medium text-gray-600 hover:text-indigo-600 transition w-fit">
                                        <i class="fas fa-phone text-gray-300 w-5"></i> {{ $interaction->user->phone }}
                                    </a>
                                    @endif
                                </div>
                            </td>

                            {{-- Account Status --}}
                            <td class="px-8 py-4">
                                @if($interaction->user->is_verified)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <i class="fas fa-check-circle text-[10px]"></i> Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                        <i class="fas fa-clock text-[10px]"></i> Unverified
                                    </span>
                                @endif
                            </td>

                            {{-- Viewed Date --}}
                            <td class="px-8 py-4 text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-sm font-bold text-gray-700">{{ $interaction->created_at->diffForHumans() }}</span>
                                    <span class="text-xs text-gray-400 font-mono mt-0.5">{{ $interaction->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-16 text-center">
                                <div class="max-w-xs mx-auto">
                                    <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                                        <i class="fas fa-user-slash text-2xl"></i>
                                    </div>
                                    <h3 class="text-gray-900 font-bold text-sm">No registered viewers yet</h3>
                                    <p class="text-gray-500 text-xs mt-1 leading-relaxed">
                                        This property has {{ number_format($property->views) }} total views, but no logged-in users have viewed it yet since tracking was enabled.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    @else
                        <tr>
                            <td colspan="4" class="px-8 py-8 text-center text-gray-500 text-sm">
                                Interaction data is not loaded. Please ensure controller loads the relationship.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
