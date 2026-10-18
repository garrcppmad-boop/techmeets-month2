<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">決済キャンセル</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8 text-center">
                <p class="text-3xl">🛑</p>
                <h1 class="mt-3 text-2xl font-bold text-gray-900">決済をキャンセルしました</h1>
                <p class="mt-2 text-gray-600">課金は行われていません。</p>
                <a href="{{ route('checkout.index') }}"
                   class="mt-6 inline-block bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm">
                    商品一覧に戻る
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
