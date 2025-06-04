<?php

namespace App\Http\Controllers\Course;

use App\Models\Quiz;
use Inertia\Inertia;
use App\Models\Answer;
use App\Models\Course;
use App\Models\Lecture;
use App\Models\Section;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourseContentController extends Controller
{
    public function storeSection(Request $request, Course $course)
    {
        // ... existing storeSection method code ...
    }

    public function storeLecture(Request $request, Course $course)
    {
        // ... existing storeLecture method code ...
    }

    public function showQuizzes(Course $course)
    {
        // Get all quizzes for the course, organized by domain/chapter
        $quizzes = $course->quizzes()
            ->with(['questions.answers', 'attempts' => function($query) {
                $query->where('user_id', auth()->id());
            }])
            ->orderBy('domain')
            ->orderBy('chapter')
            ->orderBy('order')
            ->get()
            ->groupBy(['domain', 'chapter']);

        return Inertia::render('Dashboard/Course/DetailsCourse', [
            'course' => $course->load('category'),
            'lessons' => $course->lectures()->paginate(10),
            'stats' => [
                'enrolled_students' => $course->enrollments()->count(),
                'total_views' => $course->views ?? 0,
            ],
            'quizzes' => $quizzes,
            'quizDomains' => $quizzes->keys(), // Available domains
        ]);
    }

    public function showQuiz(Course $course, Quiz $quiz)
    {
        $questions = $quiz->questions()->with('answers')->get();

        // Check if user has already attempted this quiz
        $userAttempt = $quiz->attempts()
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        return Inertia::render('Dashboard/Course/QuizInterface', [
            'course' => $course,
            'quiz' => $quiz,
            'questions' => $questions,
            'userAttempt' => $userAttempt,
        ]);
    }

    public function submitQuiz(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'time_taken' => 'required|integer',
        ]);

        // Create quiz attempt
        $attempt = QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'answers' => json_encode($validated['answers']),
            'time_taken' => $validated['time_taken'],
            'score' => $this->calculateScore($quiz, $validated['answers']),
            'completed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Quiz submitted successfully!');
    }

    private function calculateScore(Quiz $quiz, array $answers)
    {
        $totalScore = 0;
        $questions = $quiz->questions()->with('answers')->get();

        foreach ($questions as $question) {
            $correctAnswer = $question->answers()->where('is_correct', true)->first();
            if ($correctAnswer && isset($answers[$question->id]) && $answers[$question->id] == $correctAnswer->id) {
                $totalScore += $question->points ?? 1;
            }
        }

        return $totalScore;
    }

    public function createQuiz(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'domain' => 'nullable|string|max:100',
            'chapter' => 'nullable|string|max:100',
            'quiz_type' => 'required|in:practice,chapter_test,final,assessment',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'time_limit' => $validated['time_limit'],
            'passing_score' => $validated['passing_score'],
            'domain' => $validated['domain'],
            'chapter' => $validated['chapter'],
            'quiz_type' => $validated['quiz_type'],
            'course_id' => $course->id,
            'section_id' => $validated['section_id'],
            'is_active' => true,
            'order' => Quiz::where('course_id', $course->id)->count() + 1,
        ]);

        return redirect()->back()->with('success', 'Quiz created successfully!');
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_title_en' => 'required|string',
            'question_title_ar' => 'nullable|string',
            'question_type_id' => 'required|integer',
            'correct_answers_required' => 'required|integer|min:1',
            'feedback' => 'nullable|string',
            'points' => 'nullable|integer|min:1',
            'answers' => 'required|array|min:2',
            'answers.*.answer_en' => 'required|string',
            'answers.*.answer_ar' => 'nullable|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question_title_en' => $validated['question_title_en'],
            'question_title_ar' => $validated['question_title_ar'],
            'question_type_id' => $validated['question_type_id'],
            'correct_answers_required' => $validated['correct_answers_required'],
            'feedback' => $validated['feedback'],
            'points' => $validated['points'] ?? 1,
        ]);

        foreach ($validated['answers'] as $answerData) {
            Answer::create([
                'question_id' => $question->id,
                'answer_en' => $answerData['answer_en'],
                'answer_ar' => $answerData['answer_ar'],
                'is_correct' => $answerData['is_correct'],
            ]);
        }

        return redirect()->back()->with('success', 'Question added successfully!');
    }
}
