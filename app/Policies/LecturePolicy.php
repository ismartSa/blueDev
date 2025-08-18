<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Lecture;
use App\Models\Enrollment;
use Illuminate\Auth\Access\HandlesAuthorization;

class LecturePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view the lecture
     */
    public function view(User $user, Lecture $lecture): bool
    {
        // Check for active enrollment in course
        $enrollment = Enrollment::where('user_id', $user->getKey())
            ->where('course_id', $lecture->course_id)
            ->where('enrollment_status', 'confirmed')
            ->first();

        return $enrollment !== null || $user->hasRole('admin');
    }

    /**
     * Determine if user can update lecture progress
     */
    public function updateProgress(User $user, Lecture $lecture): bool
    {
        $enrollment = Enrollment::where('user_id', $user->getKey())
            ->where('course_id', $lecture->course_id)
            ->where('enrollment_status', 'confirmed')
            ->first();

        return $enrollment !== null;
    }
}
