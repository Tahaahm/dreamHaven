@extends('layouts.admin-layout')

@section('title', 'Offices Directory')

@section('content')

<div class="max-w-7xl mx-auto animate-fade-in-up">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Offices Directory</h1>
            <p class="text-slate-500 mt-2 text-sm font-medium">Manage real estate agencies, verify status, and monitor listings.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(($pendingCount ?? 0) > 0)
            <a href="{{ route('admin.offices.index', ['status' => 'pending']) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-sm font-bold hover:bg-amber-100 transition shadow-sm">
                <span class="relative flex h-2.5 w-2.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                </span>
                {{ $pendingCount }} Pending Approvals
            </a>
            @endif
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 bg-white rounded-xl border border-slate-200 shadow-sm mb-8 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 overflow-hidden">

        <div class="p-6 hover:bg-slate-50/50 transition-colors group relative">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Offices</p>
                    <p class="text-3xl font-black text-slate-900">{{ number_format($stats['total'] ?? 0) }}</p>
                </div>
                <div class="p-2 bg-slate-100 rounded-lg text-slate-400 group-hover:text-slate-600 transition">
                    <i class="fas fa-building text-lg"></i>
                </div>
            </div>
             <p class="mt-4 text-xs text-slate-400 font-medium">Registered agencies</p>
        </div>

        <div class="p-6 hover:bg-slate-50/50 transition-colors group relative">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Verified</p>
                    <p class="text-3xl font-black text-slate-900">{{ number_format($stats['verified'] ?? 0) }}</p>
                </div>
                <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600 group-hover:text-emerald-700 transition">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                @php $percentage = ($stats['total'] > 0) ? ($stats['verified'] / $stats['total']) * 100 : 0; @endphp
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
            </div>
        </div>

        <div class="p-6 hover:bg-slate-50/50 transition-colors group relative">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pending Review</p>
                    <p class="text-3xl font-black text-slate-900">{{ number_format($stats['pending'] ?? 0) }}</p>
                </div>
                <div class="p-2 bg-amber-50 rounded-lg text-amber-600 group-hover:text-amber-700 transition">
                    <i class="fas fa-clock text-lg"></i>
                </div>
            </div>
             <p class="mt-4 text-xs text-amber-600 font-bold">Action required</p>
        </div>

        <div class="p-6 hover:bg-slate-50/50 transition-colors group relative">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Office Listings</p>
                    <p class="text-3xl font-black text-slate-900">{{ number_format($stats['total_properties'] ?? 0) }}</p>
                </div>
                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 group-hover:text-indigo-700 transition">
                    <i class="fas fa-home text-lg"></i>
                </div>
            </div>
            <p class="mt-4 text-xs text-slate-400 font-medium">Total active listings</p>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row gap-3">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400"></i>
            </div>
            <form method="GET" action="{{ route('admin.offices.index') }}">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="block w-full pl-10 pr-3 py-2.5 bg-slate-50 border-none rounded-lg text-sm font-semibold text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-slate-200 transition"
                       placeholder="Search offices by name or email...">
            </form>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
            <select onchange="window.location.href=this.value" class="appearance-none bg-white border border-slate-200 text-slate-700 text-xs font-bold py-2.5 pl-4 pr-10 rounded-lg hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-200 cursor-pointer transition shadow-sm">
                <option value="{{ route('admin.offices.index') }}">Status: All</option>
                <option value="{{ route('admin.offices.index', array_merge(request()->except('status'), ['status' => 'verified'])) }}" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="{{ route('admin.offices.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>

            @if(request()->hasAny(['search', 'status', 'city']))
                <a href="{{ route('admin.offices.index') }}" class="px-4 py-2.5 bg-red-50 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100 transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-wider">Office Profile</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-wider">Contact Info</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-wider text-center">Listings</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($offices as $office)
                    <tr class="hover:bg-slate-50 transition-colors group">

                        {{-- Office Identity --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-sm">
                                    @if($office->logo)
                                        <img src="{{ asset($office->logo) }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-sm font-black text-slate-400"><i class="fas fa-building text-lg"></i></span>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('admin.offices.show', $office->id) }}" class="text-sm font-bold text-slate-900 hover:text-indigo-600 transition block mb-0.5">{{ $office->company_name }}</a>
                                    <span class="text-[10px] font-bold uppercase text-slate-500 flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt text-slate-300"></i> {{ $office->city ?? 'Location N/A' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Contact Info --}}
                        <td class="px-6 py-4">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                    <div class="w-5 h-5 rounded bg-slate-100 flex items-center justify-center text-slate-400 shrink-0"><i class="far fa-envelope text-[10px]"></i></div>
                                    <span class="truncate max-w-[180px]">{{ $office->email_address }}</span>
                                </div>
                                @if($office->phone_number)
                                <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                                    <div class="w-5 h-5 rounded bg-slate-100 flex items-center justify-center text-slate-400 shrink-0"><i class="fas fa-phone text-[10px]"></i></div>
                                    <span>{{ $office->phone_number }}</span>
                                </div>
                                @endif
                            </div>
                        </td>

                        {{-- Listings Count --}}
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black bg-slate-100 text-slate-700">
                                {{ $office->owned_properties_count ?? 0 }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            @if($office->is_verified)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Verified
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Pending
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right">
                            <div class="relative group/menu">
                                <button class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>

                                <div class="hidden group-hover/menu:block absolute right-0 top-6 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-xl z-50 animate-in fade-in zoom-in-95 duration-100">
                                    <div class="p-1">
                                        <div class="px-3 py-2 border-b border-slate-100 mb-1">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Actions</p>
                                        </div>

                                        <a href="{{ route('admin.offices.show', $office->id) }}" class="flex items-center gap-3 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600 rounded-md transition">
                                            <i class="fas fa-eye w-4 text-center"></i> View Profile
                                        </a>
                                        <a href="{{ route('admin.offices.edit', $office->id) }}" class="flex items-center gap-3 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600 rounded-md transition">
                                            <i class="fas fa-pen-to-square w-4 text-center"></i> Edit Office
                                        </a>

                                        @if(!$office->is_verified)
                                        <form action="{{ route('admin.offices.verify', $office->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50 rounded-md transition text-left">
                                                <i class="fas fa-check-circle w-4 text-center"></i> Verify
                                            </button>
                                        </form>
                                        @endif

                                        <div class="my-1 border-t border-slate-100"></div>

                                        <form action="{{ route('admin.offices.delete', $office->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Permanently delete this office?')" class="w-full flex items-center gap-3 px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-md transition text-left">
                                                <i class="fas fa-trash-alt w-4 text-center"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <p class="font-medium">No real estate offices found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $offices->withQueryString()->links() }}
        </div>
    </div>
</div>

@endsection
