<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">商品購入</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($products as $product)
                    <div class="bg-white shadow-sm rounded-lg p-6 flex flex-col">
                        <span class="inline-block self-start bg-indigo-100 text-indigo-800 text-xs px-3 py-1 rounded-full">
                            {{ $product->category }}
                        </span>
                        <h3 class="mt-3 text-lg font-bold text-gray-900">{{ $product->name }}</h3>
                        <p class="mt-1 text-sm text-gray-500 whitespace-pre-wrap flex-1">
                            {{ \Illuminate\Support\Str::limit($product->description, 80) ?: '説明なし' }}
                        </p>
                        <p class="mt-3 text-xl font-semibold text-gray-900">¥{{ number_format($product->price) }}</p>

                        <form action="{{ route('checkout', $product) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm">
                                購入する
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500">購入できる商品がありません。</p>
                @endforelse
            </div>

            <div class="mt-6">{{ $products->links() }}</div>
        </div>
    </div>
</x-app-layout>
