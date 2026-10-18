<?php

declare(strict_types=1);

namespace App\Models;

use App\Repositories\PostRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
    ];

    /**
     * 投稿が保存・削除されたら一覧のキャッシュを無効にする。
     * どこから変更しても（コントローラー・API・Factory 等）必ず反映されるようモデルのイベントで行う
     */
    protected static function booted(): void
    {
        static::saved(fn() => PostRepository::flushIndexCache());
        static::deleted(fn() => PostRepository::flushIndexCache());
    }

    public static function categories(): array
    {
        return ['技術', 'ライフスタイル', '学習', 'その他'];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->user_id === $userId;
    }
}
