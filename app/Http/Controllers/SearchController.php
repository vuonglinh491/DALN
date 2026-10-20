<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\Search\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:100'], 'category' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:pdf,docx,txt'], 'sort' => ['nullable', 'in:' . implode(',', array_keys(SearchService::SORTS))],
        ]);
        $q = trim($f['q'] ?? '');

        // Tìm kiếm gần đây (lưu trong phiên của người dùng)
        $recent = $request->session()->get('recent_searches', []);
        if ($q !== '' && $request->query('page', 1) == 1) {
            $recent = array_slice(array_values(array_unique(array_merge([$q], $recent))), 0, 6);
            $request->session()->put('recent_searches', $recent);
        }

        return view('search.index', [
            'q' => $q,
            'documents' => $q === '' ? null : $search->search($f),
            'categories' => Category::active()->orderBy('name')->get(),
            'filters' => $f,
            'sorts' => SearchService::SORTS,
            'recent' => $recent,
        ]);
    }

    public function suggest(Request $request, SearchService $search)
    {
        $q = mb_substr((string) $request->query('q'), 0, 100);
        $items = array_map(fn ($d) => ['title' => $d->title, 'url' => route('library.show', $d)], $search->suggest($q));

        return response()->json($items);
    }
}
