@extends('layouts.admin-layout')

@section('title', 'Edit Office')

@section('content')

<div class="max-w-6xl mx-auto animate-fade-in-up">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <nav class="flex text-sm text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                <span class="mx-2 text-slate-300">/</span>
                <a href="{{ route('admin.offices.index') }}" class="hover:text-slate-800 transition">Offices</a>
                <span class="mx-2 text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">Edit Office</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                Edit: {{ $office->company_name }}
                @if($office->is_verified)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">Verified</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
                @endif
            </h1>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.offices.index') }}" class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-50 transition shadow-sm">
                Cancel
            </a>
            <button type="button" onclick="submitOfficeForm()" class="px-6 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl shadow-lg hover:bg-slate-800 hover:shadow-xl transition transform active:scale-95 flex items-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    <form id="editOfficeForm" method="POST" action="{{ route('admin.offices.update', $office->id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- SECTION 1: Identity & Branding --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Identity & Branding</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-12 gap-8">

                {{-- Logo Upload --}}
                <div class="md:col-span-4 flex flex-col items-center justify-center border-r border-slate-100 pr-0 md:pr-8">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-3">Office Logo</label>
                    <div class="relative group cursor-pointer w-40 h-40">
                        <div class="w-full h-full rounded-2xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden hover:border-indigo-400 transition-colors">
                            @if($office->profile_image)
                                <img id="logoPreview" src="{{ asset($office->profile_image) }}" class="w-full h-full object-cover">
                            @else
                                <div id="logoPlaceholder" class="text-center">
                                    <i class="fas fa-building text-3xl text-slate-300 mb-2"></i>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">Upload Logo</p>
                                </div>
                                <img id="logoPreview" class="hidden w-full h-full object-cover">
                            @endif
                        </div>
                        <input type="file" name="logo" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImage(this, 'logoPreview', 'logoPlaceholder')">
                    </div>
                </div>

                {{-- Basic Info --}}
                <div class="md:col-span-8 grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Company Name <span class="text-red-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name', $office->company_name) }}" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Short Bio (Tagline)</label>
                        <input type="text" name="company_bio" value="{{ old('company_bio', $office->company_bio) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Detailed Description</label>
                        <textarea name="about_company" rows="4" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition resize-none">{{ old('about_company', $office->about_company) }}</textarea>
                    </div>
                </div>
            </div>

             {{-- Cover Image Section --}}
             <div class="px-6 pb-6 border-t border-slate-100 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-3 text-center">Cover / Bio Image</label>
                        <div class="relative group cursor-pointer h-32 w-full">
                            <div class="w-full h-full rounded-xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden hover:border-indigo-400 transition-colors">
                                @if($office->company_bio_image)
                                    <img id="bioPreview" src="{{ asset($office->company_bio_image) }}" class="w-full h-full object-cover">
                                @else
                                    <div id="bioPlaceholder" class="text-center">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase">Upload Cover</p>
                                    </div>
                                    <img id="bioPreview" class="hidden w-full h-full object-cover">
                                @endif
                            </div>
                            <input type="file" name="company_bio_image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImage(this, 'bioPreview', 'bioPlaceholder')">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: Contact & Location --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Contact & Location</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email_address" value="{{ old('email_address', $office->email_address) }}" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $office->phone_number) }}"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Full Address</label>
                    <input type="text" name="office_address" value="{{ old('office_address', $office->office_address) }}"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">City</label>
                    <input type="text" name="city" value="{{ old('city', $office->city) }}"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">District</label>
                    <input type="text" name="district" value="{{ old('district', $office->district) }}"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Latitude</label>
                    <input type="number" step="any" name="latitude" value="{{ old('latitude', $office->latitude) }}"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Longitude</label>
                    <input type="number" step="any" name="longitude" value="{{ old('longitude', $office->longitude) }}"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>
            </div>
        </div>

        {{-- SECTION 3: Performance & Availability --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Performance & Availability</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">License Number</label>
                    <input type="text" name="license_number" value="{{ old('license_number', $office->license_number) }}"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Years Experience</label>
                    <input type="number" name="years_experience" value="{{ old('years_experience', $office->years_experience) }}" min="0"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Properties Sold</label>
                    <input type="number" name="properties_sold" value="{{ old('properties_sold', $office->properties_sold) }}" min="0"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                {{-- NEW AVAILABILITY SCHEDULE UI --}}
                <div class="lg:col-span-3 mt-4">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-3">Weekly Schedule</label>

                    {{-- Hidden input to store the JSON string --}}
                    <input type="hidden" name="availability_schedule" id="availability_json">

                    <div class="bg-slate-50 rounded-xl border border-slate-200 overflow-hidden">
                        <div class="grid grid-cols-1 divide-y divide-slate-200" id="schedule_container">
                            {{-- JS will populate this --}}
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- SECTION 4: Subscriptions & Financials --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Subscription Plan</h3>
                @if(isset($plans))
                    <span class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-1 rounded-lg border border-indigo-100">
                        {{ $plans->count() }} Office Plans Available
                    </span>
                @endif
            </div>
            <div class="p-6">
                 {{-- ASSIGN SUBSCRIPTION PLAN --}}
                 <div class="relative group">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Assign System Plan</label>
                    <div class="relative">
                        <select name="plan_id" class="w-full px-4 py-2.5 bg-indigo-50/50 border border-indigo-200 rounded-xl text-sm font-bold text-indigo-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition cursor-pointer appearance-none">
                            @if($office->subscription && $office->subscription->currentPlan)
                                <option value="" selected>
                                    Active: {{ $office->subscription->currentPlan->name }} (Expires: {{ $office->subscription->end_date ? $office->subscription->end_date->format('M d') : '-' }})
                                </option>
                                <option value="" disabled>──────────</option>
                            @else
                                <option value="" selected>-- Select a Plan --</option>
                            @endif

                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->name }} ({{ $plan->duration_label }}) - ${{ number_format($plan->final_price_iqd) }} IQD
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-indigo-400 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 5: System Settings --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
             <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">System Settings</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Password (Optional)</label>
                    <input type="password" name="password" placeholder="Leave empty to keep current"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" autocomplete="new-password">
                </div>
                <div class="flex items-center">
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="is_verified" value="1" {{ $office->is_verified ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        <span class="ms-3 text-sm font-bold text-slate-700">Verified Office</span>
                    </label>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
    // 1. Image Preview Logic
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

    // 2. Availability Schedule Logic
    // Parse existing JSON from PHP or use default empty object
    let scheduleData = @json($office->availability_schedule ? json_decode(is_array($office->availability_schedule) ? json_encode($office->availability_schedule) : $office->availability_schedule, true) : []);

    const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    const container = document.getElementById('schedule_container');
    const hiddenInput = document.getElementById('availability_json');

    function renderSchedule() {
        container.innerHTML = '';

        days.forEach(day => {
            // Get current data for this day or default
            const dayData = scheduleData[day] || { active: false, start: '09:00', end: '17:00' };
            const isActive = dayData.active === true || dayData.active === 'true' || dayData.active === 1;

            const row = document.createElement('div');
            row.className = `p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors ${isActive ? 'bg-indigo-50/50' : 'bg-white'}`;

            row.innerHTML = `
                <div class="flex items-center gap-4 min-w-[140px]">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" onchange="updateDay('${day}', 'active', this.checked)" ${isActive ? 'checked' : ''}>
                        <div class="relative w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        <span class="ms-3 text-sm font-bold capitalize ${isActive ? 'text-indigo-700' : 'text-slate-500'}">${day}</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 ${isActive ? 'opacity-100' : 'opacity-40 pointer-events-none'} transition-opacity">
                    <input type="time" value="${dayData.start || '09:00'}"
                        onchange="updateDay('${day}', 'start', this.value)"
                        class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-sm font-mono text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <span class="text-slate-400 font-bold">-</span>
                    <input type="time" value="${dayData.end || '17:00'}"
                        onchange="updateDay('${day}', 'end', this.value)"
                        class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-sm font-mono text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            `;
            container.appendChild(row);
        });

        // Update hidden input on every render/change
        hiddenInput.value = JSON.stringify(scheduleData);
    }

    function updateDay(day, field, value) {
        if (!scheduleData[day]) scheduleData[day] = { active: false, start: '09:00', end: '17:00' };
        scheduleData[day][field] = value;
        renderSchedule(); // Re-render to update UI states (colors, opacity)
    }

    // Initial render
    renderSchedule();

    // 3. Form Submit Handler
    function submitOfficeForm() {
        // Ensure the latest JSON is in the input
        hiddenInput.value = JSON.stringify(scheduleData);
        document.getElementById('editOfficeForm').submit();
    }
</script>

@endsection
