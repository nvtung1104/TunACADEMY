<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Classroom, Exam, ExamAttempt, Assignment};
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function overview()
    {
        return $this->success([
            'total_students'  => User::role('student')->count(),
            'total_teachers'  => User::role('teacher')->count(),
            'total_classrooms'=> Classroom::count(),
            'total_exams'     => Exam::count(),
            'exams_this_month'=> Exam::whereMonth('created_at', now()->month)->count(),
        ]);
    }

    public function users(Request $request)
    {
        $role = $request->input('role', 'student');
        $users = User::role($role)->with('roles')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->paginate(50);
        return $this->success($users);
    }

    public function exams(Request $request)
    {
        $attempts = ExamAttempt::with(['exam.subject', 'student'])
            ->when($request->exam_id, fn($q) => $q->where('exam_id', $request->exam_id))
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->paginate(50);
        return $this->success($attempts);
    }

    public function export(Request $request)
    {
        // Placeholder — implement Excel export với maatwebsite/excel
        return $this->error('Export chưa được triển khai', 501);
    }
}
