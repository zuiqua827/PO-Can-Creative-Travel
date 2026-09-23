<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Models\Order;
use App\Models\Route as BusRoute;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * Build base query with comprehensive filters
     */
    protected function buildFilteredQuery(Request $request)
    {
        $query = Order::with(['user', 'trip.route', 'trip.bus', 'orderItems.busSeat', 'payment'])
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

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('route_id')) {
            $query->whereHas('trip', function ($q) use ($request) {
                $q->where('route_id', $request->route_id);
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $orders = $this->buildFilteredQuery($request)->paginate(15)->withQueryString();
        $routes = BusRoute::where('status', 'active')->orderBy('origin')->get();

        return view('admin.orders.index', compact('orders', 'routes'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'trip.route', 'trip.bus', 'orderItems.busSeat', 'payment']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Streamed CSV Export with zero memory bloat
     */
    public function export(Request $request): StreamedResponse
    {
        $filename = 'CAN_Travel_Laporan_Pesanan_'.date('Ymd_His').'.csv';

        Log::info('Admin initiated order CSV export', [
            'admin_id' => auth()->id(),
            'filters' => $request->all(),
        ]);

        $query = $this->buildFilteredQuery($request);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write CSV column headers
            fputcsv($handle, [
                'Kode Pesanan',
                'Tanggal Pesan',
                'Nama Pemesan',
                'Email Pemesan',
                'No Telepon',
                'Rute',
                'Armada Bus',
                'Alokasi Kursi',
                'Total Biaya (IDR)',
                'Status Pesanan',
                'Status Pembayaran',
                'Metode Pembayaran',
                'Referensi Pembayaran',
                'Tanggal Bayar',
            ]);

            // Chunk through records to preserve memory
            $query->chunk(100, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    $seats = $order->orderItems->map(fn ($i) => $i->busSeat?->seat_number)->filter()->implode(', ');
                    $route = $order->trip?->route ? "{$order->trip->route->origin} → {$order->trip->route->destination}" : '-';
                    $bus = $order->trip?->bus ? "{$order->trip->bus->name} ({$order->trip->bus->type})" : '-';

                    fputcsv($handle, [
                        $order->order_code,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->user?->name ?? 'Guest',
                        $order->user?->email ?? '-',
                        $order->user?->phone ?? '-',
                        $route,
                        $bus,
                        $seats ?: '-',
                        (float) $order->total_amount,
                        strtoupper($order->status),
                        strtoupper($order->payment_status),
                        $order->payment?->payment_method ?? '-',
                        $order->payment?->payment_reference ?? '-',
                        $order->payment?->paid_at ? $order->payment->paid_at->format('Y-m-d H:i:s') : '-',
                    ]);
                }
                fflush($handle);
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function updateStatus(OrderStatusRequest $request, Order $order)
    {
        $validated = $request->validated();

        // Enforce valid lifecycle transitions
        if (! $order->canTransitionTo($validated['status'])) {
            return back()->with('error', "Perubahan status pesanan dari '{$order->status}' ke '{$validated['status']}' tidak diizinkan oleh sistem.");
        }

        DB::transaction(function () use ($order, $validated) {
            $order->update($validated);

            if ($order->payment) {
                $paymentStatus = match ($validated['payment_status']) {
                    'paid' => 'success',
                    'expired' => 'expired',
                    'refunded' => 'refunded',
                    default => ($validated['status'] === 'cancelled' ? 'failed' : 'pending'),
                };
                $order->payment->update([
                    'status' => $paymentStatus,
                    'paid_at' => ($validated['payment_status'] === 'paid' && ! $order->payment->paid_at) ? now() : $order->payment->paid_at,
                ]);
            }

            Log::info("Admin updated order status for {$order->order_code} to {$validated['status']} [Payment: {$validated['payment_status']}]");
        });

        // Trigger notifications on status transitions
        if ($validated['status'] === 'cancelled') {
            try {
                $order->user?->notify(new BookingCancelledNotification($order));
            } catch (\Throwable $e) {
                Log::error("Failed to notify user for cancelled order {$order->order_code}: ".$e->getMessage());
            }
        } elseif ($validated['payment_status'] === 'paid') {
            try {
                $order->user?->notify(new PaymentReceivedNotification($order));
            } catch (\Throwable $e) {
                Log::error("Failed to notify user for paid order {$order->order_code}: ".$e->getMessage());
            }
        }

        return back()->with('success', "Status pesanan {$order->order_code} berhasil diperbarui!");
    }
}
