<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function create()
    {
        $categories = Product::categories();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|max:200',
            'price'       => 'required|integer|min:0',
            'description' => 'nullable',
            'stock'       => 'required|integer|min:0',
            'category'    => 'required|in:' . implode(',', Product::categories()),
        ]);

        $product = Product::create($validated);
        return redirect()->route('products.show', $product)->with('success', '商品を登録しました');
    }

    public function edit(Product $product)
    {
        $categories = Product::categories();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => 'required|max:200',
            'price'       => 'required|integer|min:0',
            'description' => 'nullable',
            'stock'       => 'required|integer|min:0',
            'category'    => 'required|in:' . implode(',', Product::categories()),
        ]);

        $product->update($validated);
        return redirect()->route('products.show', $product)->with('success', '商品を更新しました');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', '商品を削除しました');
    }
}
