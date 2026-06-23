<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // 一括代入を許可するカラム（$fillable未設定だとcreate()が使えない）
    protected $fillable = [
        'title',
        'content',
        'user_id',
    ];

    // created_at をCarbonオブジェクトに変換（->format()が使えるようになる）
    protected $casts = [
        'created_at' => 'datetime',
    ];

    // belongsTo: 投稿(多) → ユーザー(1)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}