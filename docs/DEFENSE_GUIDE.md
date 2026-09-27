# Hướng dẫn bảo vệ đồ án Shirt Store

## 1. Kiến trúc cần nhớ

Laravel MVC tách ba phần: **Model** ánh xạ bảng và quan hệ; **View** dùng Blade để hiển thị; **Controller** nhận request, validate và gọi **Service** xử lý nghiệp vụ. Route gắn middleware trước khi request tới controller. Nghiệp vụ nhạy cảm như checkout, tồn kho, coupon và báo cáo nằm trong `app/Services`, không đặt trong Blade.

Ba vai trò: `customer` mua hàng; `admin` vận hành; `super_admin` có quyền quản trị cao nhất. Middleware chặn customer vào `/admin/*`, chặn admin/super admin thực hiện luồng mua hàng và chặn tài khoản `locked`.

## 2. Bảng dữ liệu quan trọng

- `users`: tài khoản, role, trạng thái, theme.
- `categories`, `products`, `product_images`: danh mục và thông tin sản phẩm; product dùng soft delete.
- `colors`, `sizes`, `product_variants`: mỗi biến thể là một tổ hợp sản phẩm–màu–size; `stock` chỉ nằm ở variant.
- `carts`, `cart_items`, `wishlists`: dữ liệu mua sắm trước checkout.
- `coupons`, `coupon_usages`, `shipping_zones`, `shipping_rates`: giảm giá và giao hàng.
- `orders`, `order_items`, `order_status_logs`: đơn, snapshot dòng hàng và lịch sử trạng thái.
- `reviews`, `banned_words`: đánh giá và kiểm duyệt.
- `settings`, `banners`: cấu hình và banner theo lịch.

## 3. Luồng nghiệp vụ cốt lõi

**Cart:** lưu `product_variant_id` và số lượng, kiểm tra giới hạn kho nhưng không trừ kho. Giá hiển thị được lấy lại từ product hiện tại.

**Checkout:** server đọc giỏ, khóa các variant theo thứ tự ID bằng `lockForUpdate()`, kiểm lại trạng thái/kho, tính lại giá, coupon, phí ship và total. Toàn bộ tạo order, snapshot item, trừ kho, ghi coupon usage và xóa giỏ nằm trong `DB::transaction()`. Có lỗi thì rollback tất cả.

**Chống overselling:** row lock buộc hai checkout cùng variant chạy tuần tự; checkout sau thấy stock mới và bị từ chối nếu thiếu. Câu lệnh giảm kho có điều kiện bảo đảm stock không âm.

**Chống double-submit:** mỗi lần checkout có UUID `checkout_token` unique. Gửi lại cùng token trả về đơn đã có, không tạo đơn thứ hai.

**Coupon:** kiểm tra active, thời gian, đơn tối thiểu, tổng lượt, lượt theo user và mức giảm tối đa. Coupon được khóa khi checkout; server không tin discount từ client.

**Order:** mã sau khi có ID là `ORD{YYYYMMDD}{ID đủ 8 số}`. State machine: `pending → confirmed → packing → shipping → completed`; chỉ `pending/confirmed` có thể hủy theo quyền tương ứng. Khách chỉ hủy `pending`.

**Hủy đơn:** transaction khóa order/variant/coupon; hoàn stock và giảm lượt coupon đúng một lần. Order đã `cancelled` không hoàn lần hai.

**Doanh thu:** dashboard/báo cáo chỉ cộng `orders.total` của đơn `completed`. Order item giữ snapshot tên, SKU, màu, size, giá nên xóa/sửa product không làm sai lịch sử.

**Review:** phải có `order_item` thuộc đơn `completed` của chính user. Review mới là `pending`; chỉ `approved` được công khai và tính rating. Từ cấm và throttle giảm spam.

**Email:** gửi sau khi transaction thành công. Lỗi mail chỉ được log, không rollback trạng thái nghiệp vụ. Reset token hết hạn sau 15 phút; local dùng `MAIL_MAILER=log`.

**Cài đặt:** low-stock lấy từ `settings`, thiếu thì fallback 5. Banner chỉ hiện khi active, nằm trong khoảng ngày và theo `sort_order`. Blade `{{ }}` escape đầu ra; form có CSRF; upload giới hạn loại và kích thước ảnh.

## 4. Câu hỏi giảng viên thường hỏi

1. **Vì sao stock không nằm ở products?** Vì mỗi màu/size có số lượng riêng; variant mới là đơn vị bán thực tế.
2. **Thêm vào giỏ có giữ hàng không?** Không. Cart không trừ kho; kho được xác nhận và trừ khi checkout thành công.
3. **Hai người mua món cuối cùng thì sao?** `lockForUpdate()` khóa cùng dòng variant; chỉ giao dịch đầu đủ kho, giao dịch sau bị từ chối.
4. **Transaction giải quyết gì?** Bảo đảm order, item, stock, coupon và cart cùng thành công hoặc cùng rollback.
5. **Tại sao không nhận total từ trình duyệt?** Client có thể sửa request; server luôn tính lại từ DB.
6. **Ngăn bấm nút đặt hàng hai lần thế nào?** UUID checkout unique và kiểm tra đơn theo token/user.
7. **Hủy đơn có cộng kho hai lần không?** Không; order bị khóa và chỉ trạng thái hợp lệ mới chạy logic hoàn.
8. **Vì sao lưu snapshot order item?** Để hóa đơn cũ không thay đổi khi product đổi giá, tên hoặc bị soft delete.
9. **Doanh thu tính đơn nào?** Chỉ `completed`, loại pending/shipping/cancelled.
10. **Ai được review?** Customer có sản phẩm trong đơn `completed` của chính mình.
11. **Rating công khai tính thế nào?** Chỉ review `approved`; pending/rejected/hidden không tính.
12. **Phân quyền nằm ở đâu?** Middleware cho vùng route, ownership check trong controller/service và validation ở backend.
13. **CSRF và XSS được xử lý thế nào?** Form POST/PATCH/DELETE có `@csrf`; Blade escape bằng `{{ }}`.
14. **Nếu gửi email lỗi thì sao?** Business state vẫn giữ; exception mail được ghi log sau commit.
15. **Ngưỡng sắp hết hàng lấy ở đâu?** Từ settings; không có hoặc lỗi thì dùng 5.

## 5. Kịch bản demo 5–10 phút

1. Mở `/`, chỉ banner, theme, sản phẩm mới/nổi bật/sale; vào `/products` để search, filter, sort.
2. Mở product detail, chọn màu/size để thấy stock; thêm wishlist và cart, nhấn mạnh cart chưa trừ kho.
3. Áp `WELCOME10`, chọn khu vực ship; checkout COD và mở trang thành công/lịch sử đơn.
4. Đăng nhập admin, mở dashboard/reports; chỉ doanh thu completed và cảnh báo low-stock.
5. Mở order vừa tạo, chuyển trạng thái hợp lệ; minh họa state machine. Có thể dùng order pending khác để demo cancel/hoàn kho.
6. Mở inventory để đối chiếu stock theo variant; mở coupon/shipping/settings/banner.
7. Mở reviews: duyệt review pending, quay lại product để thấy review approved và rating công khai.
8. Kết thúc bằng `php artisan test`, nêu kết quả test và `MAIL_MAILER=log` cho môi trường demo.
