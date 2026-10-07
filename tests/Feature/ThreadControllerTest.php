<?php

namespace Tests\Feature;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThreadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_is_visible_without_login(): void
    {
        Thread::factory()->create(['title' => '公開スレッド']);

        $this->get(route('threads.index'))
            ->assertOk()
            ->assertSee('公開スレッド');
    }

    public function test_show_is_visible_without_login(): void
    {
        $thread = Thread::factory()->create(['title' => 'スレッド詳細']);

        $this->get(route('threads.show', $thread))
            ->assertOk()
            ->assertSee('スレッド詳細');
    }

    public function test_create_requires_login(): void
    {
        $this->get(route('threads.create'))->assertRedirect(route('login'));
    }

    public function test_create_is_visible_when_logged_in(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('threads.create'))->assertOk();
    }

    public function test_store_requires_login(): void
    {
        $this->post(route('threads.store'), ['title' => 'タイトル', 'body' => '本文'])
            ->assertRedirect(route('login'));
    }

    public function test_store_creates_thread_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('threads.store'), ['title' => '新規スレッド', 'body' => '本文です']);

        $this->assertDatabaseHas('threads', ['title' => '新規スレッド', 'user_id' => $user->id]);
    }

    public function test_store_validation_fails_when_fields_missing(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('threads.store'), [])
            ->assertSessionHasErrors(['title', 'body']);
    }

    public function test_store_validation_fails_when_body_exceeds_max_length(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('threads.store'), [
            'title' => 'タイトル', 'body' => str_repeat('あ', 2001),
        ])->assertSessionHasErrors('body');
    }

    public function test_destroy_requires_login(): void
    {
        $thread = Thread::factory()->create();
        $this->delete(route('threads.destroy', $thread))->assertRedirect(route('login'));
    }

    public function test_destroy_forbidden_for_non_owner(): void
    {
        $thread = Thread::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->delete(route('threads.destroy', $thread))->assertForbidden();
        $this->assertDatabaseHas('threads', ['id' => $thread->id]);
    }

    public function test_destroy_removes_thread_for_owner(): void
    {
        $user = User::factory()->create();
        $thread = Thread::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $this->delete(route('threads.destroy', $thread))->assertRedirect(route('threads.index'));
        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
    }
}
