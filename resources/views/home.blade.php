@extends('layouts.app')

@section('title', 'CAN Travel — Perjalanan Nyaman, Berangkat Tanpa Khawatir')
@section('meta_description', 'Pesan tiket bus CAN Travel online dengan mudah dan cepat. Pilihan armada bus Executive dan Sleeper dengan fasilitas modern, pemilihan kursi real-time, dan e-tiket instan.')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-b from-navy-950 via-navy-900 to-brand-950 text-white pt-14 pb-32 overflow-hidden">
    <!-- Subtle Background Ambient Light -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 -left-40 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(#0B3A70_1px,transparent_1px)] [background-size:24px_24px] opacity-25 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <!-- Official Brand Logo Presentation in Hero -->
            <div class="mb-5 inline-flex items-center">
                <x-logo size="xl" variant="light" />
            </div>

            <div class="block">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-accent-500/15 border border-accent-500/30 text-accent-400 text-xs font-bold uppercase tracking-wider mb-6">
                    <span class="w-2 h-2 rounded-full bg-accent-400 animate-pulse"></span>
                    <span>Platform Pemesanan Tiket Bus Online Resmi</span>
                </div>
            </div>
            
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-tight">
                Perjalanan Nyaman,<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent-400 via-sky-300 to-white">
                    Berangkat Tanpa Khawatir.
                </span>
            </h1>
            
            <p class="mt-5 text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                Nikmati standar baru perjalanan bus antarkota dengan CAN Travel. Armada modern, denah kursi interaktif real-time, dan jaminan ketepatan waktu rute Trans Jawa.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="#search-card" class="px-7 py-3.5 rounded-xl font-black text-sm bg-gradient-to-r from-accent-400 via-accent-500 to-accent-600 hover:from-accent-500 hover:to-accent-600 text-navy-950 shadow-lg shadow-accent-500/25 transition duration-150">
                    Cari Tiket Sekarang
                </a>
                <a href="{{ route('trips.index') }}" class="px-7 py-3.5 rounded-xl font-bold text-sm bg-white/10 hover:bg-white/20 text-white border border-white/20 transition duration-150">
                    Lihat Semua Jadwal
                </a>
            </div>
        </div>

        <!-- Search Ticket Card -->
        <div id="search-card" class="bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 text-slate-900 max-w-5xl mx-auto -mb-20 relative z-20">
            <div class="mb-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-accent-500"></span>
                    <h2 class="text-base font-extrabold text-slate-900">Cari Jadwal Perjalanan Bus</h2>
                </div>
                <span class="text-xs font-semibold text-slate-500 hidden sm:inline">Konfirmasi Langsung &bull; E-Tiket Instan</span>
            </div>

            <form action="{{ route('trips.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Origin -->
                <div>
                    <label for="origin-select" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Kota Asal
                    </label>
                    <div class="relative">
                        <select id="origin-select" name="origin" class="w-full px-3.5 py-3 rounded-xl border border-slate-300 bg-slate-50 hover:bg-white text-slate-800 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                            <option value="">Semua Kota Asal</option>
                            @foreach($origins as $orig)
                                <option value="{{ $orig }}">{{ $orig }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Destination -->
                <div>
                    <label for="dest-select" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Kota Tujuan
                    </label>
                    <div class="relative">
                        <select id="dest-select" name="destination" class="w-full px-3.5 py-3 rounded-xl border border-slate-300 bg-slate-50 hover:bg-white text-slate-800 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                            <option value="">Semua Kota Tujuan</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest }}">{{ $dest }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Date -->
                <div>
                    <label for="date-input" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Tanggal Berangkat
                    </label>
                    <input id="date-input" type="date" name="date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" 
                        class="w-full px-3.5 py-3 rounded-xl border border-slate-300 bg-slate-50 hover:bg-white text-slate-800 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                </div>

                <!-- Search CTA Button -->
                <div>
                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-sm font-black text-navy-950 bg-gradient-to-r from-accent-400 via-accent-500 to-accent-600 hover:from-accent-500 hover:to-accent-600 shadow-lg shadow-accent-500/25 transition flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 text-navy-950" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Cari Tiket</span>
                    </button>
                </div>
            </form>

            <!-- Quick Route Suggestions -->
            <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span class="font-bold text-slate-700">Rute Populer:</span>
                <a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Yogyakarta']) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 transition font-medium">Jakarta → Yogyakarta</a>
                <a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Surabaya']) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 transition font-medium">Jakarta → Surabaya</a>
                <a href="{{ route('trips.index', ['origin' => 'Bandung', 'destination' => 'Solo']) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 transition font-medium">Bandung → Solo</a>
                <a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Semarang']) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 transition font-medium">Jakarta → Semarang</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Trips / Upcoming Schedules -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-16">
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
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
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
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keberangkatan</span>
                                <div class="text-2xl font-black text-slate-900">{{ $trip->departure_at->format('H:i') }} <span class="text-xs font-semibold text-slate-500">WIB</span></div>
                                <div class="text-xs font-bold text-slate-700 truncate max-w-[150px] mt-0.5">{{ $trip->route->origin }}</div>
                                <div class="text-[11px] text-slate-400">{{ $trip->departure_at->translatedFormat('d M Y') }}</div>
                            </div>

                            <div class="flex flex-col items-center px-4">
                                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full mb-1">
                                    {{ $trip->route->estimated_duration ?: 'Langsung' }}
                                </span>
                                <div class="w-24 sm:w-32 h-0.5 bg-slate-200 relative flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-brand-600 absolute -left-1"></div>
                                    <svg class="w-3.5 h-3.5 text-brand-600 bg-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 absolute -right-1"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1">{{ $trip->route->distance }}</span>
                            </div>

                            <div class="text-right">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kedatangan</span>
                                <div class="text-2xl font-black text-slate-900">{{ $trip->arrival_at->format('H:i') }} <span class="text-xs font-semibold text-slate-500">WIB</span></div>
                                <div class="text-xs font-bold text-slate-700 truncate max-w-[150px] mt-0.5">{{ $trip->route->destination }}</div>
                                <div class="text-[11px] text-slate-400">{{ $trip->arrival_at->translatedFormat('d M Y') }}</div>
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
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                {{ $bus->type }}
                            </span>
                            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                                {{ $bus->seat_capacity }} Kursi
                            </span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 mb-1.5">{{ $bus->name }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ Str::limit($bus->description, 100) }}</p>

                        <div class="space-y-1.5 pt-3 border-t border-slate-100">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Fasilitas Kabin:</p>
                            @if(is_array($bus->facilities))
                                @foreach(array_slice($bus->facilities, 0, 4) as $facility)
                                    <div class="flex items-center text-xs text-slate-700 font-medium">
                                        <svg class="w-3.5 h-3.5 mr-2 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="truncate">{{ $facility }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <a href="{{ route('trips.index', ['bus_type' => $bus->type]) }}" class="block text-center py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-brand-50 text-brand-700 hover:text-brand-800 text-xs font-bold border border-slate-200 hover:border-brand-200 transition">
                            Cari Jadwal Kelas Ini
                        </a>
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
        </div>

        <div class="space-y-4" x-data="{ active: null }">
            <div class="bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden">
                <button @click="active = active === 1 ? null : 1" class="w-full text-left p-5 font-bold text-slate-800 flex justify-between items-center focus:outline-none">
                    <span>Bagaimana cara memesan tiket bus di CAN Travel?</span>
                    <span class="text-slate-400 font-bold text-lg" x-text="active === 1 ? '−' : '+'"></span>
                </button>
                <div x-show="active === 1" class="px-5 pb-5 text-sm text-slate-600 leading-relaxed border-t border-slate-200/60 pt-3" style="display: none;">
                    Pilih kota asal, tujuan, dan tanggal keberangkatan pada kolom pencarian. Pilih jadwal bus yang sesuai, tentukan kursi di denah kabin bus interaktif, isi data penumpang, lalu konfirmasi pesanan. E-tiket instan akan langsung terbit di akun Anda.
                </div>
            </div>

            <div class="bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden">
                <button @click="active = active === 2 ? null : 2" class="w-full text-left p-5 font-bold text-slate-800 flex justify-between items-center focus:outline-none">
                    <span>Apakah saya wajib mencetak tiket fisik di terminal?</span>
                    <span class="text-slate-400 font-bold text-lg" x-text="active === 2 ? '−' : '+'"></span>
                </button>
                <div x-show="active === 2" class="px-5 pb-5 text-sm text-slate-600 leading-relaxed border-t border-slate-200/60 pt-3" style="display: none;">
                    Tidak perlu mencetak fisik. Cukup tunjukkan E-Tiket di menu "Pesanan Saya" pada smartphone Anda kepada kru atau petugas loket CAN Travel saat boarding di terminal/pool keberangkatan.
                </div>
            </div>

            <div class="bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden">
                <button @click="active = active === 3 ? null : 3" class="w-full text-left p-5 font-bold text-slate-800 flex justify-between items-center focus:outline-none">
                    <span>Metode pembayaran apa saja yang didukung?</span>
                    <span class="text-slate-400 font-bold text-lg" x-text="active === 3 ? '−' : '+'"></span>
                </button>
                <div x-show="active === 3" class="px-5 pb-5 text-sm text-slate-600 leading-relaxed border-t border-slate-200/60 pt-3" style="display: none;">
                    Kami mendukung Bank Transfer Virtual Account otomatis serta QRIS instan. Pada mode pengujian sistem ini, tersedia tombol simulasi bayar instan untuk memverifikasi alur e-tiket secara langsung.
                </div>
            </div>
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
@endsection
