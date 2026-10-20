<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $uid = $request->user()->id;
        $bookmarks = Bookmark::where('user_id', $uid)
            ->whereHas('document', fn ($q) => $q->where(fn ($w) => $w->published()->orWhere('user_id', $uid)))
            ->with(['document.owner:id,name', 'document.category:id,name'])->latest()->paginate(12);

        return view('bookmarks.index', compact('bookmarks'));
    }

    public function toggle(Request $request, Document $document)
    {
        abort_unless(Gate::allows('view', $document), 404);
        $existing = Bookmark::where('user_id', $request->user()->id)->where('document_id', $document->id)->first();
        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Đã bỏ khỏi danh sách yêu thích.');
        }
        Bookmark::create(['user_id' => $request->user()->id, 'document_id' => $document->id]);

        return back()->with('success', 'Đã thêm vào danh sách yêu thích.');
    }
}
