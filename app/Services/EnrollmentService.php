<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class EnrollmentService
{
    /**
     * تسجيل مستخدم في دورة
     *
     * @param User $user
     * @param int $courseId
     * @return Enrollment
     * @throws Exception
     */
    public function enrollUserInCourse(User $user, int $courseId): Enrollment
    {
        try {
            $course = Course::findOrFail($courseId);

            // التحقق من حالة الدورة
            if ($course->status !== 'published') {
                throw new Exception('هذه الدورة غير متاحة للتسجيل حالياً');
            }

            // التحقق من التسجيل المسبق
            $existingEnrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->first();

            if ($existingEnrollment) {
                return $existingEnrollment;
            }

            // إنشاء تسجيل جديد
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'enrollment_status' => 'active',
                'enrollment_date' => Carbon::now(),
                'progress_percentage' => 0,
            ]);

            // تسجيل النشاط
            activity()
                ->causedBy($user)
                ->performedOn($course)
                ->log('تم التسجيل في الدورة');

            return $enrollment;
        } catch (Exception $e) {
            Log::error('خطأ في التسجيل بالدورة: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * التحقق من حالة تسجيل المستخدم في دورة
     *
     * @param User $user
     * @param int $courseId
     * @return bool
     */
    public function isUserEnrolled(User $user, int $courseId): bool
    {
        return $user->enrollments()->where('course_id', $courseId)->exists();
    }

    /**
     * تحديث نسبة تقدم المستخدم في الدورة
     *
     * @param User $user
     * @param int $courseId
     * @param int $progressPercentage
     * @return Enrollment
     */
    public function updateProgress(User $user, int $courseId, int $progressPercentage): Enrollment
    {
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $enrollment->progress_percentage = $progressPercentage;

        if ($progressPercentage >= 100) {
            $enrollment->completion_date = Carbon::now();
        }

        $enrollment->save();

        return $enrollment;
    }
}
