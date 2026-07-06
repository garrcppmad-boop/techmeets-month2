<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Thread;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function store(Request $request, Thread $thread)
    {
        $request->validate([
            'body' => 'required|max:2000',
        ]);

        $thread->replies()->create([
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        return redirect()->route('threads.show', $thread)->with('success', 'レスを投稿しました');
    }

    public function destroy(Thread $thread, Reply $reply)
    {
        if (! $reply->isOwnedBy(auth()->id())) {
            abort(403);
        }

        $reply->delete();
        return redirect()->route('threads.show', $thread)->with('success', 'レスを削除しました');
    }
}
