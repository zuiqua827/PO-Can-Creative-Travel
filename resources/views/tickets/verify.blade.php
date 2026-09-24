@extends('layouts.app')

@section('title', 'Verifikasi Tiket — CAN Travel')
@section('meta_description', 'Portal verifikasi resmi E-Tiket dan boarding pass penumpang bus CAN Travel.')

@push('styles')
<style>
    @media print {
        header, footer, nav, aside, .no-print {
            display: none !important;
        }
        body {
            background-color: white !important;
            color: black !important;
        }
        .shadow-xl {
            box-shadow: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-100 py-10 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
    <div class="max-w-lg w-full">

        <!-- CAN Travel Official Logo Header -->
        <div class="text-center mb-6 flex justify-center">
            <x-logo size="lg" variant="dark" />
        </div>

        <!-- Verification Result Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            
            <!-- Header with Status Color -->
            @if($isValid)
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-6 text-white text-center">
                    <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-100 block">Sistem Verifikasi CAN Travel</span>
                    <h1 class="text-xl font-black mt-1">{{ $statusTitle }}</h1>
                    <p class="text-xs text-emerald-100 mt-1 max-w-sm mx-auto">{{ $statusDescription }}</p>
                </div>
            @elseif($statusType === 'unpaid')
                <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-6 text-white text-center">
                    <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-100 block">Sistem Verifikasi CAN Travel</span>
                    <h1 class="text-xl font-black mt-1">{{ $statusTitle }}</h1>
                    <p class="text-xs text-amber-100 mt-1 max-w-sm mx-auto">{{ $statusDescription }}</p>
                </div>
            @elseif($statusType === 'expired')
                <div class="bg-gradient-to-r from-slate-600 to-slate-800 p-6 text-white text-center">
                    <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-300 block">Sistem Verifikasi CAN Travel</span>
                    <h1 class="text-xl font-black mt-1">{{ $statusTitle }}</h1>
                    <p class="text-xs text-slate-300 mt-1 max-w-sm mx-auto">{{ $statusDescription }}</p>
                </div>
            @else
                <div class="bg-gradient-to-r from-rose-600 to-red-700 p-6 text-white text-center">
                    <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-100 block">Sistem Verifikasi CAN Travel</span>
                    <h1 class="text-xl font-black mt-1">{{ $statusTitle }}</h1>
                    <p class="text-xs text-rose-100 mt-1 max-w-sm mx-auto">{{ $statusDescription }}</p>
                </div>
            @endif

            <!-- Body Details -->
            @if($item)
                <div class="p-6 space-y-4">
                    <!-- Ticket Token Banner -->
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Token Tiket Digital</span>
                        <code class="text-xs font-mono font-bold text-slate-800">{{ $item->ticket_token }}</code>
                    </div>

                    <!-- Passenger & Seat Highlight -->
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-brand-50 border border-brand-100">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-brand-700 tracking-wider block">Penumpang</span>
                            <span class="text-base font-black text-slate-900">{{ $item->passenger_name }}</span>
                            <span class="text-xs text-slate-500 block">Kode Pesanan: <strong class="text-slate-800">{{ $order->order_code }}</strong></span>
                        </div>
                        <div class="text-center px-4 py-2 rounded-xl bg-brand-600 text-white shadow-md shadow-brand-600/20">
                            <span class="text-[9px] uppercase font-bold block text-brand-100">Kursi</span>
                            <span class="text-xl font-black">{{ $item->busSeat?->seat_number ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- Journey Details Grid -->
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block font-medium">Kota Keberangkatan</span>
                            <strong class="text-slate-800 text-sm font-bold">{{ $order->trip->route->origin }}</strong>
                            <span class="text-[11px] text-slate-500 block mt-0.5">{{ $order->trip->departure_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block font-medium">Kota Tujuan</span>
                            <strong class="text-slate-800 text-sm font-bold">{{ $order->trip->route->destination }}</strong>
                            <span class="text-[11px] text-slate-500 block mt-0.5">{{ $order->trip->arrival_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block font-medium">Armada Bus</span>
                            <strong class="text-slate-800 font-bold">{{ $order->trip->bus->name }}</strong>
                            <span class="text-[11px] text-slate-500 block mt-0.5">{{ $order->trip->bus->type }} ({{ $order->trip->bus->code }})</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block font-medium">Status Bayar</span>
                            <strong class="font-bold {{ $order->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ strtoupper($order->payment_status) }}
                            </strong>
                            <span class="text-[11px] text-slate-500 block mt-0.5">{{ $order->formatted_total }}</span>
                        </div>
                    </div>

                    <!-- Boarding Point Note -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                        <span class="text-slate-400 block font-medium">Titik Naik Penumpang:</span>
                        <span class="font-bold text-slate-800">{{ $order->trip->boarding_point ?: $order->trip->route->origin }}</span>
                    </div>

                    <!-- Timestamp Validation Footer -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Waktu Pengecekan:</span>
                        <span class="font-mono">{{ $verifiedAt->format('d/m/Y H:i:s') }} WIB</span>
                    </div>

                    @if($isValid)
                        <div class="pt-3 no-print">
                            <button type="button" onclick="window.print()" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                <span>Cetak Bukti Verifikasi</span>
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <div class="p-8 text-center">
                    <p class="text-sm text-slate-600 mb-6">Pastikan Anda memindai QR code tiket resmi yang diterbitkan langsung oleh sistem CAN Travel.</p>
                    <a href="{{ route('home') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                        Kembali ke Beranda
                    </a>
                </div>
            @endif

        </div>

        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                &larr; Beranda CAN Travel
            </a>
        </div>

    </div>
</div>
@endsection
