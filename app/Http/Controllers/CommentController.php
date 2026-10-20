<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Document;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Document $document)
    {
        abort_unless($document->isPublished(), 404);
        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:2000']]);
        $body = trim($data['body']);

        // Chống spam: không cho gửi lại cùng nội dung trong 2 phút
        $dup = Comment::where('user_id', $request->user()->id)->where('document_id', $document->id)
            ->where('body', $body)->where('created_at', '>=', now()->subMinutes(2))->exists();
        if ($dup) {
            return back()->withErrors(['body' => 'Bạn vừa gửi bình luận này rồi.'])->withInput();
        }

        Comment::create(['document_id' => $document->id, 'user_id' => $request->user()->id, 'body' => $body, 'status' => 'visible']);

        return redirect()->to(route('library.show', $document) . '#binh-luan')->with('success', 'Đã đăng bình luận.');
    }

    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();
        abort_unless($comment->user_id === $user->id || $user->isAdmin(), 403);
        if ($comment->user_id !== $user->id) {
            AuditLogger::record('comment.delete', $comment, ['document_id' => $comment->document_id]);
        }
        $comment->delete();

        return back()->with('success', 'Đã xóa bình luận.');
    }
}
