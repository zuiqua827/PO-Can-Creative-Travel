<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'trip_id' => Trip::factory(),
            'order_code' => 'PCT-'.date('Ymd').'-'.strtoupper(Str::random(5)),
            'total_amount' => 280000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->addHours(2),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'expires_at' => null,
        ]);
    }
}
