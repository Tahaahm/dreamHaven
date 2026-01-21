@extends('layouts.admin-layout')

@section('content')
<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 bg-gray-50/50">
    <div class="max-w-7xl mx-auto">

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Service Providers</h1>
                <p class="text-sm text-gray-500 mt-2">Manage partners, track subscriptions, and verify accounts.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.service-providers.create') }}"
                   class="flex items-center justify-center px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all duration-200">
                    <i class="fas fa-plus mr-2"></i> Add Provider
                </a>
            </div>
        </div>

        {{-- Stats Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow duration-300">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Providers</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ \App\Models\ServiceProvider::count() }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow duration-300">
                <div class="w-14 h-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fas fa-certificate"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Verified Active</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ \App\Models\ServiceProvider::where('is_verified', true)->count() }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow duration-300">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg Rating</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format(\App\Models\ServiceProvider::avg('average_rating'), 1) }}</p>
                </div>
            </div>
        </div>

        {{-- Filters & Content --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

            {{-- Toolbar --}}
            <div class="p-5 border-b border-gray-100 bg-gray-50/30 flex flex-col md:flex-row gap-4">
                <form action="{{ route('admin.service-providers.index') }}" method="GET" class="flex-1 flex flex-col md:flex-row gap-3">
                    <div class="relative flex-1 group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by name, email or phone..."
                               class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all shadow-sm">
                    </div>

                    <div class="w-full md:w-48 relative">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-filter text-gray-400 text-xs"></i>
                        </div>
                        <select name="verified" onchange="this.form.submit()"
                                class="w-full pl-9 pr-8 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all shadow-sm appearance-none cursor-pointer">
                            <option value="">All Status</option>
                            <option value="1" {{ request('verified') == '1' ? 'selected' : '' }}>Verified Only</option>
                            <option value="0" {{ request('verified') == '0' ? 'selected' : '' }}>Unverified</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>

                    @if(request('search') || request('verified'))
                        <a href="{{ route('admin.service-providers.index') }}"
                           class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-bold transition-colors flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i> Clear
                        </a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Provider Details</th>
                            <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Plan Status</th>
                            <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Verified</th>
                            <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Rating</th>
                            <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($providers as $provider)
                        <tr class="hover:bg-gray-50/80 transition-colors group">

                            {{-- Provider Info --}}
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="relative shrink-0">
                                        @if($provider->profile_image)
                                            <img src="{{ $provider->profile_image }}" class="w-12 h-12 rounded-xl object-cover border border-gray-100 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center font-bold text-lg shadow-sm">
                                                {{ substr($provider->company_name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                            {{ $provider->company_name }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500">
                                            <span class="truncate max-w-[120px]" title="{{ $provider->email_address }}">{{ $provider->email_address }}</span>
                                            <span class="text-gray-300">&bull;</span>
                                            <span>{{ $provider->phone_number }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Category --}}
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $provider->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>

                            {{-- Subscription --}}
                            <td class="px-6 py-5">
                                @if($provider->hasActivePlan())
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-indigo-700">{{ $provider->plan->name }}</span>
                                            @if($provider->remainingPlanDays() < 7)
                                                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse" title="Expiring soon"></span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-gray-400 font-medium">
                                            {{ $provider->remainingPlanDays() }} days left
                                        </span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-50 text-gray-400 border border-gray-200">
                                        No Active Plan
                                    </span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-5 text-center">
                                @if($provider->is_verified)
                                    <form action="{{ route('admin.service-providers.verify', $provider->id) }}" method="POST">
                                        @csrf
                                        <button disabled class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200 cursor-default">
                                            <i class="fas fa-check-circle text-[10px]"></i> Verified
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.service-providers.verify', $provider->id) }}" method="POST" onsubmit="return confirm('Mark this provider as verified?');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-50 text-gray-500 border border-gray-200 hover:bg-gray-100 hover:text-gray-700 transition cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Pending
                                        </button>
                                    </form>
                                @endif
                            </td>

                            {{-- Rating --}}
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex items-center gap-1 bg-amber-50 px-2 py-1 rounded-lg border border-amber-100">
                                    <span class="text-sm font-bold text-amber-600">{{ number_format($provider->average_rating, 1) }}</span>
                                    <i class="fas fa-star text-[10px] text-amber-500"></i>
                                </div>
                                <div class="text-[10px] text-gray-400 font-medium mt-1">{{ $provider->reviews->count() }} reviews</div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200 translate-x-2 group-hover:translate-x-0">
                                    <a href="{{ route('admin.service-providers.show', $provider->id) }}"
                                       class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 border border-transparent hover:border-indigo-100 transition-all"
                                       title="View Details">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>

                                    <a href="{{ route('admin.service-providers.edit', $provider->id) }}"
                                       class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-blue-600 hover:bg-blue-50 border border-transparent hover:border-blue-100 transition-all"
                                       title="Edit Profile">
                                        <i class="fas fa-pen text-sm"></i>
                                    </a>

                                    <form action="{{ route('admin.service-providers.delete', $provider->id) }}" method="POST" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 border border-transparent hover:border-red-100 transition-all"
                                                title="Delete Provider">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                        <i class="fas fa-search text-3xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-gray-900 font-bold text-lg">No providers found</h3>
                                    <p class="text-gray-500 text-sm mt-1 mb-6 max-w-sm mx-auto">We couldn't find any service providers matching your search. Try adjusting filters.</p>
                                    <a href="{{ route('admin.service-providers.create') }}" class="text-indigo-600 hover:text-indigo-700 font-bold text-sm hover:underline">
                                        + Create New Provider
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($providers->hasPages())
            <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/30">
                {{ $providers->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
