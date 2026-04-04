<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hệ thống thi trực tuyến</title>
    <script>
        (function () {
            var key = 'online-exam-theme';
            var saved = localStorage.getItem(key);
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = saved === 'light' || saved === 'dark' ? saved : (prefersDark ? 'dark' : 'light');

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>


