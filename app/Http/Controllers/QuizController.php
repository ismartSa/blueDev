<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Course; // أضف هذا في أعلى الملف
use App\Models\QuizAttempt;
use App\Imports\QuestionsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10); // Default to 10 items per page

        $quizzes = Quiz::withCount('questions')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                             ->orWhere('description', 'like', "%{$search}%");
            })
            ->paginate($perPage);

        return Inertia::render('Quizzes/Index', [
            'quizzes' => $quizzes,
            'filters' => $request->only('search', 'perPage')
        ]);
    }

    public function create()
    {
        $courses = Course::with('sections')->get();

        return Inertia::render('Quizzes/Create', [
            'courses' => $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'sections' => $course->sections->map(function ($section) {
                        return [
                            'id' => $section->id,
                            'title' => $section->title,
                        ];
                    }),
                ];
            }),
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

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Quiz created successfully. Now add some questions!');
    }

    public function show(Quiz $quiz)
    {
        $quiz->load('questions.answers'); // تأكد من أنها 'answers' وليست 'options'
        return Inertia::render('Quizzes/Show', [
            'quiz' => $quiz,
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        // التحقق من الصحة وتحديث الاختبار
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('quizzes.index')->with('success', 'Quiz deleted successfully.');
    }

    public function reports()
    {
        // منطق التقارير
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

        $question = $quiz->questions()->create([
            'question_title' => $validated['question_title'],
            'question_type_id' => $validated['question_type_id'],
            'correct_answers_required' => $validated['correct_answers_required'],
            'feedback' => $validated['feedback'],
            'image' => $validated['image'],
            'demo' => $validated['demo'] ?? false,
        ]);

        foreach ($validated['answers'] as $answerData) {
            $question->answers()->create([
                'answer' => $answerData['answer'],
                'is_correct' => $answerData['is_correct'],
            ]);
        }

        return back()->with('success', 'Question added successfully.');
    }

    public function startQuiz(Request $request, Quiz $quiz)
    {
        // Check if the user has any ongoing attempts
        $ongoingAttempt = QuizAttempt::where('user_id', auth()->id())
            ->where('quiz_id', $quiz->id)
            ->where('completed_at', null)
            ->first();

        if ($ongoingAttempt) {
            // Redirect to the ongoing attempt
            return redirect()->route('quizzes.take', ['quiz' => $quiz->id, 'attempt' => $ongoingAttempt->id]);
        }

        // Create a new attempt
        $attempt = QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'started_at' => now(),
        ]);

        // Redirect to the quiz taking page
        return redirect()->route('quizzes.take', ['quiz' => $quiz->id, 'attempt' => $attempt->id]);
    }

    public function takeQuiz(Quiz $quiz, QuizAttempt $attempt)
    {
        // Load the quiz with its questions and answers
        $quiz->load('questions.answers');

        return Inertia::render('Quizzes/Take', [
            'quiz' => $quiz,
            'attempt' => $attempt,
        ]);
    }

    public function submitQuiz(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        // التحقق من صحة البيانات الواردة
        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        // حساب النتيجة
        $totalQuestions = $quiz->questions->count();
        $correctAnswers = 0;

        foreach ($quiz->questions as $question) {
            $userAnswers = isset($validated['answers'][$question->id]) ? (array) $validated['answers'][$question->id] : [];
            $correctAnswerIds = $question->answers()->where('is_correct', true)->pluck('id')->toArray();

            $userAnswerIds = array_map('intval', $userAnswers); // تحويل القيم إلى أعداد صحيحة

            if (count($userAnswerIds) === count($correctAnswerIds) &&
                empty(array_diff($userAnswerIds, $correctAnswerIds)) &&
                empty(array_diff($correctAnswerIds, $userAnswerIds))) {
                $correctAnswers++;
            }
        }

        $score = ($correctAnswers / $totalQuestions) * 100;

        // تحديث محاولة الاختبار
        $attempt->update([
            'completed_at' => now(),
            'score' => $score,
        ]);

        // Return to the quiz taking page with the score
        return back()->with('score', $score);
    }

    public function importQuestions(Request $request, Quiz $quiz)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        Excel::import(new QuestionsImport($quiz->id), $file);

        return back()->with('success', 'Questions imported successfully.');
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/public/templates/quiz_questions_template.xlsx');

        if (file_exists($filePath)) {
            return response()->download($filePath, 'quiz_questions_template.xlsx');
        }

        return back()->with('error', 'Template file not found.');
    }

    public function importQuestionsFromJson(Request $request, Quiz $quiz)
    {
        \Log::info('Received questions:', $request->all());

        $request->validate([
            'questions' => 'required|array',
            'questions.*.question_title' => 'required|string',
            'questions.*.question_type_id' => 'required|integer',
            'questions.*.correct_answers_required' => 'required|integer',
            'questions.*.answers' => 'required|array',
            'questions.*.answers.*.answer' => 'required|string',
            'questions.*.answers.*.is_correct' => 'required|boolean',
        ]);

        DB::transaction(function () use ($request, $quiz) {
            foreach ($request->questions as $questionData) {
                $question = $quiz->questions()->create([
                    'question_title' => $questionData['question_title'],
                    'question_type_id' => $questionData['question_type_id'],
                    'correct_answers_required' => $questionData['correct_answers_required'],
                ]);

                foreach ($questionData['answers'] as $answerData) {
                    $question->answers()->create([
                        'answer' => $answerData['answer'],
                        'is_correct' => $answerData['is_correct'],
                    ]);
                }
            }
        });

        return back()->with('success', 'Questions imported successfully from JSON.');
    }

    public function questionsList(Quiz $quiz)
    {
        $quiz->load('questions.answers');
        return Inertia::render('Quizzes/QuestionsList', [
            'quiz' => $quiz
        ]);
    }
}
