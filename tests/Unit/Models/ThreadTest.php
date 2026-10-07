<?php

namespace Tests\Unit\Models;

use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ThreadTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_owned_by_returns_true_for_matching_user(): void
    {
        $user = User::factory()->create();
        $thread = Thread::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($thread->isOwnedBy($user->id));
    }

    public function test_is_owned_by_returns_false_for_other_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $thread = Thread::factory()->create(['user_id' => $owner->id]);
        $this->assertFalse($thread->isOwnedBy($other->id));
    }

    public function test_replies_relation_returns_oldest_first(): void
    {
        $thread = Thread::factory()->create();
        $second = Reply::factory()->create(['thread_id' => $thread->id, 'created_at' => now()->addMinute()]);
        $first = Reply::factory()->create(['thread_id' => $thread->id, 'created_at' => now()]);

        $ids = $thread->replies()->get()->pluck('id')->all();

        $this->assertSame([$first->id, $second->id], $ids);
    }
}
