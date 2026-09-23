@extends('layouts.admin')

@section('title', 'Edit Jadwal Perjalanan - PO CAN Travel')
@section('page_title', 'Edit Jadwal Perjalanan')
@section('page_subtitle', 'Perbarui waktu atau armada untuk ' . $trip->trip_code)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm">
        
        <form action="{{ route('admin.trips.update', $trip) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Pilih Rute Trayek</label>
                <select name="route_id" required class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}" {{ old('route_id', $trip->route_id) == $route->id ? 'selected' : '' }}>
                            {{ $route->origin }} → {{ $route->destination }}
                        </option>
                    @endforeach
                </select>
                @error('route_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Pilih Armada Bus</label>
                <select name="bus_id" required class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @foreach($buses as $bus)
                        <option value="{{ $bus->id }}" {{ old('bus_id', $trip->bus_id) == $bus->id ? 'selected' : '' }}>
                            {{ $bus->name }} ({{ $bus->type }} - {{ $bus->seat_capacity }} Kursi) [{{ $bus->code }}]
                        </option>
                    @endforeach
                </select>
                @error('bus_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Waktu Keberangkatan</label>
                    <input type="datetime-local" name="departure_at" value="{{ old('departure_at', $trip->departure_at->format('Y-m-d\TH:i')) }}" required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @error('departure_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Estimasi Tiba di Tujuan</label>
                    <input type="datetime-local" name="arrival_at" value="{{ old('arrival_at', $trip->arrival_at->format('Y-m-d\TH:i')) }}" required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @error('arrival_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Harga Tiket per Kursi (Rp)</label>
                    <input type="number" name="price" value="{{ old('price', $trip->price) }}" min="10000" step="5000" required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @error('price') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status Perjalanan</label>
                    <select name="status" required class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="scheduled" {{ $trip->status === 'scheduled' ? 'selected' : '' }}>Scheduled (Terjadwal)</option>
                        <option value="boarding" {{ $trip->status === 'boarding' ? 'selected' : '' }}>Boarding (Proses Masuk Penumpang)</option>
                        <option value="departed" {{ $trip->status === 'departed' ? 'selected' : '' }}>Departed (Sedang di Perjalanan)</option>
                        <option value="completed" {{ $trip->status === 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                        <option value="cancelled" {{ $trip->status === 'cancelled' ? 'selected' : '' }}>Cancelled (Dibatalkan)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Titik Kumpul / Boarding</label>
                    <input type="text" name="boarding_point" value="{{ old('boarding_point', $trip->boarding_point) }}" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Titik Penurunan / Drop-off</label>
                    <input type="text" name="drop_off_point" value="{{ old('drop_off_point', $trip->drop_off_point) }}" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.trips.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-md transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
