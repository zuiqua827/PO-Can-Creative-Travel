@extends('layouts.app')

@section('title', 'Detail Pesanan ' . $order->order_code . ' — CAN Travel')
@section('meta_description', 'Detail pesanan dan E-Tiket Resmi CAN Travel rute ' . $order->trip->route->origin . ' ke ' . $order->trip->route->destination . '. Kode: ' . $order->order_code)

@push('styles')
<style>
    @media print {
        header, footer, aside, .no-print {
            display: none !important;
        }
        body {
            background-color: white !important;
            color: black !important;
        }
        .ticket-card {
            box-shadow: none !important;
            border: 1px solid #94a3b8 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="bg-slate-100 py-10 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Top Navigation / Action Bar -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-6 gap-4 no-print">
            <a href="{{ route('orders.index') }}" class="inline-flex items-center text-xs font-bold text-slate-600 hover:text-slate-900 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Pesanan Saya
            </a>

            <div class="flex flex-wrap items-center gap-2">
                @if($order->payment_status === 'paid')
                    <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-bold bg-white text-slate-700 hover:bg-slate-50 border border-slate-300 shadow-sm transition">
                        <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak / Simpan PDF
                    </button>
                @endif

                @if($order->payment_status === 'unpaid' && !in_array($order->status, ['cancelled', 'expired']))
                    <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Kursi Anda akan dilepaskan kembali.')">
                        @csrf
                        <button type="submit" class="px-3.5 py-2.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 transition">
                            Batalkan Pesanan
                        </button>
                    </form>

                    <a href="{{ route('booking.payment', $order) }}" class="inline-flex items-center px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-md shadow-amber-500/20 transition">
                        <span>Bayar Sekarang</span>
                        <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>

        <!-- Status Context Banner -->
        @if($order->status === 'cancelled')
            <div class="mb-6 bg-rose-50 border border-rose-200 rounded-3xl p-5 sm:p-6 text-rose-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 no-print">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 flex items-center justify-center text-rose-600 font-bold shrink-0">
                        ✕
                    </div>
                    <div>
                        <h2 class="font-bold text-sm text-slate-900">Pesanan Telah Dibatalkan</h2>
                        <p class="text-xs text-rose-700 mt-0.5">Pesanan ini telah dibatalkan dan kursi telah dilepaskan kembali ke sistem tiket.</p>
                    </div>
                </div>
                <a href="{{ route('trips.index') }}" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shrink-0">
                    Pesan Tiket Baru
                </a>
            </div>
        @elseif($order->status === 'expired' || $order->payment_status === 'expired')
            <div class="mb-6 bg-slate-200 border border-slate-300 rounded-3xl p-5 sm:p-6 text-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 no-print">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-slate-300 flex items-center justify-center text-slate-700 font-bold shrink-0">
                        ⏰
                    </div>
                    <div>
                        <h2 class="font-bold text-sm text-slate-900">Batas Waktu Pembayaran Habis (Kedaluwarsa)</h2>
                        <p class="text-xs text-slate-600 mt-0.5">Waktu pembayaran 2 jam telah lewat. Kursi yang Anda pilih telah dilepaskan kembali.</p>
                    </div>
                </div>
                <a href="{{ route('trips.index') }}" class="px-4 py-2 rounded-xl bg-brand-600 text-white text-xs font-bold hover:bg-brand-700 transition shrink-0">
                    Cari Jadwal Baru
                </a>
            </div>
        @elseif($order->payment_status === 'unpaid')
            <div class="mb-6 bg-amber-50 border border-amber-200 rounded-3xl p-5 sm:p-6 text-amber-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 no-print">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-700 font-bold shrink-0">
                        ⏳
                    </div>
                    <div>
                        <h2 class="font-bold text-sm text-slate-900">Menunggu Pembayaran</h2>
                        <p class="text-xs text-amber-800 mt-0.5">
                            Selesaikan pembayaran sebelum <strong>{{ $order->expires_at ? $order->expires_at->translatedFormat('d M Y, H:i') : '-' }} WIB</strong> agar reservasi kursi Anda tidak dibatalkan.
                        </p>
                    </div>
                </div>
                <a href="{{ route('booking.payment', $order) }}" class="px-4 py-2 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 transition shrink-0">
                    Lanjut Bayar
                </a>
            </div>
        @endif

        <!-- Order Timeline Progress (Task 6) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm mb-6 no-print">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Status & Alur Pesanan</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-xs">
                <!-- Step 1: Created -->
                <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="block font-bold">1. Dibuat</span>
                    <span class="text-[10px] text-emerald-600 font-medium">{{ $order->created_at->format('d M, H:i') }}</span>
                </div>

                <!-- Step 2: Payment -->
                @if($order->payment_status === 'paid')
                    <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="block font-bold">2. Dibayar</span>
                        <span class="text-[10px] text-emerald-600 font-medium">{{ $order->payment && $order->payment->paid_at ? $order->payment->paid_at->format('d M, H:i') : 'Sukses' }}</span>
                    </div>
                @elseif($order->status === 'cancelled')
                    <div class="p-3 rounded-2xl bg-rose-50 text-rose-800 border border-rose-200">
                        <span class="block font-bold">2. Dibatalkan</span>
                        <span class="text-[10px] text-rose-600 font-medium">Batal</span>
                    </div>
                @elseif($order->status === 'expired' || $order->payment_status === 'expired')
                    <div class="p-3 rounded-2xl bg-slate-100 text-slate-600 border border-slate-200">
                        <span class="block font-bold">2. Kedaluwarsa</span>
                        <span class="text-[10px] text-slate-400 font-medium">Lewat Batas</span>
                    </div>
                @else
                    <div class="p-3 rounded-2xl bg-amber-50 text-amber-800 border border-amber-200">
                        <span class="block font-bold">2. Menunggu Bayar</span>
                        <span class="text-[10px] text-amber-600 font-medium">Belum Dibayar</span>
                    </div>
                @endif

                <!-- Step 3: Ticket Issue -->
                @if($order->payment_status === 'paid')
                    <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="block font-bold">3. E-Tiket Terbit</span>
                        <span class="text-[10px] text-emerald-600 font-medium">Siap Boarding</span>
                    </div>
                @else
                    <div class="p-3 rounded-2xl bg-slate-50 text-slate-400 border border-slate-100">
                        <span class="block font-bold">3. E-Tiket</span>
                        <span class="text-[10px] text-slate-400">Menunggu</span>
                    </div>
                @endif

                <!-- Step 4: Completed -->
                @if($order->status === 'completed')
                    <div class="p-3 rounded-2xl bg-blue-50 text-blue-800 border border-blue-200">
                        <span class="block font-bold">4. Selesai</span>
                        <span class="text-[10px] text-blue-600 font-medium">Perjalanan Usai</span>
                    </div>
                @else
                    <div class="p-3 rounded-2xl bg-slate-50 text-slate-400 border border-slate-100">
                        <span class="block font-bold">4. Perjalanan</span>
                        <span class="text-[10px] text-slate-400">{{ $order->trip->departure_at->format('d M') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- The Boarding Pass / E-Ticket Card -->
        <div class="ticket-card bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200">
            
            <!-- Ticket Top Header -->
            <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-navy-950 text-white p-6 sm:p-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center space-x-3">
                    <x-logo size="md" variant="light" />
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Kode Pemesanan Tiket</span>
                    <span class="text-lg font-mono font-black text-amber-400">{{ $order->order_code }}</span>
                </div>
            </div>

            <!-- Route Banner -->
            <div class="p-6 sm:p-8 bg-slate-50/80 border-b border-dashed border-slate-300">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="text-center sm:text-left flex-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kota Keberangkatan</span>
                        <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $order->trip->route->origin }}</div>
                        <div class="text-xs font-medium text-slate-600 mt-1">📍 Titik Naik: {{ $order->trip->boarding_point ?: $order->trip->route->origin }}</div>
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
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kota Kedatangan</span>
                        <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $order->trip->route->destination }}</div>
                        <div class="text-xs font-medium text-slate-600 mt-1">📍 Titik Turun: {{ $order->trip->drop_off_point ?: $order->trip->route->destination }}</div>
                        <div class="text-xs font-bold text-brand-700 mt-1">{{ $order->trip->arrival_at->translatedFormat('l, d F Y - H:i') }} WIB</div>
                    </div>
                </div>
            </div>

            <!-- Passenger Manifest & Seat Numbers -->
            <div class="p-6 sm:p-8">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Daftar Penumpang & Alokasi Kursi</h2>

                <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden mb-6">
                    @foreach($order->orderItems as $item)
                        <div class="p-4 bg-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-black flex items-center justify-center text-sm shadow-md shadow-brand-600/20">
                                    {{ $item->busSeat->seat_number }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">{{ $item->passenger_name }}</h3>
                                    <p class="text-xs text-slate-500">
                                        HP: {{ $item->passenger_phone ?: '-' }} 
                                        @if($item->passenger_id_number) &bull; ID: {{ $item->passenger_id_number }} @endif
                                    </p>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-xs font-bold text-slate-800">Kursi {{ $item->busSeat->seat_number }}</span>
                                <span class="text-[11px] text-slate-400 block">Baris {{ $item->busSeat->row }} (Sisi {{ $item->busSeat->column }})</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Info Grid: Armada & Statuses -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-slate-50 text-xs mb-6">
                    <div>
                        <span class="text-slate-400 block font-medium">Armada Bus</span>
                        <strong class="text-slate-800 font-bold">{{ $order->trip->bus->name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Total Pembayaran</span>
                        <strong class="text-slate-900 font-bold text-sm">{{ $order->formatted_total }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Status Pesanan</span>
                        <x-status-badge :status="$order->status" type="order" />
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Status Pembayaran</span>
                        <x-status-badge :status="$order->payment_status" type="payment" />
                    </div>
                </div>

                <!-- Boarding Instructions & Barcode Simulation -->
                <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="text-xs text-slate-500 space-y-1 text-center sm:text-left">
                        <p class="font-bold text-slate-800">Petunjuk Boarding CAN Travel:</p>
                        <p>1. Tiba di pool/terminal keberangkatan paling lambat 30 menit sebelum jadwal.</p>
                        <p>2. Tunjukkan E-Tiket ini kepada petugas atau kru bus saat boarding.</p>
                        <p>3. Kapasitas bagasi gratis maksimal 20 kg per penumpang.</p>
                    </div>

                    <!-- Simulated Barcode Box -->
                    <div class="text-center bg-white p-3 rounded-2xl border-2 border-slate-200 shadow-sm" aria-label="Barcode Tiket">
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
                        <span class="text-[10px] font-mono font-bold tracking-widest text-slate-700">{{ $order->order_code }}</span>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection
