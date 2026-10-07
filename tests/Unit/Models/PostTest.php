<?php

namespace Tests\Unit\Models;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_returns_fixed_list(): void
    {
        $this->assertSame(['技術', 'ライフスタイル', '学習', 'その他'], Post::categories());
    }

    public function test_is_owned_by_returns_true_for_matching_user(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($post->isOwnedBy($user->id));
    }

    public function test_is_owned_by_returns_false_for_other_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);
        $this->assertFalse($post->isOwnedBy($other->id));
    }
}
