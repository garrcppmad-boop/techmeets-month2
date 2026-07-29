<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {}

    public function createTask(int $userId, array $data): Task
    {
        return DB::transaction(function () use ($userId, $data) {
            $task = $this->taskRepository->create($userId, $data);
            Log::info('Task created', ['task_id' => $task->id, 'user_id' => $userId]);
            return $task;
        });
    }

    public function updateTask(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $updated = $this->taskRepository->update($task, $data);
            Log::info('Task updated', ['task_id' => $updated->id]);
            return $updated;
        });
    }

    public function deleteTask(Task $task): void
    {
        DB::transaction(function () use ($task) {
            Log::info('Task deleted', ['task_id' => $task->id]);
            $this->taskRepository->delete($task);
        });
    }
}
