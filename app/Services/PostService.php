<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostService
{
    public function __construct(
        private PostRepository $postRepository
    ) {}

    public function createPost(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            $post = $this->postRepository->create($data);
            Log::info('Post created', ['post_id' => $post->id, 'user_id' => $data['user_id']]);
            return $post;
        });
    }

    public function updatePost(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $updated = $this->postRepository->update($post, $data);
            Log::info('Post updated', ['post_id' => $updated->id]);
            return $updated;
        });
    }

    public function deletePost(Post $post): void
    {
        DB::transaction(function () use ($post) {
            Log::info('Post deleted', ['post_id' => $post->id]);
            $this->postRepository->delete($post);
        });
    }
}
