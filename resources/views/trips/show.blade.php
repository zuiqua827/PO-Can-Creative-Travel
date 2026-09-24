@extends('layouts.app')

@section('title', 'Pilih Kursi — ' . $trip->bus->name . ' — CAN Travel')
@section('meta_description', 'Pilih nomor kursi favorit pada denah bus interaktif ' . $trip->bus->name . ' rute ' . $trip->route->origin . ' ke ' . $trip->route->destination . ' bersama CAN Travel.')

@section('content')
<div class="bg-navy-900 text-white py-8 border-b border-navy-800" id="seat-picker-root">

    <!-- WCAG 2.1 AA Dynamic Screen Reader Announcer -->
    <div id="accessibility-announcer" class="sr-only" aria-live="polite" aria-atomic="true" data-announcement="accessibilityAnnouncement" x-text="accessibilityAnnouncement"></div>

    <!-- Inline Non-blocking Max Seats Alert Banner -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" id="max-seat-alert-box" style="display: none;">
        <div class="mb-4 p-4 rounded-2xl bg-amber-500/20 border border-amber-400/40 text-amber-200 text-sm font-semibold flex items-center justify-between shadow-lg backdrop-blur-sm" role="alert">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-amber-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Maksimal pemesanan adalah 5 kursi per transaksi.</span>
            </div>
            <button type="button" id="close-max-seat-alert" class="text-amber-300 hover:text-white text-xs font-bold px-2 py-1 rounded-lg bg-amber-500/30 transition">
                Tutup
            </button>
        </div>
    </div>

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

                <!-- Seat Legend (Phase E: 4 Explicit States) -->
                <div class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-6 pb-6 sm:pb-8 border-b border-slate-100 text-[11px] sm:text-xs font-semibold">
                    <div class="flex items-center space-x-1.5 sm:space-x-2">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl border-2 border-slate-300 bg-white flex items-center justify-center text-[10px] sm:text-xs font-bold text-slate-700 shadow-sm">
                            1A
                        </div>
                        <span class="text-slate-600">Tersedia</span>
                    </div>
                    <div class="flex items-center space-x-1.5 sm:space-x-2">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center text-[10px] sm:text-xs font-bold shadow-md shadow-brand-600/30">
                            ✓
                        </div>
                        <span class="text-slate-800 font-bold">Dipilih</span>
                    </div>
                    <div class="flex items-center space-x-1.5 sm:space-x-2">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-amber-50 border border-amber-300 text-amber-700 flex items-center justify-center text-[10px] sm:text-xs font-bold cursor-not-allowed">
                            ⏱
                        </div>
                        <span class="text-amber-800 font-semibold">Tertahan</span>
                    </div>
                    <div class="flex items-center space-x-1.5 sm:space-x-2">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-slate-200 border border-slate-300 text-slate-400 flex items-center justify-center text-[10px] sm:text-xs font-bold cursor-not-allowed">
                            ✕
                        </div>
                        <span class="text-slate-400 font-medium">Terisi</span>
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
                                            $isConfirmed = in_array($seat->id, $confirmedSeatIds ?? []);
                                            $isHeld = in_array($seat->id, $heldSeatIds ?? []);
                                            $isBooked = $isConfirmed || in_array($seat->id, $bookedSeatIds ?? []) || $seat->status !== 'available';
                                        @endphp

                                        @if($isConfirmed || ($isBooked && !$isHeld))
                                            <!-- Booked / Confirmed Disabled Seat -->
                                            <button type="button" disabled
                                                    class="w-12 h-12 rounded-2xl bg-slate-200 border border-slate-300 flex flex-col items-center justify-center text-slate-400 cursor-not-allowed select-none"
                                                    title="Kursi {{ $seat->seat_number }} sudah terisi"
                                                    aria-label="Kursi {{ $seat->seat_number }}, sudah terisi"
                                                    aria-disabled="true"
                                                    data-seat-id="{{ $seat->id }}"
                                                    data-seat-number="{{ $seat->seat_number }}"
                                                    data-seat-status="booked">
                                                <span class="text-xs font-bold">{{ $seat->seat_number }}</span>
                                                <span class="text-[8px] uppercase font-semibold">Terisi</span>
                                            </button>
                                        @elseif($isHeld)
                                            <!-- Held / Active Reservation Seat -->
                                            <button type="button" disabled
                                                    class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-300 flex flex-col items-center justify-center text-amber-700 cursor-not-allowed select-none"
                                                    title="Kursi {{ $seat->seat_number }} sedang dalam proses pemesanan pengguna lain"
                                                    aria-label="Kursi {{ $seat->seat_number }}, sedang ditahan"
                                                    aria-disabled="true"
                                                    data-seat-id="{{ $seat->id }}"
                                                    data-seat-number="{{ $seat->seat_number }}"
                                                    data-seat-status="held">
                                                <span class="text-xs font-bold">{{ $seat->seat_number }}</span>
                                                <span class="text-[8px] uppercase font-semibold">Tertahan</span>
                                            </button>
                                        @else
                                            <!-- Available Interactive Seat -->
                                            <button type="button"
                                                    id="seat-btn-{{ $seat->id }}"
                                                    data-seat-id="{{ $seat->id }}"
                                                    data-seat-number="{{ $seat->seat_number }}"
                                                    data-seat-status="available"
                                                    aria-label="Kursi {{ $seat->seat_number }}, tersedia, tarif {{ $trip->formatted_price }}"
                                                    aria-pressed="false"
                                                    class="seat-picker-btn cursor-pointer w-12 h-12 rounded-2xl flex flex-col items-center justify-center font-bold text-xs transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 select-none bg-white text-slate-800 border-2 border-slate-300 hover:border-brand-500 hover:text-brand-600 hover:shadow-sm">
                                                <span class="seat-num-text text-xs font-bold">{{ $seat->seat_number }}</span>
                                                <span class="seat-status-text text-[8px] uppercase font-semibold"></span>
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
                                            $isConfirmed = in_array($seat->id, $confirmedSeatIds ?? []);
                                            $isHeld = in_array($seat->id, $heldSeatIds ?? []);
                                            $isBooked = $isConfirmed || in_array($seat->id, $bookedSeatIds ?? []) || $seat->status !== 'available';
                                        @endphp

                                        @if($isConfirmed || ($isBooked && !$isHeld))
                                            <!-- Booked / Confirmed Disabled Seat -->
                                            <button type="button" disabled
                                                    class="w-12 h-12 rounded-2xl bg-slate-200 border border-slate-300 flex flex-col items-center justify-center text-slate-400 cursor-not-allowed select-none"
                                                    title="Kursi {{ $seat->seat_number }} sudah terisi"
                                                    aria-label="Kursi {{ $seat->seat_number }}, sudah terisi"
                                                    aria-disabled="true"
                                                    data-seat-id="{{ $seat->id }}"
                                                    data-seat-number="{{ $seat->seat_number }}"
                                                    data-seat-status="booked">
                                                <span class="text-xs font-bold">{{ $seat->seat_number }}</span>
                                                <span class="text-[8px] uppercase font-semibold">Terisi</span>
                                            </button>
                                        @elseif($isHeld)
                                            <!-- Held / Active Reservation Seat -->
                                            <button type="button" disabled
                                                    class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-300 flex flex-col items-center justify-center text-amber-700 cursor-not-allowed select-none"
                                                    title="Kursi {{ $seat->seat_number }} sedang dalam proses pemesanan pengguna lain"
                                                    aria-label="Kursi {{ $seat->seat_number }}, sedang ditahan"
                                                    aria-disabled="true"
                                                    data-seat-id="{{ $seat->id }}"
                                                    data-seat-number="{{ $seat->seat_number }}"
                                                    data-seat-status="held">
                                                <span class="text-xs font-bold">{{ $seat->seat_number }}</span>
                                                <span class="text-[8px] uppercase font-semibold">Tertahan</span>
                                            </button>
                                        @else
                                            <!-- Available Interactive Seat -->
                                            <button type="button"
                                                    id="seat-btn-{{ $seat->id }}"
                                                    data-seat-id="{{ $seat->id }}"
                                                    data-seat-number="{{ $seat->seat_number }}"
                                                    data-seat-status="available"
                                                    aria-label="Kursi {{ $seat->seat_number }}, tersedia, tarif {{ $trip->formatted_price }}"
                                                    aria-pressed="false"
                                                    class="seat-picker-btn cursor-pointer w-12 h-12 rounded-2xl flex flex-col items-center justify-center font-bold text-xs transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 select-none bg-white text-slate-800 border-2 border-slate-300 hover:border-brand-500 hover:text-brand-600 hover:shadow-sm">
                                                <span class="seat-num-text text-xs font-bold">{{ $seat->seat_number }}</span>
                                                <span class="seat-status-text text-[8px] uppercase font-semibold"></span>
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

                        <div class="p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-300 text-center text-xs text-slate-400 font-medium" id="empty-seats-placeholder">
                            Belum ada kursi yang dipilih. Silakan klik kursi yang tersedia pada peta kabin.
                        </div>

                        <!-- Selected Seats Pills Container -->
                        <div id="selected-pills-container" class="flex flex-wrap gap-2" style="display: none;"></div>
                    </div>

                    <!-- Price Calculations -->
                    <div class="border-t border-slate-100 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Tarif per Kursi:</span>
                            <span class="font-semibold">{{ $trip->formatted_price }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Jumlah Tiket:</span>
                            <span class="font-semibold" id="seat-count-display">0 Kursi</span>
                        </div>
                        <div class="flex justify-between text-slate-900 font-black text-lg pt-3 border-t border-slate-100">
                            <span>Total Estimasi:</span>
                            <span class="text-brand-700" id="total-price-display">Rp 0</span>
                        </div>
                    </div>

                    <!-- Proceed Form -->
                    <div class="mt-6">
                        <form action="{{ route('booking.checkout', $trip) }}" method="GET" id="desktop-booking-form">
                            <input type="hidden" name="seat_ids" id="desktop-seat-ids-input" value="">

                            <button type="submit"
                                    id="desktop-submit-checkout-btn"
                                    disabled
                                    class="w-full py-4 px-4 rounded-2xl text-white font-bold text-sm transition flex items-center justify-center space-x-2 opacity-50 cursor-not-allowed bg-slate-300">
                                <span>Lanjutkan Pemesanan</span>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </form>

                        @guest
                            <p class="text-center text-[11px] text-slate-500 mt-2.5">
                                Belum masuk akun? Anda akan diarahkan untuk <a href="{{ route('login') }}" class="text-brand-600 font-bold hover:underline">Masuk</a> atau <a href="{{ route('register') }}" class="text-brand-600 font-bold hover:underline">Daftar</a> saat checkout.
                            </p>
                        @endguest
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

    <!-- Mobile Sticky Bottom Floating Summary (Responsive) -->
    <div id="mobile-floating-bar"
         class="fixed bottom-0 inset-x-0 bg-white text-slate-900 border-t border-slate-200 p-4 shadow-2xl z-40 lg:hidden"
         style="display: none;">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="text-xs text-slate-500">
                    <span id="mobile-seat-count-label">0 Kursi Dipilih: </span>
                    <strong class="text-brand-600 font-bold" id="mobile-seat-list-label">-</strong>
                </div>
                <div class="text-lg font-black text-slate-900" id="mobile-total-price-label">Rp 0</div>
            </div>

            <div>
                <form action="{{ route('booking.checkout', $trip) }}" method="GET" id="mobile-booking-form">
                    <input type="hidden" name="seat_ids" id="mobile-seat-ids-input" value="">
                    <button type="submit"
                            id="mobile-submit-checkout-btn"
                            disabled
                            class="py-3 px-5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition">
                        Lanjutkan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Pure, CSP-Compliant, Robust Seat Picker Script --}}
<script>
(function() {
    'use strict';

    var rawPrice = {{ (int) $trip->price }};
    var maxSeats = 5;
    var selectedSeats = []; // Array of { id: number, number: string }

    function getSeatIdsString() {
        return selectedSeats.map(function(s) { return s.id; }).join(',');
    }

    function getTotalPriceFormatted() {
        return (selectedSeats.length * rawPrice).toLocaleString('id-ID');
    }

    function announce(message) {
        var el = document.getElementById('accessibility-announcer');
        if (el) {
            el.textContent = message;
        }
    }

    function showMaxSeatsWarning() {
        var banner = document.getElementById('max-seat-alert-box');
        if (banner) {
            banner.style.display = 'block';
            setTimeout(function() {
                banner.style.display = 'none';
            }, 4000);
        }
        announce('Peringatan: Maksimal pemesanan adalah ' + maxSeats + ' kursi per transaksi.');
    }

    function renderUI() {
        var seatIdsStr = getSeatIdsString();
        var totalPriceStr = getTotalPriceFormatted();

        // 1. Update Hidden Form Inputs
        var desktopInput = document.getElementById('desktop-seat-ids-input');
        if (desktopInput) desktopInput.value = seatIdsStr;

        var mobileInput = document.getElementById('mobile-seat-ids-input');
        if (mobileInput) mobileInput.value = seatIdsStr;

        // 2. Update Summary Counter & Total
        var countEl = document.getElementById('seat-count-display');
        if (countEl) countEl.textContent = selectedSeats.length + ' Kursi';

        var totalEl = document.getElementById('total-price-display');
        if (totalEl) totalEl.textContent = 'Rp ' + totalPriceStr;

        // 3. Update Selected Pills Container
        var placeholder = document.getElementById('empty-seats-placeholder');
        var pillsContainer = document.getElementById('selected-pills-container');

        if (placeholder && pillsContainer) {
            if (selectedSeats.length === 0) {
                placeholder.style.display = 'block';
                pillsContainer.style.display = 'none';
                pillsContainer.innerHTML = '';
            } else {
                placeholder.style.display = 'none';
                pillsContainer.style.display = 'flex';
                pillsContainer.innerHTML = '';

                selectedSeats.forEach(function(seat) {
                    var pill = document.createElement('span');
                    pill.className = 'inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-50 text-brand-800 border border-brand-200 shadow-sm';
                    pill.innerHTML = '<span>Kursi <strong>' + seat.number + '</strong></span>';

                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'ml-2 text-brand-600 hover:text-brand-900 font-black text-sm focus:outline-none';
                    removeBtn.setAttribute('aria-label', 'Batalkan pilihan kursi ' + seat.number);
                    removeBtn.innerHTML = '&times;';
                    removeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        toggleSeat(seat.id, seat.number);
                    });

                    pill.appendChild(removeBtn);
                    pillsContainer.appendChild(pill);
                });
            }
        }

        // 4. Update Desktop Checkout Submit Button
        var desktopBtn = document.getElementById('desktop-submit-checkout-btn');
        if (desktopBtn) {
            if (selectedSeats.length > 0) {
                desktopBtn.disabled = false;
                desktopBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-slate-300');
                desktopBtn.classList.add('bg-brand-600', 'hover:bg-brand-700', 'shadow-lg', 'shadow-brand-600/30', 'cursor-pointer');
            } else {
                desktopBtn.disabled = true;
                desktopBtn.classList.add('opacity-50', 'cursor-not-allowed', 'bg-slate-300');
                desktopBtn.classList.remove('bg-brand-600', 'hover:bg-brand-700', 'shadow-lg', 'shadow-brand-600/30', 'cursor-pointer');
            }
        }

        // 5. Update Mobile Floating Bar
        var mobileBar = document.getElementById('mobile-floating-bar');
        var mobileBtn = document.getElementById('mobile-submit-checkout-btn');
        var mobileCount = document.getElementById('mobile-seat-count-label');
        var mobileList = document.getElementById('mobile-seat-list-label');
        var mobilePrice = document.getElementById('mobile-total-price-label');

        if (mobileBar) {
            if (selectedSeats.length > 0) {
                mobileBar.style.display = 'block';
                if (mobileBtn) mobileBtn.disabled = false;
                if (mobileCount) mobileCount.textContent = selectedSeats.length + ' Kursi Dipilih: ';
                if (mobileList) mobileList.textContent = selectedSeats.map(function(s) { return s.number; }).join(', ');
                if (mobilePrice) mobilePrice.textContent = 'Rp ' + totalPriceStr;
            } else {
                mobileBar.style.display = 'none';
                if (mobileBtn) mobileBtn.disabled = true;
            }
        }
    }

    function toggleSeat(seatId, seatNumber) {
        var numId = parseInt(seatId, 10);
        var existingIdx = selectedSeats.findIndex(function(s) { return s.id === numId; });
        var btn = document.getElementById('seat-btn-' + numId);

        if (existingIdx > -1) {
            // Deselect seat
            selectedSeats.splice(existingIdx, 1);
            if (btn) {
                btn.classList.remove('bg-brand-600', 'text-white', 'shadow-lg', 'shadow-brand-600/30', 'border-brand-600', 'scale-105');
                btn.classList.add('bg-white', 'text-slate-800', 'border-2', 'border-slate-300');
                btn.setAttribute('aria-pressed', 'false');
                btn.setAttribute('aria-label', 'Kursi ' + seatNumber + ', tersedia, tarif Rp ' + rawPrice.toLocaleString('id-ID'));

                var numEl = btn.querySelector('.seat-num-text');
                if (numEl) numEl.textContent = seatNumber;

                var subEl = btn.querySelector('.seat-status-text');
                if (subEl) subEl.textContent = '';
            }
            announce('Pilihan kursi ' + seatNumber + ' dibatalkan. Tersisa ' + selectedSeats.length + ' kursi terpilih.');
        } else {
            // Select seat
            if (selectedSeats.length >= maxSeats) {
                showMaxSeatsWarning();
                return;
            }

            selectedSeats.push({ id: numId, number: seatNumber });
            if (btn) {
                btn.classList.add('bg-brand-600', 'text-white', 'shadow-lg', 'shadow-brand-600/30', 'border-brand-600', 'scale-105');
                btn.classList.remove('bg-white', 'text-slate-800', 'border-2', 'border-slate-300');
                btn.setAttribute('aria-pressed', 'true');
                btn.setAttribute('aria-label', 'Kursi ' + seatNumber + ', dipilih, tarif Rp ' + rawPrice.toLocaleString('id-ID'));

                var numEl2 = btn.querySelector('.seat-num-text');
                if (numEl2) numEl2.textContent = '✓';

                var subEl2 = btn.querySelector('.seat-status-text');
                if (subEl2) subEl2.textContent = 'Pilih';
            }
            announce('Kursi ' + seatNumber + ' berhasil dipilih. Total ' + selectedSeats.length + ' kursi terpilih.');
        }

        renderUI();
    }

    function initSeatPicker() {
        var buttons = document.querySelectorAll('.seat-picker-btn');
        buttons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var seatId = btn.getAttribute('data-seat-id');
                var seatNumber = btn.getAttribute('data-seat-number');
                toggleSeat(seatId, seatNumber);
            });
        });

        var closeAlertBtn = document.getElementById('close-max-seat-alert');
        if (closeAlertBtn) {
            closeAlertBtn.addEventListener('click', function() {
                var alertBox = document.getElementById('max-seat-alert-box');
                if (alertBox) alertBox.style.display = 'none';
            });
        }

        // Initialize state
        renderUI();
    }

    // Expose safe API for testing / external access
    window.CANSeatPicker = {
        toggle: toggleSeat,
        getSelected: function() { return selectedSeats.slice(); },
        getSeatIds: getSeatIdsString,
        clear: function() {
            var copy = selectedSeats.slice();
            copy.forEach(function(s) { toggleSeat(s.id, s.number); });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSeatPicker);
    } else {
        initSeatPicker();
    }
})();
</script>
@endsection
