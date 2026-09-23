@extends('layouts.admin')

@section('title', 'Detail Pesanan ' . $order->order_code . ' — CAN Travel')
@section('page_title', 'Detail Pesanan: ' . $order->order_code)
@section('page_subtitle', 'Tinjau rincian tiket, manifest penumpang, dan perbarui status')

@section('content')
<div class="space-y-6 max-w-5xl">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-slate-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Pesanan
        </a>
        <a href="{{ route('orders.show', $order) }}" target="_blank" class="inline-flex items-center px-3.5 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50">
            Pratinjau E-Tiket Penumpang &rarr;
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column: Details -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Order Header Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b border-slate-100 gap-3">
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400">Kode Pemesanan</span>
                        <div class="text-2xl font-mono font-black text-slate-900">{{ $order->order_code }}</div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $order->status_badge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $order->payment_status_badge }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                </div>

                <!-- Trip Info -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Customer Akun</span>
                        <span class="font-bold text-slate-800">{{ $order->user->name }}</span>
                        <span class="text-[11px] text-slate-400 block">{{ $order->user->email }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Jadwal Keberangkatan</span>
                        <span class="font-bold text-slate-800">{{ $order->trip->departure_at->format('H:i') }} WIB</span>
                        <span class="text-[11px] text-slate-400 block">{{ $order->trip->departure_at->translatedFormat('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Armada Bus</span>
                        <span class="font-bold text-slate-800">{{ $order->trip->bus->name }}</span>
                        <span class="text-[11px] text-brand-600 block font-semibold">{{ $order->trip->bus->type }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Rute Trayek</span>
                        <span class="font-bold text-slate-800">{{ $order->trip->route->origin }} → {{ $order->trip->route->destination }}</span>
                    </div>
                </div>
            </div>

            <!-- Passenger Manifest Table -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4">Manifest Penumpang ({{ $order->orderItems->count() }} Orang)</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase">
                                <th class="py-3 px-4">Kursi</th>
                                <th class="py-3 px-4">Nama Penumpang</th>
                                <th class="py-3 px-4">Kontak / HP</th>
                                <th class="py-3 px-4">NIK / KTP</th>
                                <th class="py-3 px-4 text-right">Tarif</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($order->orderItems as $item)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3.5 px-4">
                                        <span class="w-8 h-8 rounded-lg bg-emerald-500 text-white font-bold inline-flex items-center justify-center text-xs">
                                            {{ $item->busSeat->seat_number }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-slate-900">{{ $item->passenger_name }}</td>
                                    <td class="py-3.5 px-4">{{ $item->passenger_phone ?: '-' }}</td>
                                    <td class="py-3.5 px-4 font-mono">{{ $item->passenger_id_number ?: '-' }}</td>
                                    <td class="py-3.5 px-4 text-right font-bold text-slate-900">{{ $item->formatted_price }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4">Informasi Transaksi & Pembayaran</h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Metode</span>
                        <span class="font-bold text-slate-800">{{ $order->payment ? $order->payment->payment_method : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">No. Referensi</span>
                        <span class="font-mono font-bold text-slate-800">{{ $order->payment ? $order->payment->payment_reference : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Waktu Pembayaran</span>
                        <span class="font-bold text-slate-800">{{ $order->payment && $order->payment->paid_at ? $order->payment->paid_at->translatedFormat('d M Y, H:i') : 'Belum Dibayar' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Total Nominal</span>
                        <span class="font-black text-brand-700 text-sm">{{ $order->formatted_total }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Quick Status Update Box -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-lg sticky top-24">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                    Perbarui Status Pesanan
                </h3>

                <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Status Pesanan</label>
                        <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                            <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmed (Terkonfirmasi)</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed (Selesai Perjalanan)</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled (Dibatalkan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Status Pembayaran</label>
                        <select name="payment_status" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid (Belum Lunas)</option>
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                            <option value="expired" {{ $order->payment_status === 'expired' ? 'selected' : '' }}>Expired (Kadaluarsa)</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded (Dikembalikan)</option>
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md transition">
                            Simpan Perubahan Status
                        </button>
                    </div>
                </form>

                @if($order->notes)
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <span class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Catatan Penumpang:</span>
                        <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200 italic">
                            "{{ $order->notes }}"
                        </p>
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
