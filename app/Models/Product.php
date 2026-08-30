<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'description',
        'stock',
        'category',
        'image_path',
    ];

    public static function categories(): array
    {
        return ['食品', '衣類', '電子機器', '書籍', 'その他'];
    }
}
