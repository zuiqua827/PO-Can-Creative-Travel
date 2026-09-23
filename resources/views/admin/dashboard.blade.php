@extends('layouts.admin')

@section('title', 'Admin Dashboard — CAN Travel')
@section('page_title', 'Dashboard Operasional & Analitik')
@section('page_subtitle', 'Ringkasan performa finansial, pemesanan tiket, armada bus, dan okupansi kursi CAN Travel')

@section('content')
<div class="space-y-8">
    
    <!-- Time-Based Period Filter Header -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-800">Periode Analitik Operasional</h2>
            <p class="text-xs text-slate-500 mt-0.5">Filter data transaksi pendapatan dan volume pemesanan berdasarkan rentang waktu.</p>
        </div>

        <div class="flex items-center space-x-1.5 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
            <a href="{{ route('admin.dashboard', ['period' => 'all']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ ($period ?? 'all') === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Semua
            </a>
            <a href="{{ route('admin.dashboard', ['period' => 'today']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ ($period ?? '') === 'today' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Hari Ini
            </a>
            <a href="{{ route('admin.dashboard', ['period' => 'week']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ ($period ?? '') === 'week' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Minggu Ini
            </a>
            <a href="{{ route('admin.dashboard', ['period' => 'month']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ ($period ?? '') === 'month' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Bulan Ini
            </a>
        </div>
    </div>

    <!-- Top KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Revenue Card -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-400">Total Pendapatan</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-sm">
                    Rp
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-black text-slate-900">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-emerald-600 font-semibold">{{ $paidOrdersCount }} Pesanan Lunas</span>
            </div>
        </div>

        <!-- Total Bookings -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-400">Total Pesanan</span>
                <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">{{ $totalOrders }}</div>
                <span class="text-[11px] text-slate-500 font-medium">{{ $todayBookingsCount }} dibuat hari ini</span>
            </div>
        </div>

        <!-- Occupancy Rate KPI -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-400">Okupansi Armada</span>
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-xs">
                    %
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">{{ $occupancyRate }}%</div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ min(100, $occupancyRate) }}%"></div>
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">{{ $totalBooked }}/{{ $totalCapacity }} Kursi Terisi</span>
            </div>
        </div>

        <!-- Pending & Cancelled Orders -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-400">Status Pesanan</span>
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-black text-slate-900">{{ $pendingPaymentsCount }} <span class="text-xs font-normal text-amber-600">Pending</span></div>
                <span class="text-[11px] text-rose-500 font-semibold">{{ $cancelledOrdersCount }} Dibatalkan / Expired</span>
            </div>
        </div>

        <!-- Customers & Fleet -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-slate-400">Armada & Pelanggan</span>
                <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">{{ $totalCustomers }}</div>
                <span class="text-[11px] text-slate-500 font-medium">{{ $totalBuses }} Bus &bull; {{ $totalRoutes }} Rute</span>
            </div>
        </div>

    </div>

    <!-- 2-Column Analytics: Departures Today & Top Routes -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Departures Today Section (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-black text-slate-900">Keberangkatan Hari Ini</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ date('d F Y') }} &bull; {{ $todayDeparturesCount }} jadwal keberangkatan hari ini</p>
                </div>
                <a href="{{ route('admin.trips.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                    Semua Jadwal &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="pb-3">Trip</th>
                            <th class="pb-3">Rute</th>
                            <th class="pb-3">Armada Bus</th>
                            <th class="pb-3">Berangkat</th>
                            <th class="pb-3">Okupansi</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($todayDepartures as $trip)
                            @php
                                $bookedCount = isset($trip->active_booked_seats_count) ? (int)$trip->active_booked_seats_count : count($trip->getBookedSeatIds());
                                $totalCap = $trip->bus->seat_capacity;
                                $pct = $totalCap > 0 ? round(($bookedCount / $totalCap) * 100) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 font-mono font-bold text-slate-900">{{ $trip->trip_code }}</td>
                                <td class="py-3 font-bold">{{ $trip->route->origin }} → {{ $trip->route->destination }}</td>
                                <td class="py-3">{{ $trip->bus->name }}</td>
                                <td class="py-3 font-bold text-brand-600">{{ $trip->departure_at->format('H:i') }} WIB</td>
                                <td class="py-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-16 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="font-semibold">{{ $bookedCount }}/{{ $totalCap }}</span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
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

        <!-- Top Performing Routes (1 Col) -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-black text-slate-900">Rute Terpopuler</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Top rute berdasarkan pesanan lunas</p>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ranking</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($topRoutes as $index => $route)
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-xl {{ $index === 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center text-xs font-black shrink-0">
                                    #{{ $index + 1 }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">{{ $route->origin }} &rarr; {{ $route->destination }}</h4>
                                    <span class="text-[11px] text-slate-500">{{ $route->bookings_count }} Tiket Dipesan</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-emerald-600">Rp {{ number_format($route->total_route_revenue, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Belum ada data pesanan rute pada periode ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 mt-4 text-center">
                <a href="{{ route('admin.routes.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                    Kelola Seluruh Rute &rarr;
                </a>
            </div>
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
