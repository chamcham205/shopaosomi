# Shirt Store

Shirt Store là website bán áo sơ mi nam xây dựng bằng Laravel, gồm cửa hàng cho khách hàng và hệ thống quản trị vận hành. Dự án tập trung vào tính đúng đắn của tồn kho, giá, khuyến mãi và vòng đời đơn hàng.

## Chức năng

**Khách hàng:** đăng ký/đăng nhập/khôi phục mật khẩu; tìm kiếm, lọc và sắp xếp sản phẩm; chọn màu/size; giỏ hàng, yêu thích, mã giảm giá và phí giao hàng; thanh toán COD; xem/hủy đơn đang chờ; đánh giá sản phẩm đã mua; chọn giao diện sáng/tối/hệ thống.

**Quản trị:** dashboard; sản phẩm, danh mục, màu, size và tồn kho theo biến thể; đơn hàng và chuyển trạng thái; khóa/mở khách hàng; coupon, vận chuyển; báo cáo doanh thu; kiểm duyệt đánh giá/từ cấm; banner và cài đặt website.

## Công nghệ

- PHP >= 8.2, Laravel 12, Blade, Bootstrap 5
- MySQL, Eloquent ORM, PHPUnit
- Laravel Mail (mặc định ghi vào log khi chạy local)

## Yêu cầu trên Windows/XAMPP

- XAMPP có PHP >= 8.2, MySQL và các extension Laravel yêu cầu
- Composer 2; Git (khuyến nghị)
- Bật Apache/MySQL hoặc dùng máy chủ phát triển của Artisan

## Cài đặt

```powershell
cd C:\xampp\htdocs\shirt-store
composer install
Copy-Item .env.example .env
```

Tạo database MySQL tên `shirt_store`, sau đó cập nhật `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` trong `.env`. Tiếp tục chạy:

```powershell
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
php artisan test
```

Không commit `.env`. Khi demo local, giữ `MAIL_MAILER=log`; email được ghi vào `storage/logs/laravel.log` thay vì gửi ra Internet.

## Tài khoản demo

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Super Admin | `superadmin@shirtstore.local` | `Admin@123` |
| Admin | `admin@shirtstore.local` | `Admin@123` |
| Khách hàng | `customer@shirtstore.local` | `Customer@123` |
| Khách hàng | `minhanh@shirtstore.local` | `Customer@123` |
| Tài khoản bị khóa | `locked@shirtstore.local` | `Customer@123` |

Dữ liệu seed còn có 8 danh mục, 15 sản phẩm thực tế, màu/size/biến thể đủ hàng–sắp hết–hết hàng, coupon `WELCOME10` và `GIAM100K`, phí giao hàng, đơn mẫu, đánh giá, banner và cài đặt.

## Cấu trúc quan trọng

- `app/Http/Controllers`: điều phối luồng Customer/Admin
- `app/Services`: nghiệp vụ giỏ hàng, coupon, shipping, checkout, tồn kho, báo cáo và email
- `app/Models`: Eloquent models và quan hệ
- `database/migrations`, `database/seeders`: schema và dữ liệu demo
- `resources/views`: giao diện Blade
- `routes/web.php`: toàn bộ route web và middleware
- `tests/Feature`: kiểm thử các luồng nghiệp vụ
- `docs`: trạng thái, route, changelog và tài liệu bảo vệ

## Quy tắc nghiệp vụ chính

- `product_variants.stock` là nguồn tồn kho duy nhất; thêm giỏ không trừ kho.
- Checkout tự tính lại giá/giảm giá/phí ship trên server, khóa dòng bằng `lockForUpdate()` và tạo đơn trong `DB::transaction()`.
- `checkout_token` ngăn double-submit; tồn kho không âm; hủy đơn hoàn kho/coupon đúng một lần.
- Khách chỉ hủy đơn `pending`; admin chỉ chuyển theo state machine hợp lệ.
- Doanh thu chỉ tính đơn `completed`; lịch sử giữ snapshot sản phẩm/giá.
- Chỉ khách có đơn hoàn tất được đánh giá; đánh giá mới là `pending`, chỉ `approved` xuất hiện công khai.
- Admin/super admin không được mua hàng; customer không được truy cập `/admin/*`.

## Route demo chính

- Cửa hàng: `/`, `/products`, `/cart`, `/wishlist`, `/checkout`
- Tài khoản: `/account/orders`, `/account/reviews`
- Quản trị: `/admin/dashboard`, `/admin/products`, `/admin/inventory`, `/admin/orders`, `/admin/customers`, `/admin/reports`, `/admin/reviews`, `/admin/settings`, `/admin/banners`

Chi tiết route và HTTP method xem tại [`docs/ROUTES.md`](docs/ROUTES.md); kịch bản thuyết trình xem tại [`docs/DEFENSE_GUIDE.md`](docs/DEFENSE_GUIDE.md).
