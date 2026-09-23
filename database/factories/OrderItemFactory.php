<?php

namespace Database\Factories;

use App\Models\BusSeat;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'bus_seat_id' => BusSeat::factory(),
            'passenger_name' => fake()->name(),
            'passenger_phone' => '08'.fake()->numerify('##########'),
            'passenger_id_number' => fake()->numerify('32##############'),
            'price' => 280000,
        ];
    }
}
