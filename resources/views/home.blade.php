@extends('layouts.app')

@section('title', 'CAN Travel — Perjalanan Nyaman, Berangkat Tanpa Khawatir')
@section('meta_description', 'Pesan tiket bus CAN Travel online dengan mudah dan cepat. Pilihan armada bus Executive dan Sleeper dengan fasilitas modern, pemilihan kursi real-time, dan e-tiket instan.')

@section('content')
<!-- Hero Section with Scenic Bus Highway Landscape -->
<section class="relative text-white pt-8 sm:pt-14 pb-36 sm:pb-44 overflow-hidden isolate">
    <!-- Cinematic Scenic Bus Highway Background Image -->
    <div class="absolute inset-0 -z-20 bg-navy-950">
        <img src="{{ asset('images/hero-scenic-bus.jpg') }}"
             alt="Perjalanan Bus CAN Travel di Jalur Tol Trans Jawa"
             class="w-full h-full object-cover object-[center_35%] filter brightness-[0.9] scale-[1.01]"
             loading="eager"
             fetchpriority="high">
        <!-- Multi-layer Rich Dark Cinematic Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-navy-950/85 via-navy-950/75 to-navy-950/95"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,transparent_30%,#041B35_90%)] opacity-80"></div>
    </div>

    <!-- Subtle Background Ambient Glow -->
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute top-1/2 -left-32 w-96 h-96 bg-accent-500/15 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-14">
            <!-- Official Brand Logo Presentation in Hero -->
            <div class="mb-5 inline-flex items-center">
                <x-logo size="xl" variant="light" />
            </div>

            <div class="block">
                <div class="inline-flex items-center space-x-2 px-4 py-1.5 rounded-full bg-accent-500/20 border border-accent-400/40 text-accent-300 text-xs font-bold uppercase tracking-wider mb-6 backdrop-blur-md shadow-md">
                    <span class="w-2 h-2 rounded-full bg-accent-400 animate-pulse"></span>
                    <span>Platform Pemesanan Tiket Bus Online Resmi</span>
                </div>
            </div>
            
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-tight drop-shadow-lg">
                Perjalanan Nyaman,<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent-400 via-sky-300 to-white">
                    Berangkat Tanpa Khawatir.
                </span>
            </h1>
            
            <p class="mt-4 sm:mt-5 text-sm sm:text-lg text-slate-200 leading-relaxed max-w-2xl mx-auto font-normal drop-shadow-sm">
                Nikmati standar baru perjalanan bus antarkota dengan CAN Travel. Armada modern, denah kursi interaktif real-time, dan jaminan ketepatan waktu rute Trans Jawa.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 sm:gap-4 w-full sm:w-auto max-w-xs sm:max-w-none mx-auto">
                <a href="#search-card" class="w-full sm:w-auto text-center px-8 py-3.5 rounded-xl font-black text-sm bg-gradient-to-r from-accent-400 via-accent-500 to-accent-600 hover:from-accent-500 hover:to-accent-600 text-navy-950 shadow-xl shadow-accent-500/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-150">
                    Cari Tiket Sekarang
                </a>
                <a href="{{ route('trips.index') }}" class="w-full sm:w-auto text-center px-8 py-3.5 rounded-xl font-bold text-sm bg-white/10 hover:bg-white/20 text-white border border-white/25 backdrop-blur-md shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all duration-150">
                    Lihat Semua Jadwal
                </a>
            </div>

            <!-- Trust Highlights Badges Under Hero CTA -->
            <div class="mt-10 pt-6 border-t border-white/15 grid grid-cols-2 sm:grid-cols-4 gap-3 text-left">
                <div class="flex items-center space-x-2 text-slate-200 text-xs">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="font-medium">100% Kursi Real-Time</span>
                </div>
                <div class="flex items-center space-x-2 text-slate-200 text-xs">
                    <span class="w-6 h-6 rounded-lg bg-accent-500/20 text-accent-400 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                    <span class="font-medium">E-Tiket QR Instan</span>
                </div>
                <div class="flex items-center space-x-2 text-slate-200 text-xs">
                    <span class="w-6 h-6 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                    <span class="font-medium">Jaminan Tepat Waktu</span>
                </div>
                <div class="flex items-center space-x-2 text-slate-200 text-xs">
                    <span class="w-6 h-6 rounded-lg bg-brand-500/20 text-brand-300 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </span>
                    <span class="font-medium">Bus Nyaman & Aman</span>
                </div>
            </div>
        </div>

        <!-- Search Ticket Card -->
        <div id="search-card" class="bg-navy-900/85 backdrop-blur-xl rounded-3xl p-5 sm:p-8 shadow-2xl shadow-navy-950/80 border border-white/20 text-white max-w-5xl mx-auto -mb-24 sm:-mb-32 relative z-30 transition-all">
            <div class="mb-5 pb-4 border-b border-white/15 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-500/20 text-brand-300 border border-brand-400/30 flex items-center justify-center font-black shadow-inner">
                        <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-white tracking-tight">Cari Jadwal Perjalanan Bus</h2>
                        <p class="text-xs text-slate-300 hidden sm:block">Pilih rute antarkota dan temukan waktu keberangkatan terbaik</p>
                    </div>
                </div>
                <span class="inline-flex items-center text-xs font-bold text-emerald-300 bg-emerald-500/20 px-3 py-1 rounded-full border border-emerald-400/40">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                    Konfirmasi Langsung &bull; E-Tiket Instan
                </span>
            </div>

            <form action="{{ route('trips.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Origin -->
                <div>
                    <label for="origin-select" class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-1.5 flex items-center">
                        <svg class="w-3.5 h-3.5 text-sky-400 mr-1.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Kota Asal</span>
                    </label>
                    <div class="relative">
                        <select id="origin-select" name="origin" class="w-full px-3.5 py-3 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-accent-400 focus:border-accent-400 focus:outline-none transition shadow-sm">
                            <option value="">Semua Kota Asal</option>
                            @foreach($origins as $orig)
                                <option value="{{ $orig }}">{{ $orig }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Destination -->
                <div>
                    <label for="dest-select" class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-1.5 flex items-center">
                        <svg class="w-3.5 h-3.5 text-emerald-400 mr-1.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Kota Tujuan</span>
                    </label>
                    <div class="relative">
                        <select id="dest-select" name="destination" class="w-full px-3.5 py-3 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-accent-400 focus:border-accent-400 focus:outline-none transition shadow-sm">
                            <option value="">Semua Kota Tujuan</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest }}">{{ $dest }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Date -->
                <div>
                    <label for="date-input" class="block text-xs font-bold uppercase tracking-wider text-slate-200 mb-1.5 flex items-center">
                        <svg class="w-3.5 h-3.5 text-amber-400 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Tanggal Berangkat</span>
                    </label>
                    <input id="date-input" type="date" name="date" value="" min="{{ date('Y-m-d') }}"
                        class="w-full px-3.5 py-3 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-accent-400 focus:border-accent-400 focus:outline-none transition shadow-sm">
                </div>

                <!-- Search CTA Button -->
                <div>
                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-sm font-black text-navy-950 bg-gradient-to-r from-accent-400 via-accent-500 to-accent-600 hover:from-accent-500 hover:to-accent-600 shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:scale-[1.01] active:scale-[0.99] transition flex items-center justify-center space-x-2 min-h-[46px]">
                        <svg class="w-4 h-4 text-navy-950" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Cari Tiket</span>
                    </button>
                </div>
            </form>

            <!-- Quick Route Suggestions -->
            <div class="mt-5 pt-4 border-t border-white/15 flex flex-wrap items-center gap-2 text-xs">
                <span class="font-bold text-amber-300 flex items-center">
                    <svg class="w-3.5 h-3.5 text-amber-400 mr-1.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span>Rute Populer:</span>
                </span>
                <a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Yogyakarta']) }}" class="px-3 py-1 rounded-lg bg-white/90 hover:bg-white text-slate-800 hover:text-brand-700 transition font-semibold border border-white/40 shadow-sm">Jakarta → Yogyakarta</a>
                <a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Surabaya']) }}" class="px-3 py-1 rounded-lg bg-white/90 hover:bg-white text-slate-800 hover:text-brand-700 transition font-semibold border border-white/40 shadow-sm">Jakarta → Surabaya</a>
                <a href="{{ route('trips.index', ['origin' => 'Bandung', 'destination' => 'Solo']) }}" class="px-3 py-1 rounded-lg bg-white/90 hover:bg-white text-slate-800 hover:text-brand-700 transition font-semibold border border-white/40 shadow-sm">Bandung → Solo</a>
                <a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Semarang']) }}" class="px-3 py-1 rounded-lg bg-white/90 hover:bg-white text-slate-800 hover:text-brand-700 transition font-semibold border border-white/40 shadow-sm">Jakarta → Semarang</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Trips / Upcoming Schedules -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-36 sm:pt-44 pb-16">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
        <div>
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Jadwal Keberangkatan Terdekat</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Jadwal Pilihan Hari Ini & Besok</h2>
        </div>
        <a href="{{ route('trips.index') }}" class="inline-flex items-center text-sm font-bold text-brand-600 hover:text-brand-700 group">
            <span>Lihat Semua Jadwal</span>
            <svg class="w-4 h-4 ml-1.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($upcomingTrips as $trip)
            <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-7 border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <!-- Header with Bus Class & Trip Code -->
                    <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                {{ $trip->bus->type }}
                            </span>
                            <span class="text-xs text-slate-400 font-mono">{{ $trip->trip_code }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400 block font-medium">Mulai dari</span>
                            <span class="text-lg font-black text-brand-700">{{ $trip->formatted_price }}</span>
                        </div>
                    </div>

                    <!-- Origin to Destination Route Info -->
                    <div class="py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keberangkatan</span>
                                <div class="text-xl sm:text-2xl font-black text-slate-900">{{ $trip->departure_at->format('H:i') }} <span class="text-xs font-semibold text-slate-500">WIB</span></div>
                                <div class="text-xs font-bold text-slate-700 truncate max-w-[90px] xs:max-w-[120px] sm:max-w-[150px] mt-0.5" title="{{ $trip->route->origin }}">{{ $trip->route->origin }}</div>
                                <div class="text-[10px] sm:text-[11px] text-slate-400">{{ $trip->departure_at->translatedFormat('d M Y') }}</div>
                            </div>

                            <div class="flex flex-col items-center px-1.5 sm:px-4">
                                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full mb-1">
                                    {{ $trip->route->estimated_duration ?: 'Langsung' }}
                                </span>
                                <div class="w-14 sm:w-28 h-0.5 bg-slate-200 relative flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-brand-600 absolute -left-1"></div>
                                    <svg class="w-3.5 h-3.5 text-brand-600 bg-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 absolute -right-1"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1">{{ $trip->route->distance }}</span>
                            </div>

                            <div class="text-right">
                                <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kedatangan</span>
                                <div class="text-xl sm:text-2xl font-black text-slate-900">{{ $trip->arrival_at->format('H:i') }} <span class="text-xs font-semibold text-slate-500">WIB</span></div>
                                <div class="text-xs font-bold text-slate-700 truncate max-w-[90px] xs:max-w-[120px] sm:max-w-[150px] mt-0.5" title="{{ $trip->route->destination }}">{{ $trip->route->destination }}</div>
                                <div class="text-[10px] sm:text-[11px] text-slate-400">{{ $trip->arrival_at->translatedFormat('d M Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Bus Name & Availability Indicator -->
                    <div class="bg-slate-50 rounded-2xl p-3.5 flex items-center justify-between text-xs text-slate-600 mb-5">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-slate-800">{{ $trip->bus->name }}</span>
                        </div>
                        <div class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                            {{ $trip->available_seats_count }} Kursi Tersedia
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div>
                    <a href="{{ route('trips.show', $trip) }}" class="w-full flex items-center justify-center py-3 rounded-xl bg-slate-900 group-hover:bg-brand-600 text-white font-bold text-sm transition shadow-sm">
                        <span>Pilih Jadwal & Kursi</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12 bg-white rounded-3xl border border-slate-200">
                <p class="text-slate-500 font-medium">Belum ada jadwal keberangkatan aktif saat ini.</p>
            </div>
        @endforelse
    </div>
</section>

<!-- Why Choose CAN Travel -->
<section class="py-20 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Keunggulan Layanan</span>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Mengapa Memilih CAN Travel?</h2>
            <p class="text-slate-600 text-sm mt-3">Komitmen kami adalah memberikan pengalaman perjalanan darat yang aman, tepat waktu, dan bermutu tinggi.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="p-7 rounded-3xl bg-slate-50 border border-slate-100 hover:border-brand-200 transition">
                <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center mb-5 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Jaminan Tepat Waktu</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Jadwal keberangkatan terkoordinasi rapi via jalur tol bebas hambatan dengan pemantauan perjalanan berkala.
                </p>
            </div>

            <div class="p-7 rounded-3xl bg-slate-50 border border-slate-100 hover:border-brand-200 transition">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-5 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Armada Terawat & Aman</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Setiap bus menjalani pra-inspeksi kelaikan sebelum beroperasi, dikemudikan oleh pengemudi profesional berlisensi.
                </p>
            </div>

            <div class="p-7 rounded-3xl bg-slate-50 border border-slate-100 hover:border-brand-200 transition">
                <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center mb-5 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Pilih Kursi Real-Time</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Pilih nomor kursi favorit langsung pada denah kabin bus interaktif dengan konfirmasi ketersediaan instan.
                </p>
            </div>

            <div class="p-7 rounded-3xl bg-slate-50 border border-slate-100 hover:border-brand-200 transition">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mb-5 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">E-Tiket Digital Instan</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Tiket resmi langsung terbit di akun dan dapat ditunjukkan lewat smartphone tanpa perlu mencetak kertas fisik.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Fleet Showcase Section -->
<section id="fleet" class="bg-slate-100/70 py-20 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Pilihan Armada</span>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Armada Bus Modern & Mewah</h2>
            <p class="text-slate-600 text-sm mt-3">CAN Travel mengoperasikan armada bus bersuspensi udara dengan konfigurasi kursi ergonomis untuk memastikan kenyamanan sepanjang rute.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($buses as $bus)
                @php
                    $busImageMap = [
                        'Royal Suite' => 'images/buses/royal-suite.jpg',
                        'Executive' => 'images/buses/executive-grand.jpg',
                        'Sleeper Bus' => 'images/buses/sleeper-dream.jpg',
                        'VIP' => 'images/buses/vip-line.jpg',
                    ];
                    $imageRelPath = $busImageMap[$bus->type] ?? 'images/buses/executive-grand.jpg';
                    $hasLocalImage = file_exists(public_path($imageRelPath));
                    $bgImageUrl = $hasLocalImage ? asset($imageRelPath) : null;
                @endphp
                <div class="relative overflow-hidden rounded-3xl p-6 border border-slate-700/80 shadow-sm hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between min-h-[460px] isolate bg-navy-950">
                    <!-- Background Bus Photo with Dark Gradient Overlay -->
                    <div class="absolute inset-0 -z-10 bg-navy-950">
                        @if($bgImageUrl)
                            <img src="{{ $bgImageUrl }}" alt="{{ $bus->name }}" class="w-full h-full object-cover opacity-70 transition-transform duration-700 group-hover:scale-105" loading="lazy">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/85 to-navy-900/40 group-hover:via-navy-950/80 transition-all duration-300"></div>
                    </div>

                    <!-- Card Body Content -->
                    <div class="flex flex-col h-full justify-between z-10">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-accent-500/20 text-accent-300 border border-accent-500/30 backdrop-blur-md shadow-sm">
                                    {{ $bus->type }}
                                </span>
                                <span class="text-xs font-bold text-slate-200 bg-white/10 backdrop-blur-md px-2.5 py-1 rounded-lg border border-white/20 shadow-sm">
                                    {{ $bus->seat_capacity }} Kursi
                                </span>
                            </div>
                            <h3 class="text-xl font-black text-white mb-2 drop-shadow-md">{{ $bus->name }}</h3>
                            <p class="text-xs text-slate-300 leading-relaxed mb-4 drop-shadow-sm">{{ Str::limit($bus->description, 100) }}</p>

                            <div class="space-y-1.5 pt-3.5 border-t border-white/15">
                                <p class="text-[11px] font-bold text-accent-400 uppercase tracking-wider mb-2">Fasilitas Kabin:</p>
                                @if(is_array($bus->facilities))
                                    @foreach(array_slice($bus->facilities, 0, 4) as $facility)
                                        <div class="flex items-center text-xs text-slate-200 font-medium drop-shadow-sm">
                                            <svg class="w-3.5 h-3.5 mr-2 text-accent-400 flex-shrink-0 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="truncate">{{ $facility }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-white/15">
                            <a href="{{ route('trips.index', ['bus_type' => $bus->type]) }}" class="block text-center py-2.5 px-3 rounded-xl bg-white/10 hover:bg-brand-600 text-white text-xs font-bold border border-white/20 hover:border-brand-500 transition-all duration-300 backdrop-blur-md shadow-md group-hover:shadow-brand-500/25">
                                Cari Jadwal Kelas Ini
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Facilities Section -->
<section id="facilities" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Fasilitas Penumpang</span>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Standar Kenyamanan di Setiap Perjalanan</h2>
            <p class="text-slate-600 text-sm mt-3">Nikmati kelengkapan fasilitas kabin yang dirancang untuk mendukung perjalanan jarak jauh Anda tetap santai dan menyenangkan.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Kursi Reclining Ergonomis</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Sudut kemiringan kursi yang dapat diatur serta ruang kaki lega untuk kenyamanan istirahat maksimal.</p>
            </div>

            <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Port USB Charging</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Port pengisi daya ponsel di setiap baris kursi untuk menjaga gadget Anda tetap aktif sepanjang perjalanan.</p>
            </div>

            <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Koneksi Wi-Fi Onboard</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Akses internet gratis di dalam kabin bus untuk browsing, komunikasi, dan hiburan perjalanan.</p>
            </div>

            <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Pendingin Udara (AC) Sentral</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Suhu kabin bus terjaga stabil dan sejuk dengan kisi ventilasi AC individual di atas setiap kursi.</p>
            </div>

            <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Bagasi Luas & Aman</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Kompartemen bagasi bawah yang aman dan terproteksi untuk koper, tas, serta barang bawaan Anda.</p>
            </div>

            <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Lampu Baca & Selimut</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Fasilitas pendukung membaca di malam hari serta selimut higienis untuk perjalanan malam hari.</p>
            </div>
        </div>
    </div>
</section>

<!-- How Booking Works -->
<section class="py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Cara Pemesanan</span>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">4 Langkah Mudah Pesan Tiket</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="relative bg-white p-7 rounded-3xl border border-slate-200 shadow-sm text-center">
                <div class="w-10 h-10 rounded-full bg-brand-600 text-white font-black text-sm flex items-center justify-center mx-auto mb-4">
                    01
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1.5">Cari Rute & Jadwal</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Tentukan kota keberangkatan, kota tujuan, dan tanggal perjalanan yang Anda inginkan.</p>
            </div>

            <div class="relative bg-white p-7 rounded-3xl border border-slate-200 shadow-sm text-center">
                <div class="w-10 h-10 rounded-full bg-brand-600 text-white font-black text-sm flex items-center justify-center mx-auto mb-4">
                    02
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1.5">Pilih Kursi Favorit</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Lihat denah kursi kabin bus dan pilih nomor kursi yang tersedia secara real-time.</p>
            </div>

            <div class="relative bg-white p-7 rounded-3xl border border-slate-200 shadow-sm text-center">
                <div class="w-10 h-10 rounded-full bg-brand-600 text-white font-black text-sm flex items-center justify-center mx-auto mb-4">
                    03
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1.5">Data Penumpang</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Lengkapi nama penumpang sesuai identitas KTP dan nomor telepon aktif untuk e-tiket.</p>
            </div>

            <div class="relative bg-white p-7 rounded-3xl border border-slate-200 shadow-sm text-center">
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-black text-sm flex items-center justify-center mx-auto mb-4">
                    04
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1.5">E-Tiket Terbit</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Selesaikan pembayaran dan e-tiket langsung aktif dan siap digunakan untuk boarding.</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="py-20 bg-white border-t border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Pertanyaan Umum</span>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Pusat Bantuan & FAQ</h2>
            <p class="text-slate-600 text-xs sm:text-sm mt-2">Temukan jawaban seputar pemesanan tiket, armada bus, dan ketentuan perjalanan di CAN Travel.</p>
        </div>

        @php
            $faqs = [
                [
                    'id' => 1,
                    'question' => 'Bagaimana cara memesan tiket bus di CAN Travel?',
                    'answer' => 'Pilih rute kota asal, tujuan, dan tanggal perjalanan pada form pencarian. Tentukan jadwal bus yang diinginkan, pilih nomor kursi favorit pada denah kabin interaktif, lengkapi data identitas penumpang, lalu selesaikan pembayaran. E-tiket instan akan langsung terbit di akun Anda.',
                ],
                [
                    'id' => 2,
                    'question' => 'Apakah saya wajib mencetak tiket fisik di terminal?',
                    'answer' => 'Tidak perlu mencetak tiket fisik. Cukup tunjukkan E-Tiket digital melalui menu "Pesanan Saya" pada smartphone Anda kepada kru atau petugas loket CAN Travel saat boarding di terminal maupun pool keberangkatan.',
                ],
                [
                    'id' => 3,
                    'question' => 'Metode pembayaran apa saja yang didukung?',
                    'answer' => 'CAN Travel mendukung berbagai metode pembayaran resmi termasuk Transfer Bank Virtual Account (BCA, Mandiri, BRI, BNI), QRIS instan, dan dompet digital (GoPay, OVO, DANA, ShopeePay). Pada mode pengujian sistem, tersedia simulasi bayar instan untuk memverifikasi alur e-tiket secara langsung.',
                ],
                [
                    'id' => 4,
                    'question' => 'Bagaimana cara memilih kursi?',
                    'answer' => 'Pada halaman detail perjalanan, denah kabin bus akan menampilkan status kursi secara real-time. Kursi yang tersedia dapat langsung diklik untuk dipilih, sedangkan kursi yang sudah terisi ditandai berwarna abu-abu dan tidak dapat dipilih.',
                ],
                [
                    'id' => 5,
                    'question' => 'Berapa lama batas waktu pembayaran?',
                    'answer' => 'Batas waktu penyelesaian transaksi adalah 2 jam sejak pesanan dibuat. Jika pembayaran tidak diselesaikan sebelum batas waktu berakhir, pesanan akan kedaluwarsa secara otomatis dan kursi yang ditahan akan dilepas kembali untuk umum.',
                ],
                [
                    'id' => 6,
                    'question' => 'Bagaimana jika pembayaran saya gagal?',
                    'answer' => 'Jika pembayaran gagal atau waktu transaksi kedaluwarsa, status pesanan akan menjadi batal dan kursi akan otomatis dirilis kembali. Anda dapat melakukan pemesanan ulang dengan memilih jadwal dan nomor kursi yang masih tersedia.',
                ],
                [
                    'id' => 7,
                    'question' => 'Bagaimana cara melihat tiket yang sudah dibeli?',
                    'answer' => 'Semua e-tiket resmi yang berhasil dipesan tersimpan rapi di akun Anda. Setelah masuk ke akun, klik menu "Pesanan Saya" di navigasi atas untuk melihat ringkasan tiket, kode booking, dan rincian perjalanan Anda.',
                ],
                [
                    'id' => 8,
                    'question' => 'Apakah tiket dapat dibatalkan atau diubah?',
                    'answer' => 'Pembatalan mandiri oleh penumpang hanya dapat dilakukan untuk pesanan yang belum dibayar (status pending). Untuk tiket yang sudah berstatus lunas atau permohonan perubahan jadwal, silakan hubungi pusat bantuan pelanggan CAN Travel sesuai ketentuan operasional yang berlaku.',
                ],
            ];
        @endphp

        <div class="space-y-3.5" id="faq-accordion">
            @foreach($faqs as $faq)
                <div class="faq-card rounded-2xl border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60 transition-all duration-200">
                    <button type="button"
                        aria-expanded="false"
                        aria-controls="faq-answer-{{ $faq['id'] }}"
                        id="faq-btn-{{ $faq['id'] }}"
                        class="faq-btn w-full text-left p-5 flex justify-between items-center focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 rounded-2xl group transition-colors">
                        <span class="faq-question font-bold text-sm sm:text-base pr-4 text-slate-800 group-hover:text-brand-600 transition-colors">
                            {{ $faq['question'] }}
                        </span>
                        <span class="faq-icon-badge w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 bg-slate-100 text-slate-500 group-hover:bg-brand-50 group-hover:text-brand-600 transition-all duration-200">
                            <!-- Closed Icon: Plus -->
                            <svg class="faq-icon-plus w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <!-- Open Icon: Minus -->
                            <svg class="faq-icon-minus w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                            </svg>
                        </span>
                    </button>
                    <div id="faq-answer-{{ $faq['id'] }}"
                        role="region"
                        aria-labelledby="faq-btn-{{ $faq['id'] }}"
                        class="faq-content">
                        <div>
                            <div class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-200/50">
                                {{ $faq['answer'] }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-16 bg-gradient-to-r from-brand-700 via-brand-600 to-navy-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-black tracking-tight">Siap Melakukan Perjalanan Nyaman?</h2>
        <p class="mt-2 text-sm sm:text-base text-brand-100 max-w-xl mx-auto">
            Pesan tiket Anda sekarang dan nikmati pengalaman bepergian antarkota yang aman dan terpercaya bersama CAN Travel.
        </p>
        <div class="mt-8 flex justify-center space-x-4">
            <a href="{{ route('trips.index') }}" class="px-7 py-3.5 rounded-xl font-bold text-sm bg-white text-brand-700 hover:bg-brand-50 shadow-lg shadow-black/10 transition">
                Pilih Jadwal & Kursi
            </a>
        </div>
    </div>
</section>

<!-- Partner & Kolaborasi Section -->
<section class="py-14 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Jejaring Ekosistem</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Partner & Kolaborasi</h2>
            <p class="text-slate-600 text-xs sm:text-sm mt-2">CAN Travel berkolaborasi bersama mitra strategis untuk menghadirkan standar mutu perjalanan, infrastruktur digital terpercaya, dan keamanan berkendara yang konsisten.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- 1. Mitra Transportasi -->
            <div class="group p-5 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-brand-300 hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-200/80 group-hover:bg-brand-50 text-slate-400 group-hover:text-brand-600 flex items-center justify-center mb-3 transition-all duration-300 filter grayscale group-hover:grayscale-0 group-hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-brand-700 transition-colors">Mitra Transportasi</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Koordinasi rute terminal & kelaikan armada antarkota.</p>
            </div>

            <!-- 2. Partner Teknologi -->
            <div class="group p-5 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-brand-300 hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-200/80 group-hover:bg-brand-50 text-slate-400 group-hover:text-brand-600 flex items-center justify-center mb-3 transition-all duration-300 filter grayscale group-hover:grayscale-0 group-hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-brand-700 transition-colors">Partner Teknologi</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Sistem tiket terenkripsi & ketersediaan server cloud 24/7.</p>
            </div>

            <!-- 3. Partner Pembayaran -->
            <div class="group p-5 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-brand-300 hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-200/80 group-hover:bg-brand-50 text-slate-400 group-hover:text-brand-600 flex items-center justify-center mb-3 transition-all duration-300 filter grayscale group-hover:grayscale-0 group-hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-brand-700 transition-colors">Partner Pembayaran</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Jaringan gerbang transaksi resmi & rekonsiliasi instan.</p>
            </div>

            <!-- 4. Mitra Perjalanan -->
            <div class="group p-5 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-brand-300 hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-200/80 group-hover:bg-brand-50 text-slate-400 group-hover:text-brand-600 flex items-center justify-center mb-3 transition-all duration-300 filter grayscale group-hover:grayscale-0 group-hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-brand-700 transition-colors">Mitra Perjalanan</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Jejaring rest area transit rute Trans Jawa & titik temu penumpang.</p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const faqContainer = document.getElementById('faq-accordion');
    if (!faqContainer) return;

    const cards = faqContainer.querySelectorAll('.faq-card');

    cards.forEach(function (card) {
        const btn = card.querySelector('.faq-btn');
        const content = card.querySelector('.faq-content');
        const questionText = card.querySelector('.faq-question');
        const iconBadge = card.querySelector('.faq-icon-badge');

        if (!btn || !content) return;

        btn.addEventListener('click', function () {
            const isCurrentlyOpen = btn.getAttribute('aria-expanded') === 'true';

            // Single-open accordion: close all other items first
            cards.forEach(function (otherCard) {
                const otherBtn = otherCard.querySelector('.faq-btn');
                const otherContent = otherCard.querySelector('.faq-content');
                const otherQuestion = otherCard.querySelector('.faq-question');
                const otherBadge = otherCard.querySelector('.faq-icon-badge');

                if (otherBtn && otherContent) {
                    otherBtn.setAttribute('aria-expanded', 'false');
                    otherContent.classList.remove('is-open');
                    otherCard.classList.remove('border-brand-500', 'bg-brand-50/40', 'shadow-sm');
                    otherCard.classList.add('border-slate-200', 'bg-white');
                    if (otherQuestion) {
                        otherQuestion.classList.remove('text-brand-800');
                        otherQuestion.classList.add('text-slate-800');
                    }
                    if (otherBadge) {
                        otherBadge.classList.remove('bg-brand-600', 'text-white');
                        otherBadge.classList.add('bg-slate-100', 'text-slate-500');
                    }
                }
            });

            // If it was closed, open it now
            if (!isCurrentlyOpen) {
                btn.setAttribute('aria-expanded', 'true');
                content.classList.add('is-open');
                card.classList.add('border-brand-500', 'bg-brand-50/40', 'shadow-sm');
                card.classList.remove('border-slate-200', 'bg-white');
                if (questionText) {
                    questionText.classList.add('text-brand-800');
                    questionText.classList.remove('text-slate-800');
                }
                if (iconBadge) {
                    iconBadge.classList.add('bg-brand-600', 'text-white');
                    iconBadge.classList.remove('bg-slate-100', 'text-slate-500');
                }
            }
        });
    });
});
</script>
@endpush
