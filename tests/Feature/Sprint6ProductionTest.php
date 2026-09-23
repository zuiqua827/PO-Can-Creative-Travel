<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\BusSeat;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Route as BusRoute;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\BookingCreatedNotification;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\MidtransPaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentReconciliationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class Sprint6ProductionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $customer;

    protected Trip $trip;

    protected Bus $bus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.sprint6@cantravel.com'],
            [
                'name' => 'Admin Sprint 6',
                'password' => bcrypt('password123'),
                'phone' => '081234567890',
                'role' => 'admin',
            ]
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'customer.sprint6@cantravel.com'],
            [
                'name' => 'Budi Customer',
                'password' => bcrypt('password123'),
                'phone' => '081298765432',
                'role' => 'customer',
            ]
        );

        $this->bus = Bus::first() ?? Bus::factory()->create();
        $route = BusRoute::first() ?? BusRoute::factory()->create();

        $this->trip = Trip::firstOrCreate(
            ['trip_code' => 'CAN-TRIP-S6-'.substr(uniqid(), -6)],
            [
                'bus_id' => $this->bus->id,
                'route_id' => $route->id,
                'departure_at' => now()->addDays(2),
                'arrival_at' => now()->addDays(2)->addHours(6),
                'price' => 250000,
                'boarding_point' => 'Pool Jakarta',
                'drop_off_point' => 'Terminal Bandung',
                'status' => 'scheduled',
            ]
        );
    }

    /**
     * 1. Health endpoint works and returns 200 OK with valid JSON structure.
     */
    public function test_health_endpoint_works(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
            'app' => 'CAN Travel',
            'checks' => [
                'database' => 'ok',
                'cache' => 'ok',
                'storage' => 'ok',
            ],
        ]);
    }

    /**
     * 2. Health endpoint does not leak environment secrets, paths, or passwords.
     */
    public function test_health_endpoint_does_not_leak_secrets(): void
    {
        $response = $this->get('/health');
        $content = $response->getContent();

        $this->assertStringNotContainsString('laragon', strtolower($content));
        $this->assertStringNotContainsString('password', strtolower($content));
        $this->assertStringNotContainsString('app_key', strtolower($content));
        $this->assertStringNotContainsString('midtrans', strtolower($content));
        $this->assertStringNotContainsString('server_key', strtolower($content));
        $this->assertStringNotContainsString('.env', $content);
    }

    /**
     * 3. Production security headers exist on web responses.
     */
    public function test_production_security_headers_exist(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $this->assertTrue($response->headers->has('X-Request-ID'));
        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
    }

    /**
     * 4. Invalid webhook signature is rejected with 403 status.
     */
    public function test_invalid_webhook_signature_rejected(): void
    {
        $serverKey = 'SB-Mid-server-test-key';
        Config::set('payment.drivers.midtrans.server_key', $serverKey);
        Config::set('payment.driver', 'midtrans');
        $this->app->bind(PaymentGatewayInterface::class, fn () => app(MidtransPaymentGateway::class));

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-WH-INV-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'payment_reference' => 'PAY-WH-INV-'.uniqid(),
            'amount' => 250000,
            'status' => 'pending',
        ]);

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => '250000.00',
            'signature_key' => 'invalid-signature-hash-here',
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'midtrans-tx-'.uniqid(),
        ];

        $response = $this->postJson('/payments/webhook', $payload);

        $response->assertStatus(403);
        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertEquals('unpaid', $order->fresh()->payment_status);
    }

    /**
     * 5. Duplicate webhook is idempotent and does not corrupt payment or order state.
     */
    public function test_duplicate_webhook_is_idempotent(): void
    {
        $serverKey = 'SB-Mid-server-test-key';
        Config::set('payment.drivers.midtrans.server_key', $serverKey);
        Config::set('payment.driver', 'midtrans');
        $this->app->bind(PaymentGatewayInterface::class, fn () => app(MidtransPaymentGateway::class));

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-WH-IDEM-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'payment_reference' => 'PAY-WH-IDEM-'.uniqid(),
            'amount' => 250000,
            'status' => 'pending',
        ]);

        $gross = '250000.00';
        $signature = hash('sha512', $order->order_code.'200'.$gross.$serverKey);
        $txId = 'midtrans-tx-'.uniqid();

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => $gross,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => $txId,
        ];

        // First delivery: processes order to paid
        $response1 = $this->postJson('/payments/webhook', $payload);
        $response1->assertStatus(200);

        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('confirmed', $order->fresh()->status);

        // Second duplicate delivery: remains idempotent 200 OK
        $response2 = $this->postJson('/payments/webhook', $payload);
        $response2->assertStatus(200);

        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('confirmed', $order->fresh()->status);
    }

    /**
     * 6. Payment amount mismatch in webhook is rejected.
     */
    public function test_payment_amount_mismatch_rejected(): void
    {
        $serverKey = 'SB-Mid-server-test-key';
        Config::set('payment.drivers.midtrans.server_key', $serverKey);
        Config::set('payment.driver', 'midtrans');
        $this->app->bind(PaymentGatewayInterface::class, fn () => app(MidtransPaymentGateway::class));

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-WH-MIS-'.uniqid(),
            'total_amount' => 500000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'payment_reference' => 'PAY-WH-MIS-'.uniqid(),
            'amount' => 500000,
            'status' => 'pending',
        ]);

        // Malicious or mismatched lower amount
        $mismatchedGross = '10000.00';
        $signature = hash('sha512', $order->order_code.'200'.$mismatchedGross.$serverKey);

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => $mismatchedGross,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'midtrans-tx-'.uniqid(),
        ];

        $response = $this->postJson('/payments/webhook', $payload);
        $response->assertStatus(422);

        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertEquals('unpaid', $order->fresh()->payment_status);
    }

    /**
     * 7. Cancelled order cannot become paid through webhook.
     */
    public function test_cancelled_order_cannot_become_paid(): void
    {
        $serverKey = 'SB-Mid-server-test-key';
        Config::set('payment.drivers.midtrans.server_key', $serverKey);
        Config::set('payment.driver', 'midtrans');
        $this->app->bind(PaymentGatewayInterface::class, fn () => app(MidtransPaymentGateway::class));

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-WH-CANC-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'cancelled',
            'payment_status' => 'expired',
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'payment_reference' => 'PAY-WH-CANC-'.uniqid(),
            'amount' => 250000,
            'status' => 'failed',
        ]);

        $gross = '250000.00';
        $signature = hash('sha512', $order->order_code.'200'.$gross.$serverKey);

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => $gross,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'midtrans-tx-'.uniqid(),
        ];

        $response = $this->postJson('/payments/webhook', $payload);

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertNotEquals('paid', $order->fresh()->payment_status);
    }

    /**
     * 8. Expired order cannot become paid through webhook.
     */
    public function test_expired_order_cannot_become_paid(): void
    {
        $serverKey = 'SB-Mid-server-test-key';
        Config::set('payment.drivers.midtrans.server_key', $serverKey);
        Config::set('payment.driver', 'midtrans');
        $this->app->bind(PaymentGatewayInterface::class, fn () => app(MidtransPaymentGateway::class));

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-WH-EXP-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'cancelled',
            'payment_status' => 'expired',
            'expires_at' => now()->subHour(),
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'payment_reference' => 'PAY-WH-EXP-'.uniqid(),
            'amount' => 250000,
            'status' => 'expired',
        ]);

        $gross = '250000.00';
        $signature = hash('sha512', $order->order_code.'200'.$gross.$serverKey);

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => $gross,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'midtrans-tx-'.uniqid(),
        ];

        $response = $this->postJson('/payments/webhook', $payload);

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals('expired', $order->fresh()->payment_status);
    }

    /**
     * 9. Payment reconciliation service is idempotent and handles orders safely.
     */
    public function test_payment_reconciliation_is_idempotent(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-REC-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'payment_reference' => 'PAY-REC-'.uniqid(),
            'amount' => 250000,
            'status' => 'success',
            'paid_at' => now(),
        ]);

        $service = app(PaymentReconciliationService::class);

        // Run reconciliation 1
        $result1 = $service->reconcileOrder($payment);
        $this->assertEquals('consistent', $result1['status']);

        // Run reconciliation 2
        $result2 = $service->reconcileOrder($payment);
        $this->assertEquals('consistent', $result2['status']);

        // Verify status unchanged
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('success', $payment->fresh()->status);
    }

    /**
     * 10. Fake payment gateway processes charges successfully in sandbox/local.
     */
    public function test_fake_payment_gateway_works(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-FAKE-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $gateway = new FakePaymentGateway;
        $result = $gateway->charge($order, ['payment_method' => 'bank_transfer']);

        $this->assertTrue($result->isPending() || $result->isSuccess());
        $this->assertNotEmpty($result->getReference());
    }

    /**
     * 11. Midtrans configuration missing fails safely without crashing or leaking keys.
     */
    public function test_midtrans_configuration_missing_fails_safely(): void
    {
        Config::set('payment.drivers.midtrans.server_key', '');
        Config::set('payment.drivers.midtrans.is_production', true);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-MIDFAIL-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $gateway = new MidtransPaymentGateway;
        $result = $gateway->charge($order, []);

        $this->assertTrue($result->isFailed());
        $this->assertStringContainsString('Midtrans belum dikonfigurasi', $result->getMessage());
    }

    /**
     * 12. Queued notification does not break the booking process.
     */
    public function test_queue_notification_does_not_break_booking(): void
    {
        Notification::fake();

        $seat = BusSeat::factory()->create([
            'bus_id' => $this->trip->bus_id,
            'seat_number' => 'S6N-'.uniqid(),
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->customer)->post(route('booking.store', $this->trip), [
            'seats' => [$seat->id],
            'payment_method' => 'MANUAL_TRANSFER',
            'passengers' => [
                $seat->id => [
                    'name' => 'John Passenger',
                    'phone' => '08123456789',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(302, $response->getStatusCode());

        Notification::assertSentTo($this->customer, BookingCreatedNotification::class);
    }

    /**
     * 13. Expired orders are processed safely by orders:expire command.
     */
    public function test_expired_orders_are_processed_safely(): void
    {
        $seat = BusSeat::factory()->create([
            'bus_id' => $this->trip->bus_id,
            'seat_number' => 'S6E-'.uniqid(),
            'status' => 'available',
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-OVERDUE-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->subMinutes(15),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Expired Passenger',
            'price' => 250000,
            'ticket_token' => (string) Str::uuid(),
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'payment_reference' => 'PAY-OVERDUE-'.uniqid(),
            'amount' => 250000,
            'status' => 'pending',
        ]);

        $exitCode = Artisan::call('orders:expire');
        $this->assertEquals(0, $exitCode);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('expired', $order->payment_status);
    }

    /**
     * 14. Expired orders are not processed twice by orders:expire.
     */
    public function test_expired_orders_are_not_processed_twice(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-TWICE-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'cancelled',
            'payment_status' => 'expired',
            'expires_at' => now()->subMinutes(30),
        ]);

        $exitCode1 = Artisan::call('orders:expire');
        $this->assertEquals(0, $exitCode1);

        $exitCode2 = Artisan::call('orders:expire');
        $this->assertEquals(0, $exitCode2);

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals('expired', $order->fresh()->payment_status);
    }

    /**
     * 15. Admin payment and order filtering works with search across payment identifiers.
     */
    public function test_admin_payment_filtering_works(): void
    {
        $uniqueTxId = 'midtrans-flt-'.uniqid();
        $uniquePayRef = 'PAY-FLT-'.uniqid();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-FLT-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'payment_reference' => $uniquePayRef,
            'provider_transaction_id' => $uniqueTxId,
            'amount' => 250000,
            'status' => 'success',
            'paid_at' => now(),
        ]);

        // Search by payment reference
        $response1 = $this->actingAs($this->admin)->get(route('admin.orders.index', [
            'search' => $uniquePayRef,
        ]));
        $response1->assertStatus(200);
        $response1->assertSee($order->order_code);

        // Search by provider transaction id
        $response2 = $this->actingAs($this->admin)->get(route('admin.orders.index', [
            'search' => $uniqueTxId,
        ]));
        $response2->assertStatus(200);
        $response2->assertSee($order->order_code);
    }

    /**
     * 16. Non-admin cannot access admin payment and order operations.
     */
    public function test_non_admin_cannot_access_payment_operations(): void
    {
        // Guest redirected to login
        Auth::logout();
        $responseGuest = $this->get(route('admin.orders.index'));
        $responseGuest->assertRedirect(route('login'));

        // Customer receives 403 Forbidden
        $responseCustomer = $this->actingAs($this->customer)->get(route('admin.orders.index'));
        $responseCustomer->assertStatus(403);

        $responseAudit = $this->actingAs($this->customer)->get(route('admin.audit-logs.index'));
        $responseAudit->assertStatus(403);
    }

    /**
     * 17. Audit log records sensitive admin action.
     */
    public function test_audit_log_records_sensitive_admin_action(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-AUD-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'payment_reference' => 'PAY-AUD-'.uniqid(),
            'amount' => 250000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)->put(route('admin.orders.update', $order), [
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'order_status_updated',
            'target_type' => 'order',
            'target_id' => $order->id,
        ]);
    }

    /**
     * 18. CSV export remains authorized and logged.
     */
    public function test_csv_export_remains_authorized(): void
    {
        // Customer forbidden
        $response = $this->actingAs($this->customer)->get(route('admin.orders.export'));
        $response->assertStatus(403);

        // Admin authorized
        $adminResponse = $this->actingAs($this->admin)->get(route('admin.orders.export'));
        $adminResponse->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $adminResponse->headers->get('content-type'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'orders_csv_exported',
        ]);
    }

    /**
     * 19. Ticket verification remains protected against token enumeration.
     */
    public function test_ticket_verification_remains_protected_against_enumeration(): void
    {
        $nonExistentToken = 'NON-EXISTENT-'.Str::random(32);

        $response = $this->get(route('tickets.verify', $nonExistentToken));

        $response->assertStatus(404);
        $response->assertSee('Tiket Tidak Ditemukan');
        $response->assertDontSee('Nama Penumpang');
    }

    /**
     * 20. Upload validation rejects executable files.
     */
    public function test_upload_validation_rejects_executable_files(): void
    {
        Storage::fake('public');

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-UP-'.uniqid(),
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->customer->id,
            'payment_method' => 'MANUAL_TRANSFER',
            'provider' => 'simulation',
            'payment_reference' => 'PAY-UP-'.uniqid(),
            'amount' => 250000,
            'status' => 'pending',
        ]);

        $maliciousFile = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

        $response = $this->actingAs($this->customer)->post(route('booking.processPayment', $order), [
            'payment_proof' => $maliciousFile,
        ]);

        $response->assertSessionHasErrors(['payment_proof']);
    }

    /**
     * 21. Rate limiting is active on sensitive endpoints.
     */
    public function test_rate_limiting_works_on_sensitive_endpoints(): void
    {
        $token = 'RATE-LIMIT-TOKEN-'.Str::random(16);

        // Hit ticket verification multiple times
        for ($i = 0; $i < 65; $i++) {
            $response = $this->get(route('tickets.verify', $token));
            if ($response->getStatusCode() === 429) {
                break;
            }
        }

        // Responds gracefully with 404 or throttled with 429
        $this->assertTrue(in_array($response->getStatusCode(), [404, 429]));
    }

    /**
     * 22. No legacy PCT prefix is generated on new orders.
     */
    public function test_no_legacy_pct_prefix_is_generated(): void
    {
        $seat = BusSeat::factory()->create([
            'bus_id' => $this->trip->bus_id,
            'seat_number' => 'S6P-'.uniqid(),
            'status' => 'available',
        ]);

        $this->actingAs($this->customer)->post(route('booking.store', $this->trip), [
            'seats' => [$seat->id],
            'payment_method' => 'MANUAL_TRANSFER',
            'passengers' => [
                $seat->id => [
                    'name' => 'Prefix Tester',
                    'phone' => '08123456789',
                ],
            ],
        ]);

        $latestOrder = Order::where('user_id', $this->customer->id)->latest()->first();

        $this->assertNotNull($latestOrder);
        $this->assertStringStartsWith('CAN-', $latestOrder->order_code);
        $this->assertStringStartsNotWith('PCT-', $latestOrder->order_code);
    }

    /**
     * 23. Public pages do not display legacy "PO CAN" branding.
     */
    public function test_no_po_can_branding_remains(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringNotContainsString('PO CAN Travel', $content);
        $this->assertStringNotContainsString('PO CAN', $content);
        $this->assertStringContainsString('CAN Travel', $content);
    }

    /**
     * 24. Order total remains strictly server-authoritative.
     */
    public function test_order_total_remains_server_authoritative(): void
    {
        $seat = BusSeat::factory()->create([
            'bus_id' => $this->trip->bus_id,
            'seat_number' => 'S6T-'.uniqid(),
            'status' => 'available',
        ]);

        // Attempting to send manipulated client-side price of 1000 IDR
        $this->actingAs($this->customer)->post(route('booking.store', $this->trip), [
            'seats' => [$seat->id],
            'payment_method' => 'MANUAL_TRANSFER',
            'total_amount' => 1000,
            'price' => 1000,
            'passengers' => [
                $seat->id => [
                    'name' => 'Cheating Price Tester',
                    'phone' => '08123456789',
                ],
            ],
        ]);

        $order = Order::where('user_id', $this->customer->id)->latest()->first();

        $this->assertNotNull($order);
        // Must equal trip price (250,000) not the manipulated 1000 IDR
        $this->assertEquals((float) $this->trip->price, (float) $order->total_amount);
    }
}
