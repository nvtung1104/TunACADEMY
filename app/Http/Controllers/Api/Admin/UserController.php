<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\ClassroomStudent;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->latest();

        if ($request->filled('role'))   $query->role($request->role);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where(fn($q) =>
            $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%")
        );

        return UserResource::collection($query->paginate(min((int) $request->input('per_page', 20), 100)));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $classroomId = $data['classroom_id'] ?? null;
        $subjectIds  = $data['subject_ids'] ?? [];
        unset($data['classroom_id'], $data['subject_ids'], $data['role']);

        $user = User::create([...$data, 'password' => Hash::make($request->password)]);
        $user->assignRole($request->role);

        if ($classroomId && $request->role === 'student') {
            ClassroomStudent::create([
                'classroom_id' => $classroomId,
                'student_id'   => $user->id,
                'joined_at'    => now(),
                'status'       => 'active',
            ]);
        }

        if (!empty($subjectIds) && $request->role === 'teacher') {
            $this->syncTeacherSubjects($user->id, $subjectIds);
        }

        return $this->success(new UserResource($user->load('roles')), 'Tạo tài khoản thành công', 201);
    }

    public function show(User $user)
    {
        $user->load('roles');
        $role = $user->roles->first()?->name;
        $base = new UserResource($user);

        if ($role === 'student') {
            $user->load([
                'classroomStudents' => fn($q) => $q->with([
                    'classroom' => fn($q) => $q->with(['grade', 'schoolYear', 'homeroomTeacher']),
                ]),
                'scores' => fn($q) => $q->with(['subject', 'schoolYear'])->orderBy('school_year_id', 'desc'),
            ]);

            return $this->success([
                'user' => $base,
                'classrooms' => $user->classroomStudents->map(fn($cs) => [
                    'classroom'        => $cs->classroom->name ?? null,
                    'grade'            => $cs->classroom->grade->name ?? null,
                    'school_year'      => $cs->classroom->schoolYear->name ?? null,
                    'homeroom_teacher' => $cs->classroom->homeroomTeacher->name ?? null,
                    'status'           => $cs->status,
                ]),
                'scores' => $user->scores->map(fn($s) => [
                    'subject'        => $s->subject->name ?? null,
                    'school_year'    => $s->schoolYear->name ?? null,
                    'assignment_avg' => $s->assignment_avg,
                    'exam_avg'       => $s->exam_avg,
                    'final_score'    => $s->final_score,
                    'classification' => $s->classification,
                ]),
            ]);
        }

        if ($role === 'teacher') {
            $user->load([
                'teacherSubjects' => fn($q) => $q->with('subject'),
                'assignedClassroomSubjects' => fn($q) => $q->with(['classroom.grade', 'subject', 'schoolYear']),
                'homeroomClassrooms' => fn($q) => $q->with(['grade', 'schoolYear']),
            ]);

            return $this->success([
                'user' => $base,
                'subjects' => $user->teacherSubjects->map(fn($ts) => [
                    'id'       => $ts->subject->id ?? null,
                    'name'     => $ts->subject->name ?? null,
                    'code'     => $ts->subject->code ?? null,
                    'color'    => $ts->subject->color ?? '#6b7280',
                    'verified' => (bool) $ts->verified,
                ]),
                'teaching_assignments' => $user->assignedClassroomSubjects->map(fn($cst) => [
                    'classroom'   => $cst->classroom->name ?? null,
                    'grade'       => $cst->classroom->grade->name ?? null,
                    'subject'     => $cst->subject->name ?? null,
                    'school_year' => $cst->schoolYear->name ?? null,
                ])->unique(fn($a) => $a['classroom'].$a['subject'].$a['school_year'])->values(),
                'homeroom_classrooms' => $user->homeroomClassrooms->map(fn($c) => [
                    'name'        => $c->name,
                    'grade'       => $c->grade->name ?? null,
                    'school_year' => $c->schoolYear->name ?? null,
                ]),
            ]);
        }

        return $this->success($base);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $classroomId = array_key_exists('classroom_id', $data) ? $data['classroom_id'] : false;
        $subjectIds  = array_key_exists('subject_ids', $data) ? $data['subject_ids'] : false;
        unset($data['classroom_id'], $data['subject_ids']);

        if (!empty($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);

        $user->update($data);
        if ($request->filled('role')) $user->syncRoles([$request->role]);

        if ($classroomId !== false && $user->hasRole('student')) {
            if ($classroomId) {
                $alreadyIn = ClassroomStudent::where('student_id', $user->id)
                    ->where('classroom_id', $classroomId)
                    ->where('status', 'active')
                    ->exists();

                if (!$alreadyIn) {
                    ClassroomStudent::where('student_id', $user->id)
                        ->where('status', 'active')
                        ->update(['status' => 'transferred', 'left_at' => now()]);

                    ClassroomStudent::create([
                        'classroom_id' => $classroomId,
                        'student_id'   => $user->id,
                        'joined_at'    => now(),
                        'status'       => 'active',
                    ]);
                }
            } else {
                ClassroomStudent::where('student_id', $user->id)
                    ->where('status', 'active')
                    ->update(['status' => 'transferred', 'left_at' => now()]);
            }
        }

        if ($subjectIds !== false && $user->hasRole('teacher')) {
            $this->syncTeacherSubjects($user->id, $subjectIds ?? []);
        }

        return $this->success(new UserResource($user->fresh('roles')), 'Cập nhật thành công');
    }

    private function syncTeacherSubjects(int $teacherId, array $subjectIds): void
    {
        TeacherSubject::where('teacher_id', $teacherId)->delete();

        if (empty($subjectIds)) return;

        $adminId = auth()->id();
        $now     = now();
        TeacherSubject::insert(array_map(fn($sid) => [
            'teacher_id'  => $teacherId,
            'subject_id'  => $sid,
            'verified'    => true,
            'verified_by' => $adminId,
            'verified_at' => $now,
            'created_at'  => $now,
            'updated_at'  => $now,
        ], $subjectIds));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return $this->success(null, 'Xóa tài khoản thành công');
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:admin,teacher,student']);
        $user->syncRoles([$request->role]);
        return $this->success(new UserResource($user->load('roles')), 'Phân quyền thành công');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        return $this->success(new UserResource($user->fresh()), 'Cập nhật trạng thái thành công');
    }
}
