<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">投稿詳細</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-8">
                <span class="inline-block bg-indigo-100 text-indigo-800 text-xs px-3 py-1 rounded-full">
                    {{ $post->category }}
                </span>
                <h1 class="mt-3 text-2xl font-bold text-gray-900">{{ $post->title }}</h1>
                <p class="mt-1 text-sm text-gray-400">
                    投稿者: {{ $post->user->name }} ・ {{ $post->created_at->format('Y年m月d日 H:i') }}
                </p>

                <div class="mt-6 text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $post->content }}</div>
            </div>

            <div class="mt-4 flex gap-3 items-center">
                {{-- 自分の投稿のみ編集・削除を表示 --}}
                @auth
                    @if ($post->isOwnedBy(auth()->id()))
                        <a href="{{ route('posts.edit', $post) }}"
                           class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md text-sm">
                            編集
                        </a>
                        <form action="{{ route('posts.destroy', $post) }}" method="POST"
                              onsubmit="return confirm('この投稿を削除しますか？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md text-sm">
                                削除
                            </button>
                        </form>
                    @endif
                @endauth

                <a href="{{ route('posts.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-md text-sm">
                    一覧に戻る
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
