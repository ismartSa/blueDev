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
     * تحديد ما إذا كان يمكن للمستخدم بدء الاختبار
     */
    public function attempt(User $user, Quiz $quiz): bool
    {
        // التحقق من وجود تسجيل نشط
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->where('enrollment_status', 'confirmed')
            ->first();

        if (!$enrollment) {
            return false;
        }

        // التحقق من عدد المحاولات السابقة
        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->count();

        return $attempts < 3; // السماح بثلاث محاولات كحد أقصى
    }

    /**
     * تحديد ما إذا كان يمكن للمستخدم عرض نتائج الاختبار
     */
    public function viewResults(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->course->user_id || $user->hasRole('admin');
    }
}
