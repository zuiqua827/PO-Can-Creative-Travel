<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Route as BusRoute;
use App\Models\Trip;
use App\Models\User;
use App\Services\Payment\MidtransPaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class Sprint9FinalPolishTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $customer;

    protected User $otherCustomer;

    protected Bus $bus;

    protected BusRoute $route;

    protected Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.sprint9@cantravel.com'],
            [
                'name' => 'Admin Sprint 9',
                'password' => bcrypt('password123'),
                'phone' => '081234567891',
                'role' => 'admin',
            ]
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'customer.sprint9@cantravel.com'],
            [
                'name' => 'Customer Sprint 9',
                'password' => bcrypt('password123'),
                'phone' => '081298765491',
                'role' => 'customer',
            ]
        );

        $this->otherCustomer = User::firstOrCreate(
            ['email' => 'other.sprint9@cantravel.com'],
            [
                'name' => 'Other Customer Sprint 9',
                'password' => bcrypt('password123'),
                'phone' => '081298765492',
                'role' => 'customer',
            ]
        );

        $this->bus = Bus::create([
            'name' => 'CAN Royal Class S9',
            'code' => 'CRC-S9-'.rand(100, 999),
            'type' => 'Executive',
            'seat_capacity' => 12,
            'facilities' => ['AC', 'Reclining Seat', 'WiFi'],
            'status' => 'active',
        ]);
        $this->bus->generateSeats(12);

        $this->route = BusRoute::create([
            'origin' => 'Jakarta',
            'destination' => 'Surabaya',
            'distance' => '780 km',
            'estimated_duration' => '10 Jam',
            'base_price' => 350000,
            'status' => 'active',
        ]);

        $this->trip = Trip::create([
            'trip_code' => 'TRP-S9-'.rand(1000, 9999),
            'bus_id' => $this->bus->id,
            'route_id' => $this->route->id,
            'departure_at' => Carbon::tomorrow()->setTime(9, 0),
            'arrival_at' => Carbon::tomorrow()->setTime(19, 0),
            'price' => 350000,
            'boarding_point' => 'Terminal Pulo Gebang',
            'dropoff_point' => 'Terminal Purabaya',
            'status' => 'scheduled',
        ]);
    }

    /**
     * Helper to create a test order with seats, order items, and payment
     */
    protected function createOrder(User $user, string $status = 'pending', string $paymentStatus = 'unpaid', ?Carbon $expiresAt = null): Order
    {
        $seats = $this->bus->busSeats()->take(2)->get();
        $totalAmount = $this->trip->price * $seats->count();

        $order = Order::create([
            'order_code' => 'CAN-S9-'.strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'trip_id' => $this->trip->id,
            'total_amount' => $totalAmount,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => 'BCA Virtual Account',
            'expires_at' => $expiresAt ?? Carbon::now()->addHours(2),
        ]);

        foreach ($seats as $seat) {
            OrderItem::create([
                'order_id' => $order->id,
                'bus_seat_id' => $seat->id,
                'passenger_name' => 'Passenger '.$seat->seat_number,
                'passenger_phone' => '081234567890',
                'price' => $this->trip->price,
                'ticket_token' => Str::random(40),
            ]);
        }

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'payment_reference' => 'PAY-'.$order->order_code,
            'amount' => $order->total_amount,
            'status' => $paymentStatus === 'paid' ? 'success' : ($paymentStatus === 'expired' ? 'expired' : 'pending'),
            'payment_method' => 'bank_transfer',
        ]);

        return $order;
    }

    /**
     * 1. Unauthorized order access is forbidden (IDOR protection).
     */
    public function test_unauthorized_order_access_forbidden(): void
    {
        $order = $this->createOrder($this->customer);

        // Other customer accessing order details
        $responseView = $this->actingAs($this->otherCustomer)->get(route('orders.show', $order));
        $responseView->assertStatus(403);

        // Other customer attempting to cancel order
        $responseCancel = $this->actingAs($this->otherCustomer)->post(route('orders.cancel', $order));
        $responseCancel->assertStatus(403);

        // Other customer accessing payment page
        $responsePayment = $this->actingAs($this->otherCustomer)->get(route('booking.payment', $order));
        $responsePayment->assertStatus(403);
    }

    /**
     * 2. Expired order handling: payment page marks expired and rejects payment.
     */
    public function test_expired_order_handling(): void
    {
        $order = $this->createOrder($this->customer, 'pending', 'unpaid', Carbon::now()->subMinute());

        // Visiting payment page syncs order to expired/cancelled
        $response = $this->actingAs($this->customer)->get(route('booking.payment', $order));
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('expired', $order->payment_status);

        // Attempting to submit payment on expired order fails
        $responsePay = $this->actingAs($this->customer)->post(route('booking.processPayment', $order), [
            'action' => 'pay',
        ]);
        $responsePay->assertRedirect(route('orders.show', $order));
        $responsePay->assertSessionHas('error');
    }

    /**
     * 3. Paid order payment-page guard: already paid orders redirect to order details.
     */
    public function test_paid_order_payment_page_guard(): void
    {
        $order = $this->createOrder($this->customer, 'confirmed', 'paid');

        $response = $this->actingAs($this->customer)->get(route('booking.payment', $order));
        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('info');

        // Processing payment on already paid order is idempotent
        $responsePay = $this->actingAs($this->customer)->post(route('booking.processPayment', $order), [
            'action' => 'pay',
        ]);
        $responsePay->assertRedirect(route('orders.show', $order));
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    /**
     * 4. Unavailable seat protection: maintenance seats cannot be booked.
     */
    public function test_unavailable_seat_protection(): void
    {
        $seat = $this->bus->busSeats()->first();
        $seat->update(['status' => 'maintenance']);

        $response = $this->actingAs($this->customer)->get(
            route('booking.checkout', [$this->trip, 'seat_ids' => [$seat->id]])
        );

        $response->assertRedirect(route('trips.show', $this->trip));
        $response->assertSessionHas('error');
    }

    /**
     * 5. Duplicate seat protection: already booked seat cannot be booked again.
     */
    public function test_duplicate_seat_protection(): void
    {
        $order = $this->createOrder($this->customer, 'confirmed', 'paid');
        $bookedSeat = $order->orderItems()->first()->bus_seat_id;

        // Attempting to open checkout with an already booked seat
        $response = $this->actingAs($this->otherCustomer)->get(
            route('booking.checkout', [$this->trip, 'seat_ids' => [$bookedSeat]])
        );

        $response->assertRedirect(route('trips.show', $this->trip));
        $response->assertSessionHas('error');
    }

    /**
     * 6. Ticket verification: valid token returns 200 and renders verification details.
     */
    public function test_ticket_verification_with_valid_token(): void
    {
        $order = $this->createOrder($this->customer, 'confirmed', 'paid');
        $item = $order->orderItems()->first();

        $response = $this->get(route('tickets.verify', $item->ticket_token));

        $response->assertStatus(200);
        $response->assertSee($item->passenger_name);
        $response->assertSee($order->order_code);
        $response->assertSee('E-Tiket Resmi');
    }

    /**
     * 7. Invalid ticket verification returns 404.
     */
    public function test_invalid_ticket_verification_returns_404(): void
    {
        $response = $this->get(route('tickets.verify', 'invalid-token-'.Str::random(20)));
        $response->assertStatus(404);
    }

    /**
     * 8. Admin authorization: regular users and guests cannot access admin area.
     */
    public function test_admin_authorization(): void
    {
        // Guest redirected to login
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        // Customer receives 403 Forbidden
        $this->actingAs($this->customer)->get(route('admin.dashboard'))->assertStatus(403);
        $this->actingAs($this->customer)->get(route('admin.orders.index'))->assertStatus(403);

        // Admin has full access
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.orders.index'))->assertStatus(200);
    }

    /**
     * 9. Payment amount server-side integrity: order amount derived from trip price * seat count.
     */
    public function test_payment_amount_integrity(): void
    {
        $seat1 = $this->bus->busSeats()->first();
        $seat2 = $this->bus->busSeats()->skip(1)->first();

        $payload = [
            'seats' => [$seat1->id, $seat2->id],
            'payment_method' => 'BCA Virtual Account',
            'passengers' => [
                $seat1->id => [
                    'name' => 'Budi Santoso',
                    'phone' => '081234567890',
                ],
                $seat2->id => [
                    'name' => 'Siti Aminah',
                    'phone' => '081234567891',
                ],
            ],
            // Malicious client tries to inject cheaper amount
            'total_amount' => 1000,
        ];

        $response = $this->actingAs($this->customer)->post(
            route('booking.store', $this->trip),
            $payload
        );

        $expectedTotal = $this->trip->price * 2;
        $order = Order::where('user_id', $this->customer->id)->latest()->first();

        $this->assertNotNull($order);
        $this->assertEquals($expectedTotal, $order->total_amount);
        $this->assertNotEquals(1000, $order->total_amount);
    }

    /**
     * 10. Duplicate webhook handling is idempotent and preserves order state.
     */
    public function test_duplicate_webhook_handling(): void
    {
        $serverKey = 'SB-Mid-server-sprint9-key';
        Config::set('payment.drivers.midtrans.server_key', $serverKey);
        Config::set('payment.driver', 'midtrans');
        $this->app->bind(PaymentGatewayInterface::class, fn () => app(MidtransPaymentGateway::class));

        $order = $this->createOrder($this->customer, 'pending', 'unpaid');
        $gross = number_format($order->total_amount, 2, '.', '');
        $signature = hash('sha512', $order->order_code.'200'.$gross.$serverKey);
        $txId = 'midtrans-tx-s9-'.uniqid();

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => $gross,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => $txId,
        ];

        // First delivery
        $res1 = $this->postJson('/payments/webhook', $payload);
        $res1->assertStatus(200);
        $this->assertEquals('paid', $order->fresh()->payment_status);

        // Duplicate delivery remains 200 OK without errors
        $res2 = $this->postJson('/payments/webhook', $payload);
        $res2->assertStatus(200);
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    /**
     * 11. Booking validation: empty seats, exceeding 5 seats, and passenger errors are caught.
     */
    public function test_booking_validation(): void
    {
        // Empty seats
        $res1 = $this->actingAs($this->customer)->post(route('booking.store', $this->trip), [
            'seats' => [],
            'payment_method' => 'BCA Virtual Account',
        ]);
        $res1->assertSessionHasErrors('seats');

        // Exceeding 5 seats (enforced by BookingRequest max:5)
        $sixSeats = $this->bus->busSeats()->take(6)->pluck('id')->toArray();
        $res2 = $this->actingAs($this->customer)->post(route('booking.store', $this->trip), [
            'seats' => $sixSeats,
            'payment_method' => 'BCA Virtual Account',
            'passengers' => [
                $sixSeats[0] => ['name' => 'A', 'phone' => '0812345678'],
            ],
        ]);
        $res2->assertSessionHasErrors('seats');

        // Missing passenger info
        $seat = $this->bus->busSeats()->first();
        $res3 = $this->actingAs($this->customer)->post(route('booking.store', $this->trip), [
            'seats' => [$seat->id],
            'payment_method' => 'BCA Virtual Account',
            'passengers' => [
                $seat->id => [
                    'name' => '',
                    'phone' => '',
                ],
            ],
        ]);
        $res3->assertSessionHasErrors(['passengers.'.$seat->id.'.name', 'passengers.'.$seat->id.'.phone']);
    }

    /**
     * 12. Customer cancellation rules: only unpaid pending orders can be cancelled.
     */
    public function test_customer_cancellation_rules(): void
    {
        // Unpaid order can be cancelled
        $unpaidOrder = $this->createOrder($this->customer, 'pending', 'unpaid');
        $res1 = $this->actingAs($this->customer)->post(route('orders.cancel', $unpaidOrder));
        $res1->assertRedirect();
        $this->assertEquals('cancelled', $unpaidOrder->fresh()->status);

        // Paid order cannot be cancelled by customer (enforced by OrderPolicy returning 403 Forbidden)
        $paidOrder = $this->createOrder($this->customer, 'confirmed', 'paid');
        $res2 = $this->actingAs($this->customer)->post(route('orders.cancel', $paidOrder));
        $res2->assertStatus(403);
        $this->assertEquals('confirmed', $paidOrder->fresh()->status);
        $this->assertEquals('paid', $paidOrder->fresh()->payment_status);
    }

    /**
     * 13. Critical UI route availability: all essential customer and public routes return 200.
     */
    public function test_critical_ui_route_availability(): void
    {
        $this->get(route('home'))->assertStatus(200);
        $this->get(route('trips.index'))->assertStatus(200);
        $this->get(route('trips.show', $this->trip))->assertStatus(200);
        $this->get(route('login'))->assertStatus(200);
        $this->get(route('register'))->assertStatus(200);

        // Authenticated customer routes
        $this->actingAs($this->customer)->get(route('orders.index'))->assertStatus(200);
        $this->actingAs($this->customer)->get(route('profile.edit'))->assertStatus(200);
    }

    /**
     * 14. Health check endpoint responds with 200 and excludes secrets.
     */
    public function test_health_endpoint_healthy_without_secrets(): void
    {
        $response = $this->get('/health');
        $response->assertStatus(200);

        $json = $response->getContent();
        if ($dbPass = config('database.connections.mysql.password')) {
            $this->assertStringNotContainsString($dbPass, $json);
        }
        $this->assertStringNotContainsString(config('app.key'), $json);
        $this->assertStringContainsString('ok', strtolower($json));
    }

    /**
     * 15. Critical error handling: 404 responses and checkout old input fallback.
     */
    public function test_critical_error_handling_and_checkout_fallback(): void
    {
        // 404 for non-existent trip
        $res404 = $this->get('/trips/99999999');
        $res404->assertStatus(404);

        // Checkout old input fallback verification
        $seat = $this->bus->busSeats()->first();
        // Emulate redirect back with old('seats')
        $this->withSession(['_old_input' => ['seats' => [$seat->id]]]);

        $resCheckout = $this->actingAs($this->customer)->get(route('booking.checkout', $this->trip));
        $resCheckout->assertStatus(200);
        $resCheckout->assertSee($seat->seat_number);
    }
}
