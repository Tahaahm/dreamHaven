
@include('layouts.sidebar')
@extends('layouts.app')

@section('content')
<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }
        100% {
            background-position: 1000px 0;
        }
    }

    @keyframes pulse-ring {
        0% {
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
        }
        70% {
            box-shadow: 0 0 0 15px rgba(59, 130, 246, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-slideInRight {
        animation: slideInRight 0.5s ease-out forwards;
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .input-glow:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 
                    0 0 20px rgba(59, 130, 246, 0.2);
    }

    .btn-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        background-size: 200% 200%;
        transition: all 0.3s ease;
    }

    .btn-gradient:hover {
        background-position: right center;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }

    .section-card {
        position: relative;
        overflow: hidden;
    }

    .section-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.2),
            transparent
        );
        transition: left 0.5s;
    }

    .section-card:hover::before {
        left: 100%;
    }

    .checkbox-modern {
        appearance: none;
        width: 22px;
        height: 22px;
        border: 2px solid #d1d5db;
        border-radius: 6px;
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
    }

    .checkbox-modern:checked {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
    }

    .checkbox-modern:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 14px;
        font-weight: bold;
    }

    .step-indicator {
        position: relative;
        transition: all 0.3s ease;
    }

    .step-indicator::after {
        content: '';
        position: absolute;
        inset: -3px;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }

    .section-card:hover .step-indicator::after {
        opacity: 0.2;
    }

    .floating-shapes {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
        z-index: 0;
    }

    .shape {
        position: absolute;
        opacity: 0.05;
        animation: float 20s ease-in-out infinite;
    }

    .shape-1 {
        top: 10%;
        left: 10%;
        width: 300px;
        height: 300px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        animation-delay: 0s;
    }

    .shape-2 {
        top: 60%;
        right: 10%;
        width: 200px;
        height: 200px;
        background: linear-gradient(135deg, #f093fb, #f5576c);
        border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        animation-delay: 2s;
    }

    .shape-3 {
        bottom: 10%;
        left: 20%;
        width: 250px;
        height: 250px;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        border-radius: 63% 37% 54% 46% / 55% 48% 52% 45%;
        animation-delay: 4s;
    }

    .time-input-container {
        position: relative;
    }

    .time-input-container::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 8px;
        padding: 2px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .time-input-container:focus-within::before {
        opacity: 1;
    }

    .profile-preview {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .profile-preview::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        transition: transform 0.3s ease;
    }

    .profile-preview.loaded::after {
        transform: translate(-50%, -50%) scale(1);
    }

    @media (max-width: 768px) {
        .section-card {
            animation-delay: 0s !important;
        }
    }

    .form-section {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .form-section:nth-child(1) { animation-delay: 0.1s; }
    .form-section:nth-child(2) { animation-delay: 0.2s; }
    .form-section:nth-child(3) { animation-delay: 0.3s; }
    .form-section:nth-child(4) { animation-delay: 0.4s; }

    .schedule-row {
        transition: all 0.3s ease;
        position: relative;
    }

    .schedule-row::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 4px;
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .schedule-row.active::before {
        transform: scaleY(1);
    }

    .schedule-row:hover {
        transform: translateX(8px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }
</style>

<div class="floating-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
</div>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-12 px-4 sm:px-6 lg:px-8 relative">
    <div class="max-w-5xl mx-auto relative z-10">
        <!-- Header -->
        <div class="text-center mb-10 animate-fadeInUp">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl mb-4 shadow-lg animate-float">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Create Real Estate Office</h1>
            <p class="text-lg text-gray-600">Set up your professional office profile</p>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <!-- Main Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden glass-effect animate-fadeInUp" style="animation-delay: 0.2s;">
<form method="POST" action="{{ route('agent.real-estate-office.store') }}" enctype="multipart/form-data">
    @csrf




                
                <div class="p-8 space-y-8">
                    <!-- Basic Information Section -->
                    <div class="border-b border-gray-200 pb-8 section-card form-section">
                        <h2 class="text-2xl font-semibold text-gray-900 mb-6 flex items-center">
                            <span class="step-indicator w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">1</span>
                            Basic Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Company Name -->
                            <div class="md:col-span-2">
                                <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Company Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="company_name" id="company_name" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 input-glow @error('company_name') border-red-500 @enderror"
                                    placeholder="e.g., Premier Realty Group" value="{{ old('company_name') }}">
                                @error('company_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email_address" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email_address" id="email_address" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 input-glow @error('email_address') border-red-500 @enderror"
                                    placeholder="office@example.com" value="{{ old('email_address') }}">
                                @error('email_address')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" name="phone_number" id="phone_number" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 input-glow @error('phone_number') border-red-500 @enderror"
                                    placeholder="+1 (555) 000-0000" value="{{ old('phone_number') }}">
                                @error('phone_number')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Years of Experience -->
                            <div>
                                <label for="years_experience" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Years of Experience
                                </label>
                                <input type="number" name="years_experience" id="years_experience" min="0"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 input-glow"
                                    placeholder="10" value="{{ old('years_experience', 0) }}">
                            </div>

                            <!-- Current Plan -->
                            <div>
                                <label for="current_plan" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Subscription Plan
                                </label>
                                <select name="current_plan" id="current_plan"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 input-glow">
                                    <option value="">Select a plan</option>
                                    <option value="starter" {{ old('current_plan') == 'starter' ? 'selected' : '' }}>Starter</option>
                                    <option value="professional" {{ old('current_plan') == 'professional' ? 'selected' : '' }}>Professional</option>
                                    <option value="enterprise" {{ old('current_plan') == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information Section -->
                    <div class="border-b border-gray-200 pb-8 section-card form-section">
                        <h2 class="text-2xl font-semibold text-gray-900 mb-6 flex items-center">
                            <span class="step-indicator w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">2</span>
                            Location Details
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Office Address -->
                            <div class="md:col-span-2">
                                <label for="office_address" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Office Address
                                </label>
                                <textarea name="office_address" id="office_address" rows="3"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="123 Main Street, Suite 100">{{ old('office_address') }}</textarea>
                            </div>

                            <!-- City -->
                            <div>
                                <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                                    City
                                </label>
                                <input type="text" name="city" id="city"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="New York" value="{{ old('city') }}">
                            </div>

                            <!-- District -->
                            <div>
                                <label for="district" class="block text-sm font-semibold text-gray-700 mb-2">
                                    District
                                </label>
                                <input type="text" name="district" id="district"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Manhattan" value="{{ old('district') }}">
                            </div>

                            <!-- Latitude -->
                            <div>
                                <label for="latitude" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Latitude
                                </label>
                                <input type="number" step="any" name="latitude" id="latitude"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="40.7128" value="{{ old('latitude') }}">
                            </div>

                            <!-- Longitude -->
                            <div>
                                <label for="longitude" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Longitude
                                </label>
                                <input type="number" step="any" name="longitude" id="longitude"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="-74.0060" value="{{ old('longitude') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Company Profile Section -->
                    <div class="border-b border-gray-200 pb-8 section-card form-section">
                        <h2 class="text-2xl font-semibold text-gray-900 mb-6 flex items-center">
                            <span class="step-indicator w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">3</span>
                            Company Profile
                        </h2>
                        
                        <div class="space-y-6">
                            <!-- Profile Image -->
         <!-- Profile Image -->
<div>
    <label for="profile_image" class="block text-sm font-semibold text-gray-700 mb-2">
        Profile Image
    </label>
    <div class="flex items-center space-x-4">
        <input type="file" name="profile_image" id="profile_image"
            class="flex-1 px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 input-glow"
            accept="image/*">
        <div id="imagePreview" class="hidden w-16 h-16 rounded-lg border-2 border-gray-200 overflow-hidden profile-preview shadow-lg">
            <img src="" alt="Preview" class="w-full h-full object-cover">
        </div>
    </div>
</div>


                            <!-- Company Bio -->
                            <div>
                                <label for="company_bio" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Company Bio
                                    <span class="text-gray-500 font-normal text-xs">(Short tagline)</span>
                                </label>
                                <textarea name="company_bio" id="company_bio" rows="2"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 input-glow"
                                    placeholder="Your trusted partner in finding the perfect property">{{ old('company_bio') }}</textarea>
                            </div>

                            <!-- About Company -->
                            <div>
                                <label for="about_company" class="block text-sm font-semibold text-gray-700 mb-2">
                                    About Company
                                    <span class="text-gray-500 font-normal text-xs">(Detailed description)</span>
                                </label>
                                <textarea name="about_company" id="about_company" rows="6"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 input-glow"
                                    placeholder="Tell us about your company's history, values, and what makes you unique...">{{ old('about_company') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Availability Schedule Section -->
                    <div class="section-card form-section">
                        <h2 class="text-2xl font-semibold text-gray-900 mb-6 flex items-center">
                            <span class="step-indicator w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">4</span>
                            Availability Schedule
                        </h2>
                        
                        <div class="space-y-4" id="scheduleContainer">
                            @php
                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            @endphp
                            
                            @foreach($days as $day)
                            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl schedule-row">
                                <div class="flex items-center flex-1">
                                    <input type="checkbox" id="day_{{ strtolower($day) }}" 
                                        class="checkbox-modern schedule-checkbox"
                                        data-day="{{ strtolower($day) }}">
                                    <label for="day_{{ strtolower($day) }}" class="ml-3 text-sm font-medium text-gray-700 w-24">
                                        {{ $day }}
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2 schedule-times time-input-container" id="times_{{ strtolower($day) }}" style="display: none;">
                                    <input type="time" name="availability_schedule[{{ strtolower($day) }}][open]" 
                                        class="px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm input-glow"
                                        value="09:00">
                                    <span class="text-gray-500">to</span>
                                    <input type="time" name="availability_schedule[{{ strtolower($day) }}][close]" 
                                        class="px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm input-glow"
                                        value="17:00">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="bg-gray-50 px-8 py-6 flex items-center justify-between border-t border-gray-200">
                  <a href="{{ url()->previous() }}" 
   class="px-6 py-3 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors duration-200">
   Cancel
</a>

                    <button type="submit" 
                        class="btn-gradient px-8 py-3 text-white font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Create Office
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// Image preview for uploaded file
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    const img = preview.querySelector('img');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
            preview.classList.add('loaded');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
        preview.classList.remove('loaded');
    }
});

</script>
<script>
// Image preview functionality with enhanced animation
document.getElementById('profile_image').addEventListener('input', function(e) {
    const url = e.target.value;
    const preview = document.getElementById('imagePreview');
    const img = preview.querySelector('img');
    
    if (url) {
        img.src = url;
        preview.classList.remove('hidden');
        img.onload = function() {
            preview.classList.add('loaded');
        };
        img.onerror = function() {
            preview.classList.add('hidden');
            preview.classList.remove('loaded');
        };
    } else {
        preview.classList.add('hidden');
        preview.classList.remove('loaded');
    }
});

// Enhanced schedule checkbox functionality
document.querySelectorAll('.schedule-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const day = this.dataset.day;
        const timesDiv = document.getElementById('times_' + day);
        const scheduleRow = this.closest('.schedule-row');
        
        timesDiv.style.display = this.checked ? 'flex' : 'none';
        
        if (this.checked) {
            scheduleRow.classList.add('active');
        } else {
            scheduleRow.classList.remove('active');
        }
        
        // Enable/disable time inputs
        const timeInputs = timesDiv.querySelectorAll('input[type="time"]');
        timeInputs.forEach(input => {
            input.disabled = !this.checked;
            if (!this.checked) {
                input.removeAttribute('name');
            } else {
                const nameAttr = this.checked ? 
                    `availability_schedule[${day}][${input.parentElement.children[0] === input ? 'open' : 'close'}]` : '';
                if (nameAttr) input.setAttribute('name', nameAttr);
            }
        });
    });
});

// Enhanced form validation with better UX
document.getElementById('officeForm').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let isValid = true;
    let firstInvalidField = null;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('border-red-500');
            field.classList.add('animate-shake');
            if (!firstInvalidField) {
                firstInvalidField = field;
            }
            
            setTimeout(() => {
                field.classList.remove('animate-shake');
            }, 500);
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        
        // Create a styled alert
        const alert = document.createElement('div');
        alert.className = 'fixed top-4 right-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-lg z-50 animate-slideInRight';
        alert.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-800 font-medium">Please fill in all required fields</p>
            </div>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 3000);
        
        if (firstInvalidField) {
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalidField.focus();
        }
    }
});

// Add shake animation
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    .animate-shake {
        animation: shake 0.5s ease-in-out;
    }
`;
document.head.appendChild(style);

// Add smooth scroll behavior
document.querySelectorAll('input, textarea, select').forEach(element => {
    element.addEventListener('focus', function() {
        this.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
});
</script>
@endsection

