<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $schedules = Schedule::where('classroom_id', $request->classroom_id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $this->success($schedules);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'classroom_id' => 'required|exists:classrooms,id',
            'day_of_week'  => 'required|integer|between:2,8',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'color'        => 'nullable|string|max:50',
            'note'         => 'nullable|string',
        ]);

        $data['teacher_id'] = $request->user()->id;

        $schedule = Schedule::create($data);

        return $this->success($schedule, 'Tạo thời khóa biểu thành công', 201);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'day_of_week' => 'sometimes|required|integer|between:2,8',
            'start_time'  => 'sometimes|required|date_format:H:i',
            'end_time'    => 'sometimes|required|date_format:H:i|after:start_time',
            'color'       => 'nullable|string|max:50',
            'note'        => 'nullable|string',
        ]);

        $schedule->update($data);

        return $this->success($schedule, 'Cập nhật thời khóa biểu thành công');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return $this->success(null, 'Xóa thời khóa biểu thành công');
    }
}
