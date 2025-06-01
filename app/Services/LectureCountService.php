<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\LectureUserProgress;

class LectureCountService
{
    public function getCompletedLectureCount(Course $course, User $user): int
    {
        return LectureUserProgress::where('user_id', $user->id)
            ->whereHas('lecture', function ($query) use ($course) {
                $query->whereHas('section', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                });
            })
            ->where('completed', true)
            ->count();
    }
}
