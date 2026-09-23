<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\BusSeat;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Route;
use App\Models\Trip;
use App\Models\User;
use App\Services\Payment\FakePaymentGateway;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class Sprint4HardeningTest extends TestCase
{
    protected function createTripWithSeats(int $seatsCount = 5): array
    {
        $bus = Bus::factory()->create([
            'seat_capacity' => $seatsCount,
            'status' => 'active',
        ]);

        $seats = [];
        for ($i = 1; $i <= $seatsCount; $i++) {
            $seats[] = BusSeat::factory()->create([
                'bus_id' => $bus->id,
                'seat_number' => 'A'.$i,
                'status' => 'available',
                'row' => 1,
                'column' => $i,
            ]);
        }

        $route = Route::factory()->create(['status' => 'active']);

        $trip = Trip::factory()->create([
            'bus_id' => $bus->id,
            'route_id' => $route->id,
            'departure_at' => now()->addDays(2),
            'arrival_at' => now()->addDays(2)->addHours(4),
            'price' => 200000,
            'status' => 'scheduled',
        ]);

        return [$trip, $seats];
    }

    /**
     * 1. Payment creation and hardened schema attributes.
     */
    public function test_payment_creation_and_hardened_fields_persisted(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(3);
        $seat = $seats[0];

        $response = $this->actingAs($customer)->post(route('booking.store', $trip), [
            'seats' => [$seat->id],
            'passengers' => [
                $seat->id => [
                    'name' => 'Budi Santoso',
                    'phone' => '08123456789',
                    'id_number' => '3201123456780001',
                ],
            ],
            'payment_method' => 'SIMULATION_MANUAL',
        ]);

        $order = Order::where('user_id', $customer->id)->latest()->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('booking.payment', $order));

        $payment = $order->payment;
        $this->assertNotNull($payment);
        $this->assertEquals('simulation', $payment->provider);
        $this->assertEquals('pending', $payment->status);
        $this->assertNotNull($payment->payment_reference);

        $item = $order->orderItems->first();
        $this->assertNotNull($item->ticket_token);
        $this->assertStringStartsWith('TKT-', $item->ticket_token);
    }

    /**
     * 2. Payment gateway charge success and atomic order status synchronization.
     */
    public function test_payment_gateway_charge_success_and_order_sync(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'total_amount' => 200000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'SIMULATION_TRANSFER',
            'provider' => 'simulation',
            'payment_reference' => 'PAY-TEST-'.Str::random(8),
            'amount' => 200000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer)->post(route('booking.processPayment', $order));

        $response->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $payment->refresh();

        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertNull($order->expires_at);

        $this->assertEquals('success', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertNotNull($payment->provider_transaction_id);
    }

    /**
     * 3. Payment failure representation.
     */
    public function test_payment_fails_gracefully_when_requested(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'SIMULATION_TRANSFER',
            'provider' => 'simulation',
            'payment_reference' => 'PAY-FAIL-'.Str::random(8),
            'amount' => 200000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer)->post(route('booking.processPayment', $order), [
            'simulate_failure' => 1,
        ]);

        $response->assertRedirect(route('booking.payment', $order));

        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
        $this->assertNotNull($payment->failed_at);
    }

    /**
     * 4. Webhook rejects missing or invalid signature.
     */
    public function test_webhook_rejects_missing_or_invalid_signature(): void
    {
        $response = $this->postJson(route('payments.webhook'), [
            'payment_reference' => 'PAY-FAKE-123',
            'status' => 'success',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Invalid or missing signature.',
        ]);
    }

    /**
     * 5. Webhook processes valid success event atomically.
     */
    public function test_webhook_processes_valid_success_event(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        $ref = 'PAY-WH-'.strtoupper(Str::random(8));
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'SIMULATION_GATEWAY',
            'provider' => 'simulation',
            'payment_reference' => $ref,
            'amount' => 200000,
            'status' => 'pending',
        ]);

        $gateway = app(FakePaymentGateway::class);
        $payload = [
            'payment_reference' => $ref,
            'status' => 'settlement',
            'transaction_id' => 'TX-EXT-'.time(),
        ];
        $signature = $gateway->computeSignature(json_encode($payload));

        $response = $this->postJson(route('payments.webhook'), $payload, [
            'X-CAN-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'order_code' => $order->order_code]);

        $order->refresh();
        $payment->refresh();

        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('success', $payment->status);
        $this->assertNotNull($payment->webhook_processed_at);
    }

    /**
     * 6. Webhook idempotency ignores duplicate delivery.
     */
    public function test_webhook_idempotency_ignores_duplicate_delivery(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $ref = 'PAY-DUP-'.strtoupper(Str::random(8));
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'SIMULATION_GATEWAY',
            'provider' => 'simulation',
            'payment_reference' => $ref,
            'amount' => 200000,
            'status' => 'success',
            'webhook_processed_at' => now(),
        ]);

        $gateway = app(FakePaymentGateway::class);
        $payload = [
            'payment_reference' => $ref,
            'status' => 'settlement',
        ];
        $signature = $gateway->computeSignature(json_encode($payload));

        $response = $this->postJson(route('payments.webhook'), $payload, [
            'X-CAN-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Webhook already processed previously.',
        ]);
    }

    /**
     * 7. Scheduled Artisan orders:expire command cancels overdue orders and releases seats.
     */
    public function test_orders_expire_artisan_command_cancels_overdue_orders_and_releases_seats(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);
        $seat = $seats[0];

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->subMinutes(15), // overdue
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Penumpan Telat',
            'price' => 200000,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'SIMULATION_MANUAL',
            'provider' => 'simulation',
            'payment_reference' => 'PAY-OVERDUE-'.Str::random(8),
            'amount' => 200000,
            'status' => 'pending',
        ]);

        // Before command, seat is not booked because expires_at is past, but order in DB is pending
        $exitCode = Artisan::call('orders:expire');
        $this->assertEquals(0, $exitCode);

        $order->refresh();
        $payment->refresh();

        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('expired', $order->payment_status);
        $this->assertEquals('expired', $payment->status);
        $this->assertNotNull($payment->expired_at);

        // Seat is definitely available for re-booking
        $bookedSeatIds = $trip->getBookedSeatIds();
        $this->assertNotContains($seat->id, $bookedSeatIds);
    }

    /**
     * 8. Repeated expiration command is safe and idempotent.
     */
    public function test_repeated_orders_expire_command_is_safe(): void
    {
        $exitCode1 = Artisan::call('orders:expire');
        $this->assertEquals(0, $exitCode1);

        $exitCode2 = Artisan::call('orders:expire');
        $this->assertEquals(0, $exitCode2);
    }

    /**
     * 9. Production security headers are present in responses.
     */
    public function test_security_headers_present_in_responses(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $this->assertTrue($response->headers->has('Permissions-Policy'));
    }

    /**
     * 10. IDOR protection on order payment screen.
     */
    public function test_customer_cannot_access_other_customer_payment_page_idor(): void
    {
        $customerA = User::factory()->create(['role' => 'customer']);
        $customerB = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $orderB = Order::factory()->create([
            'user_id' => $customerB->id,
            'trip_id' => $trip->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($customerA)->get(route('booking.payment', $orderB));
        $response->assertStatus(403);
    }

    /**
     * 11. Admin can stream order CSV export with valid authorization.
     */
    public function test_admin_can_export_orders_to_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.orders.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('CAN_Travel_Laporan_Pesanan', $response->headers->get('Content-Disposition'));
    }

    /**
     * 12. Non-admin is rejected from orders CSV export.
     */
    public function test_non_admin_cannot_export_orders_csv(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get(route('admin.orders.export'));
        $response->assertStatus(403);
    }

    /**
     * 13. Admin dashboard analytics time period filtering.
     */
    public function test_admin_dashboard_time_period_filtering(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['period' => 'today']));
        $response->assertStatus(200);
        $response->assertSee('Okupansi Armada');
        $response->assertSee('Rute Terpopuler');

        $responseMonth = $this->actingAs($admin)->get(route('admin.dashboard', ['period' => 'month']));
        $responseMonth->assertStatus(200);
    }

    /**
     * 14. E-Ticket verification endpoint with valid token.
     */
    public function test_ticket_verification_with_valid_token(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $token = 'TKT-TEST-'.Str::random(10);
        $item = OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seats[0]->id,
            'ticket_token' => $token,
            'passenger_name' => 'Ahmad Dahlan',
            'price' => 200000,
        ]);

        $response = $this->get(route('tickets.verify', ['token' => $token]));
        $response->assertStatus(200);
        $response->assertSee('E-Tiket Resmi & Terverifikasi');
        $response->assertSee('Ahmad Dahlan');
        $response->assertSee($token);
    }

    /**
     * 15. E-Ticket verification endpoint with invalid token returns 404.
     */
    public function test_ticket_verification_with_invalid_token_returns_404(): void
    {
        $response = $this->get(route('tickets.verify', ['token' => 'INVALID-TOKEN-99999']));
        $response->assertStatus(404);
        $response->assertSee('Tiket Tidak Ditemukan');
    }

    /**
     * 16. Webhook handles expiration event correctly.
     */
    public function test_webhook_handles_expiration_event(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        $ref = 'PAY-EXP-'.strtoupper(Str::random(8));
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'SIMULATION_GATEWAY',
            'provider' => 'simulation',
            'payment_reference' => $ref,
            'amount' => 200000,
            'status' => 'pending',
        ]);

        $gateway = app(FakePaymentGateway::class);
        $payload = [
            'payment_reference' => $ref,
            'status' => 'expired',
        ];
        $signature = $gateway->computeSignature(json_encode($payload));

        $response = $this->postJson(route('payments.webhook'), $payload, [
            'X-CAN-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $payment->refresh();

        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('expired', $order->payment_status);
        $this->assertEquals('expired', $payment->status);
        $this->assertNotNull($payment->expired_at);
    }

    /**
     * 17. Webhook handles failed event correctly.
     */
    public function test_webhook_handles_failed_event(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        $ref = 'PAY-FL-'.strtoupper(Str::random(8));
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'SIMULATION_GATEWAY',
            'provider' => 'simulation',
            'payment_reference' => $ref,
            'amount' => 200000,
            'status' => 'pending',
        ]);

        $gateway = app(FakePaymentGateway::class);
        $payload = [
            'payment_reference' => $ref,
            'status' => 'failed',
        ];
        $signature = $gateway->computeSignature(json_encode($payload));

        $response = $this->postJson(route('payments.webhook'), $payload, [
            'X-CAN-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
        $this->assertNotNull($payment->failed_at);
    }

    /**
     * 18. Expiration command does not cancel paid or future pending orders.
     */
    public function test_active_and_paid_orders_untouched_by_expire_command(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $paidOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'expires_at' => null,
        ]);

        $futureOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
        ]);

        Artisan::call('orders:expire');

        $paidOrder->refresh();
        $futureOrder->refresh();

        $this->assertEquals('confirmed', $paidOrder->status);
        $this->assertEquals('paid', $paidOrder->payment_status);

        $this->assertEquals('pending', $futureOrder->status);
        $this->assertEquals('unpaid', $futureOrder->payment_status);
    }
}
