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
     * تحديد ما إذا كان يمكن للمستخدم التسجيل في الدورة
     */
    public function enroll(User $user, Course $course): bool
    {
        // التحقق من عدم وجود تسجيل سابق
        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        return !$existingEnrollment && $course->status;
    }

    /**
     * تحديد ما إذا كان يمكن للمستخدم إلغاء التسجيل
     */
    public function unenroll(User $user, Enrollment $enrollment): bool
    {
        return $user->id === $enrollment->user_id;
    }

    /**
     * تحديد ما إذا كان يمكن للمستخدم عرض تقدمه
     */
    public function viewProgress(User $user, Enrollment $enrollment): bool
    {
        return $user->id === $enrollment->user_id || $user->hasRole('admin');
    }
}
