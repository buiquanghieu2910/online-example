<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không có quyền truy cập</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-red-500">403</h1>
                <h2 class="text-3xl font-semibold text-gray-800 dark:text-gray-200 mt-4">Không có quyền truy cập</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-4">
                    Xin lỗi, bạn không có quyền truy cập vào trang này.
                </p>
            </div>
            
            <div class="space-y-4">
                @auth
                    <a href="{{ url()->previous() }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Quay lại trang trước
                    </a>
                    <a href="{{ 
                        auth()->user()->role === 'admin' ? route('admin.dashboard') : 
                        (auth()->user()->role === 'teacher' ? route('teacher.dashboard') : 
                        route('student.exams.index')) 
                    }}" class="inline-block ml-4 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        Về trang chủ
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Đăng nhập
                    </a>
                @endauth
            </div>
        </div>
    </div>
</body>
</html>
