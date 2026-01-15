<aside id="sidebar" class="group/sidebar flex flex-col h-screen bg-gray-900 border-r border-gray-800 transition-all duration-300 ease-in-out w-64 fixed lg:static z-50">

    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800 shrink-0">
        <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/20">
                <i class="fas fa-shield-halved text-sm"></i>
            </div>
            <span class="font-bold text-gray-100 tracking-wide text-lg opacity-100 group-[.collapsed]/sidebar:opacity-0 transition-opacity duration-300">
                AdminPanel
            </span>
        </div>

        <button onclick="toggleSidebarSize()" class="text-gray-500 hover:text-white transition p-1 rounded-md hidden lg:block">
            <i class="fas fa-chevron-left group-[.collapsed]/sidebar:rotate-180 transition-transform duration-300"></i>
        </button>
    </div>

    <div class="p-4 shrink-0 group-[.collapsed]/sidebar:hidden">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
            <input type="text"
                   id="sidebar-search"
                   onkeyup="filterSidebar()"
                   placeholder="Search route..."
                   class="w-full bg-gray-800/50 text-gray-300 text-sm rounded-lg pl-9 pr-3 py-2 border border-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none transition placeholder-gray-600"
            >
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-3 space-y-1 scrollbar-thin scrollbar-thumb-gray-800">

        <div class="mb-6 sidebar-group">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 group-[.collapsed]/sidebar:hidden">Overview</p>

            <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-all group relative {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600/10 text-indigo-400' : '' }}">
                <div class="{{ request()->routeIs('admin.dashboard') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }} w-6 text-center text-lg transition-colors">
                    <i class="fas fa-grid-2"></i> </div>
                <span class="whitespace-nowrap group-[.collapsed]/sidebar:hidden">Dashboard</span>

                <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 hidden group-[.collapsed]/sidebar:block pointer-events-none whitespace-nowrap z-50 border border-gray-700">
                    Dashboard
                </span>
            </a>
        </div>

        <div class="mb-6 sidebar-group">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 group-[.collapsed]/sidebar:hidden">Management</p>

            <a href="{{ route('admin.users.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-all group relative {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600/10 text-indigo-400' : '' }}">
                <div class="{{ request()->routeIs('admin.users.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }} w-6 text-center text-lg">
                    <i class="fas fa-users"></i>
                </div>
                <span class="whitespace-nowrap group-[.collapsed]/sidebar:hidden">Users</span>
                 <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 hidden group-[.collapsed]/sidebar:block z-50 border border-gray-700">Users</span>
            </a>

            <a href="{{ route('admin.agents.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-all group relative {{ request()->routeIs('admin.agents.*') ? 'bg-indigo-600/10 text-indigo-400' : '' }}">
                <div class="{{ request()->routeIs('admin.agents.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }} w-6 text-center text-lg">
                    <i class="fas fa-user-tie"></i>
                </div>
                <span class="whitespace-nowrap group-[.collapsed]/sidebar:hidden">Agents</span>
                <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 hidden group-[.collapsed]/sidebar:block z-50 border border-gray-700">Agents</span>
            </a>

             <a href="{{ route('admin.offices.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-all group relative {{ request()->routeIs('admin.offices.*') ? 'bg-indigo-600/10 text-indigo-400' : '' }}">
                <div class="{{ request()->routeIs('admin.offices.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }} w-6 text-center text-lg">
                    <i class="fas fa-building"></i>
                </div>
                <span class="whitespace-nowrap group-[.collapsed]/sidebar:hidden">Offices</span>
                <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 hidden group-[.collapsed]/sidebar:block z-50 border border-gray-700">Offices</span>
            </a>
        </div>

        <div class="mb-6 sidebar-group">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 group-[.collapsed]/sidebar:hidden">Assets</p>

            <a href="{{ route('admin.properties.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-all group relative {{ request()->routeIs('admin.properties.*') ? 'bg-indigo-600/10 text-indigo-400' : '' }}">
                <div class="{{ request()->routeIs('admin.properties.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }} w-6 text-center text-lg">
                    <i class="fas fa-house-chimney"></i>
                </div>
                <span class="whitespace-nowrap group-[.collapsed]/sidebar:hidden">Properties</span>
                <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 hidden group-[.collapsed]/sidebar:block z-50 border border-gray-700">Properties</span>
            </a>

            <a href="{{ route('admin.projects.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-all group relative {{ request()->routeIs('admin.projects.*') ? 'bg-indigo-600/10 text-indigo-400' : '' }}">
                <div class="{{ request()->routeIs('admin.projects.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }} w-6 text-center text-lg">
                    <i class="fas fa-city"></i>
                </div>
                <span class="whitespace-nowrap group-[.collapsed]/sidebar:hidden">Projects</span>
                <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 hidden group-[.collapsed]/sidebar:block z-50 border border-gray-700">Projects</span>
            </a>

             <a href="{{ route('admin.banners.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-all group relative {{ request()->routeIs('admin.banners.*') ? 'bg-indigo-600/10 text-indigo-400' : '' }}">
                <div class="{{ request()->routeIs('admin.banners.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }} w-6 text-center text-lg">
                    <i class="fas fa-rectangle-ad"></i>
                </div>
                <span class="whitespace-nowrap group-[.collapsed]/sidebar:hidden">Banners</span>
                <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 hidden group-[.collapsed]/sidebar:block z-50 border border-gray-700">Banners</span>
            </a>
        </div>

        <div class="mb-6 sidebar-group">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 group-[.collapsed]/sidebar:hidden">System</p>
             <a href="{{ route('admin.settings.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-all group relative {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-600/10 text-indigo-400' : '' }}">
                <div class="{{ request()->routeIs('admin.settings.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }} w-6 text-center text-lg">
                    <i class="fas fa-cog"></i>
                </div>
                <span class="whitespace-nowrap group-[.collapsed]/sidebar:hidden">Settings</span>
                <span class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 hidden group-[.collapsed]/sidebar:block z-50 border border-gray-700">Settings</span>
            </a>
        </div>

        <div id="no-results" class="hidden px-4 py-8 text-center">
            <p class="text-gray-500 text-sm">No routes found</p>
        </div>

    </nav>

    <div class="p-3 border-t border-gray-800 shrink-0">
        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 cursor-pointer transition group/profile">
            <div class="w-9 h-9 rounded-full bg-gradient-to-r from-purple-500 to-indigo-500 flex items-center justify-center text-white font-semibold text-sm shadow-md">
                {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="overflow-hidden group-[.collapsed]/sidebar:hidden">
                <p class="text-sm font-medium text-gray-200 truncate">{{ Auth::guard('admin')->user()->name ?? 'Administrator' }}</p>
                <p class="text-xs text-gray-500 truncate">View Profile</p>
            </div>
            <button class="ml-auto text-gray-500 hover:text-red-400 group-[.collapsed]/sidebar:hidden">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>
</aside>

<script>
    // 1. Toggle Sidebar Size
    function toggleSidebarSize() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
        sidebar.classList.toggle('w-64');
        sidebar.classList.toggle('w-20');

        // Save preference if needed
        // localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
    }

    // 2. Filter Sidebar Links
    function filterSidebar() {
        const input = document.getElementById('sidebar-search');
        const filter = input.value.toLowerCase();
        const groups = document.querySelectorAll('.sidebar-group');
        const items = document.querySelectorAll('.sidebar-item');
        let hasResults = false;

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(filter)) {
                item.style.display = ""; // Show
                hasResults = true;
            } else {
                item.style.display = "none"; // Hide
            }
        });

        // Hide empty groups headers
        groups.forEach(group => {
            const visibleItems = group.querySelectorAll('.sidebar-item[style=""]'); // Items without display:none
            // Note: In standard DOM usage, checking visible items requires a loop or specific check
            // Simplified logic: If we have a filter, we might just hide the Group Headers to clean up the look
            const groupHeader = group.querySelector('p');
            if(filter.length > 0) {
                 if(groupHeader) groupHeader.style.display = 'none';
            } else {
                 if(groupHeader) groupHeader.style.display = '';
            }
        });

        // Show/Hide "No Results"
        document.getElementById('no-results').classList.toggle('hidden', hasResults);
    }
</script>
