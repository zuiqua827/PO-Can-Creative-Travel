@extends('layouts.app')

@section('title', 'Pesanan & E-Tiket Saya - PO CAN Travel')

@section('content')
<div class="bg-slate-50 py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-slate-200 gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Riwayat Tiket & Pesanan</h1>
                <p class="text-xs text-slate-500 mt-1">Kelola tiket perjalanan bus dan unduh e-tiket resmi Anda di sini.</p>
            </div>
            <a href="{{ route('trips.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Pesan Tiket Baru
            </a>
        </div>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="{{ route('orders.index') }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition {{ !$status ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Semua Pesanan
            </a>
            <a href="{{ route('orders.index', ['status' => 'unpaid']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'unpaid' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Menunggu Pembayaran
            </a>
            <a href="{{ route('orders.index', ['status' => 'confirmed']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'confirmed' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Terkonfirmasi (Aktif)
            </a>
            <a href="{{ route('orders.index', ['status' => 'completed']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'completed' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Selesai
            </a>
            <a href="{{ route('orders.index', ['status' => 'cancelled']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'cancelled' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Dibatalkan
            </a>
        </div>

        <!-- Orders Cards Grid -->
        <div class="space-y-5">
            @forelse($orders as $order)
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-sm hover:shadow-md transition">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b border-slate-100 gap-3">
                        <div class="flex items-center space-x-3">
                            <span class="font-mono text-xs font-bold text-slate-800 bg-slate-100 px-3 py-1 rounded-lg">
                                {{ $order->order_code }}
                            </span>
                            <span class="text-xs text-slate-400">Dipesan pada {{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <!-- Status badge -->
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $order->status_badge }}">
                                {{ ucfirst($order->status) }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $order->payment_status_badge }}">
                                {{ $order->payment_status === 'paid' ? 'Lunas' : ($order->payment_status === 'unpaid' ? 'Belum Bayar' : ucfirst($order->payment_status)) }}
                            </span>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="py-5 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                        <div class="md:col-span-5 space-y-1">
                            <span class="text-[10px] font-bold uppercase text-brand-600 tracking-wider">{{ $order->trip->bus->type }} &bull; {{ $order->trip->bus->name }}</span>
                            <h3 class="text-lg font-black text-slate-900">
                                {{ $order->trip->route->origin }} → {{ $order->trip->route->destination }}
                            </h3>
                            <p class="text-xs text-slate-500">
                                Keberangkatan: <strong>{{ $order->trip->departure_at->translatedFormat('l, d M Y, H:i') }} WIB</strong>
                            </p>
                        </div>

                        <div class="md:col-span-4 space-y-1 text-xs text-slate-600">
                            <div>
                                <span class="text-slate-400">Kursi Dipesan:</span> 
                                <strong class="text-emerald-700 font-bold">
                                    {{ $order->orderItems->map(fn($item) => $item->busSeat->seat_number)->join(', ') }}
                                    ({{ $order->orderItems->count() }} Penumpang)
                                </strong>
                            </div>
                            <div>
                                <span class="text-slate-400">Penumpang:</span>
                                <span>{{ $order->orderItems->pluck('passenger_name')->join(', ') }}</span>
                            </div>
                        </div>

                        <div class="md:col-span-3 text-right">
                            <span class="text-xs text-slate-400 block">Total Pembayaran</span>
                            <span class="text-xl font-black text-slate-900">{{ $order->formatted_total }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="text-xs text-slate-400">
                            @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled' && $order->expires_at)
                                <span class="text-amber-600 font-medium">Batas bayar: {{ $order->expires_at->format('H:i') }} WIB</span>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2">
                            @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled')
                                <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 transition">
                                        Batalkan
                                    </button>
                                </form>

                                <a href="{{ route('booking.payment', $order) }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition">
                                    Bayar Sekarang
                                </a>
                            @endif

                            <a href="{{ route('orders.show', $order) }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-brand-600 text-white transition flex items-center space-x-1.5">
                                <span>Lihat E-Tiket</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 bg-white rounded-3xl border border-slate-200 p-8">
                    <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Belum ada pesanan pada filter ini</h3>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto mt-1 mb-6">
                        Anda belum memiliki riwayat pesanan tiket dengan kriteria ini. Mulai cari jadwal bus sekarang!
                    </p>
                    <a href="{{ route('trips.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 text-white text-xs font-bold hover:bg-brand-500 transition">
                        Cari Tiket Bus
                    </a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
