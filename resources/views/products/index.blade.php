@extends('layouts.app')

@section('title', '商品一覧')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="margin:0;">商品一覧</h1>
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ 商品登録</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>商品名</th>
                <th>カテゴリー</th>
                <th>価格</th>
                <th>在庫</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>
                        <a href="{{ route('products.show', $product) }}" style="color:#1e40af; text-decoration:none; font-weight:500;">
                            {{ $product->name }}
                        </a>
                    </td>
                    <td><span class="badge">{{ $product->category }}</span></td>
                    <td>¥{{ number_format($product->price) }}</td>
                    <td class="{{ $product->stock <= 5 ? 'stock-low' : '' }}">
                        {{ $product->stock }}
                        @if ($product->stock <= 5)
                            （残りわずか）
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary" style="padding:4px 12px; font-size:0.8rem;">編集</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST"
                                  onsubmit="return confirm('削除しますか？')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding:4px 12px; font-size:0.8rem;">削除</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#6b7280; padding:32px;">
                        商品が登録されていません。<a href="{{ route('products.create') }}">最初の商品を登録する</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:20px;">{{ $products->links() }}</div>
@endsection
