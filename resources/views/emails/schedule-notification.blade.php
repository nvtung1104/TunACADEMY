<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhắc nhở học tập</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #faf7f2; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e8e0d8; }
        .header { background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); padding: 30px 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; letter-spacing: 0.5px; }
        .body { padding: 32px; color: #4b5563; }
        .body h2 { color: #7c3aed; margin-top: 0; font-size: 18px; font-weight: bold; }
        .body p { line-height: 1.7; font-size: 15px; }
        .schedule-info { background: #f5f3ff; border-left: 4px solid #7c3aed; padding: 16px; border-radius: 8px; margin: 20px 0; }
        .schedule-info table { width: 100%; border-collapse: collapse; }
        .schedule-info td { padding: 4px 0; font-size: 14px; }
        .schedule-info td.label { font-weight: bold; color: #6d28d9; width: 100px; }
        .note-box { background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 8px; padding: 16px; margin: 20px 0; font-size: 14px; line-height: 1.6; color: #6b7280; font-style: italic; }
        .footer { background: #faf7f2; padding: 20px 32px; text-align: center; color: #9ca3af; font-size: 12px; border-top: 1px solid #e8e0d8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>TunAcademy</h1>
        </div>
        <div class="body">
            <h2>Đến giờ tự học rồi! ⏰</h2>
            <p>Chào <strong>{{ $student->name }}</strong>,</p>
            <p>Đã đến thời gian dành cho lịch tự học cá nhân của bạn. Hãy chuẩn bị đầy đủ sách vở, dụng cụ học tập và tập trung cao độ nhé!</p>
            
            <div class="schedule-info">
                <table>
                    <tr>
                        <td class="label">Nhiệm vụ:</td>
                        <td><strong>{{ $schedule->title }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Thời gian:</td>
                        <td>{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</td>
                    </tr>
                </table>
            </div>

            @if(!empty($schedule->note))
                <div class="note-box">
                    <strong>Ghi chú tự học:</strong><br>
                    {!! nl2br(e($schedule->note)) !!}
                </div>
            @endif

            <p>Chúc bạn có một buổi tự học thật hiệu quả và đạt nhiều tiến bộ!</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} TunAcademy. Chúc bạn học thật vui, thi thật tự tin!</p>
        </div>
    </div>
</body>
</html>
