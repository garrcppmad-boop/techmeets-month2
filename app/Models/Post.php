<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
    ];

    public static function categories(): array
    {
        return ['技術', 'ライフスタイル', '学習', 'その他'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->user_id === $userId;
    }
}
