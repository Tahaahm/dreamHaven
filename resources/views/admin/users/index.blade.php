@extends('layouts.admin-layout')

@section('title', 'Users Directory')

@section('content')

<div class="max-w-7xl mx-auto animate-fade-in-up">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Users Directory</h1>
            <p class="text-slate-500 mt-2 text-sm font-medium">Manage user accounts, monitor activity, and configure access.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-lg shadow-sm hover:bg-slate-50 transition">
                <i class="fas fa-download mr-2"></i> Export
            </button>
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-lg shadow-lg hover:bg-slate-800 hover:shadow-xl transition transform active:scale-95 flex items-center gap-2">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 bg-white rounded-xl border border-slate-200 shadow-sm mb-8 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 overflow-hidden">

        <div class="p-6 hover:bg-slate-50/50 transition-colors group relative">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Users</p>
                    <p class="text-3xl font-black text-slate-900">{{ $users->total() ?? 0 }}</p>
                </div>
                <div class="p-2 bg-slate-100 rounded-lg text-slate-400 group-hover:text-slate-600 transition">
                    <i class="fas fa-users text-lg"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-emerald-600">
                <i class="fas fa-arrow-up mr-1"></i> <span>12% growth</span>
            </div>
        </div>

        <div class="p-6 hover:bg-slate-50/50 transition-colors group relative">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">New (7 Days)</p>
                    <p class="text-3xl font-black text-slate-900">12</p>
                </div>
                <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600 group-hover:text-emerald-700 transition">
                    <i class="fas fa-user-plus text-lg"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 45%"></div>
            </div>
        </div>

        <div class="p-6 hover:bg-slate-50/50 transition-colors group relative">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Active Agents</p>
                    <p class="text-3xl font-black text-slate-900">8</p>
                </div>
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600 group-hover:text-blue-700 transition">
                    <i class="fas fa-user-tie text-lg"></i>
                </div>
            </div>
             <p class="mt-4 text-xs text-slate-400 font-medium">Verified providers</p>
        </div>

        <div class="p-6 hover:bg-slate-50/50 transition-colors group relative">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">System Admins</p>
                    <p class="text-3xl font-black text-slate-900">3</p>
                </div>
                <div class="p-2 bg-slate-800 rounded-lg text-white transition">
                    <i class="fas fa-shield-alt text-lg"></i>
                </div>
            </div>
            <p class="mt-4 text-xs text-slate-400 font-medium">Full access granted</p>
        </div>

    </div>

    {{-- Filters & Search --}}
    <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row gap-3">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400"></i>
            </div>
            <form method="GET" action="{{ route('admin.users.index') }}">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="block w-full pl-10 pr-3 py-2.5 bg-slate-50 border-none rounded-lg text-sm font-semibold text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-slate-200 transition"
                       placeholder="Search users...">
            </form>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
            <select onchange="window.location.href=this.value" class="appearance-none bg-white border border-slate-200 text-slate-700 text-xs font-bold py-2.5 pl-4 pr-10 rounded-lg hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-200 cursor-pointer transition shadow-sm">
                <option value="{{ route('admin.users.index') }}">Role: All</option>
                <option value="{{ route('admin.users.index', array_merge(request()->all(), ['role' => 'user'])) }}" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                <option value="{{ route('admin.users.index', array_merge(request()->all(), ['role' => 'agent'])) }}" {{ request('role') == 'agent' ? 'selected' : '' }}>Agent</option>
            </select>

            <select onchange="window.location.href=this.value" class="appearance-none bg-white border border-slate-200 text-slate-700 text-xs font-bold py-2.5 pl-4 pr-10 rounded-lg hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-200 cursor-pointer transition shadow-sm">
                <option value="{{ route('admin.users.index') }}">Status: All</option>
                <option value="{{ route('admin.users.index', array_merge(request()->all(), ['status' => 'verified'])) }}" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="{{ route('admin.users.index', array_merge(request()->all(), ['status' => 'unverified'])) }}" {{ request('status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
            </select>

            @if(request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-red-50 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100 transition flex items-center gap-2 whitespace-nowrap">
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
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-wider">User Profile</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-wider text-center">Role</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase tracking-wider text-right">Joined Date</th>
                        <th class="px-6 py-4 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
    @forelse($users as $user)
    <tr class="hover:bg-slate-50 transition-colors group">

        {{-- User Identity --}}
        <td class="px-6 py-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md">
                    @if($user->photo_image)
                        <img src="{{ asset($user->photo_image) }}" class="w-full h-full object-cover rounded-lg">
                    @else
                        {{ strtoupper(substr($user->username, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <a href="{{ route('admin.users.show', $user->id) }}" class="text-sm font-bold text-slate-900 hover:text-blue-600 transition">{{ $user->username }}</a>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">ID: {{ $user->id }}</span>
                    </div>
                </div>
            </div>
        </td>

        {{-- Contact Details --}}
        <td class="px-6 py-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2 text-sm text-slate-600 font-medium">
                    <i class="far fa-envelope text-slate-300 w-4"></i> {{ $user->email }}
                </div>
                @if($user->phone)
                <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                    <i class="fas fa-phone text-slate-300 w-4"></i> {{ $user->phone }}
                </div>
                @endif
            </div>
        </td>

        {{-- Role --}}
        <td class="px-6 py-4 text-center">
            @php
                $roleStyles = match($user->role) {
                    'admin' => 'bg-slate-100 text-slate-800 border-slate-200',
                    'agent' => 'bg-blue-50 text-blue-700 border-blue-100',
                    default => 'bg-slate-50 text-slate-600 border-slate-100'
                };
            @endphp
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border uppercase tracking-wide {{ $roleStyles }}">
                {{ ucfirst($user->role) }}
            </span>
        </td>

        {{-- Status (UPDATED to match your DB image) --}}
        <td class="px-6 py-4 text-center">
            {{-- We check $user->is_verified == 1 --}}
            @if($user->is_verified)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Verified
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Pending
                </span>
            @endif

            {{-- Optional: Show Suspended badge if is_suspended is 1 --}}
            @if($user->is_suspended)
                <div class="mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">
                        Suspended
                    </span>
                </div>
            @endif
        </td>

        {{-- Joined Date --}}
        <td class="px-6 py-4 text-right">
            <span class="text-sm font-bold text-slate-500">{{ $user->created_at->format('M d, Y') }}</span>
            <span class="block text-xs text-slate-400 mt-0.5">{{ $user->created_at->format('h:i A') }}</span>
        </td>

        {{-- Actions Menu --}}
        <td class="px-6 py-4 text-right">
            <div class="relative group/menu">
                <button class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                    <i class="fas fa-ellipsis-v"></i>
                </button>

                <div class="hidden group-hover/menu:block absolute right-0 top-6 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-xl z-50 animate-in fade-in zoom-in-95 duration-100">
                    <div class="p-1">
                        <div class="px-3 py-2 border-b border-slate-100 mb-1">
                            <p class="text-xs font-bold text-slate-900">Manage User</p>
                        </div>
                        <a href="{{ route('admin.users.show', $user->id) }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-md transition">
                            <i class="fas fa-eye w-4"></i> View Details
                        </a>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-md transition">
                            <i class="fas fa-pen-to-square w-4"></i> Edit Profile
                        </a>

                        @if($user->role !== 'admin')
                        <div class="my-1 border-t border-slate-100"></div>
                        <form action="{{ route('admin.users.delete', $user->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure? This cannot be undone.')" class="w-full flex items-center gap-3 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-md transition text-left">
                                <i class="fas fa-trash-alt w-4"></i> Delete Account
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="px-6 py-16 text-center">
            <div class="max-w-xs mx-auto text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-slate-300 text-2xl"></i>
                </div>
                <h3 class="text-slate-900 font-bold mb-1">No users found</h3>
                <p class="text-slate-500 text-sm mb-4">No results match your search criteria.</p>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-900 hover:underline">
                    <i class="fas fa-sync-alt"></i> Clear Filters
                </a>
            </div>
        </td>
    </tr>
    @endforelse
</tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>

@endsection
