<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('notifications.index', ['notifications' => $request->user()->notifications()->paginate(15)]);
    }

    public function read(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();
        $url = $n->data['url'] ?? null;

        // Chỉ chuyển hướng tới URL nội bộ
        return ($url && str_starts_with($url, url('/'))) ? redirect($url) : redirect()->route('notifications.index');
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Đã đánh dấu tất cả là đã đọc.');
    }
}
