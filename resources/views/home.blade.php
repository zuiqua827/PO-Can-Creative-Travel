@extends('layouts.app')

@section('title', 'PO CAN Travel - Tiket Bus Mewah & Terpercaya')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-b from-slate-900 via-slate-900 to-navy-950 text-white pt-12 pb-28 overflow-hidden">
    <!-- Background Glows & Patterns -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 -left-40 w-96 h-96 bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:20px_20px] opacity-25"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-10">
            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/30 text-brand-300 text-xs font-semibold uppercase tracking-wider mb-5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Pemesanan Tiket Online Resmi & Terpercaya</span>
            </div>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-tight">
                Jelajahi Indonesia Bersama <br class="hidden sm:inline">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 via-sky-300 to-brand-200">
                    PO CAN Travel
                </span>
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto">
                Kenyamanan maksimal kelas Royal Suite, Sleeper Flat Bed, dan Executive dengan fasilitas audio visual, reclining seat lega, dan jaminan jadwal tepat waktu.
            </p>
        </div>

        <!-- Search Form Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 text-slate-900 max-w-5xl mx-auto -mb-16 transform transition">
            <form action="{{ route('trips.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Origin -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-brand-500 mr-1"></span> Kota Keberangkatan
                    </label>
                    <div class="relative">
                        <select name="origin" class="w-full pl-3 pr-8 py-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-800 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                            <option value="">Semua Keberangkatan</option>
                            @foreach($origins as $orig)
                                <option value="{{ $orig }}">{{ $orig }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Destination -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span> Kota Tujuan
                    </label>
                    <div class="relative">
                        <select name="destination" class="w-full pl-3 pr-8 py-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-800 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                            <option value="">Semua Kota Tujuan</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest }}">{{ $dest }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-amber-500 mr-1"></span> Tanggal Berangkat
                    </label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" 
                        class="w-full px-3 py-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-800 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                </div>

                <!-- Search CTA Button -->
                <div>
                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 via-brand-700 to-navy-900 hover:from-brand-500 hover:to-brand-600 shadow-lg shadow-brand-600/30 transition flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Cari Perjalanan</span>
                    </button>
                </div>
            </form>

            <!-- Quick Route Suggestions -->
            <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span class="font-bold text-slate-700">Rute Cepat:</span>
                <a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Yogyakarta']) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 transition font-medium">Jakarta → Yogyakarta</a>
                <a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Surabaya']) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 transition font-medium">Jakarta → Surabaya</a>
                <a href="{{ route('trips.index', ['origin' => 'Bandung', 'destination' => 'Solo']) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 transition font-medium">Bandung → Solo</a>
                <a href="{{ route('trips.index', ['origin' => 'Jakarta', 'destination' => 'Semarang']) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 transition font-medium">Jakarta → Semarang</a>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Schedules Spotlight -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-16">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
        <div>
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Jadwal Keberangkatan Terdekat</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Perjalanan Populer Hari Ini & Besok</h2>
        </div>
        <a href="{{ route('trips.index') }}" class="inline-flex items-center text-sm font-bold text-brand-600 hover:text-brand-700 group">
            <span>Lihat Semua Jadwal</span>
            <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($upcomingTrips as $trip)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
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
                            <span class="text-xs text-slate-400 block">Mulai dari</span>
                            <span class="text-lg font-black text-brand-700">{{ $trip->formatted_price }}</span>
                        </div>
                    </div>

                    <!-- Origin to Destination Route Info -->
                    <div class="py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Berangkat</span>
                                <div class="text-xl font-black text-slate-900">{{ $trip->departure_at->format('H:i') }} WIB</div>
                                <div class="text-xs font-semibold text-slate-600 truncate max-w-[160px]">{{ $trip->route->origin }}</div>
                                <div class="text-[11px] text-slate-400">{{ $trip->departure_at->translatedFormat('d M Y') }}</div>
                            </div>

                            <div class="flex flex-col items-center px-4">
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full mb-1">
                                    {{ $trip->route->estimated_duration ?: 'Langsung' }}
                                </span>
                                <div class="w-24 sm:w-32 h-0.5 bg-slate-200 relative flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-brand-500 absolute -left-1"></div>
                                    <svg class="w-4 h-4 text-brand-600 bg-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 absolute -right-1"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1">{{ $trip->route->distance }}</span>
                            </div>

                            <div class="text-right">
                                <span class="text-xs font-bold text-slate-400 uppercase">Tiba</span>
                                <div class="text-xl font-black text-slate-900">{{ $trip->arrival_at->format('H:i') }} WIB</div>
                                <div class="text-xs font-semibold text-slate-600 truncate max-w-[160px]">{{ $trip->route->destination }}</div>
                                <div class="text-[11px] text-slate-400">{{ $trip->arrival_at->translatedFormat('d M Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Bus Name & Facilities Preview -->
                    <div class="bg-slate-50 rounded-2xl p-3.5 flex items-center justify-between text-xs text-slate-600 mb-4">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-slate-800">{{ $trip->bus->name }}</span>
                        </div>
                        <div class="font-semibold text-brand-600">
                            {{ $trip->available_seats_count }} Kursi Tersedia
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div>
                    <a href="{{ route('trips.show', $trip) }}" class="w-full flex items-center justify-center py-3 rounded-xl bg-slate-900 group-hover:bg-brand-600 text-white font-bold text-sm transition shadow-sm">
                        <span>Pilih Jadwal & Kursi</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12 bg-white rounded-3xl border border-slate-200">
                <p class="text-slate-500 font-medium">Belum ada jadwal terdekat saat ini.</p>
            </div>
        @endforelse
    </div>
</section>

<!-- Fleet Showcase Section -->
<section id="fleet" class="bg-slate-100/70 py-20 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Armada Kelas Eksekutif</span>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Armada Bus Modern & Mewah</h2>
            <p class="text-slate-600 text-sm mt-3">PO CAN Travel mengoperasikan sasis Scania, Mercedes-Benz, dan Volvo terbaru dengan suspensi udara untuk kenyamanan terbaik sepanjang perjalanan Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($buses as $bus)
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                {{ $bus->type }}
                            </span>
                            <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">
                                {{ $bus->seat_capacity }} Kursi
                            </span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 mb-2">{{ $bus->name }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ Str::limit($bus->description, 100) }}</p>

                        <div class="space-y-1.5 pt-2 border-t border-slate-100">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Fasilitas Utama:</p>
                            @if(is_array($bus->facilities))
                                @foreach(array_slice($bus->facilities, 0, 4) as $facility)
                                    <div class="flex items-center text-xs text-slate-700 font-medium">
                                        <svg class="w-3.5 h-3.5 mr-2 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="truncate">{{ $facility }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <a href="{{ route('trips.index', ['bus_type' => $bus->type]) }}" class="block text-center py-2 px-3 rounded-xl bg-slate-50 hover:bg-brand-50 text-brand-700 hover:text-brand-800 text-xs font-bold border border-slate-200 hover:border-brand-200 transition">
                            Cari Bus Ini
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section id="facilities" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Keunggulan Layanan</span>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Mengapa Memilih PO CAN Travel?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-brand-200 transition">
                <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center mb-5 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Jaminan Tepat Waktu</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Kami berkomitmen pada jadwal keberangkatan dan kedatangan yang presisi melalui jalur tol Trans Jawa bebas hambatan.
                </p>
            </div>

            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-brand-200 transition">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-5 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Keamanan & Perawatan Rutin</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Setiap armada menjalani inspeksi keselamatan komprehensif sebelum bertolak, didampingi kru pengemudi bersertifikasi dan berpengalaman.
                </p>
            </div>

            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-brand-200 transition">
                <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center mb-5 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Pilih Kursi Real-Time</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Pilih kursi favorit Anda secara visual langsung dari peta bus. Sistem mencegah kursi ganda dipesan dan tiket diterbitkan instan.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-brand-600 font-extrabold text-xs tracking-wider uppercase">Pertanyaan Umum</span>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Frequently Asked Questions</h2>
        </div>

        <div class="space-y-4" x-data="{ active: null }">
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <button @click="active = active === 1 ? null : 1" class="w-full text-left p-5 font-bold text-slate-800 flex justify-between items-center">
                    <span>Bagaimana cara memesan tiket bus di PO CAN Travel?</span>
                    <span class="text-slate-400" x-text="active === 1 ? '−' : '+'"></span>
                </button>
                <div x-show="active === 1" class="px-5 pb-5 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3" style="display: none;">
                    Pilih kota asal, tujuan, dan tanggal keberangkatan pada kolom pencarian. Pilih jadwal bus yang diinginkan, tentukan kursi di denah kabin bus interaktif, isi data penumpang, lalu selesaikan pembayaran. E-tiket instan akan langsung terbit di akun Anda.
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <button @click="active = active === 2 ? null : 2" class="w-full text-left p-5 font-bold text-slate-800 flex justify-between items-center">
                    <span>Apakah saya perlu mencetak e-tiket fisik?</span>
                    <span class="text-slate-400" x-text="active === 2 ? '−' : '+'"></span>
                </button>
                <div x-show="active === 2" class="px-5 pb-5 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3" style="display: none;">
                    Tidak wajib. Anda cukup menunjukkan E-Tiket dengan kode QR di smartphone kepada petugas saat melakukan check-in dan boarding di pool/terminal keberangkatan.
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <button @click="active = active === 3 ? null : 3" class="w-full text-left p-5 font-bold text-slate-800 flex justify-between items-center">
                    <span>Metode pembayaran apa saja yang didukung?</span>
                    <span class="text-slate-400" x-text="active === 3 ? '−' : '+'"></span>
                </button>
                <div x-show="active === 3" class="px-5 pb-5 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3" style="display: none;">
                    Kami mendukung Bank Transfer Virtual Account (BCA, Mandiri, BRI, BNI), QRIS Instant Pay (semua e-wallet dan m-banking), serta GoPay dan OVO.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
