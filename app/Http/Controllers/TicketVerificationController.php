<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketVerificationController extends Controller
{
    /**
     * Public boarding verification endpoint for conductor & passenger scans.
     */
    public function verify(Request $request, string $token)
    {
        $item = OrderItem::with([
            'order.user',
            'order.trip.route',
            'order.trip.bus',
            'order.payment',
            'busSeat',
        ])->where('ticket_token', $token)->first();

        if (! $item) {
            Log::warning("Ticket verification failed: token not found [{$token}]", ['ip' => $request->ip()]);

            return response()->view('tickets.verify', [
                'item' => null,
                'token' => $token,
                'isValid' => false,
                'statusType' => 'not_found',
                'statusTitle' => 'Tiket Tidak Ditemukan',
                'statusDescription' => 'Kode token tiket ini tidak terdaftar dalam sistem resmi CAN Travel.',
                'verifiedAt' => now(),
            ], 404);
        }

        $order = $item->order;
        $verifiedAt = now();

        Log::info("Ticket verified successfully: {$item->ticket_token} for order {$order->order_code}", [
            'ip' => $request->ip(),
            'order_code' => $order->order_code,
            'passenger' => $item->passenger_name,
        ]);

        if ($order->status === 'cancelled') {
            $statusType = 'cancelled';
            $isValid = false;
            $statusTitle = 'Pesanan Telah Dibatalkan';
            $statusDescription = 'Tiket ini tidak berlaku untuk boarding karena pesanan telah dibatalkan.';
        } elseif ($order->isExpired() || $order->payment_status === 'expired') {
            $statusType = 'expired';
            $isValid = false;
            $statusTitle = 'Tiket Kedaluwarsa';
            $statusDescription = 'Batas waktu pembayaran tiket ini telah terlewati dan kursi telah dilepaskan.';
        } elseif ($order->payment_status === 'unpaid') {
            $statusType = 'unpaid';
            $isValid = false;
            $statusTitle = 'Menunggu Pembayaran';
            $statusDescription = 'Tiket ini belum lunas. Silakan selesaikan pembayaran terlebih dahulu sebelum boarding.';
        } else {
            $statusType = 'valid';
            $isValid = true;
            $statusTitle = 'E-Tiket Resmi & Terverifikasi';
            $statusDescription = 'Tiket sah dan terverifikasi di manifest sistem CAN Travel. Penumpang berhak boarding.';
        }

        return view('tickets.verify', compact(
            'item',
            'order',
            'token',
            'isValid',
            'statusType',
            'statusTitle',
            'statusDescription',
            'verifiedAt'
        ));
    }
}
