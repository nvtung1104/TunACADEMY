<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // List classroom timetables for the student's active classrooms
    public function classroomSchedules(Request $request)
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id')->toArray();

        $schedules = Schedule::whereIn('classroom_id', $classroomIds)
            ->with(['classroom', 'teacher:id,name'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $this->success($schedules);
    }

    // List personal timetables
    public function index(Request $request)
    {
        $schedules = Schedule::where('student_id', $request->user()->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $this->success($schedules);
    }

    // Create personal timetable
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'day_of_week'=> 'required|integer|between:2,8',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'color'      => 'nullable|string|max:50',
            'note'       => 'nullable|string',
        ]);

        $data['student_id'] = $request->user()->id;

        $schedule = Schedule::create($data);

        return $this->success($schedule, 'Tạo lịch tự học thành công', 201);
    }

    // Update personal timetable
    public function update(Request $request, Schedule $personalSchedule)
    {
        // Ensure student owns the schedule
        if ((int) $personalSchedule->student_id !== (int) $request->user()->id) {
            return $this->error('Không có quyền chỉnh sửa', 403);
        }

        $data = $request->validate([
            'title'      => 'sometimes|required|string|max:255',
            'day_of_week'=> 'sometimes|required|integer|between:2,8',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time'   => 'sometimes|required|date_format:H:i|after:start_time',
            'color'      => 'nullable|string|max:50',
            'note'       => 'nullable|string',
        ]);

        $personalSchedule->update($data);

        return $this->success($personalSchedule, 'Cập nhật lịch tự học thành công');
    }

    // Delete personal timetable
    public function destroy(Request $request, Schedule $personalSchedule)
    {
        // Ensure student owns the schedule
        if ((int) $personalSchedule->student_id !== (int) $request->user()->id) {
            return $this->error('Không có quyền xóa', 403);
        }

        $personalSchedule->delete();

        return $this->success(null, 'Xóa lịch tự học thành công');
    }
}
