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

class Sprint11BrandIdentityTest extends TestCase
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
            ['email' => 'admin.sprint11@cantravel.com'],
            [
                'name' => 'Admin Sprint 11',
                'password' => bcrypt('password123'),
                'phone' => '081234567811',
                'role' => 'admin',
            ]
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'customer.sprint11@cantravel.com'],
            [
                'name' => 'Customer Sprint 11',
                'password' => bcrypt('password123'),
                'phone' => '081298765411',
                'role' => 'customer',
            ]
        );

        $this->bus = Bus::create([
            'name' => 'CAN Executive S11',
            'code' => 'CES11-'.rand(100, 999),
            'type' => 'Executive',
            'seat_capacity' => 12,
            'facilities' => ['AC', 'WiFi', 'USB Charger'],
            'status' => 'active',
        ]);
        $this->bus->generateSeats(12);

        $this->route = BusRoute::create([
            'origin' => 'Jakarta S11',
            'destination' => 'Semarang S11',
            'distance' => '450 km',
            'estimated_duration' => '6 Jam',
            'base_price' => 220000,
            'status' => 'active',
        ]);

        $this->trip = Trip::create([
            'trip_code' => 'TRP-11-'.rand(1000, 9999),
            'bus_id' => $this->bus->id,
            'route_id' => $this->route->id,
            'departure_at' => Carbon::tomorrow()->setTime(7, 30),
            'arrival_at' => Carbon::tomorrow()->setTime(13, 30),
            'price' => 220000,
            'boarding_point' => 'Terminal Pulo Gebang',
            'dropoff_point' => 'Terminal Terboyo',
            'status' => 'scheduled',
        ]);
    }

    protected function createTestOrder(string $status = 'pending', string $paymentStatus = 'unpaid'): Order
    {
        $seat = $this->bus->busSeats()->first();

        $order = Order::create([
            'order_code' => 'CAN-11-'.strtoupper(Str::random(6)),
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'total_amount' => 220000,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => 'BCA Virtual Account',
            'expires_at' => Carbon::now()->addHours(2),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Passenger Sprint 11',
            'passenger_phone' => '081234567890',
            'price' => 220000,
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

    public function test_official_logo_image_files_exist_in_public_directory(): void
    {
        $this->assertTrue(
            File::exists(public_path('images/logo/can-travel-logo.png')),
            'Transparent official logo missing in public/images/logo/can-travel-logo.png'
        );
        $this->assertTrue(
            File::exists(public_path('images/logo/can-travel-logo-white-bg.png')),
            'White background official logo missing in public/images/logo/can-travel-logo-white-bg.png'
        );
    }

    public function test_official_logo_rendered_on_customer_navbar_and_footer(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('images/logo/can-travel-logo.png');
        $response->assertSee('alt="CAN Travel"', false);
    }

    public function test_official_logo_rendered_on_admin_sidebar(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('images/logo/can-travel-logo.png');
    }

    public function test_official_logo_rendered_on_checkout_and_payment_pages(): void
    {
        $seat = $this->bus->busSeats()->first();
        $checkoutResponse = $this->actingAs($this->customer)->get("/trips/{$this->trip->id}/checkout?seat_ids={$seat->id}");
        $checkoutResponse->assertStatus(200);
        $checkoutResponse->assertSee('images/logo/can-travel-logo.png');

        $order = $this->createTestOrder('pending', 'unpaid');
        $paymentResponse = $this->actingAs($this->customer)->get("/orders/{$order->id}/payment");
        $paymentResponse->assertStatus(200);
        $paymentResponse->assertSee('images/logo/can-travel-logo.png');
    }

    public function test_official_logo_rendered_on_ticket_verification(): void
    {
        $order = $this->createTestOrder('confirmed', 'paid');
        $item = $order->orderItems()->first();

        $response = $this->get("/tickets/verify/{$item->ticket_token}");
        $response->assertStatus(200);
        $response->assertSee('images/logo/can-travel-logo.png');
    }

    public function test_error_pages_render_official_can_travel_logo(): void
    {
        $view404 = view('errors.404')->render();
        $this->assertStringContainsString('images/logo/can-travel-logo.png', $view404);

        $view429 = view('errors.429')->render();
        $this->assertStringContainsString('images/logo/can-travel-logo.png', $view429);

        $view500 = view('errors.500')->render();
        $this->assertStringContainsString('images/logo/can-travel-logo.png', $view500);
    }

    public function test_favicon_and_theme_color_metadata(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('meta name="theme-color" content="#062A52"', false);
        $response->assertSee('link rel="icon" type="image/png"', false);
        $response->assertSee('can-travel-logo.png');
    }

    public function test_hero_section_contains_official_branding_and_cta(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Cari Tiket');
        $response->assertSee('Perjalanan Nyaman');
        $response->assertSee('Platform Pemesanan Tiket Bus Online Resmi');
    }

    public function test_brand_palette_tokens_configured_in_tailwind(): void
    {
        $tailwindConfig = File::get(base_path('tailwind.config.js'));
        $this->assertStringContainsString('#062A52', $tailwindConfig, 'Deep Navy token missing');
        $this->assertStringContainsString('#1268B3', $tailwindConfig, 'Primary Blue token missing');
        $this->assertStringContainsString('#F5A623', $tailwindConfig, 'Accent Orange token missing');
        $this->assertStringContainsString('#F5F8FC', $tailwindConfig, 'Light Background token missing');
    }

    public function test_auth_pages_render_official_logo(): void
    {
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('images/logo/can-travel-logo.png');

        $registerResponse = $this->get('/register');
        $registerResponse->assertStatus(200);
        $registerResponse->assertSee('images/logo/can-travel-logo.png');
    }
}
