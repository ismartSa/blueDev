<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use App\Http\Requests\EnrollmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

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
                return redirect()->route('courses.player', [
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

    /**
     * Display user's enrolled courses
     */
    public function myCourses()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        
        $enrolledCourses = $user->enrollments()
            ->with([
                'course' => function ($query) {
                    $query->with(['category', 'instructor', 'lectures'])
                          ->withCount('lectures');
                },
                'course.lectures' => function ($query) use ($user) {
                    $query->whereHas('userProgress', function ($q) use ($user) {
                        $q->where('user_id', $user->id)->where('is_completed', true);
                    });
                }
            ])
            ->get()
            ->map(function ($enrollment) {
                $course = $enrollment->course;
                $completedLectures = $course->lectures->count();
                $totalLectures = $course->lectures_count;
                
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'image' => $course->image,
                    'category' => $course->category->name ?? 'Uncategorized',
                    'instructor' => $course->instructor->name,
                    'progress' => $totalLectures > 0 ? round(($completedLectures / $totalLectures) * 100) : 0,
                    'completed_lectures' => $completedLectures,
                    'user_enrolled' => true, // User is enrolled since this is from enrollments
                    'total_lectures' => $totalLectures,
                    'enrolled_at' => $enrollment->created_at->format('M d, Y'),
                ];
            });
        
        $totalEnrolled = $enrolledCourses->count();
        $completed = $enrolledCourses->where('progress', 100)->count();
        $inProgress = $enrolledCourses->where('progress', '>', 0)->where('progress', '<', 100)->count();
        $notStarted = $enrolledCourses->where('progress', 0)->count();
        
        $stats = [
            'total_enrolled' => $totalEnrolled,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'not_started' => $notStarted,
        ];
        
        // Get course suggestions (limit to 6 for initial load)
        $suggestions = Course::with(['category', 'instructor'])
            ->where('status', 'published')
            ->whereNotIn('id', $enrolledCourses->pluck('id'))
            ->limit(6)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'description' => Str::limit($course->description, 100),
                    'image' => $course->image,
                    'category' => $course->category->name ?? 'Uncategorized',
                    'instructor' => $course->instructor->name,
                    'price' => $course->price,
                    'is_free' => $course->isFree(),
                    'level' => $course->level,
                    'duration' => $course->formattedDuration(),
                ];
            });
        
        return Inertia::render('Courses/MyCourses', [
            'enrolledCourses' => $enrolledCourses,
            'suggestions' => $suggestions,
            'stats' => $stats,
        ]);
    }

    /**
     * Enroll user in a course via API
     */
    public function enrollApi(Request $request, $courseId)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        $course = Course::findOrFail($courseId);
        
        // Check if already enrolled
        if ($user->enrollments()->where('course_id', $courseId)->exists()) {
            return response()->json(['message' => 'Already enrolled in this course'], 400);
        }
        
        // Check if course is free
        if (!$course->isFree()) {
            return response()->json(['message' => 'This course requires payment'], 400);
        }
        
        $this->enrollmentService->enrollUserInCourse($user, $courseId);
        
        return response()->json([
            'message' => 'Successfully enrolled in course',
            'course' => $course->title
        ]);
    }
}
