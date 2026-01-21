@extends('layouts.admin-layout')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Plan</h1>
            <p class="text-sm text-gray-500 mt-1">Update subscription details for <span class="font-semibold text-indigo-600">{{ $plan->name }}</span></p>
        </div>
        <a href="{{ route('admin.service-provider-plans.index') }}" class="text-gray-500 hover:text-gray-900 font-medium text-sm flex items-center gap-2 transition">
            <i class="fas fa-arrow-left"></i> Back to Plans
        </a>
    </div>

    <form action="{{ route('admin.service-provider-plans.update', $plan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 space-y-6">

                {{-- Plan Name --}}
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Plan Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                           class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-indigo-900 focus:border-indigo-900 transition-all font-medium placeholder-gray-400"
                           placeholder="e.g. Gold Tier">
                    @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Pricing --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Monthly Price ($) <span class="text-red-600">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">$</span>
                            <input type="number" step="0.01" name="monthly_price" value="{{ old('monthly_price', $plan->monthly_price) }}" required
                                   class="w-full pl-8 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-indigo-900 focus:border-indigo-900 transition-all font-mono font-medium"
                                   placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Annual Price ($) <span class="text-red-600">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">$</span>
                            <input type="number" step="0.01" name="annual_price" value="{{ old('annual_price', $plan->annual_price) }}" required
                                   class="w-full pl-8 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-indigo-900 focus:border-indigo-900 transition-all font-mono font-medium"
                                   placeholder="0.00">
                        </div>
                    </div>
                </div>

                {{-- Limits (Optional based on your model, assuming common fields) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Ad Slots</label>
                        <input type="number" name="advertisement_slots" value="{{ old('advertisement_slots', $plan->advertisement_slots ?? 0) }}"
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-indigo-900 focus:border-indigo-900 transition-all font-medium">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Featured Days</label>
                        <input type="number" name="featured_placement_days" value="{{ old('featured_placement_days', $plan->featured_placement_days ?? 0) }}"
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-indigo-900 focus:border-indigo-900 transition-all font-medium">
                    </div>
                </div>

                {{-- Features --}}
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-bold text-gray-900">Plan Features</label>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">One feature per line</span>
                    </div>
                    {{-- Note: We use implode to turn the JSON array back into a string for the textarea --}}
                    <textarea name="features" rows="6"
                              class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-indigo-900 focus:border-indigo-900 transition-all font-medium resize-none placeholder-gray-400"
                              placeholder="Verified Badge&#10;Analytics Dashboard&#10;Priority Support">{{ old('features', is_array($plan->features) ? implode("\n", $plan->features) : $plan->features) }}</textarea>
                </div>

                {{-- Toggles --}}
                <div class="flex flex-col sm:flex-row gap-6 pt-4 border-t border-gray-100">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="active" value="1" {{ $plan->active ? 'checked' : '' }} class="peer sr-only">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-indigo-600 transition">Active Status</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="most_popular" value="1" {{ $plan->most_popular ? 'checked' : '' }} class="peer sr-only">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-amber-600 transition">Most Popular Badge</span>
                    </label>
                </div>

            </div>

            {{-- Footer Actions --}}
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                <a href="{{ route('admin.service-provider-plans.index') }}" class="px-5 py-2.5 rounded-xl text-gray-700 font-bold hover:bg-gray-200 transition">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition hover:-translate-y-0.5">
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
