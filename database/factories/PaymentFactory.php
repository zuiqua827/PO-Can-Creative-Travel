<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'payment_method' => 'BCA Virtual Account',
            'payment_reference' => 'PAY-'.strtoupper(Str::random(10)),
            'amount' => 280000,
            'status' => 'pending',
            'paid_at' => null,
            'proof_file' => null,
        ];
    }

    public function success(): static
    {
        return $this->state(fn () => [
            'status' => 'success',
            'paid_at' => now(),
        ]);
    }
}
