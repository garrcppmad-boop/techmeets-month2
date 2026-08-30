@extends('layouts.app')

@section('title', '商品編集')

@section('content')
    <h1>商品編集</h1>

    <div class="card">
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="image">商品画像</label>
                @if ($product->image_path)
                    <div style="margin-bottom:8px;">
                        <img src="{{ Storage::disk('s3')->url($product->image_path) }}" alt="{{ $product->name }}" style="max-width:200px; border-radius:6px;">
                    </div>
                @endif
                <input type="file" id="image" name="image" accept="image/*">
                @error('image')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="name">商品名 <span style="color:#dc2626;">*</span></label>
                <input type="text" id="name" name="name"
                       value="{{ old('name', $product->name) }}" maxlength="200" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="category">カテゴリー <span style="color:#dc2626;">*</span></label>
                <select id="category" name="category" required>
                    <option value="">-- 選択してください --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}"
                            {{ old('category', $product->category) === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
                @error('category')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="price">価格（円） <span style="color:#dc2626;">*</span></label>
                <input type="number" id="price" name="price"
                       value="{{ old('price', $product->price) }}" min="0" required>
                @error('price')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="stock">在庫数 <span style="color:#dc2626;">*</span></label>
                <input type="number" id="stock" name="stock"
                       value="{{ old('stock', $product->stock) }}" min="0" required>
                @error('stock')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">説明</label>
                <textarea id="description" name="description">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">更新する</button>
                <a href="{{ route('products.show', $product) }}" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>
@endsection
