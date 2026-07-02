# 03 — Thiết kế cơ sở dữ liệu

Tài liệu này diễn giải chi tiết 16 bảng trong cơ sở dữ liệu MilkMart: ý nghĩa các cột, khóa chính/khóa ngoại và quan hệ giữa các bảng.

## 1. `roles` — Vai trò người dùng

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh vai trò |
| name | varchar(50) | NOT NULL, UNIQUE | Tên vai trò (Customer, Admin, Staff...) |
| description | varchar(255) | | Mô tả quyền hạn |

**Quan hệ**: `roles` (1) — (n) `users` qua `users.role_id`.

## 2. `users` — Người dùng hệ thống

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh người dùng |
| role_id | bigint | NOT NULL, FK → `roles.id` | Vai trò của người dùng |
| fullname | varchar(255) | | Họ tên |
| email | varchar(255) | UNIQUE | Email đăng nhập |
| phone | varchar(20) | | Số điện thoại |
| password_hash | varchar(255) | | Mật khẩu đã mã hóa |
| address | text | | Địa chỉ giao hàng mặc định |
| status | varchar(20) | | Trạng thái tài khoản (active/locked...) |
| created_at | datetime | | Ngày tạo tài khoản |

**Quan hệ**: `users` (1) — (n) `carts`, `invoices`, `voucher_usage`, `goods_receipts` (qua `created_by`).

## 3. `brands` — Thương hiệu

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh thương hiệu |
| name | varchar(255) | NOT NULL | Tên thương hiệu |
| description | text | | Mô tả |
| logo_url | varchar(500) | | Đường dẫn logo |

**Quan hệ**: `brands` (1) — (n) `products` qua `products.brand_id`.

## 4. `categories` — Danh mục sản phẩm

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh danh mục |
| name | varchar(255) | NOT NULL | Tên danh mục |
| description | text | | Mô tả |

**Quan hệ**: `categories` (1) — (n) `products` qua `products.category_id`.

## 5. `products` — Sản phẩm

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh sản phẩm |
| category_id | bigint | NOT NULL, FK → `categories.id` | Danh mục sản phẩm |
| brand_id | bigint | NOT NULL, FK → `brands.id` | Thương hiệu |
| name | varchar(255) | NOT NULL | Tên sản phẩm |
| description | text | | Mô tả chi tiết |
| created_at | datetime | | Ngày tạo |

**Quan hệ**: `products` (1) — (n) `product_variants`. Mỗi sản phẩm có thể có nhiều biến thể (khác dung tích/trọng lượng, giá...).

## 6. `product_variants` — Biến thể sản phẩm

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh biến thể |
| product_id | bigint | NOT NULL, FK → `products.id` | Sản phẩm gốc |
| sku | varchar(50) | UNIQUE | Mã SKU |
| weight | decimal(10,2) | | Trọng lượng/dung tích |
| price | decimal(15,2) | | Giá bán |
| stock_quantity | int | | Số lượng tồn kho |
| image_url | varchar(500) | | Ảnh đại diện nhanh |
| status | varchar(20) | | Trạng thái (đang bán/ngừng bán) |
| created_at | datetime | | Ngày tạo |

**Quan hệ**: đây là bảng trung tâm, được tham chiếu bởi `product_images`, `cart_items`, `invoice_details`, `goods_receipt_details`. Đây cũng là đơn vị thực sự được mua bán, đặt hàng và nhập kho (không phải `products`).

## 7. `product_images` — Hình ảnh sản phẩm

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh ảnh |
| product_variant_id | bigint | NOT NULL, FK → `product_variants.id` | Biến thể sản phẩm |
| image_url | varchar(500) | | Đường dẫn ảnh |
| is_primary | boolean | | Ảnh đại diện hay không |

**Quan hệ**: `product_variants` (1) — (n) `product_images`.

## 8. `carts` — Giỏ hàng

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh giỏ hàng |
| user_id | bigint | NOT NULL, FK → `users.id` | Chủ giỏ hàng |
| created_at | datetime | | Ngày tạo |

**Quan hệ**: `users` (1) — (1..n) `carts`; `carts` (1) — (n) `cart_items`.

## 9. `cart_items` — Sản phẩm trong giỏ hàng

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh dòng giỏ hàng |
| cart_id | bigint | NOT NULL, FK → `carts.id` | Giỏ hàng chứa |
| product_variant_id | bigint | NOT NULL, FK → `product_variants.id` | Biến thể được thêm |
| quantity | int | | Số lượng |

**Quan hệ**: bảng trung gian n-n giữa `carts` và `product_variants`.

## 10. `invoices` — Hóa đơn/đơn hàng

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh đơn hàng |
| invoice_code | varchar(50) | UNIQUE | Mã đơn hàng hiển thị |
| user_id | bigint | NOT NULL, FK → `users.id` | Khách đặt hàng |
| voucher_id | bigint | FK → `vouchers.id` (nullable) | Voucher áp dụng (nếu có) |
| subtotal | decimal(15,2) | | Tổng tiền hàng trước giảm giá |
| discount_amount | decimal(15,2) | | Số tiền giảm giá |
| total_amount | decimal(15,2) | | Tổng tiền thanh toán |
| payment_method | varchar(50) | | Phương thức thanh toán |
| payment_status | varchar(30) | | Trạng thái thanh toán |
| order_status | varchar(30) | | Trạng thái đơn hàng |
| shipping_address | text | | Địa chỉ giao hàng |
| created_at | datetime | | Ngày tạo đơn |

**Quan hệ**: `users` (1) — (n) `invoices`; `vouchers` (1) — (n) `invoices`; `invoices` (1) — (n) `invoice_details`; `invoices` (1) — (0..1) `voucher_usage`.

## 11. `invoice_details` — Chi tiết đơn hàng

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh dòng chi tiết |
| invoice_id | bigint | NOT NULL, FK → `invoices.id` | Đơn hàng |
| product_variant_id | bigint | NOT NULL, FK → `product_variants.id` | Biến thể sản phẩm đã mua |
| quantity | int | | Số lượng mua |
| unit_price | decimal(15,2) | | Đơn giá tại thời điểm mua |
| total_price | decimal(15,2) | | Thành tiền |

**Quan hệ**: bảng trung gian n-n giữa `invoices` và `product_variants`, lưu lại giá tại thời điểm giao dịch (không phụ thuộc giá hiện tại của `product_variants`).

## 12. `vouchers` — Mã giảm giá

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh voucher |
| code | varchar(50) | UNIQUE | Mã voucher |
| discount_type | varchar(20) | | Loại giảm giá (%, số tiền cố định) |
| discount_value | decimal(15,2) | | Giá trị giảm |
| max_discount | decimal(15,2) | | Giảm tối đa |
| min_order_value | decimal(15,2) | | Giá trị đơn tối thiểu để áp dụng |
| quantity | int | | Số lượng mã còn có thể sử dụng |
| start_date | datetime | | Ngày bắt đầu hiệu lực |
| end_date | datetime | | Ngày hết hiệu lực |
| status | varchar(20) | | Trạng thái (active/inactive) |

**Quan hệ**: `vouchers` (1) — (n) `invoices`; `vouchers` (1) — (n) `voucher_usage`.

## 13. `voucher_usage` — Lịch sử sử dụng voucher

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh lượt sử dụng |
| voucher_id | bigint | NOT NULL, FK → `vouchers.id` | Voucher được dùng |
| user_id | bigint | NOT NULL, FK → `users.id` | Người dùng sử dụng |
| invoice_id | bigint | NOT NULL, FK → `invoices.id` | Đơn hàng áp dụng |
| used_at | datetime | | Thời điểm sử dụng |

**Quan hệ**: bảng trung gian ghi nhận quan hệ n-n giữa `vouchers` và `users` (một người có thể dùng nhiều voucher, một voucher dùng bởi nhiều người, tùy theo `quantity` cho phép), đồng thời liên kết 1-1 với `invoices` tương ứng.

## 14. `suppliers` — Nhà cung cấp

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh nhà cung cấp |
| name | varchar(255) | | Tên nhà cung cấp |
| phone | varchar(20) | | Số điện thoại |
| email | varchar(255) | | Email liên hệ |
| address | text | | Địa chỉ |

**Quan hệ**: `suppliers` (1) — (n) `goods_receipts`.

## 15. `goods_receipts` — Phiếu nhập hàng

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh phiếu nhập |
| receipt_code | varchar(50) | UNIQUE | Mã phiếu nhập |
| supplier_id | bigint | NOT NULL, FK → `suppliers.id` | Nhà cung cấp |
| created_by | bigint | NOT NULL, FK → `users.id` | Nhân viên lập phiếu |
| total_amount | decimal(15,2) | | Tổng giá trị nhập |
| created_at | datetime | | Ngày lập phiếu |

**Quan hệ**: `suppliers` (1) — (n) `goods_receipts`; `users` (1) — (n) `goods_receipts` (qua `created_by`); `goods_receipts` (1) — (n) `goods_receipt_details`.

## 16. `goods_receipt_details` — Chi tiết phiếu nhập hàng

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | bigint | PK | Định danh dòng chi tiết |
| receipt_id | bigint | NOT NULL, FK → `goods_receipts.id` | Phiếu nhập |
| product_variant_id | bigint | NOT NULL, FK → `product_variants.id` | Biến thể được nhập |
| quantity | int | | Số lượng nhập |
| import_price | decimal(15,2) | | Giá nhập |
| total_price | decimal(15,2) | | Thành tiền |

**Quan hệ**: bảng trung gian n-n giữa `goods_receipts` và `product_variants`. Sau khi tạo phiếu nhập, `quantity` được cộng dồn vào `product_variants.stock_quantity`.

## Tổng kết quan hệ chính

| Quan hệ | Loại | Bảng trung gian |
|---|---|---|
| roles — users | 1-n | — |
| categories/brands — products | 1-n | — |
| products — product_variants | 1-n | — |
| product_variants — product_images | 1-n | — |
| users — carts | 1-n | — |
| carts — product_variants | n-n | `cart_items` |
| users — invoices | 1-n | — |
| vouchers — invoices | 1-n | — |
| invoices — product_variants | n-n | `invoice_details` |
| vouchers — users (qua invoices) | n-n | `voucher_usage` |
| suppliers — goods_receipts | 1-n | — |
| users — goods_receipts (created_by) | 1-n | — |
| goods_receipts — product_variants | n-n | `goods_receipt_details` |
