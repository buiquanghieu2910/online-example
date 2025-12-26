# 📝 Hệ thống Thi Trực Tuyến (Online Exam System)

Hệ thống thi trực tuyến được xây dựng bằng Laravel 12, cho phép quản trị viên và giáo viên tạo và quản lý bài tập, học sinh làm bài thi trực tuyến với hỗ trợ tự động chấm điểm và chấm bài tự luận.

## ✨ Tính năng chính

### 👨‍💼 Quản trị viên (Admin)
- **Quản lý người dùng**: 
  - Tạo, sửa, xóa tài khoản người dùng (Admin, Teacher, Student)
  - Quản lý thông tin chi tiết người dùng
  - Phân quyền theo vai trò
- **Quản lý lớp học**:
  - Tạo, sửa, xóa lớp học
  - Gán giáo viên vào lớp
  - Xem danh sách học sinh trong lớp
- **Quản lý bài tập**: 
  - Tạo bài tập với nhiều loại câu hỏi (Trắc nghiệm, Đúng/Sai, Tự luận)
  - Tạo bài tập kèm câu hỏi trong một lần (single-page form)
  - Upload hình ảnh cho câu hỏi (hỗ trợ MinIO)
  - Cài đặt thời gian, điểm tối thiểu để đạt
  - Phân công bài tập cho học sinh hoặc cả lớp
  - Cho phép làm lại bài thi
- **Quản lý câu hỏi**:
  - Thêm, sửa, xóa câu hỏi trong bài tập
  - Hỗ trợ nhiều loại: Trắc nghiệm (có thể xóa đáp án, tối thiểu 2), Đúng/Sai, Tự luận
  - Upload hình ảnh minh họa
- **Chấm bài thi**:
  - Tự động chấm câu trắc nghiệm và đúng/sai
  - Chấm thủ công câu tự luận với phản hồi chi tiết
  - Xem theo bài tập → danh sách người dùng cần chấm
- **Bảng điều khiển**: Thống kê tổng quan (số bài tập, người dùng, lớp học, lượt thi)
- **Xem lịch sử**: Theo dõi lịch sử làm bài của từng người dùng

### 👨‍🏫 Giáo viên (Teacher)
- **Quản lý lớp học**:
  - Tạo, sửa, xóa lớp học của mình
  - Thêm/xóa học sinh vào lớp (hỗ trợ chọn nhiều)
  - Xem danh sách học sinh trong lớp với thông tin các lớp khác
  - Quản lý lớp theo năm học (start_year - end_year)
- **Quản lý học sinh**:
  - Tạo, sửa, xóa tài khoản học sinh
  - Xem chi tiết thông tin học sinh
  - Phân học sinh vào nhiều lớp
- **Quản lý bài tập**:
  - Tạo bài tập cho lớp học cụ thể
  - Tự động phân công bài tập cho tất cả học sinh trong lớp
  - Khi tạo bài tập từ trang lớp học, lớp được chọn sẵn
  - Sửa, xóa bài tập của mình
  - Cho phép học sinh làm lại bài thi
  - Gỡ học sinh khỏi bài tập
- **Quản lý câu hỏi**:
  - Thêm, sửa, xóa câu hỏi trong bài tập
  - Trắc nghiệm với khả năng xóa đáp án (tối thiểu 2 đáp án)
  - Upload hình ảnh cho câu hỏi
- **Điểm danh**:
  - Điểm danh học sinh theo ngày
  - Lọc học sinh theo lớp học
  - Hiển thị thông tin đầy đủ (tên, mã học sinh, các lớp)
  - Giao diện thân thiện với checkbox và styling hiện đại
- **Bảng điều khiển**: Thống kê lớp học, học sinh, bài tập của giáo viên

### 👨‍🎓 Học sinh (Student)
- **Xem danh sách bài tập**: 
  - Chỉ hiển thị các bài tập được phân công (theo lớp hoặc cá nhân)
  - Hiển thị trạng thái: Chưa làm, Đang làm, Đã hoàn thành
  - Xem thông tin lớp học của bài tập
- **Làm bài thi**:
  - Đồng hồ đếm ngược tự động
  - Hỗ trợ nhiều loại câu hỏi (trắc nghiệm, đúng/sai, tự luận)
  - Hiển thị hình ảnh câu hỏi nếu có
  - Tự động nộp bài khi hết giờ
  - Modal xác nhận khi nộp bài
- **Xem kết quả**:
  - Điểm số chi tiết theo từng câu hỏi
  - Xem lại câu trả lời đúng/sai
  - Nhận phản hồi từ giáo viên cho câu tự luận
- **Lịch sử làm bài**: Xem tất cả các lần thi trước đó

### 🎨 Giao diện & UX
- **Dark Mode**:
  - Giao diện hỗ trợ chế độ sáng/tối
  - Nút toggle trên navbar
  - Tự động lưu preferences vào localStorage
  - Đồng bộ với system preference
- **Modal xác nhận**:
  - Modal đẹp mắt thay thế confirm() mặc định
  - Áp dụng cho tất cả thao tác: Tạo, Sửa, Xóa
  - Tùy chỉnh màu nút (đỏ cho xóa, xanh cho cập nhật/tạo)
  - Hỗ trợ ESC và click outside để đóng
- **Responsive Design**: Hỗ trợ đầy đủ trên mobile, tablet, desktop
- **Modern UI**: Tailwind CSS với hiệu ứng gradient, shadow, hover

### 🔄 Quan hệ dữ liệu
- **Học sinh - Lớp học**: Many-to-Many (một học sinh có thể học nhiều lớp)
- **Giáo viên - Lớp học**: Many-to-Many (một giáo viên có thể dạy nhiều lớp)
- **Bài tập - Lớp học**: Belongs To (mỗi bài tập thuộc một lớp)
- **Bài tập - Học sinh**: Many-to-Many qua exam_user (phân công tự động theo lớp)

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
- **users**: Quản lý người dùng (role: admin/teacher/student)
  - username (unique), name, password, role
- **classes**: Lớp học
  - name, code, subject, start_year, end_year, description
- **class_student**: Pivot table (Many-to-Many: Student ↔ Class)
  - class_id, student_id
- **class_teacher**: Pivot table (Many-to-Many: Teacher ↔ Class)
  - class_id, teacher_id
- **exams**: Bài tập (tiêu đề, thời gian, điểm tối thiểu)
  - title, description, duration, min_score, class_id
- **questions**: Câu hỏi (trắc nghiệm, đúng/sai, tự luận)
  - question_text, question_type, points, image_url
- **answers**: Đáp án cho câu trắc nghiệm
  - answer_text, is_correct
- **user_exams**: Lịch sử làm bài của người dùng
  - user_id, exam_id, status, score, started_at, completed_at
- **user_answers**: Câu trả lời của người dùng
  - user_exam_id, question_id, answer_id, answer_text, points_earned
- **exam_user**: Bảng pivot phân công bài tập
  - exam_id, user_id
- **attendances**: Điểm danh
  - user_id, class_id, date, status (present/absent/late)

## 🎯 Hệ thống chấm điểm

### Tính điểm tuyệt đối (Absolute Scoring)
- Mỗi câu hỏi có **điểm riêng** (points)
- Bài tập có **điểm tối thiểu** để đạt (min_score)
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
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Admin controllers
│   │   │   ├── ClassController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ExamController.php
│   │   │   ├── GradingController.php
│   │   │   ├── QuestionController.php
│   │   │   └── UserController.php
│   │   ├── Teacher/        # Teacher controllers
│   │   │   ├── AttendanceController.php
│   │   │   ├── ClassController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ExamController.php
│   │   │   ├── QuestionController.php
│   │   │   └── UserController.php
│   │   ├── Student/        # Student controllers
│   │   │   ├── ExamController.php
│   │   │   └── ResultController.php
│   │   └── Auth/           # Authentication
│   └── Middleware/
├── Models/                 # Eloquent Models
│   ├── Answer.php
│   ├── Attendance.php
│   ├── Exam.php
│   ├── Question.php
│   ├── SchoolClass.php
│   ├── User.php
│   ├── UserAnswer.php
│   └── UserExam.php
├── Repositories/           # Repository interfaces & implementations
│   ├── Contracts/
│   └── impl/
├── Services/              # Business logic layer
│   ├── Contracts/
│   └── impl/
├── Helpers/               # Helper classes
│   └── MinioHelper.php
└── Providers/             # Service providers

database/
├── migrations/            # Database migrations
│   ├── *_create_users_table.php
│   ├── *_create_classes_table.php
│   ├── *_create_class_student_table.php
│   ├── *_create_class_teacher_table.php
│   ├── *_create_exams_table.php
│   ├── *_create_questions_table.php
│   ├── *_create_answers_table.php
│   ├── *_create_user_exams_table.php
│   ├── *_create_user_answers_table.php
│   ├── *_create_exam_user_table.php
│   └── *_create_attendances_table.php
└── seeders/              # Database seeders
    └── AdminUserSeeder.php

resources/
├── views/
│   ├── layouts/          # Layout templates
│   │   └── app.blade.php
│   ├── components/       # Reusable components
│   │   ├── confirm-modal.blade.php
│   │   └── dark-mode-toggle.blade.php
│   ├── admin/            # Admin views
│   │   ├── dashboard.blade.php
│   │   ├── users/
│   │   ├── classes/
│   │   ├── exams/
│   │   ├── questions/
│   │   └── grading/
│   ├── teacher/          # Teacher views
│   │   ├── dashboard.blade.php
│   │   ├── classes/
│   │   ├── students/
│   │   ├── exams/
│   │   ├── questions/
│   │   └── attendances/
│   ├── student/          # Student views
│   │   ├── exams/
│   │   └── results/
│   └── auth/             # Authentication views
│       ├── login.blade.php
│       └── register.blade.php
├── css/                  # Tailwind CSS
└── js/                   # JavaScript

routes/
└── web.php               # Web routes (admin, teacher, student)

## 🔐 Phân quyền

### Admin
- Username: `admin`
- Password: (được tạo trong seeder)
- Quyền: 
  - Quản lý toàn bộ hệ thống
  - Tạo/sửa/xóa: User, Class, Exam, Question
  - Chấm bài và xem tất cả kết quả
  - Xem thống kê tổng quan

### Teacher
- Username: Tạo bởi Admin hoặc đăng ký
- Quyền:
  - Quản lý lớp học của mình
  - Tạo/sửa/xóa: Student, Class (của mình), Exam, Question
  - Điểm danh học sinh
  - Xem kết quả học sinh trong lớp
  - Phân công bài tập cho lớp

### Student
- Username: Tạo bởi Admin/Teacher hoặc đăng ký
- Quyền:
  - Làm bài tập được phân công
  - Xem điểm và kết quả của mình
  - Xem lịch sử làm bài

## 🎯 Luồng hoạt động chính

### Tạo và phân công bài tập
1. **Teacher/Admin** tạo bài tập, chọn lớp học
2. Hệ thống tự động phân công cho tất cả học sinh trong lớp đó
3. **Student** xem bài tập trong danh sách "Bài tập của tôi"
4. Student làm bài và nộp

### Chấm bài
1. Câu trắc nghiệm/đúng-sai: **Tự động chấm** ngay khi nộp
2. Câu tự luận: 
   - Trạng thái: `pending_review`
   - **Teacher/Admin** vào mục "Chấm bài" để chấm thủ công
   - Cho điểm và feedback chi tiết
3. Tổng điểm = Điểm tự động + Điểm thủ công

### Điểm danh
1. **Teacher** chọn ngày và lớp
2. Hệ thống hiển thị danh sách học sinh
3. Teacher đánh dấu: Có mặt/Vắng/Muộn
4. Lưu điểm danh

## 🚀 Tính năng nổi bật

### 1. Quan hệ Many-to-Many linh hoạt
- Học sinh có thể học nhiều lớp
- Giáo viên có thể dạy nhiều lớp
- Dễ dàng quản lý và phân công

### 2. Phân công bài tập tự động
- Khi tạo bài tập, chọn lớp học
- Hệ thống tự động assign cho tất cả học sinh
- Khi thêm học sinh mới vào lớp, có thể assign lại

### 3. Modal xác nhận hiện đại
- Thay thế `confirm()` mặc định
- Giao diện đẹp, tùy chỉnh được
- Hỗ trợ keyboard (ESC) và click outside

### 4. Dark Mode toàn diện
- Tất cả trang đều hỗ trợ
- Tự động sync với system preference
- Lưu preference của user

### 5. Quản lý câu hỏi linh hoạt
- Trắc nghiệm: Thêm/xóa đáp án (min 2)
- Upload hình ảnh cho câu hỏi
- Điểm riêng cho từng câu

### 6. Chấm điểm thông minh
- Tự động chấm trắc nghiệm
- Chấm thủ công tự luận với feedback
- Cho phép làm lại bài thi

## 📝 License

Dự án này được phát triển dựa trên Laravel Framework - [MIT license](https://opensource.org/licenses/MIT).

## 👥 Tác giả

Phát triển bởi AI Assistant & User

---

**Happy Coding! 🚀**
