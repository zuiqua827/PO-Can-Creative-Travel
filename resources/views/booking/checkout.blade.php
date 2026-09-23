@extends('layouts.app')

@section('title', 'Konfirmasi Pemesanan Tiket - PO CAN Travel')

@section('content')
<div class="bg-slate-50 py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-xs text-slate-500 mb-6">
            <a href="{{ route('trips.show', $trip) }}" class="hover:text-brand-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Pemilihan Kursi
            </a>
            <span>/</span>
            <span class="font-bold text-slate-800">Detail Penumpang & Checkout</span>
        </div>

        <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-8">Informasi Penumpang & Pembayaran</h1>

        <form action="{{ route('booking.store', $trip) }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left Column: Forms -->
                <div class="lg:col-span-8 space-y-6">
                    
                    <!-- Trip Summary Card -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Jadwal Perjalanan</span>
                                <h3 class="text-lg font-black text-slate-900">{{ $trip->route->origin }} → {{ $trip->route->destination }}</h3>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                {{ $trip->bus->type }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
                            <div>
                                <span class="text-slate-400 block">Armada</span>
                                <span class="font-bold text-slate-800">{{ $trip->bus->name }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Waktu Keberangkatan</span>
                                <span class="font-bold text-slate-800">{{ $trip->departure_at->format('H:i') }} WIB</span>
                                <span class="text-slate-500 block">{{ $trip->departure_at->translatedFormat('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Titik Naik</span>
                                <span class="font-bold text-slate-800">{{ $trip->boarding_point ?: $trip->route->origin }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Kursi Dipesan</span>
                                <span class="font-bold text-emerald-600">{{ $seats->pluck('seat_number')->join(', ') }} ({{ $seats->count() }} Kursi)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Forms for Each Seat -->
                    <div class="space-y-4">
                        <h2 class="text-lg font-extrabold text-slate-900">Data Penumpang Sesuai Identitas Resmi</h2>

                        @foreach($seats as $index => $seat)
                            <input type="hidden" name="seats[]" value="{{ $seat->id }}">

                            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white font-black flex items-center justify-center text-xs shadow-md shadow-emerald-500/30">
                                            {{ $seat->seat_number }}
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900">Penumpang Kursi {{ $seat->seat_number }}</h4>
                                            <span class="text-[11px] text-slate-500">Baris {{ $seat->row }} &bull; Sisi {{ $seat->column }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-brand-600">{{ $trip->formatted_price }}</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                            Nama Lengkap Penumpang <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" name="passengers[{{ $seat->id }}][name]" required
                                            value="{{ old("passengers.{$seat->id}.name", $index === 0 ? auth()->user()->name : '') }}"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                            placeholder="Sesuai KTP/SIM/Paspor">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                            Nomor WhatsApp / HP Aktif <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="tel" name="passengers[{{ $seat->id }}][phone]" required
                                            value="{{ old("passengers.{$seat->id}.phone", $index === 0 ? auth()->user()->phone : '') }}"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                            placeholder="Contoh: 081234567890">
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                            Nomor NIK / KTP / Paspor <span class="text-slate-400 font-normal">(Opsional)</span>
                                        </label>
                                        <input type="text" name="passengers[{{ $seat->id }}][id_number]"
                                            value="{{ old("passengers.{$seat->id}.id_number") }}"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                            placeholder="16 digit NIK untuk verifikasi saat boarding">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                        <h2 class="text-lg font-extrabold text-slate-900">Pilih Metode Pembayaran</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ selectedMethod: 'BCA Virtual Account' }">
                            <label :class="selectedMethod === 'BCA Virtual Account' ? 'border-brand-600 bg-brand-50/50 ring-2 ring-brand-500' : 'border-slate-200 hover:border-slate-300'"
                                class="flex items-center p-4 rounded-2xl border cursor-pointer transition">
                                <input type="radio" name="payment_method" value="BCA Virtual Account" checked 
                                    @click="selectedMethod = 'BCA Virtual Account'" class="text-brand-600 focus:ring-brand-500 h-4 w-4">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-slate-800">BCA Virtual Account</span>
                                    <span class="block text-xs text-slate-500">Verifikasi otomatis 24 Jam</span>
                                </div>
                            </label>

                            <label :class="selectedMethod === 'Mandiri Virtual Account' ? 'border-brand-600 bg-brand-50/50 ring-2 ring-brand-500' : 'border-slate-200 hover:border-slate-300'"
                                class="flex items-center p-4 rounded-2xl border cursor-pointer transition">
                                <input type="radio" name="payment_method" value="Mandiri Virtual Account" 
                                    @click="selectedMethod = 'Mandiri Virtual Account'" class="text-brand-600 focus:ring-brand-500 h-4 w-4">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-slate-800">Mandiri Virtual Account</span>
                                    <span class="block text-xs text-slate-500">Livin' by Mandiri & ATM</span>
                                </div>
                            </label>

                            <label :class="selectedMethod === 'QRIS Instant Pay' ? 'border-brand-600 bg-brand-50/50 ring-2 ring-brand-500' : 'border-slate-200 hover:border-slate-300'"
                                class="flex items-center p-4 rounded-2xl border cursor-pointer transition">
                                <input type="radio" name="payment_method" value="QRIS Instant Pay" 
                                    @click="selectedMethod = 'QRIS Instant Pay'" class="text-brand-600 focus:ring-brand-500 h-4 w-4">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-slate-800">QRIS (Semua E-Wallet)</span>
                                    <span class="block text-xs text-slate-500">GoPay, OVO, ShopeePay, Dana, dll</span>
                                </div>
                            </label>

                            <label :class="selectedMethod === 'BRI Virtual Account' ? 'border-brand-600 bg-brand-50/50 ring-2 ring-brand-500' : 'border-slate-200 hover:border-slate-300'"
                                class="flex items-center p-4 rounded-2xl border cursor-pointer transition">
                                <input type="radio" name="payment_method" value="BRI Virtual Account" 
                                    @click="selectedMethod = 'BRI Virtual Account'" class="text-brand-600 focus:ring-brand-500 h-4 w-4">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-slate-800">BRI Virtual Account (BRIVA)</span>
                                    <span class="block text-xs text-slate-500">BRImo & Semua ATM</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Notes field -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Catatan Khusus <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea name="notes" rows="2" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                            placeholder="Contoh: Bawa bagasi koper besar atau mohon bantuan lansia saat boarding..."></textarea>
                    </div>

                </div>

                <!-- Right Column: Order Summary & Checkout Trigger -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-lg sticky top-24">
                        <h3 class="text-lg font-black text-slate-900 mb-4 pb-3 border-b border-slate-100">Rincian Pembayaran</h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-slate-600">
                                <span>Tarif Tiket:</span>
                                <span>{{ $trip->formatted_price }} &times; {{ $seats->count() }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Biaya Layanan & Asuransi:</span>
                                <span class="font-bold text-emerald-600">GRATIS</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>PPN 11%:</span>
                                <span class="text-slate-500">Termasuk</span>
                            </div>

                            <div class="pt-4 border-t border-slate-200 flex justify-between items-baseline">
                                <div>
                                    <span class="text-xs font-bold uppercase text-slate-500 block">Total Tagihan</span>
                                    <span class="text-2xl font-black text-brand-700">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100">
                            <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-extrabold text-sm shadow-xl shadow-emerald-600/30 transition flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Konfirmasi & Buat Pesanan</span>
                            </button>
                        </div>

                        <p class="mt-4 text-center text-[11px] text-slate-400">
                            Dengan mengklik tombol di atas, Anda menyetujui Ketentuan Layanan & Kebijakan Bagasi PO CAN Travel.
                        </p>
                    </div>
                </div>

            </div>
        </form>

    </div>
</div>
@endsection
