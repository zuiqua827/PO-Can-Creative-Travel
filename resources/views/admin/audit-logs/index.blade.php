@extends('layouts.admin')

@section('title', 'Audit Log Sistem — CAN Travel')
@section('page_title', 'Audit Log & Jejak Keamanan')
@section('page_subtitle', 'Catatan aktivitas sensitif operasional dan audit kepatuhan keamanan sistem CAN Travel')

@section('content')
<div class="space-y-6">
    
    <!-- Filter Container -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Filter Riwayat Audit</h2>
                <p class="text-xs text-slate-500 mt-0.5">Saring jejak aktivitas berdasarkan aksi, aktor pelaksana, atau tanggal kejadian.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                Total {{ $logs->total() }} Catatan
            </span>
        </div>

        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Aksi / Event</label>
                <input type="text" name="action" value="{{ request('action') }}" 
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 font-semibold"
                    placeholder="Contoh: update_status, reconciliation...">
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Aktor Pelaksana</label>
                <input type="text" name="actor" value="{{ request('actor') }}" 
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 font-semibold"
                    placeholder="Nama / email aktor...">
            </div>

            <div>
                <label class="block font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}" 
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 font-semibold">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition shadow-sm">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['action', 'actor', 'date']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition text-center shrink-0">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-6">Waktu Kejadian</th>
                        <th class="py-3.5 px-6">Aktor</th>
                        <th class="py-3.5 px-6">Aksi</th>
                        <th class="py-3.5 px-6">Target</th>
                        <th class="py-3.5 px-6">Rincian Metadata</th>
                        <th class="py-3.5 px-6">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-6 whitespace-nowrap font-mono text-slate-500 text-[11px]">
                                {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-bold text-slate-900 block">{{ $log->actor_name ?: 'System' }}</span>
                                @if($log->user)
                                    <span class="text-[10px] text-slate-400">{{ $log->user->email }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6">
                                @if($log->target_type)
                                    <span class="font-mono text-slate-600 block">{{ class_basename($log->target_type) }} #{{ $log->target_id }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 font-mono text-[10px] text-slate-600 max-w-xs truncate">
                                @if($log->metadata)
                                    {{ json_encode($log->metadata) }}
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 font-mono text-slate-500 text-[11px] whitespace-nowrap">
                                {{ $log->ip ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 font-medium">
                                Belum ada catatan audit log yang terekam.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-6 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
