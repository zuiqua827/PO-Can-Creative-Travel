<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        // Enforce Policy authorization
        $this->authorize('cancel', $order);

        if ($order->payment_status === 'paid') {
            return back()->with('error', 'Pesanan yang telah dibayar tidak dapat dibatalkan secara otomatis. Silakan hubungi customer service.');
        }

        $order->update([
            'status' => 'cancelled',
            'payment_status' => 'expired',
        ]);

        return back()->with('success', 'Pesanan telah berhasil dibatalkan.');
    }
}
