<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class PostRepository
{
    /** 一覧キャッシュの有効期間（秒） */
    public const CACHE_TTL = 600;

    /** 一覧キャッシュの世代番号。投稿が変わるたびに増やし、古いキャッシュをまとめて無効にする */
    public const CACHE_VERSION_KEY = 'posts:index:version';

    /**
     * 投稿一覧（ページごとにキャッシュする）
     *
     * キャッシュキーに「世代番号」と「ページ番号」を含める。
     * 投稿が作成・更新・削除されると Post モデルのイベントで世代番号が増え、
     * 全ページのキャッシュが一度に使われなくなる。
     */
    public function getPublished(): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();
        $version = Cache::get(self::CACHE_VERSION_KEY, 1);

        return Cache::remember(
            "posts:index:v{$version}:page:{$page}",
            self::CACHE_TTL,
            fn() => Post::with('user')->latest()->paginate(10),
        );
    }

    /**
     * 一覧キャッシュを無効にする（世代番号を進める）
     */
    public static function flushIndexCache(): void
    {
        Cache::forever(self::CACHE_VERSION_KEY, Cache::get(self::CACHE_VERSION_KEY, 1) + 1);
    }

    public function findById(int $id): Post
    {
        return Post::findOrFail($id);
    }

    public function create(array $data): Post
    {
        $post = new Post($data);
        $post->user_id = $data['user_id'];
        $post->save();
        return $post;
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);
        return $post;
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }
}
