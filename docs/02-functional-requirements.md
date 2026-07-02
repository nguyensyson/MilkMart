# 02 — Yêu cầu chức năng

Tài liệu này liệt kê các chức năng chi tiết của hệ thống MilkMart, chia theo 7 module nghiệp vụ. Mỗi chức năng gồm: mô tả, actor sử dụng, input/output cơ bản.

## 1. Quản lý người dùng & phân quyền

Liên quan bảng: `roles`, `users`.

| Chức năng | Mô tả | Actor | Input | Output |
|---|---|---|---|---|
| Đăng ký tài khoản | Khách hàng tạo tài khoản mới bằng email/số điện thoại | Khách hàng | Họ tên, email, mật khẩu, số điện thoại | Tài khoản mới (`users`, `role_id` = Customer), trạng thái `status` |
| Đăng nhập | Xác thực tài khoản để truy cập hệ thống | Khách hàng, Admin, Staff | Email/mật khẩu | Phiên đăng nhập (session/token) |
| Quản lý hồ sơ cá nhân | Xem/cập nhật thông tin cá nhân, địa chỉ nhận hàng | Khách hàng | Họ tên, số điện thoại, địa chỉ | Thông tin `users` được cập nhật |
| Quản lý vai trò (roles) | Tạo/sửa các vai trò và mô tả quyền hạn | Admin | Tên vai trò, mô tả | Bản ghi `roles` |
| Quản lý tài khoản người dùng | Xem danh sách, khóa/mở khóa tài khoản, gán vai trò | Admin | `user_id`, `role_id`, `status` | Trạng thái tài khoản cập nhật |
| Phân quyền chức năng | Giới hạn thao tác theo vai trò (Customer/Admin/Staff) | Hệ thống | `role_id` của người dùng | Cho phép/từ chối truy cập chức năng |

## 2. Quản lý sản phẩm

Liên quan bảng: `brands`, `categories`, `products`, `product_variants`, `product_images`.

| Chức năng | Mô tả | Actor | Input | Output |
|---|---|---|---|---|
| Quản lý thương hiệu | CRUD thương hiệu sữa (tên, mô tả, logo) | Admin | Tên, mô tả, `logo_url` | Bản ghi `brands` |
| Quản lý danh mục | CRUD danh mục sản phẩm (sữa tươi, sữa bột...) | Admin | Tên, mô tả | Bản ghi `categories` |
| Quản lý sản phẩm | CRUD sản phẩm, gắn danh mục và thương hiệu | Admin | Tên, mô tả, `category_id`, `brand_id` | Bản ghi `products` |
| Quản lý biến thể sản phẩm | CRUD biến thể theo SKU (dung tích/trọng lượng, giá, tồn kho) | Admin | `product_id`, `sku`, `weight`, `price`, `stock_quantity` | Bản ghi `product_variants` |
| Quản lý hình ảnh sản phẩm | Upload/xóa ảnh cho từng biến thể, chọn ảnh đại diện | Admin | File ảnh, `product_variant_id`, `is_primary` | Bản ghi `product_images` |
| Duyệt/tìm kiếm sản phẩm | Xem danh sách, lọc theo danh mục/thương hiệu, tìm kiếm theo tên | Khách hàng | Từ khóa, bộ lọc | Danh sách `products`/`product_variants` phù hợp |
| Xem chi tiết sản phẩm | Xem thông tin chi tiết, các biến thể, hình ảnh, tồn kho | Khách hàng | `product_id` | Chi tiết sản phẩm và biến thể |

## 3. Giỏ hàng & đặt hàng

Liên quan bảng: `carts`, `cart_items`, `invoices`, `invoice_details`.

| Chức năng | Mô tả | Actor | Input | Output |
|---|---|---|---|---|
| Thêm vào giỏ hàng | Thêm biến thể sản phẩm vào giỏ hàng | Khách hàng | `product_variant_id`, `quantity` | Bản ghi `cart_items` |
| Cập nhật giỏ hàng | Thay đổi số lượng hoặc xóa sản phẩm trong giỏ | Khách hàng | `cart_item_id`, `quantity` mới | `cart_items` được cập nhật/xóa |
| Xem giỏ hàng | Xem danh sách sản phẩm, tổng tiền tạm tính | Khách hàng | `cart_id` | Danh sách `cart_items` kèm tổng tiền |
| Đặt hàng (checkout) | Tạo đơn hàng từ giỏ hàng, chọn địa chỉ giao hàng | Khách hàng | Giỏ hàng, `shipping_address`, voucher (nếu có) | Bản ghi `invoices` + `invoice_details` |
| Xem lịch sử đơn hàng | Xem danh sách đơn đã đặt và trạng thái | Khách hàng | `user_id` | Danh sách `invoices` |
| Xem chi tiết đơn hàng | Xem chi tiết sản phẩm, số lượng, đơn giá trong đơn | Khách hàng, Admin, Staff | `invoice_id` | Danh sách `invoice_details` |

## 4. Thanh toán & trạng thái đơn hàng

Liên quan bảng: `invoices` (`payment_method`, `payment_status`, `order_status`).

| Chức năng | Mô tả | Actor | Input | Output |
|---|---|---|---|---|
| Chọn phương thức thanh toán | Chọn COD, chuyển khoản... khi đặt hàng | Khách hàng | `payment_method` | Cập nhật `invoices.payment_method` |
| Cập nhật trạng thái thanh toán | Đánh dấu đơn đã/chưa thanh toán | Admin, Staff | `invoice_id`, `payment_status` | Cập nhật `invoices.payment_status` |
| Cập nhật trạng thái đơn hàng | Chuyển trạng thái: chờ xử lý → đang giao → hoàn tất/hủy | Admin, Staff | `invoice_id`, `order_status` | Cập nhật `invoices.order_status` |
| Theo dõi đơn hàng | Xem trạng thái đơn hàng theo thời gian thực | Khách hàng | `invoice_id`/`invoice_code` | Trạng thái hiện tại của đơn |
| Hủy đơn hàng | Hủy đơn khi chưa xử lý/giao hàng | Khách hàng, Admin | `invoice_id` | `order_status` = Đã hủy |

## 5. Khuyến mãi / voucher

Liên quan bảng: `vouchers`, `voucher_usage`.

| Chức năng | Mô tả | Actor | Input | Output |
|---|---|---|---|---|
| Tạo voucher | Tạo mã giảm giá với điều kiện áp dụng | Admin | `code`, `discount_type`, `discount_value`, `max_discount`, `min_order_value`, `quantity`, `start_date`, `end_date` | Bản ghi `vouchers` |
| Quản lý voucher | Sửa/khóa voucher, theo dõi số lượng còn lại | Admin | `voucher_id`, `status` | `vouchers` được cập nhật |
| Áp dụng voucher | Nhập mã voucher khi đặt hàng, kiểm tra điều kiện | Khách hàng | `code`, giá trị đơn hàng | Giảm giá áp dụng vào `invoices.discount_amount` |
| Kiểm tra điều kiện voucher | Kiểm tra hạn dùng, số lượng, giá trị đơn tối thiểu | Hệ thống | `voucher_id`, `subtotal`, `used_at` | Chấp nhận/từ chối áp dụng |
| Ghi nhận lượt sử dụng voucher | Lưu lại lịch sử sử dụng voucher theo từng đơn | Hệ thống | `voucher_id`, `user_id`, `invoice_id` | Bản ghi `voucher_usage` |

## 6. Quản lý kho & nhập hàng từ nhà cung cấp

Liên quan bảng: `suppliers`, `goods_receipts`, `goods_receipt_details`.

| Chức năng | Mô tả | Actor | Input | Output |
|---|---|---|---|---|
| Quản lý nhà cung cấp | CRUD thông tin nhà cung cấp | Admin, Staff | Tên, số điện thoại, email, địa chỉ | Bản ghi `suppliers` |
| Tạo phiếu nhập hàng | Lập phiếu nhập hàng từ nhà cung cấp | Staff | `supplier_id`, `created_by`, danh sách sản phẩm nhập | Bản ghi `goods_receipts` + `goods_receipt_details` |
| Cập nhật tồn kho sau nhập | Cộng số lượng nhập vào `stock_quantity` của biến thể | Hệ thống | `goods_receipt_details` | `product_variants.stock_quantity` cập nhật |
| Xem lịch sử nhập hàng | Xem danh sách phiếu nhập theo thời gian/nhà cung cấp | Admin, Staff | Bộ lọc thời gian, `supplier_id` | Danh sách `goods_receipts` |
| Xem chi tiết phiếu nhập | Xem chi tiết sản phẩm, số lượng, giá nhập | Admin, Staff | `receipt_id` | Danh sách `goods_receipt_details` |

## 7. Báo cáo & thống kê

Liên quan bảng: `invoices`, `invoice_details`, `product_variants`, `goods_receipts`, `vouchers`.

| Chức năng | Mô tả | Actor | Input | Output |
|---|---|---|---|---|
| Báo cáo doanh thu | Thống kê doanh thu theo ngày/tháng/năm | Admin | Khoảng thời gian | Tổng doanh thu, số đơn hàng |
| Báo cáo tồn kho | Xem tồn kho hiện tại theo sản phẩm/biến thể | Admin, Staff | Bộ lọc danh mục/thương hiệu | Danh sách tồn kho theo `product_variants` |
| Sản phẩm bán chạy | Xếp hạng sản phẩm theo số lượng/bán ra | Admin | Khoảng thời gian | Top sản phẩm bán chạy |
| Hiệu quả voucher | Thống kê số lượt dùng, doanh thu từ đơn có voucher | Admin | `voucher_id`/khoảng thời gian | Số liệu sử dụng voucher |
| Báo cáo nhập hàng | Thống kê giá trị nhập hàng theo nhà cung cấp/thời gian | Admin | Khoảng thời gian, `supplier_id` | Tổng giá trị nhập hàng |
