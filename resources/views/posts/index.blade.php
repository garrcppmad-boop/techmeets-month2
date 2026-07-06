<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">投稿一覧</h2>
            @auth
                <a href="{{ route('posts.create') }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                    + 新規投稿
                </a>
            @endauth
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @forelse ($posts as $post)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <span class="inline-block bg-indigo-100 text-indigo-800 text-xs px-3 py-1 rounded-full">
                        {{ $post->category }}
                    </span>
                    <h3 class="mt-2 text-lg font-semibold">
                        <a href="{{ route('posts.show', $post) }}" class="text-indigo-700 hover:underline">
                            {{ $post->title }}
                        </a>
                    </h3>
                    <p class="mt-2 text-gray-600 text-sm leading-relaxed">
                        {{ Str::limit($post->content, 120) }}
                    </p>
                    <p class="mt-3 text-xs text-gray-400">
                        {{ $post->user->name }} ・ {{ $post->created_at->format('Y年m月d日') }}
                    </p>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-12 text-center text-gray-500">
                    まだ投稿がありません。
                    @auth
                        <a href="{{ route('posts.create') }}" class="text-indigo-600 hover:underline">最初の投稿を作成する</a>
                    @else
                        <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">ログイン</a>して投稿しましょう。
                    @endauth
                </div>
            @endforelse

            <div>{{ $posts->links() }}</div>
        </div>
    </div>
</x-app-layout>
