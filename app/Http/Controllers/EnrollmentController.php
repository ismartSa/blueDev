<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use App\Http\Requests\EnrollmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    protected $enrollmentService;

    /**
     * إنشاء مثيل جديد من المتحكم
     */
    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * تسجيل المستخدم في دورة معينة
     *
     * @param int $courseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enroll($courseId)
    {
        try {
            $user = auth()->user();
            $course = Course::findOrFail($courseId);

            // استخدام خدمة التسجيل لتنفيذ عملية التسجيل
            $enrollment = $this->enrollmentService->enrollUserInCourse($user, $courseId);

            // التحقق مما إذا كان التسجيل موجودًا مسبقًا
            if ($enrollment->wasRecentlyCreated === false) {
                return redirect()->route('course.player', [
                    'courseId' => $course->id,
                    'courseSlug' => $course->slug
                ])->with('info', 'أنت مسجل بالفعل في هذه الدورة');
            }

            return redirect()->route('course.details', [
                'id' => $course->id,
                'courseSlug' => $course->slug
            ])->with('success', 'تم التسجيل في الدورة بنجاح');
        } catch (\Exception $e) {
            Log::error('خطأ في التسجيل بالدورة: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء التسجيل في الدورة، يرجى المحاولة مرة أخرى');
        }
    }

    /**
     * التحقق من حالة تسجيل المستخدم في دورة معينة
     *
     * @param int $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEnrollment($courseId)
    {
        $user = auth()->user();
        $isEnrolled = $user->enrollments()->where('course_id', $courseId)->exists();

        return response()->json(['isEnrolled' => $isEnrolled]);
    }
}
