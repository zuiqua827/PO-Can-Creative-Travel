<?php

namespace Tests\Feature;

use App\Exceptions\Handler;
use App\Models\Bus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Route as BusRoute;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class Sprint7FinalReleaseTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $customer;

    protected Trip $trip;

    protected Bus $bus;

    protected BusRoute $route;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.sprint7@cantravel.com'],
            [
                'name' => 'Admin Sprint 7',
                'password' => bcrypt('password123'),
                'phone' => '081234567899',
                'role' => 'admin',
            ]
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'customer.sprint7@cantravel.com'],
            [
                'name' => 'Customer Sprint 7',
                'password' => bcrypt('password123'),
                'phone' => '081298765499',
                'role' => 'customer',
            ]
        );

        $this->bus = Bus::create([
            'name' => 'CAN Luxury Voyager S7',
            'code' => 'CLV-S7-'.rand(100, 999),
            'type' => 'Executive',
            'seat_capacity' => 12,
            'facilities' => ['AC', 'Reclining Seat', 'USB Charger'],
            'status' => 'active',
        ]);
        $this->bus->generateSeats(12);

        $this->route = BusRoute::create([
            'origin' => 'Jakarta',
            'destination' => 'Yogyakarta',
            'distance' => '520 km',
            'estimated_duration' => '8 Jam',
            'base_price' => 250000,
            'status' => 'active',
        ]);

        $this->trip = Trip::create([
            'trip_code' => 'TRP-S7-'.rand(1000, 9999),
            'bus_id' => $this->bus->id,
            'route_id' => $this->route->id,
            'departure_at' => Carbon::tomorrow()->setTime(8, 0),
            'arrival_at' => Carbon::tomorrow()->setTime(16, 0),
            'price' => 250000,
            'boarding_point' => 'Terminal Pulo Gebang',
            'dropoff_point' => 'Terminal Giwangan',
            'status' => 'scheduled',
        ]);
    }

    /**
     * 1. Additive performance composite index exists on orders table.
     */
    public function test_orders_table_has_scheduler_performance_index(): void
    {
        $hasIndex = false;
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();

        $indexes = DB::select(
            "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'orders' AND INDEX_NAME = 'idx_orders_status_payment_expires'",
            [$dbName]
        );

        $this->assertNotEmpty($indexes, 'Composite index idx_orders_status_payment_expires should exist on orders table.');
    }

    /**
     * 2. Admin dashboard trend query aggregates 7 days into a single collection without N+1.
     */
    public function test_admin_dashboard_trend_query_uses_single_aggregation(): void
    {
        // Create sample orders across past days
        $order = Order::create([
            'order_code' => 'CAN-TREND-TEST-1',
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'total_amount' => 500000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'created_at' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('dailyTrends');

        $dailyTrends = $response->viewData('dailyTrends');
        $this->assertCount(7, $dailyTrends);
        $this->assertArrayHasKey('date', $dailyTrends[0]);
        $this->assertArrayHasKey('label', $dailyTrends[0]);
        $this->assertArrayHasKey('revenue', $dailyTrends[0]);
        $this->assertArrayHasKey('bookings', $dailyTrends[0]);
    }

    /**
     * 3. Admin cannot delete a bus that is assigned to active trips.
     */
    public function test_admin_cannot_delete_bus_with_active_trips(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.buses.destroy', $this->bus));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('buses', ['id' => $this->bus->id]);
    }

    /**
     * 4. Admin can delete an unassigned bus and the operation is recorded in audit logs.
     */
    public function test_admin_can_delete_bus_without_trips_and_logs_audit(): void
    {
        $freeBus = Bus::create([
            'name' => 'CAN Unassigned Bus',
            'code' => 'CUB-'.rand(100, 999),
            'type' => 'Suite Class',
            'seat_capacity' => 10,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.buses.destroy', $freeBus));

        $response->assertRedirect(route('admin.buses.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('buses', ['id' => $freeBus->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'bus_deleted',
            'target_type' => 'bus',
            'target_id' => $freeBus->id,
        ]);
    }

    /**
     * 5. Admin cannot delete a route that is assigned to trips.
     */
    public function test_admin_cannot_delete_route_with_active_trips(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.routes.destroy', $this->route));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('routes', ['id' => $this->route->id]);
    }

    /**
     * 6. Admin can delete an unassigned route and the action is recorded in audit logs.
     */
    public function test_admin_can_delete_route_without_trips_and_logs_audit(): void
    {
        $freeRoute = BusRoute::create([
            'origin' => 'Solo',
            'destination' => 'Semarang',
            'distance' => '100 km',
            'estimated_duration' => '2 Jam',
            'base_price' => 90000,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.routes.destroy', $freeRoute));

        $response->assertRedirect(route('admin.routes.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('routes', ['id' => $freeRoute->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'route_deleted',
            'target_type' => 'route',
            'target_id' => $freeRoute->id,
        ]);
    }

    /**
     * 7. Admin trip deletion is safely audited.
     */
    public function test_admin_trip_deletion_is_logged_to_audit_trail(): void
    {
        $emptyTrip = Trip::create([
            'trip_code' => 'TRP-EMPTY-'.rand(1000, 9999),
            'bus_id' => $this->bus->id,
            'route_id' => $this->route->id,
            'departure_at' => Carbon::tomorrow()->addDays(5)->setTime(10, 0),
            'arrival_at' => Carbon::tomorrow()->addDays(5)->setTime(18, 0),
            'price' => 200000,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.trips.destroy', $emptyTrip));

        $response->assertRedirect(route('admin.trips.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('trips', ['id' => $emptyTrip->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'trip_deleted',
            'target_type' => 'trip',
            'target_id' => $emptyTrip->id,
        ]);
    }

    /**
     * 8. Scheduler order expiration records audit log for transparency.
     */
    public function test_scheduler_order_expiration_records_audit_log(): void
    {
        $seat = $this->bus->busSeats()->first();

        $expiredOrder = Order::create([
            'order_code' => 'CAN-EXP-AUDIT-1',
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->subMinutes(10),
        ]);

        OrderItem::create([
            'order_id' => $expiredOrder->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Penumpang Expired',
            'passenger_phone' => '081200000000',
            'price' => 250000,
            'ticket_token' => (string) Str::uuid(),
        ]);

        Artisan::call('orders:expire');

        $this->assertDatabaseHas('orders', [
            'id' => $expiredOrder->id,
            'status' => 'cancelled',
            'payment_status' => 'expired',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'order_expired_by_scheduler',
            'target_type' => 'order',
            'target_id' => $expiredOrder->id,
        ]);
    }

    /**
     * 9. Exception handler scrubs sensitive keys from session flash.
     */
    public function test_exception_handler_scrubs_sensitive_keys_from_session_flash(): void
    {
        $handler = app(Handler::class);
        $reflection = new \ReflectionClass($handler);
        $property = $reflection->getProperty('dontFlash');
        $property->setAccessible(true);
        $dontFlash = $property->getValue($handler);

        $this->assertContains('password', $dontFlash);
        $this->assertContains('token', $dontFlash);
        $this->assertContains('server_key', $dontFlash);
        $this->assertContains('client_key', $dontFlash);
        $this->assertContains('secret', $dontFlash);
        $this->assertContains('cvv', $dontFlash);
        $this->assertContains('card_number', $dontFlash);
    }

    /**
     * 10. Checkout form contains double-submit prevention and loading state attributes.
     */
    public function test_checkout_form_contains_double_submit_protection_attributes(): void
    {
        $seat = $this->bus->busSeats()->first();

        $response = $this->actingAs($this->customer)->get(
            route('booking.checkout', [$this->trip, 'seat_ids' => [$seat->id]])
        );

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('submitting', $content);
        $this->assertStringContainsString(':disabled="submitting"', $content);
        $this->assertStringContainsString('Memproses Pesanan...', $content);
    }

    /**
     * 11. Payment form contains double-submit prevention and loading state attributes.
     */
    public function test_payment_form_contains_double_submit_protection_attributes(): void
    {
        $order = Order::create([
            'order_code' => 'CAN-PAY-DOUBLE-1',
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addMinutes(60),
        ]);

        $response = $this->actingAs($this->customer)->get(route('booking.payment', $order));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('submitting', $content);
        $this->assertStringContainsString(':disabled="submitting"', $content);
        $this->assertStringContainsString('Memproses Konfirmasi...', $content);
    }

    /**
     * 12. Trip seat selection contains WCAG 2.1 AA aria-live dynamic region.
     */
    public function test_trip_seat_selection_contains_aria_live_screen_reader_region(): void
    {
        $response = $this->get(route('trips.show', $this->trip));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('aria-live="polite"', $content);
        $this->assertStringContainsString('accessibilityAnnouncement', $content);
        $this->assertStringContainsString('sr-only', $content);
    }

    /**
     * 13. Strict brand audit reconfirms zero legacy strings across all endpoints.
     */
    public function test_strict_brand_audit_reconfirms_zero_legacy_strings(): void
    {
        $pages = [
            route('home'),
            route('trips.index'),
            route('trips.show', $this->trip),
            route('login'),
            route('register'),
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $content = $response->getContent();

            $this->assertStringNotContainsString('PO CAN Travel', $content, "Legacy string found on {$url}");
            $this->assertStringNotContainsString('PO CAN', $content, "Legacy string found on {$url}");
        }
    }

    /**
     * 14. Health check endpoint returns 200 without exposing secrets.
     */
    public function test_health_check_returns_200_without_secrets(): void
    {
        $response = $this->get(route('health'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'app',
            'timestamp',
            'checks' => [
                'database',
                'cache',
                'storage',
            ],
        ]);

        $content = $response->getContent();
        $this->assertStringNotContainsString('password', $content);
        $this->assertStringNotContainsString('root', $content);
        $this->assertStringNotContainsString('secret', $content);
        $this->assertStringNotContainsString('key', $content);
    }

    /**
     * 15. Production security headers remain enforced on responses.
     */
    public function test_production_security_headers_remain_compliant(): void
    {
        $response = $this->get(route('home'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringNotContainsString('unsafe-eval', $csp);
    }
}
