# 📝 Hệ thống Thi Trực Tuyến (Online Exam System)

Hệ thống thi trực tuyến được xây dựng bằng Laravel 12, cho phép quản trị viên tạo và quản lý đề thi, người dùng làm bài thi trực tuyến với hỗ trợ tự động chấm điểm và chấm bài tự luận.

## ✨ Tính năng chính

### 👨‍💼 Quản trị viên (Admin)
- **Quản lý người dùng**: Tạo, sửa, xóa tài khoản người dùng
- **Quản lý đề thi**: 
  - Tạo đề thi với nhiều loại câu hỏi (Trắc nghiệm, Đúng/Sai, Tự luận)
  - Tạo đề thi kèm câu hỏi trong một lần (single-page form)
  - Upload hình ảnh cho câu hỏi (hỗ trợ MinIO)
  - Cài đặt thời gian, điểm tối thiểu để đạt
  - Phân công đề thi cho người dùng cụ thể
- **Chấm bài thi**:
  - Tự động chấm câu trắc nghiệm và đúng/sai
  - Chấm thủ công câu tự luận với phản hồi chi tiết
  - Xem theo đề thi → danh sách người dùng cần chấm
- **Bảng điều khiển**: Thống kê tổng quan (số đề thi, người dùng, lượt thi)
- **Xem lịch sử**: Theo dõi lịch sử làm bài của từng người dùng

### 👨‍🎓 Người dùng (User)
- **Xem danh sách đề thi**: Chỉ hiển thị các đề thi được phân công
- **Làm bài thi**:
  - Đồng hồ đếm ngược tự động
  - Hỗ trợ nhiều loại câu hỏi (trắc nghiệm, đúng/sai, tự luận)
  - Hiển thị hình ảnh câu hỏi nếu có
  - Tự động nộp bài khi hết giờ
- **Xem kết quả**:
  - Điểm số chi tiết theo từng câu hỏi
  - Xem lại câu trả lời đúng/sai
  - Nhận phản hồi từ giáo viên cho câu tự luận
- **Lịch sử làm bài**: Xem tất cả các lần thi trước đó

### 🌓 Dark Mode
- Giao diện hỗ trợ chế độ sáng/tối
- Nút toggle dễ dùng trên navbar
- Tự động lưu preferences vào localStorage

## 🛠️ Công nghệ sử dụng

- **Backend**: Laravel 12.42.0
- **Database**: PostgreSQL
- **Storage**: MinIO
- **Frontend**: Blade Templates + Tailwind CSS
- **Authentication**: Laravel Authentication với username
- **Architecture**: Clean Architecture (Repository + Service Pattern)

## 📋 Yêu cầu hệ thống

- PHP >= 8.2
- Composer
- PostgreSQL >= 14
- MinIO Server
- Node.js & NPM (cho Vite)

## 🚀 Cài đặt

### 1. Clone repository
```bash
git clone <repository-url>
cd online-exam
```

### 2. Cài đặt dependencies
```bash
composer install
npm install
```

### 3. Cấu hình môi trường
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Cấu hình database trong `.env`
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=25432
DB_DATABASE=online-exam
DB_USERNAME=admin
DB_PASSWORD=your_password
```

### 5. Cấu hình MinIO trong `.env`
```env
MINIO_ACCESS_KEY=your_access_key
MINIO_SECRET_KEY=your_secret_key
MINIO_REGION=us-east-1
MINIO_BUCKET=online-exam-system
MINIO_ENDPOINT=http://localhost:9003

FILESYSTEM_DISK=minio
```

### 6. Chạy migration
```bash
php artisan migrate
```

### 7. (Tùy chọn) Seed dữ liệu mẫu
```bash
php artisan db:seed
```

### 8. Build assets
```bash
npm run build
# Hoặc cho development
npm run dev
```

### 9. Khởi động server
```bash
php artisan serve
```

Truy cập: `http://localhost:8000`

## 📊 Cấu trúc Database

### Các bảng chính:
- **users**: Quản lý người dùng (admin/user)
- **exams**: Đề thi (tiêu đề, thời gian, điểm tối thiểu)
- **questions**: Câu hỏi (trắc nghiệm, đúng/sai, tự luận)
- **answers**: Đáp án cho câu trắc nghiệm
- **user_exams**: Lịch sử làm bài của người dùng
- **user_answers**: Câu trả lời của người dùng
- **exam_user**: Bảng pivot phân công đề thi

## 🎯 Hệ thống chấm điểm

### Tính điểm tuyệt đối (Absolute Scoring)
- Mỗi câu hỏi có **điểm riêng** (points)
- Đề thi có **điểm tối thiểu** để đạt (min_score)
- Không dùng phần trăm (%)

### Quy trình chấm bài
1. **Tự động chấm**: Câu trắc nghiệm và đúng/sai
2. **Chờ chấm**: Câu tự luận (status: pending_review)
3. **Chấm thủ công**: Admin chấm và cho điểm câu tự luận
4. **Hoàn thành**: Tổng điểm = điểm trắc nghiệm + điểm tự luận

### Trạng thái chấm bài:
- `auto_graded`: Tự động chấm (chỉ có trắc nghiệm)
- `pending_review`: Chờ chấm tự luận
- `manually_graded`: Đã chấm xong tất cả

## 🗂️ Cấu trúc thư mục

```
app/
├── Http/Controllers/     # Controllers (Admin, User, Auth)
├── Models/              # Eloquent Models
├── Repositories/        # Repository interfaces & implementations
├── Services/           # Business logic layer
└── Providers/          # Service providers

database/
├── migrations/         # Database migrations
└── seeders/           # Database seeders

resources/
├── views/
│   ├── layouts/       # Layout templates (app.blade.php)
│   ├── admin/         # Admin views
│   ├── user/          # User views
│   └── auth/          # Authentication views
└── css/               # Tailwind CSS

routes/
└── web.php            # Web routes
```

## 🔐 Phân quyền

### Admin
- Username: admin
- Có thể: Quản lý người dùng, đề thi, chấm bài, xem thống kê

### User
- Username: user (hoặc tạo mới)
- Có thể: Làm bài thi được phân công, xem kết quả, lịch sử

## 📝 License

Dự án này được phát triển dựa trên Laravel Framework - [MIT license](https://opensource.org/licenses/MIT).

## 👥 Tác giả

Phát triển bởi AI Assistant & User

---

**Happy Coding! 🚀**
