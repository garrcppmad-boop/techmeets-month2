<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">タスク管理</h2>
            <a href="{{ route('tasks.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                + タスク追加
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @forelse ($tasks as $task)
                @php
                    $badgeClass = match($task->status) {
                        'in_progress' => 'bg-yellow-100 text-yellow-800',
                        'done'        => 'bg-green-100 text-green-800',
                        default       => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <div class="bg-white shadow-sm rounded-lg p-5 flex items-start justify-between gap-4
                            {{ $task->status === 'done' ? 'opacity-60' : '' }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $badgeClass }}">
                                {{ $statuses[$task->status] }}
                            </span>
                            @if ($task->due_date)
                                <span class="text-xs text-gray-400">
                                    期限: {{ $task->due_date->format('Y年m月d日') }}
                                </span>
                            @endif
                        </div>
                        <h3 class="mt-1 text-base font-semibold {{ $task->status === 'done' ? 'line-through text-gray-400' : 'text-gray-800' }}">
                            <a href="{{ route('tasks.show', $task) }}" class="hover:underline">
                                {{ $task->title }}
                            </a>
                        </h3>
                        @if ($task->description)
                            <p class="mt-1 text-sm text-gray-500 truncate">{{ Str::limit($task->description, 80) }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('tasks.edit', $task) }}"
                           class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded">
                            編集
                        </a>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                              onsubmit="return confirm('このタスクを削除しますか？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1.5 rounded">
                                削除
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-12 text-center text-gray-500">
                    タスクがまだありません。
                    <a href="{{ route('tasks.create') }}" class="text-indigo-600 hover:underline">最初のタスクを追加する</a>
                </div>
            @endforelse

            <div>{{ $tasks->links() }}</div>
        </div>
    </div>
</x-app-layout>
