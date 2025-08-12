<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Lecture;
use App\Models\QuizAttempt;
use App\Imports\QuestionsImport;
use App\Services\QuizService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class QuizController extends BaseController
{
    protected string $model = Quiz::class;
    protected string $routePrefix = 'quizzes';
    protected string $viewPrefix = 'Quizzes';
    protected array $relationships = ['course', 'lecture', 'questions'];

    protected QuizService $quizService;

    public function __construct(QuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    /**
     * Display a listing of quizzes
     */
    public function index(Request $request): Response
    {

        $quizzes = $this->quizService->getPaginated($request);
        $stats = $this->quizService->getStats();

        return $this->inertiaResponse('Index', [
            'quizzes' => $quizzes,
            'stats' => $stats,
            'courses' => Course::select('id', 'title')->get(),
            'lectures' => Lecture::select('id', 'title')->get(),
            'filters' => $request->only(['search', 'course_id', 'lecture', 'status', 'field', 'order'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Quizzes/Create', [
            'courses' => Course::with('sections:id,title,course_id')->get(['id', 'title'])
        ]);
    }

    /**
     * Store a newly created quiz
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        $quiz = $this->quizService->create($validated);

        return $this->successResponse(
            route('quizzes.show', $quiz->getKey()),
            'Quiz created successfully'
        );
    }

    /**
     * Display the specified quiz with optimized data loading
     */
    public function show(Quiz $quiz): Response
    {
        // Load quiz with relationships in single query using route model binding
        $quiz->load(['course', 'lecture', 'questions.answers']);

        // Get cached stats efficiently
        $stats = $this->quizService->getQuizStats($quiz->getKey());

        return $this->inertiaResponse('Show', compact('quiz', 'stats'));
    }

    /**
     * Update the specified quiz
     */
    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        $updatedQuiz = $this->quizService->update($quiz->getKey(), $validated);

        if (!$updatedQuiz) {
            return back()->withErrors(['error' => 'Quiz not found']);
        }

        return $this->successResponse(
            route('quizzes.show', $updatedQuiz->getKey()),
            'Quiz updated successfully'
        );
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return $this->successResponse('quizzes.index', 'Quiz deleted successfully.');
    }

    /**
     * Get validation rules for quiz
     */
    protected function getValidationRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'time_limit' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'course_id' => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:sections,id',
        ];
    }

    /**
     * Get search fields for quiz
     */
    protected function getSearchFields(): array
    {
        return ['title', 'description'];
    }

    /**
     * Get filters including course_id
     */
    protected function getFilters(\Illuminate\Http\Request $request): array
    {
        return $request->only(['search', 'field', 'order', 'perPage', 'course_id']);
    }

    public function reports()
    {
        return Inertia::render('Quizzes/Reports');
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_title' => 'required|string',
            'question_type_id' => 'required|integer',
            'correct_answers_required' => 'required|integer|min:1',
            'feedback' => 'nullable|string',
            'image' => 'nullable|string',
            'demo' => 'boolean',
            'answers' => 'required|array|min:2',
            'answers.*.answer' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        DB::transaction(function () use ($validated, $quiz) {
            $question = $quiz->questions()->create([
                'question_title' => $validated['question_title'],
                'question_type_id' => $validated['question_type_id'],
                'correct_answers_required' => $validated['correct_answers_required'],
                'feedback' => $validated['feedback'],
                'image' => $validated['image'],
                'demo' => $validated['demo'] ?? false,
            ]);

            $question->answers()->createMany(
                collect($validated['answers'])->map(fn($answer) => [
                    'answer' => $answer['answer'],
                    'is_correct' => $answer['is_correct'],
                ])->toArray()
            );
        });

        return back()->with('success', 'Question added successfully.');
    }

    public function startQuiz(Request $request, Quiz $quiz)
    {
        $ongoingAttempt = QuizAttempt::where([
            ['user_id', Auth::user()->id],
            ['quiz_id', $quiz->getKey()],
            ['completed_at', null]
        ])->first();

        if ($ongoingAttempt) {
            return redirect()->route('quizzes.take', [
                'quiz' => $quiz->getKey(),
                'attempt' => $ongoingAttempt->getKey()
            ]);
        }

        $attempt = QuizAttempt::create([
            'user_id' => Auth::user()->id,
            'quiz_id' => $quiz->getKey(),
            'started_at' => now(),
        ]);

        return redirect()->route('quizzes.take', [
            'quiz' => $quiz->getKey(),
            'attempt' => $attempt->getKey()
        ]);
    }

    public function takeQuiz(Quiz $quiz, QuizAttempt $attempt)
    {
        return Inertia::render('Quizzes/Take', [
            'quiz' => $quiz->load('questions.answers'),
            'attempt' => $attempt,
        ]);
    }

    public function submitQuiz(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $totalQuestions = $quiz->questions->count();

        if ($totalQuestions === 0) {
            return back()->withErrors(['quiz' => 'This quiz has no questions.']);
        }

        $correctAnswers = $quiz->questions->filter(function ($question) use ($validated) {
            $userAnswers = (array) ($validated['answers'][$question->getKey()] ?? []);
            $correctAnswerIds = $question->answers()->where('is_correct', true)->pluck('id')->toArray();
            $userAnswerIds = array_map('intval', $userAnswers);

            return count($userAnswerIds) === count($correctAnswerIds) &&
                   empty(array_diff($userAnswerIds, $correctAnswerIds)) &&
                   empty(array_diff($correctAnswerIds, $userAnswerIds));
        })->count();

        $score = ($correctAnswers / $totalQuestions) * 100;

        $attempt->update([
            'completed_at' => now(),
            'score' => $score,
        ]);

        return back()->with('score', $score);
    }

    public function importQuestions(Request $request, Quiz $quiz)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new QuestionsImport($quiz->getKey()), $request->file('file'));
            return back()->with('success', 'Questions imported successfully.');
        } catch (\Exception $e) {
            Log::error('Question import failed: ' . $e->getMessage());
            return back()->withErrors(['file' => 'Failed to import questions.']);
        }
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/public/templates/quiz_questions_template.xlsx');

        if (!file_exists($filePath)) {
            return back()->withErrors(['template' => 'Template file not found.']);
        }

        return response()->download($filePath, 'quiz_questions_template.xlsx');
    }

    public function importQuestionsFromJson(Request $request, Quiz $quiz)
    {
        Log::info('Received questions:', $request->all());

        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.question_title' => 'required|string',
            'questions.*.question_type_id' => 'required|integer',
            'questions.*.correct_answers_required' => 'required|integer',
            'questions.*.answers' => 'required|array',
            'questions.*.answers.*.answer' => 'required|string',
            'questions.*.answers.*.is_correct' => 'required|boolean',
        ]);

        try {
            DB::transaction(function () use ($validated, $quiz) {
                foreach ($validated['questions'] as $questionData) {
                    $question = $quiz->questions()->create([
                        'question_title' => $questionData['question_title'],
                        'question_type_id' => $questionData['question_type_id'],
                        'correct_answers_required' => $questionData['correct_answers_required'],
                    ]);

                    $question->answers()->createMany(
                        collect($questionData['answers'])->map(fn($answer) => [
                            'answer' => $answer['answer'],
                            'is_correct' => $answer['is_correct'],
                        ])->toArray()
                    );
                }
            });

            return back()->with('success', 'Questions imported successfully from JSON.');
        } catch (\Exception $e) {
            Log::error('JSON import failed: ' . $e->getMessage());
            return back()->withErrors(['questions' => 'Failed to import questions.']);
        }
    }

    public function questionsList(Quiz $quiz)
    {
        return Inertia::render('Quizzes/QuestionsList', [
            'quiz' => $quiz->load('questions.answers')
        ]);
    }

    /**
     * Show the form for editing the specified quiz
     */
    public function edit(Quiz $quiz): Response
    {
        return $this->inertiaResponse('Edit', [
            'quiz' => $quiz->load('course', 'lecture'),
            'courses' => Course::select('id', 'title')->orderBy('title')->get(),
            'lectures' => Lecture::select('id', 'title')->orderBy('title')->get()
        ]);
    }
}
