<?php

namespace Tests\Feature;

use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReplyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_requires_login(): void
    {
        $thread = Thread::factory()->create();

        $this->post(route('threads.replies.store', $thread), ['body' => 'レス本文'])
            ->assertRedirect(route('login'));
    }

    public function test_store_creates_reply_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $thread = Thread::factory()->create();
        $this->actingAs($user);

        $this->post(route('threads.replies.store', $thread), ['body' => 'レス本文です'])
            ->assertRedirect(route('threads.show', $thread));

        $this->assertDatabaseHas('replies', [
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'body' => 'レス本文です',
        ]);
    }

    public function test_store_validation_fails_when_body_missing(): void
    {
        $thread = Thread::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->post(route('threads.replies.store', $thread), [])
            ->assertSessionHasErrors('body');
    }

    public function test_store_validation_fails_when_body_exceeds_max_length(): void
    {
        $thread = Thread::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->post(route('threads.replies.store', $thread), ['body' => str_repeat('あ', 2001)])
            ->assertSessionHasErrors('body');
    }

    public function test_destroy_requires_login(): void
    {
        $thread = Thread::factory()->create();
        $reply = Reply::factory()->create(['thread_id' => $thread->id]);

        $this->delete(route('threads.replies.destroy', [$thread, $reply]))
            ->assertRedirect(route('login'));
    }

    public function test_destroy_forbidden_for_non_owner(): void
    {
        $thread = Thread::factory()->create();
        $reply = Reply::factory()->create(['thread_id' => $thread->id]);
        $this->actingAs(User::factory()->create());

        $this->delete(route('threads.replies.destroy', [$thread, $reply]))->assertForbidden();
        $this->assertDatabaseHas('replies', ['id' => $reply->id]);
    }

    public function test_destroy_removes_reply_for_owner(): void
    {
        $user = User::factory()->create();
        $thread = Thread::factory()->create();
        $reply = Reply::factory()->create(['thread_id' => $thread->id, 'user_id' => $user->id]);
        $this->actingAs($user);

        $this->delete(route('threads.replies.destroy', [$thread, $reply]))
            ->assertRedirect(route('threads.show', $thread));

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
    }
}
