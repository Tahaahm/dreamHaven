@extends('layouts.admin-layout')

@section('content')
<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 bg-gray-50/50">
    <div class="max-w-7xl mx-auto">

        {{-- Header & Search --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Service Categories</h1>
                <p class="text-sm text-gray-500 mt-2">Manage and organize your service classifications.</p>
            </div>

            <div class="flex items-center gap-4">
                {{-- Search Bar --}}
                <form action="{{ route('admin.categories.index') }}" method="GET" class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search categories..."
                           class="pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm w-full md:w-64 transition-all">
                </form>

                {{-- Add Button --}}
                <a href="{{ route('admin.categories.create') }}"
                   class="flex items-center justify-center px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all duration-200">
                    <i class="fas fa-plus mr-2"></i> New Category
                </a>
            </div>
        </div>

        {{-- Content Card --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Sort</th>
                            <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Category Info</th>
                            <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Subtitle</th>
                            <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Providers</th>
                            <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($categories as $category)
                        <tr class="hover:bg-gray-50/80 transition-colors group">

                            {{-- Sort Order --}}
                            <td class="px-8 py-5">
                                <span class="font-mono text-xs font-medium text-gray-400 bg-gray-100 px-2 py-1 rounded-md border border-gray-200">
                                    {{ str_pad($category->sort_order, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            {{-- Category Name & Image --}}
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="relative shrink-0">
                                        @if($category->image)
                                            <img src="{{ $category->image }}" class="w-12 h-12 rounded-xl object-cover border border-gray-100 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center shadow-sm">
                                                <span class="text-lg font-bold">{{ substr($category->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                            {{ $category->name }}
                                        </h3>
                                        <p class="text-xs text-gray-400">ID: {{ $category->id }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Subtitle --}}
                            <td class="px-6 py-5">
                                <span class="text-sm text-gray-600 block max-w-xs truncate">
                                    {{ $category->subtitle ?? '—' }}
                                </span>
                            </td>

                            {{-- Status Toggle --}}
                            <td class="px-6 py-5 text-center">
                                <form action="{{ route('admin.categories.toggle', $category->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border transition-all duration-200 cursor-pointer hover:shadow-sm
                                            {{ $category->is_active
                                                ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                                                : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'
                                            }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $category->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                        {{ $category->is_active ? 'Active' : 'Hidden' }}
                                    </button>
                                </form>
                            </td>

                            {{-- Provider Count --}}
                            <td class="px-6 py-5 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    <i class="fas fa-users mr-1.5 opacity-70"></i>
                                    {{ $category->serviceProviders->count() }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}"
                                       class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 border border-transparent hover:border-indigo-100 transition-all"
                                       title="Edit">
                                        <i class="fas fa-pen text-sm"></i>
                                    </a>

                                    <form action="{{ route('admin.categories.delete', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure? This category cannot have any attached providers.');">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 hover:text-red-600 hover:bg-red-50 border border-transparent hover:border-red-100 transition-all"
                                                title="Delete">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                        <i class="fas fa-layer-group text-2xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-gray-900 font-bold text-lg">No categories found</h3>
                                    <p class="text-gray-500 text-sm mt-1 mb-6">Start by creating your first service category.</p>
                                    <a href="{{ route('admin.categories.create') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm hover:underline">
                                        + Create Category
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($categories->hasPages())
            <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/30">
                {{ $categories->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
