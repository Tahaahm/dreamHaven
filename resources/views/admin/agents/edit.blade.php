@extends('layouts.admin-layout')

@section('title', 'Edit Agent Profile')

@section('content')

<div class="max-w-6xl mx-auto animate-fade-in-up">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex text-sm text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                <span class="mx-2 text-slate-300">/</span>
                <a href="{{ route('admin.agents.index') }}" class="hover:text-slate-800 transition">Agents</a>
                <span class="mx-2 text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">Edit Profile</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                Edit Agent: {{ $agent->agent_name }}
                @if($agent->is_verified)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                        <i class="fas fa-check-circle text-[10px]"></i> Verified
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200 flex items-center gap-1">
                        <i class="fas fa-clock text-[10px]"></i> Pending
                    </span>
                @endif
            </h1>
        </div>
        <div class="flex gap-3">
            {{-- ✅ ADD PROPERTY BUTTON FOR AGENTS --}}
            <a href="{{ route('admin.properties.create') }}?agent_id={{ $agent->id }}"
               class="px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-bold rounded-xl shadow-lg hover:shadow-xl hover:from-emerald-700 hover:to-emerald-800 transition transform active:scale-95 flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> Add Property
            </a>

            <a href="{{ route('admin.agents.index') }}" class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition shadow-sm">
                Cancel
            </a>
            <button type="submit" form="editAgentForm" class="px-6 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl shadow-lg hover:bg-slate-800 hover:shadow-xl transition transform active:scale-95 flex items-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    <form id="editAgentForm" method="POST" action="{{ route('admin.agents.update', $agent->id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- SECTION 1: Identity & Media --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Identity & Media</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-12 gap-8">
                {{-- Profile Image Upload --}}
                <div class="md:col-span-4 flex flex-col items-center justify-center border-r border-slate-100 pr-0 md:pr-8">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-3">Profile Photo</label>
                    <div class="relative group cursor-pointer w-40 h-40">
                        <div class="w-full h-full rounded-full bg-slate-100 border-4 border-white shadow-lg overflow-hidden relative">
                            @if($agent->profile_image)
                                <img id="profilePreview" src="{{ asset($agent->profile_image) }}" class="w-full h-full object-cover">
                            @else
                                <div id="profilePlaceholder" class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                                    <i class="fas fa-camera text-3xl mb-1"></i>
                                </div>
                                <img id="profilePreview" class="hidden w-full h-full object-cover">
                            @endif
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-pen text-white text-xl"></i>
                            </div>
                        </div>
                        <input type="file" name="profile_image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer rounded-full" onchange="previewImage(this, 'profilePreview', 'profilePlaceholder')">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-3 font-medium">Allowed: JPG, PNG (Max 2MB)</p>
                </div>

                {{-- Basic Info Inputs --}}
                <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Agent Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="agent_name" value="{{ old('agent_name', $agent->agent_name) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Primary Email <span class="text-red-500">*</span></label>
                        <input type="email" name="primary_email" value="{{ old('primary_email', $agent->primary_email) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Primary Phone</label>
                        <input type="text" name="primary_phone" value="{{ old('primary_phone', $agent->primary_phone) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">WhatsApp Number</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-emerald-500"><i class="fab fa-whatsapp text-lg"></i></span>
                            <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $agent->whatsapp_number) }}" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Agent Type</label>
                        <select name="type" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            <option value="independent" {{ $agent->type == 'independent' ? 'selected' : '' }}>Independent</option>
                            <option value="company" {{ $agent->type == 'company' ? 'selected' : '' }}>Company</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Bio --}}
            <div class="px-6 pb-6 border-t border-slate-100 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-3">Bio / Cover Image</label>
                        <div class="relative group cursor-pointer h-32 w-full">
                            <div class="w-full h-full rounded-xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden hover:border-indigo-400 transition-colors">
                                @if($agent->bio_image)
                                    <img id="bioPreview" src="{{ asset($agent->bio_image) }}" class="w-full h-full object-cover">
                                @else
                                    <div id="bioPlaceholder" class="text-center"><i class="fas fa-image text-slate-300 text-2xl mb-1"></i><p class="text-[10px] text-slate-400 font-bold uppercase">Click to Upload</p></div>
                                    <img id="bioPreview" class="hidden w-full h-full object-cover">
                                @endif
                            </div>
                            <input type="file" name="bio_image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImage(this, 'bioPreview', 'bioPlaceholder')">
                        </div>
                    </div>
                    <div class="md:col-span-8">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Professional Bio</label>
                        <textarea name="agent_bio" rows="4" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition resize-none">{{ old('agent_bio', $agent->agent_bio) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: Professional Details (Shortened for brevity, use previous code here if needed) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Professional Details</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div><label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Company Name</label><input type="text" name="company_name" value="{{ old('company_name', $agent->company_name) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"></div>
                <div><label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Employment Status</label><input type="text" name="employment_status" value="{{ old('employment_status', $agent->employment_status) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"></div>
                <div><label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">License Number</label><input type="text" name="license_number" value="{{ old('license_number', $agent->license_number) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"></div>
                <div><label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Years Experience</label><input type="number" name="years_experience" value="{{ old('years_experience', $agent->years_experience) }}" min="0" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"></div>
                <div><label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Properties Sold</label><input type="number" name="properties_sold" value="{{ old('properties_sold', $agent->properties_sold) }}" min="0" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"></div>
                <div class="lg:col-span-3"><label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Working Hours (JSON)</label><input type="text" name="working_hours" value="{{ is_array($agent->working_hours) ? json_encode($agent->working_hours) : old('working_hours', $agent->working_hours) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-600 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"></div>
            </div>
        </div>

        {{-- SECTION 3: Location Details (Shortened) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Location Details</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2"><label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Office Address</label><input type="text" name="office_address" value="{{ old('office_address', $agent->office_address) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"></div>
                <div><label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">City</label><input type="text" name="city" value="{{ old('city', $agent->city) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"></div>
                <div><label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">District</label><input type="text" name="district" value="{{ old('district', $agent->district) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"></div>
            </div>
        </div>

        {{-- SECTION 4: Financials & Subscription (UPDATED FOR DYNAMIC PLANS) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Financials & Subscription</h3>
                @if(isset($plans))
                    <span class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-1 rounded-lg border border-indigo-100">
                        {{ $plans->count() }} System Plans Available
                    </span>
                @endif
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-5">

                {{-- DYNAMIC PLAN SELECTION --}}
                <div class="relative group sm:col-span-3 lg:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Assign System Plan</label>
                    <div class="relative">
                        <select name="plan_id" class="w-full px-4 py-2.5 bg-indigo-50/50 border border-indigo-200 rounded-xl text-sm font-bold text-indigo-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition cursor-pointer appearance-none">

                            {{-- Check if we have an active subscription linked via ID --}}
                            @if($agent->subscription && $agent->subscription->currentPlan)
                                <option value="" selected>
                                    Active: {{ $agent->subscription->currentPlan->name }} (Expires: {{ $agent->subscription->end_date ? $agent->subscription->end_date->format('M d') : '-' }})
                                </option>
                                <option value="" disabled>──────────</option>
                            @else
                                <option value="" selected>-- Select a Plan --</option>
                            @endif

                            {{-- List all plans from DB --}}
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->name }} ({{ $plan->duration_label }}) - ${{ number_format($plan->final_price_usd) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-indigo-400 text-xs"></i>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">
                        * Selecting a new plan creates a new subscription. Old DB Status: <span class="font-mono bg-slate-100 px-1 rounded">{{ $agent->current_plan ?? 'NULL' }}</span>
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Remaining Uploads</label>
                    <input type="number" name="remaining_property_uploads" value="{{ old('remaining_property_uploads', $agent->remaining_property_uploads) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Commission Rate (%)</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate', $agent->commission_rate) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Consultation Fee</label>
                    <input type="number" step="0.01" name="consultation_fee" value="{{ old('consultation_fee', $agent->consultation_fee) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Currency</label>
                    <select name="currency" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        <option value="USD" {{ $agent->currency == 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="IQD" {{ $agent->currency == 'IQD' ? 'selected' : '' }}>IQD (د.ع)</option>
                        <option value="EUR" {{ $agent->currency == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- SECTION 5: System Settings --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
             <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">System Settings</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Overall Rating</label>
                    <input type="number" step="0.1" max="5" name="overall_rating" value="{{ old('overall_rating', $agent->overall_rating) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Password (Optional)</label>
                    <input type="password" name="password" placeholder="Leave empty to keep current" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" autocomplete="new-password">
                </div>
                <div class="flex items-end pb-2">
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="is_verified" value="1" {{ $agent->is_verified ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        <span class="ms-3 text-sm font-bold text-slate-700">Verified Status</span>
                    </label>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
    function previewImage(input, previewId, placeholderId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById(previewId).src = e.target.result;
                document.getElementById(previewId).classList.remove('hidden');
                if(document.getElementById(placeholderId)) {
                    document.getElementById(placeholderId).classList.add('hidden');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
