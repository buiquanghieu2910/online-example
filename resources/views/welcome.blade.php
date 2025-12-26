<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống thi trực tuyến</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 min-h-screen flex items-center justify-center transition-colors duration-200">
    <x-dark-mode-toggle />
    
    <div class="text-center px-4">
        <h1 class="text-5xl font-bold text-gray-900 dark:text-gray-100 mb-4">Hệ thống thi trực tuyến</h1>
        <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">Kiểm tra kiến thức của bạn với nền tảng thi trực tuyến đa năng</p>
        
        <div class="space-x-4">
            <a href="{{ route('login') }}" class="inline-block bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition">
                Đăng nhập
            </a>
        </div>

        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg transition-colors">
                <svg class="w-12 h-12 text-blue-600 dark:text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Nhiều loại bài tập</h3>
                <p class="text-gray-600 dark:text-gray-300">Trắc nghiệm, đúng sai và câu hỏi tự luận</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg transition-colors">
                <svg class="w-12 h-12 text-blue-600 dark:text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Thi có giới hạn thời gian</h3>
                <p class="text-gray-600 dark:text-gray-300">Hoàn thành bài thi trong thời gian quy định</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg transition-colors">
                <svg class="w-12 h-12 text-blue-600 dark:text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Kết quả ngay lập tức</h3>
                <p class="text-gray-600 dark:text-gray-300">Xem điểm số và đáp án chi tiết ngay sau khi thi</p>
            </div>
        </div>
    </div>
</body>
</html>

