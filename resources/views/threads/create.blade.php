<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">スレッドを立てる</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8">
                <form action="{{ route('threads.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            タイトル <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                               value="{{ old('title') }}" maxlength="200" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-700 mb-1">
                            本文 <span class="text-red-500">*</span>
                        </label>
                        <textarea id="body" name="body" rows="8" required maxlength="2000"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-md text-sm">
                            スレッドを立てる
                        </button>
                        <a href="{{ route('threads.index') }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded-md text-sm">
                            キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
