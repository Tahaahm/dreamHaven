@extends('layouts.admin-layout')

@section('title', 'Projects Management')

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- Page Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage all real estate development projects</p>
            </div>
            <a href="{{ route('admin.projects.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Project
            </a>
        </div>
    </div>

    <div class="px-6 py-6 space-y-5">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
                <svg class="w-4 h-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                <svg class="w-4 h-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9v4a1 1 0 102 0V9a1 1 0 10-2 0zm1-4a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
                $statCards = [
                    ['label' => 'Total',         'value' => $stats['total'],             'color' => 'bg-slate-100 text-slate-700'],
                    ['label' => 'Active',         'value' => $stats['active'],            'color' => 'bg-green-100 text-green-700'],
                    ['label' => 'Construction',   'value' => $stats['under_construction'],'color' => 'bg-orange-100 text-orange-700'],
                    ['label' => 'Completed',      'value' => $stats['completed'],         'color' => 'bg-blue-100 text-blue-700'],
                    ['label' => 'Featured',       'value' => $stats['featured'],          'color' => 'bg-purple-100 text-purple-700'],
                    ['label' => 'Unpublished',    'value' => $stats['unpublished'],       'color' => 'bg-red-100 text-red-700'],
                ];
            @endphp
            @foreach($statCards as $card)
                <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex flex-col gap-1">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $card['label'] }}</span>
                    <span class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</span>
                    <span class="inline-block self-start text-xs font-medium px-2 py-0.5 rounded-full {{ $card['color'] }}">
                        {{ $stats['total'] > 0 ? round($card['value'] / $stats['total'] * 100) : 0 }}%
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Filters & Search --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" action="{{ route('admin.projects.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by project name..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                <div class="min-w-36">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="planning"           {{ request('status') == 'planning'           ? 'selected' : '' }}>Planning</option>
                        <option value="under_construction" {{ request('status') == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                        <option value="completed"          {{ request('status') == 'completed'          ? 'selected' : '' }}>Completed</option>
                        <option value="delivered"          {{ request('status') == 'delivered'          ? 'selected' : '' }}>Delivered</option>
                        <option value="on_hold"            {{ request('status') == 'on_hold'            ? 'selected' : '' }}>On Hold</option>
                        <option value="cancelled"          {{ request('status') == 'cancelled'          ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="min-w-36">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Sales Status</label>
                    <select name="sales_status" class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="pre_launch" {{ request('sales_status') == 'pre_launch' ? 'selected' : '' }}>Pre-Launch</option>
                        <option value="launched"   {{ request('sales_status') == 'launched'   ? 'selected' : '' }}>Launched</option>
                        <option value="selling"    {{ request('sales_status') == 'selling'    ? 'selected' : '' }}>Selling</option>
                        <option value="sold_out"   {{ request('sales_status') == 'sold_out'   ? 'selected' : '' }}>Sold Out</option>
                        <option value="suspended"  {{ request('sales_status') == 'suspended'  ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <div class="min-w-36">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                    <select name="type" class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        <option value="residential" {{ request('type') == 'residential' ? 'selected' : '' }}>Residential</option>
                        <option value="commercial"  {{ request('type') == 'commercial'  ? 'selected' : '' }}>Commercial</option>
                        <option value="mixed_use"   {{ request('type') == 'mixed_use'   ? 'selected' : '' }}>Mixed Use</option>
                        <option value="industrial"  {{ request('type') == 'industrial'  ? 'selected' : '' }}>Industrial</option>
                    </select>
                </div>

                <div class="min-w-32">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Featured</label>
                    <select name="featured" class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="1" {{ request('featured') == '1' ? 'selected' : '' }}>Featured Only</option>
                        <option value="0" {{ request('featured') == '0' ? 'selected' : '' }}>Not Featured</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Projects Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">
                    {{ $projects->total() }} project{{ $projects->total() != 1 ? 's' : '' }} found
                </span>
            </div>

            @if($projects->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">No projects found</p>
                    <p class="text-gray-400 text-sm mt-1">Try adjusting your filters or create a new project.</p>
                    <a href="{{ route('admin.projects.create') }}" class="mt-4 inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create first project
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                <th class="px-5 py-3 text-left">Project</th>
                                <th class="px-5 py-3 text-left">Type</th>
                                <th class="px-5 py-3 text-left">Status</th>
                                <th class="px-5 py-3 text-left">Sales</th>
                                <th class="px-5 py-3 text-left">Progress</th>
                                <th class="px-5 py-3 text-left">Units</th>
                                <th class="px-5 py-3 text-left">Stats</th>
                                <th class="px-5 py-3 text-left">Visibility</th>
                                <th class="px-5 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($projects as $project)
                                @php
                                    $name = is_array($project->name) ? ($project->name['en'] ?? 'Untitled') : $project->name;
                                    $statusColors = [
                                        'planning'           => 'bg-yellow-100 text-yellow-700',
                                        'under_construction' => 'bg-orange-100 text-orange-700',
                                        'completed'          => 'bg-green-100 text-green-700',
                                        'delivered'          => 'bg-blue-100 text-blue-700',
                                        'on_hold'            => 'bg-gray-100 text-gray-600',
                                        'cancelled'          => 'bg-red-100 text-red-700',
                                    ];
                                    $salesColors = [
                                        'pre_launch' => 'bg-purple-100 text-purple-700',
                                        'launched'   => 'bg-blue-100 text-blue-700',
                                        'selling'    => 'bg-green-100 text-green-700',
                                        'sold_out'   => 'bg-gray-100 text-gray-600',
                                        'suspended'  => 'bg-red-100 text-red-700',
                                    ];
                                    $statusClass = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-600';
                                    $salesClass  = $salesColors[$project->sales_status] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors group">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 bg-gradient-to-br from-indigo-100 to-blue-100 flex items-center justify-center">
                                                @if($project->cover_image_url)
                                                    <img src="{{ $project->cover_image_url }}" class="w-full h-full object-cover" alt="{{ $name }}">
                                                @else
                                                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-gray-900 truncate max-w-40">{{ $name }}</div>
                                                <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                                                    {{ $project->created_at->format('M d, Y') }}
                                                    @if($project->is_featured)
                                                        <span class="inline-flex items-center gap-0.5 text-amber-600 font-medium">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                            Featured
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="text-xs font-medium text-gray-600 capitalize">
                                            {{ str_replace('_', ' ', $project->project_type) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }} capitalize">
                                            {{ str_replace('_', ' ', $project->status) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $salesClass }} capitalize">
                                            {{ str_replace('_', ' ', $project->sales_status) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2 min-w-24">
                                            <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                                <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $project->completion_percentage }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 whitespace-nowrap">{{ $project->completion_percentage }}%</span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="text-xs">
                                            <span class="font-semibold text-gray-800">{{ $project->available_units ?? 0 }}</span>
                                            <span class="text-gray-400"> / {{ $project->total_units ?? '—' }}</span>
                                        </div>
                                        <div class="text-xs text-gray-400">available</div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3 text-xs text-gray-500">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                {{ number_format($project->views ?? 0) }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                                {{ number_format($project->favorites_count ?? 0) }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex items-center gap-1 text-xs {{ $project->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $project->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                                {{ $project->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 text-xs {{ $project->published ? 'text-blue-600' : 'text-gray-400' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $project->published ? 'bg-blue-500' : 'bg-gray-300' }}"></span>
                                                {{ $project->published ? 'Published' : 'Draft' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.projects.show', $project->id) }}"
                                               class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="View">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.projects.edit', $project->id) }}"
                                               class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('admin.projects.toggle.active', $project->id) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                                        title="{{ $project->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        @if($project->is_active)
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        @endif
                                                    </svg>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.projects.delete', $project->id) }}"
                                                  onsubmit="return confirm('Delete \'{{ addslashes($name) }}\' permanently?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($projects->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $projects->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</div>
@endsection