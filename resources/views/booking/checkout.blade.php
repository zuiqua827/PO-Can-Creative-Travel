@extends('layouts.app')

@section('title', 'Data Penumpang & Konfirmasi Pesanan — CAN Travel')
@section('meta_description', 'Lengkapi identitas penumpang dan konfirmasi pesanan tiket bus CAN Travel Anda dengan aman.')

@section('content')
<div class="bg-slate-50 py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Multi-Step Progress Tracker (Task 13) -->
        <nav aria-label="Progress Pemesanan" class="mb-8">
            <ol class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs font-bold">
                <li class="p-3 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">✓</span>
                    <span>01. Pilih Jadwal</span>
                </li>
                <li class="p-3 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">✓</span>
                    <span>02. Pilih Kursi</span>
                </li>
                <li class="p-3 rounded-2xl bg-brand-600 text-white shadow-md shadow-brand-600/25 flex items-center justify-center space-x-2" aria-current="step">
                    <span class="w-5 h-5 rounded-full bg-white text-brand-600 flex items-center justify-center text-[10px]">03</span>
                    <span>03. Data Penumpang</span>
                </li>
                <li class="p-3 rounded-2xl bg-white text-slate-400 border border-slate-200 flex items-center justify-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[10px]">04</span>
                    <span>04. Pembayaran</span>
                </li>
            </ol>
        </nav>

        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-xs text-slate-500 mb-4">
            <a href="{{ route('trips.show', $trip) }}" class="hover:text-brand-600 flex items-center transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Pemilihan Kursi
            </a>
            <span>/</span>
            <span class="font-bold text-slate-800">Checkout Tiket</span>
        </div>

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Informasi Penumpang &amp; Checkout</h1>
                <p class="text-xs text-slate-500 mt-1">Lengkapi data penumpang sesuai identitas resmi untuk penerbitan tiket.</p>
            </div>
            <div class="hidden sm:block">
                <x-logo size="md" variant="dark" />
            </div>
        </div>

        <form action="{{ route('booking.store', $trip) }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left Column: Forms -->
                <div class="lg:col-span-8 space-y-6">
                    
                    <!-- Trip Summary Card -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jadwal Perjalanan</span>
                                <h2 class="text-lg font-black text-slate-900">{{ $trip->route->origin }} → {{ $trip->route->destination }}</h2>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                {{ $trip->bus->type }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
                            <div>
                                <span class="text-slate-400 block font-medium">Armada Bus</span>
                                <span class="font-bold text-slate-800">{{ $trip->bus->name }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-medium">Waktu Berangkat</span>
                                <span class="font-bold text-slate-800">{{ $trip->departure_at->format('H:i') }} WIB</span>
                                <span class="text-slate-500 block text-[11px]">{{ $trip->departure_at->translatedFormat('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-medium">Titik Naik</span>
                                <span class="font-bold text-slate-800">{{ $trip->boarding_point ?: $trip->route->origin }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-medium">Kursi Dipilih</span>
                                <span class="font-bold text-brand-600">{{ $seats->pluck('seat_number')->join(', ') }} ({{ $seats->count() }} Kursi)</span>
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
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-xl bg-brand-600 text-white font-black flex items-center justify-center text-xs shadow-md shadow-brand-600/30">
                                            {{ $seat->seat_number }}
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-slate-900">Penumpang Kursi {{ $seat->seat_number }}</h3>
                                            <span class="text-[11px] text-slate-500">Baris {{ $seat->row }} &bull; Sisi {{ $seat->column }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-brand-600">{{ $trip->formatted_price }}</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="passenger-name-{{ $seat->id }}" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                            Nama Lengkap Penumpang <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" id="passenger-name-{{ $seat->id }}" name="passengers[{{ $seat->id }}][name]" required
                                            value="{{ old("passengers.{$seat->id}.name", $index === 0 ? auth()->user()->name : '') }}"
                                            aria-invalid="{{ $errors->has("passengers.{$seat->id}.name") ? 'true' : 'false' }}"
                                            @if($errors->has("passengers.{$seat->id}.name")) aria-describedby="passenger-name-err-{{ $seat->id }}" @endif
                                            class="w-full px-4 py-3 rounded-xl border {{ $errors->has("passengers.{$seat->id}.name") ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-300' }} text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                            placeholder="Sesuai KTP / SIM / Paspor">
                                        @error("passengers.{$seat->id}.name")
                                            <p id="passenger-name-err-{{ $seat->id }}" class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="passenger-phone-{{ $seat->id }}" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                            Nomor WhatsApp / HP <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="tel" id="passenger-phone-{{ $seat->id }}" name="passengers[{{ $seat->id }}][phone]" required
                                            value="{{ old("passengers.{$seat->id}.phone", $index === 0 ? auth()->user()->phone : '') }}"
                                            aria-invalid="{{ $errors->has("passengers.{$seat->id}.phone") ? 'true' : 'false' }}"
                                            @if($errors->has("passengers.{$seat->id}.phone")) aria-describedby="passenger-phone-err-{{ $seat->id }}" @endif
                                            class="w-full px-4 py-3 rounded-xl border {{ $errors->has("passengers.{$seat->id}.phone") ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-300' }} text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                            placeholder="Contoh: 081234567890">
                                        @error("passengers.{$seat->id}.phone")
                                            <p id="passenger-phone-err-{{ $seat->id }}" class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="passenger-id-{{ $seat->id }}" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                            Nomor NIK / KTP / Paspor <span class="text-slate-400 font-normal">(Opsional)</span>
                                        </label>
                                        <input type="text" id="passenger-id-{{ $seat->id }}" name="passengers[{{ $seat->id }}][id_number]"
                                            value="{{ old("passengers.{$seat->id}.id_number") }}"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                            placeholder="Nomor identitas untuk verifikasi boarding">
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
                        <label for="booking-notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Catatan Perjalanan <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea id="booking-notes" name="notes" rows="2" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                            placeholder="Contoh: Titik jemput khusus atau catatan bagasi..."></textarea>
                    </div>

                </div>

                <!-- Right Column: Order Summary & Checkout Trigger -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-lg sticky top-24">
                        <h2 class="text-lg font-black text-slate-900 mb-4 pb-3 border-b border-slate-100">Rincian Pembayaran</h2>

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
                                <span>Pajak & Retribusi:</span>
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
                            <button type="submit"
                                    :disabled="submitting"
                                    :class="submitting ? 'opacity-70 cursor-not-allowed' : 'hover:bg-brand-700'"
                                    class="w-full py-4 px-6 rounded-2xl bg-brand-600 text-white font-extrabold text-sm shadow-xl shadow-brand-600/30 transition flex items-center justify-center space-x-2">
                                <svg x-show="!submitting" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg x-show="submitting" x-cloak class="w-5 h-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="submitting ? 'Memproses Pesanan...' : 'Konfirmasi Pesanan'">Konfirmasi Pesanan</span>
                            </button>
                        </div>

                        <p class="mt-4 text-center text-[11px] text-slate-400">
                            Dengan mengonfirmasi pesanan, Anda menyetujui Ketentuan Layanan & Kebijakan Bagasi CAN Travel.
                        </p>
                    </div>
                </div>

            </div>
        </form>

    </div>
</div>
@endsection
