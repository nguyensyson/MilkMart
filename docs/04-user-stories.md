# 04 — User Stories

Định dạng: *"Là [vai trò], tôi muốn [hành động], để [mục đích]"*. Ưu tiên theo MoSCoW: **Must have**, **Should have**, **Nice to have**.

## 1. Quản lý người dùng & phân quyền

| STT | User Story | Vai trò | Ưu tiên |
|---|---|---|---|
| 1 | Là khách hàng, tôi muốn đăng ký tài khoản, để tôi có thể đặt hàng trên hệ thống | Khách hàng | Must have |
| 2 | Là khách hàng, tôi muốn đăng nhập bằng email/mật khẩu, để truy cập tài khoản của mình | Khách hàng | Must have |
| 3 | Là khách hàng, tôi muốn cập nhật thông tin cá nhân và địa chỉ giao hàng, để đơn hàng được giao đúng nơi | Khách hàng | Must have |
| 4 | Là admin, tôi muốn quản lý danh sách người dùng và khóa/mở tài khoản, để kiểm soát truy cập hệ thống | Admin | Should have |
| 5 | Là admin, tôi muốn phân quyền theo vai trò (Customer/Admin/Staff), để mỗi người chỉ thao tác đúng phạm vi | Admin | Must have |
| 6 | Là khách hàng, tôi muốn đặt lại mật khẩu khi quên, để không bị mất quyền truy cập tài khoản | Khách hàng | Should have |

## 2. Quản lý sản phẩm

| STT | User Story | Vai trò | Ưu tiên |
|---|---|---|---|
| 7 | Là khách hàng, tôi muốn xem danh sách sản phẩm theo danh mục/thương hiệu, để dễ dàng tìm sản phẩm cần mua | Khách hàng | Must have |
| 8 | Là khách hàng, tôi muốn tìm kiếm sản phẩm theo tên, để nhanh chóng tìm được sản phẩm mong muốn | Khách hàng | Must have |
| 9 | Là khách hàng, tôi muốn xem chi tiết sản phẩm gồm các biến thể, giá, hình ảnh, để đưa ra quyết định mua hàng | Khách hàng | Must have |
| 10 | Là admin, tôi muốn thêm/sửa/xóa sản phẩm và biến thể sản phẩm, để quản lý danh mục hàng hóa | Admin | Must have |
| 11 | Là admin, tôi muốn quản lý thương hiệu và danh mục sản phẩm, để tổ chức sản phẩm một cách khoa học | Admin | Must have |
| 12 | Là admin, tôi muốn upload nhiều hình ảnh cho mỗi biến thể và chọn ảnh đại diện, để sản phẩm hiển thị trực quan hơn | Admin | Should have |
| 13 | Là admin, tôi muốn cập nhật trạng thái ngừng bán cho biến thể sản phẩm, để ẩn sản phẩm hết hàng khỏi website | Admin | Should have |

## 3. Giỏ hàng & đặt hàng

| STT | User Story | Vai trò | Ưu tiên |
|---|---|---|---|
| 14 | Là khách hàng, tôi muốn thêm sản phẩm vào giỏ hàng, để gom nhiều sản phẩm trước khi thanh toán | Khách hàng | Must have |
| 15 | Là khách hàng, tôi muốn thay đổi số lượng hoặc xóa sản phẩm khỏi giỏ hàng, để điều chỉnh đơn hàng trước khi đặt | Khách hàng | Must have |
| 16 | Là khách hàng, tôi muốn đặt hàng từ giỏ hàng và chọn địa chỉ giao hàng, để hoàn tất việc mua sắm | Khách hàng | Must have |
| 17 | Là khách hàng, tôi muốn xem lại lịch sử đơn hàng đã đặt, để theo dõi các đơn mua trước đó | Khách hàng | Should have |
| 18 | Là admin/nhân viên, tôi muốn xem chi tiết đơn hàng của khách, để xử lý và đóng gói hàng chính xác | Admin, Staff | Must have |

## 4. Thanh toán & trạng thái đơn hàng

| STT | User Story | Vai trò | Ưu tiên |
|---|---|---|---|
| 19 | Là khách hàng, tôi muốn chọn phương thức thanh toán (COD/chuyển khoản), để thanh toán theo cách thuận tiện nhất | Khách hàng | Must have |
| 20 | Là nhân viên, tôi muốn cập nhật trạng thái thanh toán của đơn hàng, để theo dõi công nợ và dòng tiền | Staff | Must have |
| 21 | Là nhân viên, tôi muốn cập nhật trạng thái đơn hàng (đang xử lý/đang giao/hoàn tất), để khách hàng nắm được tiến độ | Staff | Must have |
| 22 | Là khách hàng, tôi muốn theo dõi trạng thái đơn hàng của mình theo thời gian thực, để biết khi nào nhận được hàng | Khách hàng | Should have |
| 23 | Là khách hàng, tôi muốn hủy đơn hàng khi chưa được xử lý, để tránh nhận hàng không mong muốn | Khách hàng | Should have |

## 5. Khuyến mãi / voucher

| STT | User Story | Vai trò | Ưu tiên |
|---|---|---|---|
| 24 | Là admin, tôi muốn tạo mã voucher với điều kiện áp dụng (giá trị đơn tối thiểu, hạn dùng, số lượng), để triển khai chương trình khuyến mãi | Admin | Should have |
| 25 | Là khách hàng, tôi muốn nhập mã voucher khi đặt hàng, để được giảm giá đơn hàng | Khách hàng | Should have |
| 26 | Là hệ thống, tôi cần kiểm tra điều kiện voucher (hạn dùng, số lượng còn lại, giá trị đơn tối thiểu) trước khi áp dụng, để đảm bảo tính đúng đắn của khuyến mãi | Hệ thống | Should have |
| 27 | Là admin, tôi muốn xem thống kê số lượt sử dụng của từng voucher, để đánh giá hiệu quả chương trình khuyến mãi | Admin | Nice to have |

## 6. Quản lý kho & nhập hàng

| STT | User Story | Vai trò | Ưu tiên |
|---|---|---|---|
| 28 | Là admin/nhân viên, tôi muốn quản lý danh sách nhà cung cấp, để thuận tiện khi lập phiếu nhập hàng | Admin, Staff | Should have |
| 29 | Là nhân viên, tôi muốn tạo phiếu nhập hàng từ nhà cung cấp, để cập nhật hàng hóa nhập kho | Staff | Must have |
| 30 | Là hệ thống, tôi cần tự động cộng số lượng tồn kho khi phiếu nhập được xác nhận, để tồn kho luôn chính xác | Hệ thống | Must have |
| 31 | Là admin, tôi muốn xem lịch sử các phiếu nhập hàng theo thời gian hoặc nhà cung cấp, để kiểm soát chi phí nhập hàng | Admin | Should have |

## 7. Báo cáo & thống kê

| STT | User Story | Vai trò | Ưu tiên |
|---|---|---|---|
| 32 | Là admin, tôi muốn xem báo cáo doanh thu theo ngày/tháng/năm, để đánh giá hiệu quả kinh doanh | Admin | Should have |
| 33 | Là admin, tôi muốn xem báo cáo tồn kho hiện tại, để lên kế hoạch nhập hàng kịp thời | Admin | Should have |
| 34 | Là admin, tôi muốn xem danh sách sản phẩm bán chạy nhất, để điều chỉnh chiến lược kinh doanh | Admin | Nice to have |
| 35 | Là admin, tôi muốn xem báo cáo hiệu quả các voucher đã triển khai, để tối ưu ngân sách khuyến mãi | Admin | Nice to have |
