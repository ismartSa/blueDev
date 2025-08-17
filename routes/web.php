<?php

use App\Models\{Role, User, Permission, Course};
use App\Http\Controllers\{
    ProfileController,
    UserController,
    RoleController,
    PermissionController,
    CourseController,
    CourseWishlistController,
    CourseEnrollmentController,
    EnrollmentController,
    CourseRecommendationController,
    SectionController,
    GoogleController,
    QuizController,
    QuestionController,
    CategoryController,
    SettingsController,
    BackupController,
};
use App\Http\Controllers\Course\CourseContentController;
use App\Http\Controllers\Course\CourseManagementController;

use App\Http\Controllers\Opt\OptController;
use Illuminate\Support\Facades\{Route, Session, Cache, DB, App};
use Illuminate\Foundation\Application;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Index/Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'courses' => Course::latest()->take(3)->get(),
    ]);
});

// Language Switch Routes
Route::get('/setLang/{locale}', function ($locale) {
    // Verify the locale is supported
    if (!in_array($locale, ['en', 'ar'])) {
        return back()->with('error', 'Language not supported');
    }

    // Set locale in session and app
    Session::put('locale', $locale);
    App::setLocale($locale);

    return back()->with('success', 'Language changed successfully');
})->name('setlang');

// Authentication Routes
require __DIR__.'/auth.php';

// Google Login Routes
Route::prefix('auth/google')->group(function () {
    Route::get('/', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/callback', [GoogleController::class, 'handleGoogleCallback']);
});
// User Login as Admin
Route::get('admix', [UserController::class, 'loginAsUser'])->name('user.loginAs');
// User Reset Password
Route::post('/user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('user.reset-password');
/*
|--------------------------------------------------------------------------
| Public Course Routes
|--------------------------------------------------------------------------
*/
// Public explore route - no authentication required
Route::get('/courses/explore', [CourseController::class, 'explore'])->name('courses.explore');

// Protected course routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/{id}/details/{courseSlug}', [CourseController::class, 'show'])->name('show');
        Route::get('/{courseId}/learn/{courseSlug}', [CourseController::class, 'learn'])->name('learn');
        Route::post('/{courseId}/enroll', [EnrollmentController::class, 'enroll'])->name('enroll');
        Route::get('/{courseId}/player/{courseSlug}', [CourseController::class, 'coursePlayer'])->name('player');
        Route::get('/{courseId}/player/{courseSlug}/watch/{lectureID}', [CourseController::class, 'watchLecture'])->name('watch');
        Route::post('/lectures/mark-completed', [CourseController::class, 'markLectureAsCompleted'])->name('lecture.complete');
        Route::post('/{courseId}/wishlist/toggle', [CourseController::class, 'toggleWishlist'])->name('courses.wishlist.toggle');
    });
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Wishlist
    Route::get('/my-wishlist', [CourseWishlistController::class, 'index'])->name('wishlist');

    // Dashboard
Route::get('/dashboard', function () {
    $stats = Cache::remember('dashboard_stats', 60*60, function () {
        return [
            'users' => (int) DB::table('users')->count(),
            'roles' => (int) DB::table('roles')->count(),
            'permissions' => (int) DB::table('permissions')->count(),
            'courses' => (int) DB::table('courses')->count(),
            'quizzes' => (int) DB::table('quizzes')->count(),
            'enrollments' => (int) DB::table('enrollments')->count(),
        ];
    });

    return Inertia::render('Dashboard', $stats);
})->name('dashboard');

    // Dashboard Course Management Routes
    Route::middleware(['can:manage courses'])
        ->prefix('dashboard/courses')
        ->name('dashboard.courses.')
        ->group(function () {
            Route::get('/', [CourseController::class, 'index'])->name('index');
            Route::post('/', [CourseController::class, 'store'])->name('store');
            Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('edit');
            Route::put('/{course}', [CourseController::class, 'update'])->name('update');
            Route::get('/{course}/enrollments', [CourseEnrollmentController::class, 'enrollments'])->name('enrollments');
        });

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::controller(ProfileController::class)
        ->prefix('profile')
        ->name('profile.')
        ->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::patch('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | User, Role, and Permission Management Routes
    |--------------------------------------------------------------------------
    */
    // User Management
    Route::resource('user', UserController::class)->except(['create']);
    Route::post('user/destroy-bulk', [UserController::class, 'destroyBulk'])->name('user.destroy-bulk');
    Route::patch('user/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('user.toggle-status');

    // Role Management
    Route::resource('/role', RoleController::class)->except('create', 'show', 'edit');
        Route::post('/destroy-bulk', [RoleController::class, 'destroyBulk'])->name('destroy-bulk');


    // Permission Management
    Route::prefix('permission')->name('permission.')->group(function () {
        Route::resource('', PermissionController::class)->except(['create', 'show', 'edit']);
        Route::post('/destroy-bulk', [PermissionController::class, 'destroyBulk'])->name('destroy-bulk');
    });


    /*
    |--------------------------------------------------------------------------
    | Course Management Routes
    |--------------------------------------------------------------------------
    */
    // Admin Only Routes
    Route::middleware(['can:manage courses'])
        ->prefix('courses')
        ->name('courses.')
        ->group(function () {
            Route::get('/create', [CourseController::class, 'create'])->name('create');
            Route::post('/', [CourseController::class, 'store'])->name('store');
            Route::post('/destroy-bulk', [CourseController::class, 'destroyBulk'])->name('destroy-bulk');
            Route::get('{courseId}/details/', [CourseController::class, 'details'])->name('details.show');
            Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('edit');
            Route::put('/{course}', [CourseController::class, 'update'])->name('update');

            // Design Settings Routes
            Route::get('/{course}/design', [CourseController::class, 'designSettings'])->name('design.settings');
            Route::post('/{course}/design', [CourseController::class, 'updateDesignSettings'])->name('design.update');

            // Lecture and Section Management
            Route::prefix('{course}')->group(function () {
                Route::get('/lecture/create', [CourseController::class, 'createLecture'])->name('lecture.create');
                Route::post('/lecture', [CourseContentController::class, 'storeLecture'])->name('lecture.store');
                Route::post('/sections', [CourseController::class, 'storeSection'])->name('sections.store');
                Route::post('/sections/{section}/lectures', [CourseController::class, 'storeLecture'])->name('sections.lectures.store');
                Route::delete('/sections/{section}', [CourseController::class, 'destroySection'])->name('sections.destroy');
                Route::get('/quizzes', [CourseController::class, 'quizzes'])->name('quizzes');
            });
        });

    // Course Player Routes - For Enrolled Users
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/{courseId}/player/{courseSlug}', [CourseController::class, 'coursePlayer'])->name('player');
        Route::get('/{courseId}/player/{courseSlug}/watch/{lectureID}', [CourseController::class, 'watchLecture'])->name('watch');
        Route::post('/lectures/mark-completed', [CourseController::class, 'markLectureAsCompleted'])->name('lecture.complete');
        Route::get('/{courseId}/learn/{courseSlug}', [CourseController::class, 'learn'])->name('learn');
    });

    // My Courses Routes
    Route::prefix('my-courses')->name('my-courses.')->group(function () {
        Route::get('/', [EnrollmentController::class, 'myCourses'])->name('index');
        Route::get('/suggestions', [CourseRecommendationController::class, 'suggestions'])->name('suggestions');
    });

    // API Enrollment Route
    Route::post('/courses/{courseId}/enroll-api', [EnrollmentController::class, 'enrollApi'])->name('courses.enroll-api');

    // Course Enrollment Routes
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::post('/{courseId}/enroll', [EnrollmentController::class, 'enroll'])->name('enroll');
        Route::get('/{courseId}/check-enrollment', [EnrollmentController::class, 'checkEnrollment'])->name('check-enrollment');
        Route::post('/{courseId}/update-progress', [EnrollmentController::class, 'updateProgress'])->name('update-progress');
    });

    /*
    |--------------------------------------------------------------------------
    | Backup Management Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['can:manage courses'])
        ->prefix('backup')
        ->name('backup.')
        ->group(function () {
            Route::get('/status', [BackupController::class, 'getStatus'])->name('status');
            Route::post('/test-connection', [BackupController::class, 'testConnection'])->name('test');
            Route::post('/user', [BackupController::class, 'backupUser'])->name('user');
            Route::post('/users/bulk', [BackupController::class, 'bulkBackupUsers'])->name('users.bulk');
            Route::post('/sync-all', [BackupController::class, 'syncAllData'])->name('sync.all');
        });

    // Alternative Course Enrollment Routes (using CourseEnrollmentController)
    Route::prefix('course')->name('course.')->group(function () {
        Route::post('/{courseId}/enroll', [\App\Http\Controllers\Course\CourseEnrollmentController::class, 'enroll'])->name('enroll');
        Route::get('/{courseId}/check-enrollment', [\App\Http\Controllers\Course\CourseEnrollmentController::class, 'checkEnrollment'])->name('check-enrollment');
    });

    /*
    |--------------------------------------------------------------------------
    | Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['can:manage courses'])
        ->prefix('admin/settings')
        ->name('admin.settings.')
        ->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::post('/update', [SettingsController::class, 'update'])->name('update');
            Route::post('/upload/{key}', [SettingsController::class, 'uploadFile'])->name('upload');
            Route::get('/get/{key}', [SettingsController::class, 'getSetting'])->name('get');
            Route::post('/reset', [SettingsController::class, 'reset'])->name('reset');
            Route::get('/database-status', [SettingsController::class, 'refreshDatabaseStatus'])->name('database.status');
        });

    /*
    |--------------------------------------------------------------------------
    | Quiz Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('quizzes')
        ->name('quizzes.')
        ->group(function () {
            Route::get('/', [QuizController::class, 'index'])->name('index');
            Route::middleware('can:create quiz')->group(function () {
                Route::get('/create', [QuizController::class, 'create'])->name('create');
                Route::post('/', [QuizController::class, 'store'])->name('store');
            });

            Route::get('/reports', [QuizController::class, 'reports'])
                ->name('reports')
                ->middleware('can:view quiz reports');

            Route::post('/activate-all', [QuizController::class, 'activateAll'])->name('activate-all')->middleware('can:update quiz');

            Route::prefix('{quiz}')->group(function () {

                Route::get('/show', [QuizController::class, 'show'])->name('show');
                Route::get('/edit', [QuizController::class, 'edit'])->name('edit')->middleware('can:update quiz'); // Add this line
                Route::put('/', [QuizController::class, 'update'])->name('update')->middleware('can:update quiz');
                Route::delete('/', [QuizController::class, 'destroy'])->name('destroy')->middleware('can:delete quiz');

                // Quiz Questions
                Route::prefix('questions')->name('questions.')->group(function () {
                    Route::get('/', [QuizController::class, 'questionsList'])->name('list');
                    Route::get('/create', [QuestionController::class, 'create'])->name('create');
                    Route::post('/', [QuestionController::class, 'store'])->name('store');
                });

                // Quiz Taking
                Route::post('/start', [QuizController::class, 'startQuiz'])->name('start');
                Route::get('/take/{attempt}', [QuizController::class, 'takeQuiz'])->name('take');
                Route::post('/submit/{attempt}', [QuizController::class, 'submitQuiz'])->name('submit');

                // Question Import
                Route::post('/import-questions', [QuizController::class, 'importQuestions'])->name('import-questions');
                Route::post('/import-questions-json', [QuizController::class, 'importQuestionsFromJson'])->name('import-questions-json');
            });

            Route::get('/template/download', [QuizController::class, 'downloadTemplate'])->name('template.download');
        });

    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::prefix('quizzes')->name('quizzes.')->group(function () {
                Route::get('/create', [QuizController::class, 'create'])->name('create');
                Route::post('/', [QuizController::class, 'store'])->name('store');
                Route::get('/{quiz}/edit', [QuizController::class, 'edit'])->name('edit');
                Route::put('/{quiz}', [QuizController::class, 'update'])->name('update');
                Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('destroy');
                Route::get('/reports', [QuizController::class, 'reports'])->name('reports');
            });
        });

    // Main Opt Routes
    Route::get('/opt', [OptController::class, 'index'])->name('opt.index');
    Route::post('/opt/convert', [OptController::class, 'convertToSql'])->name('opt.convert');

    // Category Management Routes
    Route::prefix('category')->name('category.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::post('/destroy-bulk', [CategoryController::class, 'destroyBulk'])->name('destroy-bulk');
    });

    // Additional Course Management Routes
    Route::prefix('courses')->group(function () {
        Route::get('/', [\App\Http\Controllers\Course\CourseManagementController::class, 'index'])->name('courses.index');
        Route::get('/create', [\App\Http\Controllers\Course\CourseManagementController::class, 'create'])->name('courses.create');
        Route::post('/', [\App\Http\Controllers\Course\CourseManagementController::class, 'store'])->name('courses.store');
        Route::get('/{course}/edit', [\App\Http\Controllers\Course\CourseManagementController::class, 'edit'])->name('courses.edit');
        Route::put('/{course}', [\App\Http\Controllers\Course\CourseManagementController::class, 'update'])->name('courses.update');
        Route::delete('/{course}', [\App\Http\Controllers\Course\CourseManagementController::class, 'destroy'])->name('courses.destroy');
        Route::post('/destroy-bulk', [\App\Http\Controllers\Course\CourseManagementController::class, 'destroyBulk'])->name('courses.destroy-bulk');

        // Course Content Routes
        Route::prefix('{course}')->group(function () {
            Route::post('/sections', [\App\Http\Controllers\Course\CourseContentController::class, 'storeSection']);
            Route::post('/lectures', [\App\Http\Controllers\Course\CourseContentController::class, 'storeLecture']);
            Route::get('/quizzes', [\App\Http\Controllers\Course\CourseContentController::class, 'showQuizzes'])->name('course.quizzes');
            Route::get('/quiz/{quiz}', [\App\Http\Controllers\Course\CourseContentController::class, 'showQuiz'])->name('course.quiz.show');
            Route::post('/quiz/create', [CourseContentController::class, 'createQuiz'])->name('course.quiz.create');
        });
    });

    // Quiz management routes
    Route::post('/quiz/{quiz}/submit', [\App\Http\Controllers\Course\CourseContentController::class, 'submitQuiz'])->name('quiz.submit');
    Route::post('/quiz/{quiz}/question', [CourseContentController::class, 'storeQuestion'])->name('quiz.question.store');

}); // Close protected routes middleware group

