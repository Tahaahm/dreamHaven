@extends('layouts.admin-layout')

@section('title', 'Edit Plan')

@section('content')

@php
    $names = is_array($plan->name) ? $plan->name : ['en' => $plan->name];
    // Convert features array to string for textarea
    $featuresString = is_array($plan->features) ? implode("\n", $plan->features) : '';
@endphp

<div class="max-w-4xl mx-auto animate-in fade-in zoom-in-95 duration-500">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <nav class="flex text-sm text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.subscription-plans.index') }}" class="hover:text-slate-900 transition">Plans</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-bold">Edit</span>
            </nav>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Edit: {{ $names['en'] ?? 'Plan' }}</h1>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.subscription-plans.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition">Cancel</a>
            <button type="submit" form="editPlanForm" class="px-6 py-2.5 bg-black text-white font-bold rounded-xl shadow-lg hover:bg-slate-800 transition flex items-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    <form id="editPlanForm" method="POST" action="{{ route('admin.subscription-plans.update', $plan->id) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- Main Column --}}
            <div class="md:col-span-2 space-y-6">

                {{-- Names Card --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-6">Plan Names</h3>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Name (English) <span class="text-red-500">*</span></label>
                            <input type="text" name="name[en]" value="{{ old('name.en', $names['en'] ?? '') }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition">
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Name (Arabic)</label>
                                <input type="text" name="name[ar]" value="{{ old('name.ar', $names['ar'] ?? '') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition text-right" dir="rtl">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Name (Kurdish)</label>
                                <input type="text" name="name[ku]" value="{{ old('name.ku', $names['ku'] ?? '') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition text-right" dir="rtl">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pricing Card --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-6">Pricing & Limits</h3>
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Price (USD)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input type="number" name="final_price_usd" value="{{ old('final_price_usd', $plan->final_price_usd) }}" class="w-full pl-8 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-lg font-black text-emerald-600 outline-none focus:border-emerald-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Price (IQD)</label>
                            <input type="number" name="final_price_iqd" value="{{ old('final_price_iqd', $plan->final_price_iqd) }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-lg font-black text-slate-700 outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                         <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Property Upload Limit</label>
                         <input type="number" name="property_activation_limit" value="{{ old('property_activation_limit', $plan->property_activation_limit) }}" placeholder="Leave empty for unlimited" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none focus:border-indigo-500">
                         <p class="text-[10px] text-slate-400 mt-1">Set to 0 or empty for unlimited uploads.</p>
                    </div>
                </div>

                {{-- Features Card --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h3 class="text-lg font-black text-slate-900 mb-2">Plan Features</h3>
                    <p class="text-xs text-slate-500 mb-4">Enter each feature on a new line.</p>
                    <textarea name="features" rows="6" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none resize-none leading-relaxed" placeholder="10 Featured Listings&#10;Advanced Analytics&#10;Priority Support">{{ old('features', $featuresString) }}</textarea>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Configuration --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Configuration</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Target Audience</label>
                            <select name="type" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none cursor-pointer">
                                <option value="agent" {{ $plan->type == 'agent' ? 'selected' : '' }}>Agent</option>
                                <option value="real_estate_office" {{ $plan->type == 'real_estate_office' ? 'selected' : '' }}>Real Estate Office</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Duration (Months)</label>
                            <input type="number" name="duration_months" value="{{ old('duration_months', $plan->duration_months) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none">
                        </div>

                        <div class="pt-4 border-t border-slate-100 space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer p-2 hover:bg-slate-50 rounded-lg transition">
                                <input type="checkbox" name="active" value="1" {{ $plan->active ? 'checked' : '' }} class="w-5 h-5 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                                <span class="text-sm font-bold text-slate-700">Plan Active</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer p-2 hover:bg-slate-50 rounded-lg transition">
                                <input type="checkbox" name="is_featured" value="1" {{ $plan->is_featured ? 'checked' : '' }} class="w-5 h-5 text-amber-500 rounded border-gray-300 focus:ring-amber-500">
                                <span class="text-sm font-bold text-slate-700">Featured Plan</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Sort Order --}}
                <div class="bg-slate-50 rounded-3xl border border-slate-200 p-6">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-900 outline-none text-center">
                    <p class="text-[10px] text-slate-400 mt-2 text-center">Lower numbers appear first.</p>
                </div>

            </div>
        </div>
    </form>
</div>

@endsection
