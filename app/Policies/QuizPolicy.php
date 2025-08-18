<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuizPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can attempt the quiz
     */
    public function attempt(User $user, Quiz $quiz): bool
    {
        // Check for active enrollment
        $enrollment = Enrollment::where('user_id', $user->getKey())
            ->where('course_id', $quiz->course_id)
            ->where('enrollment_status', 'confirmed')
            ->first();

        if (!$enrollment) {
            return false;
        }

        // Check previous attempts count
        $attempts = QuizAttempt::where('user_id', $user->getKey())
            ->where('quiz_id', $quiz->getKey())
            ->count();

        return $attempts < 3; // Allow maximum 3 attempts
    }

    /**
     * Determine if user can view quiz results
     */
    public function viewResults(User $user, Quiz $quiz): bool
    {
        return $user->getKey() === $quiz->course->user_id || $user->hasRole('admin');
    }
}
