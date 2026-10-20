<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use App\Models\Download;
use App\Models\User;

class HomeController extends Controller
{
    public function __invoke()
    {
        // Mọi số liệu lấy trực tiếp từ database
        $stats = [
            'documents' => Document::published()->count(),
            'users' => User::where('is_active', true)->count(),
            'downloads' => Download::count(),
        ];
        $categories = Category::active()->orderBy('sort_order')->orderBy('name')
            ->withCount(['documents' => fn ($q) => $q->published()])->get();
        $latest = Document::published()->with(['owner:id,name', 'category:id,name'])->latest('approved_at')->limit(8)->get();
        $popular = Document::published()->with(['owner:id,name', 'category:id,name'])->where('download_count', '>', 0)
            ->orderByDesc('download_count')->limit(4)->get();

        return view('home', compact('stats', 'categories', 'latest', 'popular'));
    }
}
