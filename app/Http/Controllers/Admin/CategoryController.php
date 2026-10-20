<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Document;
use App\Models\Tag;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount(['documents' => fn ($q) => $q->withTrashed()])->orderBy('sort_order')->orderBy('name')->get(),
            'tags' => Tag::withCount('documents')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'between:0,9999'],
        ]);
        $c = Category::create([
            'name' => $d['name'], 'slug' => $this->uniqueSlug($d['name']), 'description' => $d['description'] ?? null,
            'sort_order' => $d['sort_order'] ?? 0, 'is_active' => true,
        ]);
        AuditLogger::record('category.create', $c, ['name' => $c->name]);

        return back()->with('success', 'Đã thêm danh mục.');
    }

    public function update(Request $request, Category $category)
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'between:0,9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $category->update([
            'name' => $d['name'], 'description' => $d['description'] ?? null,
            'sort_order' => $d['sort_order'] ?? 0, 'is_active' => $request->boolean('is_active'),
        ]);
        AuditLogger::record('category.update', $category, ['name' => $category->name]);

        return back()->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category)
    {
        if (Document::withTrashed()->where('category_id', $category->id)->exists()) {
            return back()->with('error', 'Không thể xóa danh mục đang có tài liệu. Hãy tắt kích hoạt danh mục thay vì xóa.');
        }
        AuditLogger::record('category.delete', $category, ['name' => $category->name]);
        $category->delete();

        return back()->with('success', 'Đã xóa danh mục.');
    }

    public function storeTag(Request $request)
    {
        $d = $request->validate(['name' => ['required', 'string', 'max:60']]);
        $slug = Str::slug($d['name']);
        if ($slug === '' || Tag::where('slug', $slug)->exists()) {
            return back()->with('error', 'Thẻ không hợp lệ hoặc đã tồn tại.');
        }
        $t = Tag::create(['name' => $d['name'], 'slug' => $slug]);
        AuditLogger::record('tag.create', $t, ['name' => $t->name]);

        return back()->with('success', 'Đã thêm thẻ.');
    }

    public function destroyTag(Tag $tag)
    {
        AuditLogger::record('tag.delete', $tag, ['name' => $tag->name]);
        $tag->delete();

        return back()->with('success', 'Đã xóa thẻ.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'danh-muc';
        $slug = $base;
        $i = 2;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
