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
     * تحديد ما إذا كان يمكن للمستخدم عرض المحاضرة
     */
    public function view(User $user, Lecture $lecture): bool
    {
        // التحقق من وجود تسجيل نشط في الدورة
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $lecture->course_id)
            ->where('enrollment_status', 'confirmed')
            ->first();

        return $enrollment !== null || $user->hasRole('admin');
    }

    /**
     * تحديد ما إذا كان يمكن للمستخدم تحديث تقدمه في المحاضرة
     */
    public function updateProgress(User $user, Lecture $lecture): bool
    {
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $lecture->course_id)
            ->where('enrollment_status', 'confirmed')
            ->first();

        return $enrollment !== null;
    }
}
