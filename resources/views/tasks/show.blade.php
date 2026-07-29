<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">タスク詳細</h2>
            <a href="{{ route('tasks.index') }}" class="text-sm text-gray-500 hover:underline">← 一覧へ戻る</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @php
                $badgeClass = match($task->status) {
                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                    'done'        => 'bg-green-100 text-green-800',
                    default       => 'bg-gray-100 text-gray-600',
                };
            @endphp

            <div class="bg-white shadow-sm rounded-lg p-8 space-y-5">
                <div class="flex items-center gap-3">
                    <span class="inline-block text-sm px-3 py-1 rounded-full font-medium {{ $badgeClass }}">
                        {{ $statuses[$task->status] }}
                    </span>
                    @if ($task->due_date)
                        <span class="text-sm text-gray-500">期限: {{ $task->due_date->format('Y年m月d日') }}</span>
                    @endif
                </div>

                <h1 class="text-2xl font-bold text-gray-800 {{ $task->status === 'done' ? 'line-through text-gray-400' : '' }}">
                    {{ $task->title }}
                </h1>

                @if ($task->description)
                    <div class="text-gray-700 leading-relaxed whitespace-pre-wrap border-t pt-4">
                        {{ $task->description }}
                    </div>
                @else
                    <p class="text-gray-400 text-sm border-t pt-4">説明なし</p>
                @endif

                <div class="text-xs text-gray-400 border-t pt-4">
                    作成: {{ $task->created_at->format('Y年m月d日 H:i') }}
                    @if ($task->updated_at->ne($task->created_at))
                        ・更新: {{ $task->updated_at->format('Y年m月d日 H:i') }}
                    @endif
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('tasks.edit', $task) }}"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-5 rounded-md text-sm">
                        編集
                    </a>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                          onsubmit="return confirm('このタスクを削除しますか？')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 px-5 rounded-md text-sm">
                            削除
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
