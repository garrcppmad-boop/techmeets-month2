<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Thread;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    // 未ログインでも閲覧可能
    public function index()
    {
        // 一覧ではレス数しか表示しないので、レス本体は読み込まず件数だけ取得する（withCount）
        $threads = Thread::with('user')
            ->withCount('replies')
            ->latest()
            ->paginate(20);

        return view('threads.index', compact('threads'));
    }

    public function show(Thread $thread)
    {
        $thread->load('user', 'replies.user');
        return view('threads.show', compact('thread'));
    }

    // 以下はルートの auth ミドルウェアでログイン済みのみ到達できる

    public function create()
    {
        return view('threads.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:200',
            'body'  => 'required|max:2000',
        ]);

        $thread = Thread::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('threads.show', $thread)->with('success', 'スレッドを作成しました');
    }

    public function destroy(Thread $thread)
    {
        if (! $thread->isOwnedBy(auth()->id())) {
            abort(403);
        }

        $thread->delete();
        return redirect()->route('threads.index')->with('success', 'スレッドを削除しました');
    }
}
