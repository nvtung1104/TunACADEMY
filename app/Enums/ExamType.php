<?php

namespace App\Enums;

enum ExamType: string
{
    case Quiz15      = 'quiz_15';
    case Quiz30      = 'quiz_30';
    case Quiz45      = 'quiz_45';
    case PracticeExam = 'practice_exam';

    public function durationMinutes(): int
    {
        return match ($this) {
            self::Quiz15      => 15,
            self::Quiz30      => 30,
            self::Quiz45      => 45,
            self::PracticeExam => 0,
        };
    }

    public function isTimeLimited(): bool
    {
        return $this !== self::PracticeExam;
    }
}
