<?php

use App\Models\{Role, User, Permission, Course};
use App\Http\Controllers\{
    ProfileController,
    UserController,
    RoleController,
    PermissionController,
    CourseController,
    EnrollmentController,
    SectionController,
    GoogleController,
    QuizController,
    QuestionController
};
use App\Http\Controllers\Opt\OptController;
use Illuminate\Support\Facades\{Route, Session};
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
Route::post('/user/login-as', [UserController::class, 'loginAsUser'])->name('user.loginAs');
/*
|--------------------------------------------------------------------------
| Public Course Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/{id}/details/{courseSlug}', [CourseController::class, 'show'])->name('details');
        Route::get('/{courseId}/learn/{courseSlug}', [CourseController::class, 'learn'])->name('learn');
        Route::post('/{courseId}/enroll', [EnrollmentController::class, 'enroll'])->name('enroll');
        Route::get('/{courseId}/player/{courseSlug}', [CourseController::class, 'coursePlayer'])->name('player');
        Route::get('/{courseId}/player/{courseSlug}/watch/{lectureID}', [CourseController::class, 'watchLecture'])->name('watch');
        Route::post('/lectures/mark-completed', [CourseController::class, 'markLectureAsCompleted'])->name('lecture.complete');
    });
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
Route::get('/dashboard', function () {
    $stats = Cache::remember('dashboard_stats', 60*60, function () {
        return [
            'users' => (int) DB::table('users')->count(),
            'roles' => (int) DB::table('roles')->count(),
            'permissions' => (int) DB::table('permissions')->count(),
            'courses' => (int) DB::table('courses')->count(),
        ];
    });

    return Inertia::render('Dashboard', $stats);
})->name('dashboard');

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
    Route::prefix('user')->name('user.')->group(function () {
        Route::resource('/', UserController::class)->except(['create', 'show', 'edit']);
        Route::post('/destroy-bulk', [UserController::class, 'destroyBulk'])->name('destroy-bulk');
    });

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
            Route::get('/create', [CourseController::class, 'create'])->name('create'); // تأكد من وجود هذا المسار
            Route::post('/', [CourseController::class, 'store'])->name('store'); // تأكد من وجود هذا المسار
           // Route::resource('', CourseController::class)->except(['index', 'show', 'create', 'store']);
            Route::post('/destroy-bulk', [CourseController::class, 'destroyBulk'])->name('destroy-bulk');
            Route::get('{courseId}/details/', [CourseController::class, 'details'])->name('details.show');
            Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');

            // Lecture and Section Management
            Route::prefix('{course}')->group(function () {
                Route::get('/lecture/create', [CourseController::class, 'createLecture'])->name('lecture.create');
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

    // Course Enrollment Routes
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::post('/{courseId}/enroll', [EnrollmentController::class, 'enroll'])->name('enroll');
        Route::get('/{courseId}/check-enrollment', [EnrollmentController::class, 'checkEnrollment'])->name('check-enrollment');
        Route::post('/{courseId}/update-progress', [EnrollmentController::class, 'updateProgress'])->name('update-progress');
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

            Route::prefix('{quiz}')->group(function () {
                Route::get('/', [QuizController::class, 'show'])->name('show');
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
});

/*
|--------------------------------------------------------------------------
| Admin Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])
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
Route::prefix('opt')->name('opt.')->group(function () {

    // Dashboard
    Route::get('/', [OptController::class, 'index'])->name('index');

    // Upload JSON
    Route::get('/upload', [OptController::class, 'showUploadForm'])->name('upload.form');
    Route::post('/upload-json', [OptController::class, 'uploadJson'])->name('upload.json');

    // View Data
    Route::get('/data-viewer', [OptController::class, 'showDataViewer'])->name('data.viewer');
    Route::post('/get-data', [OptController::class, 'getData'])->name('get.data');

    // Execute Query
    Route::get('/query-executor', [OptController::class, 'showQueryExecutor'])->name('query.executor');
    Route::post('/execute-query', [OptController::class, 'executeQuery'])->name('execute.query');

    // Test Connection
    Route::post('/test-connection', [OptController::class, 'testConnection'])->name('test.connection');

    // Get Tables (AJAX)
    Route::post('/get-tables', [OptController::class, 'getTables'])->name('get.tables');

    // Get PostgreSQL Info (AJAX)
    Route::post('/get-postgresql-info', [OptController::class, 'getPostgresqlInfo'])->name('get.postgresql.info');

    // Export Data
    Route::get('/export', [OptController::class, 'showExportForm'])->name('export.form');
    Route::post('/export-json', [OptController::class, 'exportToJson'])->name('export.json');
    Route::get('/download/{filename}', [OptController::class, 'downloadFile'])->name('download.file');

});
