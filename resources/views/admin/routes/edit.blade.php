@extends('layouts.admin')

@section('title', 'Edit Rute — CAN Travel')
@section('page_title', 'Edit Rute Perjalanan')
@section('page_subtitle', 'Perbarui detail rute ' . $route->origin . ' → ' . $route->destination)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm">
        
        <form action="{{ route('admin.routes.update', $route) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kota Asal (Terminal)</label>
                    <input type="text" name="origin" value="{{ old('origin', $route->origin) }}" required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @error('origin') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kota Tujuan (Terminal)</label>
                    <input type="text" name="destination" value="{{ old('destination', $route->destination) }}" required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @error('destination') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jarak Tempuh (Km)</label>
                    <input type="text" name="distance" value="{{ old('distance', $route->distance) }}" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Estimasi Durasi Perjalanan</label>
                    <input type="text" name="estimated_duration" value="{{ old('estimated_duration', $route->estimated_duration) }}" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tarif Dasar (Rp)</label>
                    <input type="number" name="base_price" value="{{ old('base_price', $route->base_price) }}" min="10000" step="5000" required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @error('base_price') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status Rute</label>
                    <select name="status" required class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="active" {{ $route->status === 'active' ? 'selected' : '' }}>Active (Aktif)</option>
                        <option value="inactive" {{ $route->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.routes.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
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
