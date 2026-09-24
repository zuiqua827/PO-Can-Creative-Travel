<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Route as BusRoute;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class Sprint10OptimizationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $customer;

    protected Bus $bus;

    protected BusRoute $route;

    protected Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.sprint10@cantravel.com'],
            [
                'name' => 'Admin Sprint 10',
                'password' => bcrypt('password123'),
                'phone' => '081234567810',
                'role' => 'admin',
            ]
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'customer.sprint10@cantravel.com'],
            [
                'name' => 'Customer Sprint 10',
                'password' => bcrypt('password123'),
                'phone' => '081298765410',
                'role' => 'customer',
            ]
        );

        $this->bus = Bus::create([
            'name' => 'CAN Sprinter 10',
            'code' => 'CS10-'.rand(100, 999),
            'type' => 'Executive',
            'seat_capacity' => 10,
            'facilities' => ['AC', 'WiFi'],
            'status' => 'active',
        ]);
        $this->bus->generateSeats(10);

        $this->route = BusRoute::create([
            'origin' => 'Jakarta',
            'destination' => 'Bandung',
            'distance' => '150 km',
            'estimated_duration' => '3 Jam',
            'base_price' => 175000,
            'status' => 'active',
        ]);

        $this->trip = Trip::create([
            'trip_code' => 'TRP-10-'.rand(1000, 9999),
            'bus_id' => $this->bus->id,
            'route_id' => $this->route->id,
            'departure_at' => Carbon::tomorrow()->setTime(8, 0),
            'arrival_at' => Carbon::tomorrow()->setTime(11, 0),
            'price' => 175000,
            'boarding_point' => 'Pool Jakarta',
            'dropoff_point' => 'Pool Bandung',
            'status' => 'scheduled',
        ]);
    }

    protected function createTestOrder(string $status = 'pending', string $paymentStatus = 'unpaid'): Order
    {
        $seat = $this->bus->busSeats()->first();

        $order = Order::create([
            'order_code' => 'CAN-10-'.strtoupper(Str::random(6)),
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'total_amount' => 175000,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => 'BCA Virtual Account',
            'expires_at' => Carbon::now()->addHours(2),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Sprint 10 Passenger',
            'passenger_phone' => '081234567890',
            'price' => 175000,
            'ticket_token' => Str::random(40),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $this->customer->id,
            'payment_reference' => 'PAY-'.$order->order_code,
            'amount' => $order->total_amount,
            'status' => $paymentStatus === 'paid' ? 'success' : 'pending',
            'payment_method' => 'bank_transfer',
        ]);

        return $order;
    }

    public function test_guest_is_redirected_to_login_from_dashboard_and_orders(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $responseOrders = $this->get('/orders');
        $responseOrders->assertRedirect('/login');
    }

    public function test_customer_accessing_dashboard_redirects_to_my_orders(): void
    {
        $response = $this->actingAs($this->customer)->get('/dashboard');
        $response->assertRedirect(route('orders.index'));
    }

    public function test_admin_accessing_dashboard_redirects_to_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_orders_alias_redirects_customer_to_my_orders(): void
    {
        $response = $this->actingAs($this->customer)->get('/orders');
        $response->assertRedirect(route('orders.index'));
    }

    public function test_order_detail_alias_redirects_customer_to_my_orders_show(): void
    {
        $order = $this->createTestOrder();

        $response = $this->actingAs($this->customer)->get("/orders/{$order->id}");
        $response->assertRedirect(route('orders.show', $order));
    }

    public function test_profile_page_renders_recent_orders_card(): void
    {
        $order = $this->createTestOrder('confirmed', 'paid');

        $response = $this->actingAs($this->customer)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Pesanan Terakhir');
        $response->assertSee($order->order_code);
    }

    public function test_skip_to_content_links_present_in_public_and_admin_layouts(): void
    {
        // Public / Customer layout
        $response = $this->actingAs($this->customer)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('href="#main-content"', false);
        $response->assertSee('id="main-content"', false);

        // Admin layout
        $adminResponse = $this->actingAs($this->admin)->get('/admin/dashboard');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('href="#main-content"', false);
        $adminResponse->assertSee('id="main-content"', false);
    }

    public function test_zero_native_alert_calls_in_any_blade_template(): void
    {
        $bladeFiles = File::allFiles(resource_path('views'));

        $violations = [];

        foreach ($bladeFiles as $file) {
            $content = File::get($file->getRealPath());

            // Regex matches alert(...) in scripts or event handlers, excluding alert styles/classes like .alert- or alert_
            if (preg_match('/(?<![\w\.\-])alert\s*\(/i', $content)) {
                $violations[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty($violations, 'Found native window.alert() in: '.implode(', ', $violations));
    }

    public function test_order_cancellation_confirmation_guard_in_admin_order_detail(): void
    {
        $order = $this->createTestOrder('pending');

        $response = $this->actingAs($this->admin)->get("/admin/orders/{$order->id}");
        $response->assertStatus(200);
        $response->assertSee('Perhatian: Mengubah status pesanan menjadi Cancelled akan membatalkan tiket dan melepaskan kursi. Lanjutkan?', false);
    }

    public function test_one_click_copy_button_present_in_customer_order_detail(): void
    {
        $order = $this->createTestOrder('confirmed', 'paid');

        $response = $this->actingAs($this->customer)->get("/my-orders/{$order->id}");
        $response->assertStatus(200);
        $response->assertSee('aria-label="Salin Kode Pesanan"', false);
        $response->assertSee('order-code-text', false);
    }
}
