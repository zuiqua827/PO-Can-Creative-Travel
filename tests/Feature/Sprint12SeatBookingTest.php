<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Route as BusRoute;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Sprint12SeatBookingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected User $otherCustomer;

    protected Trip $trip;

    protected array $seats;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::firstOrCreate(
            ['email' => 'customer.test12@cantravel.com'],
            [
                'name' => 'Customer Sprint 12',
                'password' => bcrypt('password123'),
                'phone' => '081298765412',
                'role' => 'customer',
            ]
        );

        $this->otherCustomer = User::firstOrCreate(
            ['email' => 'other.test12@cantravel.com'],
            [
                'name' => 'Other Customer Sprint 12',
                'password' => bcrypt('password123'),
                'phone' => '081298765499',
                'role' => 'customer',
            ]
        );

        $bus = Bus::create([
            'name' => 'CAN Test Liner S12',
            'code' => 'CTL-S12-'.uniqid(),
            'type' => 'Executive',
            'seat_capacity' => 12,
            'facilities' => ['AC', 'WiFi', 'USB Charger'],
            'description' => 'Test fleet for Sprint 12 seat verification',
            'status' => 'active',
        ]);
        $bus->generateSeats(12);

        $route = BusRoute::firstOrCreate(
            ['origin' => 'Jakarta (Terminal Pulo Gebang)', 'destination' => 'Bandung (Terminal Leuwipanjang)'],
            [
                'distance' => '150 km',
                'estimated_duration' => '3 Jam',
                'base_price' => 120000,
                'status' => 'active',
            ]
        );

        $this->trip = Trip::create([
            'bus_id' => $bus->id,
            'route_id' => $route->id,
            'trip_code' => 'TRIP-S12-'.uniqid(),
            'departure_at' => now()->addDays(2),
            'arrival_at' => now()->addDays(2)->addHours(3),
            'price' => 125000,
            'boarding_point' => 'Jakarta (Terminal Pulo Gebang)',
            'drop_off_point' => 'Bandung (Terminal Leuwipanjang)',
            'status' => 'scheduled',
        ]);

        $this->seats = $bus->busSeats()->orderBy('row')->orderBy('column')->get()->all();
    }

    /**
     * 1. Available seat can be viewed and has correct interactive attributes.
     */
    public function test_available_seat_can_be_selected(): void
    {
        $response = $this->get(route('trips.show', $this->trip));

        $response->assertStatus(200);
        $response->assertSee('data-seat-status="available"', false);
        $response->assertSee('seat-picker-btn', false);
        $response->assertSee('Tersedia');
        $response->assertSee('Dipilih');
        $response->assertSee('Tertahan');
        $response->assertSee('Terisi');
    }

    /**
     * 2. Booked seat cannot be selected and has disabled attributes.
     */
    public function test_booked_seat_cannot_be_selected(): void
    {
        $seat = $this->seats[0];

        $order = Order::create([
            'user_id' => $this->otherCustomer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-S12-BOOKED-1',
            'total_amount' => $this->trip->price,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'expires_at' => null,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Paid Passenger',
            'passenger_phone' => '081234567890',
            'price' => $this->trip->price,
        ]);

        $response = $this->get(route('trips.show', $this->trip));
        $response->assertStatus(200);
        $response->assertSee('data-seat-status="booked"', false);

        // Attempting checkout directly with this booked seat must fail
        $checkoutResponse = $this->actingAs($this->customer)
            ->get(route('booking.checkout', ['trip' => $this->trip, 'seat_ids' => $seat->id]));
        $checkoutResponse->assertRedirect(route('trips.show', $this->trip));
        $checkoutResponse->assertSessionHas('error');
    }

    /**
     * 3. Active held seat cannot be selected.
     */
    public function test_active_held_seat_cannot_be_selected(): void
    {
        $seat = $this->seats[1];

        $order = Order::create([
            'user_id' => $this->otherCustomer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-S12-HELD-1',
            'total_amount' => $this->trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Holding User',
            'passenger_phone' => '081234567890',
            'price' => $this->trip->price,
        ]);

        $response = $this->get(route('trips.show', $this->trip));
        $response->assertStatus(200);
        $response->assertSee('data-seat-status="held"', false);

        // Checkout with held seat should fail
        $checkoutResponse = $this->actingAs($this->customer)
            ->get(route('booking.checkout', ['trip' => $this->trip, 'seat_ids' => $seat->id]));
        $checkoutResponse->assertRedirect(route('trips.show', $this->trip));
        $checkoutResponse->assertSessionHas('error');
    }

    /**
     * 4. Expired held seat is released and can be selected.
     */
    public function test_expired_held_seat_can_be_selected(): void
    {
        $seat = $this->seats[2];

        $order = Order::create([
            'user_id' => $this->otherCustomer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-S12-EXP-1',
            'total_amount' => $this->trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->subMinutes(10), // expired hold
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Expired User',
            'passenger_phone' => '081234567890',
            'price' => $this->trip->price,
        ]);

        // Seat should NOT be considered booked or held because expires_at is past
        $this->assertNotContains($seat->id, $this->trip->getBookedSeatIds());

        $checkoutResponse = $this->actingAs($this->customer)
            ->get(route('booking.checkout', ['trip' => $this->trip, 'seat_ids' => $seat->id]));
        $checkoutResponse->assertStatus(200);
        $checkoutResponse->assertSee($seat->seat_number);
    }

    /**
     * 5. Selected seats reach checkout page with passenger form.
     */
    public function test_selected_seats_reach_checkout(): void
    {
        $seatA = $this->seats[3];
        $seatB = $this->seats[4];

        $response = $this->actingAs($this->customer)
            ->get(route('booking.checkout', ['trip' => $this->trip, 'seat_ids' => "{$seatA->id},{$seatB->id}"]));

        $response->assertStatus(200);
        $response->assertSee("passengers[{$seatA->id}][name]", false);
        $response->assertSee("passengers[{$seatB->id}][name]", false);
        $response->assertSee('Rp '.number_format($this->trip->price * 2, 0, ',', '.'));
    }

    /**
     * 6. Checkout creates order atomically.
     */
    public function test_checkout_creates_order(): void
    {
        $seat = $this->seats[5];

        $response = $this->actingAs($this->customer)->post(route('booking.store', $this->trip), [
            'seats' => [$seat->id],
            'passengers' => [
                $seat->id => [
                    'name' => 'Budi Passenger',
                    'phone' => '081298765412',
                    'id_number' => '3271012345670001',
                ],
            ],
            'payment_method' => 'BCA Virtual Account',
            'notes' => 'Catatan pesanan testing',
        ]);

        $order = Order::where('user_id', $this->customer->id)->where('trip_id', $this->trip->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('unpaid', $order->payment_status);
        $this->assertEquals($this->trip->price, (float) $order->total_amount);

        $response->assertRedirect(route('booking.payment', $order));
    }

    /**
     * 7. Duplicate seat booking (race condition) is prevented.
     */
    public function test_duplicate_seat_booking_is_prevented(): void
    {
        $seat = $this->seats[6];

        // First user reserves seat
        $this->actingAs($this->customer)->post(route('booking.store', $this->trip), [
            'seats' => [$seat->id],
            'passengers' => [
                $seat->id => ['name' => 'First User', 'phone' => '081298765412'],
            ],
            'payment_method' => 'BCA Virtual Account',
        ]);

        // Second user tries to book the same seat before hold expires
        $secondResponse = $this->actingAs($this->otherCustomer)->post(route('booking.store', $this->trip), [
            'seats' => [$seat->id],
            'passengers' => [
                $seat->id => ['name' => 'Second User', 'phone' => '081299998888'],
            ],
            'payment_method' => 'BCA Virtual Account',
        ]);

        $secondResponse->assertRedirect(route('trips.show', $this->trip));
        $secondResponse->assertSessionHas('error');

        // Only 1 order item exists for this seat
        $this->assertEquals(1, OrderItem::where('bus_seat_id', $seat->id)->count());
    }

    /**
     * 8. Cancelled order releases seat immediately.
     */
    public function test_cancelled_order_releases_seat(): void
    {
        $seat = $this->seats[7];

        $order = Order::create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-S12-CANCEL-'.uniqid(),
            'total_amount' => $this->trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'To Cancel',
            'passenger_phone' => '081234567890',
            'price' => $this->trip->price,
        ]);

        $this->assertContains($seat->id, $this->trip->fresh()->getBookedSeatIds());

        // Customer cancels order
        $response = $this->actingAs($this->customer)->post(route('orders.cancel', $order));
        $response->assertRedirect();

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertNotContains($seat->id, $this->trip->fresh()->getBookedSeatIds());
    }

    /**
     * 9. Expired order releases seat via expiration sweep.
     */
    public function test_expired_order_releases_seat(): void
    {
        $seat = $this->seats[8];

        $order = Order::create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-S12-EXPIRE-'.uniqid(),
            'total_amount' => $this->trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->subMinute(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $this->customer->id,
            'payment_method' => 'BCA Virtual Account',
            'payment_reference' => 'PAY-EXP-'.uniqid(),
            'amount' => $this->trip->price,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Overdue User',
            'passenger_phone' => '081234567890',
            'price' => $this->trip->price,
        ]);

        $this->artisan('orders:expire')->assertExitCode(0);

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals('expired', $order->fresh()->payment_status);
        $this->assertNotContains($seat->id, $this->trip->fresh()->getBookedSeatIds());
    }

    /**
     * 10. Successful payment confirms order and issues ticket.
     */
    public function test_successful_payment_confirms_order(): void
    {
        $seat = $this->seats[9];

        $order = Order::create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-S12-PAY-'.uniqid(),
            'total_amount' => $this->trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $this->customer->id,
            'payment_method' => 'BCA Virtual Account',
            'payment_reference' => 'PAY-SIM-'.uniqid(),
            'amount' => $this->trip->price,
            'status' => 'pending',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Budi Santoso',
            'passenger_phone' => '081234567890',
            'price' => $this->trip->price,
        ]);

        $response = $this->actingAs($this->customer)->post(route('booking.processPayment', $order), [
            'payment_simulation' => 'success',
        ]);

        $response->assertRedirect(route('orders.show', $order));
        $this->assertEquals('confirmed', $order->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertNotNull($item->fresh()->ticket_token);
    }

    /**
     * 11. Ticket is generated correctly with token and valid verification.
     */
    public function test_ticket_is_generated_correctly(): void
    {
        $seat = $this->seats[10];

        $order = Order::create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-S12-TKT-'.uniqid(),
            'total_amount' => $this->trip->price,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'expires_at' => null,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'bus_seat_id' => $seat->id,
            'passenger_name' => 'Verified Passenger',
            'passenger_phone' => '081234567890',
            'price' => $this->trip->price,
        ]);

        $this->assertStringStartsWith('TKT-', $item->ticket_token);

        $verifyResponse = $this->get(route('tickets.verify', ['token' => $item->ticket_token]));
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertSee($item->passenger_name);
        $verifyResponse->assertSee($seat->seat_number);
        $verifyResponse->assertSee($order->order_code);
    }

    /**
     * 12. Demo users do not automatically occupy seats.
     */
    public function test_demo_user_does_not_occupy_seats(): void
    {
        $demoUser = User::where('email', 'demo@pocan.com')->first();
        $this->assertNotNull($demoUser);

        // Demo user has 0 active seat locks
        $activeDemoSeats = OrderItem::whereHas('order', function ($q) use ($demoUser) {
            $q->where('user_id', $demoUser->id)
                ->where('status', '!=', 'cancelled')
                ->where('payment_status', '!=', 'expired');
        })->count();

        $this->assertEquals(0, $activeDemoSeats);
    }

    /**
     * 13. Customer profile updates work normally.
     */
    public function test_profile_still_works(): void
    {
        $response = $this->actingAs($this->customer)->put(route('profile.update'), [
            'name' => 'Customer Updated Name',
            'phone' => '081299887766',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Customer Updated Name', $this->customer->fresh()->name);
        $this->assertEquals('081299887766', $this->customer->fresh()->phone);
    }

    /**
     * 14. Order ownership is protected (IDOR safeguard).
     */
    public function test_order_ownership_is_protected(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-S12-IDOR-'.uniqid(),
            'total_amount' => $this->trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Other customer cannot view
        $response = $this->actingAs($this->otherCustomer)->get(route('orders.show', $order));
        $response->assertStatus(403);
    }

    /**
     * 15. Ticket cancellation ownership is protected.
     */
    public function test_ticket_ownership_is_protected(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-S12-CANCEL-GUARD-'.uniqid(),
            'total_amount' => $this->trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        // Other customer cannot cancel
        $response = $this->actingAs($this->otherCustomer)->post(route('orders.cancel', $order));
        $response->assertStatus(403);
    }

    /**
     * 16. Unauthorized guest cannot access private order operations.
     */
    public function test_unauthorized_user_cannot_access_another_users_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'trip_id' => $this->trip->id,
            'order_code' => 'CAN-S12-GUEST-'.uniqid(),
            'total_amount' => $this->trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->get(route('orders.show', $order));
        $response->assertRedirect(route('login'));

        $paymentResponse = $this->get(route('booking.payment', $order));
        $paymentResponse->assertRedirect(route('login'));
    }

    /**
     * 17. Price is calculated server-side.
     */
    public function test_price_is_calculated_server_side(): void
    {
        $seat = $this->seats[11];

        $response = $this->actingAs($this->customer)->post(route('booking.store', $this->trip), [
            'seats' => [$seat->id],
            'passengers' => [
                $seat->id => ['name' => 'Price Check', 'phone' => '081234567890'],
            ],
            'payment_method' => 'BCA Virtual Account',
            'total_amount' => 1, // Tampered client price must be ignored
        ]);

        $order = Order::where('user_id', $this->customer->id)->where('trip_id', $this->trip->id)->latest()->first();
        $this->assertEquals($this->trip->price, (float) $order->total_amount);
    }

    /**
     * 18. Maximum seat limit (5 seats) remains enforced.
     */
    public function test_maximum_seat_limit_remains_enforced(): void
    {
        // Generate extra seats to test 6 seats
        $sixSeats = array_slice($this->seats, 0, 6);
        $sixSeatIds = array_map(fn ($s) => $s->id, $sixSeats);

        // Checkout rejects > 5 seats
        $checkoutResponse = $this->actingAs($this->customer)
            ->get(route('booking.checkout', ['trip' => $this->trip, 'seat_ids' => implode(',', $sixSeatIds)]));
        $checkoutResponse->assertRedirect(route('trips.show', $this->trip));
        $checkoutResponse->assertSessionHas('error');

        // Store rejects > 5 seats
        $passengers = [];
        foreach ($sixSeatIds as $id) {
            $passengers[$id] = ['name' => 'Passenger '.$id, 'phone' => '081234567890'];
        }

        $storeResponse = $this->actingAs($this->customer)->post(route('booking.store', $this->trip), [
            'seats' => $sixSeatIds,
            'passengers' => $passengers,
            'payment_method' => 'BCA Virtual Account',
        ]);

        $storeResponse->assertSessionHasErrors('seats');
    }
}
