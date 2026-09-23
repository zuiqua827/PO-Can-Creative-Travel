@extends('layouts.admin')

@section('title', 'Manajemen Pesanan Tiket — CAN Travel')
@section('page_title', 'Kelola Pesanan Tiket')
@section('page_subtitle', 'Monitor seluruh transaksi pemesanan tiket customer')

@section('content')
<div class="space-y-6">
    
    <!-- Filter and Search Bar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Cari Pesanan</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 font-semibold"
                    placeholder="Kode order / nama / HP...">
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Status Order</label>
                <select name="status" class="w-full py-2 px-3 rounded-xl border border-slate-300 font-semibold">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Status Bayar</label>
                <select name="payment_status" class="w-full py-2 px-3 rounded-xl border border-slate-300 font-semibold">
                    <option value="">Semua Status Bayar</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="expired" {{ request('payment_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition">
                    Filter
                </button>
                <a href="{{ route('admin.orders.index') }}" class="py-2.5 px-3 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50 transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-4 px-6">Kode Order</th>
                        <th class="py-4 px-6">Customer Pemesan</th>
                        <th class="py-4 px-6">Jadwal & Rute</th>
                        <th class="py-4 px-6">Kursi</th>
                        <th class="py-4 px-6">Total Tagihan</th>
                        <th class="py-4 px-6">Status Order</th>
                        <th class="py-4 px-6">Pembayaran</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">{{ $order->order_code }}</td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $order->user->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $order->user->email }} &bull; {{ $order->user->phone }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold">{{ $order->trip->route->origin }} → {{ $order->trip->route->destination }}</div>
                                <div class="text-[11px] text-slate-400">{{ $order->trip->bus->name }} &bull; {{ $order->trip->departure_at->translatedFormat('d M Y, H:i') }} WIB</div>
                            </td>
                            <td class="py-4 px-6 font-bold text-emerald-600">
                                {{ $order->orderItems->map(fn($i) => $i->busSeat->seat_number)->join(', ') }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-900">{{ $order->formatted_total }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $order->status_badge }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $order->payment_status_badge }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-brand-600 text-white font-bold transition">
                                    Kelola
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data pesanan sesuai kriteria filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection
