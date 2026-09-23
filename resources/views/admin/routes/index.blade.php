@extends('layouts.admin')

@section('title', 'Manajemen Rute Perjalanan - PO CAN Travel')
@section('page_title', 'Kelola Rute Perjalanan')
@section('page_subtitle', 'Daftar rute asal, tujuan, jarak, dan tarif dasar')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-black text-slate-900">Daftar Trayek & Rute Antarkota</h2>
            <p class="text-xs text-slate-500">Rute digunakan sebagai acuan jadwal operasional armada bus.</p>
        </div>
        <a href="{{ route('admin.routes.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Rute Baru
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-4 px-6">Kota Asal</th>
                        <th class="py-4 px-6">Kota Tujuan</th>
                        <th class="py-4 px-6">Estimasi Jarak & Durasi</th>
                        <th class="py-4 px-6">Tarif Dasar</th>
                        <th class="py-4 px-6">Total Jadwal</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($routes as $route)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-6 font-bold text-slate-900">{{ $route->origin }}</td>
                            <td class="py-4 px-6 font-bold text-slate-900">{{ $route->destination }}</td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-800">{{ $route->distance ?: '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $route->estimated_duration ?: '-' }}</div>
                            </td>
                            <td class="py-4 px-6 font-bold text-brand-700">{{ $route->formatted_price }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                    {{ $route->trips_count }} Jadwal
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $route->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ ucfirst($route->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.routes.edit', $route) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.routes.destroy', $route) }}" method="POST" onsubmit="return confirm('Hapus rute ini?')">
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
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada rute yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $routes->links() }}
        </div>
    </div>

</div>
@endsection
