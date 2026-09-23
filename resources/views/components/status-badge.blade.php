@props([
    'status' => 'pending',
    'type' => 'order', // 'order' or 'payment'
])

@php
    $labels = [
        'pending' => 'Menunggu Pembayaran',
        'confirmed' => 'Terkonfirmasi',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'paid' => 'Lunas',
        'unpaid' => 'Belum Bayar',
        'failed' => 'Gagal',
        'refunded' => 'Dikembalikan',
    ];

    $styles = [
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'confirmed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
        'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'unpaid' => 'bg-amber-50 text-amber-700 border-amber-200',
        'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
        'refunded' => 'bg-slate-50 text-slate-700 border-slate-200',
    ];

    $label = $labels[$status] ?? ucfirst($status);
    $style = $styles[$status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {$style}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ match($status) {
        'confirmed', 'paid' => 'bg-emerald-500',
        'pending', 'unpaid' => 'bg-amber-500',
        'cancelled', 'failed' => 'bg-rose-500',
        'completed' => 'bg-blue-500',
        default => 'bg-slate-400'
    } }}"></span>
    {{ $label }}
</span>
