<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DownloadHistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// ---------- Công khai ----------
Route::get('/', HomeController::class)->name('home');
Route::get('/tai-lieu', [LibraryController::class, 'index'])->name('library.index');
Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search');
Route::get('/tim-kiem/goi-y', [SearchController::class, 'suggest'])->name('search.suggest');
Route::get('/tai-lieu/{document}', [LibraryController::class, 'show'])->whereNumber('document')->name('library.show');

// ---------- Khách ----------
Route::middleware(['guest', 'throttle:auth'])->group(function () {
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register']);
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login']);
    Route::get('/quen-mat-khau', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/quen-mat-khau', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [PasswordResetController::class, 'reset'])->name('password.update');
});
Route::post('/dang-xuat', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Người dùng đã đăng nhập ----------
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/tai-lieu/{document}/tai-xuong', [LibraryController::class, 'download'])->whereNumber('document')->name('library.download');
    Route::get('/tai-lieu/{document}/mo-tep', [LibraryController::class, 'file'])->whereNumber('document')->name('library.file');

    Route::get('/tai-len', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/tai-len', [DocumentController::class, 'store'])->middleware('throttle:uploads')->name('documents.store');
    Route::get('/tai-lieu-cua-toi', [DocumentController::class, 'mine'])->name('documents.mine');
    Route::get('/tai-lieu/{document}/sua', [DocumentController::class, 'edit'])->whereNumber('document')->name('documents.edit');
    Route::put('/tai-lieu/{document}', [DocumentController::class, 'update'])->whereNumber('document')->middleware('throttle:uploads')->name('documents.update');
    Route::delete('/tai-lieu/{document}', [DocumentController::class, 'destroy'])->whereNumber('document')->name('documents.destroy');
    Route::post('/tai-lieu/{document}/gui-lai', [DocumentController::class, 'resubmit'])->whereNumber('document')->name('documents.resubmit');
    Route::post('/tai-lieu/{document}/xu-ly-lai', [DocumentController::class, 'reprocess'])->whereNumber('document')->name('documents.reprocess');

    Route::get('/yeu-thich', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/tai-lieu/{document}/yeu-thich', [BookmarkController::class, 'toggle'])->whereNumber('document')->name('bookmarks.toggle');
    Route::post('/tai-lieu/{document}/danh-gia', [RatingController::class, 'store'])->whereNumber('document')->name('ratings.store');
    Route::post('/tai-lieu/{document}/binh-luan', [CommentController::class, 'store'])->whereNumber('document')->middleware('throttle:comments')->name('comments.store');
    Route::delete('/binh-luan/{comment}', [CommentController::class, 'destroy'])->whereNumber('comment')->name('comments.destroy');
    Route::post('/bao-cao', [ReportController::class, 'store'])->middleware('throttle:reports')->name('reports.store');

    Route::get('/lich-su-tai-xuong', [DownloadHistoryController::class, 'index'])->name('downloads.index');

    Route::get('/thong-bao', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/thong-bao/doc-tat-ca', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/thong-bao/{id}/doc', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('/ho-so', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/ho-so', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/ho-so/mat-khau', [ProfileController::class, 'password'])->name('profile.password');

    Route::get('/tro-ly-ai', [ChatController::class, 'index'])->name('ai.index');
    Route::post('/tro-ly-ai', [ChatController::class, 'start'])->name('ai.start');
    Route::get('/tro-ly-ai/lich-su', [ChatController::class, 'history'])->name('ai.history');
    Route::post('/tro-ly-ai/dong-y', [ChatController::class, 'consent'])->name('ai.consent');
    Route::delete('/tro-ly-ai/dong-y', [ChatController::class, 'revokeConsent'])->name('ai.consent.revoke');
    Route::get('/tro-ly-ai/{conversation}', [ChatController::class, 'show'])->whereNumber('conversation')->name('ai.show');
    Route::post('/tro-ly-ai/{conversation}/tin-nhan', [ChatController::class, 'message'])->whereNumber('conversation')->middleware('throttle:ai')->name('ai.message');
    Route::delete('/tro-ly-ai/{conversation}', [ChatController::class, 'destroy'])->whereNumber('conversation')->name('ai.destroy');
});

// ---------- Quản trị (kiểm tra vai trò ở server) ----------
Route::prefix('quan-tri')->name('admin.')->middleware(['auth', 'active', 'role:admin'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/nguoi-dung', [Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/nguoi-dung/{user}/khoa', [Admin\UserController::class, 'lock'])->name('users.lock');
    Route::post('/nguoi-dung/{user}/mo-khoa', [Admin\UserController::class, 'unlock'])->name('users.unlock');
    Route::post('/nguoi-dung/{user}/vai-tro', [Admin\UserController::class, 'role'])->name('users.role');

    Route::get('/kiem-duyet', [Admin\ModerationController::class, 'index'])->name('moderation.index');
    Route::get('/kiem-duyet/{document}', [Admin\ModerationController::class, 'show'])->whereNumber('document')->name('moderation.show');
    Route::post('/kiem-duyet/{document}', [Admin\ModerationController::class, 'decide'])->whereNumber('document')->name('moderation.decide');
    Route::delete('/kiem-duyet/{document}', [Admin\ModerationController::class, 'destroy'])->whereNumber('document')->name('moderation.destroy');
    Route::post('/kiem-duyet/{document}/xu-ly-lai', [Admin\ModerationController::class, 'reprocess'])->whereNumber('document')->name('moderation.reprocess');

    Route::get('/danh-muc', [Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/danh-muc', [Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/danh-muc/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/danh-muc/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/the', [Admin\CategoryController::class, 'storeTag'])->name('tags.store');
    Route::delete('/the/{tag}', [Admin\CategoryController::class, 'destroyTag'])->name('tags.destroy');

    Route::get('/bao-cao', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::post('/bao-cao/{report}', [Admin\ReportController::class, 'handle'])->name('reports.handle');
    Route::post('/binh-luan/{comment}/chuyen-trang-thai', [Admin\ReportController::class, 'toggleComment'])->name('comments.toggle');

    Route::get('/giam-sat', [Admin\MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/cau-hinh', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/cau-hinh', [Admin\SettingController::class, 'update'])->name('settings.update');
});
