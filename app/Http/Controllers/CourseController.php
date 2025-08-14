<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Category;
use App\Helpers\BreadcrumbHelper;
use App\Models\LectureUserProgress;
use App\Services\BreadcrumbService;
use App\Helpers\CourseProgressHelper;
use App\Services\CourseHelperService;
use App\Services\LectureCountService;
use App\Services\LectureProgressService;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\CourseStoreRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\{Support\Str, Http\Request, Support\Facades\Auth, Support\Facades\Storage, Support\Facades\Hash};
use App\Models\{User, Course, Lecture, Section, Enrollment, QuizAttempt, Wishlist };
use App\{Services\CourseService, Http\Resources\CourseResource, Repositories\CourseRepository};
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseController extends Controller
{
    protected $courseService;
    protected $courseRepository;
    protected $lectureProgressService;
    protected $breadcrumbService;
    protected $courseHelper;
    protected $lectureCountService;
    protected $courseProgressHelper;

    public function __construct(
        CourseService $courseService,
        CourseRepository $courseRepository,
        BreadcrumbService $breadcrumbService,
        CourseHelperService $courseHelper,
        LectureCountService $lectureCountService,
        CourseProgressHelper $courseProgressHelper
    ) {
        $this->courseService = $courseService;
        $this->courseRepository = $courseRepository;
        $this->courseHelper = $courseHelper;
        $this->lectureCountService = $lectureCountService;
        $this->courseProgressHelper = $courseProgressHelper;
        $this->middleware('permission:create courses', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit courses', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete courses', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        // Get search and filter parameters
        $search = $request->get('search');
        $field = $request->get('field', 'created_at');
        $order = $request->get('order', 'desc');
        $perPage = $request->get('perPage', 10);

        // Validate sort field to prevent SQL injection
        $allowedFields = ['title', 'status', 'created_at', 'updated_at'];
        if (!in_array($field, $allowedFields)) {
            $field = 'created_at';
        }

        // Validate sort order and per page
        $order = in_array($order, ['asc', 'desc']) ? $order : 'desc';
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        // Build optimized query
        $coursesQuery = Course::query()
            ->with(['category:id,name'])
            ->select(['id', 'title', 'status', 'category_id', 'created_at', 'updated_at']);

        // Apply search filter
        if ($search) {
            $coursesQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhereHas('category', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
            });
        }

        // Get paginated results with transformation
        $courses = $coursesQuery->orderBy($field, $order)
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'status' => ucfirst($course->status),
                    'category' => $course->category ? [
                        'id' => $course->category->id,
                        'name' => $course->category->name,
                    ] : null,
                    'created_at' => $course->created_at->format('Y-m-d H:i'),
                    'updated_at' => $course->updated_at->format('Y-m-d H:i'),
                ];
            });

        $categories = Category::select(['id', 'name'])->orderBy('name')->get();

        return Inertia::render('Dashboard/Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => compact('search', 'field', 'order'),
        ]);
    }


    public function details($id)
    {
        $course = Course::with([
            'sections.lectures',
            'instructor:id,name',
            'category:id,name'
        ])->findOrFail($id);

        // Ensure relations are loaded
        $course->load(['category', 'instructor']);
        return inertia('Dashboard/Course/DetailsCourse',[
            'course' => new CourseResource($course),
            'lessons' => $course->sections->flatMap->lectures,
            'title' => __('courses.title'),
            'breadcrumbs' => [
                ['label' => __('dashboard'), 'href' => route('dashboard')],
            ],
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        return Inertia::render('Dashboard/Course/CreateCourse', [
            'categories' => $categories
        ]);
    }

    public function store(CourseStoreRequest $request)
    {
        try {
            // إنشاء الكورس
            $course = new Course($request->except('image', 'sections'));
            $course->slug = Str::slug($request->title);
            $course->user_id = Auth::id(); // تعيين المستخدم الحالي كمدرس للكورس

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('course_images', 'public');
                $course->image = $imagePath;
            }

            $course->save();

            // إنشاء الأقسام إذا وجدت
            if ($request->has('sections')) {
                $sections = json_decode($request->sections, true);

                foreach ($sections as $sectionData) {
                    $section = new Section([
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'],
                        'order' => $sectionData['order'],
                        'course_id' => $course->id
                    ]);

                    $section->save();
                }
            }

            Log::info(__('courses.log.created_success'), ['course_id' => $course->id]);

            return redirect()->route('courses.index')
                ->with('success', __('courses.created_success'));
        } catch (\Exception $e) {
            Log::error(__('courses.log.creation_error'), ['error' => $e->getMessage()]);
            return back()->with('error', __('courses.creation_error') . $e->getMessage());
        }
    }

    public function createLecture(Course $course)
    {
        $sections = Section::where('course_id', $course->id)->get();

        $url = route('course.create.lecture', ['courseId' => $course->id]);
        return Inertia::render('Course/CreateLecture', [
            'title'         => __('app.label.courses'),
            'course'        => $course,
            'sections'      => $sections,
            'breadcrumbs'   => [['label' => __('app.label.learn'), 'href' => $url]],
        ]);
    }

    public function storeLecture(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string',
            'section_id' => 'required|exists:sections,id',
            'video_url' => 'required|url',
            'duration' => 'required|numeric',
        ]);

        $lecture = new Lecture([
            'title' => $request->input('title'),
            'section_id' => $request->input('section_id'),
            'course_id' => $course->id,
            'video_url' => $request->input('video_url'),
            'duration' => $request->input('duration'),
        ]);

        $lecture->save();

        return redirect()->route('course.details', ['id' => $course->id, 'courseSlug' => $course->slug])
            ->with('success', __('lectures.created_success'));
    }

    public function storeSection(Request $request, Course $course)
    {
        $request->validate([
            'title'       => 'required|string',
            'order'       => 'required',
            'description' => 'required',
        ]);

        $section = new Section([
            'title' => $request->input('title'),
            'order' => $request->input('order'),
            'description' => $request->input('description'),
            'course_id' => $course->id,
        ]);

        $section->save();

        return back()->with('success', __('app.label.created_successfully', ['name' => $course->title]));
    }

    public function show($id, $courseSlug)
    {
        try {
            $course = Course::with([
                'sections.lectures',
                'instructor:id,name',
                'category:id,name'
            ])->findOrFail($id);

            $user = Auth::user();
            $sections = $course->sections;

            $statistics = [
                'lecturesCount' => $sections->sum(fn($section) => $section->lectures->count()),
                'totalDuration' => $sections->flatMap(fn($section) => $section->lectures)->sum('duration'),
                'enrollmentsCount' => Enrollment::where('course_id', $id)->count(),
                'completionRate' => $this->courseHelper->calculateCompletionRate($course),
            ];

            $durationFormatted = $this->courseHelper->formatDuration($statistics['totalDuration']);

            // Check user enrollment status and progress
            $enrollmentStatus = $this->getEnrollmentStatus($user, $id);

            // Return Inertia view with all necessary data
            return inertia('Index/Course/Show', [
                'course' => new CourseResource($course),
                'statistics' => array_merge($statistics, ['durationFormatted' => $durationFormatted]),
                'enrollmentStatus' => $enrollmentStatus,
                'progress' => $this->courseProgressHelper->getUserProgress($user, $course),
                //'breadcrumbs' => $this->generateBreadcrumbs($course, $courseSlug),
                'meta' => [
                    'title' => $course->title,
                    'description' => $course->description,
                    //'shareUrl' => route('course.details', ['id' => $id, 'courseSlug' => $courseSlug])
                ]
            ]);
        } catch (\Exception $e) {
            // Log error and redirect with error message
            Log::error('Course display error: ' . $e->getMessage());
            dd($e->getMessage());
            return redirect()->route('courses.index')
                ->with('error', 'An error occurred while displaying the course. Please try again.');
        }
    }

    public function enroll($courseId)
    {
        try {
            $user = Auth::user();
            $course = Course::findOrFail($courseId);

            // Check if course is available for enrollment
            if (!$course->isPublished()) {
                return Redirect::back()
                    ->with('error', 'This course is not available for enrollment currently.');
            }

            // Check existing enrollment
            $existingEnrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->first();

            if ($existingEnrollment) {
                return Redirect::route('courses.learn', $course->id)
                    ->with('info', 'You are already enrolled in this course.');
            }

            // Handle enrollment based on course type
            if ($course->isFree()) {
                $this->createEnrollment($user->id, $courseId);
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
     * Create enrollment record
     */
    private function createEnrollment($userId, $courseId)
    {
        return Enrollment::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'enrollment_status' => 'active',
            'enrollment_date' => Carbon::now(),
            'progress_percentage' => 0
        ]);
    }



    /**
     * Display a listing of the courses.
     */
    public function indexcourse(Request $request)
    {
        $query = Course::with(['category', 'lectures'])
            ->withCount(['lectures', 'enrollments'])
            ->latest();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        $courses = $query->paginate(12);
        $categories = Category::all();

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'category']),
        ]);
    }

    /**
     * Display the explore courses page for users.
     */
    public function explore(Request $request)
    {
        $query = Course::select(['id', 'title', 'slug', 'description', 'image', 'duration', 'price', 'status', 'category_id', 'user_id'])
            ->with(['category', 'instructor'])
            ->withCount(['lectures as lessons_count', 'enrollments'])
            ->where('status', 1); // Use numeric status: 1 for active, 0 for inactive

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by price
        if ($request->filled('price')) {
            if ($request->price === 'free') {
                $query->where('price', 0);
            } elseif ($request->price === 'paid') {
                $query->where('price', '>', 0);
            }
        }

        // Sort functionality
        switch ($request->get('sort', 'latest')) {
            case 'popular':
                $query->orderBy('enrollments_count', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
        }

        // Add user-specific data for authenticated users
        $user = Auth::user();
        if ($user) {
            $query->addSelect([
                'user_enrolled' => DB::raw('EXISTS(SELECT 1 FROM enrollments WHERE enrollments.course_id = courses.id AND enrollments.user_id = ' . $user->id . ') as user_enrolled'),
                'is_wishlisted' => DB::raw('EXISTS(SELECT 1 FROM wishlists WHERE wishlists.course_id = courses.id AND wishlists.user_id = ' . $user->id . ') as is_wishlisted')
            ]);
        } else {
            $query->addSelect([
                'user_enrolled' => DB::raw('0 as user_enrolled'),
                'is_wishlisted' => DB::raw('0 as is_wishlisted')
            ]);
        }

        $courses = $query->paginate(12)->appends(request()->query());
        $categories = Category::all(['id', 'name']);

        // Platform statistics
        $stats = [
            'totalCourses' => Course::where('status', 1)->count(), // Use numeric status
            'totalStudents' => User::whereHas('enrollments')->count(),
            'totalInstructors' => User::whereHas('courses')->count(),
        ];

        return Inertia::render('Courses/Explore', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'price', 'sort']),
            'stats' => $stats,
        ]);
    }

    /**
     * Toggle course wishlist status.
     */
    public function toggleWishlist(Request $request, $courseId)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to add courses to wishlist');
        }

        $user = Auth::user();
        $course = Course::findOrFail($courseId);

        $existingWishlist = Wishlist::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();

        if ($existingWishlist) {
            // Remove from wishlist
            $existingWishlist->delete();
            $message = 'Course removed from wishlist';
        } else {
            // Add to wishlist
            Wishlist::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
            ]);
            $message = 'Course added to wishlist';
        }

        return back()->with('success', $message);
    }

    /**
     * Display user's wishlist.
     */
    public function wishlist()
    {
        /** @var User $user */
        $user = Auth::user();

        $wishlistCourses = $user->wishlists()
            ->with(['course.category', 'course.instructor'])
            ->get()
            ->map(function ($wishlist) {
                $course = $wishlist->course;
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'description' => $course->description,
                    'image' => $course->image,
                    'price' => $course->price,
                    'rating' => $course->rating,
                    'level' => $course->level,
                    'category' => $course->category->name ?? 'Uncategorized',
                    'instructor' => $course->instructor->name,
                    'duration' => $course->duration,
                    'is_free' => $course->price == 0,
                ];
            });

        return Inertia::render('Courses/Wishlist', [
            'courses' => $wishlistCourses,
        ]);
    }

    /**
     * Store a newly created course in storage.
     */
    public function storecourse(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,active,inactive',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'duration' => 'nullable|integer|min:1',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        // Add creator
        $validated['instructor_id'] = Auth::id();

        $course = Course::create($validated);

        return Redirect::route('courses.show', $course->id)
            ->with('success', 'تم إنشاء الكورس بنجاح!');
    }

    /**
     * Display the specified course.
     */
    public function showcourse(Course $course)
    {
        $course->load([
            'category',
            'instructor',
            'lessons' => function ($query) {
                $query->orderBy('order')->orderBy('created_at');
            }
        ]);

        // Get course statistics
        $stats = [
            'enrolled_students' => $course->enrollments()->count(),
            'total_views' => $course->lessons()->sum('views'),
            'completion_rate' => $this->calculateCompletionRate($course),
            'total_duration' => $course->lessons()->sum('duration'),
            'free_lessons' => $course->lessons()->where('is_free', true)->count(),
            'paid_lessons' => $course->lessons()->where('is_free', false)->count(),
        ];

        // Get lessons with pagination
        $lessons = $course->lessons()
            ->orderBy('order')
            ->orderBy('created_at')
            ->paginate(20);

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'lessons' => $lessons,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified course.
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
     * Display course enrollments management page.
     */
    public function enrollments(Course $course)
    {
        $this->authorize('update', $course);

        // Calculate total lessons first
        $totalLessons = $course->sections()->withCount('lectures')->get()->sum('lectures_count');

        // Get enrollments with user data and pagination
        $enrollments = $course->enrollments()
            ->with(['user:id,name,email,created_at'])
            ->select('*')
            ->addSelect([
                DB::raw("ROUND((progress_percentage / 100) * {$totalLessons}) as completed_lessons")
            ])
            ->latest()
            ->paginate(20);

        // Calculate course statistics
        $stats = [
            'total_enrollments' => $course->enrollments()->count(),
            'active_students' => $course->enrollments()
                ->where('updated_at', '>=', now()->subDays(30))
                ->where('enrollment_status', 'confirmed')
                ->count(),
            'completion_rate' => $this->calculateCompletionRate($course),
            'total_lessons' => $totalLessons,
        ];

        return Inertia::render('Dashboard/Courses/Enrollments', [
            'course' => $course,
            'enrollments' => $enrollments,
            'stats' => $stats,
        ]);
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        // Debug: Log the request data
        \Illuminate\Support\Facades\Log::info('Course update request data:', $request->all());

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,active,inactive',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'duration' => 'nullable|integer|min:1',
            'language' => 'nullable|string|in:en,ar,fr,es',
        ]);

        // Ensure description is not empty or null
        if (empty($validated['description']) || is_null($validated['description'])) {
            $validated['description'] = $course->description ?? 'Default course description';
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($course->thumbnail && Storage::disk('public')->exists($course->thumbnail)) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        } else {
            // Keep the old thumbnail if a new one is not uploaded
            $validated['thumbnail'] = $course->thumbnail;
        }

        $course->update($validated);

        return Redirect::route('courses.show', $course->id)
            ->with('success', 'Course updated successfully!');
    }

    public function destroy(Course $course)
    {
        try {
            $course->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => $course->title]));
        } catch (\Exception $e) {
            return back()->with('error', __('app.label.deleted_error', ['name' => $course->title]) . $e->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            Course::whereIn('id', $request->id)->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.courses')]));
        } catch (\Exception $e) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.courses')]) . $e->getMessage());
        }
    }

    public function quizzes(Course $course)
    {
        $quizzes = $course->quizzes;

        $quizHistory = QuizAttempt::where('user_id', Auth::id())
                                   ->where('course_id', $course->id)
                                   ->with('quiz:id,title')
                                   ->orderBy('created_at', 'desc')
                                   ->get()
                                   ->map(function ($attempt) {
                                       return [
                                           'id' => $attempt->id,
                                           'quiz_title' => $attempt->quiz->title,
                                           'date' => $attempt->created_at,
                                           'score' => $attempt->score,
                                           'passed' => $attempt->passed,
                                       ];
                                   });

        return inertia('Course/Quizzes', [
            'quizzes' => $quizzes,
            'quizHistory' => $quizHistory,
            'breadcrumbs' => [
                ['label' => __('courses.list'), 'href' => route('courses.index')],
                ['label' => __('quizzes.title'), 'href' => route('course.quizzes', ['course' => $course->id])]
            ]
        ]);
    }




    private function getEnrollmentStatus($user, $courseId)
    {
        if (!$user) return ['enrolled' => false, 'status' => null];

        $enrollment = $user->enrollments()
            ->where('course_id', $courseId)
            ->first();

        return [
            'enrolled' => (bool) $enrollment,
            'status' => $enrollment?->status,
            'enrollmentDate' => $enrollment?->created_at
        ];
    }

    private function checkCertificateEligibility(User $user, Course $course)
    {
        if (!$user) return false;

        $totalLectures = $course->sections()
            ->withCount('lectures')
            ->get()
            ->sum('lectures_count');

        $completedLectures = $this->lectureCountService->getCompletedLectureCount($course, $user);

        // User is eligible if they completed at least 80% of the lectures
        return ($totalLectures > 0) && (($completedLectures / $totalLectures) >= 0.8);
    }

    private function getUserProgress($user, $course)
    {
        if (!$user) return null;

        return [
            'completedLectures' => $this->lectureCountService->getCompletedLectureCount($course, $user),
            'lastAccessedLecture' => $this->getLastAccessedLecture($user, $course),
            'certificateEligible' => $this->checkCertificateEligibility($user, $course)
        ];
    }



    private function getLastAccessedLecture(User $user, Course $course)
    {
        return LectureUserProgress::where('user_id', $user->id)
            ->whereHas('lecture', function ($query) use ($course) {
                $query->whereHas('section', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                });
            })
            ->with('lecture')
            ->latest()
            ->first()?->lecture;
    }

    public function coursePlayer($courseId, $courseSlug)
    {
        $data = $this->getCourseWithLectureData($courseId, $courseSlug);

        return inertia('Course/CoursePlayer', [
            'title' => $data['course']->title,
            ...$data,
            'breadcrumbs' => $this->buildBreadcrumbs('player', $data['lectureData']['title'], compact('courseId', 'courseSlug'))
        ]);
    }

    public function watchLecture($courseId, $courseSlug, $lectureID)
    {
        $data = $this->getCourseWithLectureData($courseId, $courseSlug, $lectureID);

        return inertia('Course/CoursePlayer', [
            ...$data,
            'currentLectureId' => (int) $lectureID,
            'breadcrumbs' => $this->buildBreadcrumbs('watch', $data['lectureData']['title'], compact('courseId', 'courseSlug', 'lectureID'))
        ]);
    }

    /**
     * Handle course learning page - displays course content for enrolled users
     * Route: /courses/{courseId}/learn/{courseSlug}
     */
    public function learn($courseId, $courseSlug)
    {
        try {
            // Validate user enrollment before allowing access
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Please login to access course content.');
            }

            // Check if user is enrolled in the course
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->whereIn('enrollment_status', ['active', 'confirmed'])
                ->first();

            if (!$enrollment) {
                return redirect()->route('courses.show', ['id' => $courseId, 'courseSlug' => $courseSlug])
                    ->with('error', 'You must be enrolled to access this course.');
            }

            // Get course data using existing method
            $data = $this->getCourseWithLectureData($courseId, $courseSlug);

            return inertia('Course/Learn', [
                'title' => $data['course']->title . ' - Learning',
                'course' => $data['course'],
                'sections' => $data['course']->sections,
                'lectures' => $data['course']->sections->flatMap->lectures,
                'user' => $user,
                'enrolled' => true,
                'firstLecture' => $data['lectureData'],
                'completionPercentage' => $data['completionPercentage'],
                'enrollment' => [
                    'status' => $enrollment->enrollment_status,
                    'progress' => $enrollment->progress_percentage ?? 0,
                    'enrolled_date' => $enrollment->enrollment_date
                ],
                'breadcrumbs' => [
                    ['label' => 'Courses', 'href' => route('courses.explore')],
                    ['label' => $data['course']->title, 'href' => route('courses.learn', ['courseId' => $courseId, 'courseSlug' => $courseSlug])]
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Course learning access error: ' . $e->getMessage(), [
                'courseId' => $courseId,
                'courseSlug' => $courseSlug,
                'userId' => Auth::id()
            ]);

            return redirect()->route('courses.explore')
                ->with('error', 'Unable to access course content. Please try again.');
        }
    }

    private function getCourseWithLectureData($courseId, $courseSlug, $lectureID = null)
    {
        // Optimized single query with proper eager loading
        $course = Course::where('id', $courseId)
            ->where('slug', $courseSlug)
            ->with([
                'sections' => fn($q) => $q->orderBy('order'),
                'sections.lectures' => fn($q) => $q->orderBy('order')->select(['id', 'title', 'video_url', 'section_id', 'course_id', 'order'])
            ])
            ->select(['id', 'title', 'slug', 'description', 'image'])
            ->firstOrFail();

        $lecture = $this->findLecture($course, $lectureID);
        $this->validateLectureOwnership($lecture, $course);

        $completedLectureIds = $this->getCompletedLectureIds();
        $navigation = $this->buildLectureNavigation($course->sections, $lecture);

        // Mark completion status efficiently
        $this->enrichLecturesWithCompletion($course->sections, $completedLectureIds);

        return [
            'course' => $course,
            'sections' => $course->sections,
            'lectures' => $course->sections->flatMap->lectures,
            'lectureData' => $this->buildLectureData($lecture, $navigation, $completedLectureIds),
            'completionPercentage' => $this->calculateCompletionPercentage($course, $completedLectureIds)
        ];
    }

    /**
     * Find lecture by ID or return first available lecture
     */
    private function findLecture($course, $lectureID)
    {
        if ($lectureID) {
            return Lecture::findOrFail($lectureID);
        }

        $firstLecture = $course->sections->first()?->lectures->first();

        if (!$firstLecture) {
            abort(404, 'No lectures found in this course');
        }

        return $firstLecture;
    }

    /**
     * Validate lecture belongs to course
     */
    private function validateLectureOwnership($lecture, $course)
    {
        if (!$lecture || $lecture->course_id !== $course->id) {
            abort(404, 'Lecture not found or does not belong to the course');
        }
    }

    /**
     * Build navigation for lectures with cross-section support
     */
    private function buildLectureNavigation($sections, $lecture)
    {
        $allLectures = $sections->flatMap->lectures->sortBy(['section.order', 'order']);
        $currentIndex = $allLectures->search(fn($l) => $l->id === $lecture->id);

        return [
            'previous' => $currentIndex > 0 ? $allLectures->values()[$currentIndex - 1] : null,
            'next' => $currentIndex < $allLectures->count() - 1 ? $allLectures->values()[$currentIndex + 1] : null
        ];
    }

    /**
     * Get completed lecture IDs for current user
     */
    private function getCompletedLectureIds()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->lectureProgress()
            ->where('completed', true)
            ->pluck('lecture_id')
            ->toArray() ?? [];
    }

    /**
     * Efficiently mark lectures as completed
     */
    private function enrichLecturesWithCompletion($sections, $completedLectureIds)
    {
        $completedSet = array_flip($completedLectureIds); // O(1) lookup

        $sections->each(fn($section) =>
            $section->lectures->each(fn($lecture) =>
                $lecture->completed = isset($completedSet[$lecture->id])
            )
        );
    }

    /**
     * Calculate course completion percentage
     */
    private function calculateCompletionPercentage($course, $completedLectureIds)
    {
        $totalLectures = $course->sections->sum(fn($section) => $section->lectures->count());

        return $totalLectures > 0 ? round((count($completedLectureIds) / $totalLectures) * 100, 1) : 0;
    }

    /**
     * Build lecture data with navigation and completion status
     */
    private function buildLectureData($lecture, $navigation, $completedLectureIds)
    {
        return [
            'id' => $lecture->id,
            'title' => $lecture->title,
            'video_url' => $lecture->video_url,
            'completed' => in_array($lecture->id, $completedLectureIds),
            'previous_lecture_id' => $navigation['previous']?->id,
            'next_lecture_id' => $navigation['next']?->id,
        ];
    }

    /**
     * Unified breadcrumb builder for different contexts
     */
    private function buildBreadcrumbs($type, $title, $params = [])
    {
        $breadcrumbs = [
            ['label' => 'Courses', 'href' => route('courses.index')]
        ];

        switch ($type) {
            case 'watch':
                $breadcrumbs[] = [
                    'label' => $title,
                    'href' => route('courses.watch', $params)
                ];
                break;
            case 'player':
                $breadcrumbs[] = [
                    'label' => $title,
                    'href' => route('courses.player', $params)
                ];
                break;
            default:
                $breadcrumbs[] = ['label' => $title, 'href' => '#'];
        }

        return $breadcrumbs;
    }

    /**
     * Display user's enrolled courses with progress and suggestions
     */
    public function myCourses()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        // Get enrolled courses with progress and quizzes
        /** @var \App\Models\User $user */
        $enrolledCourses = $user->enrollments()
            ->with(['course.category', 'course.instructor', 'course.sections.lectures', 'course.quizzes'])
            ->get()
            ->map(fn($enrollment) => $this->formatCourseData($enrollment, $user));

        // Quiz data loaded successfully

        // Get course suggestions
        $suggestions = $this->generateCourseSuggestions($user, 6);

        return Inertia::render('Courses/MyCourses', [
            'enrolledCourses' => $enrolledCourses,
            'suggestions' => $suggestions,
            'stats' => [
                'total_enrolled' => $enrolledCourses->count(),
                'completed' => $enrolledCourses->where('is_completed', true)->count(),
                'in_progress' => $enrolledCourses->where('progress', '>', 0)->where('is_completed', false)->count(),
                'not_started' => $enrolledCourses->where('progress', 0)->count()
            ]
        ]);
    }

    /**
     * Get course suggestions for the user
     */
    public function courseSuggestions(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $filters = $request->only(['category', 'level', 'price', 'sort', 'offset']);
        $limit = $request->get('limit', 12);

        $suggestions = $this->generateCourseSuggestions($user, $limit, $filters);

        // Get all categories for filter dropdown
        $categories = Course::with('category')
            ->whereHas('category')
            ->get()
            ->pluck('category.name')
            ->unique()
            ->filter()
            ->values();

        if ($request->expectsJson()) {
            return response()->json([
                'courses' => $suggestions,
                'hasMore' => $suggestions->count() === $limit,
            ]);
        }

        return Inertia::render('Courses/Suggestions', [
            'courses' => $suggestions,
            'categories' => $categories,
            'totalCourses' => Course::where('status', true)->count(),
            'hasMore' => $suggestions->count() === $limit,
        ]);
    }

    /**
     * Generate course suggestions based on user's enrolled courses
     */
    private function generateCourseSuggestions($user, $limit = 6, $filters = [])
    {
        // Get user's enrolled course categories
        $enrolledCategoryIds = $user->enrollments()
            ->with('course.category')
            ->get()
            ->pluck('course.category.id')
            ->filter()
            ->unique()
            ->values();

        // Get enrolled course IDs to exclude
        $enrolledCourseIds = $user->enrollments()->pluck('course_id');

        // Build suggestion query
        $suggestionsQuery = Course::with(['category', 'instructor'])
            ->where('status', true)
            ->whereNotIn('id', $enrolledCourseIds);

        // Apply filters
        if (!empty($filters['category'])) {
            $suggestionsQuery->whereHas('category', function ($query) use ($filters) {
                $query->where('name', $filters['category']);
            });
        } elseif ($enrolledCategoryIds->isNotEmpty()) {
            // Prioritize courses from same categories
            $suggestionsQuery->orderByRaw(
                'CASE WHEN category_id IN (' . $enrolledCategoryIds->implode(',') . ') THEN 0 ELSE 1 END'
            );
        }

        if (!empty($filters['level'])) {
            $suggestionsQuery->where('level', $filters['level']);
        }

        if (!empty($filters['price'])) {
            if ($filters['price'] === 'free') {
                $suggestionsQuery->where('price', 0);
            } elseif ($filters['price'] === 'paid') {
                $suggestionsQuery->where('price', '>', 0);
            }
        }

        // Apply sorting
        $sortBy = $filters['sort'] ?? 'relevance';
        switch ($sortBy) {
            case 'rating':
                $suggestionsQuery->orderBy('rating', 'desc');
                break;
            case 'newest':
                $suggestionsQuery->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $suggestionsQuery->withCount('enrollments')->orderBy('enrollments_count', 'desc');
                break;
            default:
                $suggestionsQuery->orderBy('created_at', 'desc');
        }

        if (isset($filters['offset'])) {
            $suggestionsQuery->skip($filters['offset']);
        }

        return $suggestionsQuery
            ->limit($limit)
            ->get()
            ->map(function ($course) use ($user) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'description' => Str::limit($course->description, 120),
                    'image' => $course->image,
                    'category' => $course->category?->name,
                    'instructor' => $course->instructor?->name,
                    'price' => $course->price,
                    'is_free' => $course->isFree(),
                    'duration' => $course->formattedDuration(),
                    'level' => $course->level,
                    'rating' => $course->rating ?? 0,
                    'students_count' => $course->enrollments_count ?? 0,
                    'is_enrolled' => $user->enrollments()->where('course_id', $course->id)->exists(),
                ];
            });
    }

    /**
     * Enroll user in a course via API
     */
    public function enrollApi(Request $request, $courseId)
    {
        $user = Auth::user();
        $course = Course::findOrFail($courseId);

        // Check if already enrolled
        /** @var User $user */
        if ($user->enrollments()->where('course_id', $courseId)->exists()) {
            return response()->json(['message' => 'Already enrolled in this course'], 400);
        }

        // Check if course is free
        if (!$course->isFree()) {
            return response()->json(['message' => 'This course requires payment'], 400);
        }

        // Create enrollment
        $this->createEnrollment($user->id, $courseId);

        return response()->json(['message' => 'Successfully enrolled in course']);
    }


/*         /**
         * Display a listing of the courses.
         */
      //  public function indexcourse(Request $request)
      //  {
            // $query = Course::with(['category', 'lessons'])
            //     ->withCount(['lessons', 'enrollments'])
            //     ->latest();

            // // Search functionality
            // if ($request->has('search') && $request->search) {
            //     $query->where('title', 'like', '%' . $request->search . '%')
            //           ->orWhere('description', 'like', '%' . $request->search . '%');
            // }

            // // Filter by status
            // if ($request->has('status') && $request->status) {
            //     $query->where('status', $request->status);
            // }

            // // Filter by category
            // if ($request->has('category') && $request->category) {
            //     $query->where('category_id', $request->category);
            // }

            // $courses = $query->paginate(12);
            // $categories = Category::all();

            // return Inertia::render('Courses/Index', [
            //     'courses' => $courses,
            //     'categories' => $categories,
            //     'filters' => $request->only(['search', 'status', 'category']),
            // ]);
       // }


        /**
         * Show the form for creating a new course.
         */




        /**
         * Remove the specified course from storage.
         */
        // public function destroy(Course $course)
        // {
        //     try {
        //         DB::beginTransaction();

        //         // Delete associated lessons
        //         foreach ($course->lessons as $lesson) {
        //             if ($lesson->video_file) {
        //                 Storage::disk('public')->delete($lesson->video_file);
        //             }
        //             $lesson->delete();
        //         }

        //         // Delete thumbnail
        //         if ($course->thumbnail) {
        //             Storage::disk('public')->delete($course->thumbnail);
        //         }

        //         // Delete course
        //         $course->delete();

        //         DB::commit();

        //         return Redirect::route('courses.index')
        //             ->with('success', 'تم حذف الكورس بنجاح!');

        //     } catch (\Exception $e) {
        //         DB::rollBack();

        //         return Redirect::back()
        //             ->with('error', 'حدث خطأ أثناء حذف الكورس. يرجى المحاولة مرة أخرى.');
        //     }
        // }

        /**
         * Duplicate a course with all its lessons.
         */
        public function duplicate(Course $course)
        {
            try {
                DB::beginTransaction();

                // Create new course
                $newCourse = $course->replicate();
                $newCourse->title = $course->title . ' - نسخة';
                $newCourse->status = 'draft';
                $newCourse->created_at = now();
                $newCourse->updated_at = now();
                $newCourse->save();

                // Duplicate lessons
                foreach ($course->lessons as $lesson) {
                    $newLesson = $lesson->replicate();
                    $newLesson->course_id = $newCourse->id;
                    $newLesson->created_at = now();
                    $newLesson->updated_at = now();
                    $newLesson->save();
                }

                DB::commit();

                return Redirect::route('courses.show', $newCourse->id)
                    ->with('success', 'تم نسخ الكورس بنجاح!');

            } catch (\Exception $e) {
                DB::rollBack();

                return Redirect::back()
                    ->with('error', 'حدث خطأ أثناء نسخ الكورس. يرجى المحاولة مرة أخرى.');
            }
        }

        /**
         * Toggle course status between active and inactive.
         */
        public function toggleStatus(Course $course)
        {
            $newStatus = $course->status === 'active' ? 'inactive' : 'active';
            $course->update(['status' => $newStatus]);

            $message = $newStatus === 'active' ? 'تم تفعيل الكورس بنجاح!' : 'تم إلغاء تفعيل الكورس بنجاح!';

            return Redirect::back()->with('success', $message);
        }

        /**
         * Get course analytics data.
         */
        public function analytics(Course $course)
        {
            $analytics = [
                'enrollments_by_month' => $this->getEnrollmentsByMonth($course),
                'lesson_completion_rates' => $this->getLessonCompletionRates($course),
                'student_progress' => $this->getStudentProgress($course),
                'revenue_data' => $this->getRevenueData($course),
                'engagement_metrics' => $this->getEngagementMetrics($course),
            ];

            return response()->json($analytics);
        }

        /**
         * Export course data to Excel.
         */
        public function export(Course $course)
        {
            // This would integrate with Laravel Excel package
            // For now, return basic course data as JSON
            $data = [
                'course' => $course->load('lectures', 'enrollments.user'),
                'stats' => [
                    'total_students' => $course->enrollments()->count(),
                    'total_lectures' => $course->lectures()->count(),
                    'total_duration' => $course->lectures()->sum('duration'),
                 //   'completion_rate' => $this->calculateCompletionRate($course),
                ]
            ];

            return response()->json($data);
        }

        /**
         * Reorder lectures within a course.
         */
        public function reordelecture(Request $request, Course $course)
        {
            $validated = $request->validate([
                'lectures' => 'required|array',
                'lectures.*.id' => 'required|exists:lessons,id',
                'lectures.*.order' => 'required|integer|min:1',
            ]);

            try {
                DB::beginTransaction();

                foreach ($validated['lectures'] as $lectureData) {
                    Lecture::where('id', $lectureData['id'])
                        ->where('course_id', $course->id)
                        ->update(['order' => $lectureData['order']]);
                }

                DB::commit();

                return response()->json(['message' => 'تم إعادة ترتيب الدروس بنجاح!']);

            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'message' => 'حدث خطأ أثناء إعادة ترتيب الدروس.'
                ], 500);
            }
        }

        /**
         * Calculate course completion rate.
         */
        private function calculateCompletionRate(Course $course)
        {
            $totalEnrollments = $course->enrollments()->count();

            if ($totalEnrollments === 0) {
                return 0;
            }

            $completedEnrollments = $course->enrollments()
                ->where('progress_percentage', '>=', 100)
                ->count();

            return round(($completedEnrollments / $totalEnrollments) * 100, 2);
        }

        /**
         * Get enrollments by month for analytics.
         */
        private function getEnrollmentsByMonth(Course $course)
        {
            return $course->enrollments()
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        /**
         * Get lesson completion rates.
         */
        private function getLessonCompletionRates(Course $course)
        {
            return $course->lectures()
                ->withCount([
                    'progress',
                    'progress as completed_count' => function ($query) {
                        $query->where('is_completed', true);
                    }
                ])
                ->get()
                ->map(function ($lesson) {
                    $lesson->completion_rate = $lesson->progress_count > 0
                        ? round(($lesson->completed_count / $lesson->progress_count) * 100, 2)
                        : 0;
                    return $lesson;
                });
        }

        /**
         * Get student progress data.
         */
        private function getStudentProgress(Course $course)
        {
            return $course->enrollments()
                ->with(['user', 'progress.lesson'])
                ->get()
                ->map(function ($enrollment) {
                    $totalLessons = $enrollment->course->lectures()->count();
                    $completedLessons = $enrollment->progress()->where('is_completed', true)->count();

                    $enrollment->progress_percentage = $totalLessons > 0
                        ? round(($completedLessons / $totalLessons) * 100, 2)
                        : 0;

                    return $enrollment;
                });
        }

        /**
         * Get revenue data for the course.
         */
        private function getRevenueData(Course $course)
        {
            if ($course->price == 0) {
                return ['total_revenue' => 0, 'monthly_revenue' => []];
            }

            $enrollments = $course->enrollments()
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as enrollments')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $totalRevenue = $enrollments->sum('enrollments') * $course->price;

            $monthlyRevenue = $enrollments->map(function ($item) use ($course) {
                $item->revenue = $item->enrollments * $course->price;
                return $item;
            });

            return [
                'total_revenue' => $totalRevenue,
                'monthly_revenue' => $monthlyRevenue,
            ];
        }

        /**
         * Get engagement metrics.
         */
        private function getEngagementMetrics(Course $course)
        {
            return [
                'average_watch_time' => $course->lessons()->avg('average_watch_time') ?? 0,
                'total_views' => $course->lessons()->sum('views'),
                'bounce_rate' => $this->calculateBounceRate($course),
                'retention_rate' => $this->calculateRetentionRate($course),
            ];
        }

        /**
         * Calculate bounce rate (students who left after first lesson).
         */
        private function calculateBounceRate(Course $course)
        {
            $totalEnrollments = $course->enrollments()->count();

            if ($totalEnrollments === 0) {
                return 0;
            }

            $firstLessonOnly = $course->enrollments()
                ->whereHas('progress', function ($query) {
                    $query->having(DB::raw('COUNT(*)'), '=', 1);
                })
                ->count();

            return round(($firstLessonOnly / $totalEnrollments) * 100, 2);
        }

        /**
         * Calculate retention rate.
         */
        private function calculateRetentionRate(Course $course)
        {
            $totalEnrollments = $course->enrollments()->count();

            if ($totalEnrollments === 0) {
                return 0;
            }

            $activeStudents = $course->enrollments()
                ->whereHas('progress', function ($query) {
                    $query->where('updated_at', '>=', now()->subDays(30));
                })
                ->count();

            return round(($activeStudents / $totalEnrollments) * 100, 2);
        }

        /**
         * Show design settings page for the course.
         */
        public function designSettings(Course $course)
        {
            $this->authorize('update', $course);

            // Get current design settings or use defaults
            $currentSettings = $course->design_settings ?? [];

            return Inertia::render('Dashboard/Course/DesignSettings', [
                'course' => $course,
                'currentSettings' => $currentSettings,
                'breadcrumbs' => [
                    ['label' => __('dashboard'), 'href' => route('dashboard')],
                    ['label' => __('courses.title'), 'href' => route('courses.index')],
                    ['label' => $course->title, 'href' => route('courses.edit', $course)],
                    ['label' => 'Design Settings', 'href' => null]
                ]
            ]);
        }

        /**
         * Update design settings for the course.
         */
        public function updateDesignSettings(Request $request, Course $course)
        {
            $this->authorize('update', $course);

            $validated = $request->validate([
                'settings' => 'required|array',
                'settings.primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'settings.secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'settings.accent_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'settings.background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'settings.text_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'settings.theme_mode' => 'nullable|string|in:light,dark,auto',
                'settings.font_family' => 'nullable|string|in:inter,roboto,poppins',
                'settings.font_size' => 'nullable|integer|min:12|max:20',
                'settings.line_height' => 'nullable|numeric|min:1.2|max:2.0',
                'settings.heading_weight' => 'nullable|string|in:400,500,600,700',
                'settings.container_width' => 'nullable|string|in:sm,md,lg,xl',
                'settings.border_radius' => 'nullable|integer|min:0|max:20',
                'settings.card_shadow' => 'nullable|string|in:none,sm,md,lg',
                'settings.spacing_scale' => 'nullable|numeric|min:0.8|max:1.5',
                'settings.button_style' => 'nullable|string|in:rounded,square,pill',
                'settings.input_style' => 'nullable|string|in:outlined,filled,underlined',
                'settings.card_style' => 'nullable|string|in:elevated,outlined,filled'
            ]);

            try {
                $course->update([
                    'design_settings' => $validated['settings']
                ]);

                return Redirect::back()->with('success', 'Design settings updated successfully!');
            } catch (\Exception $e) {
                Log::error('Design settings update error: ' . $e->getMessage());
                return Redirect::back()->with('error', 'Failed to update design settings. Please try again.');
            }
        }

    /**
     * Format course data for My Courses page
     */
    private function formatCourseData($enrollment, $user): array
    {
        $course = $enrollment->course;
        $totalLectures = $course->sections->sum(fn($section) => $section->lectures->count());
        $completedLectures = $user->lectureProgress()
            ->whereHas('lecture', fn($query) => $query->where('course_id', $course->id))
            ->where('completed', true)
            ->count();

        $progressPercentage = $totalLectures > 0 ? ($completedLectures / $totalLectures) * 100 : 0;

        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'image' => $course->image,
            'category' => $course->category?->name,
            'instructor' => $course->instructor?->name,
            'progress' => round($progressPercentage, 1),
            'enrollment_date' => $enrollment->enrollment_date->format('M d, Y'),
            'status' => $enrollment->enrollment_status,
            'total_lectures' => $totalLectures,
            'completed_lectures' => $completedLectures,
            'is_completed' => $progressPercentage >= 100,
            'quizzes' => $this->getQuizData($course->quizzes, $user)
        ];
    }

    /**
     * Get quiz data with completion status
     */
    private function getQuizData($quizzes, $user): array
    {
        return $quizzes->map(function ($quiz) use ($user) {
            $latestAttempt = $user->quizAttempts()
                ->where('quiz_id', $quiz->id)
                ->where('completed_at', '!=', null)
                ->latest('completed_at')
                ->first();

            $isCompleted = $latestAttempt !== null;
            $lastScore = $isCompleted ? round($latestAttempt->score, 1) : null;

            return [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'is_completed' => $isCompleted,
                'questions_count' => $quiz->questions()->count(),
                'time_limit' => $quiz->time_limit ?? 30,
                'last_score' => $lastScore
            ];
        })->toArray();
    }
}
