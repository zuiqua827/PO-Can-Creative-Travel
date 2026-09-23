@extends('layouts.admin')

@section('title', 'Daftar Pelanggan - PO CAN Travel')
@section('page_title', 'Daftar Pelanggan')
@section('page_subtitle', 'Pengguna terdaftar dengan riwayat pemesanan tiket bus')

@section('content')
<div class="space-y-6">
    
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900">Direktori Akun Pelanggan</h2>
            <p class="text-xs text-slate-500">Total terdaftar: <strong>{{ $customers->total() }} Pelanggan</strong></p>
        </div>

        <form action="{{ route('admin.customers.index') }}" method="GET" class="flex items-center space-x-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" 
                class="py-2 px-3.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none w-full sm:w-64"
                placeholder="Cari nama, email, HP...">
            <button type="submit" class="py-2 px-4 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                Cari
            </button>
        </form>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-4 px-6">Pelanggan</th>
                        <th class="py-4 px-6">No. Telepon / WhatsApp</th>
                        <th class="py-4 px-6">Total Pesanan</th>
                        <th class="py-4 px-6">Total Pengeluaran (Paid)</th>
                        <th class="py-4 px-6">Tanggal Bergabung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($customers as $cust)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-sm">
                                        {{ strtoupper(substr($cust->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $cust->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $cust->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 font-semibold">{{ $cust->phone ?: '-' }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                    {{ $cust->orders_count }} Pesanan
                                </span>
                            </td>
                            <td class="py-4 px-6 font-black text-emerald-700 text-sm">
                                Rp {{ number_format($cust->orders_sum_total_amount ?: 0, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-slate-500 font-medium">
                                {{ $cust->created_at->translatedFormat('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Belum ada data pelanggan yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $customers->links() }}
        </div>
    </div>

</div>
@endsection
