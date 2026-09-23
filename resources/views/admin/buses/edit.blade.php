@extends('layouts.admin')

@section('title', 'Edit Armada Bus - ' . $bus->name . ' - PO CAN Travel')
@section('page_title', 'Edit Armada Bus')
@section('page_subtitle', 'Perbarui informasi armada ' . $bus->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm">
        
        <form action="{{ route('admin.buses.update', $bus) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Armada Bus</label>
                <input type="text" name="name" value="{{ old('name', $bus->name) }}" required 
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kode Bus (Unik)</label>
                    <input type="text" name="code" value="{{ old('code', $bus->code) }}" required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-mono font-semibold uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tipe / Kelas Bus</label>
                    <select name="type" required class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="Executive" {{ $bus->type === 'Executive' ? 'selected' : '' }}>Executive</option>
                        <option value="Royal Suite" {{ $bus->type === 'Royal Suite' ? 'selected' : '' }}>Royal Suite</option>
                        <option value="Sleeper Bus" {{ $bus->type === 'Sleeper Bus' ? 'selected' : '' }}>Sleeper Bus</option>
                        <option value="VIP" {{ $bus->type === 'VIP' ? 'selected' : '' }}>VIP</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kapasitas Kursi</label>
                    <input type="number" name="seat_capacity" value="{{ old('seat_capacity', $bus->seat_capacity) }}" min="10" max="60" required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @error('seat_capacity') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status Bus</label>
                    <select name="status" required class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="active" {{ $bus->status === 'active' ? 'selected' : '' }}>Active (Siap Jalan)</option>
                        <option value="maintenance" {{ $bus->status === 'maintenance' ? 'selected' : '' }}>Maintenance (Servis/Perawatan)</option>
                        <option value="inactive" {{ $bus->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200">
                <label class="flex items-center text-xs font-bold text-amber-900 cursor-pointer">
                    <input type="checkbox" name="regenerate_seats" value="1" class="rounded text-brand-600 focus:ring-brand-500 mr-2 h-4 w-4">
                    <span>Buat ulang struktur kursi otomatis (Akan mengatur ulang nomor kursi sesuai kapasitas)</span>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Daftar Fasilitas</label>
                <textarea name="facilities" rows="3" 
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('facilities', is_array($bus->facilities) ? implode(', ', $bus->facilities) : '') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Deskripsi Armada</label>
                <textarea name="description" rows="2" 
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('description', $bus->description) }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.buses.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
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
