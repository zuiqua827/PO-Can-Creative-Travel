@extends('layouts.admin')

@section('title', 'Manajemen Armada Bus - PO CAN Travel')
@section('page_title', 'Kelola Armada Bus')
@section('page_subtitle', 'Daftar bus dan tata letak konfigurasi kursi')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-black text-slate-900">Daftar Bus Operasional</h2>
            <p class="text-xs text-slate-500">Kelola armada, tipe kelas bus, dan status kursi bus.</p>
        </div>
        <a href="{{ route('admin.buses.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Bus Baru
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-4 px-6">Nama & Kode</th>
                        <th class="py-4 px-6">Tipe Kelas</th>
                        <th class="py-4 px-6">Kapasitas Kursi</th>
                        <th class="py-4 px-6">Fasilitas</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($buses as $bus)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900 text-sm">{{ $bus->name }}</div>
                                <div class="font-mono text-slate-400 text-[11px]">{{ $bus->code }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $bus->type }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $bus->seat_capacity }} Kursi</div>
                                <div class="text-[11px] text-slate-400">Layout 2-2</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @if(is_array($bus->facilities))
                                        @foreach(array_slice($bus->facilities, 0, 3) as $fac)
                                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px]">{{ $fac }}</span>
                                        @endforeach
                                        @if(count($bus->facilities) > 3)
                                            <span class="text-slate-400 text-[10px] self-center">+{{ count($bus->facilities) - 3 }}</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $bus->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($bus->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.buses.show', $bus) }}" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold transition" title="Lihat Peta Kursi">
                                        Peta Kursi
                                    </a>
                                    <a href="{{ route('admin.buses.edit', $bus) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.buses.destroy', $bus) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus armada ini?')">
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
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada armada bus yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $buses->links() }}
        </div>
    </div>

</div>
@endsection
