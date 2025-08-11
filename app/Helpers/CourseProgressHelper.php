<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Course;
use App\Models\LectureUserProgress;
use App\Services\LectureCountService;

class CourseProgressHelper
{
    protected $lectureCountService;

    public function __construct(LectureCountService $lectureCountService)
    {
        $this->lectureCountService = $lectureCountService;
    }

    public function checkCertificateEligibility(User $user, Course $course): bool
    {
        if (!$user) return false;

        $totalLectures = $course->sections()
            ->withCount('lectures')
            ->get()
            ->sum('lectures_count');

        $completedLectures = $this->lectureCountService->getCompletedLectureCount($course, $user);

        // User is eligible if they completed at least 80% of the lectures
        return ($totalLectures > 0) && (($completedLectures / $totalLectures) >= 0.8);
    }

    public function getUserProgress(User $user, Course $course): ?array
    {
        if (!$user) return null;

        return [
            'completedLectures' => $this->lectureCountService->getCompletedLectureCount($course, $user),
            'lastAccessedLecture' => $this->getLastAccessedLecture($user, $course),
            'certificateEligible' => $this->checkCertificateEligibility($user, $course)
        ];
    }

    public function getLastAccessedLecture(User $user, Course $course)
    {
        return LectureUserProgress::where('user_id', $user->id)
            ->whereHas('lecture', function ($query) use ($course) {
                $query->whereHas('section', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                });
            })
            ->with('lecture')
            ->latest()
            ->first()?->lecture;
    }
}
