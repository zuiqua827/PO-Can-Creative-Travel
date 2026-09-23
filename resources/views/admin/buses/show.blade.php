@extends('layouts.admin')

@section('title', 'Peta Kursi Bus ' . $bus->name . ' — CAN Travel')
@section('page_title', 'Peta & Status Kursi: ' . $bus->name)
@section('page_subtitle', 'Konfigurasi visual kursi dan status ketersediaan')

@section('content')
<div class="space-y-6">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.buses.index') }}" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-slate-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Armada
        </a>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.buses.edit', $bus) }}" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-sm hover:bg-slate-800 transition">
                Edit Data Bus
            </a>
        </div>
    </div>

    <!-- Bus Overview Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                    {{ $bus->type }}
                </span>
                <span class="font-mono text-xs font-bold text-slate-400">{{ $bus->code }}</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900">{{ $bus->name }}</h2>
            <p class="text-xs text-slate-500 mt-1">Total Kapasitas: <strong>{{ $bus->seat_capacity }} Kursi</strong> &bull; Status: <strong class="text-emerald-600">{{ ucfirst($bus->status) }}</strong></p>
        </div>

        <!-- Legend -->
        <div class="flex items-center space-x-4 text-xs font-semibold">
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-emerald-500"></span>
                <span class="text-slate-600">Available</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-amber-500"></span>
                <span class="text-slate-600">Blocked</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-rose-500"></span>
                <span class="text-slate-600">Maintenance</span>
            </div>
        </div>
    </div>

    <!-- Interactive Visual Cabin Grid -->
    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm">
        <h3 class="font-bold text-slate-900 text-sm mb-4">Tata Letak Kabin Bus (Klik kursi untuk mengubah status)</h3>

        <div class="max-w-md mx-auto bg-slate-50 rounded-[40px] p-6 border-4 border-slate-200">
            <!-- Front -->
            <div class="flex justify-between items-center pb-4 border-b-2 border-dashed border-slate-300 mb-6 text-xs font-bold text-slate-400">
                <span class="bg-slate-200 text-slate-600 px-3 py-1.5 rounded-xl">Area Pengemudi</span>
                <span>DEPAN</span>
                <span class="bg-white border px-3 py-1 rounded-xl text-slate-500">Pintu</span>
            </div>

            <!-- Seat Rows -->
            <div class="space-y-3">
                @foreach($seatsByRow as $row => $seats)
                    <div class="flex items-center justify-between">
                        <!-- Left Columns A & B -->
                        <div class="flex space-x-2">
                            @foreach($seats->whereIn('column', ['A', 'B']) as $seat)
                                <form action="{{ route('admin.seats.update', $seat) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="{{ $seat->status === 'available' ? 'blocked' : ($seat->status === 'blocked' ? 'maintenance' : 'available') }}">
                                    <button type="submit" 
                                        class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center font-bold text-xs shadow-sm transition hover:scale-105
                                            {{ $seat->status === 'available' ? 'bg-emerald-500 text-white' : ($seat->status === 'blocked' ? 'bg-amber-500 text-white' : 'bg-rose-500 text-white') }}"
                                        title="Klik untuk ubah status: {{ $seat->status }}">
                                        <span>{{ $seat->seat_number }}</span>
                                        <span class="text-[8px] uppercase tracking-tighter opacity-80">{{ substr($seat->status, 0, 4) }}</span>
                                    </button>
                                </form>
                            @endforeach
                        </div>

                        <!-- Aisle -->
                        <div class="text-[10px] text-slate-400 font-mono">R{{ $row }}</div>

                        <!-- Right Columns C & D -->
                        <div class="flex space-x-2">
                            @foreach($seats->whereIn('column', ['C', 'D']) as $seat)
                                <form action="{{ route('admin.seats.update', $seat) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="{{ $seat->status === 'available' ? 'blocked' : ($seat->status === 'blocked' ? 'maintenance' : 'available') }}">
                                    <button type="submit" 
                                        class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center font-bold text-xs shadow-sm transition hover:scale-105
                                            {{ $seat->status === 'available' ? 'bg-emerald-500 text-white' : ($seat->status === 'blocked' ? 'bg-amber-500 text-white' : 'bg-rose-500 text-white') }}"
                                        title="Klik untuk ubah status: {{ $seat->status }}">
                                        <span>{{ $seat->seat_number }}</span>
                                        <span class="text-[8px] uppercase tracking-tighter opacity-80">{{ substr($seat->status, 0, 4) }}</span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Rear -->
            <div class="mt-6 pt-4 border-t-2 border-dashed border-slate-300 flex justify-between text-xs text-slate-400">
                <span>Toilet</span>
                <span class="font-bold">BELAKANG</span>
                <span>Pintu Darurat</span>
            </div>
        </div>
    </div>

</div>
@endsection
