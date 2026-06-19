<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('teacher');
    }

    public function view(User $user, Exam $exam): bool
    {
        return $user->hasRole('teacher') && $exam->teacher_id === $user->id;
    }

    public function update(User $user, Exam $exam): bool
    {
        return $user->hasRole('teacher') && $exam->teacher_id === $user->id;
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->hasRole('teacher') && $exam->teacher_id === $user->id;
    }
}
