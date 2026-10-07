<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function getAllForUser(int $userId): LengthAwarePaginator
    {
        return Task::where('user_id', $userId)
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 0 WHEN 'pending' THEN 1 WHEN 'done' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->paginate(15);
    }

    public function findById(int $id): Task
    {
        return Task::findOrFail($id);
    }

    public function create(int $userId, array $data): Task
    {
        $task = new Task($data);
        $task->user_id = $userId;
        $task->save();
        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
