<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_array_contains_expected_fields_without_loaded_user(): void
    {
        $post = Post::factory()->create();

        $array = (new PostResource($post))->toArray(Request::create('/'));

        $this->assertSame($post->id, $array['id']);
        $this->assertSame($post->title, $array['title']);
        $this->assertSame($post->content, $array['content']);
        $this->assertSame($post->category, $array['category']);
        $this->assertInstanceOf(\Illuminate\Http\Resources\MissingValue::class, $array['author']);
    }

    public function test_to_array_includes_author_when_user_loaded(): void
    {
        $user = User::factory()->create(['name' => '山田太郎']);
        $post = Post::factory()->create(['user_id' => $user->id])->load('user');

        $array = (new PostResource($post))->toArray(Request::create('/'));

        $this->assertSame('山田太郎', $array['author']);
    }
}
