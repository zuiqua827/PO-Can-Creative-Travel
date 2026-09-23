<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Notifications\BookingCancelledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $query = Auth::user()->orders()
            ->with(['trip.route', 'trip.bus', 'orderItems.busSeat'])
            ->latest();

        if ($status === 'unpaid') {
            $query->where('payment_status', 'unpaid')->whereNotIn('status', ['cancelled', 'expired']);
        } elseif ($status === 'confirmed') {
            $query->where('status', 'confirmed');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->whereIn('status', ['cancelled', 'expired']);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('orders.index', compact('orders', 'status'));
    }

    public function show(Order $order)
    {
        // Enforce Policy authorization
        $this->authorize('view', $order);

        $order->load(['trip.route', 'trip.bus', 'orderItems.busSeat', 'payment', 'user']);

        // Synchronize expired status if deadline has passed
        if ($order->isExpired() && $order->payment_status !== 'expired') {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'expired',
                ]);
                if ($order->payment && $order->payment->status !== 'expired') {
                    $order->payment->update(['status' => 'expired']);
                }
            });
        }

        return view('orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        // Idempotency: If already cancelled, notify customer safely
        if ($order->status === 'cancelled') {
            return back()->with('info', 'Pesanan ini sudah dibatalkan sebelumnya.');
        }

        // Enforce Policy authorization
        $this->authorize('cancel', $order);

        if ($order->payment_status === 'paid' || $order->status === 'confirmed') {
            return back()->with('error', 'Pesanan yang telah dibayar tidak dapat dibatalkan secara otomatis. Silakan hubungi customer service CAN Travel.');
        }

        if ($order->status === 'completed') {
            return back()->with('error', 'Pesanan yang telah selesai tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'expired',
            ]);

            if ($order->payment) {
                $order->payment->update(['status' => 'failed']);
            }

            Log::info("Order cancelled by customer: {$order->order_code} (User ID: {$order->user_id})");
        });

        try {
            $order->user?->notify(new BookingCancelledNotification($order));
        } catch (\Throwable $e) {
            Log::error("Failed to notify user for cancelled order {$order->order_code}: ".$e->getMessage());
        }

        return back()->with('success', 'Pesanan telah berhasil dibatalkan dan kursi Anda telah dilepaskan kembali.');
    }
}
