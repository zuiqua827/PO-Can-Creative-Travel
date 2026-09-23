@extends('layouts.app')

@section('title', '422 - Data Tidak Valid - PO CAN Travel')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center space-y-6 bg-white p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-100">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 font-black text-2xl flex items-center justify-center mx-auto">
            422
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Data Tidak Valid</h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-500 leading-relaxed">
                Permintaan tidak dapat diproses karena data masukan yang dikirimkan tidak memenuhi persyaratan validasi.
            </p>
        </div>
        <div class="pt-2">
            <a href="javascript:history.back()" class="inline-flex items-center justify-center w-full py-3.5 px-5 rounded-2xl bg-slate-900 hover:bg-brand-600 text-white font-bold text-sm transition shadow-md">
                Kembali ke Form Sebelumnya
            </a>
        </div>
    </div>
</div>
@endsection
