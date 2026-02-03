@extends('layouts.admin-layout')

@section('title', 'Property Viewers - ' . ($property->name['en'] ?? 'Property'))

@section('content')

<div class="max-w-[1400px] mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Property Viewers</h1>
                <p class="text-gray-600 mt-1">{{ $property->name['en'] ?? 'Property' }}</p>
            </div>
            <a href="{{ route('admin.properties.show', $property->id) }}"
               class="bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Property
            </a>
        </div>
    </div>

    {{-- Stats Bar --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Views</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($property->views ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-eye text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Unique Viewers</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $property->interactions()->where('interaction_type', 'view')->distinct('user_id')->count('user_id') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Today</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $property->interactions()->where('interaction_type', 'view')->whereDate('created_at', today())->count() }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-day text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">This Week</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $property->interactions()->where('interaction_type', 'view')->where('created_at', '>=', now()->startOfWeek())->count() }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-week text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Viewers Table --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-600 uppercase">Viewer</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-600 uppercase">Contact</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-600 uppercase">Viewed At</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-600 uppercase text-center">View Count</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-600 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($viewers as $interaction)
                        @if($interaction->user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold text-lg">
                                        {{ strtoupper(substr($interaction->user->username ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $interaction->user->username ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">
                                            @if($interaction->user->is_verified)
                                                <i class="fas fa-check-circle text-green-500 mr-1"></i> Verified
                                            @else
                                                <i class="fas fa-circle text-gray-300 mr-1"></i> Unverified
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @if($interaction->user->email)
                                    <p class="text-sm text-gray-700">
                                        <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                        {{ $interaction->user->email }}
                                    </p>
                                    @endif
                                    @if($interaction->user->phone)
                                    <p class="text-sm text-gray-700">
                                        <i class="fas fa-phone text-gray-400 mr-2"></i>
                                        {{ $interaction->user->phone }}
                                    </p>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900 font-medium">{{ $interaction->created_at->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $interaction->created_at->format('h:i A') }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $interaction->created_at->diffForHumans() }}</p>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-indigo-50 text-indigo-700">
                                    {{ $property->interactions()->where('user_id', $interaction->user_id)->where('interaction_type', 'view')->count() }}x
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $interaction->user->id) }}"
                                       class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-semibold hover:bg-indigo-100 transition">
                                        <i class="fas fa-user mr-1"></i> View Profile
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="max-w-sm mx-auto">
                                    <i class="fas fa-eye-slash text-4xl text-gray-300 mb-3"></i>
                                    <h3 class="text-gray-900 font-bold mb-1">No Viewers Yet</h3>
                                    <p class="text-gray-500 text-sm">This property hasn't been viewed by any registered users.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
            {{ $viewers->links() }}
        </div>
    </div>

</div>

@endsection
