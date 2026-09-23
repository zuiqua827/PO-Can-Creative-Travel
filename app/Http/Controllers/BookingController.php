<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\BookingRequest;
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
        // 1. Verify trip is bookable
        if ($trip->status !== 'scheduled' || $trip->departure_at->isPast()) {
            return redirect()->route('trips.index')
                ->with('error', 'Jadwal perjalanan ini sudah tidak dapat dipesan.');
        }

        $seatIdsRaw = $request->input('seat_ids');
        $seatIds = is_array($seatIdsRaw) ? $seatIdsRaw : explode(',', (string) $seatIdsRaw);
        $seatIds = array_filter(array_map('intval', $seatIds));

        if (empty($seatIds)) {
            return redirect()->route('trips.show', $trip)
                ->with('error', 'Silakan pilih minimal 1 kursi sebelum melanjutkan pemesanan.');
        }

        // 2. Verify seats belong to this bus and are operational
        $seats = BusSeat::where('bus_id', $trip->bus_id)
            ->whereIn('id', $seatIds)
            ->where('status', 'available')
            ->orderBy('row')
            ->orderBy('column')
            ->get();

        if ($seats->count() !== count($seatIds)) {
            return redirect()->route('trips.show', $trip)
                ->with('error', 'Salah satu atau lebih kursi yang Anda pilih tidak valid atau sedang dalam perawatan.');
        }

        // 3. Check if any seat is already booked for this trip
        $bookedSeatIds = $trip->getBookedSeatIds();
        $conflicts = array_intersect($seatIds, $bookedSeatIds);

        if (! empty($conflicts)) {
            return redirect()->route('trips.show', $trip)
                ->with('error', 'Maaf, salah satu kursi yang Anda pilih sudah terisi. Silakan pilih kursi lain.');
        }

        // 4. Server-side price calculation
        $totalAmount = $trip->price * $seats->count();

        return view('booking.checkout', compact('trip', 'seats', 'totalAmount'));
    }

    /**
     * Store order with concurrency control & double booking prevention
     */
    public function store(BookingRequest $request, Trip $trip)
    {
        // 1. Verify trip is still bookable
        if ($trip->status !== 'scheduled' || $trip->departure_at->isPast()) {
            return redirect()->route('trips.index')
                ->with('error', 'Jadwal perjalanan ini sudah tidak dapat dipesan.');
        }

        $validated = $request->validated();
        $seatIds = $validated['seats'];
        $passengers = $validated['passengers'];
        $user = Auth::user();

        try {
            $order = DB::transaction(function () use ($trip, $seatIds, $passengers, $user, $validated) {
                // 2. Validate seats belong to this bus
                $seats = BusSeat::where('bus_id', $trip->bus_id)
                    ->whereIn('id', $seatIds)
                    ->where('status', 'available')
                    ->get();

                if ($seats->count() !== count($seatIds)) {
                    throw new \Exception('Pilihan kursi tidak valid untuk armada bus ini.');
                }

                // 3. Concurrency Lock: Lock conflicting seat reservations for this trip
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

                // 4. Server-side total calculation (never trust frontend total)
                $seatCount = count($seatIds);
                $totalAmount = $trip->price * $seatCount;
                $orderCode = 'PCT-'.date('Ymd').'-'.strtoupper(Str::random(5));

                // 5. Create Order
                $order = Order::create([
                    'user_id' => $user->id,
                    'trip_id' => $trip->id,
                    'order_code' => $orderCode,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'expires_at' => Carbon::now()->addHours(2),
                    'notes' => $validated['notes'] ?? null,
                ]);

                // 6. Create Order Items for each seat with passenger info
                foreach ($seatIds as $seatId) {
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

                // 7. Create Payment record
                Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'payment_method' => $validated['payment_method'],
                    'payment_reference' => 'PAY-'.strtoupper(Str::random(10)),
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
        // Policy check for authorization
        $this->authorize('view', $order);

        $order->load(['trip.route', 'trip.bus', 'orderItems.busSeat', 'payment']);

        // Check if order expired
        if ($order->payment_status === 'unpaid' && $order->expires_at && $order->expires_at->isPast()) {
            $order->update([
                'status' => 'expired',
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
        $this->authorize('view', $order);

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
