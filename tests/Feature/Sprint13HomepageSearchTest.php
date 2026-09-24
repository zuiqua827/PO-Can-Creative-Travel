<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Sprint13HomepageSearchTest extends TestCase
{
    use DatabaseTransactions;

    protected $route1;

    protected $route2;

    protected $bus;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard testing data
        $this->bus = Bus::factory()->create([
            'status' => 'active',
            'type' => 'Executive',
        ]);

        $this->route1 = Route::factory()->create([
            'origin' => 'TestCityA',
            'destination' => 'TestCityB',
            'status' => 'active',
        ]);

        $this->route2 = Route::factory()->create([
            'origin' => 'TestCityC',
            'destination' => 'TestCityD',
            'status' => 'active',
        ]);
    }

    /**
     * TEST 1: Homepage search form validation and structure
     * The form should have action to trips.index, method GET, and the correct inputs.
     */
    public function test_homepage_form_points_to_correct_schedule_search_route(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $content = $response->getContent();

        // Should point to trips.index via GET
        $this->assertStringContainsString('action="'.route('trips.index').'"', $content);
        $this->assertStringContainsString('method="GET"', $content);

        // Should contain input fields for origin, destination, and date
        $this->assertStringContainsString('name="origin"', $content);
        $this->assertStringContainsString('name="destination"', $content);
        $this->assertStringContainsString('name="date"', $content);

        // Date input should not force today's date by default (should be empty value="")
        // But the exact check depends on HTML formatting, we can just ensure it doesn't force a value
        $this->assertStringContainsString('name="date" value=""', $content);
    }

    /**
     * TEST 2 & 3: Schedule page accepts origin filter and returns correct trips
     */
    public function test_schedule_page_filters_by_origin_correctly(): void
    {
        // Trip 1 from Jakarta
        Trip::factory()->create([
            'route_id' => $this->route1->id,
            'bus_id' => $this->bus->id,
            'departure_at' => now()->addDays(2),
            'status' => 'scheduled',
        ]);

        // Trip 2 from Bandung
        Trip::factory()->create([
            'route_id' => $this->route2->id,
            'bus_id' => $this->bus->id,
            'departure_at' => now()->addDays(2),
            'status' => 'scheduled',
        ]);

        $response = $this->get(route('trips.index', ['origin' => 'Jakarta']));

        $response->assertStatus(200);

        // Should show trips from Jakarta
        $response->assertSee('TestCityA');
        // Should not show trips from Bandung
        $response->assertDontSee('TestCityC &rarr; TestCityD', false);
    }

    /**
     * TEST 4: Destination match -> trip muncul
     */
    public function test_schedule_page_filters_by_destination_correctly(): void
    {
        Trip::factory()->create([
            'route_id' => $this->route1->id,
            'bus_id' => $this->bus->id,
            'departure_at' => now()->addDays(2),
            'status' => 'scheduled',
        ]);

        Trip::factory()->create([
            'route_id' => $this->route2->id,
            'bus_id' => $this->bus->id,
            'departure_at' => now()->addDays(2),
            'status' => 'scheduled',
        ]);

        $response = $this->get(route('trips.index', ['destination' => 'Surabaya']));

        $response->assertStatus(200);

        $response->assertSee('TestCityC');
        $response->assertDontSee('TestCityA &rarr; TestCityB', false);
    }

    /**
     * TEST 5: Date match -> trip muncul
     */
    public function test_schedule_page_filters_by_date_correctly(): void
    {
        $targetDate = Carbon::create(2027, 5, 15, 12, 0, 0);
        $otherDate = Carbon::create(2027, 6, 20, 12, 0, 0);

        Trip::factory()->create([
            'route_id' => $this->route1->id,
            'bus_id' => $this->bus->id,
            'departure_at' => $targetDate,
            'status' => 'scheduled',
        ]);

        Trip::factory()->create([
            'route_id' => $this->route2->id,
            'bus_id' => $this->bus->id,
            'departure_at' => $otherDate,
            'status' => 'scheduled',
        ]);

        $response = $this->get(route('trips.index', ['date' => $targetDate->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee($targetDate->translatedFormat('d M Y'));
        $response->assertDontSee($otherDate->translatedFormat('d M Y'));
    }

    /**
     * TEST 6: Origin + destination + date combined filter
     */
    public function test_schedule_page_filters_by_combined_parameters(): void
    {
        $targetDate = Carbon::create(2027, 7, 22, 12, 0, 0);

        Trip::factory()->create([
            'route_id' => $this->route1->id, // Jakarta -> Yogyakarta
            'bus_id' => $this->bus->id,
            'departure_at' => $targetDate,
            'status' => 'scheduled',
        ]);

        Trip::factory()->create([
            'route_id' => $this->route1->id, // Jakarta -> Yogyakarta
            'bus_id' => $this->bus->id,
            'departure_at' => Carbon::create(2027, 7, 25, 12, 0, 0),
            'status' => 'scheduled',
        ]);

        Trip::factory()->create([
            'route_id' => $this->route2->id, // Bandung -> Surabaya
            'bus_id' => $this->bus->id,
            'departure_at' => $targetDate,
            'status' => 'scheduled',
        ]);

        $response = $this->get(route('trips.index', [
            'origin' => 'TestCityA',
            'destination' => 'TestCityB',
            'date' => $targetDate->format('Y-m-d'),
        ]));

        $response->assertStatus(200);

        $response->assertSee('TestCityA');
        $response->assertSee('TestCityB');
        $response->assertSee($targetDate->translatedFormat('d M Y'));

        // Assert we see "1 jadwal"
        $this->assertStringContainsString('Menampilkan', $response->getContent());
        $this->assertStringContainsString('<strong class="text-slate-900 font-bold">1</strong>', $response->getContent());
    }

    /**
     * TEST 7: No trip found -> empty state with custom message
     */
    public function test_empty_state_shows_correct_filtered_message(): void
    {
        $targetDate = '2099-01-01';
        $response = $this->get(route('trips.index', [
            'origin' => 'TestCityA',
            'destination' => 'TestCityB',
            'date' => $targetDate,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Jadwal perjalanan tidak ditemukan');
        $response->assertSee('TestCityA &rarr; TestCityB', false);
        $response->assertSee(Carbon::parse($targetDate)->translatedFormat('d F Y'));
        $response->assertSee('Reset Filter');
    }

    /**
     * TEST 8: Reset filter URL returns default schedule
     */
    public function test_reset_filter_button_points_to_clean_index(): void
    {
        $response = $this->get(route('trips.index', [
            'origin' => 'TestCityA',
            'destination' => 'TestCityB',
        ]));

        $response->assertStatus(200);
        // Reset filter link should be exact trips.index route without query strings
        $this->assertStringContainsString('href="'.route('trips.index').'"', $response->getContent());
    }

    /**
     * TEST 12: Invalid origin or destination handles gracefully (no 500 error)
     */
    public function test_invalid_parameters_do_not_cause_500_error(): void
    {
        $response = $this->get(route('trips.index', [
            'origin' => 'InvalidCity12345',
            'destination' => 'Nowhere999',
            'date' => '2099-13-45', // invalid date might be ignored or handled by carbon safely or ignored by DB
        ]));

        $response->assertStatus(200); // Should gracefully return empty state, not 500
        $response->assertSee('Jadwal perjalanan tidak ditemukan');
    }
}
