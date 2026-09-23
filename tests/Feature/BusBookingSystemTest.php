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
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BusBookingSystemTest extends TestCase
{
    /**
     * User registration test.
     */
    public function test_user_registration_success(): void
    {
        $uniqueEmail = 'register_'.time().'@test.com';
        $response = $this->post(route('register.post'), [
            'name' => 'Calon Penumpang',
            'email' => $uniqueEmail,
            'phone' => '081234567000',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    /**
     * User registration validation fails on invalid input.
     */
    public function test_user_registration_validation_fails(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => '',
            'email' => 'not-an-email',
            'phone' => '',
            'password' => '123',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
        $this->assertGuest();
    }

    /**
     * User login test.
     */
    public function test_user_login_success(): void
    {
        $customer = User::where('email', 'budi@gmail.com')->first();
        if (! $customer) {
            $customer = User::factory()->create([
                'email' => 'budi@gmail.com',
                'password' => 'password',
                'role' => 'customer',
            ]);
        }

        $response = $this->post(route('login.post'), [
            'email' => 'budi@gmail.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($customer);
    }

    /**
     * User login with invalid credentials fails.
     */
    public function test_user_login_with_invalid_credentials_fails(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => 'budi@gmail.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Public pages load CAN Travel branding and not PO CAN Travel.
     */
    public function test_public_pages_load_correctly(): void
    {
        $responseHome = $this->get('/');
        $responseHome->assertStatus(200);
        $responseHome->assertSee('CAN Travel');
        $responseHome->assertDontSee('PO CAN Travel');

        $responseTrips = $this->get('/trips');
        $responseTrips->assertStatus(200);
        $responseTrips->assertSee('CAN Travel');
        $responseTrips->assertDontSee('PO CAN Travel');
    }

    /**
     * 1. Task 14: Trip search engine filtering.
     */
    public function test_trip_search_filters_correctly(): void
    {
        $trip = Trip::with(['route', 'bus'])->where('status', 'scheduled')->where('departure_at', '>', now())->first();
        $this->assertNotNull($trip);

        // Search with exact origin
        $response = $this->get(route('trips.index', [
            'origin' => $trip->route->origin,
            'destination' => $trip->route->destination,
        ]));

        $response->assertStatus(200);
        $response->assertSee($trip->trip_code);
    }

    /**
     * 2. Task 14: Unavailable trip (cancelled) cannot be booked.
     */
    public function test_unavailable_trip_is_not_bookable(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create();
        $trip = Trip::where('status', 'scheduled')->first();
        $this->assertNotNull($trip);

        // Mark trip as cancelled
        $cancelledTrip = Trip::factory()->create([
            'bus_id' => $trip->bus_id,
            'route_id' => $trip->route_id,
            'trip_code' => 'TRIP-CANCELLED-'.rand(100, 999),
            'status' => 'cancelled',
            'departure_at' => now()->addDays(2),
            'arrival_at' => now()->addDays(2)->addHours(8),
        ]);

        $response = $this->actingAs($customer)->get(route('booking.checkout', [
            'trip' => $cancelledTrip,
            'seat_ids' => [1],
        ]));

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('error');
    }

    /**
     * 3. Task 14: Expired trip (past departure) cannot be booked.
     */
    public function test_expired_trip_cannot_be_booked(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create();
        $trip = Trip::first();

        $pastTrip = Trip::factory()->create([
            'bus_id' => $trip->bus_id,
            'route_id' => $trip->route_id,
            'trip_code' => 'TRIP-PAST-'.rand(100, 999),
            'status' => 'scheduled',
            'departure_at' => now()->subDay(),
            'arrival_at' => now()->subHours(16),
        ]);

        $response = $this->actingAs($customer)->get(route('booking.checkout', [
            'trip' => $pastTrip,
            'seat_ids' => [1],
        ]));

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('error');
    }

    /**
     * 4. Task 14: Unavailable seat (e.g. maintenance) cannot be booked.
     */
    public function test_unavailable_seat_cannot_be_selected(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create();
        $trip = Trip::with('bus.busSeats')->where('status', 'scheduled')->where('departure_at', '>', now())->first();
        $this->assertNotNull($trip);

        // Find or set a seat on this bus to maintenance status
        $maintenanceSeat = BusSeat::where('bus_id', $trip->bus_id)->where('status', 'maintenance')->first();
        if (! $maintenanceSeat) {
            $maintenanceSeat = $trip->bus->busSeats->last();
            $maintenanceSeat->update(['status' => 'maintenance']);
        }

        $response = $this->actingAs($customer)->post(route('booking.store', $trip), [
            'seats' => [$maintenanceSeat->id],
            'passengers' => [
                $maintenanceSeat->id => [
                    'name' => 'Test Passenger',
                    'phone' => '081234567890',
                ],
            ],
            'payment_method' => 'BCA Virtual Account',
        ]);

        $response->assertRedirect(route('trips.show', $trip));
        $response->assertSessionHas('error');
    }

    /**
     * 5. Task 14: Cancelled order releases seats.
     */
    public function test_cancelled_order_releases_seats(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create();
        $trip = Trip::with('bus.busSeats')->where('status', 'scheduled')->where('departure_at', '>', now())->first();

        $bookedIds = $trip->getBookedSeatIds();
        $availableSeat = $trip->bus->busSeats->whereNotIn('id', $bookedIds)->first();
        $this->assertNotNull($availableSeat);

        // Create a pending order with this seat
        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-TEST-'.rand(10000, 99999),
            'total_amount' => $trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $availableSeat->id,
            'passenger_name' => 'Tester',
            'passenger_phone' => '081234567890',
            'price' => $trip->price,
        ]);

        // Seat is currently booked
        $this->assertContains($availableSeat->id, $trip->fresh()->getBookedSeatIds());

        // Cancel order via customer endpoint
        $response = $this->actingAs($customer)->post(route('orders.cancel', $order));
        $response->assertRedirect();

        // Seat is now RELEASED!
        $this->assertNotContains($availableSeat->id, $trip->fresh()->getBookedSeatIds());
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    /**
     * 6. Task 14: Expired order releases seats.
     */
    public function test_expired_order_releases_seats(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create();
        $trip = Trip::with('bus.busSeats')->where('status', 'scheduled')->where('departure_at', '>', now())->first();

        $bookedIds = $trip->getBookedSeatIds();
        $availableSeat = $trip->bus->busSeats->whereNotIn('id', $bookedIds)->first();
        $this->assertNotNull($availableSeat);

        // Create an already-expired order
        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-EXP-'.rand(10000, 99999),
            'total_amount' => $trip->price,
            'status' => 'cancelled',
            'payment_status' => 'expired',
            'expires_at' => now()->subMinutes(30),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $availableSeat->id,
            'passenger_name' => 'Tester Expired',
            'passenger_phone' => '081234567890',
            'price' => $trip->price,
        ]);

        // Expired order does NOT hold seats
        $this->assertNotContains($availableSeat->id, $trip->fresh()->getBookedSeatIds());
    }

    /**
     * 7. Task 14: Customer cannot view another customer order (IDOR).
     */
    public function test_customer_cannot_access_another_customer_order(): void
    {
        $userA = User::where('role', 'customer')->first() ?? User::factory()->create();
        $userB = User::factory()->create(['role' => 'customer']);

        $trip = Trip::first();
        $orderB = Order::create([
            'user_id' => $userB->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-SEC-'.rand(10000, 99999),
            'total_amount' => $trip->price,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($userA)->get(route('orders.show', $orderB));
        $response->assertStatus(403);
    }

    /**
     * 8. Task 14: Customer cannot modify another customer order (Cancel/Payment).
     */
    public function test_customer_cannot_modify_another_customer_order(): void
    {
        $userA = User::where('role', 'customer')->first() ?? User::factory()->create();
        $userB = User::factory()->create(['role' => 'customer']);

        $trip = Trip::first();
        $orderB = Order::create([
            'user_id' => $userB->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-MOD-'.rand(10000, 99999),
            'total_amount' => $trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        // User A attempts to cancel User B's order
        $responseCancel = $this->actingAs($userA)->post(route('orders.cancel', $orderB));
        $responseCancel->assertStatus(403);

        // User A attempts to process payment on User B's order
        $responsePay = $this->actingAs($userA)->post(route('booking.processPayment', $orderB));
        $responsePay->assertStatus(403);
    }

    /**
     * 9. Task 14: Payment cannot be repeated (Idempotency).
     */
    public function test_payment_cannot_be_repeated_idempotency(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create();
        $trip = Trip::first();

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-IDEM-'.rand(10000, 99999),
            'total_amount' => $trip->price,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'BCA Virtual Account',
            'payment_reference' => 'PAY-IDEM-'.rand(10000, 99999),
            'amount' => $order->total_amount,
            'status' => 'success',
            'paid_at' => now(),
        ]);

        $initialPaymentCount = Payment::count();

        // Repeating payment request
        $response = $this->actingAs($customer)->post(route('booking.processPayment', $order));

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('info', 'Pesanan ini sudah dibayar sebelumnya.');

        // No new payment created
        $this->assertEquals($initialPaymentCount, Payment::count());
    }

    /**
     * 10. Task 14: Expired payment cannot be completed.
     */
    public function test_expired_payment_cannot_be_completed(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create();
        $trip = Trip::first();

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-EXP-PAY-'.rand(10000, 99999),
            'total_amount' => $trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->subMinute(), // Expired
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'BCA Virtual Account',
            'payment_reference' => 'PAY-EXP-'.rand(10000, 99999),
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer)->post(route('booking.processPayment', $order));

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('error');
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals('expired', $order->fresh()->payment_status);
    }

    /**
     * 11. Task 14: Invalid order status transition is prevented.
     */
    public function test_invalid_order_status_transition_prevented(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->admin()->create();
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create();
        $trip = Trip::first();

        // Create cancelled order
        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'CAN-TRANS-'.rand(10000, 99999),
            'total_amount' => $trip->price,
            'status' => 'cancelled',
            'payment_status' => 'expired',
        ]);

        // Admin attempts invalid transition: cancelled -> confirmed
        $response = $this->actingAs($admin)->put(route('admin.orders.update', $order), [
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    /**
     * 12. Task 14: Admin order filtering by search, status, and date.
     */
    public function test_admin_order_filtering_by_status_and_date(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.orders.index', [
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'date' => Carbon::today()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('orders');
    }

    /**
     * 13. Task 14: Admin authorization enforced.
     */
    public function test_admin_authorization_enforced(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create();
        $this->actingAs($customer)->get('/admin/dashboard')->assertStatus(403);

        $admin = User::where('role', 'admin')->first() ?? User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Dashboard Operasional');
    }

    /**
     * 14. Task 14: Server-side price calculation authoritative.
     */
    public function test_order_creation_calculates_price_server_side(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $trip = Trip::with('bus.busSeats')->where('status', 'scheduled')->where('departure_at', '>', now())->get()->first(function ($t) {
            return count($t->bus->busSeats) - count($t->getBookedSeatIds()) >= 2;
        });

        if (! $trip) {
            $bus = Bus::factory()->create(['seat_capacity' => 10]);
            $bus->generateSeats(10);
            $route = Route::first() ?? Route::factory()->create();
            $trip = Trip::factory()->create([
                'bus_id' => $bus->id,
                'route_id' => $route->id,
                'price' => 200000,
                'status' => 'scheduled',
                'departure_at' => now()->addDays(3),
                'arrival_at' => now()->addDays(3)->addHours(8),
            ]);
            $trip->load('bus.busSeats');
        }

        $bookedIds = $trip->getBookedSeatIds();
        $availableSeats = $trip->bus->busSeats->whereNotIn('id', $bookedIds)->take(2);
        $seatIds = $availableSeats->pluck('id')->toArray();

        $passengers = [];
        foreach ($seatIds as $sid) {
            $passengers[$sid] = [
                'name' => 'Penumpang '.$sid,
                'phone' => '081234567890',
            ];
        }

        $response = $this->actingAs($customer)->post(route('booking.store', $trip), [
            'seats' => $seatIds,
            'passengers' => $passengers,
            'payment_method' => 'QRIS Instant Pay',
        ]);

        $response->assertRedirect();

        $expectedTotal = $trip->price * count($seatIds);
        $order = Order::where('user_id', $customer->id)->latest()->first();
        $this->assertEquals($expectedTotal, $order->total_amount);
        $this->assertStringStartsWith('CAN-', $order->order_code);
    }

    /**
     * 15. Task 14: Multiple seat booking creates manifest records.
     */
    public function test_multiple_seat_booking_creates_manifest_records(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $trip = Trip::with('bus.busSeats')->where('status', 'scheduled')->where('departure_at', '>', now())->get()->first(function ($t) {
            return count($t->bus->busSeats) - count($t->getBookedSeatIds()) >= 2;
        });

        if (! $trip) {
            $bus = Bus::factory()->create(['seat_capacity' => 10]);
            $bus->generateSeats(10);
            $route = Route::first() ?? Route::factory()->create();
            $trip = Trip::factory()->create([
                'bus_id' => $bus->id,
                'route_id' => $route->id,
                'price' => 200000,
                'status' => 'scheduled',
                'departure_at' => now()->addDays(3),
                'arrival_at' => now()->addDays(3)->addHours(8),
            ]);
            $trip->load('bus.busSeats');
        }

        $bookedIds = $trip->getBookedSeatIds();
        $availableSeats = $trip->bus->busSeats->whereNotIn('id', $bookedIds)->take(2);
        $seatIds = $availableSeats->pluck('id')->toArray();

        $passengers = [
            $seatIds[0] => ['name' => 'Ahmad Dahlan', 'phone' => '081200000001', 'id_number' => '3201010101010001'],
            $seatIds[1] => ['name' => 'Siti Walidah', 'phone' => '081200000002', 'id_number' => '3201010101010002'],
        ];

        $response = $this->actingAs($customer)->post(route('booking.store', $trip), [
            'seats' => $seatIds,
            'passengers' => $passengers,
            'payment_method' => 'BCA Virtual Account',
        ]);

        $response->assertRedirect();

        $order = Order::where('user_id', $customer->id)->latest()->first();
        $this->assertCount(2, $order->orderItems);
        $this->assertEquals('Ahmad Dahlan', $order->orderItems->first()->passenger_name);
    }

    /**
     * 16. Task 14: Concurrent seat booking is prevented (Double-Booking Protection).
     */
    public function test_concurrent_seat_booking_is_prevented(): void
    {
        $trip = Trip::with('bus.busSeats')->where('status', 'scheduled')->where('departure_at', '>', now())->first();
        $this->assertNotNull($trip);

        $customer1 = User::where('role', 'customer')->first() ?? User::factory()->create();
        $customer2 = User::factory()->create(['role' => 'customer']);

        $bookedIds = $trip->getBookedSeatIds();
        $availableSeat = $trip->bus->busSeats->whereNotIn('id', $bookedIds)->first();
        $this->assertNotNull($availableSeat);

        // Customer 1 books the seat
        $response1 = $this->actingAs($customer1)->post(route('booking.store', $trip), [
            'seats' => [$availableSeat->id],
            'passengers' => [
                $availableSeat->id => [
                    'name' => 'Penumpang Pertama',
                    'phone' => '081299990001',
                ],
            ],
            'payment_method' => 'BCA Virtual Account',
        ]);
        $response1->assertRedirect();

        // Customer 2 attempts to book the SAME seat concurrently
        $response2 = $this->actingAs($customer2)->post(route('booking.store', $trip), [
            'seats' => [$availableSeat->id],
            'passengers' => [
                $availableSeat->id => [
                    'name' => 'Penumpang Kedua',
                    'phone' => '081299990002',
                ],
            ],
            'payment_method' => 'Mandiri Virtual Account',
        ]);

        $response2->assertRedirect(route('trips.show', $trip));
        $response2->assertSessionHas('error');
    }
}
