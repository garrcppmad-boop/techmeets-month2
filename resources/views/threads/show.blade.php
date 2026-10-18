<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">{{ $thread->title }}</h2>
            <a href="{{ route('threads.index') }}" class="text-sm text-gray-500 hover:underline ml-4 shrink-0">← 一覧に戻る</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- スレッド本文（1番） --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="bg-indigo-50 px-4 py-2 flex justify-between items-center text-xs text-gray-500">
                    <span>
                        <span class="font-bold text-indigo-700">1</span>
                        　{{ $thread->user->name }}
                        　{{ $thread->created_at->format('Y/m/d H:i') }}
                    </span>
                    @auth
                        @if ($thread->isOwnedBy(auth()->id()))
                            <form action="{{ route('threads.destroy', $thread) }}" method="POST"
                                  onsubmit="return confirm('このスレッドを削除しますか？（レスも全て削除されます）')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">削除</button>
                            </form>
                        @endif
                    @endauth
                </div>
                <div class="px-6 py-4 text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $thread->body }}</div>
            </div>

            {{-- レス一覧 --}}
            @foreach ($thread->replies as $i => $reply)
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 flex justify-between items-center text-xs text-gray-500">
                        <span>
                            <span class="font-bold text-gray-700">{{ $i + 2 }}</span>
                            　{{ $reply->user->name }}
                            　{{ $reply->created_at->format('Y/m/d H:i') }}
                        </span>
                        @auth
                            @if ($reply->isOwnedBy(auth()->id()))
                                <form action="{{ route('threads.replies.destroy', [$thread, $reply]) }}" method="POST"
                                      onsubmit="return confirm('削除しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">削除</button>
                                </form>
                            @endif
                        @endauth
                    </div>
                    <div class="px-6 py-4 text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $reply->body }}</div>
                </div>
            @endforeach

            {{-- レス投稿フォーム --}}
            @auth
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">レスを投稿する</h3>
                    <form action="{{ route('threads.replies.store', $thread) }}" method="POST">
                        @csrf
                        <textarea name="body" rows="4" required maxlength="2000"
                                  placeholder="本文を入力してください..."
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 flex justify-end">
                            <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-md text-sm">
                                書き込む
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-4 text-center text-sm text-gray-500">
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">ログイン</a>するとレスを投稿できます。
                </div>
            @endauth
        </div>
    </div>
</x-app-layout>
