<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\ItemResource;
use Illuminate\Http\Request;
use Tests\TestCase;

class ItemResourceTest extends TestCase
{
    public function test_to_array_contains_expected_fields(): void
    {
        $item = (object) [
            'id' => 1,
            'name' => 'Widget',
            'price' => 500,
            'created_at' => now(),
        ];

        $array = (new ItemResource($item))->toArray(Request::create('/'));

        $this->assertSame([
            'id' => 1,
            'name' => 'Widget',
            'price' => 500,
            'created_at' => $item->created_at->toISOString(),
        ], $array);
    }
}
