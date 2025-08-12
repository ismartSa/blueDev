<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnrollPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can enroll in the course
     */
    public function enroll(User $user, Course $course): bool
    {
        // Check for no existing enrollment
        $existingEnrollment = Enrollment::where('user_id', $user->getKey())
            ->where('course_id', $course->getKey())
            ->first();

        return !$existingEnrollment && $course->status;
    }

    /**
     * Determine if user can unenroll from course
     */
    public function unenroll(User $user, Enrollment $enrollment): bool
    {
        return $user->getKey() === $enrollment->user_id;
    }

    /**
     * Determine if user can view enrollment progress
     */
    public function viewProgress(User $user, Enrollment $enrollment): bool
    {
        return $user->getKey() === $enrollment->user_id || $user->hasRole('admin');
    }
}
