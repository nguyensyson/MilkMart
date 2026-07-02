# 05 — Công nghệ sử dụng (Tech Stack)

## 1. Backend: PHP Laravel

- **Phiên bản đề xuất**: Laravel 11.x (bản LTS/ổn định hiện hành), PHP >= 8.2.
- **Package/thư viện chính dự kiến sử dụng**:

| Package | Mục đích |
|---|---|
| `laravel/sanctum` | Xác thực phiên đăng nhập (session-based cho ứng dụng Inertia) |
| `inertiajs/inertia-laravel` | Cầu nối giữa Laravel và Vue.js theo mô hình Inertia |
| Eloquent ORM (có sẵn trong Laravel) | Tương tác với cơ sở dữ liệu, định nghĩa model cho 16 bảng |
| `laravel/queue` (built-in) | Xử lý tác vụ nền: gửi email xác nhận đơn hàng, thông báo trạng thái đơn |
| `spatie/laravel-permission` | Quản lý phân quyền chi tiết theo vai trò (mở rộng cho `roles`) |
| `yajra/laravel-oci8` | Driver kết nối Eloquent với Oracle Database (chi tiết ở mục 4) |

## 2. Frontend: Vue.js 3 + Composition API

- Vue 3 sử dụng Composition API (`<script setup>`).
- **Thư viện đi kèm**:

| Thư viện | Mục đích |
|---|---|
| `@inertiajs/vue3` | Kết nối Vue với backend Laravel qua Inertia, thay thế phần lớn nhu cầu Vue Router/Axios cho điều hướng và lấy dữ liệu trang |
| Pinia | Quản lý state phía client cho các trường hợp phức tạp cục bộ (giỏ hàng tạm, bộ lọc sản phẩm...) khi Inertia props không đủ |
| TailwindCSS | Xây dựng giao diện UI nhanh, nhất quán |
| Vite | Build tool cho frontend (biên dịch Vue + Tailwind) |

> **Lưu ý**: vì dùng Inertia.js, Vue Router và Axios không còn là thành phần bắt buộc như trong mô hình SPA thuần — Inertia đảm nhiệm routing phía client (dựa trên route Laravel) và giao tiếp dữ liệu giữa client-server.

## 3. Kiến trúc hệ thống

### 3.1. So sánh hai hướng tiếp cận

| Tiêu chí | API rời + SPA thuần (Laravel API + Vue SPA) | Laravel + Inertia.js + Vue |
|---|---|---|
| Tốc độ phát triển | Chậm hơn — phải xây API riêng, xử lý auth token, đồng bộ state | Nhanh hơn — dùng trực tiếp route/controller Laravel, không cần thiết kế API riêng |
| Số codebase | 2 codebase tách biệt (backend API + frontend SPA) | 1 codebase Laravel duy nhất (frontend nằm trong `resources/js`) |
| Authentication | Cần cơ chế token (Sanctum SPA token/JWT), phức tạp hơn | Session/cookie-based đơn giản, tận dụng session Laravel có sẵn |
| Khả năng tái sử dụng cho mobile app | Cao — API có thể dùng chung cho web và mobile | Thấp hơn — cần xây thêm API layer riêng nếu phát triển mobile app sau này |
| Độ phức tạp vận hành | Cao hơn (CORS, versioning API, deploy 2 phần riêng) | Thấp hơn (deploy như một ứng dụng Laravel thông thường) |

### 3.2. Đề xuất

**Chọn hướng Laravel + Inertia.js + Vue** cho giai đoạn hiện tại của dự án, vì:

- Đội ngũ phát triển nhanh hơn với một codebase duy nhất, giảm chi phí xây dựng và bảo trì API riêng.
- MVP tập trung vào website bán hàng, chưa có yêu cầu về mobile app native.
- Authentication đơn giản hơn nhờ session/cookie thay vì phải quản lý token.

**Đánh đổi cần lưu ý**: khi dự án mở rộng sang mobile app (xem [01-project-overview.md](01-project-overview.md) mục phạm vi mở rộng), cần bổ sung một lớp API riêng (ví dụ dùng Sanctum token cho mobile) song song với các route Inertia hiện có, thay vì tái cấu trúc toàn bộ backend.

## 4. Database: Oracle Database

### 4.1. Kết nối Eloquent với Oracle

Laravel không hỗ trợ Oracle sẵn (native driver chỉ có MySQL, PostgreSQL, SQLite, SQL Server). Cần dùng package bên thứ ba:

- **Package**: [`yajra/laravel-oci8`](https://github.com/yajra/laravel-oci8) — driver Oracle OCI8 cho Laravel, tích hợp với Eloquent/Query Builder/Migration.
- **Cài đặt cơ bản**:
  1. Cài Oracle Instant Client (OCI8 extension) trên máy chủ/máy phát triển — đây là yêu cầu bắt buộc ở tầng hệ điều hành, không chỉ là package PHP.
  2. `composer require yajra/laravel-oci8`.
  3. Cấu hình connection `oracle` trong `config/database.php` (driver `oracle`, host, port, service name/SID, username, password) — connection string khác hoàn toàn so với MySQL (dùng TNS hoặc Easy Connect thay vì `host:port/database`).
  4. Đặt `DB_CONNECTION=oracle` trong `.env` cùng các biến `DB_HOST`, `DB_PORT`, `DB_SERVICE_NAME`.

### 4.2. Giới hạn/khác biệt khi dùng chung với Eloquent

- Cần cài đặt **Oracle Instant Client (OCI8)** trên mọi môi trường (dev, staging, production) — không có sẵn như driver MySQL, có thể gây khó khăn khi setup môi trường mới hoặc container hóa.
- Một số tính năng Eloquent (ví dụ `lastInsertId`, một số hàm chuỗi/ngày tháng trong Query Builder) hoạt động khác biệt do cú pháp SQL của Oracle khác MySQL.
- Không phải mọi tính năng Laravel Migration/Schema Builder được hỗ trợ đầy đủ như với MySQL; một số thao tác cần viết raw SQL.

### 4.3. Khác biệt Oracle vs MySQL ảnh hưởng đến schema đã thiết kế

| Vấn đề | MySQL | Oracle | Ảnh hưởng đến schema MilkMart |
|---|---|---|---|
| Tự tăng khóa chính | `AUTO_INCREMENT` | Không có; dùng `SEQUENCE` + `TRIGGER`, hoặc `IDENTITY` column (Oracle 12c+) | Tất cả 16 bảng dùng cột `id` tự tăng → mỗi bảng cần 1 SEQUENCE (+ TRIGGER nếu Oracle 11g trở xuống) |
| Kiểu số nguyên lớn | `BIGINT` | `NUMBER(19)` | Cột `id`, các khóa ngoại `*_id` cần map sang `NUMBER(19)` |
| Kiểu chuỗi | `VARCHAR` | `VARCHAR2` | Các cột như `name`, `email`, `sku`... map sang `VARCHAR2` |
| Kiểu văn bản dài | `TEXT` | `CLOB` | Các cột `description`, `address` map sang `CLOB` |
| Kiểu ngày giờ | `DATETIME` | `DATE` hoặc `TIMESTAMP` | Các cột `created_at`, `start_date`, `end_date`, `used_at` cần map sang `DATE`/`TIMESTAMP` |
| Kiểu boolean | `BOOLEAN` | Không có kiểu boolean gốc, dùng `NUMBER(1)` | Cột `is_primary` trong `product_images` cần map sang `NUMBER(1)` (0/1) |
| Độ dài tên bảng/cột | Không giới hạn thực tế | 30 ký tự (bản cũ) / 128 ký tự (từ 12.2+) | Rà soát: tên bảng/cột hiện tại (vd. `goods_receipt_details`, `product_variant_id`) đều dưới 30 ký tự nên an toàn cho cả phiên bản cũ; cần lưu ý khi đặt tên cột/bảng mới về sau |

### 4.4. Điều chỉnh Laravel Migration khi dùng Oracle

- Dùng `unsignedBigInteger`/`bigIncrements` cẩn thận vì Oracle không có khái niệm `unsigned` — package `yajra/laravel-oci8` sẽ tự chuyển đổi nhưng cần kiểm tra kỹ với `IDENTITY` (12c+).
- Với Oracle 11g trở xuống (không có `IDENTITY` column), cân nhắc viết raw SQL trong migration để tạo `SEQUENCE` và `TRIGGER` tương ứng cho từng bảng thay vì dựa hoàn toàn vào Schema Builder.
- Kiểm tra kỹ các ràng buộc `NOT NULL`, `UNIQUE`, khóa ngoại giữa các bảng khi generate migration, vì cú pháp DDL của Oracle có khác biệt nhỏ so với MySQL.

## 5. Công cụ hỗ trợ khác

| Công cụ | Mục đích |
|---|---|
| Docker | Đóng gói môi trường phát triển; dùng image `gvenzl/oracle-xe` để chạy Oracle Database dạng container cho môi trường local/dev |
| Vite | Build & hot-reload cho frontend Vue trong Laravel |
| Postman / Swagger (OpenAPI) | Tài liệu hóa và kiểm thử các API endpoint thuần túy (ví dụ phục vụ mobile app sau này hoặc tích hợp bên thứ ba) |

## 6. Cấu trúc thư mục đề xuất

### 6.1. Backend (Laravel, theo mô hình Inertia)

```
app/
  Http/
    Controllers/       # Controller theo module: ProductController, CartController, InvoiceController...
    Middleware/
    Requests/           # Form Request validation
  Models/               # Eloquent Model tương ứng 16 bảng
  Services/             # Xử lý nghiệp vụ phức tạp (áp dụng voucher, tính tồn kho...)
config/
database/
  migrations/
routes/
  web.php               # Route trả về Inertia response
resources/
  js/
    Pages/              # Trang Vue tương ứng route Inertia (Products/Index.vue, Cart/Show.vue...)
    Components/         # Component dùng chung
    Layouts/            # Layout chung (AdminLayout, CustomerLayout)
    stores/             # Pinia store (nếu cần)
  css/
```

### 6.2. Frontend (nằm trong `resources/js` theo mô hình Inertia)

Không tách thư mục frontend riêng biệt — toàn bộ mã Vue nằm trong `resources/js` của dự án Laravel, tổ chức theo `Pages` (map với route), `Components` (tái sử dụng) và `Layouts` (khung giao diện chung).

## 7. Luồng Authentication

- Sử dụng **Laravel Sanctum ở chế độ session/cookie-based** (không phải API token), phù hợp với kiến trúc Inertia vì frontend và backend chạy cùng domain.
- Luồng: người dùng đăng nhập → Laravel tạo session → cookie session được gửi kèm các request Inertia tiếp theo → middleware `auth` bảo vệ các route cần đăng nhập.
- Khi mở rộng API riêng cho mobile app, có thể bật thêm chế độ **Sanctum API token** cho các route API độc lập, song song với session hiện tại.
