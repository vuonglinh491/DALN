<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Paginator::useBootstrapFive();

        // Tên ngắn cho quan hệ đa hình (reports.reportable_type). Không dùng enforce để các model khác vẫn hoạt động.
        Relation::morphMap([
            'document' => Document::class,
            'comment' => Comment::class,
        ]);
    }
}
