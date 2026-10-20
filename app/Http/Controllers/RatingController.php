<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Document $document)
    {
        abort_unless($document->isPublished(), 404);
        if ($document->user_id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể tự đánh giá tài liệu của mình.');
        }
        $data = $request->validate(['score' => ['required', 'integer', 'between:1,5']]);
        Rating::updateOrCreate(['user_id' => $request->user()->id, 'document_id' => $document->id], ['score' => $data['score']]);

        return back()->with('success', 'Cảm ơn bạn đã đánh giá.');
    }
}
