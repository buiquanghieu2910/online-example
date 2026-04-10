<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảo trì hệ thống</title>
    <style>
        :root {
            color-scheme: light dark;
        }
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #0f172a;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 640px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 12px 36px rgba(15, 23, 42, 0.12);
            padding: 28px;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 24px;
        }
        p {
            margin: 0 0 12px;
            line-height: 1.55;
            color: #334155;
        }
        .hint {
            font-size: 14px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Hệ thống đang bảo trì</h1>
        <p>{{ $message ?? 'Hệ thống tạm thời gián đoạn để nâng cấp. Vui lòng quay lại sau.' }}</p>
        <p class="hint">Nếu bạn là quản trị viên, có thể đăng nhập tại <strong>/app/login</strong> để kiểm tra trạng thái.</p>
    </div>
</body>
</html>

