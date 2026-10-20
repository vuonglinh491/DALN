<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Document;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:document,comment'],
            'id' => ['required', 'integer'],
            'reason' => ['required', 'in:' . implode(',', array_keys(Report::REASONS))],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);
        $user = $request->user();

        if ($data['type'] === 'document') {
            $target = Document::findOrFail($data['id']);
            abort_unless(Gate::allows('view', $target), 404);
            $own = $target->user_id === $user->id;
        } else {
            $target = Comment::visible()->findOrFail($data['id']);
            abort_unless($target->document && Gate::allows('view', $target->document), 404);
            $own = $target->user_id === $user->id;
        }
        if ($own) {
            return back()->with('error', 'Bạn không thể báo cáo nội dung của chính mình.');
        }

        $exists = Report::where('reporter_id', $user->id)->where('reportable_type', $data['type'])
            ->where('reportable_id', $target->id)->where('status', 'open')->exists();
        if ($exists) {
            return back()->with('error', 'Bạn đã báo cáo nội dung này và đang chờ xử lý.');
        }

        Report::create([
            'reporter_id' => $user->id, 'reportable_type' => $data['type'], 'reportable_id' => $target->id,
            'reason' => $data['reason'], 'details' => $data['details'] ?? null, 'status' => 'open',
        ]);

        return back()->with('success', 'Đã gửi báo cáo. Quản trị viên sẽ xem xét.');
    }
}
