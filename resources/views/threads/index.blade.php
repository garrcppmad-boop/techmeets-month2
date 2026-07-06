<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">掲示板</h2>
            @auth
                <a href="{{ route('threads.create') }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                    + スレッドを立てる
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="text-indigo-600 hover:underline text-sm">
                    ログインして投稿する
                </a>
            @endauth
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- スレッド一覧テーブル --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">投稿者</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">レス</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">日時</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($threads as $thread)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('threads.show', $thread) }}"
                                       class="text-indigo-700 hover:underline font-medium">
                                        {{ $thread->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $thread->user->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ $thread->replies->count() }}</td>
                                <td class="px-6 py-4 text-xs text-gray-400">{{ $thread->created_at->format('m/d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                    スレッドがありません。
                                    @auth
                                        <a href="{{ route('threads.create') }}" class="text-indigo-600 hover:underline">最初のスレッドを立てる</a>
                                    @endauth
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $threads->links() }}</div>
        </div>
    </div>
</x-app-layout>
