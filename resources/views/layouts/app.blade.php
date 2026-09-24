<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CAN Travel — Perjalanan Nyaman, Berangkat Tanpa Khawatir')</title>
    <meta name="description" content="@yield('meta_description', 'Pesan tiket bus CAN Travel online dengan mudah dan aman. Pilihan armada Executive dan Sleeper dengan fasilitas modern, denah kursi interaktif, dan e-tiket instan.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="theme-color" content="#062A52">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="CAN Travel">
    <meta property="og:title" content="@yield('title', 'CAN Travel — Perjalanan Nyaman, Berangkat Tanpa Khawatir')">
    <meta property="og:description" content="@yield('meta_description', 'Pesan tiket bus CAN Travel online dengan mudah dan aman. Pilihan armada Executive dan Sleeper dengan fasilitas modern, denah kursi interaktif, dan e-tiket instan.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/logo/can-travel-logo.png') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'CAN Travel — Perjalanan Nyaman, Berangkat Tanpa Khawatir')">
    <meta name="twitter:description" content="@yield('meta_description', 'Pesan tiket bus CAN Travel online dengan mudah dan aman.')">
    <meta name="twitter:image" content="{{ asset('images/logo/can-travel-logo.png') }}">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "TravelAgency",
      "name": "CAN Travel",
      "url": "{{ url('/') }}",
      "description": "Platform pemesanan tiket bus online resmi CAN Travel armada Executive & Sleeper bus.",
      "priceRange": "Rp 150.000 - Rp 500.000"
    }
    </script>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo/can-travel-logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Alpine.js (Strict CSP Compliant Build) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/csp@3.14.8/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans antialiased selection:bg-brand-600 selection:text-white">

    <!-- WCAG 2.1 AA Skip to Content Accessible Link -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:px-4 focus:py-2 focus:bg-brand-600 focus:text-white focus:font-bold focus:rounded-xl focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-white">
        Menuju ke Konten Utama
    </a>

    <!-- Top Announcement Bar -->
    <aside class="bg-navy-950 text-slate-300 text-xs py-2 px-4 border-b border-navy-900" aria-label="Informasi Layanan">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-accent-500/20 text-accent-400 border border-accent-500/30">
                    Resmi
                </span>
                <span>Portal Pemesanan Tiket Bus Online Terpercaya — <strong>CAN Travel</strong></span>
            </div>
            <div class="flex items-center space-x-4 text-slate-400 text-[11px] whitespace-nowrap">
                <span class="whitespace-nowrap">Pusat Bantuan: <strong class="text-white">0812-3456-7890</strong></span>
                <span class="hidden sm:inline">|</span>
                <span class="hidden sm:inline">support@cantravel.co.id</span>
            </div>
        </div>
    </aside>

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-sm" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">

                <!-- CAN Travel Brand Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="group focus:outline-none focus:ring-2 focus:ring-brand-500 rounded-xl" aria-label="Beranda CAN Travel">
                        <x-logo size="md" variant="dark" />
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-7" aria-label="Navigasi Utama">
                    <a href="{{ route('home') }}"
                        class="text-sm font-semibold transition-colors {{ request()->routeIs('home') ? 'text-brand-600 font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                        Beranda
                    </a>
                    <a href="{{ route('trips.index') }}"
                        class="text-sm font-semibold transition-colors {{ request()->routeIs('trips.*') ? 'text-brand-600 font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                        Jadwal & Tiket
                    </a>
                    <a href="{{ route('home') }}#fleet"
                        class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                        Armada
                    </a>
                    <a href="{{ route('home') }}#facilities"
                        class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                        Fasilitas
                    </a>
                    <a href="{{ route('home') }}#faq"
                        class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                        Bantuan
                    </a>
                </nav>

                <!-- User Auth Area -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}"
                                class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm">
                                <svg class="w-4 h-4 mr-1.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Admin Panel
                            </a>
                        @endif

                        <!-- Customer Dropdown Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button id="user-menu-btn" @click="open = !open" @click.away="open = false"
                                class="flex items-center space-x-2.5 p-1.5 pr-3 rounded-2xl border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 transition focus:outline-none focus:ring-2 focus:ring-brand-500"
                                :aria-expanded="open" aria-haspopup="true">
                                <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs shadow-inner">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-bold text-slate-800 max-w-[130px] truncate">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div id="user-menu-dropdown" x-show="open"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 divide-y divide-slate-100"
                                 style="display: none;" role="menu">
                                <div class="px-4 py-3">
                                    <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Akun Masuk</p>
                                    <p class="text-sm font-extrabold text-slate-900 truncate mt-0.5">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="py-1.5">
                                    <a href="{{ route('orders.index') }}" class="flex items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-600 font-medium transition" role="menuitem">
                                        <svg class="w-4 h-4 mr-3 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                        </svg>
                                        Pesanan Saya
                                    </a>
                                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-600 font-medium transition" role="menuitem">
                                        <svg class="w-4 h-4 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Profil Akun
                                    </a>
                                </div>
                                <div class="py-1">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 font-medium transition text-left" role="menuitem">
                                            <svg class="w-4 h-4 mr-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-slate-700 hover:text-brand-600 px-3 py-2 transition focus:outline-none focus:ring-2 focus:ring-brand-500 rounded-xl">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-md shadow-brand-600/25 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                            Daftar Sekarang
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button id="mobile-nav-toggle" @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500"
                        aria-label="Buka Menu Navigasi" :aria-expanded="mobileMenuOpen">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobile-nav-menu" x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden border-t border-slate-200 bg-white px-5 pt-4 pb-6 space-y-3"
             style="display: none;">
            <a href="{{ route('home') }}" class="block text-base font-bold py-2 text-slate-800 hover:text-brand-600">Beranda</a>
            <a href="{{ route('trips.index') }}" class="block text-base font-bold py-2 text-slate-800 hover:text-brand-600">Jadwal & Tiket</a>
            <a href="{{ route('home') }}#fleet" class="block text-base font-bold py-2 text-slate-800 hover:text-brand-600">Armada</a>
            <a href="{{ route('home') }}#facilities" class="block text-base font-bold py-2 text-slate-800 hover:text-brand-600">Fasilitas</a>
            <a href="{{ route('home') }}#faq" class="block text-base font-bold py-2 text-slate-800 hover:text-brand-600">Bantuan</a>

            <div class="pt-4 border-t border-slate-100 space-y-2.5">
                @auth
                    <div class="px-2 py-1 mb-2">
                        <span class="text-xs text-slate-400 block">Masuk sebagai</span>
                        <span class="text-sm font-extrabold text-slate-900 block truncate">{{ auth()->user()->name }}</span>
                    </div>
                    <a href="{{ route('orders.index') }}" class="block w-full text-center py-3 font-bold rounded-xl bg-brand-50 text-brand-700 border border-brand-200">
                        Pesanan Saya
                    </a>
                    <a href="{{ route('profile.edit') }}" class="block w-full text-center py-2.5 font-semibold text-slate-700 border border-slate-200 rounded-xl">
                        Profil Akun
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block w-full text-center py-2.5 font-bold rounded-xl bg-slate-900 text-white">
                            Admin Panel
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-center py-2.5 text-rose-600 font-bold hover:bg-rose-50 rounded-xl transition">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block w-full text-center py-3 font-bold rounded-xl border border-slate-300 text-slate-800 hover:bg-slate-50 transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="block w-full text-center py-3 font-bold rounded-xl bg-brand-600 text-white hover:bg-brand-700 shadow-md shadow-brand-600/25 transition">
                        Daftar Sekarang
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Global Flash Notification Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full" aria-live="polite">
        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-start space-x-3 shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1 font-semibold text-sm">{{ session('success') }}</div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none" aria-label="Tutup notifikasi">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-rose-800 flex items-start space-x-3 shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1 font-semibold text-sm">{{ session('error') }}</div>
                <button @click="show = false" class="text-rose-500 hover:text-rose-700 text-lg leading-none" aria-label="Tutup notifikasi">&times;</button>
            </div>
        @endif

        @if(session('info'))
            <div class="rounded-2xl bg-sky-50 border border-sky-200 p-4 text-sky-800 flex items-start space-x-3 shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                <svg class="w-5 h-5 text-sky-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1 font-semibold text-sm">{{ session('info') }}</div>
                <button @click="show = false" class="text-sky-500 hover:text-sky-700 text-lg leading-none" aria-label="Tutup notifikasi">&times;</button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1" id="main-content">
        @yield('content')
    </main>

    <!-- Professional Footer -->
    <footer class="bg-navy-900 text-slate-300 pt-16 pb-12 border-t border-navy-800 mt-20" aria-label="Footer Resmi CAN Travel">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-navy-800">

                <!-- Brand Info -->
                <div class="space-y-4">
                    <x-logo size="md" variant="light" />
                    <p class="text-sm text-slate-400 leading-relaxed mt-2">
                        Penyedia layanan transportasi bus antarkota dengan standar kenyamanan eksekutif, armada modern, dan jaminan ketepatan waktu terpercaya di Indonesia.
                    </p>
                    <div class="pt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2"></span>
                            Layanan Beroperasi 24 Jam
                        </span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h2 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Navigasi</h2>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a></li>
                        <li><a href="{{ route('trips.index') }}" class="hover:text-white transition">Jadwal & Tiket</a></li>
                        <li><a href="{{ route('home') }}#fleet" class="hover:text-white transition">Armada Bus</a></li>
                        <li><a href="{{ route('home') }}#facilities" class="hover:text-white transition">Fasilitas</a></li>
                        <li><a href="{{ route('home') }}#faq" class="hover:text-white transition">Bantuan & FAQ</a></li>
                    </ul>
                </div>

                <!-- Customer Links -->
                <div>
                    <h2 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Pelanggan</h2>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        <li><a href="{{ route('orders.index') }}" class="hover:text-white transition">Pesanan Saya</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="hover:text-white transition">Profil Akun</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Masuk Akun</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Daftar Akun Baru</a></li>
                    </ul>
                </div>

                <!-- Contact Info (Authentic Project Configuration) -->
                <div>
                    <h2 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Pusat Layanan</h2>
                    <div class="space-y-3 text-sm text-slate-400">
                        <p class="flex items-start">
                            <span class="text-white font-semibold mr-2 flex-shrink-0">Kantor:</span>
                            <span>Terminal Terpadu Pulo Gebang, Jakarta Timur</span>
                        </p>
                        <p>
                            <span class="text-white font-semibold mr-2">Telepon:</span>
                            <span class="text-slate-300">0812-3456-7890</span>
                        </p>
                        <p>
                            <span class="text-white font-semibold mr-2">Email:</span>
                            <span class="text-slate-300">support@cantravel.co.id</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-4">
                <p>&copy; 2026 CAN Travel. Seluruh hak cipta dilindungi undang-undang.</p>
                <div class="flex space-x-6">
                    <span class="text-slate-400">Pemesanan Tiket Bus Online Resmi</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Resilient mobile menu handler
        const mobileToggle = document.getElementById('mobile-nav-toggle');
        const mobileMenu = document.getElementById('mobile-nav-menu');
        if (mobileToggle && mobileMenu) {
            mobileToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const isHidden = window.getComputedStyle(mobileMenu).display === 'none';
                mobileMenu.style.display = isHidden ? 'block' : 'none';
                mobileToggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            });
        }

        // Resilient user dropdown menu handler
        const userBtn = document.getElementById('user-menu-btn');
        const userDropdown = document.getElementById('user-menu-dropdown');
        if (userBtn && userDropdown) {
            userBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isHidden = window.getComputedStyle(userDropdown).display === 'none';
                userDropdown.style.display = isHidden ? 'block' : 'none';
                userBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            });
            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target) && !userBtn.contains(e.target)) {
                    userDropdown.style.display = 'none';
                    userBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
    </script>
    @stack('scripts')
</body>
</html>
