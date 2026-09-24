@extends('layouts.app')

@section('title', 'Jadwal & Tiket Bus — CAN Travel')
@section('meta_description', 'Lihat jadwal keberangkatan bus dan pesan tiket online resmi CAN Travel. Pilihan kelas Executive dan Sleeper, harga transparan, dan ketersediaan kursi real-time.')

@section('content')
<div class="bg-navy-900 py-10 border-b border-navy-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-5">
            <span class="text-xs font-bold text-accent-400 uppercase tracking-wider">Jadwal Keberangkatan Resmi</span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white mt-0.5">Cari &amp; Pesan Jadwal Bus</h1>
        </div>
        
        <!-- Search Filter Header Bar -->
        <form action="{{ route('trips.index') }}" method="GET" class="bg-white/10 backdrop-blur-md p-4 sm:p-5 rounded-2xl border border-white/15 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label for="filter-origin" class="block text-[11px] font-bold uppercase text-slate-300 mb-1">Kota Asal</label>
                <select id="filter-origin" name="origin" class="w-full py-2.5 px-3 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Asal</option>
                    @foreach($origins as $orig)
                        <option value="{{ $orig }}" {{ $origin == $orig ? 'selected' : '' }}>{{ $orig }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-dest" class="block text-[11px] font-bold uppercase text-slate-300 mb-1">Kota Tujuan</label>
                <select id="filter-dest" name="destination" class="w-full py-2.5 px-3 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Tujuan</option>
                    @foreach($destinations as $dest)
                        <option value="{{ $dest }}" {{ $destination == $dest ? 'selected' : '' }}>{{ $dest }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-date" class="block text-[11px] font-bold uppercase text-slate-300 mb-1">Tanggal</label>
                <input id="filter-date" type="date" name="date" value="{{ $date }}" min="{{ date('Y-m-d') }}"
                    class="w-full py-2.5 px-3 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label for="filter-bus" class="block text-[11px] font-bold uppercase text-slate-300 mb-1">Kelas Bus</label>
                <select id="filter-bus" name="bus_type" class="w-full py-2.5 px-3 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Kelas</option>
                    @foreach($busTypes as $bType)
                        <option value="{{ $bType }}" {{ $busType == $bType ? 'selected' : '' }}>{{ $bType }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-xs font-black bg-gradient-to-r from-accent-400 via-accent-500 to-accent-600 hover:from-accent-500 hover:to-accent-600 text-navy-950 shadow-md transition flex items-center justify-center space-x-1.5">
                    <svg class="w-4 h-4 text-navy-950" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>Cari Jadwal</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Active filters bar & sorting -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-slate-200 gap-4 mb-6">
        <div class="text-sm text-slate-600 font-medium">
            Menampilkan <strong class="text-slate-900 font-bold">{{ $trips->total() }}</strong> jadwal perjalanan bus ditemukan
            @if($origin) untuk rute <span class="text-brand-600 font-bold">{{ $origin }}</span> @endif
            @if($destination) ke <span class="text-brand-600 font-bold">{{ $destination }}</span> @endif
        </div>

        <!-- Sort Select -->
        <div class="flex items-center space-x-2 text-xs">
            <span class="text-slate-500 font-bold uppercase tracking-wider">Urutkan:</span>
            <form action="{{ route('trips.index') }}" method="GET" id="sortForm">
                @if($origin)<input type="hidden" name="origin" value="{{ $origin }}">@endif
                @if($destination)<input type="hidden" name="destination" value="{{ $destination }}">@endif
                @if($date)<input type="hidden" name="date" value="{{ $date }}">@endif
                @if($busType)<input type="hidden" name="bus_type" value="{{ $busType }}">@endif
                @if($minPrice)<input type="hidden" name="min_price" value="{{ $minPrice }}">@endif
                @if($maxPrice)<input type="hidden" name="max_price" value="{{ $maxPrice }}">@endif

                <select name="sort" onchange="document.getElementById('sortForm').submit()" 
                    class="py-2 px-3 rounded-xl border border-slate-300 bg-white text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-sm" aria-label="Urutkan Jadwal">
                    <option value="departure_asc" {{ $sortBy == 'departure_asc' ? 'selected' : '' }}>Keberangkatan Paling Awal</option>
                    <option value="departure_desc" {{ $sortBy == 'departure_desc' ? 'selected' : '' }}>Keberangkatan Paling Akhir</option>
                    <option value="price_asc" {{ $sortBy == 'price_asc' ? 'selected' : '' }}>Harga Termurah</option>
                    <option value="price_desc" {{ $sortBy == 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Trips Schedule List -->
    <div class="space-y-5">
        @forelse($trips as $trip)
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-sm hover:shadow-lg transition-all duration-200">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                    
                    <!-- Left: Bus Details -->
                    <div class="lg:col-span-4 space-y-2">
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                {{ $trip->bus->type }}
                            </span>
                            <span class="text-xs text-slate-400 font-mono">{{ $trip->trip_code }}</span>
                        </div>
                        <h2 class="text-lg font-black text-slate-900">{{ $trip->bus->name }}</h2>
                        <p class="text-xs text-slate-500">{{ Str::limit($trip->bus->description, 80) }}</p>

                        <!-- Facilities preview tags -->
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @if(is_array($trip->bus->facilities))
                                @foreach(array_slice($trip->bus->facilities, 0, 3) as $f)
                                    <span class="text-[10px] font-semibold bg-slate-100 text-slate-600 px-2.5 py-0.5 rounded-md">
                                        {{ $f }}
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Middle: Origin & Destination Timeline -->
                    <div class="lg:col-span-5 border-y lg:border-y-0 lg:border-x border-slate-100 py-4 lg:py-0 lg:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Berangkat</span>
                                <div class="text-2xl font-black text-slate-900">{{ $trip->departure_at->format('H:i') }} <span class="text-xs font-semibold text-slate-500">WIB</span></div>
                                <div class="text-xs font-bold text-slate-700 truncate max-w-[130px]" title="{{ $trip->route->origin }}">{{ $trip->route->origin }}</div>
                                <div class="text-[11px] text-slate-400">{{ $trip->departure_at->translatedFormat('d M Y') }}</div>
                            </div>

                            <div class="flex flex-col items-center px-2">
                                <span class="text-[10px] font-bold text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-full mb-1">
                                    {{ $trip->route->estimated_duration ?: 'Langsung' }}
                                </span>
                                <div class="w-16 sm:w-24 h-0.5 bg-slate-300 relative flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-brand-600 absolute -left-1"></div>
                                    <div class="w-2 h-2 rounded-full bg-emerald-600 absolute -right-1"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1">{{ $trip->route->distance }}</span>
                            </div>

                            <div class="text-right">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tiba</span>
                                <div class="text-2xl font-black text-slate-900">{{ $trip->arrival_at->format('H:i') }} <span class="text-xs font-semibold text-slate-500">WIB</span></div>
                                <div class="text-xs font-bold text-slate-700 truncate max-w-[130px]" title="{{ $trip->route->destination }}">{{ $trip->route->destination }}</div>
                                <div class="text-[11px] text-slate-400">{{ $trip->arrival_at->translatedFormat('d M Y') }}</div>
                            </div>
                        </div>

                        <div class="mt-3 text-[11px] text-slate-500">
                            <span>Titik Kumpul: <strong>{{ $trip->boarding_point ?: $trip->route->origin }}</strong></span>
                        </div>
                    </div>

                    <!-- Right: Price & CTA -->
                    <div class="lg:col-span-3 text-right flex flex-col justify-between items-end space-y-4">
                        <div>
                            <span class="text-[11px] text-slate-400 block font-medium">Harga per Kursi</span>
                            <span class="text-2xl font-black text-brand-700">{{ $trip->formatted_price }}</span>
                            <div class="mt-1">
                                @if($trip->available_seats_count > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        Sisa {{ $trip->available_seats_count }} Kursi
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Kursi Penuh
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="w-full">
                            @if($trip->available_seats_count > 0)
                                <a href="{{ route('trips.show', $trip) }}" class="w-full flex items-center justify-center py-3 px-5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-600/25 transition">
                                    <span>Pilih Jadwal</span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            @else
                                <button disabled class="w-full py-3 px-5 rounded-2xl bg-slate-200 text-slate-400 font-bold text-sm cursor-not-allowed">
                                    Kursi Penuh
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-3xl border border-slate-200 p-8">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Belum ada jadwal yang sesuai.</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto mt-1 mb-6">
                    Silakan ubah tanggal atau pilih rute kota asal dan tujuan lainnya untuk melihat jadwal bus CAN Travel.
                </p>
                <a href="{{ route('trips.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                    Reset Filter
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $trips->links() }}
    </div>
</div>
@endsection
