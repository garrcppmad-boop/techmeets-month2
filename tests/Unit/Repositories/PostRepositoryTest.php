<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PostRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PostRepository();
    }

    public function test_get_published_returns_paginated_posts_with_user_loaded(): void
    {
        Post::factory()->count(3)->create();

        $result = $this->repository->getPublished();

        $this->assertCount(3, $result);
        $this->assertTrue($result->first()->relationLoaded('user'));
    }

    public function test_get_published_uses_cache_on_second_call(): void
    {
        Post::factory()->count(3)->create();
        $this->repository->getPublished(); // 1回目: DB から取得してキャッシュ

        DB::enableQueryLog();
        $result = $this->repository->getPublished(); // 2回目: キャッシュから取得

        $this->assertCount(3, $result);
        $this->assertCount(0, DB::getQueryLog(), '2回目は SQL を発行しない');
    }

    public function test_cache_is_refreshed_when_post_is_created(): void
    {
        Post::factory()->count(3)->create();
        $this->repository->getPublished();

        Post::factory()->create(['title' => '新しい投稿']);

        $result = $this->repository->getPublished();
        $this->assertCount(4, $result);
        $this->assertSame('新しい投稿', $result->first()->title);
    }

    public function test_cache_is_refreshed_when_post_is_deleted(): void
    {
        $posts = Post::factory()->count(3)->create();
        $this->repository->getPublished();

        $posts->first()->delete();

        $this->assertCount(2, $this->repository->getPublished());
    }

    public function test_cache_is_stored_per_page(): void
    {
        Post::factory()->count(15)->create();

        Paginator::currentPageResolver(fn() => 1);
        $page1 = $this->repository->getPublished();
        Paginator::currentPageResolver(fn() => 2);
        $page2 = $this->repository->getPublished();

        $this->assertCount(10, $page1);
        $this->assertCount(5, $page2);
    }

    public function test_find_by_id_returns_matching_post(): void
    {
        $post = Post::factory()->create();

        $found = $this->repository->findById($post->id);

        $this->assertSame($post->id, $found->id);
    }

    public function test_find_by_id_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->repository->findById(9999);
    }

    public function test_create_sets_user_id_even_though_not_fillable(): void
    {
        $user = User::factory()->create();

        $post = $this->repository->create([
            'title' => 'テストタイトル',
            'content' => '本文',
            'category' => 'その他',
            'user_id' => $user->id,
        ]);

        $this->assertSame($user->id, $post->user_id);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'user_id' => $user->id]);
    }

    public function test_update_changes_attributes(): void
    {
        $post = Post::factory()->create(['title' => '元タイトル']);

        $updated = $this->repository->update($post, ['title' => '新タイトル']);

        $this->assertSame('新タイトル', $updated->title);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => '新タイトル']);
    }

    public function test_delete_removes_post(): void
    {
        $post = Post::factory()->create();

        $this->repository->delete($post);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
