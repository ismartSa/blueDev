<?php

namespace App\Imports;

use App\Models\{Question, Answer, Quiz};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Log, Validator};
use Maatwebsite\Excel\Concerns\{
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    SkipsOnError,
    SkipsOnFailure
};
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class QuestionsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    SkipsOnError,
    SkipsOnFailure
{
    protected Quiz $quiz;
    protected int $rowNumber = 0;
    protected array $importStats = [
        'success' => 0,
        'failed' => 0,
        'total' => 0
    ];

    public function __construct(int $quizId)
    {
        $this->quiz = Quiz::findOrFail($quizId);
    }

    /**
     * معالجة صف البيانات وإنشاء نموذج السؤال
     */
    public function model(array $row)
    {
        $this->rowNumber++;
        $this->importStats['total']++;

        try {
            return DB::transaction(function () use ($row) {
                $question = $this->createQuestion($row);
                $this->createAnswers($row, $question);

                $this->importStats['success']++;
                return $question;
            });
        } catch (Throwable $e) {
            $this->importStats['failed']++;
            $this->logError($e, $row);
            throw $e;
        }
    }

    /**
     * إنشاء سؤال جديد
     */
    protected function createQuestion(array $row): Question
    {
        return Question::create([
            'quiz_id' => $this->quiz->id,
            'question_title' => trim($row['question']),
            'question_type_id' => (int) $row['question_type_id'],
            'correct_answers_required' => (int) $row['correct_answers_required'],
            'points' => $row['points'] ?? 1,
            'explanation' => $row['explanation'] ?? null,
        ]);
    }

    /**
     * إنشاء إجابات للسؤال
     */
    protected function createAnswers(array $row, Question $question): void
    {
        $answers = [];
        $hasCorrectAnswer = false;

        for ($i = 1; $i <= 4; $i++) {
            if (!empty($row['answer' . $i])) {
                $isCorrect = filter_var($row['is_correct' . $i] ?? false, FILTER_VALIDATE_BOOLEAN);

                $answers[] = [
                    'question_id' => $question->id,
                    'answer' => trim($row['answer' . $i]),
                    'is_correct' => $isCorrect,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($isCorrect) {
                    $hasCorrectAnswer = true;
                }
            }
        }

        if (!$hasCorrectAnswer) {
            throw new \Exception("السؤال في الصف {$this->rowNumber} يجب أن يحتوي على إجابة صحيحة واحدة على الأقل");
        }

        Answer::insert($answers);
    }

    /**
     * قواعد التحقق من صحة البيانات
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:3', 'max:1000'],
            'question_type_id' => ['required', 'integer', 'exists:question_types,id'],
            'correct_answers_required' => ['required', 'integer', 'min:1', 'max:4'],
            'points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'explanation' => ['nullable', 'string', 'max:2000'],
            'answer1' => ['required', 'string', 'max:500'],
            'is_correct1' => ['boolean'],
            'answer2' => ['required', 'string', 'max:500'],
            'is_correct2' => ['boolean'],
            'answer3' => ['nullable', 'string', 'max:500'],
            'is_correct3' => ['nullable', 'boolean'],
            'answer4' => ['nullable', 'string', 'max:500'],
            'is_correct4' => ['nullable', 'boolean'],
        ];
    }

    /**
     * رسائل التحقق من صحة البيانات المخصصة
     */
    public function customValidationMessages(): array
    {
        return [
            'question.required' => 'حقل السؤال مطلوب في الصف :row',
            'question.min' => 'يجب أن يحتوي السؤال على 3 أحرف على الأقل في الصف :row',
            'question_type_id.required' => 'نوع السؤال مطلوب في الصف :row',
            'question_type_id.exists' => 'نوع السؤال غير صالح في الصف :row',
            'correct_answers_required.required' => 'عدد الإجابات الصحيحة المطلوبة مطلوب في الصف :row',
            'answer1.required' => 'الإجابة الأولى مطلوبة في الصف :row',
            'answer2.required' => 'الإجابة الثانية مطلوبة في الصف :row',
        ];
    }

    /**
     * معالجة الأخطاء وتسجيلها
     */
    protected function logError(Throwable $e, array $row): void
    {
        Log::error('خطأ في استيراد السؤال', [
            'row_number' => $this->rowNumber,
            'quiz_id' => $this->quiz->id,
            'error' => $e->getMessage(),
            'data' => $row
        ]);
    }

    /**
     * تخطي الصفوف التي تحتوي على أخطاء
     */
    public function onError(Throwable $e)
    {
        Log::warning('تم تخطي صف بسبب خطأ', [
            'row_number' => $this->rowNumber,
            'error' => $e->getMessage()
        ]);
    }

    /**
     * تخطي الصفوف التي فشل التحقق من صحتها
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('فشل التحقق من صحة البيانات', [
                'row_number' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors()
            ]);
        }
    }

    /**
     * تحديد حجم الدفعة للإدخال
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * تحديد حجم القراءة للملف
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * الحصول على إحصائيات الاستيراد
     */
    public function getImportStats(): array
    {
        return $this->importStats;
    }
}
