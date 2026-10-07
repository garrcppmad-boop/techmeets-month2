<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_is_visible_without_login(): void
    {
        Product::factory()->create(['name' => '公開商品']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('公開商品');
    }

    public function test_show_is_visible_without_login(): void
    {
        $product = Product::factory()->create(['name' => '商品詳細']);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('商品詳細');
    }

    public function test_create_requires_login(): void
    {
        $this->get(route('products.create'))->assertRedirect(route('login'));
    }

    public function test_store_requires_login(): void
    {
        $this->post(route('products.store'), [
            'name' => '商品', 'price' => 1000, 'stock' => 1, 'category' => 'その他',
        ])->assertRedirect(route('login'));
    }

    public function test_store_creates_product_for_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('products.store'), [
            'name' => '新商品', 'price' => 1000, 'stock' => 5, 'category' => '食品',
        ]);

        $this->assertDatabaseHas('products', ['name' => '新商品', 'price' => 1000]);
    }

    public function test_store_validation_fails_when_fields_missing(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('products.store'), [])
            ->assertSessionHasErrors(['name', 'price', 'stock', 'category']);
    }

    public function test_store_validation_fails_for_negative_price(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('products.store'), [
            'name' => '商品', 'price' => -100, 'stock' => 1, 'category' => 'その他',
        ])->assertSessionHasErrors('price');
    }

    public function test_store_validation_fails_for_invalid_category(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('products.store'), [
            'name' => '商品', 'price' => 100, 'stock' => 1, 'category' => '不正',
        ])->assertSessionHasErrors('category');
    }

    public function test_store_uploads_image_to_s3_disk(): void
    {
        Storage::fake('s3');
        $this->actingAs(User::factory()->create());

        $this->post(route('products.store'), [
            'name' => '画像商品', 'price' => 100, 'stock' => 1, 'category' => 'その他',
            'image' => UploadedFile::fake()->image('photo.png'),
        ]);

        $product = Product::where('name', '画像商品')->first();
        $this->assertNotNull($product->image_path);
        Storage::disk('s3')->assertExists($product->image_path);
    }

    public function test_edit_requires_login(): void
    {
        $product = Product::factory()->create();
        $this->get(route('products.edit', $product))->assertRedirect(route('login'));
    }

    public function test_update_changes_product(): void
    {
        $product = Product::factory()->create(['name' => '元の名前']);
        $this->actingAs(User::factory()->create());

        $this->put(route('products.update', $product), [
            'name' => '新しい名前', 'price' => $product->price, 'stock' => $product->stock, 'category' => $product->category,
        ])->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => '新しい名前']);
    }

    public function test_update_replaces_image_and_deletes_old_one(): void
    {
        Storage::fake('s3');
        $oldPath = 'products/old.jpg';
        Storage::disk('s3')->put($oldPath, 'dummy');
        $product = Product::factory()->create(['image_path' => $oldPath]);
        $this->actingAs(User::factory()->create());

        $this->put(route('products.update', $product), [
            'name' => $product->name, 'price' => $product->price, 'stock' => $product->stock, 'category' => $product->category,
            'image' => UploadedFile::fake()->image('new.png'),
        ]);

        Storage::disk('s3')->assertMissing($oldPath);
        $product->refresh();
        Storage::disk('s3')->assertExists($product->image_path);
    }

    public function test_destroy_removes_product_and_its_image(): void
    {
        Storage::fake('s3');
        $path = 'products/to-delete.jpg';
        Storage::disk('s3')->put($path, 'dummy');
        $product = Product::factory()->create(['image_path' => $path]);
        $this->actingAs(User::factory()->create());

        $this->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('s3')->assertMissing($path);
    }
}
