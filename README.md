# MilkMart

Website thương mại điện tử (B2C) bán sữa và các chế phẩm từ sữa. Xem chi tiết nghiệp vụ, schema và kiến trúc trong thư mục [`docs/`](docs/), đặc biệt [`docs/05-tech-stack.md`](docs/05-tech-stack.md).

## Tech stack

- **Backend**: Laravel 11 (PHP 8.2) + Inertia.js (server-side adapter)
- **Frontend**: Vue 3 (Composition API) + `@inertiajs/vue3` + Pinia + TailwindCSS + Vite
- **Database**: Oracle Database (qua `yajra/laravel-oci8`)
- **Auth**: Laravel Sanctum (session/cookie-based) + `spatie/laravel-permission` (phân quyền chi tiết)
- **Hạ tầng dev**: Docker Compose (app, webserver, oracle, node)

## Cấu trúc thư mục

```
app/
  Http/Controllers/   Controller theo module (ProductController, CartController, InvoiceController...)
  Http/Requests/       Form Request validation
  Models/               16 Eloquent Model tương ứng docs/03-database-schema.md
  Services/             Nghiệp vụ phức tạp (VoucherService, InventoryService...)
database/migrations/    Migration cho 16 bảng domain + bảng hạ tầng Laravel (sessions, cache, jobs, sanctum, permission)
resources/js/
  Pages/                Trang Vue map theo route Inertia (Products/Index.vue, Cart/Show.vue...)
  Components/           Component dùng chung
  Layouts/              Layout chung (CustomerLayout...)
  stores/               Pinia store (giỏ hàng tạm, bộ lọc...)
docker/
  php/                  Dockerfile PHP-FPM 8.2 + Oracle Instant Client + ext-oci8
  nginx/                Cấu hình reverse proxy tới PHP-FPM
docker-compose.yml       Toàn bộ môi trường local: app, webserver, oracle, node
```

Lý do tổ chức như trên: bám sát mô hình Inertia monolith mô tả ở [`docs/05-tech-stack.md`](docs/05-tech-stack.md) mục 6 — một codebase Laravel duy nhất, frontend nằm trong `resources/js`, không tách SPA/API riêng.

## Chạy dự án bằng Docker Compose

### Yêu cầu

- Docker Desktop (đã bật WSL2 backend trên Windows)

### Các bước

1. Copy file môi trường mẫu (đã có sẵn `.env` cho local dev, nhưng nếu cần tạo lại):
   ```bash
   cp .env.example .env
   ```

2. Build và khởi động toàn bộ stack:
   ```bash
   docker compose up -d --build
   ```
   Lần đầu build sẽ tải Oracle Instant Client (~115MB) trong image `app`, và Oracle container sẽ mất **1-2 phút** để khởi tạo pluggable database (`XEPDB1`). Theo dõi bằng:
   ```bash
   docker compose ps
   ```
   Chờ đến khi service `oracle` ở trạng thái `healthy` trước khi chạy migration.

3. Sinh `APP_KEY` (chỉ cần lần đầu, nếu `.env` chưa có):
   ```bash
   docker compose exec app php artisan key:generate
   ```

4. Chạy migration + seed dữ liệu mẫu (3 role: Admin/Staff/Customer + 1 tài khoản admin):
   ```bash
   docker compose exec app php artisan migrate --seed
   ```

5. Truy cập ứng dụng:
   - Web (qua nginx): http://localhost:8080
   - Vite dev server (HMR, không cần truy cập trực tiếp): http://localhost:5173
   - Oracle (kết nối bằng SQL Developer/DBeaver từ máy host): `localhost:1522`, service name `XEPDB1`, user `milkmart` (xem `.env`)

### Dừng môi trường

```bash
docker compose down        # dừng, giữ lại dữ liệu Oracle (volume oracle-data)
docker compose down -v     # dừng và xoá luôn dữ liệu Oracle
```

## Vai trò từng service trong `docker-compose.yml`

| Service | Vai trò |
|---|---|
| `app` | PHP-FPM 8.2 chạy code Laravel, có sẵn Oracle Instant Client + extension `oci8` (build riêng vì không có driver Oracle chính thức cho PHP/Docker Hub) |
| `webserver` | Nginx, reverse-proxy request PHP tới `app:9000`, phục vụ static file trong `public/` |
| `oracle` | `gvenzl/oracle-xe:21-slim-faststart` — Oracle Database 21c XE, đúng theo đề xuất ở mục 5 tài liệu tech-stack |
| `node` | Chạy Vite dev server (`npm run dev`) cho hot-reload Vue trong lúc phát triển; `npm install` chạy trong container Linux để tránh lệch native binary (esbuild/rollup) với host Windows |

## Ghi chú vận hành

- Session/Cache/Queue đều dùng driver `database` (không thêm Redis, vì tài liệu tech-stack không đề cập) — cần các bảng `sessions`, `cache`, `jobs` đã có sẵn trong migration.
- Mail dùng driver `log` — email xác nhận đơn hàng sẽ ghi vào `storage/logs/laravel.log` thay vì gửi SMTP thật.
- Bảng `roles` trong `spatie/laravel-permission` được đổi tên thành `permission_roles` (xem `config/permission.php`) để tránh trùng với bảng domain `roles` đã thiết kế sẵn (`users.role_id` → `roles.id`).
- Cổng host của Oracle map ra `1522` (không phải `1521` mặc định) để tránh xung đột với Oracle Listener cài sẵn trên máy dev; giao tiếp nội bộ giữa `app` và `oracle` trong Docker network vẫn dùng cổng `1521` như bình thường.

## Giả định đã áp dụng khi khởi tạo

1. Laravel 11.x mới nhất, PHP 8.2-FPM.
2. `composer.json` tên package: `milkmart/milkmart`.
3. Không có bảng `updated_at` cho các bảng domain — bám sát đúng schema trong [`docs/03-database-schema.md`](docs/03-database-schema.md) (chỉ bảng nào tài liệu liệt kê `created_at` mới có cột này).
4. Cột `password_hash` (không phải `password` mặc định của Laravel) — `App\Models\User::getAuthPassword()` được override để tương thích với `Auth::attempt()`.
