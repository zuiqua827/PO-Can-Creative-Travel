<?php

namespace Database\Factories;

use App\Models\Bus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bus>
 */
class BusFactory extends Factory
{
    protected $model = Bus::class;

    public function definition(): array
    {
        $code = 'CAN-'.strtoupper(fake()->bothify('??-##'));

        return [
            'name' => 'CAN '.fake()->randomElement(['Royal Suite', 'Executive Grand', 'Sleeper Dream', 'VIP Line']).' '.fake()->numberBetween(10, 99),
            'code' => $code,
            'type' => fake()->randomElement(['Executive', 'Royal Suite', 'Sleeper Bus', 'VIP']),
            'seat_capacity' => 28,
            'facilities' => ['AC', 'WiFi', 'Reclining Seat', 'Toilet'],
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
