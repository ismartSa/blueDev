<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;
use App\Models\Course;
use App\Models\Section;
use App\Models\Lecture;
use App\Services\CourseService;
use App\Http\Resources\CourseResource;
use App\Http\Requests\CourseStoreRequest;
use App\Http\Requests\CourseUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
        $this->middleware('permission:create courses', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit courses', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete courses', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of courses for admin dashboard
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'field', 'order', 'perPage']);
        $courses = $this->courseService->getFilteredCourses($filters);
        $categories = Category::select(['id', 'name'])->orderBy('name')->get();

        return Inertia::render('Dashboard/Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Display course details for admin
     */
    public function details($id)
    {
        $course = Course::with([
            'sections.lectures',
            'instructor:id,name',
            'category' => function ($query) {
                $query->select('id', 'name');
            }
        ])->findOrFail($id);

        return inertia('Dashboard/Course/DetailsCourse', [
            'course' => new CourseResource($course),
            'lectures' => $course->sections->flatMap->lectures,
            'title' => __('courses.title'),
            'breadcrumbs' => [
                ['label' => __('dashboard'), 'href' => route('dashboard')],
            ],
        ]);
    }

    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        $categories = Category::all();
        return Inertia::render('Dashboard/Course/CreateCourse', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created course
     */
    public function store(CourseStoreRequest $request)
    {
        try {
            $course = $this->courseService->create($request->validated());
            
            Log::info(__('courses.log.created_success'), ['course_id' => $course->id]);
            
            return redirect()->route('courses.index')
                ->with('success', __('courses.created_success'));
        } catch (\Exception $e) {
            Log::error(__('courses.log.creation_error'), ['error' => $e->getMessage()]);
            return back()->with('error', __('courses.creation_error') . $e->getMessage());
        }
    }

    /**
     * Display the specified course for public view
     */
    public function show($id, $courseSlug)
    {
        try {
            $course = Course::with([
                'sections.lectures',
                'instructor:id,name',
                'category' => function ($query) {
                    $query->select('id', 'name');
                }
            ])->findOrFail($id);

            $courseData = $this->courseService->formatCourseForDisplay($course);

            return inertia('Index/Course/Show', $courseData);
        } catch (\Exception $e) {
            Log::error('Course display error: ' . $e->getMessage());
            return redirect()->route('courses.explore')
                ->with('error', 'An error occurred while displaying the course.');
        }
    }

    /**
     * Display courses for public exploration
     */
    public function explore(Request $request)
    {
        $filters = $request->only(['search', 'category', 'price', 'sort']);
        $courses = $this->courseService->getPublicCourses($filters);
        $categories = Category::all(['id', 'name']);
        $stats = $this->courseService->getPlatformStats();

        return Inertia::render('Courses/Explore', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified course
     */
    public function edit(Course $course)
    {
        $this->authorize('update', $course);
        $categories = Category::all();

        return Inertia::render('Dashboard/Courses/Edit', [
            'course' => $course,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified course
     */
    public function update(CourseUpdateRequest $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validated();

        try {
            $this->courseService->update($course, $validated);
            
            return Redirect::route('courses.index')
                ->with('success', 'Course updated successfully!');
        } catch (\Exception $e) {
            Log::error('Course update error: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to update course.');
        }
    }

    /**
     * Remove the specified course
     */
    public function destroy(Course $course)
    {
        try {
            $course->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => $course->title]));
        } catch (\Exception $e) {
            return back()->with('error', __('app.label.deleted_error', ['name' => $course->title]) . $e->getMessage());
        }
    }

    /**
     * Load course with optimized eager loading
     */
    private function loadCourseWithRelations($courseId, $includeCategory = true)
    {
        $relations = [
            'sections.lectures' => function ($query) {
                $query->orderBy('order');
            },
            'instructor:id,name'
        ];
        
        if ($includeCategory) {
            $relations['category'] = function ($query) {
                $query->select('id', 'name');
            };
        }
        
        return Course::with($relations)->findOrFail($courseId);
    }

    /**
     * Prepare common course player data
     */
    private function prepareCoursePlayerData($course, $enrollment, $currentLectureId = null)
    {
        $lectures = $course->sections->flatMap->lectures->sortBy('order');
        $viewedLectureIds = $enrollment->viewedLectures->pluck('id')->toArray();
        
        $data = [
            'course' => $course,
            'sections' => $course->sections,
            'lectures' => $lectures,
            'viewedLectureIds' => $viewedLectureIds,
            'totalLectures' => $lectures->count(),
            'completedLectures' => count($viewedLectureIds),
            'progressPercentage' => $lectures->count() > 0 ? round((count($viewedLectureIds) / $lectures->count()) * 100) : 0
        ];
        
        if ($currentLectureId) {
            $currentIndex = $lectures->search(function ($item) use ($currentLectureId) {
                return $item->id == $currentLectureId;
            });
            
            $data['currentLecture'] = $lectures->firstWhere('id', $currentLectureId);
            $data['currentIndex'] = $currentIndex;
            $data['previousLecture'] = $currentIndex > 0 ? $lectures->values()[$currentIndex - 1] : null;
            $data['nextLecture'] = $currentIndex < $lectures->count() - 1 ? $lectures->values()[$currentIndex + 1] : null;
        }
        
        return $data;
    }



    /**
     * Validate user enrollment for a course
     */
    private function validateEnrollment($course, $courseSlug, $errorRoute = 'courses.show')
    {
        $enrollment = $course->enrollments()->where('user_id', Auth::id())->first();
        
        if (!$enrollment) {
            return redirect()->route($errorRoute, [$course->id, $courseSlug])
                ->with('error', 'You must be enrolled in this course to access this content.');
        }
        
        return $enrollment;
    }

    /**
     * Display the course player interface for enrolled users
     */
    public function coursePlayer($courseId, $courseSlug)
    {
        // Load course with optimized relationships
        $course = $this->loadCourseWithRelations($courseId);
        
        // Validate enrollment
        $enrollment = $this->validateEnrollment($course, $courseSlug);
        if ($enrollment instanceof \Illuminate\Http\RedirectResponse) {
            return $enrollment;
        }
        
        // Prepare course player data
        $playerData = $this->prepareCoursePlayerData($course, $enrollment);
        
        return Inertia::render('Courses/Player', $playerData);
    }

    /**
     * Watch a specific lecture in a course
     */
    public function watchLecture($courseId, $courseSlug, $lectureId)
    {
        // Load course with optimized relationships
        $course = $this->loadCourseWithRelations($courseId, false);
        
        // Validate enrollment
        $enrollment = $this->validateEnrollment($course, $courseSlug, 'courses.details');
        if ($enrollment instanceof \Illuminate\Http\RedirectResponse) {
            return $enrollment;
        }
        
        // Find the lecture from already loaded relationships to avoid additional query
        $lecture = $course->sections->flatMap->lectures->firstWhere('id', $lectureId);
        if (!$lecture) {
            abort(404, 'Lecture not found');
        }
        
        // Mark lecture as viewed efficiently
        $course->lectures()->find($lectureId)->usersProgress()->updateOrCreate(
            ['user_id' => Auth::id(), 'lecture_id' => $lectureId],
            ['completed' => true]
        );
        
        // Prepare course player data with current lecture
        $playerData = $this->prepareCoursePlayerData($course, $enrollment, $lectureId);
        
        return Inertia::render('Courses/Player', $playerData);
    }
    
    /**
     * Mark lecture as completed
     */
    public function markLectureAsCompleted(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'lecture_id' => 'required|exists:lectures,id',
        ]);
        
        $course = Course::findOrFail($request->course_id);
        $lecture = $course->lectures()->findOrFail($request->lecture_id);
        
        $enrollment = $course->enrollments()->where('user_id', $user->id)->first();
        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'Not enrolled'], 403);
        }
        
        // Mark lecture as completed
        $this->markLectureAsCompletedInternal($enrollment, $lecture);
        
        // Calculate progress
        $totalLectures = $course->lectures()->count();
        $completedLectures = $this->getCompletedLecturesCount($enrollment);
        $progress = $totalLectures > 0 ? ($completedLectures / $totalLectures) * 100 : 0;
        
        $enrollment->update([
            'progress' => $progress,
            'completed_at' => $progress >= 100 ? now() : null,
        ]);
        
        return response()->json([
            'success' => true,
            'progress' => $progress,
            'completed_lectures' => $completedLectures,
            'total_lectures' => $totalLectures,
        ]);
    }
    

    
    /**
     * Mark lecture as completed (private helper)
     */
    private function markLectureAsCompletedInternal($enrollment, $lecture)
    {
        $completedLectures = json_decode($enrollment->completed_lectures ?? '[]', true);
        
        if (!in_array($lecture->id, $completedLectures)) {
            $completedLectures[] = $lecture->id;
            $enrollment->update(['completed_lectures' => json_encode($completedLectures)]);
        }
    }
    
    /**
     * Get count of completed lectures (private helper)
     */
    private function getCompletedLecturesCount($enrollment)
    {
        $completedLectures = json_decode($enrollment->completed_lectures ?? '[]', true);
        return count($completedLectures);
    }

    /**
     * Bulk delete courses
     */
    public function destroyBulk(Request $request)
    {
        try {
            Course::whereIn('id', $request->id)->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.courses')]));
        } catch (\Exception $e) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.courses')]) . $e->getMessage());
        }
    }

    /**
     * Duplicate a course
     */
    public function duplicate(Course $course)
    {
        try {
            $newCourse = $this->courseService->duplicate($course);
            
            return Redirect::route('courses.show', $newCourse->id)
                ->with('success', 'Course duplicated successfully!');
        } catch (\Exception $e) {
            return Redirect::back()
                ->with('error', 'Failed to duplicate course.');
        }
    }

    /**
     * Toggle course status
     */
    public function toggleStatus(Course $course)
    {
        $newStatus = $course->status === 'active' ? 'inactive' : 'active';
        $course->update(['status' => $newStatus]);

        $message = $newStatus === 'active' ? 'Course activated successfully!' : 'Course deactivated successfully!';
        return Redirect::back()->with('success', $message);
    }
}
