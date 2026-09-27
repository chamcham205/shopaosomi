<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function index(Request $r)
    {
        $customers = User::where('role', 'customer')
            ->when($r->filled('q'), function ($q) use ($r) {
                $t = '%' . trim($r->q) . '%';
                $q->where(fn ($x) => $x->where('name', 'like', $t)
                    ->orWhere('email', 'like', $t)
                    ->orWhere('phone', 'like', $t));
            })
            ->when($r->filled('status'), fn ($q) => $q->where('status', $r->status))
            ->withCount('orders')
            ->withCount(['orders as completed_orders_count' => fn ($q) => $q->where('status', 'completed')])
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('status', 'completed')], 'total')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'status'   => 'nullable|in:active,locked',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role'     => 'customer',
            'status'   => $data['status'] ?? 'active',
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Thêm khách hàng thành công.');
    }

    public function edit(User $user)
    {
        abort_unless($user->role === 'customer', 404);
        return view('admin.customers.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->role === 'customer', 404);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'status'   => 'required|in:active,locked',
        ]);

        $updateData = [
            'name'   => $data['name'],
            'email'  => $data['email'],
            'phone'  => $data['phone'] ?? null,
            'status' => $data['status'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.customers.index')->with('success', 'Cập nhật thông tin khách hàng thành công.');
    }

    public function show(User $user)
    {
        abort_unless($user->role === 'customer', 404);
        $user->load(['orders' => fn ($q) => $q->latest()]);
        $stats = [
            'total'     => $user->orders->count(),
            'completed' => $user->orders->where('status', 'completed')->count(),
            'cancelled' => $user->orders->where('status', 'cancelled')->count(),
            'spent'     => $user->orders->where('status', 'completed')->sum('total'),
        ];
        return view('admin.customers.show', compact('user', 'stats'));
    }

    public function status(Request $r, User $user)
    {
        abort_unless($user->role === 'customer', 404);
        $d = $r->validate(['status' => 'required|in:active,locked']);
        $user->update(['status' => $d['status']]);
        return back()->with('success', 'Đã cập nhật khách hàng.');
    }
}
