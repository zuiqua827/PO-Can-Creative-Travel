<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Sprint14EnhancementTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 1. Fleet showcase section contains bus image backgrounds, overlay, and schedule search links.
     */
    public function test_fleet_showcase_section_renders_bus_photos_and_links(): void
    {
        // Ensure the 4 representative bus types exist
        $types = ['Royal Suite', 'Executive', 'Sleeper Bus', 'VIP'];
        foreach ($types as $type) {
            Bus::firstOrCreate(
                ['type' => $type],
                [
                    'name' => "CAN {$type} Unit",
                    'code' => "CAN-TEST-{$type}",
                    'seat_capacity' => 24,
                    'facilities' => ['AC', 'WiFi', 'Reclining Seat'],
                    'description' => "Test description for {$type}",
                    'status' => 'active',
                ]
            );
        }

        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // Section heading
        $this->assertStringContainsString('Armada Bus Modern & Mewah', $content);

        // Check image asset references exist for the bus types
        $this->assertStringContainsString('images/buses/royal-suite.jpg', $content);
        $this->assertStringContainsString('images/buses/executive-grand.jpg', $content);
        $this->assertStringContainsString('images/buses/sleeper-dream.jpg', $content);
        $this->assertStringContainsString('images/buses/vip-line.jpg', $content);

        // Check that each bus card has the CTA link with bus_type parameter
        foreach ($types as $type) {
            $expectedQuery = 'bus_type='.rawurlencode($type);
            $this->assertStringContainsString($expectedQuery, $content);
        }

        // Check dark gradient overlay class exists
        $this->assertStringContainsString('bg-gradient-to-t from-navy-950', $content);
    }

    /**
     * 2. FAQ section has all 8 required questions, semantic buttons, aria attributes, and plus/minus icons.
     */
    public function test_faq_section_has_8_interactive_accordion_items(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $content = $response->getContent();

        $requiredQuestions = [
            'Bagaimana cara memesan tiket bus di CAN Travel?',
            'Apakah saya wajib mencetak tiket fisik di terminal?',
            'Metode pembayaran apa saja yang didukung?',
            'Bagaimana cara memilih kursi?',
            'Berapa lama batas waktu pembayaran?',
            'Bagaimana jika pembayaran saya gagal?',
            'Bagaimana cara melihat tiket yang sudah dibeli?',
            'Apakah tiket dapat dibatalkan atau diubah?',
        ];

        foreach ($requiredQuestions as $question) {
            $this->assertStringContainsString($question, $content);
        }

        // Verify accessibility attributes
        $this->assertStringContainsString('aria-expanded', $content);
        $this->assertStringContainsString('aria-controls="faq-answer-1"', $content);
        $this->assertStringContainsString('id="faq-btn-1"', $content);
        $this->assertStringContainsString('role="region"', $content);

        // Verify single-open accordion container and animation classes
        $this->assertStringContainsString('id="faq-accordion"', $content);
        $this->assertStringContainsString('faq-content', $content);
    }

    /**
     * 3. Partner & Pembayaran section is located under CTA and renders partner logos.
     */
    public function test_partner_and_kolaborasi_section_uses_generic_partner_labels(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // Heading & Label
        $this->assertStringContainsString('PARTNER & PEMBAYARAN', $content);
        $this->assertStringContainsString('Partner yang Mendukung Perjalanan Anda', $content);

        // Partner logos
        $this->assertStringContainsString('images/bni.png', $content);
        $this->assertStringContainsString('images/bri.png', $content);
        $this->assertStringContainsString('images/bca.png', $content);
        $this->assertStringContainsString('images/dana.png', $content);
        $this->assertStringContainsString('images/ovo.png', $content);
        $this->assertStringContainsString('images/gopay.png', $content);
        $this->assertStringContainsString('images/shopeepay.png', $content);

        // Ensure CTA appears before Partner section
        $ctaPos = strpos($content, 'Siap Melakukan Perjalanan Nyaman?');
        $partnerPos = strpos($content, 'Partner yang Mendukung Perjalanan Anda');

        $this->assertNotFalse($ctaPos);
        $this->assertNotFalse($partnerPos);
        $this->assertGreaterThan($ctaPos, $partnerPos, 'Partner & Pembayaran section should be positioned after the CTA section.');
    }

    /**
     * 4. Filter schedule by bus_type works as linked by the armada cards.
     */
    public function test_schedule_filter_by_bus_type_functions_correctly(): void
    {
        $busExecutive = Bus::factory()->create(['type' => 'Executive', 'status' => 'active']);
        $busSleeper = Bus::factory()->create(['type' => 'Sleeper Bus', 'status' => 'active']);

        $route = Route::factory()->create([
            'origin' => 'UniqueOriginXYZ',
            'destination' => 'UniqueDestXYZ',
            'status' => 'active',
        ]);

        $tripExecutive = Trip::factory()->create([
            'bus_id' => $busExecutive->id,
            'route_id' => $route->id,
            'status' => 'scheduled',
            'departure_at' => now()->addDays(2),
        ]);

        $tripSleeper = Trip::factory()->create([
            'bus_id' => $busSleeper->id,
            'route_id' => $route->id,
            'status' => 'scheduled',
            'departure_at' => now()->addDays(2),
        ]);

        $response = $this->get(route('trips.index', [
            'origin' => $route->origin,
            'bus_type' => 'Executive',
        ]));
        $response->assertStatus(200);
        $response->assertSee($tripExecutive->trip_code);
        $response->assertDontSee($tripSleeper->trip_code);
    }

    /**
     * 5. Booking flow regression check: customer can view trip, reserve seat, and create booking.
     */
    public function test_booking_flow_remains_fully_functional(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $bus = Bus::factory()->create(['type' => 'Royal Suite', 'status' => 'active', 'seat_capacity' => 10]);
        $bus->generateSeats(10);

        $route = Route::factory()->create(['status' => 'active']);
        $trip = Trip::factory()->create([
            'bus_id' => $bus->id,
            'route_id' => $route->id,
            'status' => 'scheduled',
            'price' => 250000,
            'departure_at' => now()->addDays(2),
        ]);

        $seat = $bus->busSeats()->first();

        // 1. Visit trip detail
        $response = $this->actingAs($customer)->get(route('trips.show', $trip));
        $response->assertStatus(200);
        $response->assertSee($seat->seat_number);

        // 2. Visit checkout with valid seat_ids parameter
        $response = $this->actingAs($customer)->get(route('booking.checkout', [
            'trip' => $trip,
            'seat_ids' => [$seat->id],
        ]));
        $response->assertStatus(200);
        $response->assertSee('Konfirmasi Pesanan');

        // 3. Submit booking
        $response = $this->actingAs($customer)->post(route('booking.store', $trip), [
            'seats' => [$seat->id],
            'passengers' => [
                $seat->id => [
                    'name' => 'Budi Santoso',
                    'id_card_number' => '3201012345678901',
                    'phone' => '081234567890',
                ],
            ],
            'payment_method' => 'BCA Virtual Account',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'trip_id' => $trip->id,
            'total_amount' => 250000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }
}
