@extends('layouts.admin-layout')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.service-provider-plans.index') }}"
               class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create New Plan</h1>
                <p class="text-sm text-gray-500">Set up a new subscription plan for service providers</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.service-provider-plans.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-50 to-indigo-100/50 px-6 py-4 border-b border-indigo-100">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-600"></i>
                    Basic Information
                </h3>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Plan Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                           placeholder="e.g. Gold Tier, Premium Plus">
                    <p class="text-xs text-gray-500 mt-1.5">Choose a memorable name that reflects the plan's value</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Monthly Price ($) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">$</span>
                            <input type="number" step="0.01" name="monthly_price" required
                                   class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                   placeholder="99.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Annual Price ($) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">$</span>
                            <input type="number" step="0.01" name="annual_price" required
                                   class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                   placeholder="999.00">
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">Offer annual discount to encourage longer commitments</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-amber-50 to-amber-100/50 px-6 py-4 border-b border-amber-100">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-crown text-amber-600"></i>
                    Plan Benefits
                </h3>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Advertisement Slots
                        </label>
                        <div class="relative">
                            <i class="fas fa-rectangle-ad absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="number" name="advertisement_slots" value="0" min="0"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                   placeholder="5">
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">Number of ads the provider can post</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Featured Placement Days
                        </label>
                        <div class="relative">
                            <i class="fas fa-calendar-star absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="number" name="featured_placement_days" value="0" min="0"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                   placeholder="30">
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">Days their listing appears in featured section</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Plan Features
                    </label>
                    <textarea name="features" rows="8"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition font-mono text-sm"
                              placeholder="Enter each feature on a new line. For example:&#10;✓ Verified Provider Badge&#10;✓ Advanced Analytics Dashboard&#10;✓ Priority Customer Support&#10;✓ Custom Profile Design&#10;✓ Lead Generation Tools"></textarea>
                    <p class="text-xs text-gray-500 mt-1.5">
                        <i class="fas fa-lightbulb text-amber-500 mr-1"></i>
                        Tip: Start each feature with an emoji or symbol for better visual appeal
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-50 to-purple-100/50 px-6 py-4 border-b border-purple-100">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-purple-600"></i>
                    Plan Settings
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/50 transition cursor-pointer group">
                    <input type="checkbox" name="active" value="1" checked
                           class="mt-0.5 w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div class="flex-1">
                        <span class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition">Plan Active</span>
                        <p class="text-xs text-gray-500 mt-0.5">Enable this plan for service providers to subscribe</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-gray-200 hover:border-amber-300 hover:bg-amber-50/50 transition cursor-pointer group">
                    <input type="checkbox" name="most_popular" value="1"
                           class="mt-0.5 w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                    <div class="flex-1">
                        <span class="text-sm font-semibold text-gray-900 group-hover:text-amber-600 transition">
                            <i class="fas fa-star text-amber-500 mr-1"></i>Most Popular Badge
                        </span>
                        <p class="text-xs text-gray-500 mt-0.5">Highlight this plan with a special badge to increase visibility</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.service-provider-plans.index') }}"
               class="flex-1 inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3.5 rounded-xl text-sm font-semibold transition-all">
                <i class="fas fa-times"></i>
                <span>Cancel</span>
            </a>
            <button type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-6 py-3.5 rounded-xl text-sm font-semibold transition-all shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40 hover:-translate-y-0.5">
                <i class="fas fa-check"></i>
                <span>Create Plan</span>
            </button>
        </div>
    </form>
</div>
@endsection
