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
use Illuminate\{Support\Str, Http\Request, Support\Facades\Log};
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

    public function index()
    {
        // جلب جميع الدورات مع تفاصيلها
        $courses = $this->courseRepository->getActiveCourses();

        // إعداد البيانات للعرض
        $data = [
            'courses' => CourseResource::collection($courses),
            'filters' => request()->only(['search']),
            'title' => __('courses.title'),
            'breadcrumbs' => [
                ['label' => __('dashboard'), 'href' => route('dashboard')],
                ['label' => __('courses.list'), 'href' => route('courses.index')]
            ],
            'statistics' => [
                'total_courses' => $courses->count(),
                'active_courses' => $courses->where('status', 'active')->count(),
                'draft_courses' => $courses->where('status', 'draft')->count()
            ]
        ];

        return inertia('Course/Index', $data);
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

    private function calculateCompletionRate($course)
    {
        $totalEnrollments = $course->enrollments()->count();
        if (!$totalEnrollments) return 0;

        $completedEnrollments = $course->enrollments()
            ->where('completion_status', 'completed')
            ->count();

        return round(($completedEnrollments / $totalEnrollments) * 100);
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



}




