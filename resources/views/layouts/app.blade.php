<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PO CAN Travel - Tiket Bus Mewah & Terpercaya')</title>
    <meta name="description" content="Pesan tiket bus PO CAN Travel secara online. Pilihan bus Executive, Royal Suite, dan Sleeper Bus dengan fasilitas mewah dan rute terlengkap di Indonesia.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col antialiased selection:bg-brand-500 selection:text-white">

    <!-- Top Notification Bar -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-brand-500/20 text-brand-400 border border-brand-500/30">Official Portal</span>
                <span>✨ Nikmati Perjalanan Nyaman & Aman Bersama Armada Flagship PO CAN Travel</span>
            </div>
            <div class="flex items-center space-x-4 text-slate-400">
                <span>📞 Hotline 24 Jam: <strong class="text-white">0812-3456-7890</strong></span>
                <span class="hidden sm:inline">|</span>
                <span>Terminal Pulo Gebang & Giwangan</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-sm" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-brand-700 via-brand-600 to-sky-400 flex items-center justify-center text-white shadow-lg shadow-brand-500/25 group-hover:scale-105 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-8 5h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-2xl font-black tracking-tight text-slate-900 flex items-center">
                                PO CAN <span class="ml-1 text-brand-600 font-extrabold text-sm tracking-widest uppercase bg-brand-50 px-2 py-0.5 rounded border border-brand-200">TRAVEL</span>
                            </span>
                            <span class="text-[11px] font-medium text-slate-500 block -mt-1">Luxury & Intercity Bus Service</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-sm font-semibold transition-colors {{ request()->routeIs('home') ? 'text-brand-600' : 'text-slate-600 hover:text-slate-900' }}">
                        Beranda
                    </a>
                    <a href="{{ route('trips.index') }}" class="text-sm font-semibold transition-colors {{ request()->routeIs('trips.*') ? 'text-brand-600' : 'text-slate-600 hover:text-slate-900' }}">
                        Jadwal & Tiket
                    </a>
                    <a href="{{ route('home') }}#fleet" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                        Armada Bus
                    </a>
                    <a href="{{ route('home') }}#facilities" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                        Fasilitas
                    </a>
                    <a href="{{ route('home') }}#faq" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                        Bantuan
                    </a>
                </nav>

                <!-- User Auth Area -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm">
                                <svg class="w-4 h-4 mr-1.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Panel Admin
                            </a>
                        @endif

                        <!-- Customer Menu Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center space-x-2 p-1.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 transition">
                                <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-sm">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-semibold text-slate-700 max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-150" 
                                 x-transition:enter-start="opacity-0 scale-95" 
                                 x-transition:enter-end="opacity-100 scale-100" 
                                 x-transition:leave="transition ease-in duration-100" 
                                 x-transition:leave-start="opacity-100 scale-100" 
                                 x-transition:leave-end="opacity-0 scale-95" 
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 divide-y divide-slate-100"
                                 style="display: none;">
                                <div class="px-4 py-2.5">
                                    <p class="text-xs text-slate-400">Masuk sebagai</p>
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="py-1">
                                    <a href="{{ route('orders.index') }}" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 font-medium">
                                        <svg class="w-4 h-4 mr-2.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                        </svg>
                                        Tiket & Pesanan Saya
                                    </a>
                                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 font-medium">
                                        <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Profil Akun
                                    </a>
                                </div>
                                <div class="py-1">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 font-medium">
                                            <svg class="w-4 h-4 mr-2.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Keluar (Logout)
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-brand-600 px-3 py-2 transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 shadow-md shadow-brand-600/25 transition duration-200">
                            Daftar Sekarang
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen" class="md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-3" style="display: none;">
            <a href="{{ route('home') }}" class="block text-base font-semibold py-2 text-slate-700">Beranda</a>
            <a href="{{ route('trips.index') }}" class="block text-base font-semibold py-2 text-slate-700">Jadwal & Tiket</a>
            <a href="{{ route('home') }}#fleet" class="block text-base font-semibold py-2 text-slate-700">Armada Bus</a>
            <div class="pt-3 border-t border-slate-100 space-y-2">
                @auth
                    <a href="{{ route('orders.index') }}" class="block w-full text-center py-2.5 font-bold rounded-xl bg-brand-50 text-brand-700 border border-brand-200">Pesanan Saya</a>
                    <a href="{{ route('profile.edit') }}" class="block w-full text-center py-2 font-medium text-slate-600">Profil Saya</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block w-full text-center py-2.5 font-bold rounded-xl bg-slate-900 text-white">Panel Admin</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-center py-2 text-rose-600 font-medium">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block w-full text-center py-2.5 font-bold rounded-xl border border-slate-300 text-slate-700">Masuk</a>
                    <a href="{{ route('register') }}" class="block w-full text-center py-2.5 font-bold rounded-xl bg-brand-600 text-white">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Global Flash Notification Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-start space-x-3 shadow-sm mb-4 animate-fade-in" x-data="{ show: true }" x-show="show">
                <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1 font-medium text-sm">{{ session('success') }}</div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-rose-800 flex items-start space-x-3 shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1 font-medium text-sm">{{ session('error') }}</div>
                <button @click="show = false" class="text-rose-500 hover:text-rose-700">&times;</button>
            </div>
        @endif

        @if(session('info'))
            <div class="rounded-2xl bg-sky-50 border border-sky-200 p-4 text-sky-800 flex items-start space-x-3 shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                <svg class="w-5 h-5 text-sky-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1 font-medium text-sm">{{ session('info') }}</div>
                <button @click="show = false" class="text-sky-500 hover:text-sky-700">&times;</button>
            </div>
        @endif
    </div>

    <!-- Main Dynamic Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-12 border-t border-slate-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
                <!-- Brand Info -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold">
                            CAN
                        </div>
                        <span class="text-xl font-black text-white">PO CAN Travel</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Penyedia layanan transportasi bus antarkota dengan standar kenyamanan eksekutif, suite sleeper, dan ketepatan waktu terdepan di Indonesia.
                    </p>
                    <div class="flex items-center space-x-3 pt-2 text-slate-400">
                        <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center hover:text-white cursor-pointer transition">IG</span>
                        <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center hover:text-white cursor-pointer transition">FB</span>
                        <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center hover:text-white cursor-pointer transition">YT</span>
                        <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center hover:text-white cursor-pointer transition">WA</span>
                    </div>
                </div>

                <!-- Rute Populer -->
                <div>
                    <h4 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Rute Populer</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Yogyakarta']) }}" class="hover:text-white transition">Jakarta → Yogyakarta</a></li>
                        <li><a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Surabaya']) }}" class="hover:text-white transition">Jakarta → Surabaya</a></li>
                        <li><a href="{{ route('trips.index', ['origin' => 'Bandung', 'destination' => 'Solo']) }}" class="hover:text-white transition">Bandung → Solo</a></li>
                        <li><a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Semarang']) }}" class="hover:text-white transition">Jakarta → Semarang</a></li>
                        <li><a href="{{ route('trips.index', ['origin' => 'Yogyakarta', 'destination' => 'Jakarta']) }}" class="hover:text-white transition">Yogyakarta → Jakarta</a></li>
                    </ul>
                </div>

                <!-- Layanan & Kelas -->
                <div>
                    <h4 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Kelas Armada</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><span class="text-brand-400 font-semibold">CAN Royal Suite</span> (Kabin Mewah 2-2)</li>
                        <li><span class="text-brand-400 font-semibold">CAN Sleeper Dream</span> (Flat Bed 180°)</li>
                        <li><span class="text-brand-400 font-semibold">CAN Executive Grand</span> (Legrest Ekstra)</li>
                        <li><span class="text-brand-400 font-semibold">CAN VIP Line</span> (Keluarga & Rombongan)</li>
                    </ul>
                </div>

                <!-- Hubungi Kami -->
                <div>
                    <h4 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Pusat Layanan</h4>
                    <div class="space-y-3 text-sm text-slate-400">
                        <p class="flex items-start">
                            <span class="text-white font-semibold mr-2">Kantor:</span>
                            Jl. Raya Terminal Terpadu Pulo Gebang Lt. Mezzanine No. 12, Jakarta Timur
                        </p>
                        <p>
                            <span class="text-white font-semibold">Telepon:</span> 0812-3456-7890
                        </p>
                        <p>
                            <span class="text-white font-semibold">Email:</span> support@pocan.com
                        </p>
                        <div class="pt-2">
                            <span class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full text-xs font-semibold">
                                Pelayanan Buka 24 Jam
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} PO CAN Travel Inc. Seluruh hak cipta dilindungi undang-undang.</p>
                <div class="flex space-x-6">
                    <a href="#" class="hover:text-slate-400 transition">Syarat & Ketentuan</a>
                    <a href="#" class="hover:text-slate-400 transition">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-slate-400 transition">Pusat Bantuan</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
