# 06 — Bản nháp danh sách API/Route Endpoints

> Ghi chú: Với kiến trúc Laravel + Inertia.js (xem [05-tech-stack.md](05-tech-stack.md)), phần lớn các route dưới đây trả về Inertia response (render trang Vue kèm dữ liệu) thay vì JSON thuần túy. Danh sách được trình bày theo dạng REST-style path để mô tả rõ luồng nghiệp vụ, và làm cơ sở nếu sau này bổ sung lớp API JSON riêng (ví dụ cho mobile app).

## 1. Auth (Xác thực)

| Method | Path | Mô tả |
|---|---|---|
| GET | `/register` | Hiển thị trang đăng ký |
| POST | `/register` | Xử lý đăng ký tài khoản mới |
| GET | `/login` | Hiển thị trang đăng nhập |
| POST | `/login` | Xử lý đăng nhập |
| POST | `/logout` | Đăng xuất |
| POST | `/forgot-password` | Gửi yêu cầu đặt lại mật khẩu |
| POST | `/reset-password` | Đặt lại mật khẩu |

## 2. Users / Profile (Người dùng & hồ sơ)

| Method | Path | Mô tả |
|---|---|---|
| GET | `/profile` | Xem trang hồ sơ cá nhân |
| PUT | `/profile` | Cập nhật thông tin cá nhân, địa chỉ |
| GET | `/admin/users` | (Admin) Danh sách người dùng |
| GET | `/admin/users/{id}` | (Admin) Chi tiết người dùng |
| PUT | `/admin/users/{id}` | (Admin) Cập nhật vai trò/trạng thái người dùng |
| GET | `/admin/roles` | (Admin) Danh sách vai trò |
| POST | `/admin/roles` | (Admin) Tạo vai trò mới |
| PUT | `/admin/roles/{id}` | (Admin) Cập nhật vai trò |

## 3. Products (Sản phẩm) — Public

| Method | Path | Mô tả |
|---|---|---|
| GET | `/products` | Danh sách sản phẩm, hỗ trợ lọc theo danh mục/thương hiệu, tìm kiếm |
| GET | `/products/{id}` | Chi tiết sản phẩm, danh sách biến thể và hình ảnh |
| GET | `/categories` | Danh sách danh mục |
| GET | `/brands` | Danh sách thương hiệu |

## 4. Products / Categories / Brands — Admin

| Method | Path | Mô tả |
|---|---|---|
| GET | `/admin/products` | (Admin) Danh sách sản phẩm quản trị |
| POST | `/admin/products` | (Admin) Tạo sản phẩm mới |
| PUT | `/admin/products/{id}` | (Admin) Cập nhật sản phẩm |
| DELETE | `/admin/products/{id}` | (Admin) Xóa sản phẩm |
| POST | `/admin/products/{id}/variants` | (Admin) Thêm biến thể sản phẩm |
| PUT | `/admin/variants/{id}` | (Admin) Cập nhật biến thể (giá, tồn kho, trạng thái) |
| DELETE | `/admin/variants/{id}` | (Admin) Xóa biến thể |
| POST | `/admin/variants/{id}/images` | (Admin) Upload hình ảnh cho biến thể |
| DELETE | `/admin/images/{id}` | (Admin) Xóa hình ảnh |
| PUT | `/admin/images/{id}/primary` | (Admin) Đặt ảnh đại diện |
| POST | `/admin/categories` | (Admin) Tạo danh mục |
| PUT | `/admin/categories/{id}` | (Admin) Cập nhật danh mục |
| DELETE | `/admin/categories/{id}` | (Admin) Xóa danh mục |
| POST | `/admin/brands` | (Admin) Tạo thương hiệu |
| PUT | `/admin/brands/{id}` | (Admin) Cập nhật thương hiệu |
| DELETE | `/admin/brands/{id}` | (Admin) Xóa thương hiệu |

## 5. Cart (Giỏ hàng)

| Method | Path | Mô tả |
|---|---|---|
| GET | `/cart` | Xem giỏ hàng hiện tại |
| POST | `/cart/items` | Thêm sản phẩm vào giỏ hàng |
| PUT | `/cart/items/{id}` | Cập nhật số lượng sản phẩm trong giỏ |
| DELETE | `/cart/items/{id}` | Xóa sản phẩm khỏi giỏ hàng |

## 6. Checkout / Invoices (Đặt hàng & đơn hàng)

| Method | Path | Mô tả |
|---|---|---|
| POST | `/checkout` | Tạo đơn hàng từ giỏ hàng (áp dụng voucher nếu có) |
| GET | `/orders` | Danh sách đơn hàng của khách hàng hiện tại |
| GET | `/orders/{id}` | Chi tiết đơn hàng |
| POST | `/orders/{id}/cancel` | Hủy đơn hàng (khi chưa xử lý) |
| GET | `/admin/orders` | (Admin/Staff) Danh sách toàn bộ đơn hàng |
| GET | `/admin/orders/{id}` | (Admin/Staff) Chi tiết đơn hàng |
| PUT | `/admin/orders/{id}/payment-status` | (Admin/Staff) Cập nhật trạng thái thanh toán |
| PUT | `/admin/orders/{id}/order-status` | (Admin/Staff) Cập nhật trạng thái đơn hàng |

## 7. Vouchers (Khuyến mãi)

| Method | Path | Mô tả |
|---|---|---|
| POST | `/cart/apply-voucher` | Áp dụng mã voucher vào giỏ hàng/đơn hàng đang tạo |
| GET | `/admin/vouchers` | (Admin) Danh sách voucher |
| POST | `/admin/vouchers` | (Admin) Tạo voucher mới |
| PUT | `/admin/vouchers/{id}` | (Admin) Cập nhật voucher |
| DELETE | `/admin/vouchers/{id}` | (Admin) Xóa/khóa voucher |
| GET | `/admin/vouchers/{id}/usage` | (Admin) Lịch sử sử dụng của một voucher |

## 8. Suppliers / Goods Receipts (Nhà cung cấp & nhập hàng)

| Method | Path | Mô tả |
|---|---|---|
| GET | `/admin/suppliers` | (Admin/Staff) Danh sách nhà cung cấp |
| POST | `/admin/suppliers` | (Admin/Staff) Thêm nhà cung cấp |
| PUT | `/admin/suppliers/{id}` | (Admin/Staff) Cập nhật nhà cung cấp |
| DELETE | `/admin/suppliers/{id}` | (Admin/Staff) Xóa nhà cung cấp |
| GET | `/admin/goods-receipts` | (Admin/Staff) Danh sách phiếu nhập hàng |
| GET | `/admin/goods-receipts/{id}` | (Admin/Staff) Chi tiết phiếu nhập hàng |
| POST | `/admin/goods-receipts` | (Staff) Tạo phiếu nhập hàng mới (kèm chi tiết sản phẩm, tự động cập nhật tồn kho) |

## 9. Reports (Báo cáo & thống kê) — Admin

| Method | Path | Mô tả |
|---|---|---|
| GET | `/admin/reports/revenue` | Báo cáo doanh thu theo khoảng thời gian |
| GET | `/admin/reports/inventory` | Báo cáo tồn kho hiện tại |
| GET | `/admin/reports/best-sellers` | Danh sách sản phẩm bán chạy |
| GET | `/admin/reports/vouchers` | Báo cáo hiệu quả sử dụng voucher |
| GET | `/admin/reports/goods-receipts` | Báo cáo giá trị nhập hàng theo thời gian/nhà cung cấp |
