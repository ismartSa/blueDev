<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Imports\QuestionsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'course_id', 'perPage']);
        $perPage = $request->input('perPage', 10);

        $quizzes = Quiz::withCount('questions')
            ->with('course:id,title')
            ->when($filters['search'] ?? null, fn($query, $search) =>
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
            )
            ->when($filters['course_id'] ?? null, fn($query, $courseId) =>
                $query->where('course_id', $courseId)
            )
            ->paginate($perPage);

        return Inertia::render('Quizzes/Index', [
            'quizzes' => $quizzes,
            'courses' => Course::select('id', 'title')->orderBy('title')->get(),
            'filters' => $filters
        ]);
    }

    public function create()
    {
        return Inertia::render('Quizzes/Create', [
            'courses' => Course::with('sections:id,title,course_id')->get(['id', 'title'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'time_limit' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'course_id' => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $quiz = Quiz::create($validated);

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', 'Quiz created successfully. Now add some questions!');
    }

    public function show(Quiz $quiz)
    {
        return Inertia::render('Quizzes/Show', [
            'quiz' => $quiz->load('questions.answers')
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'time_limit' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'course_id' => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $quiz->update($validated);

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('quizzes.index')
            ->with('success', 'Quiz deleted successfully.');
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
            ['user_id', auth()->id()],
            ['quiz_id', $quiz->id],
            ['completed_at', null]
        ])->first();

        if ($ongoingAttempt) {
            return redirect()->route('quizzes.take', [
                'quiz' => $quiz->id,
                'attempt' => $ongoingAttempt->id
            ]);
        }

        $attempt = QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'started_at' => now(),
        ]);

        return redirect()->route('quizzes.take', [
            'quiz' => $quiz->id,
            'attempt' => $attempt->id
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
            $userAnswers = (array) ($validated['answers'][$question->id] ?? []);
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
            Excel::import(new QuestionsImport($quiz->id), $request->file('file'));
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

    public function edit(Quiz $quiz)
    {
        return Inertia::render('Quizzes/Edit', [
            'quiz' => $quiz,
            'courses' => Course::with('sections:id,title,course_id')->get(['id', 'title']),
        ]);
    }
}
