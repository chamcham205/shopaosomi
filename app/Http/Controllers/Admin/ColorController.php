<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreColorRequest;
use App\Models\Color;

class ColorController extends Controller
{
    public function index()
    {
        $colors = Color::withCount('variants')->orderBy('name')->paginate(20);
        return view('admin.colors.index', compact('colors'));
    }

    public function store(StoreColorRequest $request)
    {
        Color::create($request->validated());
        return redirect()->route('admin.colors.index')->with('success', 'Màu sắc đã được thêm.');
    }

    public function update(StoreColorRequest $request, Color $color)
    {
        $color->update($request->validated());
        return redirect()->route('admin.colors.index')->with('success', 'Màu sắc đã được cập nhật.');
    }

    public function destroy(Color $color)
    {
        if ($color->isInUse()) {
            return back()->with('error', 'Không thể xóa màu đang được sử dụng trong sản phẩm.');
        }
        $color->delete();
        return redirect()->route('admin.colors.index')->with('success', 'Đã xóa màu sắc.');
    }

    public function toggleStatus(Color $color)
    {
        $color->status = $color->status === 'active' ? 'inactive' : 'active';
        $color->save();
        return back()->with('success', 'Đã cập nhật trạng thái màu sắc.');
    }
}
