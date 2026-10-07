<?php

namespace Tests\Unit\Services;

use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use App\Services\PostService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PostService(new PostRepository());
    }

    public function test_create_post_persists_and_returns_post(): void
    {
        $user = User::factory()->create();

        $post = $this->service->createPost([
            'title' => 'タイトル',
            'content' => '本文',
            'category' => 'その他',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'user_id' => $user->id]);
    }

    public function test_update_post_persists_changes(): void
    {
        $post = Post::factory()->create(['title' => '元タイトル']);

        $updated = $this->service->updatePost($post, ['title' => '新タイトル']);

        $this->assertSame('新タイトル', $updated->title);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => '新タイトル']);
    }

    public function test_delete_post_removes_from_database(): void
    {
        $post = Post::factory()->create();

        $this->service->deletePost($post);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
