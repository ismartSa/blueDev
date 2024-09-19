<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuestionController extends Controller
{
    public function create(Quiz $quiz)
    {
        return Inertia::render('Questions/Create', [
            'quiz' => $quiz
        ]);
    }

    public function store(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_title_en' => 'required|string',
            'question_title_ar' => 'required|string',
            'question_type_id' => 'required|integer',
            'correct_answers_required' => 'required|integer',
            'answers' => 'required|array',
            'answers.*.answer_en' => 'required|string',
            'answers.*.answer_ar' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        $question = $quiz->questions()->create([
            'question_title_en' => $validated['question_title_en'],
            'question_title_ar' => $validated['question_title_ar'],
            'question_type_id' => $validated['question_type_id'],
            'correct_answers_required' => $validated['correct_answers_required'],
        ]);

        foreach ($validated['answers'] as $answerData) {
            $question->answers()->create([
                'answer_en' => $answerData['answer_en'],
                'answer_ar' => $answerData['answer_ar'],
                'is_correct' => $answerData['is_correct'],
            ]);
        }

        return redirect()->route('quizzes.show', $quiz->id)->with('success', 'Question added successfully.');
    }

    // ... المزيد من الدوال حسب الحاجة
}
