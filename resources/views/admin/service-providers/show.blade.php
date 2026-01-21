@extends('layouts.admin-layout')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header & Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <a href="{{ route('admin.service-providers.index') }}"
           class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-900 font-medium transition-colors group">
            <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center group-hover:border-gray-300 shadow-sm transition-all">
                <i class="fas fa-arrow-left text-sm"></i>
            </div>
            <span>Back to Providers</span>
        </a>

        <div class="flex gap-3">
            <a href="{{ route('admin.service-providers.edit', $provider->id) }}"
               class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 shadow-sm transition-all">
                <i class="fas fa-pen text-gray-400"></i> Edit Profile
            </a>

            <form action="{{ route('admin.service-providers.delete', $provider->id) }}" method="POST" onsubmit="return confirm('Delete this provider? This action is permanent.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm font-semibold border border-red-100 shadow-sm transition-all">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- LEFT COLUMN: Profile Summary --}}
        <div class="space-y-6">

            {{-- Profile Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden relative">
                {{-- Banner/Cover --}}
                <div class="h-32 bg-gradient-to-r from-gray-100 to-gray-200 w-full relative">
                     <div class="absolute inset-0 pattern-grid opacity-10"></div> {{-- Optional pattern --}}
                </div>

                <div class="px-6 pb-6 text-center relative">
                    {{-- Avatar --}}
                    <div class="-mt-16 mb-4 inline-block relative">
                        @if($provider->profile_image)
                            <img src="{{ $provider->profile_image }}" class="w-32 h-32 rounded-2xl object-cover border-4 border-white shadow-md bg-white">
                        @else
                            <div class="w-32 h-32 rounded-2xl bg-white border-4 border-white shadow-md flex items-center justify-center text-4xl font-bold text-indigo-600">
                                {{ substr($provider->company_name, 0, 1) }}
                            </div>
                        @endif

                        @if($provider->is_verified)
                            <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-1 shadow-sm">
                                <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                            </div>
                        @endif
                    </div>

                    <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $provider->company_name }}</h1>

                    <div class="flex items-center justify-center gap-2 mb-6">
                        @if($provider->category)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                {{ $provider->category->name }}
                            </span>
                        @endif
                        <div class="flex items-center text-amber-400 text-sm font-bold bg-amber-50 px-2 py-1 rounded-md border border-amber-100">
                            <span>{{ number_format($provider->average_rating, 1) }}</span>
                            <i class="fas fa-star ml-1 text-xs"></i>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-6 space-y-4 text-left">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Email</p>
                                <p class="text-sm text-gray-900 truncate" title="{{ $provider->email_address }}">{{ $provider->email_address }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Phone</p>
                                <p class="text-sm text-gray-900">{{ $provider->phone_number }}</p>
                            </div>
                        </div>

                        @if($provider->website_url)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Website</p>
                                <a href="{{ $provider->website_url }}" target="_blank" class="text-sm text-indigo-600 hover:underline">Visit Site</a>
                            </div>
                        </div>
                        @endif

                        @if($provider->city)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Location</p>
                                <p class="text-sm text-gray-900">{{ $provider->city }}{{ $provider->district ? ', ' . $provider->district : '' }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Plan Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Subscription Plan</h3>

                @if($provider->hasActivePlan())
                    <div class="bg-indigo-50/50 rounded-xl p-5 border border-indigo-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-indigo-100 rounded-full blur-xl opacity-50"></div>

                        <div class="flex justify-between items-start mb-4 relative z-10">
                            <div>
                                <h4 class="text-lg font-bold text-indigo-900">{{ $provider->plan->name }}</h4>
                                <p class="text-xs text-indigo-600 font-medium">Active Subscription</p>
                            </div>
                            <div class="bg-white p-1.5 rounded-lg shadow-sm text-indigo-600">
                                <i class="fas fa-crown"></i>
                            </div>
                        </div>

                        <div class="space-y-2 relative z-10">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Expires On</span>
                                <span class="font-semibold text-gray-900">{{ $provider->plan_expires_at->format('M d, Y') }}</span>
                            </div>

                            <div class="w-full bg-indigo-200 h-1.5 rounded-full overflow-hidden mt-2">
                                @php $percent = min(100, ($provider->remainingPlanDays() / 30) * 100); @endphp
                                <div class="h-full rounded-full transition-all duration-500 {{ $percent > 20 ? 'bg-indigo-600' : 'bg-red-500' }}" style="width: {{ $percent }}%"></div>
                            </div>
                            <p class="text-xs text-right mt-1 {{ $provider->remainingPlanDays() < 7 ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                {{ $provider->remainingPlanDays() }} days left
                            </p>
                        </div>
                    </div>
                @else
                    <div class="text-center p-6 bg-gray-50 rounded-xl border border-gray-100 border-dashed">
                        <p class="text-sm text-gray-500 font-medium mb-3">No active subscription plan</p>
                        <a href="{{ route('admin.service-providers.edit', $provider->id) }}" class="text-indigo-600 text-sm font-bold hover:underline">Upgrade Plan</a>
                    </div>
                @endif
            </div>

            {{-- Quick Stats --}}
            @if($provider->years_in_business || $provider->completed_projects)
            <div class="grid grid-cols-2 gap-4">
                @if($provider->years_in_business)
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Experience</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $provider->years_in_business }} <span class="text-sm text-gray-400 font-normal">Years</span></p>
                </div>
                @endif
                @if($provider->completed_projects)
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Projects</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $provider->completed_projects }} <span class="text-sm text-gray-400 font-normal">Done</span></p>
                </div>
                @endif
            </div>
            @endif

        </div>

        {{-- RIGHT COLUMN: Details --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- About Section --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <h3 class="font-bold text-gray-900 text-lg mb-4">About Company</h3>
                <div class="prose prose-sm prose-gray max-w-none text-gray-600">
                    <p class="leading-relaxed">{{ $provider->company_bio ?? 'No bio provided.' }}</p>

                    @if($provider->company_overview)
                        <hr class="my-6 border-gray-100">
                        <p class="leading-relaxed">{{ $provider->company_overview }}</p>
                    @endif
                </div>
            </div>

            {{-- Services Offered --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 text-lg">Services Offered</h3>
                    <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-1 rounded">{{ $provider->offerings->count() }}</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($provider->offerings as $offering)
                        <div class="p-6 hover:bg-gray-50 transition group">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-base mb-1 group-hover:text-indigo-600 transition-colors">{{ $offering->service_title }}</h4>
                                    <p class="text-sm text-gray-500 leading-relaxed">{{ $offering->service_description }}</p>
                                </div>
                                @if($offering->price_range)
                                    <span class="flex-shrink-0 inline-flex items-center px-3 py-1 rounded-lg bg-green-50 text-green-700 text-sm font-bold border border-green-100">
                                        {{ $offering->price_range }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-400">
                            <p>No services listed yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Gallery Grid --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-900 text-lg">Project Gallery</h3>
                </div>
                <div class="p-8">
                    @if($provider->galleries->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($provider->galleries as $gallery)
                                <div class="group relative aspect-square rounded-xl overflow-hidden bg-gray-100 cursor-zoom-in">
                                    <img src="{{ $gallery->image_url }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                        <div>
                                            <p class="text-white font-bold text-sm truncate">{{ $gallery->project_title }}</p>
                                            <p class="text-gray-300 text-xs truncate">{{ $gallery->description }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <p>No images uploaded.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Reviews --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 text-lg">Customer Reviews</h3>
                    <a href="{{ route('admin.reviews.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">View All</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($provider->reviews()->latest()->take(3)->get() as $review)
                        <div class="p-6 hover:bg-gray-50 transition">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    @if($review->reviewer_avatar)
                                        <img src="{{ $review->reviewer_avatar }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                            {{ substr($review->reviewer_name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-1">
                                        <h5 class="font-bold text-gray-900 text-sm">{{ $review->reviewer_name }}</h5>
                                        <span class="text-xs text-gray-400">{{ $review->review_date->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="flex text-amber-400 text-xs">
                                            @for($i=1; $i<=5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->star_rating ? '' : 'text-gray-200' }}"></i>
                                            @endfor
                                        </div>
                                        @if($review->is_verified)
                                            <span class="bg-green-50 text-green-700 text-[10px] font-bold px-1.5 py-0.5 rounded border border-green-100">Verified Purchase</span>
                                        @endif
                                    </div>
                                    <p class="text-gray-600 text-sm leading-relaxed">"{{ $review->review_content }}"</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-400">
                            <p>No reviews yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
