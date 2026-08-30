@extends('layouts.app')

@section('title', '商品登録')

@section('content')
    <h1>商品登録</h1>

    <div class="card">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="image">商品画像</label>
                <input type="file" id="image" name="image" accept="image/*">
                @error('image')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="name">商品名 <span style="color:#dc2626;">*</span></label>
                <input type="text" id="name" name="name"
                       value="{{ old('name') }}" maxlength="200" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="category">カテゴリー <span style="color:#dc2626;">*</span></label>
                <select id="category" name="category" required>
                    <option value="">-- 選択してください --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
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
                       value="{{ old('price', 0) }}" min="0" required>
                @error('price')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="stock">在庫数 <span style="color:#dc2626;">*</span></label>
                <input type="number" id="stock" name="stock"
                       value="{{ old('stock', 0) }}" min="0" required>
                @error('stock')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">説明</label>
                <textarea id="description" name="description">{{ old('description') }}</textarea>
                @error('description')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">登録する</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>
@endsection
