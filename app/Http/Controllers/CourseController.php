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
use Illuminate\{Support\Str, Http\Request, Support\Facades\Log, Support\Facades\Storage, Support\Facades\DB};
use App\Models\{User, Course, Lecture, Section, Enrollment, QuizAttempt };
use App\{Services\CourseService, Http\Resources\CourseResource, Repositories\CourseRepository};

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

    }


    public function details($id)
    {
        $course = Course::with([
            'sections.lectures',
            'instructor:id,name',
            'category:id,name'
        ])->findOrFail($id);

        // تأكد من تحميل العلاقات
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
            $course->user_id = auth()->id(); // تعيين المستخدم الحالي كمدرس للكورس

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

            $user = auth()->user();
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
            \Log::error('Course display error: ' . $e->getMessage());
            dd($e->getMessage());
            return redirect()->route('courses.index')
                ->with('error', 'An error occurred while displaying the course. Please try again.');
        }
    }

    public function enroll($courseId)
    {
        $user = auth()->user();
        $course = Course::findOrFail($courseId);

        $enrollment = Enrollment::firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $courseId
            ],
            [
                'enrollment_status' => 'active',
                'enrollment_date' => Carbon::now()
            ]
        );

        // Redirect to course details page
        return Redirect::route('courses.details', [
            'course' => $course->id,
            'slug' => $course->slug
        ])->with('success', 'Successfully enrolled in the course.');
    }

    public function update(UserUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->update([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => $request->password ? Hash::make($request->password) : $user->password,
            ]);
            $user->syncRoles($request->role);
            DB::commit();
            return back()->with('success', __('app.label.updated_successfully', ['name' => $user->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $user->name]) . $th->getMessage());
        }
  }

    /**
     * Display a listing of the courses.
     */
    public function indexcourse(Request $request)
    {
        $query = Course::with(['category', 'lessons'])
            ->withCount(['lessons', 'enrollments'])
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
        $validated['instructor_id'] = auth()->id();

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
    public function editcourse(Course $course)
    {
        $categories = Category::all();

        return Inertia::render('Courses/Edit', [
            'course' => $course,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified course in storage.
     */
    public function updatecourse(Request $request, Course $course)
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
            // Delete old thumbnail
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course->update($validated);

        return Redirect::route('courses.show', $course->id)
            ->with('success', 'تم تحديث الكورس بنجاح!');
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

        $quizHistory = QuizAttempt::where('user_id', auth()->id())
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
            'course' => $data['course'],
            'sections' => $data['course']->sections,
            'lectures' => $data['course']->sections->flatMap->lectures,
            'firstLecture' => $data['lectureData'],
            'completionPercentage' => $data['completionPercentage'],
            'breadcrumbs' => [
                ['label' => $data['lectureData']['title'], 'href' => route('course.player', ['courseId' => $courseId, 'courseSlug' => $courseSlug])]
            ],
        ]);
    }

    public function watchLecture($courseId, $courseSlug, $lectureID)
    {
        $data = $this->getCourseWithLectureData($courseId, $courseSlug, $lectureID);

        return inertia('Course/CoursePlayer', [
            'course' => $data['course'],
            'sections' => $data['course']->sections,
            'lectures' => $data['course']->sections->flatMap->lectures,
            'firstLecture' => $data['lectureData'],
            'completionPercentage' => $data['completionPercentage'],
            'breadcrumbs' => [
                ['label' => 'Course ', 'href' => route('courses.index')],
                ['label' => $data['lectureData']['title'], 'href' => route('course.watchLecture', ['courseId' => $courseId, 'courseSlug' => $courseSlug, 'lectureID' => $lectureID])]
            ]
        ]);
    }

    private function getCourseWithLectureData($courseId, $courseSlug, $lectureID = null)
    {
        // جلب الكورس مع الأقسام والمحاضرات
        $course = Course::where('id', $courseId)
            ->where('slug', $courseSlug)
            ->with(['sections.lectures'])
            ->firstOrFail();

        // إذا لم يتم تحديد محاضرة، جلب أول محاضرة بشكل افتراضي
        $lecture = $lectureID ? Lecture::findOrFail($lectureID) : optional($course->sections->first()->lectures->first());

        if (!$lecture || $lecture->course_id !== $course->id) {
            abort(404, 'Lecture not found or does not belong to the course');
        }

        // إعداد بيانات المحاضرة
        $sections = $course->sections->sortBy('order'); // ترتيب الأقسام حسب الترتيب
        $currentSection = $sections->where('id', $lecture->section_id)->first();
        $lecturesInCurrentSection = $currentSection->lectures->sortBy('order');

        $previousLecture = $lecturesInCurrentSection->where('order', '<', $lecture->order)->last();
        $nextLecture = $lecturesInCurrentSection->where('order', '>', $lecture->order)->first();

        // إذا لم تكن هناك محاضرة سابقة في نفس القسم، الانتقال إلى آخر محاضرة في القسم السابق
        if (!$previousLecture) {
            $previousSection = $sections->where('order', '<', $currentSection->order)->last();
            $previousLecture = $previousSection ? $previousSection->lectures->sortByDesc('order')->first() : null;
        }

        // إذا لم تكن هناك محاضرة تالية في نفس القسم، الانتقال إلى أول محاضرة في القسم التالي
        if (!$nextLecture) {
            $nextSection = $sections->where('order', '>', $currentSection->order)->first();
            $nextLecture = $nextSection ? $nextSection->lectures->sortBy('order')->first() : null;
        }

        // إعداد بيانات المحاضرة مع خاصية 'completed'
        $user = auth()->user();
        $completedLectureIds = $user->lectureProgress()
            ->where('completed', true)
            ->pluck('lecture_id')
            ->toArray();

        $lectureData = [
            'id' => $lecture->id,
            'title' => $lecture->title,
            'video_url' => $lecture->video_url,
            'completed' => in_array($lecture->id, $completedLectureIds), // تعيين حالة الإنجاز
            'previous_lecture_id' => $previousLecture ? $previousLecture->id : null,
            'next_lecture_id' => $nextLecture ? $nextLecture->id : null,
        ];

        // إعداد خاصية 'completed' لكل محاضرة في الأقسام
        foreach ($sections as $section) {
            foreach ($section->lectures as $lecture) {
                $lecture->completed = in_array($lecture->id, $completedLectureIds);
            }
        }

        // حساب النسبة المئوية للإنجاز
        $totalLecturesCount = $course->sections->flatMap->lectures->count();
        $completionPercentage = $totalLecturesCount > 0 ? (count($completedLectureIds) / $totalLecturesCount) * 100 : 0;

        return compact('course', 'lectureData', 'completionPercentage');
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
                'course' => $course->load('lessons', 'enrollments.user'),
                'stats' => [
                    'total_students' => $course->enrollments()->count(),
                    'total_lessons' => $course->lessons()->count(),
                    'total_duration' => $course->lessons()->sum('duration'),
                 //   'completion_rate' => $this->calculateCompletionRate($course),
                ]
            ];

            return response()->json($data);
        }

        /**
         * Reorder lessons within a course.
         */
        public function reorderLessons(Request $request, Course $course)
        {
            $validated = $request->validate([
                'lessons' => 'required|array',
                'lessons.*.id' => 'required|exists:lessons,id',
                'lessons.*.order' => 'required|integer|min:1',
            ]);

            try {
                DB::beginTransaction();

                foreach ($validated['lessons'] as $lessonData) {
                    Lesson::where('id', $lessonData['id'])
                        ->where('course_id', $course->id)
                        ->update(['order' => $lessonData['order']]);
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
                ->whereHas('progress', function ($query) use ($course) {
                    $query->where('is_completed', true)
                          ->whereHas('lesson', function ($q) use ($course) {
                              $q->where('course_id', $course->id);
                          });
                })
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
            return $course->lessons()
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
                    $totalLessons = $enrollment->course->lessons()->count();
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
    }






