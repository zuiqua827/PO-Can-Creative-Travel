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
            $query->where('payment_status', 'unpaid')->where('status', '!=', 'cancelled');
        } elseif ($status === 'confirmed') {
            $query->where('status', 'confirmed');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('orders.index', compact('orders', 'status'));
    }

    public function show(Order $order)
    {
        // Authorization check
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak berhak melihat pesanan ini.');
        }

        $order->load(['trip.route', 'trip.bus', 'orderItems.busSeat', 'payment', 'user']);

        return view('orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

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
