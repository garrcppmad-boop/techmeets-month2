@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="card">
        <span class="badge">{{ $product->category }}</span>
        <h1 style="margin-top:10px;">{{ $product->name }}</h1>

        <table style="margin-top:20px; box-shadow:none; border:1px solid #e5e7eb;">
            <tr>
                <th style="background:#f3f4f6; color:#374151; width:140px;">価格</th>
                <td>¥{{ number_format($product->price) }}</td>
            </tr>
            <tr>
                <th style="background:#f3f4f6; color:#374151;">在庫数</th>
                <td class="{{ $product->stock <= 5 ? 'stock-low' : '' }}">
                    {{ $product->stock }} 個
                    @if ($product->stock <= 5)
                        （残りわずか）
                    @endif
                </td>
            </tr>
            <tr>
                <th style="background:#f3f4f6; color:#374151;">説明</th>
                <td style="white-space:pre-wrap;">{{ $product->description ?? '説明なし' }}</td>
            </tr>
            <tr>
                <th style="background:#f3f4f6; color:#374151;">登録日</th>
                <td>{{ $product->created_at->format('Y年m月d日') }}</td>
            </tr>
        </table>
    </div>

    <div class="actions">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary">編集</a>
        <form action="{{ route('products.destroy', $product) }}" method="POST"
              onsubmit="return confirm('この商品を削除しますか？')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">削除</button>
        </form>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">一覧に戻る</a>
    </div>
@endsection
