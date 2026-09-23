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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BusBookingSystemTest extends TestCase
{
    /**
     * Test home page renders and contains brand name.
     */
    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('PO CAN Travel');
    }

    /**
     * Test trips index page renders.
     */
    public function test_trips_search_page_loads(): void
    {
        $response = $this->get('/trips');
        $response->assertStatus(200);
    }

    /**
     * Test admin authorization middleware blocks regular user.
     */
    public function test_regular_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::where('role', 'customer')->first();
        if (!$customer) {
            $customer = User::create([
                'name' => 'Test Customer',
                'email' => 'cust@test.com',
                'phone' => '08123456789',
                'role' => 'customer',
                'password' => Hash::make('password'),
            ]);
        }

        $response = $this->actingAs($customer)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test admin can access admin dashboard.
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin Test',
                'email' => 'admin_test@pocan.com',
                'phone' => '08123456789',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Operasional');
    }

    /**
     * Test booking flow with concurrency protection (double-booking prevention).
     */
    public function test_cannot_book_already_reserved_seat(): void
    {
        $trip = Trip::with('bus.busSeats')->first();
        $this->assertNotNull($trip);

        $customer1 = User::where('role', 'customer')->first();
        $this->assertNotNull($customer1);

        // Pick an available seat
        $bookedSeatIds = $trip->getBookedSeatIds();
        $availableSeat = $trip->bus->busSeats->whereNotIn('id', $bookedSeatIds)->first();
        $this->assertNotNull($availableSeat, 'Must have at least 1 available seat for test');

        // Customer 1 books the seat
        $bookingData = [
            'seats' => [$availableSeat->id],
            'passengers' => [
                $availableSeat->id => [
                    'name' => 'Passenger One',
                    'phone' => '081299990001',
                    'id_number' => '1234567890123456',
                ]
            ],
            'payment_method' => 'BCA Virtual Account',
            'notes' => 'Test booking 1',
        ];

        $response1 = $this->actingAs($customer1)
            ->post(route('booking.store', $trip), $bookingData);
        
        $response1->assertRedirect();
        
        // Find the created order
        $order = Order::where('user_id', $customer1->id)
            ->where('trip_id', $trip->id)
            ->latest()
            ->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);

        // Customer 2 tries to book the EXACT SAME SEAT on the same trip
        $customer2 = User::where('email', 'siti@gmail.com')->first();
        if (!$customer2) {
            $customer2 = User::create([
                'name' => 'Customer Two',
                'email' => 'cust2@test.com',
                'phone' => '08123456780',
                'role' => 'customer',
                'password' => Hash::make('password'),
            ]);
        }

        $bookingData2 = [
            'seats' => [$availableSeat->id],
            'passengers' => [
                $availableSeat->id => [
                    'name' => 'Passenger Two',
                    'phone' => '081299990002',
                    'id_number' => '1234567890123457',
                ]
            ],
            'payment_method' => 'QRIS Instant Pay',
            'notes' => 'Test booking duplicate',
        ];

        $response2 = $this->actingAs($customer2)
            ->post(route('booking.store', $trip), $bookingData2);

        // Should be rejected back to trip view with error session
        $response2->assertRedirect(route('trips.show', $trip));
        $response2->assertSessionHas('error');
    }

    /**
     * Test payment simulation confirms order and issues ticket.
     */
    public function test_payment_simulation_marks_order_as_paid(): void
    {
        $customer = User::where('role', 'customer')->first();
        $order = Order::where('user_id', $customer->id)->where('payment_status', 'unpaid')->first();

        if (!$order) {
            // Create a pending unpaid order
            $trip = Trip::first();
            $seat = $trip->bus->busSeats->first();
            $order = Order::create([
                'user_id' => $customer->id,
                'trip_id' => $trip->id,
                'order_code' => 'TEST-ORD-' . rand(1000, 9999),
                'total_amount' => $trip->price,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'expires_at' => now()->addHour(),
            ]);
            Payment::create([
                'order_id' => $order->id,
                'user_id' => $customer->id,
                'payment_method' => 'BCA Virtual Account',
                'payment_reference' => 'PAY-TEST-' . rand(1000, 9999),
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);
        }

        $response = $this->actingAs($customer)
            ->post(route('booking.processPayment', $order));

        $response->assertRedirect(route('orders.show', $order));
        $this->assertEquals('confirmed', $order->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('success', $order->fresh()->payment->status);
    }
}
