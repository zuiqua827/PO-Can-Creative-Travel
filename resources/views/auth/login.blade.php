@extends('layouts.app')

@section('title', 'Masuk Akun — CAN Travel')
@section('meta_description', 'Masuk ke akun CAN Travel Anda untuk memesan tiket bus dan mengelola e-tiket perjalanan.')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-100">
        
        <!-- Header with CAN Travel Brand -->
        <div class="text-center">
            <div class="flex justify-center mb-4">
                <x-logo size="lg" variant="dark" />
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Selamat Datang Kembali</h1>
            <p class="mt-1 text-xs text-slate-500">Masuk ke akun Anda untuk memesan tiket dan melihat e-tiket.</p>
        </div>

        <!-- Quick 1-Click Test Access -->
        <div class="bg-brand-50/70 border border-brand-100 rounded-2xl p-4">
            <p class="text-[11px] font-bold text-brand-800 uppercase tracking-wider mb-2 flex items-center">
                <span class="w-2 h-2 rounded-full bg-brand-500 mr-2 animate-pulse"></span>
                Akses Cepat Pengujian (1-Click Demo)
            </p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="fillDemo('admin@pocan.com', 'password')" class="text-left px-3 py-2 rounded-xl bg-white border border-brand-200 hover:border-brand-400 text-xs font-semibold text-slate-700 hover:text-brand-700 transition shadow-sm">
                    <div class="font-bold text-brand-600">Admin CAN Travel</div>
                    <div class="text-[10px] text-slate-500">admin@pocan.com</div>
                </button>
                <button type="button" onclick="fillDemo('budi@gmail.com', 'password')" class="text-left px-3 py-2 rounded-xl bg-white border border-brand-200 hover:border-brand-400 text-xs font-semibold text-slate-700 hover:text-brand-700 transition shadow-sm">
                    <div class="font-bold text-emerald-600">Customer Budi</div>
                    <div class="text-[10px] text-slate-500">budi@gmail.com</div>
                </button>
            </div>
        </div>

        <!-- Login Form -->
        <form class="mt-6 space-y-5" action="{{ route('login.post') }}" method="POST">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email</label>
                <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" 
                    class="block w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition @error('email') border-rose-500 @enderror"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required 
                    class="block w-full px-4 py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition @error('password') border-rose-500 @enderror"
                    placeholder="••••••••">
                @error('password')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300 rounded">
                    <label for="remember" class="ml-2 block text-xs font-medium text-slate-600">Ingat saya di perangkat ini</label>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-600/30 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                    Masuk ke Akun
                </button>
            </div>
        </form>

        <div class="text-center pt-2">
            <p class="text-xs text-slate-500">
                Belum memiliki akun? 
                <a href="{{ route('register') }}" class="font-bold text-brand-600 hover:text-brand-700 hover:underline">
                    Daftar sekarang
                </a>
            </p>
        </div>

    </div>
</div>

<script>
    function fillDemo(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
</script>
@endsection
