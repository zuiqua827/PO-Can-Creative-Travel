@extends('layouts.app')

@section('title', 'Pembayaran Pesanan - ' . $order->order_code . ' - PO CAN Travel')

@section('content')
<div class="bg-slate-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Status Header Card -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-600 mx-auto flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-300">
                Menunggu Pembayaran
            </span>
            <h1 class="text-2xl font-black text-slate-900 mt-3">Selesaikan Pembayaran Anda</h1>
            <p class="text-xs text-slate-500 mt-1">Kode Pesanan: <strong class="font-mono text-slate-800">{{ $order->order_code }}</strong></p>

            <!-- Total Amount Big Display -->
            <div class="mt-6 py-4 px-6 rounded-2xl bg-slate-50 border border-slate-200 inline-block">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Total Tagihan</span>
                <span class="text-3xl sm:text-4xl font-black text-brand-700">{{ $order->formatted_total }}</span>
            </div>

            @if($order->expires_at)
                <p class="text-xs text-rose-600 font-medium mt-3">
                    Batas Waktu Pembayaran: <strong>{{ $order->expires_at->translatedFormat('d M Y, H:i') }} WIB</strong>
                </p>
            @endif
        </div>

        <!-- Payment Instructions Card -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm space-y-6 mb-8">
            <h3 class="font-black text-slate-900 text-lg flex items-center">
                <svg class="w-5 h-5 text-brand-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Instruksi Pembayaran &bull; {{ $order->payment ? $order->payment->payment_method : 'Transfer Bank' }}
            </h3>

            <!-- Virtual Account / Payment Details Box -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200" x-data="{ copied: false }">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <span class="text-xs text-slate-500 font-bold uppercase">Nomor Virtual Account / Rekening</span>
                        <div class="text-2xl font-mono font-black text-slate-900 mt-0.5 tracking-wider">
                            8277 08{{ substr($order->order_code, -6) }}
                        </div>
                        <span class="text-xs text-slate-500">Atas Nama: <strong>PT PO CAN TRAVEL INDONESIA</strong></span>
                    </div>

                    <button type="button" @click="navigator.clipboard.writeText('827708{{ substr($order->order_code, -6) }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                        class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-300 hover:border-brand-500 bg-white text-slate-700 hover:text-brand-600 transition shadow-sm flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="copied ? 'Tersalin!' : 'Salin Nomor'"></span>
                    </button>
                </div>
            </div>

            <!-- Steps Guide -->
            <div class="text-xs text-slate-600 space-y-2 border-t border-slate-100 pt-4">
                <p class="font-bold text-slate-800">Petunjuk Pembayaran ATM / Mobile Banking:</p>
                <ol class="list-decimal list-inside space-y-1 text-slate-500">
                    <li>Buka aplikasi Mobile Banking pilihan Anda (BCA Mobile, Livin, BRImo, dll).</li>
                    <li>Pilih menu <strong>Transfer / Bayar</strong> &gt; <strong>Virtual Account</strong>.</li>
                    <li>Masukkan nomor Virtual Account di atas.</li>
                    <li>Periksa kesesuaian nama (PO CAN Travel) dan nominal tagihan (<strong class="text-slate-700">{{ $order->formatted_total }}</strong>).</li>
                    <li>Masukkan PIN Anda untuk menyelesaikan transaksi.</li>
                </ol>
            </div>
        </div>

        <!-- Payment Actions & Sandbox Simulator -->
        <div class="bg-gradient-to-br from-brand-900 to-slate-900 text-white rounded-3xl p-8 shadow-xl">
            <div class="flex items-center space-x-3 mb-4">
                <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
                <h4 class="font-bold text-sm tracking-wider uppercase text-brand-300">Simulasi Pembayaran Instan (Demo Mode)</h4>
            </div>
            <p class="text-xs text-slate-300 mb-6 leading-relaxed">
                Untuk kemudahan pengujian technical test ini, Anda dapat langsung mengonfirmasi pembayaran secara instan tanpa perlu transfer sungguhan. E-Tiket akan langsung diterbitkan otomatis.
            </p>

            <form action="{{ route('booking.processPayment', $order) }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-black text-sm shadow-xl shadow-emerald-500/30 transition flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>⚡ Simulasi Bayar Berhasil Sekarang</span>
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('orders.show', $order) }}" class="text-xs text-slate-400 hover:text-white underline">
                    Lihat Rincian Pesanan
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
