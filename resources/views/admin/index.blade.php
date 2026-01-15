@extends('layouts.admin-layout')

@section('title', 'Users Management')

@section('content')

<!-- Page Header -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Users Management</h1>
        <p class="text-sm text-gray-600 mt-1">Manage all platform users</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="gradient-primary text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition inline-flex items-center justify-center">
        <i class="fas fa-plus mr-2"></i>Add New User
    </a>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-blue-50 rounded-lg">
                <i class="fas fa-users text-blue-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Users</p>
                <p class="text-xl font-bold text-gray-900">{{ $users->total() }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-green-50 rounded-lg">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Active Users</p>
                <p class="text-xl font-bold text-gray-900">{{ \App\Models\User::where('is_active', true)->count() }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-purple-50 rounded-lg">
                <i class="fas fa-user-check text-purple-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Verified</p>
                <p class="text-xl font-bold text-gray-900">{{ \App\Models\User::whereNotNull('email_verified_at')->count() }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-orange-50 rounded-lg">
                <i class="fas fa-clock text-orange-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">This Month</p>
                <p class="text-xl font-bold text-gray-900">{{ \App\Models\User::whereMonth('created_at', now()->month)->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl p-6 mb-6 border border-gray-100">
    <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="all">All Roles</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                <option value="agent" {{ request('role') == 'agent' ? 'selected' : '' }}>Agent</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select name="sort_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="created_at">Newest First</option>
                <option value="username">Name A-Z</option>
                <option value="email">Email A-Z</option>
            </select>
        </div>
        <div class="flex items-end space-x-2">
            <button type="submit" class="flex-1 gradient-primary text-white px-4 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-redo"></i>
            </a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 gradient-primary rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                {{ substr($user->username ?? $user->email, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $user->username ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">#{{ $user->id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="text-sm text-gray-900">{{ $user->email }}</p>
                        @if($user->email_verified_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Verified
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i>Unverified
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $user->phone ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $user->role == 'admin' ? 'bg-purple-100 text-purple-800' :
                               ($user->role == 'agent' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($user->role ?? 'user') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->is_active)
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Active</span>
                        @else
                        <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">Suspended</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $user->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-900" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.users.suspend', $user->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-orange-600 hover:text-orange-900" title="{{ $user->is_active ? 'Suspend' : 'Activate' }}">
                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check-circle' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 font-semibold">No users found</p>
                            <p class="text-sm text-gray-400 mt-1">Try adjusting your filters or add a new user</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection
