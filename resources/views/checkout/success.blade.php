<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">決済完了</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8 text-center">
                <p class="text-3xl">✅</p>
                <h1 class="mt-3 text-2xl font-bold text-gray-900">ご購入ありがとうございます</h1>
                <p class="mt-2 text-gray-600">
                    決済が完了しました。購入履歴は数秒以内に反映されます。
                </p>
                <a href="{{ route('checkout.index') }}"
                   class="mt-6 inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-md text-sm">
                    商品一覧に戻る
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
