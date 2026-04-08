# Hệ thống Thi Trực Tuyến (Online Exam System)

Hệ thống thi trực tuyến xây dựng bằng Laravel 12 + Vue 3 SPA, hỗ trợ quản trị đề thi, làm bài có giới hạn thời gian, chấm điểm tự động/thủ công và giám sát theo thời gian thực.

## Tính năng chính

### Quản trị viên / Giáo viên
- Quản lý người dùng, lớp học, bài thi, câu hỏi.
- Phân công bài thi theo lớp hoặc theo học sinh.
- Cho phép học sinh làm lại bài thi (áp dụng với bài tự động chấm).
- Chấm thủ công câu tự luận, xem danh sách học sinh cần chấm.
- Theo dõi màn hình giám sát (`/monitor`) với bộ lọc theo lớp/bài thi/học sinh.
- Xem lịch sử hoạt động làm bài theo từng lượt thi.

### Học sinh
- Xem danh sách bài thi được phân công.
- Làm bài thi với đồng hồ đếm ngược.
- Tự động nộp bài khi hết thời gian.
- Xem điểm chi tiết, trạng thái đạt/chưa đạt và lịch sử các lần làm.

### Dashboard
- Thống kê tổng quan theo vai trò.
- Cảnh báo cần xử lý.
- Xu hướng theo thời gian.
- Progress tỉ lệ đạt trong phần xu hướng.

## Cập nhật gần đây

- Chuẩn hóa kiến trúc Backend theo tầng Service:
  - `DashboardService`, `ExamMonitoringService`, `ExamSessionService`.
  - Controller chỉ xử lý request/response, nghiệp vụ đặt trong service.
- Đồng bộ timer làm bài theo từng người dùng bằng Redis (server là nguồn đúng).
- Màn hình monitor:
  - Có bộ lọc theo lớp/bài thi/học sinh.
  - Thêm thanh progress khi tự tải lại và khi bấm làm mới.
  - Không hiển thị loading toàn trang ở các lần reload sau.
  - Client đang mở sẽ cập nhật trạng thái đã nộp khi có thay đổi.
- Sửa lỗi truy vấn mơ hồ cột trong API monitor `active-attempts`.
- Việt hóa thông báo validate/lỗi đầu ra API.
- Bổ sung tooltip cho các nút chỉ có icon ở Frontend.
- Đồng bộ màu chủ đạo (prime color) với trạng thái active menu, hỗ trợ thêm nhiều màu.
- Cập nhật phần hiển thị điểm bài thi rõ ràng hơn, có trạng thái đạt/chưa đạt.

## Công nghệ sử dụng

- Backend: Laravel 12
- Frontend: Vue 3 + PrimeVue + Tailwind CSS
- Cơ sở dữ liệu: PostgreSQL
- Cache/Timer realtime: Redis
- Lưu trữ tệp: MinIO

## Yêu cầu hệ thống

- PHP >= 8.2
- Composer
- Node.js + npm
- PostgreSQL >= 14
- Redis
- MinIO (nếu dùng upload ảnh)

## Cài đặt nhanh

### 1. Clone mã nguồn
```bash
git clone <repository-url>
cd online-exam
```

### 2. Cài dependencies
```bash
composer install
npm install
```

### 3. Tạo file môi trường và app key
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Cấu hình PostgreSQL trong `.env`
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=online_exam
DB_USERNAME=postgres
DB_PASSWORD=secret
```

### 5. Cấu hình Redis trong `.env` (dùng `REDIS_*`)
```env
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

### 6. Cấu hình MinIO trong `.env` (tùy chọn)
```env
MINIO_ACCESS_KEY=your_access_key
MINIO_SECRET_KEY=your_secret_key
MINIO_REGION=us-east-1
MINIO_BUCKET=online-exam-system
MINIO_ENDPOINT=http://localhost:9000
FILESYSTEM_DISK=minio
```

### 7. Chạy migration / seed
```bash
php artisan migrate
php artisan db:seed
```

### 8. Chạy ứng dụng
```bash
npm run dev
php artisan serve
```

Truy cập:
- SPA: `http://localhost:8000/app`
- API: `http://localhost:8000/api`

## Build production

```bash
npm run build
php artisan optimize
```

Nếu gặp lỗi `ViteManifestNotFoundException`, cần build lại frontend:
```bash
npm run build
```

## Docker (production)

```bash
docker build -t online-exam:prod .
docker run -d --name online-exam \
  -p 8000:80 \
  --env-file .env \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e RUN_MIGRATIONS=true \
  online-exam:prod
```

## Ghi chú vận hành

- Bắt buộc có `APP_KEY` hợp lệ để tránh lỗi `MissingAppKeyException`.
- Nếu chạy HTTPS, cần đặt `APP_URL=https://...` và cấu hình proxy đúng để tránh lỗi mixed content.
- Timer bài thi lấy mốc từ server/Redis, không phụ thuộc việc tab trình duyệt có đang active hay không.

## Tác giả

Phát triển bởi Bùi Quang Hiếu.
