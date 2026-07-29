<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use App\Services\PostService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private PostRepository $postRepository,
        private PostService $postService
    ) {}

    public function index()
    {
        $posts = $this->postRepository->getPublished();
        return PostResource::collection($posts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:200',
            'content'  => 'required|string|max:10000',
            'category' => 'required|in:' . implode(',', Post::categories()),
        ]);

        $post = $this->postService->createPost(array_merge($validated, [
            'user_id' => User::first()->id,
        ]));

        return new PostResource($post);
    }
}
