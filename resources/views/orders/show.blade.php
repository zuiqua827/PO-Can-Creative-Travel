@extends('layouts.app')

@section('title', 'E-Tiket Resmi - ' . $order->order_code . ' - PO CAN Travel')

@push('styles')
<style>
    @media print {
        header, footer, .no-print {
            display: none !important;
        }
        body {
            background-color: white !important;
        }
        .ticket-card {
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="bg-slate-100 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Action Toolbar -->
        <div class="flex items-center justify-between mb-6 no-print">
            <a href="{{ route('orders.index') }}" class="inline-flex items-center text-xs font-bold text-slate-600 hover:text-slate-900">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Pesanan Saya
            </a>

            <div class="flex items-center space-x-3">
                <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-white text-slate-700 hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak / Simpan PDF
                </button>

                @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled')
                    <a href="{{ route('booking.payment', $order) }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition">
                        Selesaikan Pembayaran
                    </a>
                @endif
            </div>
        </div>

        <!-- The Boarding Pass / E-Ticket Card -->
        <div class="ticket-card bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200">
            
            <!-- Ticket Top Header -->
            <div class="bg-gradient-to-r from-slate-900 via-navy-900 to-slate-900 text-white p-6 sm:p-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-xl bg-brand-600 flex items-center justify-center font-bold text-xl shadow-lg shadow-brand-500/30">
                        CAN
                    </div>
                    <div>
                        <span class="text-xl font-black tracking-tight text-white block">PO CAN TRAVEL</span>
                        <span class="text-[11px] font-semibold text-brand-400 uppercase tracking-widest">Official Electronic Boarding Pass</span>
                    </div>
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Nomor Tiket / Order Code</span>
                    <span class="text-lg font-mono font-black text-amber-400">{{ $order->order_code }}</span>
                </div>
            </div>

            <!-- Route Banner -->
            <div class="p-6 sm:p-8 bg-slate-50/80 border-b border-dashed border-slate-300">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="text-center sm:text-left flex-1">
                        <span class="text-xs font-bold text-slate-400 uppercase">Kota Asal</span>
                        <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $order->trip->route->origin }}</div>
                        <div class="text-xs font-medium text-slate-600 mt-1">📍 Titik Kumpul: {{ $order->trip->boarding_point ?: $order->trip->route->origin }}</div>
                        <div class="text-xs font-bold text-brand-700 mt-1">{{ $order->trip->departure_at->translatedFormat('l, d F Y - H:i') }} WIB</div>
                    </div>

                    <div class="flex flex-col items-center px-4">
                        <span class="text-[10px] font-bold text-slate-500 bg-white border border-slate-200 px-3 py-1 rounded-full mb-1">
                            {{ $order->trip->route->estimated_duration ?: 'Jalur Tol Trans Jawa' }}
                        </span>
                        <div class="w-24 sm:w-32 h-0.5 bg-slate-300 relative flex items-center justify-center">
                            <div class="w-2.5 h-2.5 rounded-full bg-brand-600 absolute -left-1"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-600 absolute -right-1"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1">{{ $order->trip->bus->type }}</span>
                    </div>

                    <div class="text-center sm:text-right flex-1">
                        <span class="text-xs font-bold text-slate-400 uppercase">Kota Tujuan</span>
                        <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $order->trip->route->destination }}</div>
                        <div class="text-xs font-medium text-slate-600 mt-1">📍 Titik Turun: {{ $order->trip->drop_off_point ?: $order->trip->route->destination }}</div>
                        <div class="text-xs font-bold text-brand-700 mt-1">{{ $order->trip->arrival_at->translatedFormat('l, d F Y - H:i') }} WIB</div>
                    </div>
                </div>
            </div>

            <!-- Passenger Manifest & Seat Numbers -->
            <div class="p-6 sm:p-8">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Daftar Penumpang & Alokasi Kursi</h3>

                <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden mb-6">
                    @foreach($order->orderItems as $item)
                        <div class="p-4 bg-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white font-black flex items-center justify-center text-sm shadow-md shadow-emerald-500/20">
                                    {{ $item->busSeat->seat_number }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $item->passenger_name }}</h4>
                                    <p class="text-xs text-slate-500">
                                        HP: {{ $item->passenger_phone ?: '-' }} 
                                        @if($item->passenger_id_number) &bull; NIK: {{ $item->passenger_id_number }} @endif
                                    </p>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-xs font-bold text-slate-700">Kursi {{ $item->busSeat->seat_number }}</span>
                                <span class="text-[11px] text-slate-400 block">Baris {{ $item->busSeat->row }} (Kolom {{ $item->busSeat->column }})</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Info Grid: Armada & Payment Status -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-slate-50 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Armada Bus</span>
                        <strong class="text-slate-800 font-bold">{{ $order->trip->bus->name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Kode Armada</span>
                        <strong class="text-slate-800 font-bold font-mono">{{ $order->trip->bus->code }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Metode Pembayaran</span>
                        <strong class="text-slate-800 font-bold">{{ $order->payment ? $order->payment->payment_method : '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Status Pembayaran</span>
                        @if($order->payment_status === 'paid')
                            <span class="inline-flex items-center font-bold text-emerald-600">
                                <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                LUNAS / TERVERIFIKASI
                            </span>
                        @else
                            <span class="font-bold text-amber-600 uppercase">{{ $order->payment_status }}</span>
                        @endif
                    </div>
                </div>

                <!-- QR Barcode Simulation Section -->
                <div class="mt-8 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="text-xs text-slate-500 space-y-1 text-center sm:text-left">
                        <p class="font-bold text-slate-800">Petunjuk Keberangkatan:</p>
                        <p>1. Tiba di pool/terminal minimal 30 menit sebelum jadwal keberangkatan.</p>
                        <p>2. Tunjukkan kode QR atau E-Tiket ini kepada petugas boarding pass.</p>
                        <p>3. Bagasi maksimal 20 kg per penumpang.</p>
                    </div>

                    <!-- Simulated Barcode Box -->
                    <div class="text-center bg-white p-3 rounded-2xl border-2 border-slate-200 shadow-sm">
                        <!-- Barcode lines simulation -->
                        <div class="flex items-center justify-center space-x-1 h-12 w-48 mb-1">
                            <div class="w-1 bg-slate-900 h-full"></div>
                            <div class="w-2 bg-slate-900 h-full"></div>
                            <div class="w-0.5 bg-slate-900 h-full"></div>
                            <div class="w-3 bg-slate-900 h-full"></div>
                            <div class="w-1 bg-slate-900 h-full"></div>
                            <div class="w-0.5 bg-slate-900 h-full"></div>
                            <div class="w-2 bg-slate-900 h-full"></div>
                            <div class="w-1 bg-slate-900 h-full"></div>
                            <div class="w-3 bg-slate-900 h-full"></div>
                            <div class="w-1 bg-slate-900 h-full"></div>
                            <div class="w-0.5 bg-slate-900 h-full"></div>
                            <div class="w-2 bg-slate-900 h-full"></div>
                        </div>
                        <span class="text-[10px] font-mono font-bold tracking-widest text-slate-600">{{ $order->order_code }}</span>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection
