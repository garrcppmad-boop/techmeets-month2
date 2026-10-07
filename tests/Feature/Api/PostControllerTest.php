<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_published_posts_as_json(): void
    {
        Post::factory()->count(2)->create();

        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_store_creates_post_without_authentication(): void
    {
        User::factory()->create();

        $response = $this->postJson('/api/posts', [
            'title' => 'APIタイトル', 'content' => '本文', 'category' => 'その他',
        ]);

        $response->assertCreated()->assertJsonPath('data.title', 'APIタイトル');
        $this->assertDatabaseHas('posts', ['title' => 'APIタイトル']);
    }

    public function test_store_validation_fails_when_fields_missing(): void
    {
        User::factory()->create();

        $this->postJson('/api/posts', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content', 'category']);
    }
}
