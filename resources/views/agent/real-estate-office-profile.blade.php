<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $office->company_name }} - Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            scroll-behavior: smooth;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }
        
        .animate-slide-up {
            animation: slideUp 0.6s ease-out forwards;
        }
        
        .animate-slide-left {
            animation: slideInLeft 0.6s ease-out forwards;
        }
        
        .animate-slide-right {
            animation: slideInRight 0.6s ease-out forwards;
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><g fill="white" opacity="0.05"><circle cx="100" cy="100" r="80"/><circle cx="400" cy="300" r="120"/><circle cx="900" cy="150" r="100"/><circle cx="1100" cy="400" r="90"/><circle cx="600" cy="500" r="70"/></g></svg>');
            background-size: cover;
        }
        
        .card-hover {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.3);
        }
        
        .tab-button {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .tab-button::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .tab-button.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            color: #667eea;
        }
        
        .tab-button.active::after {
            width: 100%;
        }
        
        .property-grid-item {
            opacity: 0;
            animation: slideUp 0.5s ease-out forwards;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .info-badge {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        
        .image-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .card-hover:hover .image-overlay {
            opacity: 1;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--tw-gradient-stops));
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        
        @media (max-width: 768px) {
            .hero-gradient h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
  
<body class="bg-gradient-to-br from-gray-50 to-gray-100">

    <!-- Hero Section -->
    <div class="hero-gradient relative">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/20"></div>
        <div class="relative container mx-auto px-4 sm:px-6 py-16 sm:py-24 animate-fade-in">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                <!-- Left Side: Office Info -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 sm:gap-8 animate-slide-left">
                    @if($office->profile_image)
                        <img src="{{ asset('storage/public/' . $office->profile_image) }}" 

                    

                             alt="{{ $office->company_name }}" 
                             class="w-32 h-32 sm:w-40 sm:h-40 rounded-3xl object-cover border-4 border-white shadow-2xl floating">
                    @else
                        <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-3xl bg-white/20 flex items-center justify-center border-4 border-white shadow-2xl floating">
                            <i class="fas fa-building text-5xl sm:text-7xl text-white"></i>
                        </div>
                    @endif
                    <div class="text-white text-center sm:text-left">
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-bold mb-3 sm:mb-4">{{ $office->company_name }}</h1>
                        <p class="text-lg sm:text-xl text-white/90 mb-2 flex items-center justify-center sm:justify-start">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            {{ $office->city }}, {{ $office->district }}
                        </p>
                        @if($office->years_experience)
                        <p class="text-base sm:text-lg text-white/80 flex items-center justify-center sm:justify-start">
                            <i class="fas fa-award mr-2"></i>
                            {{ $office->years_experience }} Years of Excellence
                        </p>
                        @endif
                    </div>
                </div>
                
                <!-- Right Side: Contact Info -->
                <div class="flex flex-col gap-4 w-full sm:w-auto animate-slide-right">
                    <a href="mailto:{{ $office->email_address }}" 
                       class="info-badge px-6 sm:px-8 py-4 rounded-2xl text-center hover:scale-105 transition shadow-xl">
                        <i class="fas fa-envelope text-purple-600 mr-2"></i>
                        <span class="font-semibold text-gray-800 text-sm sm:text-base">{{ $office->email_address }}</span>
                    </a>
                    <a href="tel:{{ $office->phone_number }}" 
                       class="info-badge px-6 sm:px-8 py-4 rounded-2xl text-center hover:scale-105 transition shadow-xl">
                        <i class="fas fa-phone text-purple-600 mr-2"></i>
                        <span class="font-semibold text-gray-800 text-sm sm:text-base">{{ $office->phone_number }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 py-8 sm:py-12">
        
        <!-- About Section -->
        @if($office->about_company || $office->company_bio)
        <div class="glass-card rounded-3xl shadow-2xl p-6 sm:p-10 mb-8 sm:mb-12 card-hover animate-slide-up" style="animation-delay: 0.1s">
            <h2 class="text-3xl sm:text-4xl font-bold gradient-text mb-6 flex items-center">
                <i class="fas fa-info-circle mr-3"></i>About Us
            </h2>
            @if($office->about_company)
            <p class="text-gray-700 text-base sm:text-lg leading-relaxed mb-6">{{ $office->about_company }}</p>
            @endif
            @if($office->company_bio)
            <p class="text-gray-600 text-sm sm:text-base leading-relaxed">{{ $office->company_bio }}</p>
            @endif
            
            @if($office->current_plan)
            <div class="mt-8 flex items-center justify-center sm:justify-start">
                <div class="inline-block bg-gradient-to-r from-purple-600 to-purple-700 text-white px-6 py-3 rounded-full shadow-lg">
                    <i class="fas fa-star mr-2"></i>
                    <span class="font-bold">{{ ucfirst($office->current_plan) }} Plan</span>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Stats Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mb-8 sm:mb-12">
            <div class="stat-card from-blue-500 to-blue-600 rounded-3xl p-6 sm:p-8 text-white shadow-2xl card-hover animate-slide-up" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between relative z-10">
                    <i class="fas fa-users text-4xl sm:text-5xl opacity-80"></i>
                    <div class="text-right">
                        <h3 class="text-4xl sm:text-5xl font-bold">{{ $totalAgents }}</h3>
                        <p class="text-blue-100 text-sm sm:text-lg">Professional Agents</p>
                    </div>
                </div>
            </div>

            <div class="stat-card from-green-500 to-green-600 rounded-3xl p-6 sm:p-8 text-white shadow-2xl card-hover animate-slide-up" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between relative z-10">
                    <i class="fas fa-home text-4xl sm:text-5xl opacity-80"></i>
                    <div class="text-right">
                        <h3 class="text-4xl sm:text-5xl font-bold">{{ $totalProperties }}</h3>
                        <p class="text-green-100 text-sm sm:text-lg">Property Listings</p>
                    </div>
                </div>
            </div>

            <div class="stat-card from-purple-500 to-purple-600 rounded-3xl p-6 sm:p-8 text-white shadow-2xl card-hover animate-slide-up sm:col-span-2 lg:col-span-1" style="animation-delay: 0.4s">
                <div class="flex items-center justify-between relative z-10">
                    <i class="fas fa-map-marked-alt text-4xl sm:text-5xl opacity-80"></i>
                    <div class="text-right">
                        <h3 class="text-4xl sm:text-5xl font-bold">{{ $office->city }}</h3>
                        <p class="text-purple-100 text-sm sm:text-lg">Service Area</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="glass-card rounded-2xl shadow-xl p-2 mb-8 animate-slide-up" style="animation-delay: 0.5s">
            <div class="flex flex-wrap gap-2">
                <button class="tab-button active flex-1 sm:flex-none px-4 sm:px-8 py-3 sm:py-4 rounded-xl font-semibold text-gray-700 hover:bg-gray-50 transition text-sm sm:text-base" 
                        onclick="switchTab('agents')">
                    <i class="fas fa-users mr-2"></i><span class="hidden sm:inline">Our Team </span>({{ $totalAgents }})
                </button>
                <button class="tab-button flex-1 sm:flex-none px-4 sm:px-8 py-3 sm:py-4 rounded-xl font-semibold text-gray-700 hover:bg-gray-50 transition text-sm sm:text-base" 
                        onclick="switchTab('properties')">
                    <i class="fas fa-home mr-2"></i><span class="hidden sm:inline">Properties </span>({{ $totalProperties }})
                </button>
                @if($office->office_address || ($office->latitude && $office->longitude))
                <button class="tab-button flex-1 sm:flex-none px-4 sm:px-8 py-3 sm:py-4 rounded-xl font-semibold text-gray-700 hover:bg-gray-50 transition text-sm sm:text-base" 
                        onclick="switchTab('location')">
                    <i class="fas fa-map-marker-alt mr-2"></i><span class="hidden sm:inline">Location</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Agents Tab -->
        <div id="agents-tab" class="tab-content">
            <div class="glass-card rounded-3xl shadow-2xl p-6 sm:p-10 animate-slide-up" style="animation-delay: 0.6s">
                <h2 class="text-3xl sm:text-4xl font-bold gradient-text mb-6 sm:mb-8 flex items-center">
                    <i class="fas fa-users mr-3"></i>Meet Our Expert Team
                </h2>
                
                <div id="agents-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($office->agents as $index => $agent)
                    <div class="card-hover glass-card rounded-2xl p-6 border border-gray-200 property-grid-item" 
                         style="animation-delay: {{ 0.1 * $index }}s">


           @php
    // Detect if stored value is a full URL or just a filename
    $image = null;

    if (!empty($agent->profile_image)) {
        if (filter_var($agent->profile_image, FILTER_VALIDATE_URL)) {
            // Full URL stored
            $image = $agent->profile_image;
        } else {
            // Local file stored inside /public/profile_images
            $image = asset('profile_images/' . $agent->profile_image);
        }
    }
@endphp
          <div class="flex items-center gap-4 sm:gap-5 mb-5">
  @if($agent->profile_image && file_exists(storage_path('app/public/'.$agent->profile_image)))
    <img 
        src="{{ asset('storage/'.$agent->profile_image) }}" 
        alt="{{ $agent->agent_name }}"
        class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shadow-lg flex-shrink-0"
    >
@else
    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-purple-400 via-purple-500 to-purple-600 flex items-center justify-center text-white text-xl sm:text-2xl font-bold shadow-lg flex-shrink-0">
        {{ substr($agent->agent_name, 0, 1) }}
    </div>
@endif

       

    <div class="min-w-0">
        <h3 class="font-bold text-lg sm:text-xl text-gray-800 truncate">{{ $agent->agent_name }}</h3>
        <p class="text-purple-600 font-medium text-sm sm:text-base">{{ $agent->type }}</p>
    </div>
</div>




                        <div class="space-y-3">
                            <a href="mailto:{{ $agent->primary_email }}" 
                               class="flex items-center text-gray-600 hover:text-purple-600 transition group">
                                <i class="fas fa-envelope text-purple-500 mr-3 w-5 group-hover:scale-110 transition"></i>
                                <span class="text-xs sm:text-sm truncate">{{ $agent->primary_email }}</span>
                            </a>
                            <a href="tel:{{ $agent->primary_phone }}" 
                               class="flex items-center text-gray-600 hover:text-purple-600 transition group">
                                <i class="fas fa-phone text-purple-500 mr-3 w-5 group-hover:scale-110 transition"></i>
                                <span class="text-xs sm:text-sm">{{ $agent->primary_phone }}</span>
                            </a>
                            <p class="flex items-center text-gray-600">
                                <i class="fas fa-map-marker-alt text-purple-500 mr-3 w-5"></i>
                                <span class="text-xs sm:text-sm">{{ $agent->city }}</span>
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($totalAgents > 6)
                <div class="text-center mt-8 sm:mt-10">
                    <button id="load-more-agents" 
                            data-offset="6" 
                            data-office-id="{{ $office->id }}"
                            class="bg-gradient-to-r from-purple-600 via-purple-700 to-purple-800 text-white px-8 sm:px-10 py-3 sm:py-4 rounded-xl hover:shadow-2xl transition-all transform hover:scale-105 font-semibold text-base sm:text-lg">
                        <i class="fas fa-chevron-down mr-2"></i>Show More Agents
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Properties Tab -->
        <div id="properties-tab" class="tab-content hidden">
            <div class="glass-card rounded-3xl shadow-2xl p-6 sm:p-10 animate-slide-up" style="animation-delay: 0.6s">
                <h2 class="text-3xl sm:text-4xl font-bold gradient-text mb-6 sm:mb-8 flex items-center">
                    <i class="fas fa-home mr-3"></i>Featured Properties
                </h2>
                
                <div id="properties-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($properties as $index => $property)
                    <div class="card-hover bg-white rounded-2xl overflow-hidden shadow-xl border border-gray-200 property-grid-item" 
                         style="animation-delay: {{ 0.1 * $index }}s">
                        <div class="relative h-56 sm:h-64 overflow-hidden group">
                            @if(!empty($property->images))
                                <img src="{{ $property->images[0] }}" 
                                     alt="{{ $property->name['en'] ?? 'Property' }}" 
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                    <i class="fas fa-home text-white text-5xl sm:text-6xl"></i>
                                </div>
                            @endif
                            <div class="image-overlay"></div>
                            <div class="absolute top-4 right-4 z-10">
                                <span class="bg-{{ $property->listing_type === 'rent' ? 'blue' : 'green' }}-500 text-white px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-bold shadow-lg">
                                    {{ ucfirst($property->listing_type) }}
                                </span>
                            </div>
                            @if($property->is_boosted)
                            <div class="absolute top-4 left-4 z-10">
                                <span class="bg-yellow-500 text-white px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-bold shadow-lg">
                                    <i class="fas fa-star mr-1"></i>Featured
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="p-5 sm:p-6">
                            <h3 class="font-bold text-xl sm:text-2xl text-gray-800 mb-3 truncate">
                                {{ $property->name['en'] ?? 'Property' }}
                            </h3>
                            <p class="text-gray-600 mb-4 flex items-center text-sm sm:text-base">
                                <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                <span class="truncate">{{ $property->address_details['city']['en'] ?? 'N/A' }}</span>
                            </p>
                            <div class="mb-4">
                                <span class="text-2xl sm:text-3xl font-bold gradient-text">
                                    ${{ number_format($property->price['usd']) }}
                                </span>
                                @if($property->listing_type === 'rent')
                                <span class="text-gray-500 text-xs sm:text-sm">/ {{ $property->rental_period ?? 'month' }}</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200 text-sm sm:text-base">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-bed text-purple-500 mr-1 sm:mr-2"></i>
                                    <span class="font-semibold">{{ $property->rooms['bedroom']['count'] }}</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-bath text-purple-500 mr-1 sm:mr-2"></i>
                                    <span class="font-semibold">{{ $property->rooms['bathroom']['count'] }}</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-ruler-combined text-purple-500 mr-1 sm:mr-2"></i>
                                    <span class="font-semibold">{{ $property->area }}m²</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($totalProperties > 9)
                <div class="text-center mt-8 sm:mt-10">
                    <button id="load-more-properties" 
                            data-offset="9" 
                            data-office-id="{{ $office->id }}"
                            class="bg-gradient-to-r from-green-600 via-green-700 to-green-800 text-white px-8 sm:px-10 py-3 sm:py-4 rounded-xl hover:shadow-2xl transition-all transform hover:scale-105 font-semibold text-base sm:text-lg">
                        <i class="fas fa-chevron-down mr-2"></i>Show More Properties
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Location Tab -->
        @if($office->office_address || ($office->latitude && $office->longitude))
        <div id="location-tab" class="tab-content hidden">
            <div class="glass-card rounded-3xl shadow-2xl p-6 sm:p-10 animate-slide-up" style="animation-delay: 0.6s">
                <h2 class="text-3xl sm:text-4xl font-bold gradient-text mb-6 sm:mb-8 flex items-center">
                    <i class="fas fa-map-marker-alt mr-3"></i>Our Location
                </h2>
                
                @if($office->office_address)
                <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-2xl p-6 sm:p-8 mb-6 sm:mb-8">
                    <p class="text-base sm:text-xl text-gray-700 flex items-start">
                        <i class="fas fa-location-dot text-purple-600 mr-3 sm:mr-4 mt-1 text-xl sm:text-2xl flex-shrink-0"></i>
                        <span class="flex-1">{{ $office->office_address }}</span>
                    </p>
                </div>
                @endif
                
                @if($office->latitude && $office->longitude)
                <div class="rounded-2xl overflow-hidden shadow-2xl h-64 sm:h-96">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        scrolling="no" 
                        marginheight="0" 
                        marginwidth="0" 
                        src="https://maps.google.com/maps?q={{ $office->latitude }},{{ $office->longitude }}&hl=en&z=15&output=embed">
                    </iframe>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Footer -->
    <footer class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white py-12 sm:py-16 mt-12 sm:mt-20">
        <div class="container mx-auto px-4 sm:px-6 text-center">
            <h3 class="text-2xl sm:text-3xl font-bold mb-4">{{ $office->company_name }}</h3>
            <p class="text-gray-400 mb-6 text-sm sm:text-base">Your trusted real estate partner</p>
            <div class="flex justify-center gap-6 sm:gap-8">
                <a href="mailto:{{ $office->email_address }}" 
                   class="hover:text-purple-400 transition transform hover:scale-110">
                    <i class="fas fa-envelope text-2xl sm:text-3xl"></i>
                </a>
                <a href="tel:{{ $office->phone_number }}" 
                   class="hover:text-purple-400 transition transform hover:scale-110">
                    <i class="fas fa-phone text-2xl sm:text-3xl"></i>
                </a>
                @if($office->latitude && $office->longitude)
                <a href="https://maps.google.com/maps?q={{ $office->latitude }},{{ $office->longitude }}" 
                   target="_blank"
                   class="hover:text-purple-400 transition transform hover:scale-110">
                    <i class="fas fa-map-marked-alt text-2xl sm:text-3xl"></i>
                </a>
                @endif
            </div>
            <p class="text-gray-500 text-xs sm:text-sm mt-8">© {{ date('Y') }} {{ $office->company_name }}. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Tab switching
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            event.target.closest('.tab-button').classList.add('active');
        }

        // Load more agents
        document.getElementById('load-more-agents')?.addEventListener('click', function() {
            const button = this;
            const offset = parseInt(button.dataset.offset);
            const officeId = button.dataset.officeId;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            
            fetch(`/office/${officeId}/agents/load-more?offset=${offset}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.agents.length > 0) {
                        const grid = document.getElementById('agents-grid');
                        
                        data.agents.forEach((agent, index) => {
                            const agentCard = document.createElement('div');
                            agentCard.className = 'card-hover glass-card rounded-2xl p-6 border border-gray-200 property-grid-item';
                            agentCard.style.animationDelay = `${0.1 * index}s`;
                            agentCard.innerHTML = `
                                <div class="flex items-center gap-4 sm:gap-5 mb-5">
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-purple-400 via-purple-500 to-purple-600 flex items-center justify-center text-white text-xl sm:text-2xl font-bold shadow-lg flex-shrink-0">
                                        ${agent.agent_name.charAt(0)}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-lg sm:text-xl text-gray-800 truncate">${agent.agent_name}</h3>
                                        <p class="text-purple-600 font-medium text-sm sm:text-base">${agent.type}</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <a href="mailto:${agent.primary_email}" class="flex items-center text-gray-600 hover:text-purple-600 transition group">
                                        <i class="fas fa-envelope text-purple-500 mr-3 w-5 group-hover:scale-110 transition"></i>
                                        <span class="text-xs sm:text-sm truncate">${agent.primary_email}</span>
                                    </a>
                                    <a href="tel:${agent.primary_phone}" class="flex items-center text-gray-600 hover:text-purple-600 transition group">
                                        <i class="fas fa-phone text-purple-500 mr-3 w-5 group-hover:scale-110 transition"></i>
                                        <span class="text-xs sm:text-sm">${agent.primary_phone}</span>
                                    </a>
                                    <p class="flex items-center text-gray-600">
                                        <i class="fas fa-map-marker-alt text-purple-500 mr-3 w-5"></i>
                                        <span class="text-xs sm:text-sm">${agent.city}</span>
                                    </p>
                                </div>
                            `;
                            grid.appendChild(agentCard);
                        });
                        
                        button.dataset.offset = offset + 6;
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-chevron-down mr-2"></i>Show More Agents';
                        
                        if (!data.hasMore) {
                            button.remove();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-chevron-down mr-2"></i>Show More Agents';
                });
        });

        // Load more properties
        document.getElementById('load-more-properties')?.addEventListener('click', function() {
            const button = this;
            const offset = parseInt(button.dataset.offset);
            const officeId = button.dataset.officeId;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            
            fetch(`/office/${officeId}/properties/load-more?offset=${offset}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.properties.length > 0) {
                        const grid = document.getElementById('properties-grid');
                        
                        data.properties.forEach((property, index) => {
                            const imageUrl = property.images && property.images.length > 0 ? property.images[0] : '';
                            const isBoosted = property.is_boosted || false;
                            const listingColor = property.listing_type === 'rent' ? 'blue' : 'green';
                            
                            const propertyCard = document.createElement('div');
                            propertyCard.className = 'card-hover bg-white rounded-2xl overflow-hidden shadow-xl border border-gray-200 property-grid-item';
                            propertyCard.style.animationDelay = `${0.1 * index}s`;
                            propertyCard.innerHTML = `
                                <div class="relative h-56 sm:h-64 overflow-hidden group">
                                    ${imageUrl ? 
                                        `<img src="${imageUrl}" alt="${property.name.en || 'Property'}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">` :
                                        `<div class="w-full h-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                            <i class="fas fa-home text-white text-5xl sm:text-6xl"></i>
                                        </div>`
                                    }
                                    <div class="image-overlay"></div>
                                    <div class="absolute top-4 right-4 z-10">
                                        <span class="bg-${listingColor}-500 text-white px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-bold shadow-lg">
                                            ${property.listing_type.charAt(0).toUpperCase() + property.listing_type.slice(1)}
                                        </span>
                                    </div>
                                    ${isBoosted ? 
                                        `<div class="absolute top-4 left-4 z-10">
                                            <span class="bg-yellow-500 text-white px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-bold shadow-lg">
                                                <i class="fas fa-star mr-1"></i>Featured
                                            </span>
                                        </div>` : ''
                                    }
                                </div>
                                <div class="p-5 sm:p-6">
                                    <h3 class="font-bold text-xl sm:text-2xl text-gray-800 mb-3 truncate">
                                        ${property.name.en || 'Property'}
                                    </h3>
                                    <p class="text-gray-600 mb-4 flex items-center text-sm sm:text-base">
                                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                        <span class="truncate">${property.address_details.city.en || 'N/A'}</span>
                                    </p>
                                    <div class="mb-4">
                                        <span class="text-2xl sm:text-3xl font-bold gradient-text">
                                            ${Number(property.price.usd).toLocaleString()}
                                        </span>
                                        ${property.listing_type === 'rent' ? 
                                            `<span class="text-gray-500 text-xs sm:text-sm">/ ${property.rental_period || 'month'}</span>` : ''
                                        }
                                    </div>
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 text-sm sm:text-base">
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-bed text-purple-500 mr-1 sm:mr-2"></i>
                                            <span class="font-semibold">${property.rooms.bedroom.count}</span>
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-bath text-purple-500 mr-1 sm:mr-2"></i>
                                            <span class="font-semibold">${property.rooms.bathroom.count}</span>
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-ruler-combined text-purple-500 mr-1 sm:mr-2"></i>
                                            <span class="font-semibold">${property.area}m²</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                            grid.appendChild(propertyCard);
                        });
                        
                        button.dataset.offset = offset + 9;
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-chevron-down mr-2"></i>Show More Properties';
                     
                        if (!data.hasMore) {
                            button.remove();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-chevron-down mr-2"></i>Show More Properties';
                });
        });

        // Smooth scroll for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add loading shimmer to images
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('load', function() {
                this.classList.add('animate-fade-in');
            });
        });
    </script>
</body>
</html>

