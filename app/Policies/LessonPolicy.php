<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    // Admin can do anything
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    // Any authenticated teacher can create lessons
    public function create(User $user): bool
    {
        return $user->hasRole('teacher');
    }

    // Only the lesson's own teacher may view, edit, delete
    public function view(User $user, Lesson $lesson): bool
    {
        return $user->hasRole('teacher') && $lesson->teacher_id === $user->id;
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->hasRole('teacher') && $lesson->teacher_id === $user->id;
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->hasRole('teacher') && $lesson->teacher_id === $user->id;
    }
}
