<header class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">

        <!-- Left: Mobile Menu + Page Title -->
        <div class="flex items-center space-x-4">
            <button onclick="toggleSidebar()" class="lg:hidden text-gray-600 hover:text-gray-900">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                <p class="text-sm text-gray-500">@yield('page-description', 'Welcome back!')</p>
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center space-x-4">

            <!-- Notifications -->
            <button class="relative text-gray-600 hover:text-gray-900">
                <i class="fas fa-bell text-xl"></i>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">3</span>
            </button>

            <!-- Profile Dropdown -->
            <div class="relative">
                <button onclick="toggleDropdown('profile-dropdown')" class="flex items-center space-x-2">
                    <div class="w-10 h-10 gradient-primary rounded-full flex items-center justify-center text-white font-semibold">
                        {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                </button>
                <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                    <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-user mr-2"></i>Profile
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-cog mr-2"></i>Settings
                    </a>
                    <hr class="my-2">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>
