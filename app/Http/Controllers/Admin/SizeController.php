<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSizeRequest;
use App\Models\Size;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::withCount('variants')->orderBy('sort_order')->paginate(20);
        return view('admin.sizes.index', compact('sizes'));
    }

    public function store(StoreSizeRequest $request)
    {
        Size::create($request->validated());
        return redirect()->route('admin.sizes.index')->with('success', 'Kích cỡ đã được thêm.');
    }

    public function update(StoreSizeRequest $request, Size $size)
    {
        $size->update($request->validated());
        return redirect()->route('admin.sizes.index')->with('success', 'Kích cỡ đã được cập nhật.');
    }

    public function destroy(Size $size)
    {
        if ($size->isInUse()) {
            return back()->with('error', 'Không thể xóa kích cỡ đang được sử dụng trong sản phẩm.');
        }
        $size->delete();
        return redirect()->route('admin.sizes.index')->with('success', 'Đã xóa kích cỡ.');
    }
}
