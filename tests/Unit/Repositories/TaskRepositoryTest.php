<?php

namespace Tests\Unit\Repositories;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TaskRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TaskRepository();
    }

    public function test_get_all_for_user_only_returns_that_users_tasks(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Task::factory()->count(2)->create(['user_id' => $user->id]);
        Task::factory()->create(['user_id' => $other->id]);

        $result = $this->repository->getAllForUser($user->id);

        $this->assertCount(2, $result);
    }

    public function test_get_all_for_user_orders_in_progress_before_pending_and_done(): void
    {
        $user = User::factory()->create();
        $done = Task::factory()->create(['user_id' => $user->id, 'status' => 'done', 'due_date' => '2026-01-01']);
        $pending = Task::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'due_date' => '2026-01-01']);
        $inProgress = Task::factory()->create(['user_id' => $user->id, 'status' => 'in_progress', 'due_date' => '2026-01-01']);

        $ids = $this->repository->getAllForUser($user->id)->pluck('id')->all();

        $this->assertSame([$inProgress->id, $pending->id, $done->id], $ids);
    }

    public function test_find_by_id_returns_matching_task(): void
    {
        $task = Task::factory()->create();

        $found = $this->repository->findById($task->id);

        $this->assertSame($task->id, $found->id);
    }

    public function test_find_by_id_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->repository->findById(9999);
    }

    public function test_create_sets_user_id_even_though_not_fillable(): void
    {
        $user = User::factory()->create();

        $task = $this->repository->create($user->id, [
            'title' => 'タスク',
            'status' => 'pending',
        ]);

        $this->assertSame($user->id, $task->user_id);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'user_id' => $user->id]);
    }

    public function test_update_changes_attributes(): void
    {
        $task = Task::factory()->create(['status' => 'pending']);

        $updated = $this->repository->update($task, ['status' => 'done']);

        $this->assertSame('done', $updated->status);
    }

    public function test_delete_removes_task(): void
    {
        $task = Task::factory()->create();

        $this->repository->delete($task);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
