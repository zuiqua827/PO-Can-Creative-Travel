@extends('layouts.app')

@section('title', '429 — Terlalu Banyak Permintaan — CAN Travel')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center space-y-6 bg-white p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-100">
        <!-- CAN Travel Official Logo Header -->
        <div class="flex justify-center mb-2">
            <x-logo size="md" variant="dark" />
        </div>

        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 font-black text-2xl flex items-center justify-center mx-auto border border-amber-100 shadow-sm">
            429
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Terlalu Banyak Permintaan</h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-500 leading-relaxed">
                Sistem keamanan kami mendeteksi terlalu banyak aktivitas dalam waktu singkat dari perangkat Anda. Silakan tunggu beberapa saat sebelum mencoba kembali.
            </p>
        </div>
        <div class="pt-2 flex flex-col sm:flex-row gap-3">
            <a href="javascript:location.reload()" class="inline-flex items-center justify-center flex-1 py-3 px-5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm transition shadow-md shadow-brand-600/25">
                Muat Ulang
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center flex-1 py-3 px-5 rounded-2xl bg-navy-900 hover:bg-navy-800 text-white font-bold text-sm transition shadow-md">
                Ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
