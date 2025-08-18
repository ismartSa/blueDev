<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        $quizzes = Quiz::all();

        foreach ($quizzes as $quiz) {
            $questionCount = rand(5, 10); // Random number of questions per quiz

            for ($i = 1; $i <= $questionCount; $i++) {
                $question = Question::create([
                    'quiz_id' => $quiz->id,
                    'question_title_en' => "Question $i for " . $quiz->title . " (English)",
                    'question_title_ar' => "السؤال $i لـ " . $quiz->title . " (العربية)",
                    'question_type_id' => rand(1, 4), // Assuming 4 types of questions
                    'correct_answers_required' => rand(1, 3),

                //    'image' => null,
                    //'demo' => null,
                ]);

                // Create answers for each question
                $answerCount = rand(2, 5);
                $correctAnswer = rand(1, $answerCount);

                for ($j = 1; $j <= $answerCount; $j++) {
                    Answer::create([
                        'question_id' => $question->id,
                        'answer_en' => "Answer $j for Question $i (English)",
                        'answer_ar' => "الإجابة $j للسؤال $i (العربية)",
                        'is_correct' => ($j === $correctAnswer),
                        'feedback' => "Feedback for Question $i",
                        'feedback_ar' => "Feedback for Question $i",
                    ]);
                }
            }
        }
    }
}
