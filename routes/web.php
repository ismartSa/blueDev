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
*/

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
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

    // Course routes
    Route::controller(CourseController::class)->group(function () {
        Route::resource('/courses', CourseController::class);
        Route::get('/courses/{id}/details/{courseSlug}', 'show')->name('course.details');
        Route::get('/courses/{courseId}/player/{courseSlug}', 'coursePlayer')->name('course.player');
        Route::get('/courses/{courseId}/player/{courseSlug}/watch/{lectureID}', 'watchLecture')->name('course.watchLecture');
        Route::post('/lectures/mark-completed', 'markLectureAsCompleted')->name('lecture.markCompleted');
        Route::post('/courses/destroy-bulk', 'destroyBulk')->name('course.destroy-bulk');
        Route::get('/courses/{courseId}/learn/{courseSlug}', 'learn')->name('course.learn');
        Route::get('/courses/{courseId}/lecture/create/', 'createLecture')->name('course.create.lecture');
        Route::post('/courses/{course}/sections', 'storeSection')->name('courses.sections.store');
        Route::post('/courses/{course}/sections/{section}/lectures', 'storeLecture')->name('courses.sections.lectures.store');
        Route::delete('/courses/{course}/sections/{section}', 'destroySection')->name('course.sections.destroy');
        Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::delete('/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
    });

    // Section routes
    Route::get('/sections/{id}/edit', [SectionController::class, 'edit'])->name('sections.edit');

    // Enroll routes
    Route::controller(EnrollController::class)->group(function () {
        Route::post('/courses/{courseId}/enroll', 'enroll')->name('courses.enroll');
        Route::get('/courses/{courseId}/check-enrollment', 'checkEnrollment')->name('courses.checkEnrollment');
    });
});

// طرق الاختبارات
Route::middleware(['auth'])->group(function () {
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', [QuizController::class, 'index'])->name('index');
        Route::get('/create', [QuizController::class, 'create'])->name('create')->middleware('can:create quiz');
        Route::get('/reports', [QuizController::class, 'reports'])->name('reports')->middleware('can:view quiz reports');

        // يمكنك إضافة المزيد من الطرق هنا
        Route::post('/', [QuizController::class, 'store'])->name('store')->middleware('can:create quiz');
        Route::get('/{quiz}', [QuizController::class, 'show'])->name('show');
        Route::put('/{quiz}', [QuizController::class, 'update'])->name('update')->middleware('can:update quiz');
        Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('destroy')->middleware('can:delete quiz');
    });
});

// طرق إدارة الاختبارات (للمسؤولين فقط)
Route::middleware(['auth', 'admin'])->group(function () {
    // إنشاء اختبار جديد
    Route::get('/admin/quizzes/create', [QuizController::class, 'create'])
         ->name('admin.quizzes.create');
    Route::post('/admin/quizzes', [QuizController::class, 'store'])
         ->name('admin.quizzes.store');

    // تعديل اختبار موجود
    Route::get('/admin/quizzes/{quizzes}/edit', [QuizController::class, 'edit'])
         ->name('admin.quizzes.edit');
    Route::put('/admin/quizzes/{quizzes}', [QuizController::class, 'update'])
         ->name('admin.quizzes.update');

    // حذف اختبار
    Route::delete('/admin/quizzes/{quizzes}', [QuizController::class, 'destroy'])
         ->name('admin.quizzes.destroy');

    // عرض تقارير وإحصائيات الاختبارات
    Route::get('/admin/quizzes/reports', [QuizController::class, 'reports'])
         ->name('admin.quizzes.reports');
});

Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('quizzes.questions.store');
Route::post('/quizzes/{quiz}/start', [QuizController::class, 'startQuiz'])->name('quizzes.start');

Route::get('/quizzes/{quiz}/take/{attempt}', [QuizController::class, 'takeQuiz'])->name('quizzes.take');

Route::post('/quizzes/{quiz}/submit/{attempt}', [QuizController::class, 'submitQuiz'])->name('quizzes.submit');

Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');

Route::post('/quizzes/{quiz}/import-questions', [QuizController::class, 'importQuestions'])->name('quizzes.import-questions');

Route::post('/quizzes/{quiz}/import-questions-json', [QuizController::class, 'importQuestionsFromJson'])
    ->name('quizzes.import-questions-json');

Route::get('/quizzes/template/download', [QuizController::class, 'downloadTemplate'])->name('quizzes.template.download');

Route::get('/quizzes/{quiz}/questions/create', [QuestionController::class, 'create'])->name('quizzes.questions.create');
Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('quizzes.questions.store');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.details');


