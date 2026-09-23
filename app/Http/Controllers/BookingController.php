<?php

namespace App\Http\Controllers;

use App\Models\BusSeat;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Show checkout form with selected seats and passenger input fields
     */
    public function checkout(Request $request, Trip $trip)
    {
        $seatIdsRaw = $request->input('seat_ids');
        $seatIds = is_array($seatIdsRaw) ? $seatIdsRaw : explode(',', (string)$seatIdsRaw);
        $seatIds = array_filter(array_map('intval', $seatIds));

        if (empty($seatIds)) {
            return redirect()->route('trips.show', $trip)
                ->with('error', 'Silakan pilih minimal 1 kursi sebelum melanjutkan pemesanan.');
        }

        // Verify seats belong to this bus
        $seats = BusSeat::where('bus_id', $trip->bus_id)
            ->whereIn('id', $seatIds)
            ->orderBy('row')
            ->orderBy('column')
            ->get();

        if ($seats->count() !== count($seatIds)) {
            return redirect()->route('trips.show', $trip)
                ->with('error', 'Pilihan kursi tidak valid.');
        }

        // Check if any seat is already booked
        $bookedSeatIds = $trip->getBookedSeatIds();
        $conflicts = array_intersect($seatIds, $bookedSeatIds);

        if (!empty($conflicts)) {
            return redirect()->route('trips.show', $trip)
                ->with('error', 'Maaf, salah satu kursi yang Anda pilih sudah terisi. Silakan pilih kursi lain.');
        }

        $totalAmount = $trip->price * $seats->count();

        return view('booking.checkout', compact('trip', 'seats', 'totalAmount'));
    }

    /**
     * Store order with concurrency control & double booking prevention
     */
    public function store(Request $request, Trip $trip)
    {
        $request->validate([
            'seats' => ['required', 'array', 'min:1'],
            'seats.*' => ['required', 'integer', 'exists:bus_seats,id'],
            'passengers' => ['required', 'array'],
            'passengers.*.name' => ['required', 'string', 'max:255'],
            'passengers.*.phone' => ['required', 'string', 'max:20'],
            'passengers.*.id_number' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'seats.required' => 'Pilihan kursi tidak boleh kosong.',
            'passengers.*.name.required' => 'Nama lengkap setiap penumpang wajib diisi.',
            'passengers.*.phone.required' => 'Nomor telepon setiap penumpang wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        $seatIds = $request->input('seats');
        $passengers = $request->input('passengers');
        $user = Auth::user();

        try {
            $order = DB::transaction(function () use ($trip, $seatIds, $passengers, $user, $request) {
                // 1. Lock and check for conflicts (Double-Booking Prevention)
                $alreadyBooked = OrderItem::whereIn('bus_seat_id', $seatIds)
                    ->whereHas('order', function ($q) use ($trip) {
                        $q->where('trip_id', $trip->id)
                            ->where('status', '!=', 'cancelled')
                            ->where(function ($sub) {
                                $sub->where('payment_status', 'paid')
                                    ->orWhere('expires_at', '>', now());
                            });
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyBooked) {
                    throw new \Exception('Maaf, salah satu kursi yang Anda pilih baru saja dipesan oleh pengguna lain. Silakan pilih kursi kembali.');
                }

                // 2. Calculate total amount
                $seatCount = count($seatIds);
                $totalAmount = $trip->price * $seatCount;
                $orderCode = 'CAN-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                // 3. Create Order
                $order = Order::create([
                    'user_id' => $user->id,
                    'trip_id' => $trip->id,
                    'order_code' => $orderCode,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'expires_at' => Carbon::now()->addHours(2),
                    'notes' => $request->input('notes'),
                ]);

                // 4. Create Order Items for each seat
                foreach ($seatIds as $idx => $seatId) {
                    $passengerData = $passengers[$seatId] ?? [
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'id_number' => null,
                    ];

                    OrderItem::create([
                        'order_id' => $order->id,
                        'bus_seat_id' => $seatId,
                        'passenger_name' => $passengerData['name'] ?? $user->name,
                        'passenger_phone' => $passengerData['phone'] ?? $user->phone,
                        'passenger_id_number' => $passengerData['id_number'] ?? null,
                        'price' => $trip->price,
                    ]);
                }

                // 5. Create Payment record
                Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'payment_method' => $request->input('payment_method'),
                    'payment_reference' => 'PAY-' . strtoupper(Str::random(10)),
                    'amount' => $totalAmount,
                    'status' => 'pending',
                    'paid_at' => null,
                ]);

                return $order;
            });

            return redirect()->route('booking.payment', $order)
                ->with('success', 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.');

        } catch (\Exception $e) {
            return redirect()->route('trips.show', $trip)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show Payment page with instructions and simulation actions
     */
    public function payment(Order $order)
    {
        // Ensure user can only view their own order (or admin)
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $order->load(['trip.route', 'trip.bus', 'orderItems.busSeat', 'payment']);

        // Check if order expired
        if ($order->payment_status === 'unpaid' && $order->expires_at && $order->expires_at->isPast()) {
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'expired',
            ]);
        }

        return view('booking.payment', compact('order'));
    }

    /**
     * Process payment (instant simulation or confirmation)
     */
    public function processPayment(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Pesanan ini sudah dibayar sebelumnya.');
        }

        // Handle proof upload if provided
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        DB::transaction(function () use ($order, $proofPath) {
            // Update payment record
            if ($order->payment) {
                $order->payment->update([
                    'status' => 'success',
                    'paid_at' => now(),
                    'proof_file' => $proofPath,
                ]);
            }

            // Update order status
            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'expires_at' => null,
            ]);
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pembayaran berhasil dikonfirmasi! E-Tiket Anda telah terbit.');
    }
}
