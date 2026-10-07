<?php

namespace Tests\Unit\Models;

use App\Models\Reply;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_owned_by_returns_true_for_matching_user(): void
    {
        $user = User::factory()->create();
        $reply = Reply::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($reply->isOwnedBy($user->id));
    }

    public function test_is_owned_by_returns_false_for_other_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $reply = Reply::factory()->create(['user_id' => $owner->id]);
        $this->assertFalse($reply->isOwnedBy($other->id));
    }

    public function test_belongs_to_thread(): void
    {
        $thread = \App\Models\Thread::factory()->create();
        $reply = Reply::factory()->create(['thread_id' => $thread->id]);

        $this->assertTrue($reply->thread->is($thread));
    }
}
