<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Http\Resources\ItemResource;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::all();
        return ItemResource::collection($items);
    }

    public function show($id)
    {
        $item = Item::findOrFail($id);
        return new ItemResource($item);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|integer|min:0',
        ]);
        $item = Item::create($validated);
        return new ItemResource($item);
    }
}