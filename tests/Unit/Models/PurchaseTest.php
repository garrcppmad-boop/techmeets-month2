<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $purchase = Purchase::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($purchase->user->is($user));
    }

    public function test_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $purchase = Purchase::factory()->create(['product_id' => $product->id]);

        $this->assertTrue($purchase->product->is($product));
    }

    public function test_amount_is_cast_to_integer(): void
    {
        $purchase = Purchase::factory()->create(['amount' => '1500']);

        $this->assertSame(1500, $purchase->amount);
    }
}
