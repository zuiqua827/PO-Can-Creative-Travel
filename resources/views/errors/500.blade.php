@extends('layouts.app')

@section('title', '500 - Terjadi Kesalahan - PO CAN Travel')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center space-y-6 bg-white p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-100">
        <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 font-black text-2xl flex items-center justify-center mx-auto">
            500
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Terjadi Kesalahan Sistem</h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-500 leading-relaxed">
                Maaf, sedang terjadi kendala pada server kami. Tim teknis kami telah diberitahu dan sedang menanganinya.
            </p>
        </div>
        <div class="pt-2">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center w-full py-3.5 px-5 rounded-2xl bg-slate-900 hover:bg-brand-600 text-white font-bold text-sm transition shadow-md">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
