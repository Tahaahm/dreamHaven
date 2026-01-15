<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Dream Haven Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        :root {
            --primary: #303b97;
            --primary-light: #4b68ff;
            --primary-lighter: #6b7fff;
            --sidebar-bg: #1a1d2e;
            --sidebar-darker: #12141f;
        }

        /* Sidebar Gradient */
        .sidebar-dark {
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-darker) 100%);
        }

        /* Active Link Styling */
        .sidebar-link-active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            box-shadow: 0 4px 12px rgba(48, 59, 151, 0.4);
            color: white !important;
            border-right: 3px solid white;
        }

        .gradient-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }

        /* Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Animation */
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-gray-800">

    <div class="flex h-screen overflow-hidden bg-gray-50">

        <aside id="sidebar" class="w-64 sidebar-dark flex flex-col shadow-2xl transition-transform duration-300 -translate-x-full lg:translate-x-0 fixed lg:static h-full z-50">

            <div class="px-6 py-6 shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center shadow-lg ring-1 ring-white/10">
                        <i class="fas fa-shield-halved text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white tracking-wide">Dream Haven</h1>
                        <p class="text-xs text-gray-400 font-medium">Admin Panel</p>
                    </div>
                </div>
            </div>

            <div class="px-4 mb-2">
                <div class="relative">
                    <input type="text" id="menu-search" onkeyup="filterMenu()" placeholder="Search menu..."
                           class="w-full bg-gray-800/50 text-gray-300 text-xs rounded-lg pl-9 pr-3 py-2.5 border border-gray-700/50 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none transition placeholder-gray-500">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-500 text-xs"></i>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 overflow-y-auto custom-scrollbar space-y-1" id="sidebar-menu">

                <a href="{{ route('admin.dashboard') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-th-large w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Dashboard</span>
                </a>

                <div class="menu-label px-4 mt-6 mb-2 text-xs font-bold text-gray-500 uppercase tracking-wider">Management</div>

                <a href="{{ route('admin.users.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-users w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Users</span>
                </a>

                <a href="{{ route('admin.agents.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.agents.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-user-tie w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Agents</span>
                </a>

                <a href="{{ route('admin.offices.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.offices.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-building w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Offices</span>
                </a>

                <div class="menu-label px-4 mt-6 mb-2 text-xs font-bold text-gray-500 uppercase tracking-wider">Real Estate</div>

                <a href="{{ route('admin.properties.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.properties.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-home w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Properties</span>
                </a>

                <a href="{{ route('admin.projects.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.projects.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-city w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Projects</span>
                </a>

                <a href="{{ route('admin.appointments.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.appointments.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
    <i class="fas fa-calendar-check w-6 text-center group-hover:scale-110 transition-transform"></i>
    <span class="ml-2">Appointments</span>
</a>

                <a href="{{ route('admin.banners.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.banners.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-rectangle-ad w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Banners</span>
                </a>

                <div class="menu-label px-4 mt-6 mb-2 text-xs font-bold text-gray-500 uppercase tracking-wider">Finance</div>

                {{-- NEW: Subscription Plans Link --}}
                <a href="{{ route('admin.subscription-plans.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.subscription-plans.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-tags w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Plans</span>
                </a>

                <a href="{{ route('admin.subscriptions.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.subscriptions.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-credit-card w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Subscriptions</span>
                </a>

                <a href="{{ route('admin.transactions.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.transactions.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-money-bill-transfer w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Transactions</span>
                </a>

                <div class="menu-label px-4 mt-6 mb-2 text-xs font-bold text-gray-500 uppercase tracking-wider">System</div>

                <a href="{{ route('admin.settings.index') }}" class="menu-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="fas fa-cog w-6 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="ml-2">Settings</span>
                </a>

                <div id="no-menu-results" class="hidden px-4 py-6 text-center text-gray-500 text-xs">
                    No modules found
                </div>

            </nav>

            <div class="p-4 border-t border-gray-700/50 bg-gray-900/50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 gradient-primary rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md ring-2 ring-white/10">
                        {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0 overflow-hidden">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::guard('admin')->user()->name ?? 'Administrator' }}</p>
                        <a href="{{ route('admin.profile') }}" class="text-xs text-gray-400 hover:text-white truncate transition">View Profile</a>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-red-400 transition p-2" title="Logout">
                            <i class="fas fa-power-off"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden hidden transition-opacity" id="sidebar-overlay"></div>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <header class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm z-30">
                <div class="flex items-center justify-between gap-4">

                    <div class="flex items-center gap-4 flex-1">
                        <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-800 p-2 rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-bars text-xl"></i>
                        </button>

                        <div class="relative hidden md:block max-w-md w-full">
                            <input type="text" placeholder="Global search..." class="w-full pl-10 pr-4 py-2 bg-gray-100/50 border-0 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button class="relative p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition">
                            <i class="far fa-bell text-xl"></i>
                            <span class="absolute top-1.5 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                        </button>

                        <div class="relative">
                            <button onclick="toggleDropdown('header-profile-menu')" class="flex items-center gap-2 p-1.5 hover:bg-gray-100 rounded-lg transition border border-transparent hover:border-gray-200">
                                <img src="https://ui-avatars.com/api/?name={{ Auth::guard('admin')->user()->name ?? 'Admin' }}&background=random" class="w-8 h-8 rounded-full">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </button>

                            <div id="header-profile-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 animate-fade-in-up origin-top-right">
                                <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                    <p class="text-sm font-semibold text-gray-800">Signed in as</p>
                                    <p class="text-xs text-gray-500 truncate">{{ Auth::guard('admin')->user()->email ?? 'admin@example.com' }}</p>
                                </div>
                                <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">
                                    <i class="fas fa-user mr-2 w-4"></i> Profile
                                </a>
                                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">
                                    <i class="fas fa-cog mr-2 w-4"></i> Settings
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt mr-2 w-4"></i> Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-50 p-6 custom-scrollbar fade-in">

                @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center shrink-0">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-green-900 text-sm">Success</h4>
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center shrink-0">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-red-900 text-sm">Error</h4>
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // 1. Sidebar Toggle (Mobile)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // 2. Dropdown Toggle
        function toggleDropdown(id) {
            const el = document.getElementById(id);
            // Close others first (optional)
            document.querySelectorAll('[id$="-menu"]').forEach(menu => {
                if(menu.id !== id) menu.classList.add('hidden');
            });
            el.classList.toggle('hidden');
        }

        // 3. Search Filter Logic (The feature you requested)
        function filterMenu() {
            const input = document.getElementById('menu-search');
            const filter = input.value.toLowerCase();
            const items = document.querySelectorAll('.menu-item');
            const labels = document.querySelectorAll('.menu-label');
            let hasResults = false;

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(filter)) {
                    item.style.display = "flex";
                    hasResults = true;
                } else {
                    item.style.display = "none";
                }
            });

            // Hide labels if searching
            labels.forEach(label => {
                label.style.display = filter.length > 0 ? 'none' : 'block';
            });

            // Show 'No Results'
            const noRes = document.getElementById('no-menu-results');
            if (noRes) noRes.style.display = hasResults ? 'none' : 'block';
        }

        // 4. Click Outside to Close
        document.addEventListener('click', function(event) {
            // Close Dropdowns
            if (!event.target.closest('[onclick^="toggleDropdown"]')) {
                document.querySelectorAll('[id$="-menu"]:not(#sidebar-menu)').forEach(el => el.classList.add('hidden'));
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
