@extends('layouts.app')

@section('title', 'Profil Akun — CAN Travel')
@section('meta_description', 'Kelola informasi profil akun pengguna dan kata sandi Anda di CAN Travel.')

@section('content')
<div class="bg-slate-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Profil Akun Saya</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola informasi kontak dan pembaruan kata sandi akun CAN Travel Anda.</p>
        </div>

        @if(session('status') || session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center space-x-3 shadow-sm" role="alert">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') ?? session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center space-x-3 shadow-sm" role="alert">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            
            <!-- Left: Profile Info Form -->
            <div class="md:col-span-8 space-y-6">
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                    <h2 class="text-base font-black text-slate-900 mb-6 pb-3 border-b border-slate-100">
                        Informasi Pribadi
                    </h2>

                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap Sesuai KTP</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none @error('name') border-rose-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email (Akun)</label>
                            <input id="email" type="email" value="{{ $user->email }}" disabled
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-sm font-semibold cursor-not-allowed">
                            <span class="text-[11px] text-slate-400 mt-1 block">Email tidak dapat diubah demi perlindungan keamanan transaksi.</span>
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nomor WhatsApp / HP Aktif</label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none @error('phone') border-rose-500 @enderror">
                            @error('phone')
                                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Change section -->
                        <div class="pt-6 border-t border-slate-100 space-y-4">
                            <h3 class="text-sm font-bold text-slate-800">Ubah Kata Sandi (Opsional)</h3>
                            
                            <div>
                                <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi Saat Ini</label>
                                <input id="current_password" type="password" name="current_password"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none @error('current_password') border-rose-500 @enderror"
                                    placeholder="Masukkan jika ingin mengganti kata sandi">
                                @error('current_password')
                                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="new_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi Baru</label>
                                    <input id="new_password" type="password" name="new_password"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none @error('new_password') border-rose-500 @enderror"
                                        placeholder="Minimal 6 karakter">
                                    @error('new_password')
                                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="new_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Ulangi Kata Sandi Baru</label>
                                    <input id="new_password_confirmation" type="password" name="new_password_confirmation"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                        placeholder="Ulangi kata sandi baru">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="py-3 px-6 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-600/25 transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Account Overview Card -->
            <div class="md:col-span-4 space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm text-center">
                    <div class="w-20 h-20 rounded-2xl bg-brand-100 text-brand-700 text-2xl font-black flex items-center justify-center mx-auto mb-4 shadow-inner">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h2 class="text-lg font-black text-slate-900">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                    <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ ucfirst($user->role) }} Terverifikasi
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-100 text-left space-y-3 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Total Transaksi:</span>
                            <span class="font-bold text-slate-800">{{ $user->orders()->count() }} Pesanan</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Bergabung:</span>
                            <span class="font-semibold">{{ $user->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
