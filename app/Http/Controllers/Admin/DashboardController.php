<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ChatMessage;
use App\Models\Comment;
use App\Models\Document;
use App\Models\Download;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // Toàn bộ số liệu đọc trực tiếp từ database
        $byModeration = Document::query()->select('moderation_status', DB::raw('count(*) as n'))->groupBy('moderation_status')->pluck('n', 'moderation_status');
        $byProcessing = Document::query()->select('processing_status', DB::raw('count(*) as n'))->groupBy('processing_status')->pluck('n', 'processing_status');

        $queue = ['pending' => null, 'failed' => null];
        if (Schema::hasTable('jobs')) {
            $queue['pending'] = DB::table('jobs')->count();
        }
        if (Schema::hasTable('failed_jobs')) {
            $queue['failed'] = DB::table('failed_jobs')->count();
        }

        return view('admin.dashboard', [
            'users' => ['total' => User::count(), 'locked' => User::where('is_active', false)->count()],
            'byModeration' => $byModeration,
            'byProcessing' => $byProcessing,
            'totalDocs' => Document::count(),
            'downloads' => Download::count(),
            'comments' => Comment::count(),
            'openReports' => Report::where('status', 'open')->count(),
            'aiToday' => ChatMessage::where('role', 'user')->where('created_at', '>=', now()->startOfDay())->count(),
            'aiFailed24h' => ChatMessage::where('role', 'assistant')->where('status', 'failed')->where('created_at', '>=', now()->subDay())->count(),
            'queue' => $queue,
            'pendingDocs' => Document::where('moderation_status', Document::PENDING)->with('owner:id,name')->oldest('submitted_at')->limit(6)->get(),
            'recentAudit' => AuditLog::with('user:id,name')->latest('id')->limit(8)->get(),
        ]);
    }
}
