@extends('layouts.admin-layout')

@section('title', $user->username)

@section('content')

<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="h-32 bg-slate-900 relative">
        <div class="absolute inset-0 bg-gradient-to-r from-slate-900 to-slate-800 opacity-90"></div>
        <div class="absolute right-0 top-0 h-full w-1/3 bg-white/5 skew-x-12"></div>
    </div>

    <div class="px-8 pb-8 relative">
        <div class="flex flex-col md:flex-row items-end gap-6 -mt-12">

            <div class="relative">
                <div class="w-32 h-32 rounded-xl border-4 border-white bg-white shadow-md overflow-hidden">
                    @if($user->photo_image)
                        <img src="{{ asset($user->photo_image) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-400 text-4xl font-bold">
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        </div>
                    @endif
                </div>
                @if($user->is_verified)
                <div class="absolute -bottom-2 -right-2 bg-emerald-500 text-white rounded-full p-1.5 border-4 border-white" title="Verified User">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                @endif
            </div>

            <div class="flex-1 mb-1">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $user->username }}</h1>
                        <div class="flex items-center gap-4 mt-2 text-sm text-slate-500 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i class="far fa-envelope"></i> {{ $user->email }}
                            </span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="flex items-center gap-1.5 uppercase">
                                <i class="fas fa-shield-alt text-slate-400"></i> {{ $user->role }}
                            </span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        <span class="px-2 py-0.5 rounded text-xs font-bold uppercase border {{ ($user->is_active ?? 1) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                            {{ ($user->is_active ?? 1) ? 'Active' : 'Suspended' }}
                        </span>
                        </div>
                    </div>

                    {{-- <div class="flex items-center gap-3">
                        <form action="{{ route($user->is_verified ? 'admin.users.unverify' : 'admin.users.verify', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 font-semibold text-sm rounded-lg hover:bg-slate-50 hover:text-slate-900 transition">
                                {{ $user->is_verified ? 'Revoke Verification' : 'Verify Identity' }}
                            </button>
                        </form>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="px-5 py-2 bg-slate-900 text-white text-sm font-bold rounded-lg shadow-sm hover:bg-black transition flex items-center gap-2">
                            <i class="fas fa-pen text-xs"></i> Edit
                        </a>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600"><i class="fas fa-building text-xl"></i></div>
            <span class="text-xs font-bold text-slate-400 uppercase">Properties</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $user->ownedProperties->count() }}</p>
    </div>

    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-purple-50 rounded-lg text-purple-600"><i class="fas fa-calendar-check text-xl"></i></div>
            <span class="text-xs font-bold text-slate-400 uppercase">Appointments</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $user->appointments->count() }}</p>
    </div>

    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-pink-50 rounded-lg text-pink-600"><i class="fas fa-heart text-xl"></i></div>
            <span class="text-xs font-bold text-slate-400 uppercase">Favorites</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $user->favoriteProperties->count() }}</p>
    </div>

    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600"><i class="fas fa-history text-xl"></i></div>
            <span class="text-xs font-bold text-slate-400 uppercase">Sessions</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $user->sessions->count() }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="space-y-6">

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-3">Personal Details</h3>

            <div class="space-y-4">
                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase">Phone Number</label>
                    <p class="text-sm font-medium text-slate-800">{{ $user->phone ?? 'Not provided' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase">Address / Place</label>
                    <p class="text-sm font-medium text-slate-800">{{ $user->place ?? 'Not specified' }}</p>
                </div>

                @if($user->lat && $user->lng)
                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase">Location</label>
                    <a href="https://maps.google.com/?q={{ $user->lat }},{{ $user->lng }}" target="_blank" class="flex items-center gap-2 mt-1 text-sm text-blue-600 hover:underline">
                        <i class="fas fa-map-marker-alt"></i> View on Map
                    </a>
                </div>
                @endif

                @if($user->about_me)
                <div class="pt-2">
                    <label class="text-xs font-semibold text-slate-400 uppercase">About</label>
                    <p class="text-sm text-slate-600 italic mt-1 leading-relaxed">"{{ $user->about_me }}"</p>
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Active Devices</h3>
                <span class="bg-slate-100 text-slate-600 text-xs font-bold px-2 py-1 rounded">{{ count($user->device_tokens ?? []) }}</span>
            </div>

            @if(!empty($user->device_tokens) && is_array($user->device_tokens))
                <div class="space-y-3">
                    @foreach($user->device_tokens as $index => $token)
                        <div class="flex items-start gap-3">
                            <div class="mt-1 w-8 h-8 bg-slate-50 rounded flex items-center justify-center text-slate-400 shrink-0">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800">Device #{{ $index + 1 }}</p>
                                <p class="text-xs text-slate-500 font-mono truncate max-w-[200px]" title="{{ is_array($token) ? ($token['device_id'] ?? 'N/A') : 'N/A' }}">
                                    ID: {{ is_array($token) ? ($token['device_id'] ?? 'N/A') : 'Unknown' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-sm text-slate-400">No registered devices found.</p>
                </div>
            @endif
        </div>

    </div>

    <div class="lg:col-span-2 space-y-6">

       <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Search Preferences</h3>
            </div>

            <div class="p-0">
                @if(!empty($user->search_preferences) && is_array($user->search_preferences))
                    <table class="w-full text-left">
                        <tbody>
                            @foreach($user->search_preferences as $key => $value)
                                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition">
                                    <td class="py-3 px-6 text-sm font-semibold text-slate-500 capitalize w-1/3 align-top">
                                        {{ str_replace(['_', '-'], ' ', $key) }}
                                    </td>

                                    <td class="py-3 px-6 text-sm font-medium text-slate-900">
                                        @if(is_array($value))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($value as $subKey => $subValue)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                                        @if(!is_numeric($subKey))
                                                            <span class="text-slate-500 mr-1">{{ $subKey }}:</span>
                                                        @endif

                                                        @if(is_array($subValue))
                                                            {{ json_encode($subValue) }} @else
                                                            {{ $subValue }}
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-8 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 mb-3 text-slate-400">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <p class="text-sm text-slate-500">User hasn't saved any search filters yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Latest Activity</h3>
            </div>

            <div class="p-0">
                @if($user->favoriteProperties->isNotEmpty())
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-xs text-slate-500 uppercase font-semibold">
                            <tr>
                                <th class="px-6 py-3">Property</th>
                                <th class="px-6 py-3">Added Date</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($user->favoriteProperties->take(5) as $fav)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3">
                                    <span class="text-sm font-medium text-indigo-600">Property #{{ $fav->property_id }}</span>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600">
                                    {{ $fav->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="#" class="text-xs font-bold text-slate-600 hover:text-slate-900 border border-slate-200 px-3 py-1 rounded bg-white hover:bg-slate-50">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-8 text-center text-slate-500 text-sm">
                        No recent favorites found.
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs text-slate-500 pt-4">
            <div class="bg-slate-100 rounded-lg p-3">
                <span class="block text-slate-400 mb-1">Registered</span>
                <span class="font-mono text-slate-700">{{ $user->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="bg-slate-100 rounded-lg p-3">
                <span class="block text-slate-400 mb-1">Last Login</span>
                <span class="font-mono text-slate-700">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
            </div>
            <div class="bg-slate-100 rounded-lg p-3 col-span-2">
                <span class="block text-slate-400 mb-1">System UUID</span>
                <span class="font-mono text-slate-700">{{ $user->id }}</span>
            </div>
        </div>

    </div>
</div>

@endsection
