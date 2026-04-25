<?php
namespace App\Exports;

use App\Models\ExamAttempt;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExamResultsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private ?int $examId = null) {}

    public function query()
    {
        return ExamAttempt::with(['exam.subject', 'exam.classroom', 'student'])
            ->when($this->examId, fn($q) => $q->where('exam_id', $this->examId))
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at', 'desc');
    }

    public function headings(): array
    {
        return ['ID', 'Học sinh', 'Email', 'Bài kiểm tra', 'Môn học', 'Lớp', 'Điểm', 'Số câu đúng', 'Thời gian nộp', 'Trạng thái'];
    }

    public function map($attempt): array
    {
        return [
            $attempt->id,
            $attempt->student->name ?? '',
            $attempt->student->email ?? '',
            $attempt->exam->title ?? '',
            $attempt->exam->subject->name ?? '',
            $attempt->exam->classroom->name ?? '',
            $attempt->score ?? 0,
            $attempt->total_correct ?? 0,
            $attempt->submitted_at->format('d/m/Y H:i'),
            $attempt->status,
        ];
    }
}
