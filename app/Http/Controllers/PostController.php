<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use App\Repositories\PostRepository;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService,
        private PostRepository $postRepository
    ) {}

    public function index()
    {
        $posts = $this->postRepository->getPublished();
        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function create()
    {
        $categories = Post::categories();
        return view('posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|max:200',
            'content'  => 'required|max:10000',
            'category' => 'required|in:' . implode(',', Post::categories()),
        ]);

        $post = $this->postService->createPost(
            array_merge($validated, ['user_id' => auth()->id()])
        );

        return redirect()->route('posts.show', $post)->with('success', '投稿を作成しました');
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        $categories = Post::categories();
        return view('posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title'    => 'required|max:200',
            'content'  => 'required|max:10000',
            'category' => 'required|in:' . implode(',', Post::categories()),
        ]);

        $post = $this->postService->updatePost($post, $validated);

        return redirect()->route('posts.show', $post)->with('success', '投稿を更新しました');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $this->postService->deletePost($post);
        return redirect()->route('posts.index')->with('success', '投稿を削除しました');
    }
}
