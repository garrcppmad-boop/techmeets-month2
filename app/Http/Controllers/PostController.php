<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // 一覧・詳細は未ログインでも閲覧可能
    public function index()
    {
        $posts = Post::with('user')->latest()->paginate(10);
        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    // 以下はルートの auth ミドルウェアでログイン済みのみ到達できる

    public function create()
    {
        $categories = Post::categories();
        return view('posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|max:200',
            'content'  => 'required',
            'category' => 'required|in:' . implode(',', Post::categories()),
        ]);

        $validated['user_id'] = auth()->id();

        $post = Post::create($validated);
        return redirect()->route('posts.show', $post)->with('success', '投稿を作成しました');
    }

    public function edit(Post $post)
    {
        // 自分の投稿以外は編集不可
        if (! $post->isOwnedBy(auth()->id())) {
            abort(403, 'この操作は許可されていません');
        }

        $categories = Post::categories();
        return view('posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        if (! $post->isOwnedBy(auth()->id())) {
            abort(403, 'この操作は許可されていません');
        }

        $validated = $request->validate([
            'title'    => 'required|max:200',
            'content'  => 'required',
            'category' => 'required|in:' . implode(',', Post::categories()),
        ]);

        $post->update($validated);
        return redirect()->route('posts.show', $post)->with('success', '投稿を更新しました');
    }

    public function destroy(Post $post)
    {
        if (! $post->isOwnedBy(auth()->id())) {
            abort(403, 'この操作は許可されていません');
        }

        $post->delete();
        return redirect()->route('posts.index')->with('success', '投稿を削除しました');
    }
}
