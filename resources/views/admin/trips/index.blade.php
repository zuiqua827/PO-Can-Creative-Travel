@extends('layouts.admin')

@section('title', 'Manajemen Jadwal Keberangkatan — CAN Travel')
@section('page_title', 'Kelola Jadwal / Trips')
@section('page_subtitle', 'Pengaturan jadwal keberangkatan bus dan penetapan tarif tiket')

@section('content')
<div class="space-y-6">
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900">Jadwal Keberangkatan Bus</h2>
            <p class="text-xs text-slate-500">Atur penugasan armada pada rute dan waktu operasional.</p>
        </div>
        <a href="{{ route('admin.trips.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Jadwalkan Perjalanan Baru
        </a>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
        <form action="{{ route('admin.trips.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Rute</label>
                <select name="route_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 font-semibold">
                    <option value="">Semua Rute</option>
                    @foreach($routes as $r)
                        <option value="{{ $r->id }}" {{ request('route_id') == $r->id ? 'selected' : '' }}>
                            {{ $r->origin }} → {{ $r->destination }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Armada</label>
                <select name="bus_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 font-semibold">
                    <option value="">Semua Armada</option>
                    @foreach($buses as $b)
                        <option value="{{ $b->id }}" {{ request('bus_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }} ({{ $b->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full py-2 px-3 rounded-xl border border-slate-300 font-semibold">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition">
                    Filter
                </button>
                <a href="{{ route('admin.trips.index') }}" class="py-2.5 px-3 rounded-xl border border-slate-300 text-slate-600 font-bold hover:bg-slate-50 transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-4 px-6">Kode Trip</th>
                        <th class="py-4 px-6">Rute Trayek</th>
                        <th class="py-4 px-6">Armada Bus</th>
                        <th class="py-4 px-6">Jadwal Berangkat & Tiba</th>
                        <th class="py-4 px-6">Kapasitas Kursi</th>
                        <th class="py-4 px-6">Harga Tiket</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($trips as $trip)
                        @php
                            $booked = isset($trip->active_booked_seats_count) ? (int)$trip->active_booked_seats_count : count($trip->getBookedSeatIds());
                            $totalCap = $trip->bus ? $trip->bus->seat_capacity : 0;
                            $avail = max(0, $totalCap - $booked);
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">{{ $trip->trip_code }}</td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $trip->route->origin }} → {{ $trip->route->destination }}</div>
                                <div class="text-[11px] text-slate-400">Titik Kumpul: {{ $trip->boarding_point ?: '-' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold">{{ $trip->bus->name }}</div>
                                <span class="text-[10px] font-semibold text-brand-600">{{ $trip->bus->type }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $trip->departure_at->translatedFormat('d M Y, H:i') }} WIB</div>
                                <div class="text-[11px] text-slate-400">Tiba: {{ $trip->arrival_at->translatedFormat('d M Y, H:i') }} WIB</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold {{ $avail > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                    {{ $avail }} / {{ $totalCap }} Kursi
                                </div>
                                <div class="text-[11px] text-slate-400 font-medium">{{ $booked }} Terpesan</div>
                            </td>
                            <td class="py-4 px-6 font-black text-brand-700 text-sm">{{ $trip->formatted_price }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $trip->status === 'scheduled' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 
                                      ($trip->status === 'boarding' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 
                                      ($trip->status === 'departed' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 
                                      ($trip->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'))) }}">
                                    {{ ucfirst($trip->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('trips.show', $trip) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold transition" title="Lihat Tampilan Publik">
                                        Lihat Kursi
                                    </a>
                                    <a href="{{ route('admin.trips.edit', $trip) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.trips.destroy', $trip) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini? Jadwal yang sudah memiliki pesanan tidak dapat dihapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-slate-700 text-sm">Belum ada jadwal keberangkatan</h4>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Buat jadwal perjalanan baru untuk membuka pemesanan tiket customer.</p>
                                <a href="{{ route('admin.trips.create') }}" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs transition">
                                    + Tambah Jadwal Baru
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $trips->links() }}
        </div>
    </div>

</div>
@endsection
