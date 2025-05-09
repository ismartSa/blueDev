<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use App\Models\Course;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
Route::get('/', function () {
    $courses = Course::latest()->take(6)->get();
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'courses' => $courses,
    ]);
});

Route::get('/setLang/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return back();
})->name('setlang');

// Authentication routes
require __DIR__.'/auth.php';

// Google Authentication routes
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Public course routes
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{id}/details/{courseSlug}', [CourseController::class, 'show'])->name('course.details');

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'users'       => (int) User::count(),
            'roles'       => (int) Role::count(),
            'permissions' => (int) Permission::count(),
            'courses'     => (int) Course::count(),
        ]);
    })->name('dashboard');

    // Profile routes
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // User management routes
    Route::controller(UserController::class)->group(function () {
        Route::resource('/user', UserController::class)->except(['create', 'show', 'edit']);
        Route::post('/user/destroy-bulk', 'destroyBulk')->name('user.destroy-bulk');
    });

    // Role management routes
    Route::controller(RoleController::class)->group(function () {
        Route::resource('/role', RoleController::class)->except(['create', 'show', 'edit']);
        Route::post('/role/destroy-bulk', 'destroyBulk')->name('role.destroy-bulk');
    });

    // Permission management routes
    Route::controller(PermissionController::class)->group(function () {
        Route::resource('/permission', PermissionController::class)->except(['create', 'show', 'edit']);
        Route::post('/permission/destroy-bulk', 'destroyBulk')->name('permission.destroy-bulk');
    });

    // Course routes - Admin only
    Route::group(['middleware' => ['can:manage courses']], function () {
        Route::resource('/courses', CourseController::class)->except(['index', 'show']);
        Route::post('/courses/destroy-bulk', [CourseController::class, 'destroyBulk'])->name('course.destroy-bulk');
        Route::get('/courses/{courseId}/lecture/create/', [CourseController::class, 'createLecture'])->name('course.create.lecture');
        Route::post('/courses/{course}/sections', [CourseController::class, 'storeSection'])->name('courses.sections.store');
        Route::post('/courses/{course}/sections/{section}/lectures', [CourseController::class, 'storeLecture'])->name('courses.sections.lectures.store');
        Route::delete('/courses/{course}/sections/{section}', [CourseController::class, 'destroySection'])->name('course.sections.destroy');
        Route::get('/courses/{course}/quizzes', [CourseController::class, 'quizzes'])->name('course.quizzes');
    });

    // Course player routes - For enrolled users
    Route::get('/courses/{courseId}/player/{courseSlug}', [CourseController::class, 'coursePlayer'])->name('course.player');
    Route::get('/courses/{courseId}/player/{courseSlug}/watch/{lectureID}', [CourseController::class, 'watchLecture'])->name('course.watchLecture');
    Route::post('/lectures/mark-completed', [CourseController::class, 'markLectureAsCompleted'])->name('lecture.markCompleted');
    Route::get('/courses/{courseId}/learn/{courseSlug}', [CourseController::class, 'learn'])->name('course.learn');

    // Section routes
    Route::get('/sections/{id}/edit', [SectionController::class, 'edit'])->name('sections.edit');

    // Enroll routes
    Route::controller(EnrollmentController::class)->middleware(['auth'])->group(function () {
        Route::post('/courses/{courseId}/enroll', 'enroll')->name('courses.enroll');
        Route::get('/courses/{courseId}/check-enrollment', 'checkEnrollment')->name('courses.checkEnrollment');
        Route::post('/courses/{courseId}/update-progress', 'updateProgress')->name('courses.updateProgress');
    });

    // Quiz routes
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', [QuizController::class, 'index'])->name('index');
        Route::get('/create', [QuizController::class, 'create'])->name('create')->middleware('can:create quiz');
        Route::get('/reports', [QuizController::class, 'reports'])->name('reports')->middleware('can:view quiz reports');
        Route::post('/', [QuizController::class, 'store'])->name('store')->middleware('can:create quiz');
        Route::get('/{quiz}', [QuizController::class, 'show'])->name('show');
        Route::put('/{quiz}', [QuizController::class, 'update'])->name('update')->middleware('can:update quiz');
        Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('destroy')->middleware('can:delete quiz');

        // Quiz questions
        Route::get('/{quiz}/questions', [QuizController::class, 'questionsList'])->name('questions.list');
        Route::get('/{quiz}/questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/{quiz}/questions', [QuestionController::class, 'store'])->name('questions.store');

        // Quiz taking
        Route::post('/{quiz}/start', [QuizController::class, 'startQuiz'])->name('start');
        Route::get('/{quiz}/take/{attempt}', [QuizController::class, 'takeQuiz'])->name('take');
        Route::post('/{quiz}/submit/{attempt}', [QuizController::class, 'submitQuiz'])->name('submit');

        // Quiz import
        Route::post('/{quiz}/import-questions', [QuizController::class, 'importQuestions'])->name('import-questions');
        Route::post('/{quiz}/import-questions-json', [QuizController::class, 'importQuestionsFromJson'])->name('import-questions-json');
        Route::get('/template/download', [QuizController::class, 'downloadTemplate'])->name('template.download');
    });
});

// Admin quiz management routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/create', [QuizController::class, 'create'])->name('create');
        Route::post('/', [QuizController::class, 'store'])->name('store');
        Route::get('/{quizzes}/edit', [QuizController::class, 'edit'])->name('edit');
        Route::put('/{quizzes}', [QuizController::class, 'update'])->name('update');
        Route::delete('/{quizzes}', [QuizController::class, 'destroy'])->name('destroy');
        Route::get('/reports', [QuizController::class, 'reports'])->name('reports');
    });
});


