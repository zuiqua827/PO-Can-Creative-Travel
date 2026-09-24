<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\BookingRequest;
use App\Models\BusSeat;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Trip;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Services\Payment\PaymentGatewayInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        protected PaymentGatewayInterface $paymentGateway
    ) {}

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

        $seatIdsRaw = $request->input('seat_ids') ?? old('seats');
        $seatIds = is_array($seatIdsRaw) ? $seatIdsRaw : explode(',', (string) $seatIdsRaw);
        $seatIds = array_filter(array_map('intval', $seatIds));

        if (empty($seatIds)) {
            return redirect()->route('trips.show', $trip)
                ->with('error', 'Silakan pilih minimal 1 kursi sebelum melanjutkan pemesanan.');
        }

        if (count($seatIds) > 5) {
            return redirect()->route('trips.show', $trip)
                ->with('error', 'Maksimal pemesanan adalah 5 kursi per transaksi.');
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
                            ->where('payment_status', '!=', 'expired')
                            ->where(function ($sub) {
                                $sub->where('payment_status', 'paid')
                                    ->orWhere(function ($pending) {
                                        $pending->where('payment_status', 'unpaid')
                                            ->where('expires_at', '>', now());
                                    });
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
                $orderCode = 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5));

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

                // 7. Create Payment record with provider
                Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'payment_method' => $validated['payment_method'],
                    'provider' => $this->paymentGateway->getProviderName(),
                    'payment_reference' => 'PAY-'.strtoupper(Str::random(10)),
                    'amount' => $totalAmount,
                    'status' => 'pending',
                    'paid_at' => null,
                ]);

                return $order;
            });

            // Dispatch customer booking notification
            try {
                $user->notify(new BookingCreatedNotification($order));
            } catch (\Throwable $e) {
                Log::channel('booking')->error("Failed to notify user for booking {$order->order_code}: ".$e->getMessage());
            }

            Log::channel('booking')->info("Booking created successfully: {$order->order_code}", [
                'user_id' => $user->id,
                'trip_id' => $trip->id,
                'seats_count' => count($seatIds),
                'total_amount' => $order->total_amount,
            ]);

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

        // Check if order expired and synchronize status
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

        // If order is already paid, redirect directly to order details
        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Pesanan ini sudah lunas. Anda dapat melihat tiket dan detail pesanan Anda di sini.');
        }

        return view('booking.payment', compact('order'));
    }

    /**
     * Process payment (instant simulation or confirmation)
     */
    public function processPayment(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        // Validate upload security (MIME, max size 2MB)
        $request->validate([
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ], [
            'payment_proof.mimes' => 'Bukti pembayaran harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'payment_proof.max' => 'Ukuran berkas bukti pembayaran maksimal 2 MB.',
        ]);

        // 1. Safe Idempotency: If already paid, redirect without modifying
        if ($order->payment_status === 'paid' || $order->status === 'confirmed') {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Pesanan ini sudah dibayar sebelumnya.');
        }

        // 2. Prevent payment on cancelled or completed orders
        if (in_array($order->status, ['cancelled', 'completed'])) {
            return redirect()->route('orders.show', $order)
                ->with('error', "Pesanan berstatus {$order->status} tidak dapat diproses pembayarannya.");
        }

        // 3. Backend verification of expiration deadline
        if ($order->isExpired()) {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'expired',
                ]);
                if ($order->payment && $order->payment->status !== 'expired') {
                    $order->payment->update(['status' => 'expired']);
                }
            });

            return redirect()->route('orders.show', $order)
                ->with('error', 'Batas waktu pembayaran pesanan ini telah habis (Kedaluwarsa). Kursi telah dilepaskan kembali.');
        }

        // Handle secure proof upload if provided
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofFile = $request->file('payment_proof');
            $extension = $proofFile->getClientOriginalExtension() ?: 'png';
            $safeFileName = Str::random(40).'.'.strtolower($extension);
            $proofPath = $proofFile->storeAs('payment_proofs', $safeFileName, 'public');
        }

        // Delegate to PaymentGatewayInterface
        $paymentResult = $this->paymentGateway->charge($order, array_merge($request->all(), [
            'proof_file' => $proofPath,
        ]));

        if ($paymentResult->isExpired()) {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'expired',
                ]);
                if ($order->payment && $order->payment->status !== 'expired') {
                    $order->payment->update([
                        'status' => 'expired',
                        'expired_at' => now(),
                    ]);
                }
            });

            Log::channel('payments')->warning("Payment marked expired for order: {$order->order_code}");

            return redirect()->route('orders.show', $order)
                ->with('error', $paymentResult->getMessage() ?: 'Batas waktu pembayaran pesanan ini telah habis (Kedaluwarsa). Kursi telah dilepaskan kembali.');
        }

        if ($paymentResult->isFailed()) {
            DB::transaction(function () use ($order) {
                if ($order->payment) {
                    $order->payment->update([
                        'status' => 'failed',
                        'failed_at' => now(),
                    ]);
                }
            });

            Log::channel('payments')->warning("Payment failed for order: {$order->order_code}");

            return redirect()->route('booking.payment', $order)
                ->with('error', $paymentResult->getMessage() ?: 'Pembayaran gagal diproses. Silakan coba kembali.');
        }

        // Process successful payment atomically
        DB::transaction(function () use ($order, $proofPath, $paymentResult) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if ($lockedOrder->payment_status === 'paid') {
                return;
            }

            // Update payment record
            if ($lockedOrder->payment) {
                $lockedOrder->payment->update([
                    'status' => 'success',
                    'provider' => $this->paymentGateway->getProviderName(),
                    'provider_transaction_id' => $paymentResult->getTransactionId(),
                    'paid_at' => now(),
                    'proof_file' => $proofPath ?: $lockedOrder->payment->proof_file,
                    'metadata' => array_merge($lockedOrder->payment->metadata ?? [], [
                        'charge_result' => $paymentResult->getPayload(),
                    ]),
                ]);
            }

            // Update order status to confirmed
            $lockedOrder->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'expires_at' => null,
            ]);
        });

        Log::channel('payments')->info("Payment processed successfully for order: {$order->order_code}", [
            'amount' => $order->total_amount,
            'transaction_id' => $paymentResult->getTransactionId(),
        ]);

        // Dispatch customer payment notification
        try {
            $order->user?->notify(new PaymentReceivedNotification($order));
        } catch (\Throwable $e) {
            Log::channel('payments')->error("Failed to notify user for payment {$order->order_code}: ".$e->getMessage());
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pembayaran berhasil dikonfirmasi! E-Tiket Anda telah terbit.');
    }
}
