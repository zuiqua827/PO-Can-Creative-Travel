@extends('layouts.app')

@section('title', 'Daftar Akun Baru — CAN Travel')
@section('meta_description', 'Buat akun pelanggan CAN Travel untuk memesan tiket bus online dengan mudah, cepat, dan aman.')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-100">
        
        <!-- Header with Brand Logo -->
        <div class="text-center">
            <div class="flex justify-center mb-4">
                <x-logo size="lg" variant="dark" />
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Daftar Akun Baru</h1>
            <p class="mt-1 text-xs text-slate-500">Daftar sekarang untuk kemudahan reservasi tiket dan e-tiket instan.</p>
        </div>

        <!-- Register Form -->
        <form class="mt-6 space-y-4" action="{{ route('register.post') }}" method="POST">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap Sesuai KTP</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}" 
                    class="block w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition @error('name') border-rose-500 @enderror"
                    placeholder="Contoh: Budi Pratama">
                @error('name')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}" 
                    class="block w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition @error('email') border-rose-500 @enderror"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nomor WhatsApp / HP Aktif</label>
                <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}" 
                    class="block w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition @error('phone') border-rose-500 @enderror"
                    placeholder="Contoh: 081234567890">
                @error('phone')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi</label>
                <input id="password" name="password" type="password" required 
                    class="block w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition @error('password') border-rose-500 @enderror"
                    placeholder="Minimal 6 karakter">
                @error('password')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Ulangi Kata Sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required 
                    class="block w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition"
                    placeholder="Ulangi kata sandi baru">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-600/30 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                    Daftar Sekarang
                </button>
            </div>
        </form>

        <div class="text-center pt-2">
            <p class="text-xs text-slate-500">
                Sudah memiliki akun? 
                <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-700 hover:underline">
                    Masuk di sini
                </a>
            </p>
        </div>

    </div>
</div>
@endsection
