<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

/**
 * Handles course enrollment operations including enrollment, progress tracking,
 * and enrollment status management.
 */
class CourseEnrollmentController extends Controller
{
    protected EnrollmentService $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
        $this->middleware('auth');
    }

    /**
     * Enroll user in a course
     */
    public function enroll($courseId)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($courseId);

            // Check existing enrollment
            if ($this->enrollmentService->isUserEnrolled($user, $courseId)) {
                return Redirect::route('courses.learn', $course->id)
                    ->with('info', 'You are already enrolled in this course.');
            }

            // Handle enrollment based on course type
            if ($course->isFree()) {
                $this->enrollmentService->enrollUserInCourse($user, $courseId);
                return Redirect::route('courses.learn', $course->id)
                    ->with('success', 'Successfully enrolled in the free course!');
            }

            // Redirect to payment for paid courses
            return Redirect::route('courses.payment', [
                'course' => $course->id,
                'slug' => $course->slug
            ])->with('info', 'Please complete payment to enroll in this course.');

        } catch (\Exception $e) {
            Log::error('Course enrollment error: ' . $e->getMessage());
            return Redirect::back()
                ->with('error', 'Failed to enroll in the course. Please try again.');
        }
    }

    /**
     * Check enrollment status for a course
     */
    public function checkEnrollment($courseId)
    {
        $user = Auth::user();
        $isEnrolled = $this->enrollmentService->isUserEnrolled($user, $courseId);
        
        $enrollmentData = ['enrolled' => $isEnrolled];
        
        if ($isEnrolled) {
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->first();
            $enrollmentData['enrollment_date'] = $enrollment->enrollment_date;
            $enrollmentData['progress'] = $enrollment->progress_percentage;
        }
        
        return response()->json($enrollmentData);
    }

    /**
     * Update course progress
     */
    public function updateProgress(Request $request, $courseId)
    {
        $request->validate([
            'progress_percentage' => 'required|numeric|min:0|max:100'
        ]);

        try {
            $user = Auth::user();
            $enrollment = $this->enrollmentService->updateProgress(
                $user, 
                $courseId, 
                $request->progress_percentage
            );

            return response()->json([
                'success' => true,
                'progress' => $enrollment->progress_percentage
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
    }

    /**
     * Get user's enrolled courses
     */
    public function myCourses()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $enrollments = $user->enrollments()
            ->with(['course.category', 'course.instructor'])
            ->orderBy('enrollment_date', 'desc')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'progress_percentage' => $enrollment->progress_percentage,
                    'course' => [
                        'id' => $enrollment->course->id,
                        'title' => $enrollment->course->title,
                        'slug' => $enrollment->course->slug,
                        'description' => $enrollment->course->description,
                        'image' => $enrollment->course->image,
                        'category' => $enrollment->course->category?->name,
                        'instructor' => $enrollment->course->instructor?->name,
                        'user_enrolled' => true, // User is enrolled since this is from enrollments
                    ]
                ];
            });

        return Inertia::render('Courses/MyCourses', [
            'enrollments' => $enrollments
        ]);
    }

    /**
     * API endpoint for enrollment
     */
    public function enrollApi(Request $request, $courseId)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($courseId);

            if ($this->enrollmentService->isUserEnrolled($user, $courseId)) {
                return response()->json(['error' => 'Already enrolled'], 400);
            }

            if ($course->isFree()) {
                $enrollment = $this->enrollmentService->enrollUserInCourse($user, $courseId);
                return response()->json([
                    'success' => true,
                    'enrollment' => $enrollment
                ]);
            }

            return response()->json(['error' => 'Payment required'], 402);

        } catch (\Exception $e) {
            Log::error('API enrollment error: ' . $e->getMessage());
            return response()->json(['error' => 'Enrollment failed'], 500);
        }
    }

    /**
     * Show course enrollments (admin)
     */
    public function enrollments(Course $course)
    {
        $this->authorize('manage courses');
        
        $enrollments = Enrollment::with(['user:id,name,email'])
            ->where('course_id', $course->id)
            ->orderBy('enrollment_date', 'desc')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'progress_percentage' => $enrollment->progress_percentage,
                    'enrollment_status' => $enrollment->enrollment_status,
                    'user' => [
                        'id' => $enrollment->user->id,
                        'name' => $enrollment->user->name,
                        'email' => $enrollment->user->email,
                    ]
                ];
            });
        
        // Calculate statistics
        $totalEnrollments = $course->enrollments()->count();
        $activeStudents = $course->enrollments()->where('enrollment_status', 'confirmed')->count();
        $totalLectures = $course->lectures()->count();
        
        // Calculate completion rate
        $completedEnrollments = $course->enrollments()
            ->where('progress_percentage', '>=', 100)
            ->count();
        
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100) : 0;

        $stats = [
            'total_enrollments' => $totalEnrollments,
            'active_students' => $activeStudents,
            'completion_rate' => $completionRate,
            'total_lessons' => $totalLectures,
        ];
        
        return Inertia::render('Dashboard/Courses/Enrollments', [
            'course' => $course,
            'enrollments' => $enrollments,
            'stats' => $stats
        ]);
    }
}