<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ChatMessage;
use App\Models\ProcessingJob;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'jobs');
        $data = ['tab' => $tab];

        if ($tab === 'ai') {
            $data['aiErrors'] = ChatMessage::where('role', 'assistant')->where('status', 'failed')
                ->with('conversation:id,user_id')->latest('id')->paginate(20)->withQueryString();
        } elseif ($tab === 'audit') {
            $action = trim((string) $request->query('action'));
            $data['action'] = $action;
            $data['logs'] = AuditLog::with('user:id,name')->when($action !== '', fn ($q) => $q->where('action', 'like', $action . '%'))
                ->latest('id')->paginate(25)->withQueryString();
        } else {
            $status = $request->query('status');
            $data['status'] = $status;
            $data['jobs'] = ProcessingJob::with('document:id,title,user_id')
                ->when($status, fn ($q) => $q->where('status', $status))->latest('id')->paginate(20)->withQueryString();
        }

        return view('admin.monitoring.index', $data);
    }
}
