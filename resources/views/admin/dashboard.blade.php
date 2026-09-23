@extends('layouts.admin')

@section('title', 'Admin Dashboard - PO CAN Travel')
@section('page_title', 'Dashboard Operasional')
@section('page_subtitle', 'Ringkasan performa pemesanan dan armada bus')

@section('content')
<div class="space-y-8">
    
    <!-- Top KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Revenue Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl">
                Rp
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-slate-400">Total Pendapatan</span>
                <div class="text-xl font-black text-slate-900 mt-0.5">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-emerald-600 font-semibold">{{ $paidOrdersCount }} Pesanan Berhasil</span>
            </div>
        </div>

        <!-- Total Orders Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-slate-400">Total Pesanan</span>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $totalOrders }}</div>
                <span class="text-[11px] text-amber-600 font-semibold">{{ $pendingOrdersCount }} Menunggu Bayar</span>
            </div>
        </div>

        <!-- Active Trips Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-slate-400">Jadwal Aktif</span>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $activeTrips }}</div>
                <span class="text-[11px] text-slate-500 font-medium">Jadwal Keberangkatan</span>
            </div>
        </div>

        <!-- Fleet Count Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-8 5h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-slate-400">Total Armada</span>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $totalBuses }}</div>
                <span class="text-[11px] text-slate-500 font-medium">{{ $totalRoutes }} Rute Tersedia</span>
            </div>
        </div>

    </div>

    <!-- Departures Today Section -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-black text-slate-900">Keberangkatan Hari Ini</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ date('d F Y') }} &bull; Pantau status armada dan okupansi kursi</p>
            </div>
            <a href="{{ route('admin.trips.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                Semua Jadwal &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="pb-3">Kode Trip</th>
                        <th class="pb-3">Rute</th>
                        <th class="pb-3">Armada Bus</th>
                        <th class="pb-3">Jam Berangkat</th>
                        <th class="pb-3">Okupansi Kursi</th>
                        <th class="pb-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($todayDepartures as $trip)
                        @php
                            $bookedCount = count($trip->getBookedSeatIds());
                            $totalCap = $trip->bus->seat_capacity;
                            $pct = $totalCap > 0 ? round(($bookedCount / $totalCap) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 font-mono font-bold text-slate-900">{{ $trip->trip_code }}</td>
                            <td class="py-3.5 font-bold">{{ $trip->route->origin }} → {{ $trip->route->destination }}</td>
                            <td class="py-3.5">{{ $trip->bus->name }} ({{ $trip->bus->type }})</td>
                            <td class="py-3.5 font-bold text-brand-600">{{ $trip->departure_at->format('H:i') }} WIB</td>
                            <td class="py-3.5">
                                <div class="flex items-center space-x-2">
                                    <div class="w-24 bg-slate-200 rounded-full h-2 overflow-hidden">
                                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="font-semibold">{{ $bookedCount }}/{{ $totalCap }}</span>
                                </div>
                            </td>
                            <td class="py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                    {{ ucfirst($trip->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 font-medium">
                                Tidak ada jadwal keberangkatan bus untuk hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-black text-slate-900">Pesanan Tiket Terbaru</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar transaksi pemesanan tiket terakhir dari customer</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                Kelola Semua Pesanan &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="pb-3">Kode Order</th>
                        <th class="pb-3">Customer</th>
                        <th class="pb-3">Rute & Bus</th>
                        <th class="pb-3">Kursi</th>
                        <th class="pb-3">Total</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 font-mono font-bold text-slate-900">{{ $order->order_code }}</td>
                            <td class="py-3.5">
                                <div class="font-bold text-slate-800">{{ $order->user->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $order->user->phone }}</div>
                            </td>
                            <td class="py-3.5">
                                <div class="font-semibold">{{ $order->trip->route->origin }} → {{ $order->trip->route->destination }}</div>
                                <div class="text-[11px] text-slate-400">{{ $order->trip->bus->name }}</div>
                            </td>
                            <td class="py-3.5 font-bold text-emerald-600">
                                {{ $order->orderItems->map(fn($i) => $i->busSeat->seat_number)->join(', ') }}
                            </td>
                            <td class="py-3.5 font-bold text-slate-900">{{ $order->formatted_total }}</td>
                            <td class="py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $order->status_badge }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-brand-600 text-white font-bold transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400 font-medium">
                                Belum ada pesanan terbaru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
