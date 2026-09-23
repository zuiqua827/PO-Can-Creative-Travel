<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition(): array
    {
        $dep = now()->addDays(fake()->numberBetween(1, 10))->setTime(fake()->numberBetween(6, 21), fake()->randomElement([0, 30]));
        $arr = (clone $dep)->addHours(8);

        return [
            'bus_id' => Bus::factory(),
            'route_id' => Route::factory(),
            'trip_code' => 'TRIP-'.strtoupper(Str::random(8)),
            'departure_at' => $dep,
            'arrival_at' => $arr,
            'price' => fake()->randomElement([250000, 280000, 320000, 380000]),
            'boarding_point' => 'Pool Utama',
            'drop_off_point' => 'Terminal Akhir',
            'status' => 'scheduled',
        ];
    }
}
