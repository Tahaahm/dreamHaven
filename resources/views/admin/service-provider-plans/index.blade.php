@extends('layouts.admin-layout')
@section('content')
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Provider Plans</h1>
            <p class="text-sm text-gray-500">Manage subscription plans for service providers</p>
        </div>
        <a href="{{ route('admin.service-provider-plans.create') }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40 hover:-translate-y-0.5">
            <i class="fas fa-plus"></i>
            <span>Create Plan</span>
        </a>
    </div>
</div>

@if($plans->isEmpty())
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-20 text-center">
        <div class="w-20 h-20 bg-gradient-to-br from-indigo-100 to-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-clipboard-list text-3xl text-indigo-600"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">No Plans Yet</h3>
        <p class="text-sm text-gray-500 mb-6">Get started by creating your first service provider plan</p>
        <a href="{{ route('admin.service-provider-plans.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition">
            <i class="fas fa-plus"></i>
            <span>Create First Plan</span>
        </a>
    </div>
</div>
@else
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($plans as $plan)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl hover:border-indigo-200 transition-all duration-300 group relative">
        @if($plan->most_popular)
        <div class="absolute -top-1 -right-1 z-10">
            <div class="bg-gradient-to-r from-amber-400 to-amber-500 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-bl-xl rounded-tr-xl shadow-lg">
                <i class="fas fa-star mr-1"></i>Most Popular
            </div>
        </div>
        @endif

        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-900 mb-1 group-hover:text-indigo-600 transition">{{ $plan->name }}</h3>
                    <div class="flex items-center gap-2">
                        @if($plan->active)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 px-2.5 py-1 rounded-full border border-green-200">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 bg-gray-100 px-2.5 py-1 rounded-full border border-gray-200">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                            Inactive
                        </span>
                        @endif
                    </div>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                    <i class="fas fa-gem text-white text-lg"></i>
                </div>
            </div>

            <div class="space-y-3 mb-6">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-gray-900">${{ number_format($plan->monthly_price, 0) }}</span>
                    <span class="text-sm text-gray-500 font-medium">/month</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-600">or</span>
                    <span class="font-semibold text-indigo-600">${{ number_format($plan->annual_price, 0) }}/year</span>
                    @if($plan->monthly_price * 12 > $plan->annual_price)
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold">
                        Save {{ round((1 - $plan->annual_price / ($plan->monthly_price * 12)) * 100) }}%
                    </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <div class="text-center">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mx-auto mb-2 shadow-sm">
                        <i class="fas fa-rectangle-ad text-indigo-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $plan->advertisement_slots }}</p>
                    <p class="text-xs text-gray-500 font-medium">Ad Slots</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mx-auto mb-2 shadow-sm">
                        <i class="fas fa-calendar-star text-amber-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $plan->featured_placement_days }}</p>
                    <p class="text-xs text-gray-500 font-medium">Featured Days</p>
                </div>
            </div>

            @if($plan->features)
            @php
                $featuresArray = is_array($plan->features)
                    ? $plan->features
                    : array_filter(explode("\n", $plan->features), fn($f) => trim($f) !== '');
                $featureCount = count($featuresArray);
            @endphp
            <div class="mb-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Features Included</p>
                <div class="space-y-2">
                    @foreach(array_slice($featuresArray, 0, 3) as $feature)
                    <div class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-green-500 text-sm mt-0.5 flex-shrink-0"></i>
                        <span class="text-sm text-gray-700">{{ is_string($feature) ? trim($feature) : $feature }}</span>
                    </div>
                    @endforeach
                    @if($featureCount > 3)
                    <p class="text-xs text-indigo-600 font-medium">+ {{ $featureCount - 3 }} more features</p>
                    @endif
                </div>
            </div>
            @endif

            <div class="flex gap-2 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.service-provider-plans.edit', $plan->id) }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:shadow-lg">
                    <i class="fas fa-edit"></i>
                    <span>Edit</span>
                </a>
                <form action="{{ route('admin.service-provider-plans.delete', $plan->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this plan? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all border border-red-200 hover:border-red-300">
                        <i class="fas fa-trash-alt"></i>
                        <span>Delete</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
