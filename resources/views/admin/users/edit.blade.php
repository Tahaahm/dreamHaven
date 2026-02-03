@extends('layouts.admin-layout')

@section('title', $user->username)

@section('content')

{{-- Header Section with Breadcrumb --}}
<div class="relative mb-8">
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-slate-500 hover:text-indigo-600 font-medium transition inline-flex items-center gap-2">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-slate-300 mx-2 text-xs"></i>
                    <a href="{{ route('admin.users.index') }}" class="text-slate-500 hover:text-indigo-600 font-medium transition">Users</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-slate-300 mx-2 text-xs"></i>
                    <span class="text-slate-900 font-semibold">{{ $user->username }}</span>
                </div>
            </li>
        </ol>
    </nav>

    {{-- Profile Header --}}
    <div class="bg-gradient-to-br from-[#303b97] to-[#1e2563] rounded-2xl shadow-2xl p-8 relative overflow-hidden">
        {{-- Decorative Elements --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                {{-- Profile Image --}}
                <div class="relative group cursor-pointer" onclick="openImageUploadModal()">
                    <div class="w-28 h-28 rounded-3xl overflow-hidden ring-4 ring-white shadow-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center transition-all duration-300 group-hover:scale-105 group-hover:shadow-[0_20px_60px_-15px_rgba(48,59,151,0.5)]">
                        @if($user->photo_image)
                            <img src="{{ asset($user->photo_image) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl font-bold text-[#303b97]">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm rounded-3xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                        <div class="text-center">
                            <i class="fas fa-camera text-white text-2xl mb-1"></i>
                            <p class="text-white text-xs font-semibold">Change Photo</p>
                        </div>
                    </div>
                    @if($user->is_verified)
                    <div class="absolute -bottom-2 -right-2 bg-white p-2 rounded-full shadow-lg animate-bounce">
                        <div class="bg-[#303b97] text-white w-7 h-7 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-sm"></i>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- User Info --}}
                <div class="text-white">
                    <h1 class="text-4xl font-black tracking-tight leading-tight mb-2 drop-shadow-lg">{{ $user->username }}</h1>
                    <div class="flex flex-wrap items-center gap-3">
    <span class="flex items-center text-sm font-medium text-white/90 bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full">
        <i class="far fa-envelope mr-2"></i> {{ $user->email }}
    </span>
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide bg-white/30 backdrop-blur-sm text-white border border-white/40">
        <i class="fas fa-user-tag"></i> {{ $user->role }}
    </span>
    @if($user->is_verified)
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide bg-emerald-400/90 text-white border-2 border-emerald-300">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span> Verified
        </span>
    @else
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide bg-red-400/90 text-white border-2 border-red-300">
            <span class="w-2 h-2 rounded-full bg-white"></span> Unverified
        </span>
    @endif
</div>
                    <div class="flex items-center gap-2 mt-3 text-white/80 text-sm">
                        <i class="far fa-calendar-alt"></i>
                        <span>Joined {{ $user->created_at->format('F d, Y') }}</span>
                        @if($user->last_login_at)
                            <span class="text-white/60">•</span>
                            <span>Last seen {{ $user->last_login_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
    <button onclick="openEditModal()" class="px-6 py-3 bg-white hover:bg-gray-50 text-[#303b97] text-sm font-bold rounded-xl shadow-xl transition-all flex items-center justify-center gap-2 hover:scale-105 active:scale-95">
        <i class="fas fa-pen-to-square"></i> Edit Profile
    </button>

    @if($user->is_verified)
        <form action="{{ route('admin.users.suspend', $user->id) }}" method="POST" class="inline-block">
            @csrf
            <button type="submit" onclick="return confirm('Are you sure you want to suspend this user? This will unverify their account.')" class="w-full px-6 py-3 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl shadow-xl transition-all flex items-center justify-center gap-2 hover:scale-105 active:scale-95">
                <i class="fas fa-ban"></i> Suspend User
            </button>
        </form>
    @else
        <form action="{{ route('admin.users.activate', $user->id) }}" method="POST" class="inline-block">
            @csrf
            <button type="submit" class="w-full px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-xl shadow-xl transition-all flex items-center justify-center gap-2 hover:scale-105 active:scale-95">
                <i class="fas fa-check-circle"></i> Verify & Activate User
            </button>
        </form>
    @endif
</div>
        </div>
    </div>
</div>

{{-- Main Content Grid --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

    {{-- Left Sidebar --}}
    <div class="xl:col-span-4 space-y-6">

        {{-- Quick Stats Card --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 hover:shadow-2xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-black text-slate-900">Quick Stats</h3>
                <div class="w-10 h-10 rounded-xl bg-[#303b97] flex items-center justify-center">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-4 border border-slate-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-home text-[#303b97]"></i>
                        <span class="text-xs font-bold text-slate-500 uppercase">Properties</span>
                    </div>
                    <p class="text-3xl font-black text-[#303b97]">{{ $user->ownedProperties->count() }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-4 border border-slate-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-calendar-check text-[#303b97]"></i>
                        <span class="text-xs font-bold text-slate-500 uppercase">Appointments</span>
                    </div>
                    <p class="text-3xl font-black text-[#303b97]">{{ $user->appointments->count() }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-4 border border-slate-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-heart text-[#303b97]"></i>
                        <span class="text-xs font-bold text-slate-500 uppercase">Favorites</span>
                    </div>
                    <p class="text-3xl font-black text-[#303b97]">{{ $user->favoriteProperties->count() }}</p>
                </div>
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-4 border border-slate-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-star text-[#303b97]"></i>
                        <span class="text-xs font-bold text-slate-500 uppercase">Reviews</span>
                    </div>
                    <p class="text-3xl font-black text-[#303b97]">0</p>
                </div>
            </div>
        </div>

        {{-- Contact Information --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 hover:shadow-2xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-black text-slate-900">Contact Information</h3>
                <div class="w-10 h-10 rounded-xl bg-[#303b97] flex items-center justify-center">
                    <i class="fas fa-address-card text-white"></i>
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 hover:bg-slate-100 transition-all duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-[#303b97] flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-phone text-white"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Phone Number</p>
                        <p class="text-sm font-bold text-slate-800 truncate">{{ $user->phone ?? 'Not provided' }}</p>
                        @if($user->phone)
                        <button onclick="copyToClipboard('{{ $user->phone }}')" class="text-xs text-[#303b97] hover:text-[#1e2563] font-semibold mt-1 inline-flex items-center gap-1">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                        @endif
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 hover:bg-slate-100 transition-all duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-[#303b97] flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-envelope text-white"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Email Address</p>
                        <p class="text-sm font-bold text-slate-800 truncate">{{ $user->email }}</p>
                        <button onclick="copyToClipboard('{{ $user->email }}')" class="text-xs text-[#303b97] hover:text-[#1e2563] font-semibold mt-1 inline-flex items-center gap-1">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 hover:bg-slate-100 transition-all duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-[#303b97] flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-globe text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Language</p>
                        <p class="text-sm font-bold text-slate-800">{{ strtoupper($user->language ?? 'EN') }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 hover:bg-slate-100 transition-all duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-[#303b97] flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-map-marker-alt text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Location</p>
                        <p class="text-sm font-bold text-slate-800">{{ $user->place ?? 'No location set' }}</p>
                        @if($user->lat && $user->lng)
                        <a href="https://maps.google.com/?q={{ $user->lat }},{{ $user->lng }}" target="_blank" class="text-xs text-[#303b97] hover:text-[#1e2563] font-semibold mt-1 inline-flex items-center gap-1">
                            <i class="fas fa-external-link-alt"></i> View on Map
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Account Information --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 hover:shadow-2xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-black text-slate-900">Account Information</h3>
                <div class="w-10 h-10 rounded-xl bg-[#303b97] flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white"></i>
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100">
                    <span class="text-sm font-bold text-slate-600">Member Since</span>
                    <span class="text-sm font-black text-slate-900">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100">
                    <span class="text-sm font-bold text-slate-600">Last Login</span>
                    <span class="text-sm font-black text-slate-900">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100">
                    <span class="text-sm font-bold text-slate-600">Email Verified</span>
                    @if($user->email_verified_at)
                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">
                            <i class="fas fa-check-circle"></i> Verified
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-amber-100 text-amber-700">
                            <i class="fas fa-exclamation-circle"></i> Unverified
                        </span>
                    @endif
                </div>
                <div class="flex justify-between items-center p-3 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100">
                    <span class="text-sm font-bold text-slate-600">Account Status</span>
                    @if($user->is_verified)
                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-[#303b97]/10 text-[#303b97]">
                            <i class="fas fa-badge-check"></i> Verified
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                            <i class="fas fa-user-clock"></i> Unverified
                        </span>
                    @endif
                </div>
                <div class="pt-4 border-t border-slate-200">
                    <p class="text-xs font-bold text-slate-400 uppercase mb-2">User UUID</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 text-xs bg-gradient-to-r from-slate-100 to-slate-50 px-3 py-2 rounded-lg text-slate-700 font-mono border border-slate-200">{{ $user->id }}</code>
                        <button onclick="copyToClipboard('{{ $user->id }}')" class="px-3 py-2 bg-[#303b97] hover:bg-[#1e2563] text-white rounded-lg transition-colors duration-300">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Right Content Area --}}
    <div class="xl:col-span-8 space-y-6">

        {{-- About Me Section --}}
        @if($user->about_me)
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl shadow-xl border border-slate-200 p-8 hover:shadow-2xl transition-all duration-300">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#303b97] flex items-center justify-center shrink-0 shadow-lg">
                    <i class="fas fa-quote-left text-white text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-black text-slate-900 mb-3">About Me</h3>
                    <p class="text-slate-700 leading-relaxed text-base">{{ $user->about_me }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Properties Overview --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden hover:shadow-2xl transition-shadow duration-300">
            <div class="bg-[#303b97] px-6 py-5 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <i class="fas fa-home text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white">Properties Portfolio</h3>
                        <p class="text-white/80 text-sm">{{ $user->ownedProperties->count() }} total properties</p>
                    </div>
                </div>
                <a href="{{ route('admin.properties.index') }}?owner={{ $user->id }}" class="px-4 py-2 bg-white hover:bg-slate-100 text-[#303b97] rounded-xl font-bold text-sm transition-all hover:scale-105">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            @if($user->ownedProperties->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
                    @foreach($user->ownedProperties->take(4) as $property)
                    <div class="group bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-4 hover:shadow-lg transition-all duration-300 border border-slate-200 hover:border-[#303b97]">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-xl bg-[#303b97] flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-building text-white text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-slate-900 truncate mb-1">{{ $property->name['en'] ?? 'Property' }}</h4>
                                <div class="flex items-center gap-2 text-xs text-slate-600 mb-2">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white font-semibold">
                                        <i class="fas fa-tag"></i> {{ ucfirst($property->listing_type) }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white font-semibold">
                                        {{ $property->status }}
                                    </span>
                                </div>
                                <p class="text-sm font-black text-[#303b97]">${{ number_format($property->price['amount'] ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-home text-slate-400 text-3xl"></i>
                    </div>
                    <p class="text-slate-500 font-semibold">No properties found</p>
                </div>
            @endif
        </div>

        {{-- Appointments Table --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden hover:shadow-2xl transition-shadow duration-300">
            <div class="bg-[#303b97] px-6 py-5 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <i class="fas fa-calendar-check text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white">Recent Appointments</h3>
                        <p class="text-white/80 text-sm">Last 5 appointments</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b-2 border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-600 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-4 text-center text-xs font-black text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-black text-slate-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($user->appointments->take(5) as $appt)
                        <tr class="hover:bg-slate-50 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs bg-slate-100 px-3 py-1 rounded-lg text-slate-700 font-bold">
                                    #{{ substr($appt->id, 0, 8) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <i class="far fa-calendar text-slate-400"></i>
                                    <span class="text-sm font-semibold text-slate-700">
                                        {{ $appt->appointment_date ?? 'TBD' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusConfig = match($appt->status) {
                                        'completed' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'fa-check-circle'],
                                        'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => 'fa-times-circle'],
                                        'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'fa-clock'],
                                        default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'icon' => 'fa-circle']
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold uppercase {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    <i class="fas {{ $statusConfig['icon'] }}"></i>
                                    {{ $appt->status ?? 'Pending' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.appointments.show', $appt->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#303b97] hover:text-[#1e2563] hover:underline">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                                        <i class="fas fa-calendar-times text-slate-400 text-2xl"></i>
                                    </div>
                                    <p class="text-slate-500 font-semibold">No appointments found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Activity Timeline --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 hover:shadow-2xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-[#303b97] flex items-center justify-center">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900">Recent Activity</h3>
                        <p class="text-slate-500 text-sm">Latest user actions</p>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100 border border-slate-200">
                    <div class="w-10 h-10 rounded-full bg-[#303b97] flex items-center justify-center shrink-0">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-slate-900">Account Created</p>
                        <p class="text-xs text-slate-600 mt-1">{{ $user->created_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>
                </div>
                @if($user->last_login_at)
                <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100 border border-slate-200">
                    <div class="w-10 h-10 rounded-full bg-[#303b97] flex items-center justify-center shrink-0">
                        <i class="fas fa-sign-in-alt text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-slate-900">Last Login</p>
                        <p class="text-xs text-slate-600 mt-1">{{ $user->last_login_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>
                </div>
                @endif
                @if($user->email_verified_at)
                <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100 border border-slate-200">
                    <div class="w-10 h-10 rounded-full bg-[#303b97] flex items-center justify-center shrink-0">
                        <i class="fas fa-envelope-circle-check text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-slate-900">Email Verified</p>
                        <p class="text-xs text-slate-600 mt-1">{{ $user->email_verified_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Edit Profile Modal --}}
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto backdrop-blur-sm" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all w-full max-w-3xl border-2 border-slate-200">

            <div class="bg-[#303b97] px-6 py-5 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <i class="fas fa-user-edit text-white"></i>
                    </div>
                    <h3 class="text-xl font-black text-white">Edit User Profile</h3>
                </div>
                <button onclick="closeEditModal()" class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors duration-300">
                    <i class="fas fa-times text-white"></i>
                </button>
            </div>

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-600 uppercase tracking-wide flex items-center gap-2">
                            <i class="fas fa-user text-[#303b97]"></i> Username
                        </label>
                        <input type="text" name="username" value="{{ $user->username }}" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-[#303b97]/20 focus:border-[#303b97] outline-none transition-all font-semibold" required>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-600 uppercase tracking-wide flex items-center gap-2">
                            <i class="fas fa-envelope text-[#303b97]"></i> Email
                        </label>
                        <input type="email" name="email" value="{{ $user->email }}" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-[#303b97]/20 focus:border-[#303b97] outline-none transition-all font-semibold" required>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-600 uppercase tracking-wide flex items-center gap-2">
                            <i class="fas fa-phone text-[#303b97]"></i> Phone
                        </label>
                        <input type="text" name="phone" value="{{ $user->phone }}" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-[#303b97]/20 focus:border-[#303b97] outline-none transition-all font-semibold">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-600 uppercase tracking-wide flex items-center gap-2">
                            <i class="fas fa-user-tag text-[#303b97]"></i> Role
                        </label>
                        <select name="role" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-[#303b97]/20 focus:border-[#303b97] outline-none transition-all font-semibold">
                            <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                            <option value="agent" {{ $user->role == 'agent' ? 'selected' : '' }}>Agent</option>
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-600 uppercase tracking-wide flex items-center gap-2">
                            <i class="fas fa-globe text-[#303b97]"></i> Language
                        </label>
                        <select name="language" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-[#303b97]/20 focus:border-[#303b97] outline-none transition-all font-semibold">
                            <option value="en" {{ $user->language == 'en' ? 'selected' : '' }}>English</option>
                            <option value="ar" {{ $user->language == 'ar' ? 'selected' : '' }}>Arabic</option>
                            <option value="ku" {{ $user->language == 'ku' ? 'selected' : '' }}>Kurdish</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-600 uppercase tracking-wide flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-[#303b97]"></i> Location
                        </label>
                        <input type="text" name="place" value="{{ $user->place }}" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-[#303b97]/20 focus:border-[#303b97] outline-none transition-all font-semibold" placeholder="City, Country">
                    </div>

                    <div class="col-span-2 space-y-2">
                        <label class="text-xs font-black text-slate-600 uppercase tracking-wide flex items-center gap-2">
                            <i class="fas fa-comment-alt text-[#303b97]"></i> About Me
                        </label>
                        <textarea name="about_me" rows="4" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-[#303b97]/20 focus:border-[#303b97] outline-none transition-all font-medium resize-none" placeholder="Tell us about yourself...">{{ $user->about_me }}</textarea>
                    </div>

                    <div class="col-span-2 grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-600 uppercase tracking-wide flex items-center gap-2">
                                <i class="fas fa-map text-[#303b97]"></i> Latitude
                            </label>
                            <input type="number" step="any" name="lat" value="{{ $user->lat }}" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-[#303b97]/20 focus:border-[#303b97] outline-none transition-all font-semibold" placeholder="0.000000">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-600 uppercase tracking-wide flex items-center gap-2">
                                <i class="fas fa-map text-[#303b97]"></i> Longitude
                            </label>
                            <input type="number" step="any" name="lng" value="{{ $user->lng }}" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-[#303b97]/20 focus:border-[#303b97] outline-none transition-all font-semibold" placeholder="0.000000">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-6 py-3 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="px-8 py-3 text-sm font-bold text-white bg-[#303b97] hover:bg-[#1e2563] rounded-xl shadow-lg hover:shadow-xl transition-all hover:scale-105">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Image Upload Modal --}}
<div id="imageModal" class="hidden fixed inset-0 z-50 overflow-y-auto backdrop-blur-sm">
    <div class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all w-full max-w-md border-2 border-slate-200">

            <div class="bg-[#303b97] p-6 text-center">
                <div class="mx-auto w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-4">
                    <i class="fas fa-cloud-upload-alt text-white text-2xl"></i>
                </div>
                <h3 class="text-2xl font-black text-white mb-2">Update Profile Photo</h3>
                <p class="text-white/80 text-sm">Upload a new photo (JPG, PNG, GIF). Max 2MB.</p>
            </div>

            <form action="{{ route('admin.users.update-image', $user->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label class="block w-full cursor-pointer">
                        <div class="border-3 border-dashed border-slate-300 rounded-2xl p-8 text-center hover:border-[#303b97] hover:bg-[#303b97]/5 transition-all duration-300">
                            <i class="fas fa-image text-slate-400 text-4xl mb-3"></i>
                            <p class="text-sm font-bold text-slate-700 mb-1">Click to upload or drag and drop</p>
                            <p class="text-xs text-slate-500">JPG, PNG or GIF (MAX. 2MB)</p>
                        </div>
                        <input type="file" name="photo_image" accept="image/*" class="hidden" required onchange="previewImage(this)">
                    </label>

                    <div id="imagePreview" class="hidden mt-4">
                        <p class="text-xs font-bold text-slate-600 uppercase mb-2">Preview:</p>
                        <img id="preview" class="w-full h-48 object-cover rounded-xl border-2 border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="closeImageUploadModal()" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm font-bold text-slate-700 transition-all">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="px-4 py-3 bg-[#303b97] hover:bg-[#1e2563] rounded-xl text-sm font-bold text-white shadow-lg hover:shadow-xl transition-all hover:scale-105">
                        <i class="fas fa-upload mr-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal() {
        document.getElementById('editModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openImageUploadModal() {
        document.getElementById('imageModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImageUploadModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('imagePreview').classList.add('hidden');
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('imagePreview').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // You can add a toast notification here
            alert('Copied to clipboard!');
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }

    // Close modals when clicking outside
    document.getElementById('editModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });

    document.getElementById('imageModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeImageUploadModal();
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditModal();
            closeImageUploadModal();
        }
    });
</script>

@endsection
