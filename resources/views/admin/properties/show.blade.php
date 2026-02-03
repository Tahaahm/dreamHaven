@extends('layouts.admin-layout')

@section('title', 'Property Details')

@section('content')

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Property Details</h1>
            <p class="text-gray-600 mt-1">Complete property information</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.properties.viewers', $property->id) }}"
               class="bg-indigo-500 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition">
                <i class="fas fa-users mr-2"></i> Viewers ({{ $property->interactions->where('interaction_type', 'impression')->count() }})
            </a>

            @if($property->status == 'pending')
            <form action="{{ route('admin.properties.approve', $property->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition">
                    <i class="fas fa-check mr-2"></i> Approve
                </button>
            </form>
            @endif
            <a href="{{ route('admin.properties.edit', $property->id) }}" class="gradient-primary text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('admin.properties.index') }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Property Images -->
    <div class="lg:col-span-2">
        @if(isset($property->images) && count($property->images) > 0)
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Property Images</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($property->images as $image)
                <img src="{{ asset($image) }}" alt="Property" class="w-full h-48 object-cover rounded-lg">
                @endforeach
            </div>
        </div>
        @endif

        <!-- Property Details -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Property Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Property Name</p>
                    <p class="text-base font-semibold text-gray-800">{{ $property->name['en'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Property Type</p>
                    <p class="text-base font-semibold text-gray-800">
                        @php
                            $typeData = is_string($property->type) ? json_decode($property->type, true) : $property->type;
                            $typeCategory = is_array($typeData) ? ($typeData['category'] ?? 'N/A') : 'N/A';
                        @endphp
                        {{ $typeCategory }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Listing Type</p>
                    <p class="text-base font-semibold text-gray-800">{{ ucfirst($property->listing_type) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Price</p>
                    <p class="text-base font-semibold text-gray-800">
                        @php
                            $priceData = is_string($property->price) ? json_decode($property->price, true) : $property->price;
                            $priceAmount = is_array($priceData) ? ($priceData['usd'] ?? $priceData['amount'] ?? 0) : ($priceData ?? 0);
                        @endphp
                        ${{ number_format($priceAmount) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Area</p>
                    <p class="text-base font-semibold text-gray-800">{{ $property->area ?? 'N/A' }} sqm</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Rooms</p>
                    <p class="text-base font-semibold text-gray-800">
                        @php
                            $rooms = is_string($property->rooms) ? json_decode($property->rooms, true) : $property->rooms;
                            $bedrooms = 0;
                            $bathrooms = 0;

                            if (is_array($rooms)) {
                                // Try different possible structures
                                $bedrooms = $rooms['bedroom']['count'] ?? $rooms['bedroom'] ?? $rooms['bedrooms'] ?? 0;
                                $bathrooms = $rooms['bathroom']['count'] ?? $rooms['bathroom'] ?? $rooms['bathrooms'] ?? 0;
                            }
                        @endphp
                        {{ $bedrooms }} Bed, {{ $bathrooms }} Bath
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Furnished</p>
                    <p class="text-base font-semibold text-gray-800">{{ $property->furnished ? 'Yes' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Views</p>
                    <p class="text-base font-semibold text-gray-800">{{ number_format($property->views ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Description -->
        @if(isset($property->description['en']))
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Description</h3>
            <p class="text-gray-600 leading-relaxed">{{ $property->description['en'] }}</p>
        </div>
        @endif

        <!-- Features -->
        @if($property->features)
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Features</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($property->features as $feature)
                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold">
                    <i class="fas fa-check mr-1"></i> {{ $feature }}
                </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">

        <!-- Status Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Status</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Status</span>
                    @if($property->status == 'available')
                        <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Available</span>
                    @elseif($property->status == 'pending')
                        <span class="px-3 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full">Pending</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full">{{ ucfirst($property->status) }}</span>
                    @endif
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Active</span>
                    <span class="text-sm font-semibold {{ $property->is_active ? 'text-green-600' : 'text-red-600' }}">
                        {{ $property->is_active ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Published</span>
                    <span class="text-sm font-semibold {{ $property->published ? 'text-green-600' : 'text-red-600' }}">
                        {{ $property->published ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Verified</span>
                    <span class="text-sm font-semibold {{ $property->verified ? 'text-green-600' : 'text-red-600' }}">
                        {{ $property->verified ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Boosted</span>
                    <span class="text-sm font-semibold {{ $property->is_boosted ? 'text-purple-600' : 'text-gray-600' }}">
                        {{ $property->is_boosted ? 'Yes' : 'No' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Owner Info -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Owner Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Owner Type</p>
                    <p class="text-base font-semibold text-gray-800">{{ class_basename($property->owner_type) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Owner ID</p>
                    <p class="text-base font-semibold text-gray-800">{{ $property->owner_id }}</p>
                </div>
                @if($property->owner)
                <div>
                    <p class="text-sm text-gray-600 mb-1">Owner Name</p>
                    <p class="text-base font-semibold text-gray-800">
                        @php
                            $ownerName = 'Unknown';
                            if ($property->owner_type === 'App\Models\Agent') {
                                $ownerName = $property->owner->name
                                    ?? $property->owner->agent_name
                                    ?? $property->owner->username
                                    ?? 'Unknown Agent';
                            } elseif ($property->owner_type === 'App\Models\RealEstateOffice') {
                                $ownerName = $property->owner->company_name
                                    ?? $property->owner->name
                                    ?? 'Unknown Office';
                            } elseif ($property->owner_type === 'App\Models\User') {
                                $ownerName = $property->owner->username
                                    ?? $property->owner->name
                                    ?? 'Unknown User';
                            }
                        @endphp
                        {{ $ownerName }}
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Stats -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Statistics</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600"><i class="fas fa-eye text-purple-500 mr-2"></i>Views</span>
                    <span class="text-base font-semibold text-gray-800">{{ number_format($property->views ?? 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600"><i class="fas fa-heart text-red-500 mr-2"></i>Favorites</span>
                    <span class="text-base font-semibold text-gray-800">{{ $property->favorites_count ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600"><i class="fas fa-star text-yellow-500 mr-2"></i>Rating</span>
                    <span class="text-base font-semibold text-gray-800">{{ number_format($property->rating ?? 0, 1) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600"><i class="fas fa-calendar text-blue-500 mr-2"></i>Created</span>
                    <span class="text-base font-semibold text-gray-800">{{ $property->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Property Viewers -->
        @if($property->interactions->where('interaction_type', 'impression')->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center justify-between">
                <span><i class="fas fa-eye text-indigo-500 mr-2"></i>Recent Viewers</span>
                <span class="text-sm font-normal text-gray-500">({{ $property->interactions->where('interaction_type', 'impression')->count() }} total)</span>
            </h3>

            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($property->interactions->where('interaction_type', 'impression')->take(20) as $interaction)
                    @if($interaction->user)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold shrink-0">
                            {{ strtoupper(substr($interaction->user->username ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">
                                {{ $interaction->user->username ?? 'Unknown User' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $interaction->created_at->diffForHumans() }}
                            </p>
                        </div>
                        @if($interaction->user->email)
                        <a href="mailto:{{ $interaction->user->email }}"
                           class="text-indigo-600 hover:text-indigo-700 text-xs"
                           title="Contact viewer">
                            <i class="fas fa-envelope"></i>
                        </a>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>

            @if($property->interactions->where('interaction_type', 'impression')->count() > 20)
            <div class="mt-3 text-center">
                <a href="{{ route('admin.properties.viewers', $property->id) }}"
                   class="text-sm text-indigo-600 hover:text-indigo-700 font-semibold">
                    View All {{ $property->interactions->where('interaction_type', 'impression')->count() }} Viewers
                </a>
            </div>
            @endif
        </div>
        @endif

    </div>

</div>

@endsection
