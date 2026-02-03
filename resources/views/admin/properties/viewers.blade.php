@extends('layouts.admin-layout')

@section('title', 'Viewer Intelligence')

@section('content')

<div class="max-w-[1600px] mx-auto animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- 1. Header & Navigation --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 mb-2">
                <a href="{{ route('admin.properties.index') }}" class="hover:text-[#303b97] transition">Properties</a>
                <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                <a href="{{ route('admin.properties.show', $property->id) }}" class="hover:text-[#303b97] transition">Details</a>
                <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-gray-800">Viewers</span>
            </div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Viewer Analytics</h1>
            <p class="text-gray-500 font-medium mt-1">
                Tracking engagement for <span class="text-[#303b97] font-bold">"{{ $property->name['en'] ?? 'Property #' . $property->id }}"</span>
            </p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.properties.show', $property->id) }}"
               class="flex items-center gap-2 bg-white text-gray-700 border border-gray-200 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-50 hover:border-gray-300 transition shadow-sm">
                <i class="fas fa-arrow-left"></i> Back to Property
            </a>
        </div>
    </div>

    {{-- 2. High-Impact Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

        {{-- Card 1: Total Views (Primary) --}}
<div class="relative overflow-hidden bg-gradient-to-br from-[#303b97] to-[#4b56b2] rounded-2xl p-6 shadow-lg shadow-indigo-200 text-white group">
    {{-- ... background effects ... --}}
    <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
            <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                <i class="fas fa-eye text-xl"></i>
            </div>
            <span class="text-xs font-medium bg-white/20 px-2 py-1 rounded-md backdrop-blur-sm">All Time</span>
        </div>

        {{-- ✅ FIX: Use the max value between the 'views' column and the actual interaction count --}}
        <p class="text-3xl font-black mb-1">
            {{ number_format(max($property->views ?? 0, $property->interactions()->count())) }}
        </p>

        <p class="text-indigo-100 text-sm font-medium">Total Page Impressions</p>
    </div>
</div>

        {{-- Card 2: Unique Authenticated --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
            </div>
            {{-- Calculate unique users based on the interactions relationship --}}
            <p class="text-3xl font-black text-gray-900 mb-1">
                {{ $property->interactions()->where('interaction_type', 'impression')->distinct('user_id')->count('user_id') }}
            </p>
            <p class="text-gray-500 text-sm font-medium">Unique Registered Users</p>
        </div>

        {{-- Card 3: Today's Activity --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                @if($property->interactions()->where('interaction_type', 'impression')->whereDate('created_at', today())->count() > 0)
                <span class="flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                @endif
            </div>
            <p class="text-3xl font-black text-gray-900 mb-1">
                {{ $property->interactions()->where('interaction_type', 'impression')->whereDate('created_at', today())->count() }}
            </p>
            <p class="text-gray-500 text-sm font-medium">Views Today</p>
        </div>

        {{-- Card 4: This Week --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                    <i class="fas fa-calendar-week text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-gray-900 mb-1">
                {{ $property->interactions()->where('interaction_type', 'impression')->where('created_at', '>=', now()->startOfWeek())->count() }}
            </p>
            <p class="text-gray-500 text-sm font-medium">Views This Week</p>
        </div>
    </div>

    {{-- 3. The Data Table --}}
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">

        {{-- Table Header --}}
        <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Registered Viewer Log</h3>
                <p class="text-sm text-gray-500 mt-1">Detailed list of authenticated users who accessed this property details page.</p>
            </div>
            <div>
               <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                   Showing {{ $viewers->count() }} records
               </span>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">User Profile</th>
                        <th class="px-8 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Contact Details</th>
                        <th class="px-8 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Interest Level</th>
                        <th class="px-8 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-right">Last Interaction</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($viewers as $interaction)
                        @if($interaction->user)
                        <tr class="group hover:bg-indigo-50/30 transition-colors duration-200">

                            {{-- User Column --}}
                            <td class="px-8 py-5 align-top">
                                <div class="flex items-start gap-4">
                                    <div class="relative">
                                        @if($interaction->user->photo_image)
                                            <img src="{{ $interaction->user->photo_image }}" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm group-hover:border-indigo-100 transition">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 border-2 border-white shadow-sm flex items-center justify-center text-gray-500 font-bold text-sm">
                                                {{ strtoupper(substr($interaction->user->username ?? 'U', 0, 1)) }}
                                            </div>
                                        @endif

                                        @if($interaction->user->is_verified)
                                            <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5" title="Verified User">
                                                <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.users.show', $interaction->user->id) }}" class="text-sm font-bold text-gray-900 hover:text-[#303b97] transition block">
                                            {{ $interaction->user->username ?? 'Unknown User' }}
                                        </a>
                                        <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $interaction->user->is_verified ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $interaction->user->is_verified ? 'Verified Account' : 'Unverified' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact Column --}}
                            <td class="px-8 py-5 align-top">
                                <div class="flex flex-col gap-2">
                                    @if($interaction->user->email)
                                    <div class="flex items-center gap-2 group/link cursor-pointer" onclick="navigator.clipboard.writeText('{{ $interaction->user->email }}'); alert('Email copied!');">
                                        <div class="w-6 h-6 rounded-md bg-gray-50 flex items-center justify-center text-gray-400 group-hover/link:text-[#303b97] group-hover/link:bg-indigo-50 transition">
                                            <i class="fas fa-envelope text-xs"></i>
                                        </div>
                                        <span class="text-sm text-gray-600 font-medium group-hover/link:text-gray-900 transition">{{ $interaction->user->email }}</span>
                                    </div>
                                    @endif

                                    @if($interaction->user->phone)
                                    <div class="flex items-center gap-2 group/link">
                                        <div class="w-6 h-6 rounded-md bg-gray-50 flex items-center justify-center text-gray-400 group-hover/link:text-emerald-600 group-hover/link:bg-emerald-50 transition">
                                            <i class="fas fa-phone text-xs"></i>
                                        </div>
                                        <span class="text-sm text-gray-600 font-medium group-hover/link:text-gray-900 transition">{{ $interaction->user->phone }}</span>
                                    </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Engagement Column --}}
                            <td class="px-8 py-5 align-middle">
                                @php
                                    // Count how many times THIS specific user has viewed THIS property to show intent
                                    $viewCount = $property->interactions()->where('user_id', $interaction->user_id)->where('interaction_type', 'impression')->count();
                                    $percentage = min(($viewCount / 10) * 100, 100); // 10 views = 100% Interest
                                @endphp
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2 w-24 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-[#303b97] to-[#4b56b2] rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 whitespace-nowrap">{{ $viewCount }} Views</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    @if($viewCount > 5)
                                        <span class="text-emerald-600 font-bold">🔥 High Interest</span>
                                    @elseif($viewCount > 2)
                                        <span class="text-indigo-600 font-medium">Interested</span>
                                    @else
                                        Casual Viewer
                                    @endif
                                </p>
                            </td>

                            {{-- Time Column --}}
                            <td class="px-8 py-5 align-middle text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-sm font-bold text-gray-800">{{ $interaction->created_at->diffForHumans() }}</span>
                                    <span class="text-xs font-mono text-gray-400 mt-0.5">{{ $interaction->created_at->format('M d, Y • h:i A') }}</span>

                                    <a href="{{ route('admin.users.show', $interaction->user->id) }}" class="mt-2 text-xs font-semibold text-[#303b97] hover:underline opacity-0 group-hover:opacity-100 transition-opacity">
                                        View Full Profile &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-24 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-users-slash text-2xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">No Viewers Yet</h3>
                                    <p class="text-sm text-gray-500 mt-2 text-center leading-relaxed">
                                        This property has not been viewed by any <strong>logged-in</strong> users yet. Guest views are counted in the total stats but not listed here.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4. Footer & Pagination --}}
        <div class="bg-gray-50 px-8 py-5 border-t border-gray-200">
            {{ $viewers->links() }}
        </div>
    </div>

</div>

@endsection
