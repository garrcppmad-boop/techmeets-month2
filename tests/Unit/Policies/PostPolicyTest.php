<?php

namespace Tests\Unit\Policies;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PostPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PostPolicy();
    }

    public function test_owner_can_update(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->update($user, $post));
    }

    public function test_non_owner_cannot_update(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->update($other, $post));
    }

    public function test_owner_can_delete(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->delete($user, $post));
    }

    public function test_non_owner_cannot_delete(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->delete($other, $post));
    }
}
