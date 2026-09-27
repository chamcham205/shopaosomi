<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;

/**
 * User Model
 *
 * Model này đại diện cho tất cả người dùng trong hệ thống.
 * Có 3 role: customer, admin, super_admin
 *
 * Quan hệ (sẽ bổ sung ở các Phase sau):
 * - hasMany: Order, Address, Wishlist, Review, Cart
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Các trường được phép gán hàng loạt (mass assignment).
     *
     * Lưu ý bảo mật: role, status, theme được phép fillable nhưng
     * RegisterController TUYỆT ĐỐI KHÔNG lấy 'role' từ $request->input().
     * Controller luôn hardcode role='customer' khi đăng ký.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',    // Cho phép fillable để seeder/test/admin dùng được
        'status',  // nhưng RegisterController không bao giờ lấy từ request
        'avatar',
        'theme',
    ];

    /**
     * Ẩn các trường nhạy cảm khi serialize (trả về JSON, v.v.)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Kiểu dữ liệu tự động cast
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed', // Laravel tự hash khi gán
        ];
    }

    // =========================================================
    // HELPER METHODS – Kiểm tra role
    // =========================================================

    /**
     * Kiểm tra có phải Admin hoặc Super Admin không.
     * Dùng trong Middleware và View để phân quyền.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Kiểm tra có phải Super Admin không.
     * Super Admin có thêm quyền quản lý tài khoản admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Kiểm tra có phải Customer không.
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Kiểm tra tài khoản có đang bị khóa không.
     * Tài khoản bị khóa sẽ không được đăng nhập.
     */
    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    /**
     * Kiểm tra tài khoản đang hoạt động bình thường.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function cart() { return $this->hasOne(Cart::class); }
    public function wishlistItems() { return $this->hasMany(Wishlist::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function reviews() { return $this->hasMany(Review::class); }
    public function chatConversation() { return $this->hasOne(ChatConversation::class, 'user_id'); }
    public function chatMessages() { return $this->hasMany(ChatMessage::class, 'sender_id'); }
}
