<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\BusSeat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusSeat>
 */
class BusSeatFactory extends Factory
{
    protected $model = BusSeat::class;

    public function definition(): array
    {
        $row = fake()->numberBetween(1, 8);
        $col = fake()->randomElement(['A', 'B', 'C', 'D']);

        return [
            'bus_id' => Bus::factory(),
            'seat_number' => $row.$col,
            'row' => $row,
            'column' => $col,
            'status' => 'available',
        ];
    }
}
