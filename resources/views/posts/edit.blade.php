<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">投稿を編集</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8">
                <form action="{{ route('posts.update', $post) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            タイトル <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                               value="{{ old('title', $post->title) }}" maxlength="200" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                            カテゴリー <span class="text-red-500">*</span>
                        </label>
                        <select id="category" name="category" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">-- 選択してください --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}"
                                    {{ old('category', $post->category) === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                            内容 <span class="text-red-500">*</span>
                        </label>
                        <textarea id="content" name="content" rows="8" required
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('content', $post->content) }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-md text-sm">
                            更新する
                        </button>
                        <a href="{{ route('posts.show', $post) }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded-md text-sm">
                            キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
