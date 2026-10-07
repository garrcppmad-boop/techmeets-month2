<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_login(): void
    {
        $this->get(route('tasks.index'))->assertRedirect(route('login'));
    }

    public function test_index_shows_only_own_tasks(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'title' => '自分のタスク']);
        Task::factory()->create(['user_id' => $other->id, 'title' => '他人のタスク']);
        $this->actingAs($user);

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('自分のタスク')
            ->assertDontSee('他人のタスク');
    }

    public function test_show_requires_login(): void
    {
        $task = Task::factory()->create();
        $this->get(route('tasks.show', $task))->assertRedirect(route('login'));
    }

    public function test_show_forbidden_for_non_owner(): void
    {
        $task = Task::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->get(route('tasks.show', $task))->assertForbidden();
    }

    public function test_show_is_visible_for_owner(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'title' => '自分のタスク詳細']);
        $this->actingAs($user);

        $this->get(route('tasks.show', $task))->assertOk()->assertSee('自分のタスク詳細');
    }

    public function test_create_requires_login(): void
    {
        $this->get(route('tasks.create'))->assertRedirect(route('login'));
    }

    public function test_store_creates_task_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('tasks.store'), [
            'title' => '新タスク', 'status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('tasks', ['title' => '新タスク', 'user_id' => $user->id]);
    }

    public function test_store_validation_fails_when_title_missing(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('tasks.store'), ['status' => 'pending'])
            ->assertSessionHasErrors('title');
    }

    public function test_store_validation_fails_for_invalid_status(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('tasks.store'), ['title' => 'タスク', 'status' => '不正な状態'])
            ->assertSessionHasErrors('status');
    }

    public function test_store_validation_fails_for_due_date_in_the_past(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('tasks.store'), [
            'title' => 'タスク', 'status' => 'pending', 'due_date' => now()->subDay()->format('Y-m-d'),
        ])->assertSessionHasErrors('due_date');
    }

    public function test_store_allows_due_date_of_today(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('tasks.store'), [
            'title' => 'タスク', 'status' => 'pending', 'due_date' => now()->format('Y-m-d'),
        ])->assertSessionDoesntHaveErrors('due_date');
    }

    public function test_edit_forbidden_for_non_owner(): void
    {
        $task = Task::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->get(route('tasks.edit', $task))->assertForbidden();
    }

    public function test_update_forbidden_for_non_owner(): void
    {
        $task = Task::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->put(route('tasks.update', $task), ['title' => '更新', 'status' => 'done'])
            ->assertForbidden();
    }

    public function test_update_changes_task_for_owner(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $this->actingAs($user);

        $this->put(route('tasks.update', $task), ['title' => $task->title, 'status' => 'done'])
            ->assertRedirect(route('tasks.show', $task));

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done']);
    }

    public function test_update_allows_past_due_date_unlike_store(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $this->put(route('tasks.update', $task), [
            'title' => $task->title, 'status' => 'pending', 'due_date' => now()->subWeek()->format('Y-m-d'),
        ])->assertSessionDoesntHaveErrors('due_date');
    }

    public function test_destroy_forbidden_for_non_owner(): void
    {
        $task = Task::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->delete(route('tasks.destroy', $task))->assertForbidden();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_destroy_removes_task_for_owner(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
