<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository
{
    public function getPublished(): LengthAwarePaginator
    {
        return Post::with('user')->latest()->paginate(10);
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
