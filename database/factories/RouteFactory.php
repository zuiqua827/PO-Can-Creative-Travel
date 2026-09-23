<?php

namespace Database\Factories;

use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Route>
 */
class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition(): array
    {
        $origins = ['Jakarta (Pulo Gebang)', 'Bandung (Leuwipanjang)', 'Semarang (Terboyo)'];
        $dests = ['Yogyakarta (Giwangan)', 'Surabaya (Bungurasih)', 'Solo (Tirtonadi)'];

        return [
            'origin' => fake()->randomElement($origins),
            'destination' => fake()->randomElement($dests),
            'distance' => fake()->numberBetween(350, 800).' km',
            'estimated_duration' => fake()->numberBetween(6, 11).' Jam',
            'base_price' => fake()->randomElement([220000, 260000, 300000, 350000]),
            'status' => 'active',
        ];
    }
}
