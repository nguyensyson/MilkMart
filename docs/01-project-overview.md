# 01 — Tổng quan dự án

## 1. Giới thiệu

**MilkMart** là một website thương mại điện tử (B2C) chuyên bán các sản phẩm sữa và chế phẩm từ sữa (sữa tươi, sữa bột, sữa chua, phô mai...) trực tuyến. Hệ thống cho phép khách hàng duyệt sản phẩm theo danh mục/thương hiệu, đặt hàng, thanh toán và theo dõi đơn hàng; đồng thời cung cấp công cụ cho đội ngũ vận hành quản lý sản phẩm, tồn kho, nhập hàng từ nhà cung cấp và theo dõi doanh thu.

## 2. Mục tiêu dự án

- Xây dựng kênh bán hàng trực tuyến ổn định, dễ sử dụng cho khách hàng cá nhân và hộ gia đình.
- Số hóa quy trình quản lý sản phẩm, tồn kho và nhập hàng, giảm thao tác thủ công.
- Cung cấp công cụ khuyến mãi (voucher) linh hoạt để hỗ trợ hoạt động marketing/bán hàng.
- Cung cấp báo cáo, thống kê giúp quản trị viên ra quyết định kinh doanh (doanh thu, tồn kho, sản phẩm bán chạy).

## 3. Đối tượng người dùng (Actors)

| Vai trò | Mô tả | Nhu cầu chính |
|---|---|---|
| **Khách hàng (Customer)** | Người mua hàng trên website | Tìm kiếm sản phẩm, đặt hàng, thanh toán, theo dõi đơn hàng, sử dụng voucher |
| **Quản trị viên (Admin)** | Người quản lý toàn bộ hệ thống | Quản lý người dùng, sản phẩm, danh mục, voucher, xem báo cáo tổng thể |
| **Nhân viên (Staff)** | Nhân viên bán hàng/kho | Xử lý đơn hàng, quản lý nhập hàng từ nhà cung cấp, cập nhật tồn kho |

Việc phân quyền giữa các vai trò được quản lý thông qua bảng `roles` và trường `role_id` trong bảng `users`.

## 4. Phạm vi dự án

### 4.1. Phạm vi MVP (giai đoạn 1)

- Đăng ký, đăng nhập, quản lý hồ sơ cá nhân, phân quyền theo vai trò.
- Quản lý danh mục (`categories`), thương hiệu (`brands`), sản phẩm (`products`) và biến thể sản phẩm (`product_variants`, `product_images`).
- Giỏ hàng (`carts`, `cart_items`) và đặt hàng (`invoices`, `invoice_details`).
- Thanh toán cơ bản (COD, chuyển khoản) và theo dõi trạng thái đơn hàng.
- Áp dụng và quản lý voucher giảm giá (`vouchers`, `voucher_usage`).
- Quản lý nhà cung cấp và phiếu nhập hàng (`suppliers`, `goods_receipts`, `goods_receipt_details`) để cập nhật tồn kho.
- Báo cáo cơ bản: doanh thu theo thời gian, tồn kho hiện tại, sản phẩm bán chạy.

### 4.2. Phạm vi mở rộng (giai đoạn sau)

- Tích hợp đa cổng thanh toán trực tuyến (VNPay, Momo, thẻ tín dụng...).
- Ứng dụng di động (mobile app) cho khách hàng.
- Chương trình khách hàng thân thiết / tích điểm.
- Đánh giá, bình luận sản phẩm (review & rating).
- Chatbot hỗ trợ khách hàng, gợi ý sản phẩm cá nhân hóa.
- Tích hợp đơn vị vận chuyển (tracking vận đơn tự động).
- Báo cáo nâng cao (dự báo nhu cầu, phân tích hành vi khách hàng).

## 5. Ràng buộc & giả định

- Dữ liệu nghiệp vụ dựa trên thiết kế cơ sở dữ liệu đã có sẵn (16 bảng, xem [03-database-schema.md](03-database-schema.md)).
- Hệ thống được xây dựng mới hoàn toàn (dự án greenfield), chưa có code nền tảng trước đó.
- Công nghệ sử dụng được mô tả chi tiết tại [05-tech-stack.md](05-tech-stack.md).
