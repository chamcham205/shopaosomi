<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng.
     */
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Hiển thị form tạo người dùng mới.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Lưu người dùng mới vào CSDL.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,user,customer,super_admin',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => in_array($request->role, ['user', 'customer']) ? 'customer' : $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Thêm người dùng thành công.');
    }

    /**
     * Hiển thị chi tiết người dùng.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa người dùng.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Cập nhật thông tin người dùng.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|in:admin,user,customer,super_admin',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => in_array($request->role, ['user', 'customer']) ? 'customer' : $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật người dùng thành công.');
    }

    /**
     * Xóa người dùng (Vô hiệu hóa - mục người dùng chỉ xem và sửa).
     */
    public function destroy(User $user)
    {
        return redirect()->route('admin.users.index')->with('error', 'Chức năng xóa người dùng đã bị vô hiệu hóa. Bạn chỉ có thể xem hoặc chỉnh sửa thông tin người dùng.');
    }
}
