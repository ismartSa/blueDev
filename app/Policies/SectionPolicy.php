<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Section;
use App\Models\Enrollment;
use Illuminate\Auth\Access\HandlesAuthorization;

class SectionPolicy
{
    use HandlesAuthorization;

    /**
     * تحديد ما إذا كان يمكن للمستخدم عرض القسم
     */
    public function view(User $user, Section $section): bool
    {
        // التحقق من وجود تسجيل نشط في الدورة
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $section->course_id)
            ->where('enrollment_status', 'confirmed')
            ->first();

        return $enrollment !== null || $user->hasRole('admin');
    }

    /**
     * تحديد ما إذا كان يمكن للمستخدم الوصول إلى محتوى القسم
     */
    public function access(User $user, Section $section): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $section->course_id)
            ->where('enrollment_status', 'confirmed')
            ->first();

        if (!$enrollment) {
            return false;
        }

        // التحقق من إكمال الأقسام السابقة
        $previousSections = Section::where('course_id', $section->course_id)
            ->where('order', '<', $section->order)
            ->get();

        foreach ($previousSections as $prevSection) {
            if (!$user->hasCompletedSection($prevSection->id)) {
                return false;
            }
        }

        return true;
    }
}
