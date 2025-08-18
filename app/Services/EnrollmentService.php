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
    private const DEFAULT_ENROLLMENT_STATUS = 'confirmed';
    private const COMPLETION_THRESHOLD = 100;

    /**
     * Enroll user in a course
     */
    public function enrollUserInCourse(User $user, int $courseId): Enrollment
    {
        try {
            $course = Course::findOrFail($courseId);
            $this->validateCourseAvailability($course);
            
            return $this->findOrCreateEnrollment($user, $courseId);
        } catch (Exception $e) {
            Log::error('Course enrollment error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if user is enrolled in a course
     */
    public function isUserEnrolled(User $user, int $courseId): bool
    {
        return $user->enrollments()->where('course_id', $courseId)->exists();
    }

    /**
     * Update user progress in course
     */
    public function updateProgress(User $user, int $courseId, int $progressPercentage): Enrollment
    {
        $enrollment = $this->getUserEnrollment($user, $courseId);
        
        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'completion_date' => $progressPercentage >= self::COMPLETION_THRESHOLD ? Carbon::now() : null
        ]);

        return $enrollment->fresh();
    }

    /**
     * Validate course availability for enrollment
     */
    private function validateCourseAvailability(Course $course): void
    {
        if (!$course->isPublished()) {
            throw new Exception('This course is not available for enrollment currently');
        }
    }

    /**
     * Find existing enrollment or create new one
     */
    private function findOrCreateEnrollment(User $user, int $courseId): Enrollment
    {
        return Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $courseId],
            [
                'enrollment_status' => self::DEFAULT_ENROLLMENT_STATUS,
                'enrollment_date' => Carbon::now(),
                'progress_percentage' => 0,
            ]
        );
    }

    /**
     * Get user enrollment for a specific course
     */
    private function getUserEnrollment(User $user, int $courseId): Enrollment
    {
        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();
    }
}
