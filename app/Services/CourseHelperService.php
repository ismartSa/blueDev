<?php

namespace App\Services;

use App\Models\Enrollment;

class CourseHelperService
{
    public function calculateCompletionRate($course)
    {
        $totalEnrollments = $course->enrollments()->count();
        if (!$totalEnrollments) return 0;

        $completedEnrollments = $course->enrollments()
            ->where('completion_status', 'completed')
            ->count();

        return round(($completedEnrollments / $totalEnrollments) * 100);
    }

    public function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        return sprintf("%dh %02dmin", $hours, $minutes);
    }

    public function getEnrollmentStatus($user, $courseId)
    {
        $enrollment = Enrollment::where('user_id', $user->id)
                                ->where('course_id', $courseId)
                                ->first();
        return $enrollment ? $enrollment->enrollment_status : 'not enrolled';
    }

    // ... other helper methods ...
}
