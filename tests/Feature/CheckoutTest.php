<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        return Product::create([
            'name'     => 'テスト商品',
            'price'    => 1500,
            'stock'    => 10,
            'category' => 'その他',
        ]);
    }

    public function test_未ログインでは購入一覧にアクセスできない(): void
    {
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
    }

    public function test_未ログインで購入すると弾かれ_セッションは作られない(): void
    {
        $this->post(route('checkout', $this->product()))->assertRedirect(route('login'));
    }

    public function test_ログイン済みなら購入一覧が表示される(): void
    {
        $this->actingAs(User::factory()->create());
        $this->product();

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('テスト商品')
            ->assertSee('購入する');
    }

    public function test_成功_キャンセル画面が表示される(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('checkout.success'))->assertOk()->assertSee('ご購入ありがとうございます');
        $this->get(route('checkout.cancel'))->assertOk()->assertSee('キャンセル');
    }
}
