<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_is_visible_without_login(): void
    {
        Post::factory()->create(['title' => '公開記事タイトル']);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('公開記事タイトル');
    }

    public function test_show_is_visible_without_login(): void
    {
        $post = Post::factory()->create(['title' => '詳細タイトル']);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('詳細タイトル');
    }

    public function test_create_requires_login(): void
    {
        $this->get(route('posts.create'))->assertRedirect(route('login'));
    }

    public function test_create_is_visible_when_logged_in(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('posts.create'))->assertOk();
    }

    public function test_store_requires_login(): void
    {
        $this->post(route('posts.store'), [
            'title' => 'タイトル', 'content' => '本文', 'category' => 'その他',
        ])->assertRedirect(route('login'));
    }

    public function test_store_creates_post_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('posts.store'), [
            'title' => '新しい記事',
            'content' => '本文です',
            'category' => '技術',
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => '新しい記事',
            'user_id' => $user->id,
        ]);
        $post = Post::where('title', '新しい記事')->first();
        $response->assertRedirect(route('posts.show', $post));
    }

    public function test_store_validation_fails_when_fields_missing(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('posts.store'), [])
            ->assertSessionHasErrors(['title', 'content', 'category']);
    }

    public function test_store_validation_fails_for_invalid_category(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('posts.store'), [
            'title' => 'タイトル', 'content' => '本文', 'category' => '存在しないカテゴリ',
        ])->assertSessionHasErrors('category');
    }

    public function test_store_validation_fails_when_title_exceeds_max_length(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('posts.store'), [
            'title' => str_repeat('あ', 201), 'content' => '本文', 'category' => 'その他',
        ])->assertSessionHasErrors('title');
    }

    public function test_edit_requires_login(): void
    {
        $post = Post::factory()->create();
        $this->get(route('posts.edit', $post))->assertRedirect(route('login'));
    }

    public function test_edit_forbidden_for_non_owner(): void
    {
        $post = Post::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->get(route('posts.edit', $post))->assertForbidden();
    }

    public function test_edit_is_visible_for_owner(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $this->get(route('posts.edit', $post))->assertOk();
    }

    public function test_update_forbidden_for_non_owner(): void
    {
        $post = Post::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->put(route('posts.update', $post), [
            'title' => '更新後', 'content' => '本文', 'category' => 'その他',
        ])->assertForbidden();
    }

    public function test_update_changes_post_for_owner(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id, 'title' => '元タイトル']);
        $this->actingAs($user);

        $this->put(route('posts.update', $post), [
            'title' => '更新後タイトル', 'content' => '本文', 'category' => 'その他',
        ])->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => '更新後タイトル']);
    }

    public function test_update_validation_fails_for_invalid_category(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $this->put(route('posts.update', $post), [
            'title' => 'タイトル', 'content' => '本文', 'category' => '不正',
        ])->assertSessionHasErrors('category');
    }

    public function test_destroy_forbidden_for_non_owner(): void
    {
        $post = Post::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->delete(route('posts.destroy', $post))->assertForbidden();
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_destroy_removes_post_for_owner(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $this->delete(route('posts.destroy', $post))->assertRedirect(route('posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
