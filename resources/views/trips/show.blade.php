@extends('layouts.app')

@section('title', 'Pilih Kursi — ' . $trip->bus->name . ' — CAN Travel')
@section('meta_description', 'Pilih nomor kursi favorit pada denah bus interaktif ' . $trip->bus->name . ' rute ' . $trip->route->origin . ' ke ' . $trip->route->destination . ' bersama CAN Travel.')

@section('content')
<div class="bg-slate-900 text-white py-8 border-b border-slate-800" 
     x-data="{ 
        selectedSeats: [], 
        seatPrice: {{ $trip->price }},
        maxSeats: 5,
        toggleSeat(seatId, seatNumber) {
            if (this.selectedSeats.some(s => s.id === seatId)) {
                this.selectedSeats = this.selectedSeats.filter(s => s.id !== seatId);
            } else {
                if (this.selectedSeats.length >= this.maxSeats) {
                    alert('Maksimal pemesanan adalah ' + this.maxSeats + ' kursi per transaksi.');
                    return;
                }
                this.selectedSeats.push({ id: seatId, number: seatNumber });
            }
        },
        isSelected(seatId) {
            return this.selectedSeats.some(s => s.id === seatId);
        },
        getTotalPrice() {
            return (this.selectedSeats.length * this.seatPrice).toLocaleString('id-ID');
        },
        getSeatIdsString() {
            return this.selectedSeats.map(s => s.id).join(',');
        }
     }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Navigation & Breadcrumb -->
        <div class="flex items-center space-x-2 text-xs text-slate-400 mb-4">
            <a href="{{ route('trips.index') }}" class="hover:text-white flex items-center transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar Jadwal
            </a>
            <span>/</span>
            <span class="text-slate-200">Pilih Kursi Bus</span>
        </div>

        <!-- Trip Summary Header -->
        <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 sm:p-8 border border-white/15">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-500/20 text-brand-300 border border-brand-500/40">
                            {{ $trip->bus->type }}
                        </span>
                        <span class="text-xs text-slate-300 font-mono">{{ $trip->trip_code }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white">
                        {{ $trip->route->origin }} → {{ $trip->route->destination }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1">
                        {{ $trip->bus->name }} &bull; {{ $trip->departure_at->translatedFormat('l, d F Y') }}
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 lg:border-l lg:border-white/10 lg:pl-8">
                    <div>
                        <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Jadwal Keberangkatan</span>
                        <span class="text-xl font-bold text-white">{{ $trip->departure_at->format('H:i') }} WIB</span>
                        <span class="text-xs text-slate-300 block">Tiba est. {{ $trip->arrival_at->format('H:i') }} WIB</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Tarif per Kursi</span>
                        <span class="text-2xl font-black text-brand-300">{{ $trip->formatted_price }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Cabin & Seat Picker Grid -->
        <div class="mt-10 grid grid-cols-1 lg:grid-cols-12 gap-8 text-slate-900">
            
            <!-- Left: Interactive Visual Seat Layout (Bus Cabin) -->
            <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-10 border border-slate-200 shadow-sm">
                
                <!-- Seat Legend (Task 12) -->
                <div class="flex flex-wrap items-center justify-center gap-6 pb-8 border-b border-slate-100 text-xs font-semibold">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-xl border-2 border-slate-300 bg-white flex items-center justify-center text-xs font-bold text-slate-700 shadow-sm">
                            1A
                        </div>
                        <span class="text-slate-600">Tersedia</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center text-xs font-bold shadow-md shadow-brand-600/30">
                            ✓
                        </div>
                        <span class="text-slate-800 font-bold">Dipilih</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-400 flex items-center justify-center text-xs font-bold cursor-not-allowed">
                            ✕
                        </div>
                        <span class="text-slate-400">Terisi</span>
                    </div>
                </div>

                <!-- Bus Cabin Shell -->
                <div class="max-w-md mx-auto mt-8 bg-slate-50 rounded-[40px] p-6 border-4 border-slate-200 relative shadow-inner">
                    
                    <!-- Front Cabin (Driver & Door) -->
                    <div class="flex justify-between items-center pb-6 border-b-2 border-dashed border-slate-300 mb-6 px-2">
                        <div class="flex items-center space-x-2 bg-slate-200 px-3.5 py-2 rounded-2xl text-xs font-bold text-slate-600">
                            <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 000 20M2 12a10 10 0 0020 0"/>
                            </svg>
                            <span>Pengemudi</span>
                        </div>

                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Bagian Depan</span>

                        <div class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-[11px] font-bold text-slate-500">
                            Pintu Masuk
                        </div>
                    </div>

                    <!-- Seats Rows Layout (2-2 configuration) -->
                    <div class="space-y-4" role="group" aria-label="Denah Kursi Bus">
                        @foreach($seatsByRow as $rowNumber => $seats)
                            <div class="flex items-center justify-between">
                                
                                <!-- Left Seats (Columns A & B) -->
                                <div class="flex space-x-2.5">
                                    @foreach($seats->whereIn('column', ['A', 'B']) as $seat)
                                        @php
                                            $isBooked = in_array($seat->id, $bookedSeatIds) || $seat->status !== 'available';
                                        @endphp

                                        @if($isBooked)
                                            <!-- Booked / Disabled Seat -->
                                            <div class="w-12 h-12 rounded-2xl bg-slate-200 border border-slate-300 flex flex-col items-center justify-center text-slate-400 cursor-not-allowed select-none" 
                                                title="Kursi {{ $seat->seat_number }} sudah terisi" aria-disabled="true">
                                                <span class="text-xs font-bold">{{ $seat->seat_number }}</span>
                                                <span class="text-[8px] uppercase font-semibold">Terisi</span>
                                            </div>
                                        @else
                                            <!-- Available Interactive Seat -->
                                            <button type="button" 
                                                    @click="toggleSeat({{ $seat->id }}, '{{ $seat->seat_number }}')"
                                                    :class="isSelected({{ $seat->id }}) 
                                                        ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30 border-brand-600 scale-105' 
                                                        : 'bg-white text-slate-800 border-2 border-slate-300 hover:border-brand-500 hover:text-brand-600 hover:shadow-sm'"
                                                    aria-label="Kursi {{ $seat->seat_number }}, tersedia, tarif {{ $trip->formatted_price }}"
                                                    :aria-pressed="isSelected({{ $seat->id }})"
                                                    class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center font-bold text-xs transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 select-none">
                                                <span x-text="isSelected({{ $seat->id }}) ? '✓' : '{{ $seat->seat_number }}'"></span>
                                                <span class="text-[8px] uppercase font-semibold" x-text="isSelected({{ $seat->id }}) ? 'Pilih' : ''"></span>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Aisle (Lorong Bus) -->
                                <div class="flex-1 flex justify-center text-slate-400 text-xs font-mono select-none">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">R{{ $rowNumber }}</span>
                                </div>

                                <!-- Right Seats (Columns C & D) -->
                                <div class="flex space-x-2.5">
                                    @foreach($seats->whereIn('column', ['C', 'D']) as $seat)
                                        @php
                                            $isBooked = in_array($seat->id, $bookedSeatIds) || $seat->status !== 'available';
                                        @endphp

                                        @if($isBooked)
                                            <!-- Booked / Disabled Seat -->
                                            <div class="w-12 h-12 rounded-2xl bg-slate-200 border border-slate-300 flex flex-col items-center justify-center text-slate-400 cursor-not-allowed select-none" 
                                                title="Kursi {{ $seat->seat_number }} sudah terisi" aria-disabled="true">
                                                <span class="text-xs font-bold">{{ $seat->seat_number }}</span>
                                                <span class="text-[8px] uppercase font-semibold">Terisi</span>
                                            </div>
                                        @else
                                            <!-- Available Interactive Seat -->
                                            <button type="button" 
                                                    @click="toggleSeat({{ $seat->id }}, '{{ $seat->seat_number }}')"
                                                    :class="isSelected({{ $seat->id }}) 
                                                        ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30 border-brand-600 scale-105' 
                                                        : 'bg-white text-slate-800 border-2 border-slate-300 hover:border-brand-500 hover:text-brand-600 hover:shadow-sm'"
                                                    aria-label="Kursi {{ $seat->seat_number }}, tersedia, tarif {{ $trip->formatted_price }}"
                                                    :aria-pressed="isSelected({{ $seat->id }})"
                                                    class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center font-bold text-xs transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 select-none">
                                                <span x-text="isSelected({{ $seat->id }}) ? '✓' : '{{ $seat->seat_number }}'"></span>
                                                <span class="text-[8px] uppercase font-semibold" x-text="isSelected({{ $seat->id }}) ? 'Pilih' : ''"></span>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Rear Cabin (Toilet & Exit) -->
                    <div class="mt-8 pt-4 border-t-2 border-dashed border-slate-300 flex justify-between items-center text-xs text-slate-400 px-2">
                        <span class="px-3 py-1 bg-slate-200 rounded-lg font-semibold text-slate-600">Toilet Kabin</span>
                        <span class="font-extrabold uppercase tracking-widest text-[10px]">Bagian Belakang</span>
                        <span class="px-3 py-1 bg-slate-200 rounded-lg font-semibold text-slate-600">Pintu Darurat</span>
                    </div>

                </div>

            </div>

            <!-- Right: Booking Summary Card & Proceed Checkout Form (Desktop Sticky) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Bus Facilities Overview -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                    <h2 class="font-black text-slate-900 text-base mb-3 flex items-center">
                        <svg class="w-5 h-5 text-brand-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Fasilitas Bus {{ $trip->bus->name }}
                    </h2>
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 font-medium">
                        @if(is_array($trip->bus->facilities))
                            @foreach($trip->bus->facilities as $fac)
                                <div class="flex items-center space-x-1.5 py-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span class="truncate">{{ $fac }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Live Selection Card & Checkout Button -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-lg sticky top-24">
                    <h2 class="font-black text-slate-900 text-lg mb-4">Ringkasan Pilihan Kursi</h2>

                    <!-- Dynamic Selected Seats Pill Container -->
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kursi Dipilih</label>
                        <template x-if="selectedSeats.length === 0">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-300 text-center text-xs text-slate-400 font-medium">
                                Belum ada kursi yang dipilih. Silakan klik kursi yang tersedia pada peta kabin.
                            </div>
                        </template>

                        <div class="flex flex-wrap gap-2" x-show="selectedSeats.length > 0">
                            <template x-for="seat in selectedSeats" :key="seat.id">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-50 text-brand-800 border border-brand-200 shadow-sm">
                                    <span>Kursi <strong x-text="seat.number"></strong></span>
                                    <button type="button" @click="toggleSeat(seat.id, seat.number)" class="ml-2 text-brand-600 hover:text-brand-900 font-black text-sm" aria-label="Hapus kursi">&times;</button>
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Price Calculations -->
                    <div class="border-t border-slate-100 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Tarif per Kursi:</span>
                            <span class="font-semibold">{{ $trip->formatted_price }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Jumlah Tiket:</span>
                            <span class="font-semibold" x-text="selectedSeats.length + ' Kursi'"></span>
                        </div>
                        <div class="flex justify-between text-slate-900 font-black text-lg pt-3 border-t border-slate-100">
                            <span>Total Estimasi:</span>
                            <span class="text-brand-700" x-text="'Rp ' + getTotalPrice()"></span>
                        </div>
                    </div>

                    <!-- Proceed Form -->
                    <div class="mt-6">
                        @auth
                            <form action="{{ route('booking.checkout', $trip) }}" method="GET">
                                <input type="hidden" name="seat_ids" :value="getSeatIdsString()">
                                
                                <button type="submit" 
                                        :disabled="selectedSeats.length === 0"
                                        :class="selectedSeats.length === 0 ? 'opacity-50 cursor-not-allowed bg-slate-300' : 'bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-600/30'"
                                        class="w-full py-4 px-4 rounded-2xl text-white font-bold text-sm transition flex items-center justify-center space-x-2">
                                    <span>Lanjutkan Pemesanan</span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </button>
                            </form>
                        @else
                            <div class="space-y-3">
                                <a href="{{ route('login') }}" class="w-full flex items-center justify-center py-3.5 px-4 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition">
                                    Masuk untuk Memesan
                                </a>
                                <p class="text-center text-[11px] text-slate-500">
                                    Belum punya akun? <a href="{{ route('register') }}" class="text-brand-600 font-bold underline">Daftar sekarang</a>
                                </p>
                            </div>
                        @endauth
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                        <p class="text-[11px] text-slate-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-emerald-500 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Total harga dan ketersediaan divalidasi aman di server
                        </p>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Mobile Sticky Bottom Floating Summary (Task 12) -->
    <div x-show="selectedSeats.length > 0" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="fixed bottom-0 inset-x-0 bg-white text-slate-900 border-t border-slate-200 p-4 shadow-2xl z-40 lg:hidden"
         style="display: none;">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="text-xs text-slate-500">
                    <span x-text="selectedSeats.length + ' Kursi Dipilih: '"></span>
                    <strong class="text-brand-600 font-bold" x-text="selectedSeats.map(s => s.number).join(', ')"></strong>
                </div>
                <div class="text-lg font-black text-slate-900" x-text="'Rp ' + getTotalPrice()"></div>
            </div>

            <div>
                @auth
                    <form action="{{ route('booking.checkout', $trip) }}" method="GET">
                        <input type="hidden" name="seat_ids" :value="getSeatIdsString()">
                        <button type="submit" class="py-3 px-5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition">
                            Lanjutkan
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="py-3 px-5 rounded-xl bg-brand-600 text-white font-bold text-xs shadow-md">
                        Masuk
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
