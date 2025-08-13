<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class CourseEnrollmentController extends Controller
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->middleware('auth');
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Enroll user in a course
     *
     * @param int $courseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enroll($courseId)
    {
        try {
       
            $user = Auth::user();
            $course = Course::findOrFail($courseId);

            // Use enrollment service for robust handling
            $enrollment = $this->enrollmentService->enrollUserInCourse($user, $courseId);

            // Check if enrollment already existed
            if (!$enrollment->wasRecentlyCreated) {
                return Redirect::back()->with('info', 'You are already enrolled in this course.');
            }

            return Redirect::back()->with('success', 'Successfully enrolled in the course!');
        } catch (\Exception $e) {
            Log::error('Enrollment error: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to enroll in the course. Please try again.');
        }
    }

    /**
     * Check enrollment status
     *
     * @param int $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEnrollment($courseId)
    {
        $user = Auth::user();
        $isEnrolled = $this->enrollmentService->isUserEnrolled($user, $courseId);

        return response()->json(['isEnrolled' => $isEnrolled]);
    }
}
