<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use App\Http\Requests\EnrollmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    protected $enrollmentService;

    /**
     * Create a new controller instance
     */
    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->middleware('auth');
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Enroll user in a specific course
     *
     * @param int $courseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enroll($courseId)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($courseId);

            // Use enrollment service to execute enrollment process
            $enrollment = $this->enrollmentService->enrollUserInCourse($user, $courseId);

            // Check if enrollment already exists
            if ($enrollment->wasRecentlyCreated === false) {
                return redirect()->route('course.player', [
                    'courseId' => $course->id,
                    'courseSlug' => $course->slug
                ])->with('info', 'You are already enrolled in this course');
            }

            return redirect()->route('course.details', [
                'id' => $course->id,
                'courseSlug' => $course->slug
            ])->with('success', 'Successfully enrolled in the course');
        } catch (\Exception $e) {
            Log::error('Course enrollment error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during course enrollment, please try again');
        }
    }

    /**
     * Check user enrollment status in a specific course
     *
     * @param int $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEnrollment($courseId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isEnrolled = $user->enrollments()->where('course_id', $courseId)->exists();

        return response()->json(['isEnrolled' => $isEnrolled]);
    }
}
