<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

/**
 * Handles quiz question management including creating, updating,
 * and deleting questions and their answers.
 */
class QuizQuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage,quiz')->except(['index', 'show']);
    }

    /**
     * Display questions for a quiz
     */
    public function index(Quiz $quiz)
    {
        $questions = $quiz->questions()
            ->with('answers')
            ->orderBy('order')
            ->get()
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'type' => $question->type,
                    'order' => $question->order,
                    'answers' => $question->answers->map(function ($answer) {
                        return [
                            'id' => $answer->id,
                            'answer' => $answer->getTranslation(),
                            'is_correct' => $answer->is_correct,
                        ];
                    }),
                    'answers_count' => $question->answers->count(),
                    'correct_answers_count' => $question->answers->where('is_correct', true)->count(),
                ];
            });

        return Inertia::render('Quiz/Questions/Index', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
            ],
            'questions' => $questions,
            'can_manage' => Gate::allows('update', $quiz),
        ]);
    }

    /**
     * Show form for creating a new question
     */
    public function create(Quiz $quiz)
    {
        return Inertia::render('Quiz/Questions/Create', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
            ],
        ]);
    }

    /**
     * Store a new question
     */
    public function store(Request $request, Quiz $quiz)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'type' => 'required|in:multiple_choice,true_false,text',
            'answers' => 'required|array|min:2',
            'answers.*.answer_en' => 'required|string|max:500',
            'answers.*.answer_ar' => 'nullable|string|max:500',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        // Validate that at least one answer is correct
        $correctAnswers = collect($request->answers)->where('is_correct', true);
        if ($correctAnswers->isEmpty()) {
            return back()->withErrors(['answers' => 'At least one answer must be marked as correct.']);
        }

        DB::transaction(function () use ($request, $quiz) {
            // Get next order number
            $nextOrder = $quiz->questions()->max('order') + 1;

            // Create question
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'question' => $request->question,
                'type' => $request->type,
                'order' => $nextOrder,
            ]);

            // Create answers
            foreach ($request->answers as $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_en' => $answerData['answer_en'],
                    'answer_ar' => $answerData['answer_ar'] ?? null,
                    'is_correct' => $answerData['is_correct'],
                ]);
            }
        });

        return redirect()->route('quiz.questions.index', $quiz)
            ->with('success', 'Question created successfully!');
    }

    /**
     * Show a specific question
     */
    public function show(Quiz $quiz, Question $question)
    {
        // Verify question belongs to quiz
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        $questionData = [
            'id' => $question->id,
            'question' => $question->question,
            'type' => $question->type,
            'order' => $question->order,
            'answers' => $question->answers->map(function ($answer) {
                return [
                    'id' => $answer->id,
                    'answer_en' => $answer->answer_en,
                    'answer_ar' => $answer->answer_ar,
                    'answer' => $answer->getTranslation(),
                    'is_correct' => $answer->is_correct,
                ];
            }),
        ];

        return Inertia::render('Quiz/Questions/Show', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
            ],
            'question' => $questionData,
            'can_manage' => Gate::allows('update', $quiz),
        ]);
    }

    /**
     * Show form for editing a question
     */
    public function edit(Quiz $quiz, Question $question)
    {
        // Verify question belongs to quiz
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        $questionData = [
            'id' => $question->id,
            'question' => $question->question,
            'type' => $question->type,
            'order' => $question->order,
            'answers' => $question->answers->map(function ($answer) {
                return [
                    'id' => $answer->id,
                    'answer_en' => $answer->answer_en,
                    'answer_ar' => $answer->answer_ar,
                    'is_correct' => $answer->is_correct,
                ];
            }),
        ];

        return Inertia::render('Quiz/Questions/Edit', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
            ],
            'question' => $questionData,
        ]);
    }

    /**
     * Update a question
     */
    public function update(Request $request, Quiz $quiz, Question $question)
    {
        // Verify question belongs to quiz
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        $request->validate([
            'question' => 'required|string|max:1000',
            'type' => 'required|in:multiple_choice,true_false,text',
            'answers' => 'required|array|min:2',
            'answers.*.id' => 'nullable|exists:answers,id',
            'answers.*.answer_en' => 'required|string|max:500',
            'answers.*.answer_ar' => 'nullable|string|max:500',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        // Validate that at least one answer is correct
        $correctAnswers = collect($request->answers)->where('is_correct', true);
        if ($correctAnswers->isEmpty()) {
            return back()->withErrors(['answers' => 'At least one answer must be marked as correct.']);
        }

        DB::transaction(function () use ($request, $question) {
            // Update question
            $question->update([
                'question' => $request->question,
                'type' => $request->type,
            ]);

            // Get existing answer IDs
            $existingAnswerIds = $question->answers->pluck('id')->toArray();
            $submittedAnswerIds = collect($request->answers)
                ->pluck('id')
                ->filter()
                ->toArray();

            // Delete removed answers
            $answersToDelete = array_diff($existingAnswerIds, $submittedAnswerIds);
            if (!empty($answersToDelete)) {
                Answer::whereIn('id', $answersToDelete)->delete();
            }

            // Update or create answers
            foreach ($request->answers as $answerData) {
                if (isset($answerData['id']) && $answerData['id']) {
                    // Update existing answer
                    Answer::where('id', $answerData['id'])->update([
                        'answer_en' => $answerData['answer_en'],
                        'answer_ar' => $answerData['answer_ar'] ?? null,
                        'is_correct' => $answerData['is_correct'],
                    ]);
                } else {
                    // Create new answer
                    Answer::create([
                        'question_id' => $question->id,
                        'answer_en' => $answerData['answer_en'],
                        'answer_ar' => $answerData['answer_ar'] ?? null,
                        'is_correct' => $answerData['is_correct'],
                    ]);
                }
            }
        });

        return redirect()->route('quiz.questions.index', $quiz)
            ->with('success', 'Question updated successfully!');
    }

    /**
     * Delete a question
     */
    public function destroy(Quiz $quiz, Question $question)
    {
        // Verify question belongs to quiz
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        DB::transaction(function () use ($question) {
            // Delete associated answers
            $question->answers()->delete();
            
            // Delete question
            $question->delete();
        });

        return back()->with('success', 'Question deleted successfully!');
    }

    /**
     * Reorder questions
     */
    public function reorder(Request $request, Quiz $quiz)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.order' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $quiz) {
            foreach ($request->questions as $questionData) {
                Question::where('id', $questionData['id'])
                    ->where('quiz_id', $quiz->id)
                    ->update(['order' => $questionData['order']]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Questions reordered successfully!'
        ]);
    }

    /**
     * Duplicate a question
     */
    public function duplicate(Quiz $quiz, Question $question)
    {
        // Verify question belongs to quiz
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        DB::transaction(function () use ($quiz, $question) {
            // Get next order number
            $nextOrder = $quiz->questions()->max('order') + 1;

            // Create duplicate question
            $newQuestion = Question::create([
                'quiz_id' => $quiz->id,
                'question' => $question->question . ' (Copy)',
                'type' => $question->type,
                'order' => $nextOrder,
            ]);

            // Duplicate answers
            foreach ($question->answers as $answer) {
                Answer::create([
                    'question_id' => $newQuestion->id,
                    'answer_en' => $answer->answer_en,
                    'answer_ar' => $answer->answer_ar,
                    'is_correct' => $answer->is_correct,
                ]);
            }
        });

        return back()->with('success', 'Question duplicated successfully!');
    }
}