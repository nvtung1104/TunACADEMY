<?php
namespace App\Http\Controllers\Api\Admin;

use App\Exports\{UsersExport, ExamResultsExport};
use App\Http\Controllers\Controller;
use App\Models\{User, Classroom, Exam, ExamAttempt, Lesson, Assignment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function overview()
    {
        $data = Cache::remember('report.overview', 300, function () {
            $month = now()->month;
            $year  = now()->year;
            return [
                'total_students'      => User::role('student')->count(),
                'total_teachers'      => User::role('teacher')->count(),
                'total_classrooms'    => Classroom::count(),
                'total_exams'         => Exam::count(),
                'total_lessons'       => Lesson::count(),
                'total_assignments'   => Assignment::count(),
                'exams_this_month'    => Exam::whereYear('created_at', $year)->whereMonth('created_at', $month)->count(),
                'attempts_this_month' => ExamAttempt::whereYear('submitted_at', $year)->whereMonth('submitted_at', $month)->whereNotNull('submitted_at')->count(),
                'avg_score_this_month'=> ExamAttempt::whereYear('submitted_at', $year)->whereMonth('submitted_at', $month)->whereNotNull('score')->avg('score'),
            ];
        });

        return $this->success($data);
    }

    public function users(Request $request)
    {
        $role = $request->input('role', 'student');
        $users = User::role($role)->with('roles')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(fn($q) => $q->where('name', 'like', '%' . $request->search . '%')->orWhere('email', 'like', '%' . $request->search . '%')))
            ->paginate(50);

        return $this->success($users);
    }

    public function exams(Request $request)
    {
        $attempts = ExamAttempt::with(['exam.subject', 'exam.classroom', 'student'])
            ->when($request->exam_id, fn($q) => $q->where('exam_id', $request->exam_id))
            ->when($request->classroom_id, fn($q) => $q->whereHas('exam', fn($q) => $q->where('classroom_id', $request->classroom_id)))
            ->when($request->from, fn($q) => $q->where('submitted_at', '>=', $request->from))
            ->when($request->to, fn($q) => $q->where('submitted_at', '<=', $request->to))
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->paginate(50);

        return $this->success($attempts);
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:students,teachers,exam-results',
        ]);

        $fileName = match ($request->type) {
            'students'     => 'bao-cao-hoc-sinh-' . now()->format('Y-m-d') . '.xlsx',
            'teachers'     => 'bao-cao-giao-vien-' . now()->format('Y-m-d') . '.xlsx',
            'exam-results' => 'bao-cao-ket-qua-thi-' . now()->format('Y-m-d') . '.xlsx',
        };

        $export = match ($request->type) {
            'students'     => new UsersExport('student'),
            'teachers'     => new UsersExport('teacher'),
            'exam-results' => new ExamResultsExport($request->exam_id),
        };

        return Excel::download($export, $fileName);
    }
}
