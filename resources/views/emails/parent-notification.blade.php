<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #1a73e8; padding: 24px 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; letter-spacing: 1px; }
        .body { padding: 32px; color: #333333; }
        .body h2 { color: #1a73e8; margin-top: 0; font-size: 18px; }
        .body p { line-height: 1.7; font-size: 15px; }
        .student-info { background: #f0f7ff; border-left: 4px solid #1a73e8; padding: 12px 16px; border-radius: 4px; margin: 20px 0; }
        .student-info strong { color: #1a73e8; }
        .message-box { background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 6px; padding: 16px; margin: 20px 0; font-size: 15px; line-height: 1.7; }
        .footer { background: #f4f6f9; padding: 20px 32px; text-align: center; color: #888888; font-size: 12px; border-top: 1px solid #e0e0e0; }
        .footer a { color: #1a73e8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>TunAcademy</h1>
        </div>
        <div class="body">
            <h2>{{ $title }}</h2>
            <p>Kính gửi Quý Phụ huynh,</p>
            <div class="student-info">
                <strong>Học sinh:</strong> {{ $studentName }}
            </div>
            <div class="message-box">
                {!! nl2br(e($message)) !!}
            </div>
            <p>Đây là thông báo tự động từ hệ thống TunAcademy. Vui lòng không trả lời email này.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} TunAcademy. Mọi thắc mắc vui lòng liên hệ nhà trường.</p>
        </div>
    </div>
</body>
</html>
