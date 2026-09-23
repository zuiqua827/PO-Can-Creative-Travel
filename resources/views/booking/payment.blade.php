@extends('layouts.app')

@section('title', 'Pembayaran Tiket — ' . $order->order_code . ' — CAN Travel')
@section('meta_description', 'Selesaikan pembayaran tiket bus CAN Travel Anda. Kode Pesanan: ' . $order->order_code)

@section('content')
<div class="bg-slate-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Multi-Step Progress Tracker (Step 4) -->
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
                <li class="p-3 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">✓</span>
                    <span>03. Data Penumpang</span>
                </li>
                <li class="p-3 rounded-2xl bg-brand-600 text-white shadow-md shadow-brand-600/25 flex items-center justify-center space-x-2" aria-current="step">
                    <span class="w-5 h-5 rounded-full bg-white text-brand-600 flex items-center justify-center text-[10px]">04</span>
                    <span>04. Pembayaran</span>
                </li>
            </ol>
        </nav>

        <!-- Status Header Card (Task 14) -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            
            <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-300">
                Menunggu Pembayaran
            </span>
            
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mt-3">Pesanan Berhasil Dibuat</h1>
            <p class="text-xs text-slate-500 mt-1">Kode Pesanan: <strong class="font-mono text-slate-900">{{ $order->order_code }}</strong></p>

            <!-- Total Amount Display -->
            <div class="mt-6 py-4 px-8 rounded-2xl bg-slate-50 border border-slate-200 inline-block">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Total Tagihan</span>
                <span class="text-3xl sm:text-4xl font-black text-brand-700">{{ $order->formatted_total }}</span>
            </div>

            @if($order->expires_at)
                <p class="text-xs text-rose-600 font-medium mt-3">
                    Batas Waktu Pembayaran: <strong>{{ $order->expires_at->translatedFormat('d M Y, H:i') }} WIB</strong>
                </p>
            @endif
        </div>

        <!-- Order Summary Brief Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-4 mb-8">
            <h2 class="font-black text-slate-900 text-base pb-3 border-b border-slate-100">Ringkasan Tiket Perjalanan</h2>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Rute</span>
                    <span class="font-bold text-slate-800">{{ $order->trip->route->origin }} → {{ $order->trip->route->destination }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Keberangkatan</span>
                    <span class="font-bold text-slate-800">{{ $order->trip->departure_at->format('H:i') }} WIB</span>
                    <span class="text-slate-500 block text-[11px]">{{ $order->trip->departure_at->translatedFormat('d M Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Armada Bus</span>
                    <span class="font-bold text-slate-800">{{ $order->trip->bus->name }}</span>
                    <span class="text-slate-500 block text-[11px]">{{ $order->trip->bus->type }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Nomor Kursi</span>
                    <span class="font-bold text-brand-600">{{ $order->orderItems->map(fn($item) => $item->busSeat->seat_number)->join(', ') }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Instructions Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6 mb-8">
            <h2 class="font-black text-slate-900 text-lg flex items-center">
                <svg class="w-5 h-5 text-brand-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Instruksi Pembayaran &bull; {{ $order->payment ? $order->payment->payment_method : 'Transfer Bank' }}
            </h2>

            <!-- Virtual Account / Payment Details Box -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200" x-data="{ copied: false }">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Nomor Virtual Account</span>
                        <div class="text-2xl font-mono font-black text-slate-900 mt-0.5 tracking-wider">
                            8277 08{{ substr($order->order_code, -6) }}
                        </div>
                        <span class="text-xs text-slate-500">Penerima: <strong>CAN TRAVEL INDONESIA</strong></span>
                    </div>

                    <button type="button" @click="navigator.clipboard.writeText('827708{{ substr($order->order_code, -6) }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                        class="px-4 py-2.5 rounded-xl text-xs font-bold border border-slate-300 hover:border-brand-500 bg-white text-slate-700 hover:text-brand-600 transition shadow-sm flex items-center space-x-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="copied ? 'Tersalin!' : 'Salin Nomor'"></span>
                    </button>
                </div>
            </div>

            <!-- Steps Guide -->
            <div class="text-xs text-slate-600 space-y-2 border-t border-slate-100 pt-4">
                <p class="font-bold text-slate-800">Petunjuk Pembayaran ATM / Mobile Banking:</p>
                <ol class="list-decimal list-inside space-y-1.5 text-slate-500">
                    <li>Buka aplikasi Mobile Banking pilihan Anda (BCA Mobile, Livin', BRImo, dll).</li>
                    <li>Pilih menu <strong>Transfer / Bayar</strong> &gt; <strong>Virtual Account</strong>.</li>
                    <li>Masukkan nomor Virtual Account di atas.</li>
                    <li>Periksa kesesuaian penerima (CAN Travel) dan nominal (<strong class="text-slate-700">{{ $order->formatted_total }}</strong>).</li>
                    <li>Masukkan PIN Anda untuk menyelesaikan transaksi.</li>
                </ol>
            </div>
        </div>

        <!-- Simulation Sandbox -->
        <div class="bg-gradient-to-br from-slate-900 to-navy-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl mb-8">
            <div class="flex items-center space-x-2.5 mb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <h3 class="font-bold text-xs tracking-wider uppercase text-brand-300">Simulasi Pembayaran (Mode Pengujian)</h3>
            </div>
            <p class="text-xs text-slate-300 mb-6 leading-relaxed">
                Untuk kemudahan evaluasi technical test, Anda dapat langsung mengonfirmasi pembayaran secara instan tanpa transfer sungguhan. E-Tiket akan langsung diterbitkan secara otomatis.
            </p>

            <form action="{{ route('booking.processPayment', $order) }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-sm shadow-xl shadow-emerald-600/30 transition flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Konfirmasi Pembayaran (Simulasi Instan)</span>
                </button>
            </form>
        </div>

        <!-- Action Links (Task 14) -->
        <div class="flex flex-col sm:flex-row justify-center items-center gap-4 text-center">
            <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white border border-slate-300 hover:border-slate-400 text-slate-800 font-bold text-xs transition shadow-sm">
                Lihat Detail Pesanan
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl text-slate-500 hover:text-slate-800 font-semibold text-xs transition">
                Kembali ke Beranda
            </a>
        </div>

    </div>
</div>
@endsection
