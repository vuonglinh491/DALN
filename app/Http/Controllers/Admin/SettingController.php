<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings', [
            'values' => collect(array_keys(config('daln.settings')))->mapWithKeys(fn ($k) => [$k => Setting::get($k)])->all(),
            // Không bao giờ hiển thị khóa API; chỉ cho biết đã cấu hình hay chưa
            'geminiConfigured' => (bool) config('daln.gemini.key'),
            'geminiModel' => config('daln.gemini.model'),
            'queue' => config('queue.default'),
        ]);
    }

    public function update(Request $request)
    {
        $d = $request->validate([
            'ai_daily_limit' => ['required', 'integer', 'between:1,1000'],
            'ai_max_question_chars' => ['required', 'integer', 'between:50,4000'],
            'ai_max_output_tokens' => ['required', 'integer', 'between:200,8000'],
            'upload_max_kb' => ['required', 'integer', 'between:100,102400'],
            'preview_chars' => ['required', 'integer', 'between:0,5000'],
        ]);
        $old = [];
        foreach ($d as $k => $v) {
            $old[$k] = Setting::get($k);
            Setting::put($k, $v);
        }
        AuditLogger::record('settings.update', null, ['old' => $old, 'new' => $d]);

        return back()->with('success', 'Đã lưu cấu hình.');
    }
}
