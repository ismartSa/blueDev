<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Handles quiz attempt operations including starting attempts,
 * submitting answers, and calculating scores.
 */
class QuizAttemptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Start a new quiz attempt
     */
    public function start(Quiz $quiz)
    {
        $user = Auth::user();

        // Check if user has reached maximum attempts
        $attemptCount = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->count();

        if ($quiz->max_attempts && $attemptCount >= $quiz->max_attempts) {
            return back()->with('error', 'You have reached the maximum number of attempts for this quiz.');
        }

        // Check if there's an ongoing attempt
        $ongoingAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->whereNull('completed_at')
            ->first();

        if ($ongoingAttempt) {
            return redirect()->route('quiz.attempt.show', [$quiz->id, $ongoingAttempt->id]);
        }

        // Create new attempt
        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'started_at' => now(),
            'time_limit' => $quiz->time_limit,
        ]);

        return redirect()->route('quiz.attempt.show', [$quiz->id, $attempt->id]);
    }

    /**
     * Display quiz attempt
     */
    public function show(Quiz $quiz, QuizAttempt $attempt)
    {
        $user = Auth::user();

        // Verify attempt belongs to user
        if ($attempt->user_id !== $user->id) {
            abort(403);
        }

        // Check if attempt is completed
        if ($attempt->completed_at) {
            return redirect()->route('quiz.attempt.result', [$quiz->id, $attempt->id]);
        }

        // Check if time limit exceeded
        if ($attempt->time_limit && $attempt->started_at->addMinutes($attempt->time_limit)->isPast()) {
            $this->completeAttempt($attempt);
            return redirect()->route('quiz.attempt.result', [$quiz->id, $attempt->id])
                ->with('warning', 'Time limit exceeded. Quiz has been automatically submitted.');
        }

        // Get questions with user's answers
        $userAnswers = $attempt->answers ?? [];
        $questions = $quiz->questions()->with('answers')->get()->map(function ($question) use ($userAnswers) {
            $selectedAnswerId = $userAnswers[$question->id] ?? null;
            $selectedAnswer = $selectedAnswerId ? $question->answers->find($selectedAnswerId) : null;

            return [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'options' => $question->answers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'answer' => $answer->getTranslation(),
                        'is_correct' => $answer->is_correct,
                    ];
                }),
                'user_answer' => $selectedAnswer ? [
                    'id' => $selectedAnswer->id,
                    'answer' => $selectedAnswer->getTranslation(),
                ] : null,
            ];
        });

        return Inertia::render('Quiz/Attempt', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'time_limit' => $quiz->time_limit,
                'total_questions' => $questions->count(),
            ],
            'attempt' => [
                'id' => $attempt->id,
                'started_at' => $attempt->started_at,
                'time_remaining' => $attempt->time_limit ? 
                    max(0, $attempt->started_at->addMinutes($attempt->time_limit)->diffInSeconds(now())) : null,
            ],
            'questions' => $questions,
        ]);
    }

    /**
     * Save answer for a question
     */
    public function saveAnswer(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        $user = Auth::user();

        // Verify attempt belongs to user
        if ($attempt->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if attempt is completed
        if ($attempt->completed_at) {
            return response()->json(['error' => 'Quiz attempt is already completed'], 400);
        }

        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'selected_answer_id' => 'required|exists:answers,id',
        ]);

        // Verify question belongs to quiz
        $question = Question::where('id', $request->question_id)
            ->where('quiz_id', $quiz->id)
            ->first();

        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        // Verify answer belongs to question
        $answer = Answer::where('id', $request->selected_answer_id)
            ->where('question_id', $request->question_id)
            ->first();

        if (!$answer) {
            return response()->json(['error' => 'Answer not found'], 404);
        }

        // Save answer to attempt
        $answers = $attempt->answers ?? [];
        $answers[$request->question_id] = $request->selected_answer_id;
        
        $attempt->update(['answers' => $answers]);

        return response()->json(['success' => true, 'message' => 'Answer saved']);
    }

    /**
     * Submit quiz attempt
     */
    public function submit(Quiz $quiz, QuizAttempt $attempt)
    {
        $user = Auth::user();

        // Verify attempt belongs to user
        if ($attempt->user_id !== $user->id) {
            abort(403);
        }

        // Check if attempt is already completed
        if ($attempt->completed_at) {
            return redirect()->route('quiz.attempt.result', [$quiz->id, $attempt->id]);
        }

        $this->completeAttempt($attempt);

        return redirect()->route('quiz.attempt.result', [$quiz->id, $attempt->id])
            ->with('success', 'Quiz submitted successfully!');
    }

    /**
     * Show quiz attempt result
     */
    public function result(Quiz $quiz, QuizAttempt $attempt)
    {
        $user = Auth::user();

        // Verify attempt belongs to user
        if ($attempt->user_id !== $user->id) {
            abort(403);
        }

        // Ensure attempt is completed
        if (!$attempt->completed_at) {
            return redirect()->route('quiz.attempt.show', [$quiz->id, $attempt->id]);
        }

        // Get detailed results
        $userAnswers = $attempt->answers ?? [];
        $questions = $quiz->questions()->with('answers')->get()->map(function ($question) use ($userAnswers) {
            $selectedAnswerId = $userAnswers[$question->id] ?? null;
            $selectedAnswer = $selectedAnswerId ? $question->answers->find($selectedAnswerId) : null;
            $correctAnswer = $question->answers->where('is_correct', true)->first();

            return [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'user_answer' => $selectedAnswer ? [
                    'id' => $selectedAnswer->id,
                    'answer' => $selectedAnswer->getTranslation(),
                ] : null,
                'correct_answer' => $correctAnswer ? [
                    'id' => $correctAnswer->id,
                    'answer' => $correctAnswer->getTranslation(),
                ] : null,
                'is_correct' => $selectedAnswer && $correctAnswer ? 
                    $selectedAnswer->id === $correctAnswer->id : false,
            ];
        });

        $totalQuestions = $questions->count();
        $correctAnswers = $questions->where('is_correct', true)->count();
        $percentage = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

        return Inertia::render('Quiz/Result', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'passing_score' => $quiz->passing_score,
            ],
            'attempt' => [
                'id' => $attempt->id,
                'score' => $attempt->score,
                'percentage' => $percentage,
                'started_at' => $attempt->started_at,
                'completed_at' => $attempt->completed_at,
                'duration' => $attempt->started_at->diffInMinutes($attempt->completed_at),
                'passed' => $quiz->passing_score ? $percentage >= $quiz->passing_score : true,
            ],
            'questions' => $questions,
            'summary' => [
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'incorrect_answers' => $totalQuestions - $correctAnswers,
                'percentage' => round($percentage, 2),
            ],
        ]);
    }

    /**
     * Complete quiz attempt and calculate score
     */
    private function completeAttempt(QuizAttempt $attempt)
    {
        DB::transaction(function () use ($attempt) {
            $quiz = $attempt->quiz;
            $userAnswers = $attempt->answers ?? [];
            $totalQuestions = $quiz->questions()->count();
            
            // Update attempt with total questions
            $attempt->update([
                'total_questions' => $totalQuestions,
            ]);
            
            // Calculate score using the model's method
            $score = $attempt->calculateScore();
            
            // Mark as completed
            $attempt->markAsCompleted();
        });
    }
}