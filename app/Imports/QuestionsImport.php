<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Answer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToModel, WithHeadingRow
{
    protected $quizId;

    public function __construct($quizId)
    {
        $this->quizId = $quizId;
    }

    public function model(array $row)
    {
        $question = Question::create([
            'quiz_id' => $this->quizId,
            'question_title' => $row['question'],
            'question_type_id' => $row['question_type_id'],
            'correct_answers_required' => $row['correct_answers_required'],
        ]);

        // Assuming answers are in columns 'answer1', 'answer2', 'answer3', 'answer4'
        // and their correctness in 'is_correct1', 'is_correct2', 'is_correct3', 'is_correct4'
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($row['answer' . $i])) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer' => $row['answer' . $i],
                    'is_correct' => $row['is_correct' . $i] ?? false,
                ]);
            }
        }

        return $question;
    }
}
