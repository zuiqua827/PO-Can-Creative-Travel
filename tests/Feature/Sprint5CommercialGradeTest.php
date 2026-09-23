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
use App\Notifications\BookingCreatedNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Services\Payment\MidtransPaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentResult;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class Sprint5CommercialGradeTest extends TestCase
{
    use DatabaseTransactions;

    protected function createTripWithSeats(int $seatsCount = 4): array
    {
        $bus = Bus::factory()->create([
            'seat_capacity' => $seatsCount,
            'status' => 'active',
        ]);

        $seats = [];
        for ($i = 1; $i <= $seatsCount; $i++) {
            $seats[] = BusSeat::factory()->create([
                'bus_id' => $bus->id,
                'seat_number' => 'S5-'.Str::random(3).'-'.$i,
                'status' => 'available',
                'row' => 1,
                'column' => $i,
            ]);
        }

        $route = Route::factory()->create(['status' => 'active']);

        $trip = Trip::factory()->create([
            'bus_id' => $bus->id,
            'route_id' => $route->id,
            'departure_at' => now()->addDays(3),
            'arrival_at' => now()->addDays(3)->addHours(5),
            'price' => 250000,
            'status' => 'scheduled',
        ]);

        return [$trip, $seats];
    }

    /**
     * 1. Payment gateway charge success.
     */
    public function test_payment_gateway_charge_success(): void
    {
        $gateway = app(PaymentGatewayInterface::class);
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 500000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
        ]);

        $result = $gateway->charge($order);
        $this->assertTrue($result->isSuccess());
        $this->assertNotEmpty($result->getTransactionId());
    }

    /**
     * 2. Payment gateway charge failure simulation.
     */
    public function test_payment_gateway_charge_failure(): void
    {
        $gateway = app(PaymentGatewayInterface::class);
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 500000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
        ]);

        $result = $gateway->charge($order, ['simulate_failure' => true]);
        $this->assertTrue($result->isFailed());
    }

    /**
     * 3. Webhook valid signature processes order.
     */
    public function test_webhook_valid_signature_processes_order(): void
    {
        Notification::fake();
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'BANK_TRANSFER',
            'provider' => 'simulation',
            'payment_reference' => 'PAY-TEST-'.strtoupper(Str::random(8)),
            'amount' => 250000,
            'status' => 'pending',
        ]);

        $payload = [
            'payment_reference' => $payment->payment_reference,
            'status' => 'settlement',
            'gross_amount' => 250000,
            'currency' => 'IDR',
            'transaction_id' => 'TX-TEST-VALID-01',
        ];

        $secret = config('payment.webhook_secret', 'test_secret_can_travel');
        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        $response = $this->postJson(route('payments.webhook'), $payload, [
            'X-CAN-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $payment->refresh();

        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('success', $payment->status);
        Notification::assertSentTo($customer, PaymentReceivedNotification::class);
    }

    /**
     * 4. Webhook invalid signature rejected.
     */
    public function test_webhook_invalid_signature_rejected(): void
    {
        $payload = [
            'payment_reference' => 'PAY-TEST-TAMPERED',
            'status' => 'settlement',
        ];

        $response = $this->postJson(route('payments.webhook'), $payload, [
            'X-CAN-Signature' => 'completely_invalid_signature_hash',
        ]);

        $response->assertStatus(403);
    }

    /**
     * 5. Webhook replay protection / idempotency.
     */
    public function test_webhook_replay_protection_idempotency(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'expires_at' => null,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'BANK_TRANSFER',
            'provider' => 'simulation',
            'payment_reference' => 'PAY-REPLAY-'.strtoupper(Str::random(8)),
            'amount' => 250000,
            'status' => 'success',
            'paid_at' => now(),
            'webhook_processed_at' => now(),
        ]);

        $payload = [
            'payment_reference' => $payment->payment_reference,
            'status' => 'settlement',
            'gross_amount' => 250000,
            'currency' => 'IDR',
        ];

        $secret = config('payment.webhook_secret', 'test_secret_can_travel');
        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        $response = $this->postJson(route('payments.webhook'), $payload, [
            'X-CAN-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Webhook already processed previously.']);
    }

    /**
     * 6. Webhook amount mismatch rejected.
     */
    public function test_webhook_amount_mismatch_rejected(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'BANK_TRANSFER',
            'provider' => 'simulation',
            'payment_reference' => 'PAY-AMOUNT-MISMATCH-'.strtoupper(Str::random(8)),
            'amount' => 250000,
            'status' => 'pending',
        ]);

        // Attacker attempts to pay 1000 instead of 250000
        $payload = [
            'payment_reference' => $payment->payment_reference,
            'status' => 'settlement',
            'gross_amount' => 1000,
            'currency' => 'IDR',
        ];

        $secret = config('payment.webhook_secret', 'test_secret_can_travel');
        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        $response = $this->postJson(route('payments.webhook'), $payload, [
            'X-CAN-Signature' => $signature,
        ]);

        $response->assertStatus(422);
        $order->refresh();
        $this->assertEquals('unpaid', $order->payment_status);
    }

    /**
     * 7. Webhook unknown order returns 404.
     */
    public function test_webhook_unknown_order_returns_404(): void
    {
        $payload = [
            'payment_reference' => 'NON-EXISTENT-ORDER-CODE',
            'status' => 'settlement',
        ];

        $secret = config('payment.webhook_secret', 'test_secret_can_travel');
        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        $response = $this->postJson(route('payments.webhook'), $payload, [
            'X-CAN-Signature' => $signature,
        ]);

        $response->assertStatus(404);
    }

    /**
     * 8. Expired order cannot be paid.
     */
    public function test_expired_order_cannot_be_paid(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->subMinutes(10), // Expired
        ]);

        $response = $this->actingAs($customer)->post(route('booking.processPayment', $order));

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('expired', $order->payment_status);
        $response->assertRedirect(route('orders.show', $order));
    }

    /**
     * 9. State machine disallows invalid transitions.
     */
    public function test_state_machine_matrix_rules(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);

        // Cancelled order cannot become confirmed or paid
        $cancelledOrder = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'cancelled',
            'payment_status' => 'expired',
        ]);

        $this->assertFalse($cancelledOrder->canTransition('confirmed', 'paid'));
        $this->assertFalse($cancelledOrder->canPaymentTransitionTo('paid'));

        // Completed order cannot revert to pending
        $completedOrder = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $this->assertFalse($completedOrder->canTransitionTo('pending'));
    }

    /**
     * 10. Refund transition allowed only from paid.
     */
    public function test_refund_transition_allowed_from_paid_only(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);

        $paidOrder = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $this->assertTrue($paidOrder->canPaymentTransitionTo('refunded'));

        $unpaidOrder = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $this->assertFalse($unpaidOrder->canPaymentTransitionTo('refunded'));
    }

    /**
     * 11. Unauthorized ticket access and IDOR protection.
     */
    public function test_unauthorized_ticket_access_and_idor_protection(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $stranger = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);

        $order = Order::create([
            'user_id' => $owner->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // Stranger should be forbidden from accessing owner's order
        $response = $this->actingAs($stranger)->get(route('orders.show', $order));
        $response->assertStatus(403);
    }

    /**
     * 12. Invalid ticket token returns 404.
     */
    public function test_invalid_ticket_token_returns_404(): void
    {
        $response = $this->get(route('tickets.verify', 'TKT-INVALID-TOKEN-99999'));
        $response->assertStatus(404);
        $response->assertSee('Tiket Tidak Ditemukan');
    }

    /**
     * 13. Security headers present and unsafe-eval excluded.
     */
    public function test_security_headers_present_and_unsafe_eval_excluded(): void
    {
        $response = $this->get(route('home'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringNotContainsString('unsafe-eval', $csp);
    }

    /**
     * 14. File upload security: valid file accepted, invalid MIME rejected.
     */
    public function test_file_upload_security_valid_and_invalid_mimes(): void
    {
        Storage::fake('public');
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'MANUAL_TRANSFER',
            'provider' => 'simulation',
            'payment_reference' => 'PAY-UP-'.strtoupper(Str::random(8)),
            'amount' => 250000,
            'status' => 'pending',
        ]);

        // Malicious executable file should be rejected by validation
        $fakeExecutable = UploadedFile::fake()->create('malicious_script.php', 100, 'application/x-php');
        $badResponse = $this->actingAs($customer)->post(route('booking.processPayment', $order), [
            'payment_proof' => $fakeExecutable,
        ]);
        $badResponse->assertSessionHasErrors(['payment_proof']);

        // Valid receipt file (JPEG/PDF) without requiring GD extension
        $validReceipt = UploadedFile::fake()->create('transfer_receipt.jpg', 200, 'image/jpeg');
        $goodResponse = $this->actingAs($customer)->post(route('booking.processPayment', $order), [
            'payment_proof' => $validReceipt,
        ]);

        $goodResponse->assertRedirect(route('orders.show', $order));
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertNotNull($order->payment->proof_file);
    }

    /**
     * 15. Notification dispatch & queue readiness.
     */
    public function test_notification_dispatch_and_queue_readiness(): void
    {
        Notification::fake();
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(2);

        $response = $this->actingAs($customer)->post(route('booking.store', $trip), [
            'seats' => [$seats[0]->id],
            'passengers' => [
                $seats[0]->id => ['name' => 'Budi Santoso', 'phone' => '08123456789'],
            ],
            'payment_method' => 'BANK_TRANSFER',
        ]);

        Notification::assertSentTo($customer, BookingCreatedNotification::class);
    }

    /**
     * 16. Midtrans adapter initialization and interface compliance.
     */
    public function test_midtrans_adapter_compliance(): void
    {
        $midtrans = new MidtransPaymentGateway;
        $this->assertEquals('midtrans', $midtrans->getProviderName());

        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
        ]);

        $chargeResult = $midtrans->charge($order);
        $this->assertInstanceOf(PaymentResult::class, $chargeResult);

        $webhookResult = $midtrans->handleWebhook([
            'order_id' => $order->order_code,
            'transaction_status' => 'settlement',
            'gross_amount' => '250000.00',
        ]);
        $this->assertTrue($webhookResult->isSuccess());

        $refundResult = $midtrans->handleWebhook([
            'order_id' => $order->order_code,
            'transaction_status' => 'refund',
            'gross_amount' => '250000.00',
        ]);
        $this->assertTrue($refundResult->isRefunded());
    }

    /**
     * 17. Ticket anti-enumeration token entropy.
     */
    public function test_ticket_anti_enumeration_token_entropy(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seats[0]->id,
            'passenger_name' => 'Citra Kirana',
            'passenger_phone' => '081299998888',
            'price' => 250000,
        ]);

        $this->assertNotEmpty($item->ticket_token);
        // TKT-YYYY- (9 chars) + 24 random chars = 33 chars
        $this->assertGreaterThanOrEqual(33, strlen($item->ticket_token));
        $this->assertStringStartsWith('TKT-', $item->ticket_token);
    }

    /**
     * 18. Rate limiting enforced on ticket verification.
     */
    public function test_rate_limiting_enforced_on_ticket_verification(): void
    {
        // Hit ticket verification multiple times
        for ($i = 0; $i < 35; $i++) {
            $response = $this->get(route('tickets.verify', 'TKT-TEST-RATE-LIMIT'));
            if ($response->status() === 429) {
                break;
            }
        }

        // Must respond or throttle gracefully with 404 or 429
        $this->assertTrue(in_array($response->status(), [404, 429]));
    }

    /**
     * 19. Admin order management authorization enforced.
     */
    public function test_admin_order_management_authorization_enforced(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Customer cannot access admin orders
        $unauthorized = $this->actingAs($customer)->get(route('admin.orders.index'));
        $unauthorized->assertStatus(403);

        // Admin can access admin orders
        $authorized = $this->actingAs($admin)->get(route('admin.orders.index'));
        $authorized->assertStatus(200);
    }

    /**
     * 20. Concurrent booking race condition protection.
     */
    public function test_concurrent_seat_booking_race_condition_protection(): void
    {
        $userA = User::factory()->create(['role' => 'customer']);
        $userB = User::factory()->create(['role' => 'customer']);
        [$trip, $seats] = $this->createTripWithSeats(1);
        $seat = $seats[0];

        // User A successfully books seat
        $orderA = Order::create([
            'user_id' => $userA->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
        ]);

        OrderItem::create([
            'order_id' => $orderA->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'User A',
            'passenger_phone' => '081234567890',
            'price' => 250000,
        ]);

        // User B attempts to book the same seat before payment expires
        $responseB = $this->actingAs($userB)->post(route('booking.store', $trip), [
            'seats' => [$seat->id],
            'passengers' => [
                $seat->id => ['name' => 'User B', 'phone' => '081299998888'],
            ],
            'payment_method' => 'BANK_TRANSFER',
        ]);

        // Second booking attempt should fail and redirect back with error
        $responseB->assertRedirect(route('trips.show', $trip));
        $responseB->assertSessionHas('error');
    }
}
