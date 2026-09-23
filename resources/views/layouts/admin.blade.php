<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel — CAN Travel')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Alpine.js (Strict CSP Compliant Build) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/csp@3.14.8/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased min-h-screen flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Backdrop -->
    <div id="admin-mobile-backdrop"
         x-show="sidebarOpen"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm md:hidden"
         style="display: none;"></div>

    <!-- Admin Sidebar -->
    <aside id="admin-sidebar"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:flex md:flex-col md:w-64 border-r border-slate-800 -translate-x-full md:translate-x-0">

        <!-- Sidebar Brand -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800 bg-slate-950">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                <x-logo size="sm" variant="light" :withSubtitle="false" />
            </a>
            <button id="admin-sidebar-close" @click="sidebarOpen = false" class="text-slate-400 hover:text-white md:hidden text-2xl" aria-label="Tutup Menu">
                &times;
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3 mb-2">Menu Utama</div>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('admin.orders.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.orders.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                Kelola Pesanan
            </a>

            <a href="{{ route('admin.trips.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.trips.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Jadwal Perjalanan
            </a>

            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3 pt-5 pb-2">Master Data</div>

            <a href="{{ route('admin.buses.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.buses.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-8 5h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                </svg>
                Armada Bus
            </a>

            <a href="{{ route('admin.routes.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.routes.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Rute Perjalanan
            </a>

            <a href="{{ route('admin.customers.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.customers.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Pelanggan
            </a>

            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3 pt-5 pb-2">Keandalan & Audit</div>

            <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.audit-logs.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Audit Log
            </a>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800">
            <a href="{{ route('home') }}" target="_blank" class="flex items-center justify-center w-full py-2.5 px-3 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                <svg class="w-4 h-4 mr-1.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Kunjungi Website
            </a>
        </div>
    </aside>

    <!-- Main Admin Body -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Admin Top Navigation Bar -->
        <header class="h-20 bg-white border-b border-slate-200 px-6 sm:px-8 flex items-center justify-between shadow-sm sticky top-0 z-30">
            <div class="flex items-center">
                <button id="admin-sidebar-open" @click="sidebarOpen = true" class="text-slate-600 hover:text-slate-900 md:hidden mr-4" aria-label="Buka Menu Sidebar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">@yield('page_title', 'Dashboard')</h1>
                    <p class="text-xs text-slate-500">@yield('page_subtitle', 'Sistem Manajemen CAN Travel')</p>
                </div>
            </div>

            <!-- Admin Profile & Logout -->
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-slate-800">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-brand-600 font-semibold uppercase tracking-wider">Super Administrator</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition" title="Logout" aria-label="Keluar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-6 sm:px-8 pt-4">
            @if(session('success'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm font-medium mb-4 flex items-center justify-between shadow-sm">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500" aria-label="Tutup notifikasi">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-rose-800 text-sm font-medium mb-4 flex items-center justify-between shadow-sm">
                    <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-rose-500" aria-label="Tutup notifikasi">&times;</button>
                </div>
            @endif
        </div>

        <!-- Content Area -->
        <main class="flex-1 p-6 sm:p-8">
            @yield('content')
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('admin-sidebar');
        const openBtn = document.getElementById('admin-sidebar-open');
        const closeBtn = document.getElementById('admin-sidebar-close');
        const backdrop = document.getElementById('admin-mobile-backdrop');

        function openSidebar() {
            if (sidebar) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
            }
            if (backdrop) {
                backdrop.style.display = 'block';
            }
        }

        function closeSidebar() {
            if (sidebar) {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
            }
            if (backdrop) {
                backdrop.style.display = 'none';
            }
        }

        if (openBtn) openBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (backdrop) backdrop.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSidebar();
        });
    });
    </script>
    @stack('scripts')
</body>
</html>
