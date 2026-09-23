<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'trip.route', 'trip.bus', 'orderItems.busSeat'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('orderItems', function ($sub) use ($search) {
                        $sub->where('passenger_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'trip.route', 'trip.bus', 'orderItems.busSeat', 'payment']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(OrderStatusRequest $request, Order $order)
    {
        $validated = $request->validated();

        $order->update($validated);

        if ($order->payment) {
            $paymentStatus = match ($validated['payment_status']) {
                'paid' => 'success',
                'expired' => 'expired',
                'refunded' => 'refunded',
                default => 'pending',
            };
            $order->payment->update([
                'status' => $paymentStatus,
                'paid_at' => ($validated['payment_status'] === 'paid' && ! $order->payment->paid_at) ? now() : $order->payment->paid_at,
            ]);
        }

        return back()->with('success', "Status pesanan {$order->order_code} berhasil diperbarui!");
    }
}
