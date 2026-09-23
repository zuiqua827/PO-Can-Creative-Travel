@extends('layouts.app')

@section('title', 'Profil Pengguna - PO CAN Travel')

@section('content')
<div class="bg-slate-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Profil Akun Saya</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola data informasi akun dan kata sandi Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            
            <!-- Left: Profile Info Form -->
            <div class="md:col-span-8 space-y-6">
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                    <h3 class="text-base font-black text-slate-900 mb-6 pb-3 border-b border-slate-100">
                        Informasi Pribadi
                    </h3>

                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            @error('name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email (Akun)</label>
                            <input type="email" value="{{ $user->email }}" disabled
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-sm font-semibold cursor-not-allowed">
                            <span class="text-[11px] text-slate-400 mt-1 block">Email tidak dapat diubah demi keamanan akun.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nomor WhatsApp / HP</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            @error('phone')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Change section -->
                        <div class="pt-6 border-t border-slate-100 space-y-4">
                            <h4 class="text-sm font-bold text-slate-800">Ubah Kata Sandi (Opsional)</h4>
                            
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi Saat Ini</label>
                                <input type="password" name="current_password"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                    placeholder="Masukkan jika ingin mengganti kata sandi">
                                @error('current_password')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi Baru</label>
                                    <input type="password" name="new_password"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                        placeholder="Minimal 6 karakter">
                                    @error('new_password')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Ulangi Kata Sandi Baru</label>
                                    <input type="password" name="new_password_confirmation"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                        placeholder="Ulangi kata sandi baru">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="py-3 px-6 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm shadow-md transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Account Overview Card -->
            <div class="md:col-span-4 space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm text-center">
                    <div class="w-20 h-20 rounded-full bg-brand-100 text-brand-700 text-2xl font-black flex items-center justify-center mx-auto mb-4">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h3 class="text-lg font-black text-slate-900">{{ $user->name }}</h3>
                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                    <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ ucfirst($user->role) }} Terverifikasi
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-100 text-left space-y-3 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Total Tiket:</span>
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
