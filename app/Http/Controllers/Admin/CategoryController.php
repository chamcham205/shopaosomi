<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        $categories = $query->orderBy('sort_order')->paginate(20)->withQueryString();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        // Auto slug
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Upload ảnh danh mục nếu có
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã được tạo thành công.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            // Xóa ảnh cũ
            if ($category->image) Storage::disk('public')->delete($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã được cập nhật.');
    }

    public function destroy(Category $category)
    {
        // Không cho xóa nếu còn sản phẩm (kể cả soft-deleted)
        if ($category->hasProducts()) {
            return back()->with('error', 'Không thể xóa danh mục đang có sản phẩm.');
        }

        if ($category->image) Storage::disk('public')->delete($category->image);
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Danh mục đã được xóa.');
    }

    public function toggleStatus(Category $category)
    {
        $category->status = $category->status === 'active' ? 'inactive' : 'active';
        $category->save();
        return back()->with('success', 'Đã cập nhật trạng thái danh mục.');
    }
}
