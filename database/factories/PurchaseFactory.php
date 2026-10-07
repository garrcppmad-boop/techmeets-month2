<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'stripe_payment_intent_id' => 'pi_'.fake()->unique()->bothify('##########????????'),
            'amount' => fake()->numberBetween(100, 50000),
            'currency' => 'jpy',
            'status' => 'succeeded',
            'customer_email' => fake()->safeEmail(),
        ];
    }
}
