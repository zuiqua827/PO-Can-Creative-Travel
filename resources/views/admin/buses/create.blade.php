@extends('layouts.admin')

@section('title', 'Tambah Armada Bus Baru - PO CAN Travel')
@section('page_title', 'Tambah Armada Bus')
@section('page_subtitle', 'Masukkan data spesifikasi bus dan kapasitas')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm">
        
        <form action="{{ route('admin.buses.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Armada Bus</label>
                <input type="text" name="name" value="{{ old('name') }}" required 
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none"
                    placeholder="Contoh: CAN Royal Suite 05">
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kode Bus (Unik)</label>
                    <input type="text" name="code" value="{{ old('code') }}" required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-mono font-semibold uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none"
                        placeholder="Contoh: CAN-RS-05">
                    @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tipe / Kelas Bus</label>
                    <select name="type" required class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="Executive">Executive</option>
                        <option value="Royal Suite">Royal Suite</option>
                        <option value="Sleeper Bus">Sleeper Bus</option>
                        <option value="VIP">VIP</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kapasitas Kursi</label>
                    <input type="number" name="seat_capacity" value="{{ old('seat_capacity', 28) }}" min="10" max="60" required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <span class="text-[11px] text-slate-400 mt-1 block">Kursi otomatis dibuat dengan konfigurasi 2-2.</span>
                    @error('seat_capacity') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status Bus</label>
                    <select name="status" required class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="active">Active (Siap Jalan)</option>
                        <option value="maintenance">Maintenance (Servis/Perawatan)</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Daftar Fasilitas (Pisahkan dengan koma atau baris baru)</label>
                <textarea name="facilities" rows="3" 
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                    placeholder="Contoh: AC, WiFi Cepat, Reclining Seat, Audio Video, USB Charger, Toilet, Snack">{{ old('facilities') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Deskripsi Armada</label>
                <textarea name="description" rows="2" 
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                    placeholder="Spesifikasi mesin, karoseri, atau kenyamanan tambahan...">{{ old('description') }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.buses.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-md transition">
                    Simpan & Buat Kursi
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
