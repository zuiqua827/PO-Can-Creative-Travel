<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BusBookingSystemTest extends TestCase
{
    /**
     * 1. Test user registration success.
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
     * 2. Test user registration validation fails on invalid input.
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
     * 3. Test user login success.
     */
    public function test_user_login_success(): void
    {
        $customer = User::where('email', 'budi@gmail.com')->first();
        if (! $customer) {
            $customer = User::factory()->create([
                'email' => 'budi@gmail.com',
                'password' => Hash::make('password'),
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
     * 4. Test user login with invalid credentials fails.
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
     * 5. Test Customer Order Ownership: User A cannot view User B's order (403 Forbidden).
     */
    public function test_customer_cannot_view_another_customer_order(): void
    {
        $userA = User::where('role', 'customer')->first();
        $userB = User::where('role', 'customer')->where('id', '!=', $userA->id)->first();

        if (! $userB) {
            $userB = User::factory()->create(['role' => 'customer']);
        }

        // Create an order belonging to User B
        $trip = Trip::first();
        $orderB = Order::create([
            'user_id' => $userB->id,
            'trip_id' => $trip->id,
            'order_code' => 'PCT-TEST-'.rand(10000, 99999),
            'total_amount' => $trip->price,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // User A attempts to view User B's order
        $response = $this->actingAs($userA)->get(route('orders.show', $orderB));
        $response->assertStatus(403);
    }

    /**
     * 6. Test public pages: Home & Trip search.
     */
    public function test_public_pages_load_correctly(): void
    {
        $responseHome = $this->get('/');
        $responseHome->assertStatus(200);
        $responseHome->assertSee('PO CAN Travel');

        $responseTrips = $this->get('/trips');
        $responseTrips->assertStatus(200);
    }

    /**
     * 7. Test Admin authorization: Customer denied (403), Admin allowed (200).
     */
    public function test_admin_authorization_enforced(): void
    {
        $customer = User::where('role', 'customer')->first();
        $this->actingAs($customer)->get('/admin/dashboard')->assertStatus(403);

        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            $admin = User::factory()->admin()->create();
        }

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Dashboard Operasional');
    }

    /**
     * 8. Test Double-Booking Protection with Concurrency Lock.
     */
    public function test_double_booking_is_prevented(): void
    {
        $trip = Trip::with('bus.busSeats')->where('status', 'scheduled')->first();
        $this->assertNotNull($trip);

        $customer1 = User::where('role', 'customer')->first();
        $customer2 = User::where('role', 'customer')->where('id', '!=', $customer1->id)->first() ?? User::factory()->create();

        // Select an unbooked seat
        $bookedIds = $trip->getBookedSeatIds();
        $availableSeat = $trip->bus->busSeats->whereNotIn('id', $bookedIds)->first();
        $this->assertNotNull($availableSeat);

        // Booking 1: Customer 1 books the seat
        $response1 = $this->actingAs($customer1)->post(route('booking.store', $trip), [
            'seats' => [$availableSeat->id],
            'passengers' => [
                $availableSeat->id => [
                    'name' => 'Penumpang Pertama',
                    'phone' => '081299990001',
                    'id_number' => '3201010101010001',
                ],
            ],
            'payment_method' => 'BCA Virtual Account',
        ]);
        $response1->assertRedirect();

        // Booking 2: Customer 2 attempts to book the EXACT SAME SEAT concurrently
        $response2 = $this->actingAs($customer2)->post(route('booking.store', $trip), [
            'seats' => [$availableSeat->id],
            'passengers' => [
                $availableSeat->id => [
                    'name' => 'Penumpang Kedua',
                    'phone' => '081299990002',
                    'id_number' => '3201010101010002',
                ],
            ],
            'payment_method' => 'Mandiri Virtual Account',
        ]);

        // Second booking must be rejected back with error
        $response2->assertRedirect(route('trips.show', $trip));
        $response2->assertSessionHas('error');
    }

    /**
     * 9. Test order creation calculates total server-side authoritative.
     */
    public function test_order_creation_calculates_price_server_side(): void
    {
        $trip = Trip::with('bus.busSeats')->where('status', 'scheduled')->first();
        $customer = User::where('role', 'customer')->first();

        $bookedIds = $trip->getBookedSeatIds();
        $availableSeats = $trip->bus->busSeats->whereNotIn('id', $bookedIds)->take(2);

        if ($availableSeats->count() < 2) {
            $this->markTestSkipped('Need at least 2 available seats for multi-seat price test');
        }

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
        $this->assertStringStartsWith('PCT-', $order->order_code);
    }

    /**
     * 10. Test payment process updates status and creates valid e-ticket.
     */
    public function test_payment_simulation_marks_order_as_paid_and_valid(): void
    {
        $customer = User::where('role', 'customer')->first();
        $trip = Trip::first();

        $order = Order::create([
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'order_code' => 'PCT-TEST-'.rand(10000, 99999),
            'total_amount' => $trip->price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHour(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'BCA Virtual Account',
            'payment_reference' => 'PAY-TEST-'.rand(10000, 99999),
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer)
            ->post(route('booking.processPayment', $order));

        $response->assertRedirect(route('orders.show', $order));
        $this->assertEquals('confirmed', $order->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('success', $order->fresh()->payment->status);
    }
}
