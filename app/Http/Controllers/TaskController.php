<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use App\Repositories\TaskRepository;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService,
        private TaskRepository $taskRepository
    ) {}

    public function index()
    {
        $tasks = $this->taskRepository->getAllForUser(auth()->id());
        $statuses = Task::statuses();
        return view('tasks.index', compact('tasks', 'statuses'));
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        $statuses = Task::statuses();
        return view('tasks.show', compact('task', 'statuses'));
    }

    public function create()
    {
        $statuses = Task::statuses();
        return view('tasks.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|max:200',
            'description' => 'nullable|max:5000',
            'status'      => 'required|in:' . implode(',', array_keys(Task::statuses())),
            'due_date'    => 'nullable|date|after_or_equal:today',
        ]);

        $task = $this->taskService->createTask(auth()->id(), $validated);

        return redirect()->route('tasks.show', $task)->with('success', 'タスクを作成しました');
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        $statuses = Task::statuses();
        return view('tasks.edit', compact('task', 'statuses'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title'       => 'required|max:200',
            'description' => 'nullable|max:5000',
            'status'      => 'required|in:' . implode(',', array_keys(Task::statuses())),
            'due_date'    => 'nullable|date',
        ]);

        $task = $this->taskService->updateTask($task, $validated);

        return redirect()->route('tasks.show', $task)->with('success', 'タスクを更新しました');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $this->taskService->deleteTask($task);
        return redirect()->route('tasks.index')->with('success', 'タスクを削除しました');
    }
}
