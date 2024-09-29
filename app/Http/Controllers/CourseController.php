<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Course;
use App\Models\Lecture;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\course\CourseStoreRequest;

class CourseController extends Controller
{



    public function __construct()
    {

         $this->middleware('permission:create course', ['only' => ['create', 'store']]);
         $this->middleware('permission:read course', ['only' => ['index', 'show']]);
         $this->middleware('permission:update course', ['only' => ['edit', 'update']]);
         $this->middleware('permission:delete course', ['only' => ['destroy', 'destroyBulk']]);
    }
  /**
     * Display a listing of the resource.s
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Course::query();

        // تطبيق فلتر البحث إذا تم توفيره
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
        }

        $courses = $query->get();


        return Inertia::render('Course/Index', [
            'courses' => $courses,
            'filters' => $request->only(['search']),
            'breadcrumbs' => [['label' => __('app.label.courses'), 'href' => route('courses.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return Inertia::render('Course/Create');
    }

    public function createLecture(Course $course ,$courseId)
    {
        $sections = Section::where('course_id',$courseId)->get(); // Fetch sections for the course

        $url = route('course.create.lecture', ['courseId' => $courseId]);
        return Inertia::render('Course/CreateLecture', [
            'title'         => __('app.label.courses'),
            'course'         => $course,
            'sections'       => $sections,
            'breadcrumbs'   => [['label' => __('app.label.learn'), 'href' =>  $url ]],
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


    $lecture = new Lecture();
    $lecture->title = $request->input('title');
    $lecture->section_id = $request->input('section_id');
    $lecture->course_id = $request->input('course_id');
    $lecture->video_url = $request->input('video_url');
    $lecture->duration = $request->input('duration');
    $lecture->save();

    return redirect()->route('course.show', ['course' => $course->id]);
}



public function storeSection(Request $request, Course $course)
{
    $request->validate([
        'title'     => 'required|string',
        'order'     => 'required',
        'description'   =>'required',

    ]);

    $Section = new Section();
    $Section->title = $request->input('title');
    $Section->order = $request->input('order');
    $Section->description = $request->input('description');
    $Section->course_id =$course->id;
    $Section->save();
    return back()->with('success', __('app.label.created_successfully', ['name' => $course->name]));
}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'body' => 'required|string',
            'duration' => 'required|integer',
            'status' => 'required|in:draft,published',
            'intro_video' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $course = new Course($request->except('image'));

            $course->slug = Str::slug($request->title);
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('course_images', 'public');
                $course->image = $imagePath;
            }

            $course->save();

            Log::info('Course created successfully', ['course_id' => $course->id]);

            return redirect()->route('courses.index')->with('success', 'Course created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating course', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred while creating the course. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, $courseSlug)
    {
        $course = Course::with(['sections.lectures'])->findOrFail($id);
        $user = auth()->user();

        $sections = $course->sections->first();
        $firstLecture = $sections?->lectures->first() ?? null;
        $lecturesCount = $course->sections->sum(fn($section) => $section->lectures->count());
        $totalDuration = $course->sections->flatMap(fn($section) => $section->lectures)->sum('duration');

        $enrollmentsCount = Enrollment::where('course_id', $id)->count();

        $isEnrolled = $user ? $user->enrollments()->where('course_id', $id)->exists() : false;
        $durationFormatted = $totalDuration ? sprintf('%dh %02dmin', intdiv($totalDuration, 60), $totalDuration % 60) : '0h 00min';

        $url = route('course.details', ['id' => $id, 'courseSlug' => $courseSlug]);

        return Inertia::render('Course/Details', [
            'title'             => __('app.label.courses'),
            'course'            => $course,
            'sections'          => $sections,
            'firstLecture'      => $firstLecture,
            'lectures_count'    => $lecturesCount,
            'duration'          => $durationFormatted,
            'enrollments_count' => $enrollmentsCount,
            'isEnrolled'        => $isEnrolled,
            'isFree'            => $course->isFree(),
            'breadcrumbs'       => [['label' => __('app.label.courses'), 'href' => $url ?? '#']],
        ]);
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




    public function markLectureAsCompleted(Request $request)
{
    // التحقق من صحة المدخلات
    $validated = $request->validate([
        'lecture_id' => 'required|exists:lectures,id'
    ]);

    $user = auth()->user();
    $lectureId = $validated['lecture_id'];

    // تحديث أو إنشاء سجل التقدم الخاص بالمستخدم والمحاضرة
    $progress = \App\Models\LectureUserProgress::updateOrCreate(
        ['user_id' => $user->id, 'lecture_id' => $lectureId],
        ['completed' => true] // تعيين حالة الإنجاز إلى مكتملة
    );

    return response()->json(['message' => 'Lecture marked as completed']);
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $Course)
    {
        try {
            $Course->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => $Course->name]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => $Course->name]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $Course = Course::whereIn('id', $request->id);
            $Course->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.user')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.user')]) . $th->getMessage());
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

        return Inertia::render('Course/Quizzes', [
            'course' => $course,
            'quizzes' => $quizzes,
            'quizHistory' => $quizHistory,
        ]);
    }
}
