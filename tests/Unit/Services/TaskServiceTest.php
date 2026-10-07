<?php

namespace Tests\Unit\Services;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use App\Services\TaskService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskService(new TaskRepository());
    }

    public function test_create_task_persists_and_returns_task(): void
    {
        $user = User::factory()->create();

        $task = $this->service->createTask($user->id, [
            'title' => 'タスク',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'user_id' => $user->id]);
    }

    public function test_update_task_persists_changes(): void
    {
        $task = Task::factory()->create(['status' => 'pending']);

        $updated = $this->service->updateTask($task, ['status' => 'done']);

        $this->assertSame('done', $updated->status);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done']);
    }

    public function test_delete_task_removes_from_database(): void
    {
        $task = Task::factory()->create();

        $this->service->deleteTask($task);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
